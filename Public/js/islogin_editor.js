var ws;var u_id;var username_ws;var token;var ip;var is_published=false;var is_manager=false;$(function(){$(document).ready(function(){$("a.editormd-preview-close-btn").eq(0).css("display","none");var e={};e.func="is_login_editor";e.userAgent=navigator.userAgent;if(Request["id"]!=undefined){if(Request["id"]<1||isNaN(Request["id"])){}else{e.id=Request["id"]}}$.ajax({url:"php/ajax.php",type:"POST",dataType:"json",data:e,success:function(e){if(e.status==0){$("#say_hello").removeClass("hide");$("#logout").removeClass("hide");$("#login_or_register").addClass("hide");$("#username").html(e.username);if(e.number>0){$("#message_number").html(e.number)}var s=$("#submit");s.attr("value",0);if(e.weight==0){s.html("提交");s.attr("disabled",false)}else if(e.weight>0){s.html("发布");s.attr("disabled",false)}u_id=e.u_id;username_ws=e.username;token=e.token;ip=e.ip;if(e.weight>2){is_manager=true}ws=new WebSocket("ws://127.0.0.1:19910");ws.onopen=ws_open;ws.onmessage=ws_onmessage;ws.onerror=ws_onerror;ws.onclose=ws_onclose;if(e.mdCode!=undefined){if(e.is_published!=undefined&&e.is_published==true){is_published=true}$("#mdtextarea").html(e.mdCode);s.attr("value",1);s.html("更新")}}else{$("#login_or_register").removeClass("hide");$("#logout").addClass("hide");$("#say_hello").addClass("hide");toastr.warning("请注意，当前处于未登录状态，您在本页面所写内容将无法被保存！")}},error:function(e){toastr.error("HTTP状态码："+e.status)}})})});function logout(){var e=confirm("退出登录后，您在本页面所写内容将无法被保存！确认退出登录吗？");if(e==false){return}$.ajax({url:"php/ajax.php",type:"POST",data:{func:"logout"},dataType:"json",success:function(e){if(e.status==0){$("#login_or_register").removeClass("hide");$("#logout").addClass("hide");$("#say_hello").addClass("hide");$("#submit").attr("disabled",true);$("#submit").html("请先登录");toastr.warning("请注意，当前处于未登录状态，您在本页面所写内容将无法被保存！");ws.close()}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}ws_open=function(){console.log("连接成功");var e={status:0,string:"设置当前用户",u_id:u_id,username:username_ws,token:token,ip:ip};e=JSON.stringify(e);ws.send(e)};ws_onmessage=function(e){data=JSON.parse(e.data);if(data.status==100){console.log("身份验证成功")}else if(data.status==0){$.ajax({url:"php/ajax.php",type:"POST",data:{func:"is_login_for_ws"},dataType:"json",success:function(e){if(e.status==0){var s=$("#message_number").html();if(s=="")$("#message_number").html("1");else $("#message_number").html(parseInt(s)+1);toastr.info("You have new messages. ").click(function(){var e=testEditor.getMarkdown();if(e==""||/^\s+$/.test(e)){window.open("./home.html","_self")}else{var s=confirm("系统不会保留所写内容，是否确认离开？");if(s){window.open("./home.html","_self")}else{return}}})}else{logout()}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}else if(data.status==1){toastr.success("发送成功")}else if(data.status==-100){alert("他人异地登录，请确认密码是否泄露，即将退出登录");logout()}else{toastr.error(data.error)}};ws_onerror=function(){toastr.error("websocket出错啦")};ws_onclose=function(){};$("#submit").on("click",function(){var e=document.getElementsByClassName("editormd-preview-container")[0].innerHTML;var s=testEditor.getMarkdown();if(s==""||/^\s+$/.test(s)){toastr.warning("您并未输入任何内容");return}if($(this).attr("value")==1){if(is_published&&!is_manager){var t=confirm("更新博客内容后，需要管理员审核后才能生效，确认修改吗？");if(t==false)return}$.ajax({url:"php/ajax.php",type:"POST",dataType:"json",data:{func:"update_article",id:Request["id"],mdCode:s,htmlCode:e},success:function(e){if(e.status==0){if(e.is_submit==true){toastr.success("更新成功").click(function(){location.href="./myblog/"+Request["id"]+"/"});setTimeout('location.href="./myblog/'+Request["id"]+'/"',1e3)}else{toastr.success("更新成功").click(function(){location.href="./submit.html?id="+Request["id"]});setTimeout('location.href="./submit.html?id='+Request["id"],1e3)}}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}else{$.ajax({url:"php/ajax.php",type:"POST",dataType:"json",data:{func:"submit_article",mdCode:s,htmlCode:e},success:function(e){if(e.status==0){location.href="./submit.html?id="+e.id}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}});