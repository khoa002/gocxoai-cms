<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"]) OR !isset($_REQUEST["id"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
$user = new User($_SESSION["user"]);
$user->set_last_seen("view_discussion:{$_REQUEST["id"]}");
$post = new QuickDiscussion();
$post->load($_REQUEST["id"]);
if (!$post->id) die();

$post->mark_read();

require_once("{$_SESSION["root_path"]}/page_top.php");

// add a column to the quick_discussion_read_status table if doesn't exist
mysql_add_column("quick_discussions_read_status",$user->username,"DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00'");
?>
<div style="text-align: center; margin: 10px 0;">
	<span style="display: inline-block; vertical-align: middle;"><?php if ($post->parent_id > 0): ?><img src="files/site_images/layout/discussion-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;">&nbsp;<a onClick="load_page('view_single_discussion.php?id=<?php echo $post->parent_id; ?>');"><?php echo translate("Return to main discussion","Trở về thảo luận chính"); ?></a></span><?php else: ?><img src="files/site_images/layout/discussion-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;">&nbsp;<a onClick="load_page('discussions.php');"><?php echo translate("Return to discussions list","Trở về danh sách thảo luận"); ?></a></span><?php endif; ?></span>
</div>
<script type="text/javascript">
var loading_frame = $("#loading_frame");
var loading_frame_speed = 500;
var attachments = 0;
$(function(){
	// setting the page title
	top.document.title = "<?php echo (empty($post->title) ? translate("Post #","Thảo luận #").$post->id : $post->title); ?>";
	
	<?php if (!isMobile()): ?>
	$("#quick_msg").tinymce({
		script_url : 'scripts/tinymce/jscripts/tiny_mce/tiny_mce.js',
		language: "<?php echo $_SESSION["language"]; ?>",
		width: "100%",
		height: "150",
		theme : "advanced",
		skin : "o2k7",
		skin_variant : "silver",
		plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,cleanup,code,insertdate,inserttime,|,forecolor,backcolor,hr,sub,sup",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "justify",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false
	});
	<?php endif; ?>
	$("#quick_discussion").submit(function(e){
		e.preventDefault();
		if ( (!$.trim($("#quick_msg").val())) ){
			alert("<?php echo translate("You didn't say anything!",ucfirst($user->you)." chưa nói gì hết!"); ?>");
			return false;
		}
		$("#qd_submit").hide();
		if (window.attachments > 0){ $('#attachment_upload_control').swfupload('startUpload'); }
		else { window.submitQD_Form(); }
	});
	$('#smilies_area_body').load('show/qd_emoticons.php');
	
	$("#comments_area").load("pages/view_comments.php?id=<?php echo $post->id; ?>",function(){ $("#comments_loading").fadeOut(); });
});
function submitQD_Form(){
	$.post("/actions/quick_discussion.php?session=<?php echo session_id(); ?>", $("#quick_discussion").serialize(), function (data){
		if (data == "error"){
			alert("<?php echo translate("Sorry, something went wrong, please let the admin know.","Xin lổi, có vấn đề gì đó, xin cho quản trị viên biết."); ?>");
			$("#qd_submit").show();
			return false;
		}
		loading_frame.fadeIn(loading_frame_speed);
		load_page("view_single_discussion.php?id=<?php echo $post->id; ?>");
	});
}
</script>
<div class="wrap" style="margin: 5px 0; clear: both; text-align: left;">
	<?php
	echo "<span id=\"discussion{$post->id}category\" style=\"margin: 0 2px; font-size: 0.8em;\"><img src=\"files/site_images/layout/categories-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;".get_qd_category_desc($post->category)."</span></span>";
	
	echo "<span id=\"discussion{$post->id}author\" style=\"margin: 0 2px; font-size: 0.8em;\"><img src=\"files/site_images/layout/user-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;<a onClick=\"load_page('profile.php?who={$post->qd_author->username}');\">{$post->qd_author->name}</a></span></span>";

	echo "<span id=\"discussion{$post->id}created\" style=\"margin: 0 2px; font-size: 0.8em;\"><img src=\"files/site_images/layout/calendar-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;".translate("Posted on ","Đăng vào ")."<strong>".(date_diff_short($post->created) == "0" ? translate("just now","mới đây") : (date_diff_short($post->created) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($post->created)." ".translate("ago","trước")))."</strong></span></span>";
	
	if (!empty($post->edited) AND ($post->edited != "1970-01-01 00:00:00")) echo "<span id=\"discussion{$post->id}edited\" style=\"margin: 0 2px; font-size: 0.8em;\"><img src=\"files/site_images/layout/calendar2-16.png\" style=\"vertical-align: middle;\"/>".($post->author != $post->editor ? "<span style=\"vertical-align: middle;\">&nbsp;".translate("Edited by ","Soạn lại bởi ")."<strong>{$post->qd_editor->name}</strong>".translate(" about "," khoảng ")."<strong>".(date_diff_short($post->edited) == "0" ? translate("just now","mới đây") : (date_diff_short($post->edited) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($post->edited)." ".translate("ago","trước")))."</strong></span>" : "<span style=\"vertical-align: middle;\">&nbsp;".translate("Edited ","Soạn lại ").translate(" about "," khoảng ")."<strong>".(date_diff_short($post->edited) == "0" ? translate("just now","mới đây") : (date_diff_short($post->edited) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($post->edited)." ".translate("ago","trước")))."</strong></span>")."</span>";
	
	$numComments = $post->comment_count;
	echo "<span id=\"discussion{$post->id}numberofcomments\" style=\"margin: 0 2px; font-size: 0.8em;\"><img src=\"files/site_images/layout/comment-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;".($numComments > 0 ? translate("This post has <strong>{$numComments}</strong> repl" . ($numComments > 1 ? "ies" : "y"),"Bài này được trả lời <strong>{$numComments}</strong> lần") : translate("This post has no comments","Bài này chưa có trả lời"))."</span></span>";
	
	if ($post->author == $user->username OR $user->role > 0):
		echo "<span id=\"comment{$post->id}edit\" style=\"margin: 0 2px; font-size: 0.8em;\"><img src=\"files/site_images/layout/pencil-16.png\" style=\"vertical-align: middle;\"/>";
		echo "<span style=\"vertical-align: middle;\">&nbsp;<a href='javascript:;' onClick=\"javascript: load_page('edit_discussion.php?id={$post->id}&postid={$post->id}');\" target='_self'>".translate("Edit this post","Soạn lại bài này")."</a></span>";
		echo "</span>";
	endif;
	
	echo "<span id=\"discussion{$post->id}delete\" style=\"margin: 0 2px; font-size: 0.8em;\"><img src=\"files/site_images/layout/delete-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;";
	switch ($post->can_delete()){
		case "not_empty": echo translate("This post <strong>cannot</strong> be deleted because it contains replies.","Bài này <strong>không thể</strong> xóa được vì nó đã có bài trả lời."); break;
		case "too_old": echo translate("This post <strong>cannot</strong> be deleted because it is too old.","Bài này <strong>không thể</strong> xóa được vì nó quá thời hạn cho phép."); break;
		default:
			if ($post->author == $user->username OR $user->role > 0):
				echo "<a class='delete_qd_link' id='delete_{$post->id}' style='display: inline-block;'>".translate("Delete this post","Xóa bài này")."...</a>";
				echo "<script type='text/javascript'>";
				echo "$(function(){
					$('#delete_{$post->id}').click(function(){
						var answer = confirm('".translate("Deleting “".(empty($post->title) ? $post->id : $post->title)."” Are you sure?","Xóa bài “".(empty($post->title) ? $post->id : $post->title)."” ".ucfirst($user->you)." chắc không?")."');
						if (!answer) { return false; }
						$.post('/actions/quick_discussion.php?session=".session_id()."', { id: '{$post->id}', action: 'delete' }, function (data){
							if (data == 'error'){
								alert('".translate("Sorry, something went wrong, please let the admin know.","Xin lổi, có vấn đề gì đó, xin cho quản trị viên biết.")."');
								return false;
							}
							load_page('discussions.php');
						});
					});
				});";
				echo "</script>";
			else:
				echo translate("This post <strong>cannot</strong> be deleted because you are not the author.","Bài này <strong>không thể</strong> xóa được vì {$user->you} không phải là tác giả.");
			endif;
			break;
	}
	echo "</span></span>";
	?>
