<?php

function ret_template($blog_id, $content)
{
    $str1=<<<EOF
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../../Public/img/frankie.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../../Public/img/frankie.ico" type="image/x-icon">

    <title>Frankie's Blog</title>

    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>

    <link href="../../Public/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <script src="../../Public/js/ie-emulation-modes-warning.js"></script>

    <link href="../../Public/css/blog.css" rel="stylesheet">
    <link href="../../Public/css/comment.css" rel="stylesheet">

    <!-- toastr CSS JS -->
    <link href="https://cdn.bootcss.com/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <script src="https://cdn.bootcss.com/toastr.js/latest/toastr.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- <link rel="stylesheet" href="../../editor.md/examples/css/style.css" /> -->
    <link rel="stylesheet" href="../../editor.md/css/editormd.css" />
    <script src="../../editor.md/examples/js/zepto.min.js"></script>
    <script src="../../editor.md/editormd.js"></script>

    <!-- include summernote -->
    <link rel="stylesheet" href="../../Public/summernote/dist/summernote.css">
    <script type="text/javascript" src="../../Public/summernote/dist/summernote.js"></script>

    <!-- rewrite Markdown CSS(Cmd Markdown Style) from https://github.com/wakaryry/editorMd -->
    <link href="../../Public/css/custom.css" rel="stylesheet">

    <!-- 防XSS注入 来自：http://jsxss.com/zh/index.html -->
    <script src="../../Public/js/xss.js"></script>

    <link href="../../Public/css/back_to_top.css" rel="stylesheet">
    <style>
        .sidebar .widget {
            background: #ffffff;
            padding: 21px 30px;
        }
        
        .widget {
            margin-bottom: 35px;
        }
        
        .widget .title {
            margin-top: 0;
            padding-bottom: 7px;
            border-bottom: 1px solid #ebebeb;
            margin-bottom: 21px;
            position: relative;
        }
        
        .widget .title:after {
            content: "";
            width: 90px;
            height: 1px;
            background: #2e6da4;
            position: absolute;
            left: 0;
            bottom: -1px;
        }

        .in-page-preview {
            position: fixed;
            top: 60px;
            right: 2%;
            z-index: 1000;
        }
        
        .toc-list {
            position: absolute;
            float: left;
            list-style: none outside none;
            border: 1px solid #ccc;
            background-clip: padding-box;
            border-radius: 4px;
            margin: 2px 0 0;
            padding: 5px 0 10px;
            min-width: 23em;
            top: 100%;
            right: 0em;
            font-family: Microsoft Yahei, Hiragino Sans GB, WenQuanYi Micro Hei, sans-serif;
            background: #fff;
        }
        
        .toc-list h3 {
            margin: 10px 0;
            padding-left: 15px
        }
        
        .toc-list hr {
            border: 0;
            border-bottom: 1px dashed #cfcfcf;
            margin: 15px 0;
        }
        
        .toc-list ul {
            padding-left: 0px;
        }
        
        .toc-list li {
            list-style: none outside none;
            padding-left: 1.5em;
        }
        
        .toc-list a {
            white-space: nowrap;
        }
        
        .toc-list a:hover {
            background: 0 0;
            color: #005580;
            text-decoration: underline
        }
        
        .ul-list {
            overflow-x: hidden;
            overflow-y: auto;
            width: 330px;
            max-height: 400px;
            padding: 5px 0
        }
    </style>
</head>

<body>
    <script>
        var article_id = {$blog_id};
    </script>
    <nav class="navbar navbar-inverse navbar-fixed-top" id="top_nav" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="../../index.html">Frankie's Blog</a>
        </div>
        <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav">
                <li><a href="../../index.html">Home</a></li>
                <li><a href="../../about.html">About Me</a></li>
            </ul>
            <form class="navbar-form navbar-left" role="search">
                <div class="form-group">
                    <input type="text" id="keywords" class="form-control" placeholder="请输入关键词">
                </div>
                <button type="submit" id="submit_keywords" class="btn btn-primary">Search</button>
            </form>
            <p class="navbar-right"></p>
            <form class="navbar-form navbar-right hide" id="login_or_register">
                <a class="btn btn-primary click_to_log">登录</a>
                <a class="btn btn-success click_to_reg">注册</a>
            </form>
            <form class="navbar-form navbar-right hide" id="logout">
                <a class="btn btn-danger" onclick="logout()">退出登录</a>
            </form>
            <ul class="nav navbar-nav navbar-right hide" id="say_hello">
                <li><a href="../../home.html">Hello, <span id="username">Friend!</span>&nbsp;<span id="message_number" class="badge"></span></a></li>
            </ul>
        </div>
    </nav>
    <div id="blog-body" style="position:relative;margin:0 auto;width:90%;max-width:60em;top:60px;margin-bottom:60px">
        <!-- 内容目录begin -->
        <div class="in-page-preview pull-right">
            <a href="javascript:void(0)" title="内容目录" value=0 onclick="slideDownMenu()">
                <i class="fa fa-chevron-circle-down" style="font-size:1.5em" aria-hidden="true"></i>
            </a>
            <div class="toc-list" style="display:none" id="toc-list">
                <h3>内容目录</h3>
                <hr>
                <div class="ul-list">
                </div>
            </div>
        </div>
        <!-- 内容目录end -->
        <h1 id="blog_title" class="text-center"></h1>
        <div class="row">
            <div class="col-xs-1 col-sm-1 col-sm-offset-5" style="position:relative;left:1%;width:7em">
                <img id="author_head" src="" width="60" height="60" alt="头像加载失败" onerror="this.src='../../Public/img/default_head.jpg'">
            </div>
            <div class="col-xs-5 col-sm-5" style="width:10em">
                <span id="blog_author"></span>
                <br/>
                <span class="text-muted">阅读量：</span><span id="read_number" class="text-muted"></span>
                <br/>
                <span class="text-muted">评论数：</span><span id="comment_number" class="text-muted"></span>
            </div>
        </div>
        <p></p>
        <div class="row">
            <img style="margin:0 auto;width:90%;max-height:500px" class="img-responsive" src="" id="cover_picture" alt="图片加载失败">
        </div>
        <div class="text-center markdown-body editormd-preview-container">
EOF;
    $str2=<<<EOF
        </div>
        <hr>
        <aside class="sidebar">
            <div class="widget">
                <h4 class="title">评论区</h4>
                <div class="content">
                    <h4 class="hide" id="if_nologin"><a class="click_to_log">登录</a> / <a class="click_to_reg">注册</a>后才能发表评论</h4>
                    <div class="comments-container" id="comments-container">
                    </div>
                </div>
            </div>
        </aside>
    </div>
    <footer class="blog-footer">
        <p>Copyright <i class="fa fa-copyright" aria-hidden="true"></i><span id="year"></span></a> by <a href="https://github.com/frankie-huang">@Frankie</a>.</p>
        <p id="ICP"></p>
        <!-- <p><a href="#">Back to top</a></p> -->
    </footer>
    <script src="../../Public/js/to_log_reg_blog.js"></script>
    <script src="../../Public/js/html_code.js"></script>
    <script type="text/javascript">
        toastr.options.positionClass = 'toast-bottom-right';
        var testEditor;
    </script>
    <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="../../Public/js/ie10-viewport-bug-workaround.js"></script>
    <script src="../../Public/js/footer.js"></script>
    <script src="../../Public/js/islogin_blog.js"></script>
    <script src="../../Public/js/showMenu.js"></script>
</body>
</html>
EOF;
    return $str1.htmlspecialchars_decode($content).$str2;
}







