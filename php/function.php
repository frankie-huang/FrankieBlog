<?php
header('Content-type:text/json');
require_once "ret_template.php";

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

//检测是否登录
function is_login($post)
{
    global $redis;
    $return['status']=0;
    session_start();
    if (isset($_SESSION['u_id'])) {
        $get_user=getByUid($_SESSION['u_id']);
        if ($get_user==false) {
            ret_status($return, -1, 'session所指用户不存在');
        }
        $return=$get_user;
        unset($return['password']);
        //检查是否有新消息
        $where['to_id']=$_SESSION['u_id'];
        $where['is_read']=false;
        $res=M('message')->where($where)->count('content');
        $return['number']=$res;
        $return['token']=mt_rand();
        $redis->set('user_token'.$get_user['u_id'], $return['token']);
        $return['ip']=get_client_ip(0, true);
        $return['weight']=$_SESSION['weight'];
        ret_status($return);
    }
    recordFootprint();
    ret_status($return, -2, '未登录');
}

function is_login_home($post)
{
    global $redis;
    $return['status']=0;
    $message = M('message');
    session_start();
    if (isset($_SESSION['u_id'])) {
        $get_user=getByUid($_SESSION['u_id']);
        if ($get_user==false) {
            ret_status($return, -1, 'session所指用户不存在');
        }
        $return=$get_user;
        unset($return['password']);
        //检查是否有新消息
        $where['to_id']=$_SESSION['u_id'];
        $where['is_read']=false;
        $res=$message->where($where)->count('content');
        $return['number']=$res;
        $return['token']=mt_rand();
        $redis->set('user_token'.$get_user['u_id'], $return['token']);
        //获取所有发给当前用户消息的用户头像&昵称以及消息数
        // $new_where['to_id']=$_SESSION['u_id'];
        // $res=$message->field('from_id,username,head,sex')->join('users on from_id = u_id')->group('from_id')->where($new_where)->select();
       
        $new_where1['to_id']=$_SESSION['u_id'];
        $subsql1 = $message->field('from_id')->group('from_id')->where($new_where1)->buildSql();
        $new_where2['from_id']=$_SESSION['u_id'];
        $subsql2 = $message->field('to_id AS from_id')->group('to_id')->where($new_where2)->buildSql();
        $res=$message->table('('.$subsql1.' UNION '.$subsql2.') AS t')->field('from_id,username,head,sex')->join('users on from_id = u_id')->select();

        $length=count($res);
        for ($i=0; $i<$length; $i++) {
            $new_where3['from_id']=$res[$i]['from_id'];
            $new_where3['to_id']=$_SESSION['u_id'];
            $new_where3['is_read']=0;
            $res[$i]['number']=$message->where($new_where3)->count('content');
            if ($res[$i]['number']==0) {
                $res[$i]['number']='';
            }
        }
        $return['message']=$res;
        $return['ip']=get_client_ip(0, true);
        $return['weight']=$_SESSION['weight'];
        ret_status($return);
    }
    recordFootprint();
    ret_status($return, -2, '未登录');
}

function is_login_editor($post)
{
    global $redis;
    $return['status']=0;
    session_start();
    if (isset($_SESSION['u_id'])) {
        $get_user=getByUid($_SESSION['u_id']);
        if ($get_user==false) {
            ret_status($return, -1, 'session所指用户不存在');
        }
        $return=$get_user;
        unset($return['password']);
        if (isset($post['id'])&&is_numeric($post['id'])) {
            $where_article['article_id']=$post['id'];
            $get_article = M('article')->where($where_article)->find();
            if ($get_article!=false) {
                if ($get_article['u_id']==$_SESSION['u_id']||$_SESSION['weight']>2) {
                    $return['mdCode']=$get_article['mdCode'];
                    $where_article['is_published']=1;
                    if (M('blog')->where($where_article)->find()!=false) {
                        $return['is_published']=true;
                    }
                }
            }
        }
        //检查是否有新消息
        $where['to_id']=$_SESSION['u_id'];
        $where['is_read']=false;
        $res=M('message')->where($where)->count('content');
        $return['number']=$res;
        $return['token']=mt_rand();
        $redis->set('user_token'.$get_user['u_id'], $return['token']);
        $return['ip']=get_client_ip(0, true);
        $return['weight']=$_SESSION['weight'];
        ret_status($return);
    }
    recordFootprint();
    ret_status($return, -2, '未登录');
}

function is_login_tmpblog($post)
{
    global $redis;
    $return['status']=0;
    session_start();
    if (isset($_SESSION['u_id'])) {
        $get_user=getByUid($_SESSION['u_id']);
        if ($get_user==false) {
            ret_status($return, -1, 'session所指用户不存在');
        }
        $return=$get_user;
        unset($return['password']);
        //检测当前登录者是否有权限查看
        $where_article['article_id']=$post['article_id'];
        $get_article = M('article')->where($where_article)->find();
        if ($get_article==false) {
            $return['denied']=true;
            ret_status($return, 0, '查无博客内容信息');
        }
        if ($get_article['u_id']!=$_SESSION['u_id']&&$_SESSION['weight']<2) {
            $return['denied']=true;
            ret_status($return, 0, '无权限查看他人博客');
        }
        $get_blog=M('blog')->where($where_article)->find();
        if ($get_blog==false) {
            $return['is_submit']=false;
        } else {
            $return['is_submit']=true;
        }
        $return['htmlCode']=$get_article['htmlCode'];
        //检查是否有新消息
        $where['to_id']=$_SESSION['u_id'];
        $where['is_read']=false;
        $res=M('message')->where($where)->count('content');
        $return['number']=$res;
        $return['token']=mt_rand();
        $redis->set('user_token'.$get_user['u_id'], $return['token']);
        $return['ip']=get_client_ip(0, true);
        $return['weight']=$_SESSION['weight'];
        ret_status($return);
    }
    recordFootprint();
    ret_status($return, -2, '未登录');
}