</div>

<div class="wrap" style="margin: 5px 0; clear: both;">
	<table cellspacing="5" cellpadding="0" border="0" style="width: 100%; margin: 10px 0;"><tr><td valign="top">
		<?php echo $post->qd_author->display_user_post_image(); ?>
	</td><td valign="top">
		<fieldset style="clear: left;">
			<legend align="left" style="font-size: 1.25em; font-weight: bold;"><?php echo empty($post->title) ? ($post->parent_id == 0 ? "[ ".translate("No title","Không chủ đề")." ]" : translate("Reply","Phản hồi")) : "{$post->title}"; ?></legend>
			<?php
			echo "<div id=\"discussion{$post->id}body\" style=\"text-align: left; padding: 5px; clear: both;\">{$post->body}</div>";
			echo "<div id=\"discussion{$post->id}link_to_this\" style=\"clear: both;\"><img src=\"files/site_images/layout/link-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;";
			echo translate("Link to this discussion","Liên kết tới bài này") . ":&nbsp;";
			echo "<input id=\"discussion{$post->id}link_code\" style=\"margin: 0; font-size: 0.8em; font-family: monospace;\" value=\"!l[{$post->id}:::".shorten_string($post->title,5)."]\" readonly=\"readonly\"/>";
			echo "<script type=\"text/javascript\">
				$('#discussion{$post->id}link_code').click(function(){
					$(this).select();
				});
				$(function(){
					var link_input = $('#discussion{$post->id}link_code');
					link_input.attr('size',link_input.val().length);
					link_input.qtip({
						content: { text: '".translate("Copy this line into your discussion body to create a link to this post","Chép hàng này vào nội dung bài để tạo liên kết đến bài này").".' },
						position: { my: 'bottom center', at: 'top center' }
					})
				});
			</script>";
			echo "</span></div>";
			?>
		</fieldset>
	</td></tr></table>
