var page=1;var display_number=10;$(function(){$(document).ready(function(){if(isMobile()){$("img.Editor_picture").eq(0).attr("src","Public/img/Carousel/mEditorMd.png");$("img.Bootstrap_picture").eq(0).attr("src","Public/img/Carousel/mBootstrap.png");$("img.MeepoPS_picture").eq(0).attr("src","Public/img/Carousel/mMeepoPS.png");$("#display_number").css({position:"relative",left:"36%",width:"28%"})}else{$("img.Editor_picture").eq(0).attr("src","Public/img/Carousel/EditorMd.png");$("img.Bootstrap_picture").eq(0).attr("src","Public/img/Carousel/Bootstrap.png");$("img.MeepoPS_picture").eq(0).attr("src","Public/img/Carousel/MeepoPS.png")}init_blog()})});function init_blog(){if(Request["p"]!=undefined){if(Request["p"]<1||isNaN(Request["p"])){}else{page=Request["p"]}}if(Request["n"]!=undefined){if(Request["n"]<1||isNaN(Request["n"])){}else{display_number=Request["n"]}}get_blogs()}function get_blogs(){$.ajax({url:"php/ajax.php",type:"POST",data:{func:"get_article_list",page:page,display_number:display_number},dataType:"json",success:function(e){if(e.status==0){$("#current_page").html(page);$("#total_pages").html(e.total_pages);var a=$("#change_page");a.html("");for(var t=1;t<=e.total_pages;t++){$("<option value="+t+">"+t+"</option>").appendTo(a)}a.children().eq(page-1).attr("selected",true);if(page>=e.total_pages){$("#next_page").addClass("hide")}else{$("#next_page").removeClass("hide")}show_blogs(e.blogs)}else{toastr.warning(e.error)}},error:function(e){toastr.error("HTTP状态码："+e.status)}})}function show_blogs(e){var a=$("#div_show_blogs");a.html("");if(e==null){$('<p class="text-center" style="font-size:2em">抱歉，没有更多内容了～</p><p>&nbsp;</p>').appendTo(a);$("#form_display_number").addClass("hide");$("#form_show_page").addClass("hide")}else{var t=e.length;for(var s=0;s<t;s++){var i=$("<article id="+e[s].article_id+' class="post"></article>');i.appendTo(a);var r=$('<div class="post-head"></div>');r.appendTo(i);$('<h1 class="post-title"><a href="./blog/'+e[s].article_id+'/">'+e[s].title+"</a></h1>").appendTo(r);var p=$('<div class="post-meta"></div>');p.appendTo(r);$('<span class="author">作者：<a href="javascript:void(0);">'+e[s].author+"</a></span> &bull;").appendTo(p);$('<time class="post-date" datetime="" title="">'+e[s].published_time+"</time>").appendTo(p);if(e[s].cover!=null){var o=$('<div class="featured-media"></div>');o.appendTo(i);$('<a href="./blog/'+e[s].article_id+'/"><img src="'+e[s].cover+'" alt="'+e[s].title+'" onerror="this.src=\'Public/img/error.jpg\'"></a>').appendTo(o)}if(e[s].summary!=null){var l=$('<div class="post-content"></div>');l.appendTo(i);$("<p>"+e[s].summary+"</p>").appendTo(l)}$('<div class="post-permalink"><a href="./blog/'+e[s].article_id+'/" class="btn btn-default">阅读全文</a></div>').appendTo(i);var n=$('<footer class="post-footer clearfix"></footer>');n.appendTo(i);var d=$('<div class="pull-left tag-list"></div>');d.appendTo(n);$('<i class="fa fa-folder-open-o"></i>').appendTo(d);if(e[s].tags!=null){var g=e[s].tags;var c=g.length;for(var u=0;u<c;u++){$('<a href="./search.html?tag='+g[u].tag_id+'">'+g[u].label+"</a>&nbsp;").appendTo(d)}}}}}$("#submit_display_number").click(function(e){e.preventDefault();var a=$("#display_number").val();if(a<1||isNaN(a)||!isInteger(a)){toastr.warning("请输入大于1的整数");$("#display_number").focus();return}display_number=a;page=1;get_blogs()});$("#next_page").click(function(){page=parseInt(page)+1;get_blogs()});$("#change_page").change(function(){page=$("#change_page").val();get_blogs()});function isInteger(e){return e%1===0}