function is_login_admin($post)
{
    global $redis;
    $return['status']=0;
    session_start();
    if (isset($_SESSION['u_id'])) {
        $get_user=getByUid($_SESSION['u_id']);
        if ($get_user==false) {
            ret_status($return, -1, 'session所指用户不存在');
        }
        if ($_SESSION['weight']<2) {
            ret_status($return, 403, '抱歉，权限不够');
        }
        $return=$get_user;
        unset($return['password']);
        //获取待审核博客列表
        $where_blog['is_published']=0;
        $where_blog['modified']=1;
        $where_blog['_logic']='or';
        $get_blog=M('blog')->where($where_blog)->order('submit_time DESC')->select();
        $return['blogs']=$get_blog;
        //检查是否有新消息
        $where['to_id']=$_SESSION['u_id'];
        $where['is_read']=false;
        $res=M('message')->where($where)->count('content');
        $return['number']=$res;
        $return['token']=mt_rand();
        $redis->set('user_token'.$get_user['u_id'], $return['token']);
        $return['ip']=get_client_ip(0, true);
        $return['weight']=$_SESSION['weight'];
        ret_status($return);
    }
    recordFootprint();
    ret_status($return, -2, '未登录');
}

function login($post)
{
    global $redis;
    if ($post['is_email']=='true') {
        $get_user = getByEmail($post['username']);
    } else {
        $get_user = getByUserName($post['username']);
    }
    if ($get_user == false) {
        ret_status($return, -1, '用户名或邮箱不存在');
    }
    if (!password_verify($post['password'], $get_user['password'])) {
        ret_status($return, -2, '密码错误');
    }
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        ret_status($return, -3, '无法获取浏览器信息');
    }
    $data=array(
        'u_id'=>$get_user['u_id'],
        'ip'=>get_client_ip(0, true),
        'browser'=>returnBrowser($_SERVER['HTTP_USER_AGENT']),
        'userAgent'=>$_SERVER['HTTP_USER_AGENT'],
        'record_time'=>date('Y/m/d H:i:s'),
    );
    if (M('device')->add($data)===false) {
        ret_status($return, -4, '数据库更新出错');
    }
    if ($post['isRemember'] == 'true') {
        start_session(RememberTime);
    } else {
        session_start();
    }
    $_SESSION['u_id']=$get_user['u_id'];
    $_SESSION['username']=$post['username'];
    $_SESSION['email']=$get_user['email'];
    $get_weight=is_manager($get_user['u_id']);
    if ($get_weight!=false) {
        $_SESSION['weight']=$get_weight;
    } else {
        $_SESSION['weight']=0;
    }
    $return=$get_user;
    unset($return['password']);
    //检查是否有新消息
    $where['to_id']=$_SESSION['u_id'];
    $where['is_read']=false;
    $res=M('message')->where($where)->count('content');
    $return['number']=$res;
    $return['token']=mt_rand();
    $redis->set('user_token'.$get_user['u_id'], $return['token']);
    ret_status($return);
    session_write_close();
}

function register($post)
{
    session_start();
    $post['username']=trim($post['username']);
    $post['email']=trim($post['email']);
    if (empty($post['username'])) {
        ret_status($return, -1, '用户名为空');
    }
    $get_user = getByUserName($post['username']);
    if ($get_user!=false||$post['username']=="404-user") {
        ret_status($return, -1, '用户名已被注册或命名不可利用');
    }
    $get_user = getByEmail($post['email']);
    if ($get_user!=false) {
        ret_status($return, -1, '邮箱已被注册');
    }
    if (!in_array($post['sex'], array('0','1','2'))) {
        ret_status($return, -1, '性别数据出现错误');
    }
    if (!isset($_SESSION['authcode'])||strlen($_SESSION['authcode'])<4) {
        ret_status($return, -2, '验证码过期');
    }
    if (strtolower($post['CaptchaCode'])!=$_SESSION['authcode']) {
        ret_status($return, -3, '验证码错误');
    }
    $inactivated_users = M('inactivated_users');
    $seed = mt_rand();
    $data=array(
        'username'=>$post['username'],
        'email'=>$post['email'],
        'password'=>password_hash($post['password'], PASSWORD_BCRYPT),
        'sex'=>$post['sex'],
        'register_time'=>date('Y/m/d H:i:s'),
        'seed'=>$seed
    );
    $res = $inactivated_users->add($data);
    if ($res===false) {
        ret_status($return, -1, '注册失败，数据库插入数据出错');
    }
    $_SESSION['tmp_username']=$post['username'];
    $_SESSION['tmp_email']=$post['email'];
    $return['id']=$res;
    ret_status($return);
}

function resend_email($post)
{
    $res = M('inactivated_users')->where(array('id'=>$post['id']))->select();
    if ($res!=false) {
        if (getByEmail($res[0]['email'])!=false) {
            ret_status($return, -100, '您的邮箱已激活，请勿重复激活');
        }
        $token=password_hash($res[0]['username'].$res[0]['seed'], PASSWORD_BCRYPT);
        $username=$res[0]['username'];
        $url='http://'.$_SERVER['HTTP_HOST'].$post['path'].'active.php'.'?n='.$post['id'].'&u='.$res[0]['username'].'&e='.$res[0]['email'].'&token='.$token;
        $urlencode = urlencode($url);
        $content=<<<EOF
        <h2>来自Frankie的一封信：</h2>
        <h3>Hello {$username},<h3>
        <h1 style="color:red">如果此账号并非您注册，请忽略此邮件，请勿点击链接。</h1>
        请点击此链接激活帐号<br/>
        <a href="{$url}">{$urlencode}</a><br/>
        如果点此链接无反应，可以将其复制到浏览器中来执行，链接的有效时间为24小时。
EOF;
        $subject='来自Frankie个人博客网站的邮箱激活邮件';
        send_email($subject, $content, $res[0]['email'], $username);
        ret_status($return);
    } else {
        ret_status($return, -1, '数据库查询出错');
    }
}