</div>

<?php
$vid_query = mysql_query("SELECT * FROM `videos_general` WHERE `attachedto` = 'quick_discussions:{$post->id}'");
$image_query = mysql_query("SELECT * FROM `images_general` WHERE `attachedto` = 'quick_discussions:{$post->id}'");
$music_query = mysql_query("SELECT * FROM `music_general` WHERE `attachedto` = 'quick_discussions:{$post->id}'");
$file_query = mysql_query("SELECT * FROM `files_general` WHERE `attachedto` = 'quick_discussions:{$post->id}'");
if (mysql_num_rows($vid_query) > 0 OR mysql_num_rows($image_query) > 0 OR mysql_num_rows($music_query) > 0 OR mysql_num_rows($file_query) > 0):
?>
<div class="wrap" style="margin: 5px 0; clear: both;">
	<fieldset>
		<legend><?php echo translate("File attachments","Tư liệu kèm"); ?></legend>
		<?php
		if (mysql_num_rows($file_query) > 0):
			echo "<fieldset style='border: 0; text-align: justify;'><legend>".translate("Documents","Tài liệu")."</legend>";
			$count = 1;
			while ($file_row = mysql_fetch_object($file_query)){
				switch($file_row->type){
					default: $img = "files/site_images/layout/attachment-16.png"; break;
					case "zip": case "rar": case "7z": $img = "files/site_images/layout/filetype_archive-16.png"; break;
					case "doc": case "docx": $img = "files/site_images/layout/filetype_doc-16.png"; break;
					case "pdf": $img = "files/site_images/layout/filetype_pdf-16.png"; break;
				}
				echo "<span style='display: inline-block; white-space: nowrap; vertical-align: middle; margin: 0 2px;'><img src='{$img}' style='vertical-align: middle;'/><span style='vertical-align: middle;'>&nbsp;<a href='get_file.php?type=files_general&id={$file_row->id}'>{$file_row->file_name}</a></span></span>";
				$count++;
			}
			echo "</fieldset>";
		endif;
		
		if (mysql_num_rows($vid_query) > 0):
			echo "<fieldset style='border: 0; text-align: justify;'><legend>".translate("Videos","Phim")."</legend>";
			$count = 1;
			while ($vid_row = mysql_fetch_object($vid_query)){
				echo "<style type='text/css'> #video{$vid_row->id}_wrapper{ text-align: center; margin: 10px auto; display: none; } </style>";
				echo "<div><a class='videoshow' id='video{$vid_row->id}show'><img src='files/site_images/layout/film-16.png' style='vertical-align: middle;'/><span style='vertical-align: middle;'> ".translate("[ Video#{$count} ] View video","[ Phim#{$count} ] Xem phim")."...</span></a><a class='videohide' id='video{$vid_row->id}hide' style='display: none;'><img src='files/site_images/layout/arrow_up-16.png' style='vertical-align: middle;'/><span style='vertical-align: middle;'> ".translate("[ Video#{$count} ] Hide video","[ Phim#{$count} ] Đóng khung phim")."...</span></a></div>";
				echo "<div id='video{$vid_row->id}'></div>";
				echo "<script type='text/javascript'>
					jwplayer('video{$vid_row->id}').setup({
						'id': 'video{$vid_row->id}_video',
						'flashplayer': 'scripts/mediaplayer/player.swf',
						'width': '400',
						'height': '300',
						'type': 'video',
						'file': 'get_file.php?type=videos_general&id={$vid_row->id}'
					});
					$('#video{$vid_row->id}_wrapper').addClass('videos');
					$('#video{$vid_row->id}show').click(function(){ $('.videos').hide(0,function(){ $('.videoshow').show(); $('.videohide').hide(); $('#video{$vid_row->id}show').hide(0,function(){ $('#video{$vid_row->id}hide').fadeIn(500,function(){ $('#video{$vid_row->id}_wrapper').slideDown(500); }); }); }); });
					$('#video{$vid_row->id}hide').click(function(){
						$('#video{$vid_row->id}hide').hide(0,function(){ $('#video{$vid_row->id}show').fadeIn(500); });
						$('#video{$vid_row->id}_wrapper').slideUp(500);
					});
				</script>";
				$count++;
			}
			echo "</fieldset>";
		endif;
		
		if (mysql_num_rows($image_query) > 0):
			echo "<fieldset style='border: 0; text-align: justify;'><legend>".translate("Images","Hình")."</legend>";
			echo "<div style='text-align: left; display: inline-block; margin: auto;'>";
			$gallery_rand = rand();
			while ($img_row = mysql_fetch_object($image_query)){
				echo "<a rel='gallery_{$gallery_rand}' class='post_img' href='get_file.php?type=images_general&id={$img_row->id}' style='margin: 2px;'><span style=\"width: 75px; height: 75px; display: inline-block; background-image: url('get_file.php?type=images_general&id={$img_row->id}&thumbnail=true'); background-position: center; border: 1px solid #DDD;\"></a>";
			}
			echo "</div>";
			//echo "<div style='text-align: center;'><img src='files/site_images/layout/filetype_archive-16.png' style='vertical-align: middle;'/><span style='vertical-align: middle;'> ".translate("Download all images in post","Tải hết hình trong bài")."</span></div>";
			echo "</fieldset>";
			echo "<style type='text/css'> a.post_img:hover{ background: none; } </style>";
			echo "<script type='text/javascript'>
			$('.post_img').fancybox({
				openEffect	: 'elastic',
				closeEffect	: 'elastic',
				type : 'image',
				padding : 5,
				helpers : { title : { type : 'outside' } }
			});
			</script>";
		endif;
		
		if (mysql_num_rows($music_query) > 0):
			echo "<fieldset style='border: 0; text-align: justify;'><legend>".translate("Music","Nhạc")."</legend>";
			while ($music = mysql_fetch_object($music_query)){
				echo "<div style='clear: both;'><div id='audioplayer_{$post->id}{$music->id}'></div><script type='text/javascript'> AudioPlayer.embed('audioplayer_{$post->id}{$music->id}', {soundFile: \"".$music->file_location."\", autostart: \"no\"}); </script><div><img src='files/site_images/layout/music-16.png' style='vertical-align: middle;'/>&nbsp;<a href='get_file.php?type=music_general&id={$music->id}'>".translate("Click here to download this music file","Bấm đây để tải tư liệu nhạc này")."...</a></div></div>";
			}
			echo "</fieldset>";
		endif;
		?>
	</fieldset>
