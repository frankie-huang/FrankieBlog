var ws;var u_id;var username_ws;var token;var ip;$(function(){$(document).ready(function(){$("#blog-body").css("min-height",$(window).height());$.ajax({url:"../../php/ajax.php",type:"POST",dataType:"json",data:{func:"is_login_tmpblog",article_id:article_id},success:function(e){if(e.status==0){$("#say_hello").removeClass("hide");$("#logout").removeClass("hide");$("#login_or_register").addClass("hide");$("#username").html(e.username);if(e.number>0){$("#message_number").html(e.number)}u_id=e.u_id;username_ws=e.username;token=e.token;ip=e.ip;ws=new WebSocket("ws://127.0.0.1:19910");ws.onopen=ws_open;ws.onmessage=ws_onmessage;ws.onerror=ws_onerror;ws.onclose=ws_onclose;if(e.denied===true){$("#blog-body").append($('<div class="panel panel-warning"><div class="panel-heading"><h3 class="panel-title">ERROR: </h3></div><div class="panel-body btn-danger">对不起，您走错片场了</div></div>'))}else{var s=$('<button type="button" class="btn btn-primary">修改内容</button>');s.click(function(){location.href="../../editor.html?id="+article_id});$("#blog-body").append(s);$("#blog-body").append($("<span>&nbsp;</span>"));var o=$('<button type="button" class="btn btn-primary">发布博客</button>');o.click(function(){location.href="../../submit.html?id="+article_id});if(e.is_submit===true){o.html("修改信息")}$("#blog-body").append(o);var t=$('<div class="text-center markdown-body editormd-preview-container">'+htmlDecode(e.htmlCode)+"</div>");t.appendTo($("#blog-body"));init_toc_list()}}else{$("#login_or_register").removeClass("hide");$("#logout").addClass("hide");$("#say_hello").addClass("hide");if(e.status==-2){toastr.error(e.error)}}},error:function(e){toastr.error("HTTP状态码："+e.status)}})})});function logout(){$.ajax({url:"../../php/ajax.php",type:"POST",data:{func:"logout"},dataType:"json",success:function(e){if(e.status==0){$("#login_or_register").removeClass("hide");$("#logout").addClass("hide");$("#say_hello").addClass("hide");ws.close();location.href="../../index.html"}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}ws_open=function(){console.log("连接成功");var e={status:0,string:"设置当前用户",u_id:u_id,username:username_ws,token:token,ip:ip};e=JSON.stringify(e);ws.send(e)};ws_onmessage=function(e){data=JSON.parse(e.data);if(data.status==100){console.log("身份验证成功")}else if(data.status==0){$.ajax({url:"php/ajax.php",type:"POST",data:{func:"is_login_for_ws"},dataType:"json",success:function(e){if(e.status==0){var s=$("#message_number").html();if(s=="")$("#message_number").html("1");else $("#message_number").html(parseInt(s)+1);toastr.info("You have new messages. ").click(function(){window.open("./home.html","_self")})}else{logout()}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}else if(data.status==1){toastr.success("发送成功")}else if(data.status==-100){alert("他人异地登录，请确认密码是否泄露，即将退出登录");logout()}else{toastr.error(data.error)}};ws_onerror=function(){toastr.error("websocket出错啦")};ws_onclose=function(){};function isMobile(){var e=navigator.userAgent;isAndroid=/Android/i.test(e);isBlackBerry=/BlackBerry/i.test(e);isWindowPhone=/IEMobile/i.test(e);isIOS=/iPhone|iPad|iPod/i.test(e);isMobile=isAndroid||isBlackBerry||isWindowPhone||isIOS;if(isAndroid)isMobile="android";if(isBlackBerry)isMobile="BlackBerry";if(isWindowPhone)isMobile="WindowPhone";if(isIOS)isMobile="IOS";return isMobile}