function logout($post)
{
    session_start();
    session_destroy();
    ret_status($return);
}

function update_username($post)
{
    session_start();
    if (!isset($_SESSION['u_id'])) {
        ret_status($return, -2, '未登录');
    }
    $post['new_nick']=trim($post['new_nick']);
    if (empty($post['new_nick'])) {
        ret_status($return, -1, '用户名为空');
    }
    $get_user=getByUserName($post['new_nick']);
    if ($get_user!=false) {
        ret_status($return, -3, '抱歉，该用户名已被注册');
    }
    $update=M('users')->where(array('u_id'=>$_SESSION['u_id']))->setField('username', $post['new_nick']);
    if ($update!==1) {
        ret_status($return, -1, '数据库更新失败');
    }
    ret_status($return);
}

function update_sex($post)
{
    session_start();
    if (!isset($_SESSION['u_id'])) {
        ret_status($return, -2, '未登录');
    }
    if ($post['new_sex']!='0'&&$post['new_sex']!='1'&&$post['new_sex']!='2') {
        ret_status($return, -1, '性别数据错误');
    }
    $update=M('users')->where(array('u_id'=>$_SESSION['u_id']))->setField('sex', $post['new_sex']);
    if ($update!==1) {
        ret_status($return, -1, '数据库更新失败');
    }
    ret_status($return);
}

function update_password($post)
{
    session_start();
    if (!isset($_SESSION['u_id'])) {
        ret_status($return, -2, '未登录');
    }
    $get_user=getByUid($_SESSION['u_id']);
    if (!password_verify($post['oldPassword'], $get_user['password'])) {
        ret_status($return, -3, '原密码验证错误');
    }
    $update=M('users')->where(array('u_id'=>$_SESSION['u_id']))->setField('password', password_hash($post['newPassword'], PASSWORD_BCRYPT));
    if ($update!==1) {
        ret_status($return, -1, '数据库更新失败');
    }
    ret_status($return);
}

//submit.html页面初始化时加载blog信息
function init_submit($post)
{
    session_start();
    if (isset($_SESSION['u_id'])) {
        //检测当前用户是否有权限继续操作
        $where_article['article_id']=$post['id'];
        $get_article=M('article')->where($where_article)->select();
        if (count($get_article)!==1) {
            ret_status($return, 404, '查无博客数据');
        }
        if ($get_article[0]['u_id']!=$_SESSION['u_id']) {
            if ($_SESSION['weight']<2) {
                ret_status($return, -3, '您无权限编辑他人博客信息');
            }
        }
        $return['username']=$_SESSION['username'];
        $return['weight']=$_SESSION['weight'];
        $get_blog=M('blog')->where($where_article)->find();
        if ($get_blog!=false) {
            $return['is_submit']=true;
            if ($get_blog['is_published']==1&&$_SESSION['weight']<2) {
                $return['notice']=true;
            }
            $return['title']=$get_blog['title'];
            $return['author']=$get_blog['author'];
            $return['cover']=$get_blog['cover'];
            $return['summary']=$get_blog['summary'];
            //获取这篇博客的标签
            $return['tags']=get_tags_article($get_blog['article_id']);
        }
        //返回博客内容信息
        $return['htmlCode']=$get_article[0]['htmlCode'];
        $return['mdCode']=$get_article[0]['mdCode'];
        $return['submit_time']=$get_article[0]['first_time'];
        $return['last_time']=$get_article[0]['last_time'];
        ret_status($return);
    }
    ret_status($return, -2, '未登录');
}

function submit_article($post)
{
    session_start();
    if (!isset($_SESSION['u_id'])) {
        ret_status($return, -1, '当前处于未登录状态');
    }
    if (empty($post['mdCode'])||preg_match('/^\s+$/', $post['mdCode'])==1) {
        ret_status($return, -1, '提交内容为空');
    }
    if (empty($post['htmlCode'])||preg_match('/^\s+$/', $post['htmlCode'])==1) {
        ret_status($return, -1, '提交内容为空');
    }
    $data=array(
        'u_id'=>$_SESSION['u_id'],
        'mdCode'=>$post['mdCode'],
        'htmlCode'=>$post['htmlCode'],
        'first_time'=>date('Y/m/d H:i:s'),
        'last_time'=>date('Y/m/d H:i:s'),
    );
    $res=M('article')->add($data);
    if ($res===false) {
        ret_status($return, -1, '数据库更新失败，请联系管理员');
    }
    write_tmp_index_html($res);
    $return['id']=$res;
    ret_status($return);
}

//涉及多表更新
//更新博客内容信息（todo：同时更新index.html页面内容）
function update_article($post)
{
    session_start();
    if (!isset($_SESSION['u_id'])) {
        ret_status($return, -1, '当前处于未登录状态');
    }
    if (empty($post['mdCode'])||preg_match('/^\s+$/', $post['mdCode'])==1) {
        ret_status($return, -1, '提交内容为空');
    }
    if (empty($post['htmlCode'])||preg_match('/^\s+$/', $post['htmlCode'])==1) {
        ret_status($return, -1, '提交内容为空');
    }
    $article_table = M('article');
    $where_article['article_id']=$post['id'];
    $get_article = $article_table->where($where_article)->find();
    if ($get_article==false) {
        ret_status($return, -1, '博客内容查询不到');
    }
    if ($get_article['u_id']!=$_SESSION['u_id']&&$_SESSION['weight']<2) {
        ret_status($return, -1, '您没有权限修改他人博客内容');
    }
    $article_table->startTrans();
    $data=array(
        'mdCode'=>$post['mdCode'],
        'htmlCode'=>$post['htmlCode'],
        'last_time'=>date('Y/m/d H:i:s'),
    );
    $res=$article_table->where($where_article)->save($data);
    if ($res!==1) {
        $article_table->rollback();
    }
    //非管理员，更新博客后需要审核才能生效
    $where_blog['article_id']=$post['id'];
    $where_blog['is_published']=1;
    $get_blog = $article_table->table('blog')->where($where_blog)->find();
    if ($get_blog!=false) {
        if ($_SESSION['weight']<2) {
            $update = $article_table->table('blog')->where($where_blog)->setField('modified', 1);
            if ($update===false) {
                $article_table->rollback();
                ret_status($return, -1, '数据库更新出错');
            }
            write_tmp_index_html($get_blog['article_id']);
        } else {
            $update = $article_table->table('blog')->where($where_blog)->setField('modified', 0);
            if ($update===false) {
                $article_table->rollback();
                ret_status($return, -1, '数据库更新出错');
            }
            write_index_html($get_blog['article_id'], $post['htmlCode']);
        }
        $return['is_submit']=true;
    }
    $article_table->commit();
    ret_status($return);
}

