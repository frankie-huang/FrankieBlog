<?php
header('Content-type:text/json');
//引入MeepoPS
require_once 'MeepoPS/index.php';
require_once "Model/PDO_MySQL.class.php";
require_once "Model/config.php";

//使用WebSocket协议传输的Api类
$webSocket = new \MeepoPS\Api\Websocket('0.0.0.0', '19910');

//启动的子进程数量. 通常为CPU核心数
$webSocket->childProcessCount = 1;

//设置MeepoPS实例名称
$webSocket->instanceName = 'MeepoPS-WebSocket';

$webSocket->callbackNewData = 'callbackNewData';
$webSocket->callbackConnectClose = 'callbackConnectClose';


$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

//启动MeepoPS
\MeepoPS\runMeepoPS();

function send_message($connect,&$return,$status=0,$error=''){
    $return['status']=$status;
    $return['error']=$error;
    $connect->send(json_encode($return));
}

//$return['status']：0为无错误，1为发送消息成功（反馈），100为身份验证成功，-100为被异地登录挤退，-1为出现错误，或-2（见下一行注释）
//$return['status']为-2时：当前时间和redis设置的live_time相差超过30秒，可能是服务器延迟，更有可能是当前用户登录失效
function callbackNewData($connect, $data){
    global $redis;
    $msg=json_decode($data);
    if ($msg->status==0) {
        if(isset($msg->u_id)&&isset($msg->token)){
            if ($redis->get('user_token'.$msg->u_id)!=$msg->token) {
                send_message($connect,$return,-1,'非法连接');
                $connect->close();
            }
            $get_user = getByUid($msg->u_id);
            if($get_user==false){
                send_message($connect,$return,-1,'用户不存在');
                $connect->close();
            }
        }else{
            send_message($connect,$return,-1,'非法连接');
            $connect->close();
        }
        //获取该用户组的所有连接，判断ip是否相同，否则断开前面ip不同的所有连接
        $get_redis_set = $redis->sMembers($get_user['u_id']);
        $get_redis_set_number = $redis->sCard($get_user['u_id']);
        if($redis->get('client_ip'.$get_user['u_id'])!=$msg->ip){
            $redis->delete($get_user['u_id']);//删除redis这个集合中所有元素
            for($i=0;$i<$get_redis_set_number;$i++){
                if(isset($connect->instance->clientList[$get_redis_set[$i]])){
                    send_message($connect->instance->clientList[$get_redis_set[$i]],$return,-100,'被异地登录');
                    $connect->instance->clientList[$get_redis_set[$i]]->close();
                }
            }
        }
        // if(isset($connect->instance->clientList[$redis->get($get_user['u_id'])])&&$redis->get('client_ip'.$get_user['u_id'])!=$msg->ip){
        //     send_message($connect->instance->clientList[$redis->get($get_user['u_id'])],$return,-100,'被异地登录');
        //     $connect->instance->clientList[$redis->get($get_user['u_id'])]->close();
        // }
        $redis->sAdd($get_user['u_id'],$connect->id);
        $redis->set('client_ip'.$get_user['u_id'],$msg->ip);
        $redis->set('connect_id'.$connect->id,$get_user['u_id']);
        send_message($connect,$return,100,'验证成功');
    }else if($msg->status==1){
        $current_uid = $redis->get('connect_id'.$connect->id);//获取发送者的u_id
        if($msg->u_id!=$current_uid){
            send_message($connect,$return,-1,'当前用户信息验证错误');
            return;
        }
        if(isset($msg->send_to_me)&&$msg->send_to_me==true){
            $get_user = getByUserName('frankie');
            if($get_user==false){
                send_message($connect,$return,-1,'我去火星旅游啦');
                return;
            }
        }else{
            $get_user = getByUid($msg->object);
            if($get_user==false){
                send_message($connect,$return,-1,'接收者不存在');
                return;
            }
        }
        if($get_user['u_id']==$current_uid){
            send_message($connect,$return,-1,'不能给自己发消息哦');
            return;
        }
        if(time()-strtotime($redis->get('live_time'.$current_uid))>30){
            send_message($connect,$return,-2,'超时，服务器繁忙，请联系管理员');
            return;
        }
        if($msg->is_textarea=='1'){
            $msg->content = html_encode($msg->content);
        }
        $add_data=array(
            'from_id'=>$current_uid,
            'content'=>$msg->content,
            'to_id'=>$get_user['u_id'],
            'send_time'=>date('Y/m/d H:i:s'),
        );
        $res = M('message')->add($add_data);
        if($res===false){
            send_message($connect,$return,-1,'操作失败，请联系后台管理员');
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
        for($i=0;$i<$object_group_number;$i++){
            if(isset($connect->instance->clientList[$object_group[$i]])){
                send_message($connect->instance->clientList[$object_group[$i]],$return_to_receiver);
            }
        }
        //向发送消息者及其所在用户组的所有连接，回馈发送成功的消息
        $sender_group = $redis->sMembers($current_uid);
        $sender_group_number = $redis->sCard($current_uid);
        $return['id']=$res;
        $return['send_time']=$add_data['send_time'];
        $return['to_id']=$get_user['u_id'];
        $return['content']=$msg->content;
        for($i=0;$i<$sender_group_number;$i++){
            if(isset($connect->instance->clientList[$sender_group[$i]])){
                send_message($connect->instance->clientList[$sender_group[$i]],$return,1,'发送成功');
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