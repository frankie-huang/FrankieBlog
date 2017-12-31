<?php
header("Content-Type:application/json; charset=utf-8");
function ret_status(&$return, $status = 0, $error = '')
{
    $return['status']=$status;
    $return['error']=$error;
    exit(json_encode($return));
}

$allowedExts = array("gif", "jpg", "pjpeg", "jpeg", "png", "bmp", "webp", "ico");
$temp = explode(".", $_FILES['cover']["name"]);
$extension = end($temp);        // 获取文件后缀名
if (!in_array($extension, $allowedExts)) {
    ret_status($return, -1, '上传图片扩展名错误，'.$extension.'文件不被允许上传');
}

if (preg_match('/^image\/.*$/', $_FILES['cover']["type"])==0) {
    ret_status($return, -1, '上传图片类型错误，'.$_FILES['cover']["type"].'不被支持');
}

if ($_FILES['cover']["size"]>10240000) {
    ret_status($return, -1, '上传图片不能大于10MB');
}

if ($_FILES['cover']["error"] > 0) {
    ret_status($return, -1, "上传失败，Return Code: " . $_FILES['cover']["error"]);
}

// $type = preg_match('/(?<=image\/)\w+/',$_FILES['cover']['type'],$match);
$path = $_POST['id'].'_'.date('Y-m-d').'_'.uniqid().'.'.$extension;
$savePath = '../uploads/cover/'.$path;
$url = './uploads/cover/'.$path;

if (file_exists($savePath)) {
    ret_status($return, -1, $savePath.' already exists. ');
} else {
    if (move_uploaded_file($_FILES['cover']["tmp_name"], $savePath)==false) {
        ret_status($return, -1, '文件上传失败，可能是权限不够');
    }
    $return['url']=$url;
    ret_status($return, 0, '上传成功');
}
