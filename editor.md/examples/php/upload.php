<?php

    /*
	 * PHP upload demo for Editor.md
     *
     * @FileName: upload.php
     * @Auther: Pandao
     * @E-mail: pandao@vip.qq.com
     * @CreateTime: 2015-02-13 23:20:04
     * @UpdateTime: 2015-02-14 14:52:50
     * Copyright@2015 Editor.md all right reserved.
	 */

    header("Content-Type:application/json; charset=utf-8"); // Unsupport IE
    // header("Content-Type:text/html; charset=utf-8");
    // header("Access-Control-Allow-Origin: *");

    // require("editormd.uploader.class.php");

    error_reporting(E_ALL & ~E_NOTICE);
    
    // $path     = __DIR__ . DIRECTORY_SEPARATOR;
    // $url      = dirname($_SERVER['PHP_SELF']) . '/';
    // $savePath = realpath($path . '../uploads/') . DIRECTORY_SEPARATOR;
    // $saveURL  = $url . '../uploads/';

    // $formats  = array(
    //     'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp')
    // );

    $name = 'editormd-image-file';

    // if (isset($_FILES[$name]))
    // {
    //     $imageUploader = new EditorMdUploader($savePath, $saveURL, $formats['image'], false);  // Ymdhis表示按日期生成文件名，利用date()函数
        
    //     $imageUploader->config(array(
    //         'maxSize' => 1024,        // 允许上传的最大文件大小，以KB为单位，默认值为1024
    //         'cover'   => true         // 是否覆盖同名文件，默认为true
    //     ));
        
    //     if ($imageUploader->upload($name))
    //     {
    //         $imageUploader->message('上传成功！', 1);
    //     }
    //     else
    //     {
    //         $imageUploader->message('上传失败！', 0);
    //     }
    // }

function ret_message($success = 1, $message = '', $url = '')
{
    $return['success']=$success;
    $return['message']=$message;
    $return['url']=$url;
    exit(json_encode($return));
}

$allowedExts = array("gif", "jpg", "pjpeg", "jpeg", "png", "bmp", "webp", "ico");
$temp = explode(".", $_FILES[$name]["name"]);
$extension = end($temp);        // 获取文件后缀名
if (!in_array($extension, $allowedExts)) {
    ret_message(0, '上传图片扩展名错误，'.$extension.'文件不被允许上传');
}

if (preg_match('/^image\/.*$/', $_FILES[$name]["type"])==0) {
    ret_message(0, '上传图片类型错误，'.$_FILES[$name]["type"].'不被支持');
}

if ($_FILES[$name]["size"]>10240000) {
    ret_message(0, '上传图片不能大于10MB');
}

if ($_FILES[$name]["error"] > 0) {
    ret_message(0, "上传失败，Return Code: " . $_FILES[$name]["error"]);
}

$root_path = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], 'editor.md/examples/php/upload.php'));

// $type = preg_match('/(?<=image\/)\w+/',$_FILES[$name]["type"],$match);
$path = date('Y-m-d').'_'.uniqid().'.'.$extension;
$savePath = '../../../uploads/md/'.$path;
$url = $root_path.'uploads/md/'.$path;

if (file_exists($savePath)) {
    ret_message(0, $savePath.' already exists. ');
} else {
    if (move_uploaded_file($_FILES[$name]["tmp_name"], $savePath)==false) {
        ret_message(0, '文件上传失败，可能是权限不够');
    }
    ret_message(1, '上传成功', $url);
}