function submit_blog($post)
{
    session_start();
    $where_article['article_id']=$post['id'];
    $get_article = M('article')->where($where_article)->select();
    if (count($get_article)!==1) {
        ret_status($return, -1, '查无博客数据');
    }
    if ($get_article[0]['u_id']!=$_SESSION['u_id']) {
        if ($_SESSION['weight']<2) {
            ret_status($return, -3, '您无权限修改他人博客信息');
        }
    }
    if (strtolower(trim($post['author_name']))=="frankie") {
        if ($_SESSION['weight']<3) {
            ret_status($return, -1, '抱歉，该笔名无法使用');
        }
    }
    if (preg_match('/^\s*$/', $post['cover'])) {
        $post['cover']=null;
    }
    if (preg_match('/^\s*$/', $post['summary'])) {
        $post['summary']=null;
    }
    $post['summary'] = htmlspecialchars_decode($post['summary']);
    $post['summary'] = html_encode($post['summary']);
    $data=array(
        'article_id'=>$post['id'],
        'title'=>trim($post['title']),
        'author'=>trim($post['author_name']),
        'cover'=>$post['cover'],
        'summary'=>$post['summary'],
        'submit_time'=>date('Y/m/d H:i:s')
    );
    $blog_table = M('blog');
    $blog_table->startTrans();
    $get_blog = $blog_table->where($where_article)->find();
    if ($get_blog!=false) {
        if ($_SESSION['weight']<2) {
            //没有权限更新博客信息，只能等待审核
            $data['modified']=1;
            $res=$blog_table->where($where_article)->save($data);
            if ($res===false) {
                $blog_table->rollback();
                ret_status($return, '-1', '数据库更新出错');
            }
            write_tmp_index_html($post['id']);
            if ($get_blog['is_published']==0) {
                $return['is_published']=false;
            }
        } else {
            // $data['published_time']=date('Y/m/d H:i:s');
            $data['is_published']=1;
            $data['modified']=0;
            $res=$blog_table->where($where_article)->save($data);
            if ($res===false) {
                $blog_table->rollback();
                ret_status($return, '-1', '数据库更新出错');
            }
            write_index_html($post['id'], $get_article[0]['htmlCode']);
        }
    } else {
        if ($_SESSION['weight']<2) {
            //没有权限发布博客，只能等待审核
            $data['is_published']=0;
            $data['published_time']=null;
            $res=$blog_table->add($data);
            if ($res===false) {
                $blog_table->rollback();
                ret_status($return, '-1', '数据库更新出错');
            }
            write_tmp_index_html($post['id']);
            $return['is_published']=false;
        } else {
            $data['is_published']=1;
            $data['published_time']=date('Y/m/d H:i:s');
            $data['modified']=0;
            $res=$blog_table->add($data);
            if ($res===false) {
                $blog_table->rollback();
                ret_status($return, '-1', '数据库更新出错');
            }
            write_index_html($post['id'], $get_article[0]['htmlCode']);
        }
    }
    // 先删除blog_tag表中此博客的标签
    $delete=$blog_table->table('blog_tag')->where($where_article)->delete();
    if ($delete===false) {
        $blog_table->rollback();
        ret_status($return, -1, '数据库删除操作执行失败');
    }
    // 向blog_tag表添加此博客的标签
    if ($post['tag_be_selected_number']!=0) {
        $dataList = array();
        for ($i=0; $i<$post['tag_be_selected_number']; $i++) {
            $dataList[]=array('article_id'=>$post['id'],'tag_id'=>$post['tags'][$i]);
        }
        $add_blog_tag = $blog_table->table('blog_tag')->addAll($dataList);
        if ($add_blog_tag===false) {
            $blog_table->rollback();
            ret_status($return, -1, '数据库批量插入操作执行失败');
        }
    }
    $blog_table->commit();
    $return['id']=$post['id'];
    ret_status($return);
}

//批准发布
function permit_blog($post)
{
    session_start();
    if ($_SESSION['weight']<2) {
        ret_status($return, -1, '权限不足');
    }
    $where_article['article_id']=$post['id'];
    $get_article=M('article')->where($where_article)->find();
    if ($get_article==false) {
        ret_status($return, -1, '查无博客内容数据');
    }
    $get_blog=M('blog')->where($where_article)->find();
    if ($get_blog==false) {
        ret_status($return, -1, '查无博客信息数据');
    }
    $save['is_published']=1;
    $save['modified']=0;
    if ($get_blog['published_time']==null) {
        $save['published_time']=date('Y/m/d H:i:s');
    }
    $update = M('blog')->where($where_article)->save($save);
    if ($update===false) {
        ret_status($return, -1, '数据库更新出错');
    }
    write_index_html($post['id'], $get_article['htmlCode']);
    ret_status($return);
}

