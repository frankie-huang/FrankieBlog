<?php
require_once "Model/PDO_MySQL.class.php";
require_once "Model/config.php";

require_once "vendor/autoload.php";
use Workerman\Worker;

// SSL context.
$context = array(
    'ssl' => array(
        'local_cert'  => '/etc/nginx/CA/1_myafei.cn_bundle.crt',
        'local_pk'    => '/etc/nginx/CA/2_myafei.cn.key',
        'verify_peer' => false,
    )
);

// Create a Websocket server with ssl context.
$ws_worker = new Worker("websocket://0.0.0.0:19910/blog", $context);

// Enable SSL. WebSocket+SSL means that Secure WebSocket (wss://). 
// The similar approaches for Https etc.
$ws_worker->transport = 'ssl';

// 1 processes
$ws_worker->count = 1;

// Emitted when new connection come
$ws_worker->onConnect = 'callbackConnect';
// Emitted when data received
$ws_worker->onMessage = 'callbackNewData';
// Emitted when connection closed
$ws_worker->onClose = 'callbackConnectClose';

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Run worker
Worker::runAll();

function callbackConnect($connect)
{
    $connect->onWebSocketConnect = function ($connect, $http_header) {
        // 可以在这里判断连接来源是否合法，不合法就关掉连接
        // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket连接
        if ($_SERVER['HTTP_ORIGIN']!='https://myafei.cn'&&$_SERVER['HTTP_ORIGIN'] != 'https://www.myafei.cn') {
            $connect->send("只接受来自myafei.cn站点的连接");
            $connect->close();
        }
        // onWebSocketConnect 里面$_GET $_SERVER是可用的
        // var_dump($_GET, $_SERVER);
    };
}

//$return['status']：0为无错误，1为发送消息成功（反馈），100为身份验证成功，-100为被异地登录挤退，-1为出现错误，或-2（见下一行注释）
//$return['status']为-2时：当前时间和redis设置的live_time相差超过30秒，可能是服务器延迟，更有可能是当前用户登录失效
function callbackNewData($connect, $data)
{
    global $ws_worker;
    global $redis;
    $msg=json_decode($data);
    if ($msg->status==0) {
        if (isset($msg->u_id)&&isset($msg->token)) {
            if ($redis->get('user_token'.$msg->u_id)!=$msg->token) {
                send_message($connect, $return, -1, '非法连接');
                $connect->close();
            }
            $get_user = getByUid($msg->u_id);
            if ($get_user==false) {
                send_message($connect, $return, -1, '用户不存在');
                $connect->close();
            }
        } else {
            send_message($connect, $return, -1, '非法连接');
            $connect->close();
        }
        //获取该用户组的所有连接，判断ip是否相同，否则断开前面ip不同的所有连接
        $get_redis_set = $redis->sMembers($get_user['u_id']);
        $get_redis_set_number = $redis->sCard($get_user['u_id']);
        if ($redis->get('client_ip'.$get_user['u_id'])!=$msg->ip) {
            $redis->delete($get_user['u_id']);//删除redis这个集合中所有元素
            for ($i=0; $i<$get_redis_set_number; $i++) {
                if (isset($ws_worker->connections[$get_redis_set[$i]])) {
                    send_message($ws_worker->connections[$get_redis_set[$i]], $return, -100, '被异地登录');
                    $ws_worker->connections[$get_redis_set[$i]]->close();
                }
            }
        }
        $redis->sAdd($get_user['u_id'], $connect->id);
        $redis->set('client_ip'.$get_user['u_id'], $msg->ip);
        $redis->set('connect_id'.$connect->id, $get_user['u_id']);
        $return['id']=$connect->id;
        send_message($connect, $return, 100, '验证成功');
    } elseif ($msg->status==1) {
        $current_uid = $redis->get('connect_id'.$connect->id);//获取发送者的u_id
        if ($msg->u_id!=$current_uid) {
            send_message($connect, $return, -1, '当前用户信息验证错误');
            return;
        }
        if (isset($msg->send_to_me)&&$msg->send_to_me==true) {
            $get_user = getByUserName('frankie');
            if ($get_user==false) {
                send_message($connect, $return, -1, '我去火星旅游啦');
                return;
            }
        } else {
            $get_user = getByUid($msg->object);
            if ($get_user==false) {
                send_message($connect, $return, -1, '接收者不存在');
                return;
            }
        }
        if ($get_user['u_id']==$current_uid) {
            send_message($connect, $return, -1, '不能给自己发消息哦');
            return;
        }
        if (time()-strtotime($redis->get('live_time'.$current_uid))>30) {
            send_message($connect, $return, -2, '超时，服务器繁忙，请联系管理员');
            return;
        }
        if ($msg->is_textarea=='1') {
            $msg->content = html_encode($msg->content);
        }
        $add_data=array(
            'from_id'=>$current_uid,
            'content'=>$msg->content,
            'to_id'=>$get_user['u_id'],
            'send_time'=>date('Y/m/d H:i:s'),
        );
        $res = M('message')->add($add_data);
        if ($res===false) {
            send_message($connect, $return, -1, '操作失败，请联系后台管理员');
            return;
        }
        //获取接收者用户组的所有连接，发送消息
        $object_group = $redis->sMembers($get_user['u_id']);
        $object_group_number = $redis->sCard($get_user['u_id']);
        $return_to_receiver['id']=$res;
        $return_to_receiver['send_time']=$add_data['send_time'];
        $return_to_receiver['from_username']=$msg->from_username;
        $return_to_receiver['from_id']=$current_uid;
        $return_to_receiver['content']=$msg->content;
        for ($i=0; $i<$object_group_number; $i++) {
            if (isset($ws_worker->connections[$object_group[$i]])) {
                send_message($ws_worker->connections[$object_group[$i]], $return_to_receiver);
            }
        }
        //向发送消息者及其所在用户组的所有连接，回馈发送成功的消息
        $sender_group = $redis->sMembers($current_uid);
        $sender_group_number = $redis->sCard($current_uid);
        $return['id']=$res;
        $return['send_time']=$add_data['send_time'];
        $return['to_id']=$get_user['u_id'];
        $return['content']=$msg->content;
        for ($i=0; $i<$sender_group_number; $i++) {
            if (isset($ws_worker->connections[$sender_group[$i]])) {
                send_message($ws_worker->connections[$sender_group[$i]], $return, 1, '发送成功');
            }
        }
    }
}

function callbackConnectClose($connect)
{
    global $redis;
    $redis->sRem($redis->get('connect_id'.$connect->id), $connect->id);
    $redis->delete('connect_id'.$connect->id);
    $connect->send('88');
}

function send_message($connect, &$return, $status = 0, $error = '')
{
    $return['status']=$status;
    $return['error']=$error;
    $connect->send(json_encode($return));
}

function getByUid($u_id)
{
    $users = M("users");
    $where['u_id'] = $u_id;
    $result = $users->where($where)->select();
    if ($result==false) {
        return false;
    } else {
        return $result[0];
    }
}

function getByUserName($username)
{
    $users = M("users");
    $where['username'] = $username;
    $result = $users->where($where)->select();
    if ($result==false) {
        return false;
    } else {
        return $result[0];
    }
}
