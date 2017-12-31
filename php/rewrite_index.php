<?php
require_once "../Model/PDO_MySQL.class.php";
require_once "../Model/config.php";

require_once "function.php";

session_start();

// 提供给超级管理员rewrite myblog所有页面的操作
if(isset($_GET['myblog'])&&$_GET['myblog']=="1"){
    if(isset($_SESSION['weight'])&&$_SESSION['weight']==3){
        $get_article = M('article')->select();
        $number = count($get_article);
        for($i = 0;$i < $number;$i++){
            write_tmp_index_html($get_article[$i]['article_id']);
            print("已重写article_id为".$get_article[$i]['article_id']."的myblog页面\n");
        }
    }else{
        echo "权限不足，请登录超管账号\n";
    }
}

// 提供给超级管理员rewrite blog所有页面的操作
if(isset($_GET['blog'])&&$_GET['blog']=="1"){
    if(isset($_SESSION['weight'])&&$_SESSION['weight']==3){
        $get_article = M('blog')->field('blog.article_id, htmlCode')->join(array('article on article.article_id = blog.article_id','LEFT'))->select();
        $number = count($get_article);
        for($i = 0;$i < $number;$i++){
            write_index_html($get_article[$i]['article_id'], $get_article[$i]['htmlCode']);
            print("已重写article_id为".$get_article[$i]['article_id']."的blog页面\n");
        }
    }else{
        echo "权限不足，请登录超管账号\n";
    }
}