</div>
<?php endif; ?>

<div class="wrap" style="margin: 5px 0; clear: both;">
	<fieldset>
		<legend><?php echo translate("User read status","Thành viên đã đọc qua bài"); ?></legend>
		<?php
		$user_q = mysql_query("SELECT * FROM `users` WHERE `active` = '1' AND `last_seen` > '0000-00-00 00:00:00'");
		$read_status_array = array();
		$comments_count = 0;
		while ($u = mysql_fetch_object($user_q)){
			if ($u->username == $user->username OR $u->username == $post->author) continue;
			$status_q = mysql_query("SELECT * FROM `quick_discussions_read_status` WHERE `postid` = '{$post->id}' AND `{$u->username}` > '1970-01-01 00:00:00'");
			if (mysql_num_rows($status_q) > 0){
				$read_status = mysql_fetch_object($status_q);
				$read_status_array[] = array(
					"user" => $u->username,
					"when" => $read_status->{$u->username}
				);
				$comments_count++;
			}
		}
		
		if ($comments_count > 0){
			?>
			<span style="display: inline-block; float: left; font-size: 0.8em;">
			<?php
			usort($read_status_array,"single_discussion_usort_read_status");
			
			foreach($read_status_array as $a){
				$b = new User($a["user"]);
				echo "<span style=\"display: inline-block; float: left;\"><span style=\"display: inline-block; border-bottom: 1px solid #{$b->color};margin: 0 2px;\"><strong>".($b->username == $user->username ? translate("You",$b->you) : $b->name)."</strong></span><span style=\"display: inline-block; margin: 0 2px;\">&mdash;&nbsp;<em>".(date_diff_short($a["when"]) == "0" ? translate("just now","mới đây") : (date_diff_short($a["when"]) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($a["when"])." ".translate("ago","trước")))."</em></span></span>";
			}
			?>
			</span>
			<?php
		} else { echo "<em>".translate("No reader yet","Chưa ai đọc hết")."...</em>"; }
		?>
	</fieldset>
