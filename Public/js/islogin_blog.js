var ws;var u_id;var username_ws;var head;var sex;var sex_chinese;var token;var ip;var author_id=0;var author_name;var title;var current_reply_id=0;var current_comment_id=0;var is_first_md_comment=true;var is_first_md_reply_to_comment=true;var is_first_md_reply_to_reply=true;var is_first_comment=true;var is_first_reply=true;$(function(){$(document).ready(function(){$("#blog-body").css("min-height",$(window).height());init_toc_list();init_comment()})});function init_comment(){$.ajax({url:"../../php/ajax.php",type:"POST",data:{func:"get_comment",article_id:article_id},dataType:"json",success:function(e){if(e.status==0){author_id=e.author_id;author_name=e.author_name;title=e.title;$("title").html(title+" - Frankie's Blog");$("#blog_title").html(title);$("#blog_author").html(author_name);$("#author_head").attr("src","../../"+e.author_head);if(e.cover!=null){$("#cover_picture").attr("src","../../"+e.cover)}else{$("#cover_picture").addClass("hide")}$("#read_number").html(e.read_time);$("#comment_number").html(e.comment_number);if(e.comment==null){$("#comments-container").append('<h3 class="text-center">来当第一个评论的人吧～</h3>')}else{var t=$('<ul id="comments-list" class="comments-list"></ul>');t.appendTo($("#comments-container"));var a=e.comment;var i=a.length;for(var r=0;r<i;r++){var o=$("<li comment-id="+a[r].id+"></li>");o.appendTo(t);var s=$('<div class="comment-main-level" comment-id='+a[r].id+"></div>");s.appendTo(o);$('<div class="comment-avatar"><img src="../../'+a[r].head+'" title="性别：'+get_sex_chinese(a[r].sex)+'" onerror="this.src=\'../../Public/img/default_head.jpg\'"></div>').appendTo(s);var n=$('<div class="comment-box" onmouseover="show_del_btn(this)" onmouseout="hide_del_btn(this)"></div>');n.appendTo(s);var d=$('<div class="comment-head" uid='+a[r].u_id+' type="comment" comment-id='+a[r].id+"></div>");d.appendTo(n);if(a[r].u_id==author_id){$('<h6 class="comment-name by-author"><a href="javascript:void(0);">'+a[r].username+"</a></h6>").appendTo(d)}else{$('<h6 class="comment-name"><a href="javascript:void(0);">'+a[r].username+"</a></h6>").appendTo(d)}$("<span>"+a[r].time+"</span>").appendTo(d);if(a[r].is_deleted==0){$('<div class="comment-content"><div class="markdown-body editormd-preview-container">'+filterXSS(html_decode(a[r].content))+"</div></div>").appendTo(n)}else{$('<div class="comment-content" style="background:#f5acac"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;此评论已被用户删除</div>').appendTo(n)}if(a[r].reply!=null){var l=$('<ul class="comments-list reply-list" comment-id='+a[r].id+"></ul>");l.appendTo(o);var m=a[r].reply;var c=m.length;for(var p=0;p<c;p++){var u=$("<li comment-id="+a[r].id+"></li>");u.appendTo(l);$('<div class="comment-avatar"><img src="../../'+m[p].head+'" title="性别：'+get_sex_chinese(m[p].sex)+'" onerror="this.src=\'../../Public/img/default_head.jpg\'"></div>').appendTo(u);var h=$('<div class="comment-box" onmouseover="show_del_btn(this)" onmouseout="hide_del_btn(this)"></div>');h.appendTo(u);var f=$('<div class="comment-head" uid='+m[p].from+' type="reply" comment-id='+a[r].id+" reply-id="+m[p].id+"></div>");f.appendTo(h);if(m[p].from==author_id){$('<h6 class="comment-name by-author"><a href="javascript:void(0);">'+m[p].username+'</a><i class="fa fa-at" style="color:#2e6da4" aria-hidden="true"></i></h6>').appendTo(f)}else{$('<h6 class="comment-name"><a href="javascript:void(0);">'+m[p].username+'</a><i class="fa fa-at" style="color:#2e6da4" aria-hidden="true"></i></h6>').appendTo(f)}if(m[p].to==author_id){$('<h6 class="comment-name by-author"><a href="javascript:void(0);">'+m[p].to_name+"</a></h6>").appendTo(f)}else{$('<h6 class="comment-name"><a href="javascript:void(0);">'+m[p].to_name+"</a></h6>").appendTo(f)}$("<span>"+m[p].time+"</span>").appendTo(f);$('<div class="comment-content"><div class="markdown-body editormd-preview-container">'+filterXSS(html_decode(m[p].content))+"</div></div>").appendTo(h)}}}}is_login()}else if(e.status==-2){$("#blog-body").html('<div class="panel panel-warning"><div class="panel-heading"><h3 class="panel-title">ERROR: </h3></div><div class="panel-body btn-danger">抱歉，这篇博客在另一个平行时空</div></div>');toastr.warning(e.error);setTimeout('location.href="../../index.html"',5e3)}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}function is_login(){$.ajax({url:"../../php/ajax.php",type:"POST",dataType:"json",data:{func:"is_login"},success:function(e){if(e.status==0){$("#say_hello").removeClass("hide");$("#logout").removeClass("hide");$("#login_or_register").addClass("hide");$("#username").html(e.username);if(e.number>0){$("#message_number").html(e.number)}u_id=e.u_id;username_ws=e.username;head=e.head;sex=e.sex;sex_chinese=get_sex_chinese(sex);token=e.token;ip=e.ip;ws=new WebSocket("ws://127.0.0.1:19910");ws.onopen=ws_open;ws.onmessage=ws_onmessage;ws.onerror=ws_onerror;ws.onclose=ws_onclose;var t=$('<div id="layout"></div>');$("#comments-container").before(t);$('<div class="editormd hide" id="test-editormd"><textarea></textarea></div>').appendTo(t);$('<div id="textarea_editor" class="form-control"></div>').appendTo(t);$('<button type="button" value=1 class="btn btn-primary switch_editor_comment" onclick="switch_editor_comment(this)">USE MD</button>').appendTo(t);$('<button type="button" class="btn btn-lg btn-primary pull-right" onclick="comment(this)">评论</button>').appendTo(t);$("#textarea_editor").summernote({height:300,minHeight:null,maxHeight:null,focus:false,callbacks:{onImageUpload:function(e){var t=e[0].size;if(t>1024e4){toastr.error("图片大小不能超过10MB");return}var a=this;var i=new FormData;i.append("editormd-image-file",e[0]);$.ajax({url:"../../editor.md/examples/php/upload.php",type:"POST",data:i,dataType:"json",cache:false,contentType:false,processData:false,success:function(e){if(e.success==1){$("#"+a.id).summernote("insertImage",e.url)}else{toastr.warning(e.message)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}}});$(".note-image-input").attr("multiple",null);$("div.comment-head").append($('<i class="fa fa-reply" title="回复" onclick="click_to_reply(this)"></i>'));$("div.comment-head[uid="+u_id+'][type="comment"]').append($('<i class="fa fa-trash hide" type="comment" uid='+e.u_id+' title="删除" onclick="click_to_del(this)"></i>'));$("div.comment-head[uid="+u_id+'][type="reply"]').append($('<i class="fa fa-trash hide" type="reply" uid='+e.u_id+' title="删除" onclick="click_to_del(this)"></i>'))}else{$("#login_or_register").removeClass("hide");$("#logout").addClass("hide");$("#say_hello").addClass("hide");$("#if_nologin").removeClass("hide")}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}function comment(e){e=$(e);var t=e.prev(":first").attr("value");if(t==0){var n=$("#test-editormd").find(".editormd-preview-container").eq(0).html()}else{var n=$("#textarea_editor").summernote("code")}if(n==""||/^\s+$/.test(n)){$("#textarea_editor").summernote("focus");toastr.warning("您并没有输入任何有效的内容");return}$.ajax({url:"../../php/ajax.php",type:"POST",data:{func:"comment",article_id:article_id,content:n},dataType:"json",success:function(e){if(e.status==0){$("#test-editormd").find(".editormd-preview-container").eq(0).html("");if(testEditor!=undefined){testEditor.setMarkdown("")}$("#textarea_editor").summernote("code","");if($("#comments-list").length==0){$("#comments-container").html("");$('<ul id="comments-list" class="comments-list"></ul>').appendTo($("#comments-container"))}var t=$("<li comment-id="+e.id+"></li>");t.appendTo($("#comments-list"));var a=$('<div class="comment-main-level" comment-id="comment_id"></div>');a.appendTo(t);$('<div class="comment-avatar"><img src="../../'+head+'" title="性别：'+sex_chinese+'" onerror="this.src=\'../../Public/img/default_head.jpg\'"></div>').appendTo(a);var i=$('<div class="comment-box" onmouseover="show_del_btn(this)" onmouseout="hide_del_btn(this)"></div>');i.appendTo(a);var r=$('<div class="comment-head" uid='+u_id+' type="comment" comment-id='+e.id+"></div>");r.appendTo(i);if(u_id==author_id){$('<h6 class="comment-name by-author"><a href="javascript:void(0);">'+author_name+"</a></h6>").appendTo(r)}else{$('<h6 class="comment-name"><a href="javascript:void(0);">'+username_ws+"</a></h6>").appendTo(r)}$("<span>"+e.time+"</span>").appendTo(r);$('<i class="fa fa-reply" title="回复" onclick="click_to_reply(this)"></i>').appendTo(r);$('<i class="fa fa-trash hide" type="comment" uid='+u_id+' title="删除" onclick="click_to_del(this)"></i>').appendTo(r);$('<div class="comment-content"><div class="markdown-body editormd-preview-container">'+n+"</div></div>").appendTo(i);if(u_id!=author_id){var o='<i class="fa fa-bell" aria-hidden="true"></i>&nbsp;我评论了你的博客<a href="./blog/'+article_id+'/" target="_blank">'+title+"</a>";var s={status:1,string:"评论博客提示",u_id:u_id,content:o,object:author_id,from_username:username_ws};s=JSON.stringify(s);ws_send(s)}$("#comment_number").html(parseInt($("#comment_number").html())+1);toastr.success("评论成功")}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}function show_del_btn(e){e=$(e);e.find("i.fa-trash").removeClass("hide")}function hide_del_btn(e){e=$(e);e.find("i.fa-trash").addClass("hide")}function click_to_reply(e){e=$(e);var t=e.parent(":first");var a=t.parent(":first").parent(":first");var i=t.attr("comment-id");var r=t.attr("type");var o=t.attr("uid");var s=t.children(":first").children(":first").html();if(r=="comment"){if(i==current_comment_id){$("#layout-comment").removeClass("hide");return}$("#layout-reply").addClass("hide");if(is_first_comment){var n=$('<div id="layout-comment"></div>');a.after(n);$('<h4 style="background:#FCFCFC" class="comment-name">reply to <a href="javascript:void(0);">'+s+"</a></h4>").appendTo(n);$('<div class="editormd hide" id="editormd-comment"><textarea></textarea></div>').appendTo(n);$('<div id="textarea_comment_editor" class="form-control"></div>').appendTo(n);$('<button type="button" my-type="comment" class="btn btn-primary" style="float:right;z-index:1" comment-id='+i+" to="+o+' to-name="'+s+'" onclick="reply(this)">回复</button>').appendTo(n);$('<a href="javascript:void(0);" my-type="comment" class="btn" style="float:right;z-index:1" onclick="cancel_reply(this)">取消</a>').appendTo(n);$('<button type="button" value=1 my-type="comment" class="btn btn-primary switch_editor" onclick="switch_editor(this)">USE MD</button>').appendTo(n);$("#textarea_comment_editor").summernote({height:100,minHeight:null,maxHeight:null,focus:true,callbacks:{onImageUpload:function(e){var t=e[0].size;if(t>1024e4){toastr.error("图片大小不能超过10MB");return}var a=this;var i=new FormData;i.append("editormd-image-file",e[0]);$.ajax({url:"../../editor.md/examples/php/upload.php",type:"POST",data:i,dataType:"json",cache:false,contentType:false,processData:false,success:function(e){if(e.success==1){$("#"+a.id).summernote("insertImage",e.url)}else{toastr.warning(e.message)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}}});$(".note-image-input").attr("multiple",null);is_first_comment=false}else{var n=$("#layout-comment");n.removeClass("hide");$("#textarea_comment_editor").summernote("code","");if(!is_first_md_reply_to_comment){$("#editormd-comment").find(".editormd-preview-container").eq(0).html("");editormd_comment.setMarkdown("")}n.children(":first").children(":first").html(s);n.children().eq(4).attr({"comment-id":i,to:o,"to-name":s});a.after(n)}$("#textarea_comment_editor").summernote("focus");current_comment_id=i;current_reply_id=0}else{var d=t.attr("reply-id");if(d==current_reply_id){$("#layout-reply").removeClass("hide");return}$("#layout-comment").addClass("hide");if(is_first_reply){var l=$('<div id="layout-reply"></div>');a.after(l);$('<h4 style="background:#FCFCFC" class="comment-name">reply to <a href="javascript:void(0);">'+s+"</a></h4>").appendTo(l);$('<div class="editormd hide" id="editormd-reply"><textarea></textarea></div>').appendTo(l);$('<div id="textarea_reply_editor" class="form-control"></div>').appendTo(l);$('<button type="button" my-type="reply" class="btn btn-primary" style="float:right;z-index:1" comment-id='+i+" to="+o+' to-name="'+s+'" onclick="reply(this)">回复</button>').appendTo(l);$('<a href="javascript:void(0);" my-type="reply" class="btn" style="float:right;z-index:1" onclick="cancel_reply(this)">取消</a>').appendTo(l);$('<button type="button" value=1 my-type="reply" class="btn btn-primary switch_editor" onclick="switch_editor(this)">USE MD</button>').appendTo(l);$("#textarea_reply_editor").summernote({height:100,minHeight:null,maxHeight:null,focus:true,callbacks:{onImageUpload:function(e){var t=e[0].size;if(t>1024e4){toastr.error("图片大小不能超过10MB");return}var a=this;var i=new FormData;i.append("editormd-image-file",e[0]);$.ajax({url:"../../editor.md/examples/php/upload.php",type:"POST",data:i,dataType:"json",cache:false,contentType:false,processData:false,success:function(e){if(e.success==1){$("#"+a.id).summernote("insertImage",e.url)}else{toastr.warning(e.message)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}}});$(".note-image-input").attr("multiple",null);is_first_reply=false}else{var l=$("#layout-reply");l.removeClass("hide");$("#textarea_reply_editor").summernote("code","");if(!is_first_md_reply_to_reply){$("#editormd-reply").find(".editormd-preview-container").eq(0).html("");editormd_reply.setMarkdown("")}l.children(":first").children(":first").html(s);l.children().eq(4).attr({"comment-id":i,to:o,"to-name":s});a.after(l)}$("#textarea_reply_editor").summernote("focus");current_reply_id=d;current_comment_id=0}}function click_to_del(i){var e=confirm("确认删除这条评论吗");if(e==false){return}i=$(i);var r=i.attr("type");var o=i.parent(":first").attr(r+"-id");var t={func:"delete_"+r};if(r=="comment"){t.comment_id=o}else{t.reply_id=o}$.ajax({url:"../../php/ajax.php",type:"POST",data:t,dataType:"json",success:function(e){if(e.status==0){if(r=="comment"){if(e.no_reply){$("li[comment-id="+o+"]").addClass("hide");$("#comment_number").html(parseInt($("#comment_number").html())-1)}else{var t=i.parent(":first").next(":first");t.css("background","#f5acac");t.html('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;此评论已被用户删除');if($("div.comment-main-level[comment-id="+o+"]").eq(0).next(":first").attr("id")=="layout-comment"){$("#layout-comment").addClass("hide")}}}else{if(e.comment_is_deleted&&e.no_reply){$("li[comment-id="+i.parent(":first").attr("comment-id")+"]").eq(0).addClass("hide");$("#comment_number").html(parseInt($("#comment_number").html())-1)}else{var a=i.parent(":first").parent(":first").parent(":first");a.addClass("hide");if(a.next(":first").attr("id")=="layout-reply"){$("#layout-reply").addClass("hide")}}}}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}function cancel_reply(e){$("#layout-"+$(e).attr("my-type")).addClass("hide")}function reply(e){e=$(e);var r=e.attr("comment-id");var o=e.attr("to");var s=e.attr("to-name");var n=e.attr("my-type");var d=e.next(":first").next(":first").attr("value");if(d==0){var l=$("#editormd-"+n).find(".editormd-preview-container").eq(0).html()}else{var l=$("#textarea_"+n+"_editor").summernote("code")}if(l==""||/^\s+$/.test(l)){$("#textarea_"+n+"_editor").focus();toastr.warning("您并没有输入任何有效的内容");return}$.ajax({url:"../../php/ajax.php",type:"POST",data:{func:"reply",content:l,comment_id:r,to:o},dataType:"json",success:function(e){if(e.status==0){if($("ul.reply-list[comment-id="+r+"]").eq(0).length==0){var t=$('<ul class="comments-list reply-list" comment-id='+r+"></ul>");t.appendTo($("li[comment-id="+r+"]").eq(0));add_reply(t,r,e.id,o,s,e.time,l,d)}else{add_reply($("ul.reply-list[comment-id="+r+"]").eq(0),r,e.id,o,s,e.time,l,d)}if(d==0){$("#editormd-"+n).find(".editormd-preview-container").eq(0).html("");if(n=="comment"){editormd_comment.setMarkdown("")}else{editormd_reply.setMarkdown("")}}else{$("#textarea_"+n+"_editor").val("")}$("#layout-"+n).addClass("hide");if(u_id!=o){var a='<i class="fa fa-bell" aria-hidden="true"></i>&nbsp;我在博客<a href="./blog/'+article_id+'/" target="_blank">'+title+"</a>中回复了你";var i={status:1,string:"回复博客提示",u_id:u_id,content:a,object:o,from_username:username_ws};i=JSON.stringify(i);ws_send(i)}toastr.success("回复成功")}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}function logout(){$.ajax({url:"../../php/ajax.php",type:"POST",data:{func:"logout"},dataType:"json",success:function(e){if(e.status==0){$("#login_or_register").removeClass("hide");$("#logout").addClass("hide");$("#say_hello").addClass("hide");$("#if_nologin").removeClass("hide");$("#layout").addClass("hide");$('i.fa-reply[title="回复"]').remove();$("i.fa-trash[uid="+u_id+"]").remove();ws.close()}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}ws_open=function(){console.log("连接成功");var e={status:0,string:"设置当前用户",u_id:u_id,username:username_ws,token:token,ip:ip};e=JSON.stringify(e);ws.send(e)};ws_onmessage=function(e){data=JSON.parse(e.data);if(data.status==100){console.log("身份验证成功")}else if(data.status==0){$.ajax({url:"../../php/ajax.php",type:"POST",data:{func:"is_login_for_ws"},dataType:"json",success:function(e){if(e.status==0){var t=$("#message_number").html();if(t=="")$("#message_number").html("1");else $("#message_number").html(parseInt(t)+1);toastr.info("You have new messages. ").click(function(){window.open("../../home.html","_self")})}else{logout()}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}else if(data.status==1){}else if(data.status==-100){alert("他人异地登录，请确认密码是否泄露，即将退出登录");logout()}else{toastr.error(data.error)}};ws_onerror=function(){toastr.error("websocket出错啦")};ws_onclose=function(){};function add_reply(e,t,a,i,r,o,s,n){var d=$("<li></li>");d.appendTo(e);$('<div class="comment-avatar"><img src="../../'+head+'" onerror="this.src=\'../../Public/img/default_head.jpg\'"></div>').appendTo(d);var l=$('<div class="comment-box" onmouseover="show_del_btn(this)" onmouseout="hide_del_btn(this)"></div>');l.appendTo(d);var m=$('<div class="comment-head" uid='+u_id+' type="reply" comment-id='+t+" reply-id="+a+"></div>");m.appendTo(l);if(u_id==author_id){$('<h6 class="comment-name by-author"><a href="javascript:void(0);">'+author_name+'</a><i class="fa fa-at" style="color:#2e6da4" aria-hidden="true"></i></h6>').appendTo(m)}else{$('<h6 class="comment-name"><a href="javascript:void(0);">'+username_ws+'</a><i class="fa fa-at" style="color:#2e6da4" aria-hidden="true"></i></h6>').appendTo(m)}if(i==author_id){$('<h6 class="comment-name by-author"><a href="javascript:void(0);">'+r+"</a></h6>").appendTo(m)}else{$('<h6 class="comment-name"><a href="javascript:void(0);">'+r+"</a></h6>").appendTo(m)}$("<span>"+o+"</span>").appendTo(m);$('<i class="fa fa-reply" title="回复" onclick="click_to_reply(this)"></i>').appendTo(m);$('<i class="fa fa-trash hide" type="reply" uid='+u_id+' title="删除" onclick="click_to_del(this)"></i>').appendTo(m);$('<div class="comment-content"><div class="markdown-body editormd-preview-container">'+s+"</div></div>").appendTo(l)}function switch_editor(e){e=$(e);var t=e.parent(":first");var a=e.attr("my-type");if(e.attr("value")==0){t.children().eq(1).addClass("hide");t.children().eq(3).removeClass("hide");t.children().eq(2).summernote("focus");e.attr("value",1);e.html("USE MD")}else{if(a=="comment"){if(is_first_md_reply_to_comment){editormd_comment=editormd("editormd-comment",{width:"100%",height:400,path:"../../editor.md/lib/",theme:"default",previewTheme:"default",editorTheme:"base16-light",codeFold:true,saveHTMLToTextarea:true,searchReplace:true,htmlDecode:"style,script,iframe|on*",emoji:true,taskList:true,tocm:true,tex:true,flowChart:true,sequenceDiagram:true,imageUpload:true,imageFormats:["jpg","jpeg","pjpeg","ico","gif","png","bmp","webp"],imageUploadURL:"../../editor.md/examples/php/upload.php",onload:function(){console.log("onload",this)}});is_first_md_reply_to_comment=false}}else{if(is_first_md_reply_to_reply){editormd_reply=editormd("editormd-reply",{width:"100%",height:400,path:"../../editor.md/lib/",theme:"default",previewTheme:"default",editorTheme:"base16-light",codeFold:true,saveHTMLToTextarea:true,searchReplace:true,htmlDecode:"style,script,iframe|on*",emoji:true,taskList:true,tocm:true,tex:true,flowChart:true,sequenceDiagram:true,imageUpload:true,imageFormats:["jpg","jpeg","pjpeg","ico","gif","png","bmp","webp"],imageUploadURL:"../../editor.md/examples/php/upload.php",onload:function(){console.log("onload",this)}});is_first_md_reply_to_reply=false}}t.children().eq(1).removeClass("hide");t.children().eq(3).addClass("hide");e.attr("value",0);e.html("USE TEXTAREA")}}function switch_editor_comment(e){e=$(e);var t=e.parent(":first");if(e.attr("value")==0){t.children().eq(0).addClass("hide");t.children().eq(2).removeClass("hide");t.children().eq(1).summernote("focus");e.attr("value",1);e.html("USE MD")}else{if(is_first_md_comment){testEditor=editormd("test-editormd",{width:"100%",height:400,path:"../../editor.md/lib/",theme:"default",previewTheme:"default",editorTheme:"base16-light",codeFold:true,saveHTMLToTextarea:true,searchReplace:true,htmlDecode:"style,script,iframe|on*",emoji:true,taskList:true,tocm:true,tex:true,flowChart:true,sequenceDiagram:true,imageUpload:true,imageFormats:["jpg","jpeg","pjpeg","ico","gif","png","bmp","webp"],imageUploadURL:"../../editor.md/examples/php/upload.php",onload:function(){console.log("onload",this)}});is_first_md_comment=false}t.children().eq(0).removeClass("hide");t.children().eq(2).addClass("hide");e.attr("value",0);e.html("USE TEXTAREA")}}function isMobile(){var e=navigator.userAgent;isAndroid=/Android/i.test(e);isBlackBerry=/BlackBerry/i.test(e);isWindowPhone=/IEMobile/i.test(e);isIOS=/iPhone|iPad|iPod/i.test(e);isMobile=isAndroid||isBlackBerry||isWindowPhone||isIOS;if(isAndroid)isMobile="android";if(isBlackBerry)isMobile="BlackBerry";if(isWindowPhone)isMobile="WindowPhone";if(isIOS)isMobile="IOS";return isMobile}function get_sex_chinese(e){if(e==1){return"男"}else if(e==2){return"女"}else{return"未知"}}function html_decode(e){var t="";if(e.length==0)return"";t=e.replace(/&lt;/g,"<");t=t.replace(/&gt;/g,">");t=t.replace(/&nbsp;/g," ");t=t.replace(/&#39;/g,"'");t=t.replace(/&quot;/g,'"');t=t.replace(/&amp;/g,"&");return t}function ws_send(t){$.ajax({url:"../../php/ajax.php",type:"POST",data:{func:"is_login_for_ws"},dataType:"json",success:function(e){if(e.status==0){ws.send(t)}else{logout()}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}