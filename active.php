<?php
header('content-type:text/html;charset=utf-8');
require_once('Model/PDO_MySQL.class.php');
require_once('Model/config.php');
echo '<link rel="icon" href="Public/img/frankie.ico" type="image/x-icon">';
echo '<link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">';
echo '<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>';
$script1=<<<EOF
<script>
var i = 3;
setInterval(function() {
    i--;
    if (i == 0) window.open('./index.html','_self');
    $("#clock").html(i);
}, 1000)
</script>
EOF;
$script2=<<<EOF
<script>
var i = 3;
setInterval(function() {
    i--;
    if (i == 0) window.open('./login.html?_to=register','_self');
    $("#clock").html(i);
}, 1000)
</script>
EOF;
$get = $_GET;
if(!isset($get['n'])||!isset($get['u'])||!isset($get['e'])||!isset($get['token'])){
    echo '<h1 class="text-center">页面参数错误</h1><br/>';
    echo '<h1 class="text-center"><span id="clock">3</span>秒后跳转到<a href="./index.html">主页</a></h1>';
    echo $script1;
    exit(0);
}
$inactivated_users=M('inactivated_users');
$where['username']=$get['u'];
$where['email']=$get['e'];
$where['id']=$get['n'];
$res = $inactivated_users->where($where)->select();
if($res===false){
    echo '<h1 class="text-center">数据库查询出错，请联系后台管理员</h1><br/>';
    echo '<h1 class="text-center"><span id="clock">3</span>秒后跳转到<a href="./index.html">主页</a></h1>';
    echo $script1;
    exit(0);
}
if(count($res)!=1){
    echo '<h1 class="text-center">数据查询不到，请重新注册</h1><br/>';
    echo '<h1 class="text-center"><span id="clock">3</span>秒后跳转到<a href="./login.html?_to=register">注册页面</a></h1>';
    echo $script2;
    exit(0);
}
$users = M('users');
$is_active = $users->where(array('email'=>$get['e']))->select();
if($is_active!==false&&count($is_active)==1){
    echo '<h1 class="text-center">您已经激活过了，请勿重复激活</h1><br/>';
    echo '<h1 class="text-center"><span id="clock">3</span>秒后跳转到<a href="./index.html">主页</a></h1>';
    echo $script1;
    exit(0);
}
$date = date_create($res[0]['register_time']);
if(date_timestamp_get(date_create())-date_timestamp_get($date)>86400){
    echo '<h1 class="text-center">链接已失效，请重新注册</h1><br/>';
    echo '<h1 class="text-center"><span id="clock">3</span>秒后跳转到<a href="./login.html?_to=register">注册页面</a></h1>';
    echo $script2;
    exit(0);
}
if(!password_verify($res[0]['username'].$res[0]['seed'],$get['token'])){
    echo '<h1 class="text-center">激活失败，令牌错误</h1><br/>';
    echo '<h1 class="text-center"><span id="clock">3</span>秒后跳转到<a href="./index.html">主页</a></h1>';
    echo $script1;
    exit(0);
}
$data=array(
    'email'=>$get['e'],
    'username'=>$get['u'],
    'password'=>$res[0]['password'],
    'register_time'=>$res[0]['register_time'],
    'sex'=>$res[0]['sex'],
    'head'=>'./Public/img/default_head.jpg',
);
$res=$users->add($data);
if($res===false){
    echo '<h1 class="text-center">数据库操作出错，请联系后台管理员</h1><br/>';
    echo '<h1 class="text-center"><span id="clock">3</span>秒后跳转到<a href="./index.html">主页</a></h1>';
    echo $script1;
    exit(0);
}

//激活成功后发送欢迎消息
$welcome_msg = array(
    'from_id'=>1,
    'content'=>'<a href="./blog/1">Hello, '.$get['u'].'. Welcome to Frankie\'s Blog~</a>',
    'to_id'=>$res,
    'send_time'=>date('Y/m/d H:i:s'),
    'is_read'=>0,
);
M('message')->add($welcome_msg);

session_start();
if(isset($_SESSION['tmp_username'])&&isset($_SESSION['tmp_email'])){
    //激活成功后设置session    
    if($_SESSION['tmp_username']==$get['u']&&$_SESSION['tmp_email']==$get['e']){
        $_SESSION['u_id']=$res;
        $_SESSION['username']=$get['u'];
        $_SESSION['email']=$get['e'];
        $_SESSION['weight']=0;
    }
    unset($_SESSION['tmp_username']);
    unset($_SESSION['tmp_email']);
}
echo '<h1 class="text-center">恭喜，验证成功，账号已激活！</h1><br/>';
echo '<h1 class="text-center"><span id="clock">3</span>秒后跳转到<a href="./index.html">主页</a></h1>';
echo $script1;
?>