</div>

<?php if ($post->parent_id == 0): ?>
<div class="wrap" style="text-align: center; margin: 5px auto; clear: both;">
<fieldset>
	<legend style="font-size: 1.25em; font-weight: bold;"><?php echo translate("Post a reply","Đăng trả lời"); ?></legend>
	<form id="quick_discussion">
		<?php $group = rand(); ?>
		<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $post->id; ?>"/>
		<input type="hidden" name="action" id="action" value="add"/>
		<input type="hidden" name="group" id="group" value="<?php echo $group; ?>"/>
		<input type="hidden" name="category" id="category" value="comment"/>
		<input type="hidden" name="qd_title" id="qd_title" value="RE: <?php echo !empty($post->title) ? $post->title : $post->id; ?>"/>
		<div id="smilies_area" style="text-align: center;">
			<span style="display: inline-block; margin: 5px auto;"><span class="helpmsg"><?php echo translate("Click on an image to insert it into your post (optional)","Bấm vào một biểu trưng để gài vào bài viết (không cần thiết)"); ?>.</span></span>
			<div id="smilies_area_body" style="height: 100px; overflow: auto;"></div>
		</div>
		<textarea id="quick_msg" name="quick_msg"<?php if (isMobile()) echo " style=\"width: 95%; height: 50px;\""; ?>></textarea>
		<fieldset>
			<legend align="center"><?php echo translate("Attachments","Tư liệu đính kèm"); ?></legend>
			<div style="text-align: center; font-size: 0.8em;"><em><?php echo translate("Please note that the first image attachment will be used as the posts' feature image.","Xin lưu ý rằng hình đầu tiên được kèm với bài sẽ được sử dụng làm hình tượng trưng cho bài."); ?></em></div>
			<style type="text/css">
			#attachment_upload_control p { margin: 10px auto; font-size: 0.9em; }
			#log { margin: 5px auto; padding: 0; width: 500px;}
			#log { text-align: left; }
			#log li { list-style-position: inside; margin: 2px; border: 1px solid #ccc; padding: 10px; font-size: 12px; color: #000; background: #fff; position: relative;}
			#log li .progressbar { border: 1px solid #ddd; height: 5px; background: #fff; }
			#log li .progress { background: #7cc576; width: 0%; height: 5px; }
			#log li p { margin: 0; line-height: 18px; }
			#log li.success { border: 1px solid #339933; background: #ccf9b9; }
			#log li span.cancel { position: absolute; top: 5px; right: 5px; width: 20px; height: 20px;   background: url('files/site_images/layout/close-16.png') no-repeat; cursor: pointer; }
			</style>
			<script type="text/javascript">
			$(function(){
				var max_file_size = "50 MB";
				// var allowed_types = "*.jpg;*.jpeg;*.gif;*.png;*.bmp;*.doc;*.docx;*.pdf;*.txt;*.zip;*.7z;*.rar;*.xls;*.xlsx;*.flv;*.mp4;*.mpeg;*.mp3";
				var allowed_types = "*.jpg;*.jpeg;*.gif;*.png;*.bmp;*.doc;*.docx;*.pdf;*.txt;*.zip;*.7z;*.rar;*.xls;*.xlsx;*.flv;*.mp4;*.mpeg;";
				$('#attachment_upload_control').swfupload({
					flash_url : "/scripts/swfupload/Flash/swfupload.swf",
					upload_url: "/actions/quick_discussion_upload_attachment.php",
					post_params: {"fileTypeExts" : allowed_types, "group" : $("#group").val(), "session" : "<?php echo session_id(); ?>"},
					file_size_limit: max_file_size,
					file_types: allowed_types,
					// file_types_description: "<?php echo translate("Images","Hình ảnh"); ?>, <?php echo translate("Documents","Văn kiện"); ?>, <?php echo translate("Archives","Tài liệu nén"); ?>, <?php echo translate("Videos","Phim"); ?>, <?php echo translate("Music","Nhạc"); ?>",
					file_types_description: "<?php echo translate("Images","Hình ảnh"); ?>, <?php echo translate("Documents","Văn kiện"); ?>, <?php echo translate("Archives","Tài liệu nén"); ?>, <?php echo translate("Videos","Phim"); ?>",
					file_upload_limit : 0,
					file_queue_limit : 0,
					
					// Button settings
					button_image_url: "files/site_images/layout/buttons/selectPostAttachment_<?php echo translate("en","vi"); ?>_175x32.png",
					button_width: "175",
					button_height: "32",
					button_placeholder_id: "attachmentUploadButton",
					button_cursor: SWFUpload.CURSOR.HAND,
					button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
					
					debug: false
				})
					.bind('fileQueued', function(event, file){
						var listitem='<li id="'+file.id+'" >'+
							'<?php echo translate("File","Tư liệu"); ?>: <em>'+file.name+'</em> ('+Math.round(file.size/1024)+' KB) <span class="progressvalue" ></span>'+
							'<div class="progressbar"><div class="progress"></div></div>'+
							'<p class="status" ><?php echo translate("Pending","Đang chờ"); ?></p>'+
							'<span class="cancel" >&nbsp;</span>'+
							'</li>';
						$('#log').append(listitem);
						$('li#'+file.id+' .cancel').bind('click', function(){
							var swfu = $.swfupload.getInstance('#attachment_upload_control');
							swfu.cancelUpload(file.id);
							$('li#'+file.id).slideUp('fast');
						});
						// start the upload since it's queued
						//$(this).swfupload('startUpload');
						window.attachments++;
					})
					.bind('fileQueueError', function(event, file, errorCode, message){
						if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
							alert("<?php echo translate("You selected too many files. Please select one photo to use as an avatar.","{$user->you} đã chọn quá nhiều tư liệu. Xin chỉ chọn một hình để dùng làm tượng trưng."); ?>");
							return;
						}

						switch (errorCode) {
							case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
								alert("<?php echo translate("The file is too big; the server only allows files 50MB or less. Please reduce the size by splitting them or contact the administrator for further assistance.","Tài liệu của {$user->you} chọn quá lớn; hệ thống chỉ cho phép tư liệu 50MB hoặc nhỏ hơn. Xin nén tư liệu lại, hoặc liên hệ quản trị viên để được hổ trợ thêm."); ?>");
								break;
							case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
								alert("<?php echo translate("You file has 0 bytes.","Tư liệu của {$user->you} có 0 bytes."); ?>");
								break;
							case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
								alert("<?php echo translate("File type not allowed, please only upload the allowed file types.","Loại tư liệu không cho phép, xin chỉ tải những tư liệu cho phép thôi."); ?>");
								break;
							default:
								alert("<?php echo translate("Something's wrong with your file...","Tư liệu có vấn đề gì đó..."); ?>");
								break;
						}
					})
					.bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued){
						$('#queuestatus').text('<?php echo translate("Files Selected","Tư liệu được chọn"); ?>: '+numFilesSelected+' / <?php echo translate("Queued Files","Tư liệu trong hàng"); ?>: '+numFilesQueued);
					})
					.bind('uploadStart', function(event, file){
						$('#log li#'+file.id).find('p.status').text('<?php echo translate("Uploading","Đang tải"); ?>...');
						$('#log li#'+file.id).find('span.progressvalue').text('0%');
						$('#log li#'+file.id).find('span.cancel').hide();
					})
					.bind('uploadProgress', function(event, file, bytesLoaded){
						//Show Progress
						var percentage=Math.round((bytesLoaded/file.size)*100);
						$('#log li#'+file.id).find('div.progress').css('width', percentage+'%');
						$('#log li#'+file.id).find('span.progressvalue').text(percentage+'%');
					})
					.bind('uploadSuccess', function(event, file, serverData){
						if (serverData == "error_file_too_small"){
							alert("<?php echo translate("The selected image is too small, please select an image with at least 100 pixels in width or height.","Hình {$user->you} chọn quá nhỏ, xin chọn hình nào với chiều dài hoặc chiều cao ít nhất 100 pixels."); ?>");
							var swfu = $.swfupload.getInstance('#attachment_upload_control');
							swfu.cancelUpload(file.id);
							$('li#'+file.id).slideUp('fast');
						} else {
							var item=$('#log li#'+file.id);
							item.find('div.progress').css('width', '100%');
							item.find('span.progressvalue').text('100%');
							var pathtofile='<a href="uploads/'+file.name+'" target="_blank" >view &raquo;</a>';
							item.addClass('success').find('p.status').html('Done!!! | '+pathtofile);
						}
					})
					.bind('uploadComplete', function(event, file){
						window.attachments--;
						if (window.attachments > 0){ $(this).swfupload('startUpload'); }
						else {
							$("#loading_frame").fadeIn(500);
							window.submitQD_Form();
						}
					})
			});
			</script>
			<div id="attachment_upload_control">
				<div style="text-align: center;">
					<span class="button" id="attachmentUploadButton"></span>
					<div id="queuestatus"></div>
					<ol id="log"></ol>
				</div>
			</div>
		</fieldset>
		<button id="qd_submit" type="submit"><?php echo translate("Submit comment","Đăng trả lời"); ?></button>
	</form>
</fieldset>
</div>
<?php endif; ?>

<div id="comments_loading" style="clear: both; margin: 10px;">
	<div><img src='files/site_images/layout/loading-circle-16.gif' style='vertical-align: middle; margin: auto; display: block;'/></div>
	<div><?php echo translate("Please wait","Xin chờ giây lát"); ?>...</div>
</div>
<div id="comments_area" style="width: 90%; margin: auto;"></div>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>