function ret_tmp_template($blog_id)
{
    $str1=<<<EOF
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../../Public/img/frankie.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../../Public/img/frankie.ico" type="image/x-icon">

    <title>Frankie's Blog</title>

    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>

    <link href="../../Public/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <script src="../../Public/js/ie-emulation-modes-warning.js"></script>

    <link href="../../Public/css/blog.css" rel="stylesheet">
    
    <!-- toastr CSS JS -->
    <link href="https://cdn.bootcss.com/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <script src="https://cdn.bootcss.com/toastr.js/latest/toastr.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- <link rel="stylesheet" href="../../editor.md/examples/css/style.css" /> -->
    <link rel="stylesheet" href="../../editor.md/css/editormd.css" />
    <script src="../../editor.md/examples/js/zepto.min.js"></script>
    <script src="../../editor.md/editormd.js"></script>

    <!-- rewrite Markdown CSS(Cmd Markdown Style) from https://github.com/wakaryry/editorMd -->
    <link href="../../Public/css/custom.css" rel="stylesheet">  

    <!-- 防XSS注入 来自：http://jsxss.com/zh/index.html -->
    <script src="../../Public/js/xss.js"></script>
    
    <link href="../../Public/css/back_to_top.css" rel="stylesheet">
    <style>
        .in-page-preview {
            position: fixed;
            top: 60px;
            right: 2%;
            z-index: 1000;
        }
        
        .toc-list {
            position: absolute;
            float: left;
            list-style: none outside none;
            border: 1px solid #ccc;
            background-clip: padding-box;
            border-radius: 4px;
            margin: 2px 0 0;
            padding: 5px 0 10px;
            min-width: 23em;
            top: 100%;
            right: 0em;
            font-family: Microsoft Yahei, Hiragino Sans GB, WenQuanYi Micro Hei, sans-serif;
            background: #fff;
        }
        
        .toc-list h3 {
            margin: 10px 0;
            padding-left: 15px
        }
        
        .toc-list hr {
            border: 0;
            border-bottom: 1px dashed #cfcfcf;
            margin: 15px 0;
        }
        
        .toc-list ul {
            padding-left: 0px;
        }
        
        .toc-list li {
            list-style: none outside none;
            padding-left: 1.5em;
        }
        
        .toc-list a {
            white-space: nowrap;
        }
        
        .toc-list a:hover {
            background: 0 0;
            color: #005580;
            text-decoration: underline
        }
        
        .ul-list {
            overflow-x: hidden;
            overflow-y: auto;
            width: 330px;
            max-height: 400px;
            padding: 5px 0
        }
    </style>
</head>

<body>
    <script>
        var article_id = {$blog_id};
    </script>
    <nav class="navbar navbar-inverse navbar-fixed-top" id="top_nav" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
            <a class="navbar-brand" href="../../index.html">Frankie's Blog</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav">
                <li><a href="../../index.html">Home</a></li>
                <li><a href="../../about.html">About Me</a></li>
            </ul>
            <form class="navbar-form navbar-left" role="search">
                <div class="form-group">
                <input type="text" id="keywords" class="form-control" placeholder="请输入关键词">
                </div>
                <button type="submit" id="submit_keywords" class="btn btn-primary">Search</button>
            </form>
            <p class="navbar-right"></p>
            <form class="navbar-form navbar-right hide" id="login_or_register">
                <a class="btn btn-primary click_to_log">登录</a>
                <a class="btn btn-success click_to_reg">注册</a>
            </form>
            <form class="navbar-form navbar-right hide" id="logout">
                <a class="btn btn-danger" onclick="logout()">退出登录</a>
            </form>
            <ul class="nav navbar-nav navbar-right hide" id="say_hello">
                <li><a href="../../home.html">Hello, <span id="username">Friend!</span>&nbsp;<span id="message_number" class="badge"></span></a></li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </nav>
    <div id="blog-body" style="position:relative;margin:0 auto;width:90%;max-width:60em;top:60px;margin-bottom:60px">
        <!-- 内容目录begin -->
        <div class="in-page-preview pull-right">
            <a href="javascript:void(0)" title="内容目录" value=0 onclick="slideDownMenu()">
                <i class="fa fa-chevron-circle-down" style="font-size:1.5em" aria-hidden="true"></i>
            </a>
            <div class="toc-list" style="display:none" id="toc-list">
                <h3>内容目录</h3>
                <hr>
                <div class="ul-list">
                </div>
            </div>
        </div>
        <!-- 内容目录end -->
    </div>

    <footer class="blog-footer">
        <p>Copyright <i class="fa fa-copyright" aria-hidden="true"></i><span id="year"></span></a> by <a href="https://github.com/frankie-huang">@Frankie</a>.</p>
        <p id="ICP"></p>
        <!-- <p><a href="#">Back to top</a></p> -->
    </footer>
    <script src="../../Public/js/to_log_reg_blog.js"></script>
    <script src="../../Public/js/html_code.js"></script>
    <script type="text/javascript">
        toastr.options.positionClass = 'toast-bottom-right';
    </script>
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="../../Public/js/ie10-viewport-bug-workaround.js"></script>
    <script src="../../Public/js/footer.js"></script>
    <script src="../../Public/js/islogin_tmpblog.js"></script>
    <script src="../../Public/js/showMenu.js"></script>
</body>

</html>
EOF;
    return $str1;
}
