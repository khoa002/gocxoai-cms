<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
$user = new User($_SESSION["user"]);
if (!isset($_REQUEST["id"])) { die(); }
$id = $_REQUEST["id"];
$user->set_last_seen("editing_discussion:{$id}");
$discussion = new QuickDiscussion();
$discussion->load($id,true);

require_once("{$_SESSION["root_path"]}/page_top.php");
?>
<script type="text/javascript">
var attachments = 0;
$(function(){
    // setting the page title
    top.document.title = "<?php echo empty($discussion->title) ? translate("Editing post #{$id}","Soạn bài #{$id}") : translate("Editing {$discussion->title}","Soạn bài {$discussion->title}"); ?>";
	
	$('#smilies_area_body').load('show/qd_emoticons.php');
})
</script>
<?php
	echo "<div style=\"text-align: center; margin: 10px 0;\"><a onClick=\"load_page('view_single_discussion.php?".(isset($_REQUEST["postid"]) ? "id={$_REQUEST["postid"]}" : "")."');\">« ".translate("Return to discussion","Trở về thảo luận")."...</a></div>";
?>
<h2><img src="files/site_images/layout/discussion-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Edit discussion","Soạn bài"); ?></span></h2>
<div class="wrap">
	<form id="quick_discussion">
		<?php $group = rand(); ?>
		<input type="hidden" name="action" id="action" value="edit"/>
		<input type="hidden" name="id" id="id" value="<?php echo $discussion->id; ?>"/>
		<input type="hidden" name="group" id="group" value="<?php echo $group; ?>"/>
		<div style="display: inline-block; margin: auto;">
			<fieldset style="display: inline-block; float: left;">
				<legend align="center"><label for="qd_title"><?php echo translate("Title","Chủ đề"); ?>: </label></legend>
				<input id="qd_title" name="qd_title" value="<?php echo $discussion->title; ?>" style="width: 75%;"/>
			</fieldset>
			<fieldset style="display: inline-block; float: left;">
				<legend align="center"><label for="category"><?php echo translate("Category","Thể loại"); ?>: </label></legend>
				<?php if ($discussion->category == "comment"): ?>
				<input type="hidden" name="category" id="category" value="comment"/>
				<div style="font-size: 0.8em;"><em><?php echo translate("Comment","Phản hồi"); ?></em></div>
				<?php else: ?>
				<select name="category" id="category">
					<?php
					$categories = get_qd_categories();
					foreach($categories as $key=>$value) echo "<option value=\"{$key}\"".($key == $discussion->category ? " selected=\"selected\"" : "").">{$value}</option>";
					?>
				</select>
				<?php endif; ?>
			</fieldset>
		</div>
		<fieldset>
			<legend align="center"><?php echo translate("Smilies","Hình biểu trưng"); ?></legend>
			<div id="smilies_area" style="text-align: center;">
				<span style="display: inline-block; margin: 5px auto;"><span class="helpmsg"><?php echo translate("Click on an image to insert it into your post","Bấm vào một biểu trưng để gài vào bài viết"); ?>.</span></span>
				<div id="smilies_area_body" style="height: 150px; overflow: auto;"></div>
			</div>
		</fieldset>
		<fieldset>
			<legend align="center"><?php echo translate("Body","Nội dung"); ?></legend>
			<textarea id="quick_msg" name="quick_msg"><?php echo $discussion->body; ?></textarea>
		</fieldset>
		<div style="margin: 2px 0; text-align: left; font-size: 0.9em; font-style: italic;"><strong style="font-style: normal;"><?php echo translate("Post ID","Số định bài"); ?>: </strong><em><?php echo $discussion->id; ?></em></div>
		<div style="text-align: left; font-size: 0.9em; font-style: italic;"><strong style="font-style: normal;"><?php echo translate("Parent ID","Số bài nguồn"); ?>: </strong><input type="text" name="parent_id" id="parent_id" value="<?php echo $discussion->parent_id; ?>" style="width: 3em; text-align: center;"/> <img id="parent_id_help" src="/files/site_images/layout/help-16.png"/></div>
		<script type="text/javascript">
		$("#quick_msg").tinymce({
			script_url : 'scripts/tinymce/jscripts/tiny_mce/tiny_mce.js',
			language: "<?php echo $_SESSION["language"]; ?>",
			width: "100%",
			<?php
			if ($discussion->parent_id == 0) echo "height: \"300\",";
			else echo "height: \"150\","
			?>
			theme : "advanced",
			skin : "o2k7",
			skin_variant : "silver",
			plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
			<?php if ($discussion->parent_id == 0): ?>
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,cleanup,code,insertdate,inserttime,|,forecolor,backcolor,hr,sub,sup,|,formatselect,fontsizeselect,|,preview",
			<?php else: ?>
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,cleanup,code,insertdate,inserttime,|,forecolor,backcolor,hr,sub,sup",
			<?php endif; ?>
			<?php if ($discussion->parent_id == 0): ?>
			theme_advanced_buttons2 : "tablecontrols",
			<?php else: ?>
			theme_advanced_buttons2 : "",
			<?php endif; ?>
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "justify",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : false
		});
		
		$(function(){
			$("#parent_id_help").qtip({
				content: { text: "<?php echo translate("This is the value of the discussion this post is attached to as a comment, &ldquo;0&rdquo; means that the post is a separate discussion and is not a comment. Enter a discussion ID number (can be found on the bottom of each discussion in the main page) to attach this post as a comment; enter &ldquo;0&rdquo; to make this post a separate discussion.","Đây là số bài thảo luận mà bài này đang được gắng vào theo dạng một bài trả lời, &ldquo;0&rdquo; có nghĩa là bài này là một bài thảo luận riêng và không phải là một bài trả lời. Điền vào một số định bài (được tìm thấy ở phần cuối mổi bài ở trang chính) đề cài bài này vào thảo luận đó thành một bài trả lời; điền vào &ldquo;0&rdquo; để tách bài này ra thành một bài thảo luận riêng."); ?>" }
			});
		});
		</script>
		<?php
		$vid_query = mysql_query("SELECT * FROM `videos_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
		$image_query = mysql_query("SELECT * FROM `images_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
		$music_query = mysql_query("SELECT * FROM `music_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
		$file_query = mysql_query("SELECT * FROM `files_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
		if (mysql_num_rows($vid_query) > 0 OR mysql_num_rows($image_query) > 0 OR mysql_num_rows($music_query) > 0 OR mysql_num_rows($file_query) > 0 OR !empty($discussion->thumbnail)):
		?>
		<fieldset>
			<legend align="center"><?php echo translate("Attachments","Tư liệu đính kèm"); ?></legend>
			<?php
			$thumbnail_images_general_id = -1;
			if (mysql_num_rows($image_query) > 0 OR (!empty($discussion->thumbnail))):
			?>
			<div id="discussion<?php echo $discussion->id; ?>image_area"></div>
			<script type="text/javascript">
			$("#discussion<?php echo $discussion->id; ?>image_area").load("/show/qd_images.php?id=<?php echo $discussion->id; ?>");
			</script>
			<?php
			endif;
			
			if (mysql_num_rows($vid_query) > 0):
				echo "<fieldset style='display: inline-block;'><legend>".translate("Videos","Phim")."</legend>";
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
			
			if (mysql_num_rows($music_query) > 0):
				echo "<fieldset style='display: inline-block;'><legend align='center' style='font-size: 0.8em;'>".translate("Music","Nhạc")."</legend>";
				while ($music = mysql_fetch_object($music_query)){
					echo "<div style='clear: both;'><div id='audioplayer_{$post->id}{$music->id}'></div><script type='text/javascript'> AudioPlayer.embed('audioplayer_{$post->id}{$music->id}', {soundFile: \"".$music->file_location."\", autostart: \"no\"}); </script><div><img src='files/site_images/layout/music-16.png' style='vertical-align: middle;'/>&nbsp;<a href='get_file.php?type=music_general&id={$music->id}'>".translate("Click here to download this music file","Bấm đây để tải tư liệu nhạc này")."...</a></div></div>";
				}
			endif;
			?>
		</fieldset>
		<?php endif; ?>
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
		<button style="font-size: 0.9em;" id="qd_submit" type="submit"><?php echo translate("Edit post","Soạn bài"); ?></button>
		<script type="text/javascript">
		$("#quick_discussion").submit(function(e){
			e.preventDefault();
			if ( (!$.trim($("#quick_msg").val())) ){
				alert("<?php echo translate("Your post is empty","Bài của {$user->you} bị bỏ trống"); ?>");
				return false;
			}
			$("#qd_submit").hide();
			if (window.attachments > 0){
				$('#attachment_upload_control').swfupload('startUpload');
			} else { window.submitQD_Form(); }
		});
		function submitQD_Form(){
			$.post("/actions/quick_discussion.php?session=<?php echo session_id(); ?>", $("#quick_discussion").serialize(), function (data){
				switch (data){
					case "loading error":
						alert("<?php echo translate("Cannot load post, please let the admin know.","Không nạp được bài, xin cho quản trị viên biết."); ?>");
						break;
					case "editing error":
						alert("<?php echo translate("Did not edit successfully, please let the admin know.","Soạn thảo không hoàn tất, xin cho quản trị viên biết."); ?>");
						return false;
						break;
					default: break;
				}
				loading_frame.fadeIn(loading_frame_speed);
				load_page("view_single_discussion.php?<?php echo (isset($_REQUEST["postid"]) ? "id={$_REQUEST["postid"]}" : ""); ?>");
			});
		}
		</script>
	</form>
</div>
<div id="post<?php echo $discussion->id; ?>dummy_frame" style="display: none;"></div>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>