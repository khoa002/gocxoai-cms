<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"]) OR !isset($_REQUEST["id"]) OR !isset($_REQUEST["type"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
$user = new User($_SESSION["user"]);
$user->set_last_seen("view_image:{$_REQUEST["type"]}:{$_REQUEST["id"]}");
require_once("{$_SESSION["root_path"]}/page_top.php");
$type = $_GET["type"];
?>
<div>
<?php
switch($type){
	default: die();
	case "images_user_avatars":
		$user_being_viewed = new User($_REQUEST["id"]); ?>
		<script type="text/javascript">
		$(function(){
			top.document.title = "<?php echo translate("Viewing {$user_being_viewed->name}'s avatar","Xem hình tượng trưng của {$user_being_viewed->name}"); ?>";
		});
		</script>
		<div style="clear: both; margin: 5px auto;">
			<span class="wrap" style="display: inline-block; margin: 0px;"><a id="image" href="get_file.php?type=<?php echo $type; ?>&id=<?php echo $_REQUEST["id"]; ?>"><img src="get_file.php?type=<?php echo $type; ?>&id=<?php echo $_REQUEST["id"]; ?>" width="100%"/></a>
			<script type="text/javascript">
				$(function(){
					$("#image").fancybox({
						openEffect	: "elastic",
						closeEffect	: "elastic",
						type : "image",
						padding : 5,
						helpers : { title : { type : "over" } }
					});
				});
			</script></span>
		</div>
		<?php break;
	case "post_image":
	case "images_general": ?>
		<script type="text/javascript">
		$(function(){
			top.document.title = "<?php echo translate("Viewing image #","Xem hình #") . $_REQUEST["id"]; ?>";
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
				window.submitQD_Form();
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
				load_page("view_image.php?type=<?php echo $type; ?>&id=<?php echo $_REQUEST["id"]; ?>");
			});
		}
		</script>
		<div style="clear: both; margin: 5px auto;">
			<span class="wrap" style="float: left; display: inline-block; margin: 5px;"><a id="image" href="get_file.php?type=<?php echo $type; ?>&id=<?php echo $_REQUEST["id"]; ?>"><img src="get_file.php?type=<?php echo $type; ?>&id=<?php echo $_REQUEST["id"]; ?>" width="500"/></a>
			<script type="text/javascript">
				$(function(){
					$("#image").fancybox({
						openEffect	: "elastic",
						closeEffect	: "elastic",
						type : "image",
						padding : 5,
						helpers : { title : { type : "over" } }
					});
				});
			</script></span>
		<?php
		$i = mysql_fetch_object(mysql_query("SELECT * FROM `users_prefs` WHERE `option` = 'comments_sort'"));
		$comment_sort = $i->{$user->username};
		$result = mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` = '-1' AND `category` = '{$type}:{$_REQUEST["id"]}' ORDER BY `last_touched` {$comment_sort}, `edited` {$comment_sort}, `created` {$comment_sort}");
		if (mysql_num_rows($result) > 0):
		?>
			<span class="wrap" style="float: left; display: inline-block; margin: 5px;">
				<fieldset>
					<legend style="font-size: 1.25em; font-weight: bold;"><?php echo translate("Comments","Phản hồi"); ?></legend>
					<?php
						while ($row = mysql_fetch_object($result)){
							$comment = new QuickDiscussion();
							$comment->load($row->id); ?>
							<table cellspacing="5" cellpadding="0" border="0" style="width: 100%; margin: 10px 0;"><tr><td valign="top">
							<div><a class="post_image" href="get_file.php?type=images_user_avatars&id=<?php echo $comment->qd_author->username; ?>"><img src="get_file.php?type=images_user_avatars&id=<?php echo $comment->qd_author->username; ?>&thumbnail=true" height="50" style="border: 3px solid #<?php echo $comment->qd_author->color; ?>;"/></a></div>
							<div><strong style="font-size: 0.8em;"><?php echo $comment->qd_author->name; ?></strong></div>
							</td><td valign="top">
							<?php
							echo "<div id=\"discussion{$comment->id}body\" style=\"text-align: left; padding: 5px; clear: both;\">{$comment->body}</div>";
							?>
							<div style="font-size: 0.9em;">
								<fieldset>
									<legend><?php echo translate("User read status","Thành viên đã đọc qua bài"); ?></legend>
									<span style="display: inline-block; float: left; font-size: 0.8em;">
									<?php
									$user_q = mysql_query("SELECT * FROM `users` WHERE `active` = '1'");
									$read_status_array = array();
									$reader_count = 0;
									while ($u = mysql_fetch_object($user_q)){
										if ($u->username == $user->username OR $u->username == $comment->author) continue;
										// add a column to the quick_discussion_read_status table if doesn't exist
										mysql_add_column("quick_discussions_read_status",$u->username,"DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00'");
										$status_q = mysql_query("SELECT * FROM `quick_discussions_read_status` WHERE `postid` = '{$comment->id}' AND `{$u->username}` > '1970-01-01 00:00:00'");
										if (mysql_num_rows($status_q) > 0){
											$read_status = mysql_fetch_object($status_q);
											$read_status_array[] = array(
												"user" => $u->username,
												"when" => $read_status->{$u->username}
											);
											$reader_count++;
										}
									}
									if ($reader_count > 0){
										usort($read_status_array,"single_discussion_usort_read_status");
									
										foreach($read_status_array as $a){
											$b = new User($a["user"]);
											echo "<span style=\"display: inline-block; float: left;\"><span style=\"display: inline-block; border-bottom: 1px solid #{$b->color};margin: 0 2px;\"><strong>".($b->username == $user->username ? translate("You",$b->you) : $b->name)."</strong></span><span style=\"display: inline-block; margin: 0 2px;\">&mdash;&nbsp;<em>".(date_diff_short($a["when"]) == "0" ? translate("just now","mới đây") : (date_diff_short($a["when"]) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($a["when"])." ".translate("ago","trước")))."</em></span></span>";
										}
									} else { echo "<em>".translate("No reader yet","Chưa ai đọc hết")."...</em>"; }
									?>
									</span>
								</fieldset>
								<fieldset>
									<legend><?php echo translate("Comment information","Thông tin của bài trả lời"); ?></legend>
									<div class="meta" id="comment<?php echo $comment->id; ?>meta" style="text-align: left; margin: 0; font-size: 0.8em; padding-left: 0px;">
									<?php
										echo "<div id=\"comment{$comment->id}created\"><img src=\"files/site_images/layout/calendar-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;".translate("Posted ","Được đăng ")."<strong>".(date_diff_short($comment->created) == "0" ? translate("just now","mới đây") : (date_diff_short($comment->created) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($comment->created)." ".translate("ago","trước")))."</strong></span></div>";
										
										if (!empty($comment->edited) AND ($comment->edited != "1970-01-01 00:00:00")) echo "<div id=\"comment{$comment->id}edited\"><img src=\"files/site_images/layout/calendar2-16.png\" style=\"vertical-align: middle;\"/>".($comment->author != $comment->editor ? "<span style=\"vertical-align: middle;\">&nbsp;".translate("Edited by ","Soạn lại bởi ")."<strong>{$comment->qd_editor->name}</strong>".translate(" about "," khoảng ")."<strong>".(date_diff_short($comment->edited) == "0" ? translate("just now","mới đây") : (date_diff_short($comment->edited) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($comment->edited)." ".translate("ago","trước")))."</strong></span>" : "<span style=\"vertical-align: middle;\">&nbsp;".translate("Edited ","Soạn lại ").translate(" about "," khoảng ")."<strong>".(date_diff_short($comment->edited) == "0" ? translate("just now","mới đây") : (date_diff_short($comment->edited) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($comment->edited)." ".translate("ago","trước")))."</strong></span>")."</div>";
										
										echo "<div id=\"comment{$comment->id}delete\"><img src=\"files/site_images/layout/delete-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;";
										switch ($comment->can_delete()){
											case "not_empty": echo translate("This post <strong>cannot</strong> be deleted because it contains replies.","Bài này <strong>không thể</strong> xóa được vì nó đã có bài trả lời."); break;
											case "too_old": echo translate("This post <strong>cannot</strong> be deleted because it is too old.","Bài này <strong>không thể</strong> xóa được vì nó quá thời hạn cho phép."); break;
											default:
												if ($comment->author == $user->username OR $user->role > 0):
													echo "<a class='delete_qd_link' id='delete_{$comment->id}' style='display: inline-block;'>".translate("Click here to delete this post","Bấm đây để xóa bài này")."...</a>";
													echo "<script type='text/javascript'>";
													echo "$(function(){
														$('#delete_{$comment->id}').click(function(){
															var answer = confirm('".translate("Deleting “".(empty($comment->title) ? $comment->id : $comment->title)."” Are you sure?","Xóa bài “".(empty($comment->title) ? $comment->id : $comment->title)."” ".ucfirst($user->you)." chắc không?")."');
															if (!answer) { return false; }
															$.post('/actions/quick_discussion.php?session=".session_id()."', { id: '{$comment->id}', action: 'delete' }, function (data){
																if (data == 'error'){
																	alert('".translate("Sorry, something went wrong, please let the admin know.","Xin lổi, có vấn đề gì đó, xin cho quản trị viên biết.")."');
																	return false;
																}
																load_page('view_image.php?type={$type}&id={$_REQUEST["id"]}');
															});
														});
													});";
													echo "</script>";
												else:
													echo translate("This post <strong>cannot</strong> be deleted because you are not the author.","Bài này <strong>không thể</strong> xóa được vì {$user->you} không phải là tác giả.");
												endif;
												break;
										}
										echo "</span></div>";
									?>
									</div>
								</fieldset>
							</div>
							</td></tr></table>
						<?php } ?>
				</fieldset>
			</span>
		<?php endif; ?>
		</div>
		<div class="wrap" style="margin: 5px auto; clear: both;">
			<fieldset>
				<legend style="font-size: 1.25em; font-weight: bold;"><?php echo translate("Post a reply","Đăng phản hồi"); ?></legend>
				<form id="quick_discussion">
					<?php $group = rand(); ?>
					<input type="hidden" name="parent_id" id="parent_id" value="-1"/>
					<input type="hidden" name="action" id="action" value="add"/>
					<input type="hidden" name="group" id="group" value="<?php echo $group; ?>"/>
					<input type="hidden" name="category" id="category" value="<?php echo "{$type}:{$_REQUEST["id"]}"; ?>"/>
					<input type="hidden" name="qd_title" id="qd_title" value=""/>
					<div id="smilies_area" style="text-align: center;">
						<span style="display: inline-block; margin: 5px auto;"><span class="helpmsg"><?php echo translate("Click on an image to insert it into your post (optional)","Bấm vào một biểu trưng để gài vào bài viết (không cần thiết)"); ?>.</span></span>
						<div id="smilies_area_body" style="height: 100px; overflow: auto;"></div>
					</div>
					<textarea id="quick_msg" name="quick_msg"<?php if (isMobile()) echo " style=\"width: 95%; height: 50px;\""; ?>></textarea>
					<button id="qd_submit" type="submit"><?php echo translate("Submit comment","Đăng trả lời"); ?></button>
				</form>
			</fieldset>
		</div>
		<?php
		break;
}
?>
</div>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>