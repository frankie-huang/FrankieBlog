var ws;var u_id;var username_ws;var token;var ip;$(function(){$(document).ready(function(){$.ajax({url:"php/ajax.php",type:"POST",dataType:"json",data:{func:"is_login"},success:function(s){if(s.status==0){$("#say_hello").removeClass("hide");$("#logout").removeClass("hide");$("#login_or_register").addClass("hide");$("#username").html(s.username);if(s.number>0){$("#message_number").html(s.number)}u_id=s.u_id;username_ws=s.username;token=s.token;ip=s.ip;ws=new WebSocket("ws://127.0.0.1:19910");ws.onopen=ws_open;ws.onmessage=ws_onmessage;ws.onerror=ws_onerror;ws.onclose=ws_onclose;if(typeof do_other_thing_islogin=="function"){do_other_thing_islogin()}}else{$("#login_or_register").removeClass("hide");$("#logout").addClass("hide");$("#say_hello").addClass("hide")}},error:function(s){toastr.error("HTTP状态码："+s.status)}})})});function logout(){$.ajax({url:"php/ajax.php",type:"POST",data:{func:"logout"},dataType:"json",success:function(s){if(s.status==0){$("#login_or_register").removeClass("hide");$("#logout").addClass("hide");$("#say_hello").addClass("hide");ws.close();if(typeof do_other_thing_logout=="function"){do_other_thing_logout()}}else{toastr.warning(s.error)}},error:function(s){toastr.error("HTTP状态码："+s.status)}})}ws_open=function(){console.log("连接成功");var s={status:0,string:"设置当前用户",u_id:u_id,username:username_ws,token:token,ip:ip};s=JSON.stringify(s);ws.send(s)};ws_onmessage=function(s){data=JSON.parse(s.data);if(data.status==100){console.log("身份验证成功")}else if(data.status==0){$.ajax({url:"php/ajax.php",type:"POST",data:{func:"is_login_for_ws"},dataType:"json",success:function(s){if(s.status==0){var e=$("#message_number").html();if(e=="")$("#message_number").html("1");else $("#message_number").html(parseInt(e)+1);toastr.info("You have new messages. ").click(function(){window.open("./home.html","_self")})}else{logout()}},error:function(s){toastr.error("HTTP状态码："+s.status)}})}else if(data.status==1){toastr.success("发送成功")}else if(data.status==-100){alert("他人异地登录，请确认密码是否泄露，即将退出登录");logout()}else{toastr.error(data.error)}};ws_onerror=function(){toastr.error("websocket出错啦")};ws_onclose=function(){};function isMobile(){var s=navigator.userAgent;isAndroid=/Android/i.test(s);isBlackBerry=/BlackBerry/i.test(s);isWindowPhone=/IEMobile/i.test(s);isIOS=/iPhone|iPad|iPod/i.test(s);isMobile=isAndroid||isBlackBerry||isWindowPhone||isIOS;if(isAndroid)isMobile="android";if(isBlackBerry)isMobile="BlackBerry";if(isWindowPhone)isMobile="WindowPhone";if(isIOS)isMobile="IOS";return isMobile}function ws_send(s){$.ajax({url:"php/ajax.php",type:"POST",data:{func:"is_login_for_ws"},dataType:"json",success:function(e){if(e.status==0){ws.send(s)}else{logout()}},error:function(s){toastr.error("HTTP状态码："+s.status)}})}