//获取博客列表
function get_article_list($post)
{
    $blog = M('blog');
    $where['is_published']=1;
    $field=array('article_id','author','cover','published_time','summary','title');
    //获取指定页的博客列表
    $blogs = $blog->field($field)->page($post['page'], $post['display_number'])->order('published_time DESC')->where($where)->select();
    //计算总页数
    $total_number = $blog->count('article_id');
    $total_pages = $total_number / $post['display_number'];
    if ((int)$total_pages < $total_pages) {
        $total_pages = (int)$total_pages + 1;
    }
    //获取每篇博客的标签
    $blogs_number = count($blogs);
    $blog_tag_table = M('blog_tag');
    for ($i=0; $i<$blogs_number; $i++) {
        $where_article['article_id']=$blogs[$i]['article_id'];
        $blogs[$i]['tags']=$blog_tag_table->field('tag.tag_id,label')->join(array('tag on tag.tag_id = blog_tag.tag_id','LEFT'))->where($where_article)->select();
    }
    $return['blogs'] = $blogs;
    $return['total_pages'] = $total_pages;
    ret_status($return);
}

//搜索博客
function search_article_list($post)
{
    $table = M('blog');
    $subSQL=array();
    $where['is_published']=1;
    //精准查找
    if (isset($post['tag_id'])&&$post['tag_id']!=0) {
        $tag_to_blog = $table->table('blog_tag')->field('article_id')->where(['tag_id'=>$post['tag_id']])->select();
        $length_tag_to_blog = count($tag_to_blog);
        if($length_tag_to_blog == 0){
            $where_in_array = '';
        }else{
            $where_in_array = array();
            for($i=0;$i<$length_tag_to_blog;$i++){
                $where_in_array[] = $tag_to_blog[$i]['article_id'];
            }       
        }
        $where['blog.article_id'] = array('in', $where_in_array);
    }
    if (isset($post['time_before'])&&!empty($post['time_before'])) {
        //指定时间之前
        $where_published_time[]=array('ELT',$post['time_before']);
    }
    if (isset($post['time_after'])&&!empty($post['time_after'])) {
        //指定时间之后
        $where_published_time[]=array('EGT',$post['time_after']);
    }
    if (isset($where_published_time)) {
        $where['published_time']=array_merge($where_published_time, array('and','_tomulti'=>true));
    }
    //精准查找end
    $field = array('blog.article_id','title','author','summary','published_time','mdCode'=>'content');
    $sql = $table->table('blog')->field($field)->join('article on blog.article_id=article.article_id')->where($where)->buildSql();
    //关键词查找
    if (isset($post['label'])&&!empty($post['label'])) {
        //解析标签查询关键词
        $attr="label";
        $tmp_where=array();
        $tmp_where[$attr]=array('like',$post[$attr],'or');
        $field_label = array('a1.article_id','title','author','summary','published_time','content','attr','weight');
        $subSQL[] = $table->table($sql.' AS a1')->field($field_label)->join(array('blog_tag on a1.article_id=blog_tag.article_id','LEFT'))->join(array('tag on tag.tag_id=blog_tag.tag_id','LEFT'))->join(array('search_weight on search_weight.attr="'.$attr.'"','LEFT'))->where($tmp_where)->buildSql();
    }
    if (isset($post['title'])&&!empty($post['title'])) {
        //解析标题查询关键词
        $attr="title";
        $tmp_where=array();
        $tmp_where[$attr]=array('like',$post[$attr],'or');
        $subSQL[] = $table->table($sql.' AS a2')->join(array('search_weight on search_weight.attr="'.$attr.'"','LEFT'))->where($tmp_where)->buildSql();
    }
    if (isset($post['summary'])&&!empty($post['summary'])) {
        //解析概要查询关键词
        $attr="summary";
        $tmp_where=array();
        $tmp_where[$attr]=array('like',$post[$attr],'or');
        $subSQL[] = $table->table($sql.' AS a3')->join(array('search_weight on search_weight.attr="'.$attr.'"','LEFT'))->where($tmp_where)->buildSql();
    }
    if (isset($post['content'])&&!empty($post['content'])) {
        //解析博客内容查询关键词
        $attr="content";
        $tmp_where=array();
        $tmp_where[$attr]=array('like',$post[$attr],'or');
        $subSQL[] = $table->table($sql.' AS a4')->join(array('search_weight on search_weight.attr="'.$attr.'"','LEFT'))->where($tmp_where)->buildSql();
    }
    if (isset($post['author'])&&!empty($post['author'])) {
        //解析作者笔名查询关键词
        $attr="author";
        $tmp_where=array();
        $tmp_where[$attr]=array('like',$post[$attr],'or');
        $subSQL[] = $table->table($sql.' AS a5')->join(array('search_weight on search_weight.attr="'.$attr.'"','LEFT'))->where($tmp_where)->buildSql();
    }
    //关键词查找end
    $count_sql = count($subSQL);
    if ($count_sql==0) {
        $field_blogs=array('distinct(blog.article_id)','author','cover','published_time','summary','title');
        //获取指定页的博客列表 // 使用published_time倒序排列
        $blogs = $table->field($field_blogs)->join(array('blog_tag on blog.article_id=blog_tag.article_id','LEFT'))->page($post['page'], $post['display_number'])->order('published_time DESC')->where($where)->select();
        //计算总页数
        $total_number = $table->join(array('blog_tag on blog.article_id=blog_tag.article_id','LEFT'))->where($where)->count('blog.article_id');
        $total_pages = $total_number / $post['display_number'];
        if ((int)$total_pages < $total_pages) {
            $total_pages = (int)$total_pages + 1;
        }
    } else {
        $total_sql='';
        for ($i=0; $i<$count_sql; $i++) {
            $total_sql.=$subSQL[$i].' UNION ALL ';
        }
        $total_sql=rtrim($total_sql, ' UNION ALL ');
        $total_sql = '( '.$total_sql.' )';
        $set_blogs_weight=$table->table($total_sql.' AS a')->field('article_id, SUM(weight) AS weight')->group('article_id')->order('weight DESC')->buildSql();
        //进行分页
        $get_page=$table->table($set_blogs_weight.' AS b')->page($post['page'], $post['display_number'])->buildSql();
        $field_blogs=array('c.article_id','author','cover','published_time','summary','title','weight');
        $blogs=$table->field($field_blogs)->table($get_page.' AS c')->join('blog on blog.article_id=c.article_id')->order('weight DESC')->select();
        //计算总页数
        $total_number = $table->table($set_blogs_weight.' AS b')->count('article_id');
        $total_pages = $total_number / $post['display_number'];
        if ((int)$total_pages < $total_pages) {
            $total_pages = (int)$total_pages + 1;
        }
    }
    //获取每篇博客的标签
    $blogs_number = count($blogs);
    $blog_tag_table = M('blog_tag');
    for ($i=0; $i<$blogs_number; $i++) {
        $where_article['article_id']=$blogs[$i]['article_id'];
        $blogs[$i]['tags']=$blog_tag_table->field('tag.tag_id,label')->join(array('tag on tag.tag_id = blog_tag.tag_id','LEFT'))->where($where_article)->select();
    }
    $return['total_pages'] = $total_pages;
    $return['blogs']=$blogs;
    ret_status($return);
}

