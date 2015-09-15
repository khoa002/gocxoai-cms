<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
$user = new User($_SESSION["user"]);
$user->set_last_seen("addnew_discussion");

require_once("{$_SESSION["root_path"]}/page_top.php");
if (isMobile()):
?>
<div style="text-align: center; margin: 10px 0;"><?php echo translate("Unfortunately, adding new discussions on mobile devices isn't available at the moment, I'm currently working on making the form display properly. Please use a computer or try again at a later time.","Rất tiếc, chức năng đăng thảo luận hiện giờ không hoạt động được trên các hệ thống di động, {$user->me} đang trong quá trình chỉnh sửa để công cụ được hoàn chỉnh hơn. Xin vui lòng sử dụng máy vi tính hoặc xem lại trang này trong tương lai."); ?></div>
<div style="text-align: center; margin: 10px 0;"><a onClick="load_page('discussions.php');">« <?php echo translate("Return to discussions list","Trở về danh sách thảo luận"); ?>...</a></div>
<?php die(); endif; ?>

<script type="text/javascript">
var loading_frame = $("#loading_frame");
var loading_frame_speed = 500;
var attachments = 0;
$(function(){
	// setting the page title
	top.document.title = "<?php echo translate("Add new discussion","Đăng thảo luận mới"); ?>";
	$("#quick_msg").tinymce({
		script_url : 'scripts/tinymce/jscripts/tiny_mce/tiny_mce.js',
		language: "<?php echo $_SESSION["language"]; ?>",
		width: "100%",
		height: "300",
		theme : "advanced",
		skin : "o2k7",
		skin_variant : "silver",
		plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,cleanup,code,insertdate,inserttime,|,forecolor,backcolor,hr,sub,sup,|,formatselect,fontsizeselect,|,preview",
		theme_advanced_buttons2 : "tablecontrols",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "justify",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false
	});
	$("#quick_discussion").submit(function(e){
		e.preventDefault();
		if ( (!$.trim($("#qd_title").val())) ){
			alert("<?php echo translate("Please provide a post title!","Xin điền vào chủ đề!"); ?>");
			return false;
		}
		if ( (!$.trim($("#quick_msg").val())) ){
			alert("<?php echo translate("You didn't say anything!",ucfirst($user->you)." chưa nói gì hết!"); ?>");
			return false;
		}
		$("#qd_submit").hide();
		if (window.attachments > 0){ $('#attachment_upload_control').swfupload('startUpload'); }
		else { window.submitQD_Form(); }
	});
	$('#smilies_area_body').load('show/qd_emoticons.php');
});
function submitQD_Form(){
	$.post("/actions/quick_discussion.php?session=<?php echo session_id(); ?>", $("#quick_discussion").serialize(), function (data){
		if (data == "error"){
			alert("<?php echo translate("Sorry, something went wrong, please let the admin know.","Xin lổi, có vấn đề gì đó, xin cho quản trị viên biết."); ?>");
			$("#qd_submit").show();
			return false;
		}
		loading_frame.fadeIn(loading_frame_speed);
		load_page("discussions.php");
	});
}
</script>
<?php
	echo "<div style=\"text-align: center; margin: 10px 0;\"><a href=\"javascript:;\" onClick=\"load_page('discussions.php?".(isset($_REQUEST["start"]) ? "start={$_REQUEST["start"]}" : "")."');\">« ".translate("Return to discussions list","Trở về danh sách thảo luận")."...</a></div>";
?>
<h2><img src="files/site_images/layout/add-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Add new discussion","Đăng thảo luận mới"); ?></span></h2>
<div class="wrap">
	<form id="quick_discussion" style="width: 100%;">
		<?php $group = rand(); ?>
		<input type="hidden" name="parent_id" id="parent_id" value="0"/>
		<input type="hidden" name="action" id="action" value="add"/>
		<input type="hidden" name="group" id="group" value="<?php echo $group; ?>"/>
		<div style="display: inline-block; margin: auto; width: 90%">
			<fieldset style="width: 70%; display: inline-block; float: left;">
				<legend align="center"><label for="qd_title"><?php echo translate("Title","Chủ đề"); ?></label></legend>
				<input id="qd_title" name="qd_title" style="width: 75%;"/>
			</fieldset>
			<fieldset style="width: 20%; display: inline-block; float: left;">
				<legend align="center"><label for="category"><?php echo translate("Category","Thể loại"); ?>: </label></legend>
				<select name="category" id="category">
					<?php
					$categories = get_qd_categories();
					foreach($categories as $key=>$value) echo "<option value=\"{$key}\"".($key == "general" ? " selected=\"selected\"" : "").">{$value}</option>";
					?>
				</select>
			</fieldset>
		</div>
		<fieldset style="clear: both;">
			<legend align="center"><?php echo translate("Smilies","Hình biểu trưng"); ?></legend>
			<div id="smilies_area" style="text-align: center;">
				<span style="display: inline-block; margin: 5px auto;"><span class="helpmsg"><?php echo translate("Click on an image to insert it into your post","Bấm vào một biểu trưng để gài vào bài viết"); ?>.</span></span>
				<div id="smilies_area_body" style="height: 150px; overflow: auto;"></div>
			</div>
		</fieldset>
		<fieldset>
			<legend align="center"><?php echo translate("Body","Nội dung"); ?></legend>
			<textarea id="quick_msg" name="quick_msg"></textarea>
		</fieldset>
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
		<button id="qd_submit" type="submit"><?php echo translate("Submit discussion","Đăng thảo luận"); ?></button>
	</form>
</div>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>