//获取所有标签列表
function get_tag_list($post)
{
    $get_tag = M('tag')->field('tag.tag_id,label,COUNT(article_id) as number')->join(array('blog_tag as bt on bt.tag_id = tag.tag_id','LEFT'))->group('tag_id')->order('number DESC')->select();
    $return['tag_list']=$get_tag;
    ret_status($return);
}

function add_tag($post)
{
    session_start();
    if (!$_SESSION['u_id']) {
        ret_status($return, -2, "未登录");
    }
    $post['label']=trim($post['label']);
    $tag_table = M('tag');
    $where_tag['label']=$post['label'];
    $select_tag = $tag_table->where($where_tag)->find();
    if ($select_tag===null) {
        $data=array(
            'label'=>$post['label'],
            'by'=>$_SESSION['u_id'],
            'time'=>date('Y/m/d H:i:s'),
        );
        $res = $tag_table->add($data);
        if ($res===false) {
            ret_status($return, -1, '数据库更新失败');
        }
        $return['id']=$res;
        ret_status($return);
    } else {
        ret_status($return, -1, '标签已存在，不可重复添加');
    }
}

//获取某篇博客的标签
function get_tags_article($article_id)
{
    $where['article_id']=$article_id;
    $tags=M('blog_tag')->field('tag.tag_id,label')->join(array('tag on tag.tag_id = blog_tag.tag_id','LEFT'))->where($where)->select();
    return $tags;
}

function get_comment($post)
{
    $blog_table = M('blog');
    $where_article['article_id']=$post['article_id'];
    $get_blog = $blog_table->where($where_article)->find();
    if ($get_blog==false) {
        ret_status($return, -2, "查无博客信息数据");
    }
    $get_article = $blog_table->table('article')->where($where_article)->find();
    if ($get_article==false) {
        ret_status($return, -1, "查无博客内容数据");
    }
    //增加阅读量
    if ($blog_table->where($where_article)->setInc('read_time')!==1) {
        ret_status($return, -1, '数据库更新失败');
    }
    $get_read_time=$blog_table->field('read_time')->where($where_article)->find();
    if ($get_read_time==false) {
        ret_status($return, -1, '数据库查询出错');
    }
    $return['read_time']=$get_read_time['read_time'];
    $get_comment = $blog_table->table('comment')->where($where_article)->select();
    if ($get_comment === false) {
        ret_status($return, -1, "数据库查询出错(评论表)");
    }
    $comment_number = count($get_comment);
    $return['comment_number']=$comment_number;
    $reply = M('reply');
    for ($i=0; $i<$comment_number; $i++) {
        $get_user = getByUid($get_comment[$i]['u_id']);
        if ($get_user==false) {
            $get_comment[$i]['username']="404-user";
        } else {
            if ($get_user['u_id']==$get_article['u_id']) {
                $get_comment[$i]['username']=$get_blog['author'];
            } else {
                $get_comment[$i]['username']=$get_user['username'];
            }
        }
        $get_comment[$i]['sex']=$get_user['sex'];
        $get_comment[$i]['head']=$get_user['head'];
        if ($get_comment[$i]['is_deleted']==1) {
            $get_comment[$i]['content']=null;
        }
        //检索是否有回复
        $get_reply = $reply->where(array('comment_id'=>$get_comment[$i]['id']))->select();
        if ($get_reply===false) {
            ret_status($return, -1, "数据库查询出错(回复表)");
        }
        $reply_number = count($get_reply);
        for ($j=0; $j<$reply_number; $j++) {
            //设置发起回复者信息
            $get_user = getByUid($get_reply[$j]['from']);
            if ($get_user==false) {
                $get_reply[$j]['username']="404-user";
            } else {
                if ($get_user['u_id']==$get_article['u_id']) {
                    $get_reply[$j]['username']=$get_blog['author'];
                } else {
                    $get_reply[$j]['username']=$get_user['username'];
                }
            }
            $get_reply[$j]['sex']=$get_user['sex'];
            $get_reply[$j]['head']=$get_user['head'];
            //设置被回复者昵称
            $get_user = getByUid($get_reply[$j]['to']);
            if ($get_user==false) {
                $get_reply[$j]['to_name']="404-user";
            } else {
                if ($get_user['u_id']==$get_article['u_id']) {
                    $get_reply[$j]['to_name']=$get_blog['author'];
                } else {
                    $get_reply[$j]['to_name']=$get_user['username'];
                }
            }
        }
        $get_comment[$i]['reply']=$get_reply;
    }
    $get_author = getByUid($get_article['u_id']);
    if ($get_author==false) {
        ret_status($return, -1, '作者信息查询不到');
    }
    $return['author_id']=$get_article['u_id'];
    $return['author_name']=$get_blog['author'];
    $return['author_head']=$get_author['head'];
    $return['title']=$get_blog['title'];
    $return['cover']=$get_blog['cover'];
    $return['comment']=$get_comment;
    ret_status($return);
}

function comment($post)
{
    session_start();
    if (!isset($_SESSION['u_id'])) {
        ret_status($return, -1, "未登录");
    }
    // if ($post['is_textarea']) {
    //     $post['content'] = html_encode($post['content']);
    // }
    $data=array(
        'article_id'=>$post['article_id'],
        'u_id'=>$_SESSION['u_id'],
        'content'=>$post['content'],
        'time'=>date('Y/m/d H:i:s'),
    );
    $res = M('comment')->add($data);
    if ($res===false) {
        ret_status($return, -1, '数据库更新错误');
    }
    $return['id']=$res;
    $return['time']=$data['time'];
    ret_status($return);
}

function reply($post)
{
    session_start();
    if (!isset($_SESSION['u_id'])) {
        ret_status($return, -1, "未登录");
    }
    // if ($post['is_textarea']) {
    //     $post['content'] = html_encode($post['content']);
    // }
    $data=array(
        'comment_id'=>$post['comment_id'],
        'from'=>$_SESSION['u_id'],
        'content'=>$post['content'],
        'to'=>$post['to'],
        'time'=>date('Y/m/d H:i:s'),
    );
    $res = M('reply')->add($data);
    if ($res===false) {
        ret_status($return, -1, '数据库更新错误');
    }
    $return['id']=$res;
    $return['time']=$data['time'];
    ret_status($return);
}

function delete_comment($post)
{
    session_start();
    $comment_table = M('comment');
    $where['id']=$post['comment_id'];
    $get_comment = $comment_table->where($where)->find();
    if ($get_comment==false) {
        ret_status($return, -1, '数据库查询不到评论数据');
    }
    if ($get_comment['u_id']!=$_SESSION['u_id']) {
        ret_status($return, -1, '您无法删除他人的评论');
    }
    $get_reply = M('reply')->where(array('comment_id'=>$post['comment_id']))->count('id');
    if ($get_reply==='0') {
        $return['no_reply']=true;
        $delete = $comment_table->where($where)->delete();
        if ($delete!=1) {
            ret_status($return, '-1', '数据库更新出错');
        }
    } elseif ($get_reply>0) {
        $return['no_reply']=false;
        $update = $comment_table->where($where)->setField('is_deleted', 1);
        if ($update!=1) {
            ret_status($return, '-1', '数据库更新出错');
        }
    } else {
        ret_status($return, '-1', '数据库查询出错');
    }
    ret_status($return);
}

function delete_reply($post)
{
    session_start();
    $reply_table = M('reply');
    $where_reply['id']=$post['reply_id'];
    $get_reply = $reply_table->where($where_reply)->find();
    if ($get_reply==false) {
        ret_status($return, -1, '数据库查询不到回复数据');
    }
    if ($get_reply['from']!=$_SESSION['u_id']) {
        ret_status($return, -1, '您无法删除他人的回复');
    }
    $where_comment['id']=$get_reply['comment_id'];
    $get_comment = M('comment')->where($where_comment)->find();
    if ($get_comment==false) {
        ret_status($return, '-1', '数据库查询不到对应的评论数据');
    } else {
        if ($get_comment['is_deleted']==1) {
            $return['comment_is_deleted']=true;
            $is_have_reply = $reply_table->where(array('comment_id'=>$get_comment['id']))->count('id');
            if ($is_have_reply==='1') {
                $return['no_reply']=true;
                $delete = M('comment')->where($where_comment)->delete();
                if ($delete!=1) {
                    ret_status($return, '-1', '数据库更新出错');
                }
            } elseif ($is_have_reply>1) {
                $return['no_reply']=false;
                $delete = $reply_table->where($where_reply)->delete();
                if ($delete!=1) {
                    ret_status($return, '-1', '数据库更新出错');
                }
            } else {
                ret_status($return, '-1', '数据库查询出错');
            }
        } else {
            $return['comment_is_deleted']=false;
            $delete = $reply_table->where($where_reply)->delete();
            if ($delete!=1) {
                ret_status($return, '-1', '数据库更新出错');
            }
        }
    }
    ret_status($return);
}

function returnBrowser($userAgent)
{
    $B_name = "other";
    if (strpos($userAgent, "Firefox") !== false) {
        $B_name = "Firefox";
    } elseif (strpos($userAgent, "MQQBrowser") !== false) {
        //专门识别手机微信、QQ内置QQ浏览器
        if (strpos($userAgent, "MicroMessenger") !== false) {
            $B_name = "WeChat_QQBrowser";
        } elseif (strpos($userAgent, "QQ") !== false) {
            $B_name = "QQ_QQBrowser";
        } else {
            //手机端QQ浏览器
            $B_name = "MQQBrowser";
        }
    } elseif (strpos($userAgent, "QQBrowser") !== false) {
        $B_name = "QQBrowser";
    } elseif (strpos($userAgent, "UCBrowser") !== false) {
        $B_name = "UCBrowser";
    } elseif (strpos($userAgent, "MiuiBrowser") !== false) {
        $B_name = "MiuiBrowser";
    } elseif (strpos($userAgent, "Edge") !== false) {
        $B_name = "Edge";
    } elseif (strpos($userAgent, "OPR") !== false || strpos($userAgent, "Opera") !== false) {
        $B_name = "Opera";
    } elseif (strpos($userAgent, "Safari")!==false && strpos($userAgent, "Mac")!==false) {
        $B_name = "Safari";
    } elseif (strpos($userAgent, "Chrome") !== false) {
        $B_name = "Chrome";
    } elseif (strpos($userAgent, "MSIE") !== false && strpos($userAgent, "Trident") !== false) {
        $B_name = "IE(8-10)";
    }
    return $B_name;
}

//记录游客足迹（ip和浏览器数据）
function recordFootprint()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $B_name = returnBrowser($userAgent);
    $data = array(
        'ip'=>get_client_ip(0, true),
        'browser'=>$B_name,
        'userAgent'=>$userAgent,
        'record_time'=>date('Y/m/d H:i:s'),
    );
    M('recordFootprint')->add($data);
}

//write对应blog_id的(blog文件夹下的)index.html
function write_index_html($blog_id, $content)
{
    shell_exec('mkdir ../blog/'.$blog_id);
    $file = fopen('../blog/'.$blog_id.'/index.html', 'w');
    fwrite($file, ret_template($blog_id, $content));
    fclose($file);
}

//write对应article_id的(myblog文件夹下的)index.html
function write_tmp_index_html($blog_id)
{
    shell_exec('mkdir ../myblog/'.$blog_id);
    $file = fopen('../myblog/'.$blog_id.'/index.html', 'w');
    fwrite($file, ret_tmp_template($blog_id));
    fclose($file);
}

//发送邮件API
function send_email($subject, $content, $email, $username)
{
    require_once '../vendor/autoload.php';
    // Create the Transport
    $transport = (new Swift_SmtpTransport("smtp.exmail.qq.com", 25))
        ->setUsername(EmailAddress)  //设置发送方邮箱
        ->setPassword(EmailPassword)               //填写邮箱密码
    ;
    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);
    // Create a message
    $message = (new Swift_Message($subject))               //设置邮件主题
        ->setFrom([EmailAddress => "Frankie"])    //设置发送方的邮箱和称呼
        ->setTo(array($email=>$username))                  //指定接收方邮箱和称呼
        ->setBody($content, 'text/html', 'utf-8')            //设置邮件正文内容
    ;
    // Send the message
    $result = $mailer->send($message);
    return $result;
}

//根据用户的u_id获取某用户的个人信息（u_id，性别，头像，昵称）
function get_someone($post)
{
    $get_user=getByUid($post['u_id']);
    if ($get_user==false) {
        ret_status($return, -1, '查无此人');
    }
    $return=$get_user;
    unset($return['password']);
    unset($return['email']);
    unset($return['register_time']);
    ret_status($return);
}

//初始化聊天窗口时获取所有消息记录
function get_message($post)
{
    $where1['from_id&to_id']=array($post['object'],$post['user'],'_tosingle'=>true);
    $where2['_complex']=$where1;
    $where2['from_id&to_id']=array($post['user'],$post['object'],'_tosingle'=>true);
    $where2['_logic']='or';
    $res=M('message')->where($where2)->order('send_time')->select();
    $where1['is_read']=0;
    $save['is_read']=1;
    $update=M('message')->where($where1)->save($save);
    $return['message']=$res;
    ret_status($return);
}

//已初始化过窗口，再次点击窗口获取是否有新消息记录
function get_new_message($post)
{
    $where['id']=array('gt',$post['last_id']);
    $where['from_id&to_id']=array($post['user'],$post['object'],'_tosingle'=>true);
    $where['from_id&to_id']=array($post['object'],$post['user'],'_tosingle'=>true);
    $res=M('message')->where($where)->order('send_time')->select();
    $where['is_read']=0;
    $save['is_read']=1;
    $update=M('message')->where($where)->save($save);
    $return['message']=$res;
    ret_status($return);
}

//将某条消息标为已读
function change_to_read($post)
{
    $where['id']=$post['id'];
    $save['is_read']=1;
    $res = M('message')->where($where)->save($save);
    if ($res===false) {
        ret_status($return, -1, '更新失败');
    }
    ret_status($return);
}

//是否拥有该权限的管理员
function is_manager($u_id, $weight = 0)
{
    $manager = M('manager');
    $where['u_id']=$u_id;
    if ($weight!=0) {
        if (is_array($weight)) {
             $weight[]='or';
             $weight['_tomulti']=true;
        }
        $where['weight']=$weight;
    }
    $res = $manager->where($where)->select();
    if ($res==false) {
        return false;
    }
    if (count($res)==1) {
        return $res[0]['weight'];
    }
    return false;
}

function getByUid($u_id)
{
    $users = M("users");
    $where['u_id'] = $u_id;
    $result = $users->where($where)->find();
    if ($result==false) {
        return false;
    } else {
        return $result;
    }
}

function getByUserName($username)
{
    $users = M("users");
    $where['username'] = $username;
    $result = $users->where($where)->find();
    if ($result==false) {
        return false;
    } else {
        return $result;
    }
}

function getByEmail($email)
{
    $users = M("users");
    $where['email'] = $email;
    $result = $users->where($where)->find();
    if ($result==false) {
        return false;
    } else {
        return $result;
    }
}

function get_users($post)
{
    session_start();
    if (is_manager($_SESSION['u_id'], array(2,3))===false) {
        ret_status($return, -1, '权限不够');
    }
    $users = M("users");
    $result = $users->field('password', true)->select();
    $return['users']=$result;
    ret_status($return);
}

//检测是否登录(for websocket)
function is_login_for_ws($post)
{
    global $redis;
    session_start();
    if (isset($_SESSION['u_id'])) {
        $get_user=getByUid($_SESSION['u_id']);
        if ($get_user==false) {
            ret_status($return, -1, 'session所指用户不存在');
        }
        $return=$get_user;
        unset($return['password']);
        $redis->set('live_time'.$get_user['u_id'], date('Y/m/d H:i:s'));
        $return['ip']=get_client_ip(0, true);
        $return['weight']=$_SESSION['weight'];
        ret_status($return);
    }
    ret_status($return, -2, '未登录');
}

//AJAX返回
function ret_status(&$return, $status = 0, $error = '')
{
    $return['status']=$status;
    $return['error']=$error;
    exit(json_encode($return));
}

function start_session($expire = 0)
{
    if ($expire == 0) {
        $expire = ini_get('session.gc_maxlifetime');
    } else {
        ini_set('session.gc_maxlifetime', $expire);
    }
    if (empty($_COOKIE['PHPSESSID'])) {
        session_set_cookie_params($expire);
        session_start();
    } else {
        session_start();
        setcookie('PHPSESSID', session_id(), time() + $expire, '/');
    }
}