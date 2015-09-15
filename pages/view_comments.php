<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"]) OR !isset($_REQUEST["id"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
$user = new User($_SESSION["user"]);
$post = new QuickDiscussion();
$post->load($_REQUEST["id"]);
if (!$post->id) die();

if (isset($_REQUEST["start"])) $start = $_REQUEST["start"];
else $start = 0;
$numPerPage_default = 5;
$numPerPage = $numPerPage_default;
if (isset($_REQUEST["comment_numperpage"]) AND ($_REQUEST["comment_numperpage"] != 0)) $numPerPage = $_REQUEST["comment_numperpage"];
if (isset($_REQUEST["numPerPage"]) AND $_REQUEST["numPerPage"] != 0) $numPerPage = $_REQUEST["numPerPage"];
elseif (isset($_REQUEST["numPerPage"]) AND $_REQUEST["numPerPage"] == 0) $numPerPage = $numPerPage_default;
setcookie("comment_numperpage",$numPerPage,0,"/");
$numPerPage_array = array();
$i = $numPerPage_default;
while ($i < $post->comment_count){
	$numPerPage_array[] = $i;
	$i += $numPerPage_default;
}

if ($start == 0) $current_page = 1;
else $current_page = ($start / $numPerPage) + 1;
$total_comments = mysql_num_rows(mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` = '{$post->id}'"));
$last_page = ceil($total_comments / $numPerPage);

$comment_sort = "DESC";
if (!isset($_REQUEST["comment_sort_method"])){
	$i = mysql_fetch_object(mysql_query("SELECT * FROM `users_prefs` WHERE `option` = 'comments_sort'"));
	$comment_sort = $i->{$user->username};
}
if (isset($_REQUEST["commentsort"]) AND $_REQUEST["commentsort"] == "ASC"){
	$comment_sort = "ASC";
	$i = mysql_fetch_object(mysql_query("SELECT * FROM `users_prefs` WHERE `option` = 'comments_sort'"));
	if ($i->{$user->username} != $comment_sort) mysql_query("UPDATE `users_prefs` SET `{$user->username}` = 'ASC' WHERE `option` = 'comments_sort'");
}
elseif (isset($_REQUEST["commentsort"]) AND $_REQUEST["commentsort"] == "DESC"){
	$comment_sort = "DESC";
	$i = mysql_fetch_object(mysql_query("SELECT * FROM `users_prefs` WHERE `option` = 'comments_sort'"));
	if ($i->{$user->username} != $comment_sort) mysql_query("UPDATE `users_prefs` SET `{$user->username}` = 'DESC' WHERE `option` = 'comments_sort'");
}
setcookie("comment_sort_method",$comment_sort,0,"/");

require_once("{$_SESSION["root_path"]}/scripts_each_page.php");

$result = mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` = '{$post->id}' ORDER BY `last_touched` {$comment_sort}, `edited` {$comment_sort}, `created` {$comment_sort} LIMIT {$start}, {$numPerPage}");
$comment_count = mysql_num_rows($result);
if ($comment_count > 0):
?>
<script type="text/javascript">	
	function load_comment_page(id,start,numPerPage){
		numPerPage = typeof numPerPage !== "undefined" ? numPerPage : <?php echo $numPerPage; ?>;
		$("#comments_loading").fadeIn();
		$("#comments_area").slideUp("fast",function(){
			$("#comments_area").load("pages/view_comments.php?id=" + id + "&start=" + start<?php if (isset($_REQUEST["commentsort"]) AND $_REQUEST["commentsort"] == "ASC") echo " + \"&commentsort=ASC\""; elseif(isset($_REQUEST["commentsort"]) AND $_REQUEST["commentsort"] == "DESC") echo " + \"&commentsort=DESC\""; ?> + "&numPerPage=" + numPerPage,function(){
				$("#comments_area").slideDown("fast");
				$("#comments_loading").fadeOut();
			});
		});
	}
</script>
<h2 style="margin: 10px 0;"><img src="files/site_images/layout/comment-32.png" style="vertical-align: middle;"/>
	<span style="vertical-align: middle;"> <?php echo translate("Comment","Phản hồi"); ?> </span>
	<span style="text-align: left; font-size: 0.6em; font-style: italic; text-align: center;">
		<select style="margin: 5px;" onChange="javascript: changeSort(this.options[this.selectedIndex].value);">
			<?php
			if ($comment_sort == "ASC") echo "<option selected>" . translate("Display comments from oldest to newest","Xếp phản hồi từ củ tới mới") . "</option>";
			else echo "<option value=\"ASC\">" . translate("Display comments from oldest to newest","Xếp phản hồi từ củ tới mới") . "</option>";
			if ($comment_sort == "DESC") echo "<option selected>" . translate("Display comments from newest to oldest","Xếp phản hồi từ mới tới củ") . "</option>";
			else echo "<option value=\"DESC\">" . translate("Display comments from newest to oldest","Xếp phản hồi từ mới tới củ") . "</option>";
			?>
		</select>
		<script type="text/javascript">
		function changeSort(m){
			if (m === "") return false;
			$("#comments_loading").fadeIn();
			$("#comments_area").slideUp("fast",function(){
				$("#comments_area").load("pages/view_comments.php?id=<?php echo $post->id; ?>&start=<?php echo $start; ?>&commentsort=" + m,function(){
					$("#comments_area").slideDown("fast");
					$("#comments_loading").fadeOut();
				});
			});
		}
		</script>
	</span>
	<?php if (!isMobile()): ?>
	<span style="text-align: left; font-size: 0.6em; font-style: italic; text-align: center;">
		<strong><?php echo translate("Comments per page","Số phản hồi mổi trang"); ?>:</strong>
		<select style="margin: 5px;" onChange="javascript: changeNumPerPage(this.options[this.selectedIndex].value);">
			<?php
			foreach($numPerPage_array as $a){
				if ($a == $numPerPage) echo "<option selected>{$a}</option>";
				else echo "<option value=\"{$a}\">{$a}</option>";
			}
			if ($numPerPage == $post->comment_count) echo "<option selected>".translate("All","Tất cả")."</option>";
			else echo "<option value=\"{$post->comment_count}\">".translate("All","Tất cả")."</option>";
			?>
		</select>
		<script type="text/javascript">
		function changeNumPerPage(m){
			if (m === "") return false;
			load_comment_page(<?php echo $post->id; ?>,<?php echo $start; ?>,m);
		}
		</script>
	</span>
	<?php endif; ?>
</h2>
<div>
	<?php
	echo "<div style=\"font-size: 0.8em; clear: both;\">";
	if ($start >= $numPerPage):
		$newStart = $start - $numPerPage;
		echo "<button id='top_comment_previous_button' type=\"button\" class=\"button\" style=\"font-size: 0.9em;\">« ".translate("Previous page","Trang trước")."</button>";
		echo "<script type='text/javascript'> $('#top_comment_previous_button').click(function(){ load_comment_page('{$post->id}','{$newStart}'); }); </script>";
	endif;
	echo "<label name=\"top_comment_current_page\" for=\"top_comment_current_page\">".translate("Page ","Trang ")."</label><input id=\"top_comment_current_page\" name=\"top_comment_current_page\" type=\"text\" value=\"{$current_page}\" size=\"2\" style=\"font-size: 0.9em; text-align: center;\"/>".translate(" of "," trên ").($current_page < $last_page ? "<a id='top_comment_last_page_link'>{$last_page}</a>" : "{$last_page}");
	echo "<script type=\"text/javascript\">
		$('#top_comment_last_page_link').click(function(){ load_comment_page('{$post->id}','".(($last_page-1)*$numPerPage)."'); });
		$('#top_comment_current_page').change(function(){
			if (($(this).val() > 0 && $(this).val() <= {$last_page}) && $(this).val() != {$current_page}){
				var newStart = ($(this).val() - 1) * {$numPerPage};
				load_comment_page('{$post->id}',newStart);
			}
		});
	</script>";
	if ($current_page < $last_page):
		$newStart = $start + $numPerPage;
		echo "<button id='top_comment_next_button' type=\"button\" class=\"button\" style=\"font-size: 0.9em;\">".translate("Next page","Trang kế")." »</button>";
		echo "<script type='text/javascript'> $('#top_comment_next_button').click(function(){ load_comment_page('{$post->id}','{$newStart}'); }); </script>";
	endif;
	if ($start >= $numPerPage) echo "<div style='margin: 10px 0;'><a id='top_comment_first_page_link'>" . translate("Return to first page","Trở về trang nhất") . "</a></div>
	<script type='text/javascript'> $('#top_comment_first_page_link').click(function(){ load_comment_page('{$post->id}',0); }); </script>";
	echo "</div>";
	
	$minWidth = 100; // in percentage
	$maxWidth = 100; // in percentage
	$widthIncrement = ($maxWidth - $minWidth)/$comment_count;
	$commentWidth = ($comment_sort == "ASC" ? $maxWidth : $minWidth); // in percentage
	while ($row = mysql_fetch_object($result)){
		$comment = new QuickDiscussion();
		$comment->load($row->id);
		$comment->mark_read();
		?>
		<table cellspacing="5" cellpadding="0" border="0" style="width: <?php echo $commentWidth; ?>%; margin: 10px 0;"><tr><td valign="top">
			<?php echo $comment->qd_author->display_user_post_image(); ?>
			</td><td class="wrap">
				<?php
				$vid_query = mysql_query("SELECT * FROM `videos_general` WHERE `attachedto` = 'quick_discussions:{$comment->id}'");
				$image_query = mysql_query("SELECT * FROM `images_general` WHERE `attachedto` = 'quick_discussions:{$comment->id}'");
				$music_query = mysql_query("SELECT * FROM `music_general` WHERE `attachedto` = 'quick_discussions:{$comment->id}'");
				$file_query = mysql_query("SELECT * FROM `files_general` WHERE `attachedto` = 'quick_discussions:{$comment->id}'");
				
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
				
				if (mysql_num_rows($image_query) > 0):
					echo "<fieldset style='display: inline-block;'><legend>".translate("Images","Hình")."</legend>";
					$gallery_rand = rand();
					while ($img_row = mysql_fetch_object($image_query)){
						echo "<a rel='gallery_{$gallery_rand}' class='post_img' href='get_file.php?type=images_general&id={$img_row->id}'><img style='margin: 5px;' src='get_file.php?type=images_general&id={$img_row->id}&thumbnail=true' height='50'/></a>";
					}
					echo "</fieldset>";
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
					echo "<fieldset style='display: inline-block;'><legend>".translate("Music","Nhạc")."</legend>";
					while ($music = mysql_fetch_object($music_query)){
						echo "<div style='clear: both;'><div id='audioplayer_{$comment->id}{$music->id}'></div><script type='text/javascript'> AudioPlayer.embed('audioplayer_{$comment->id}{$music->id}', {soundFile: \"".$music->file_location."\", autostart: \"no\"}); </script><div><img src='files/site_images/layout/music-16.png' style='vertical-align: middle;'/>&nbsp;<a href='get_file.php?type=music_general&id={$music->id}'>".translate("Click here to download this music file","Bấm đây để tải tư liệu nhạc này")."...</a></div></div>";
					}
					echo "</fieldset>";
				endif;
				
				echo "<div id=\"discussion{$comment->id}body\" style=\"text-align: left; padding: 5px; clear: both;\">{$comment->body}</div>";
				echo "<div id=\"discussion{$comment->id}link_to_this\" style=\"clear: both;\"><img src=\"files/site_images/layout/link-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;";
				echo translate("Link to this discussion","Liên kết tới bài này") . ":&nbsp;";
				echo "<input id=\"discussion{$comment->id}link_code\" style=\"margin: 0; font-size: 0.8em; font-family: monospace;\" value=\"!l[{$comment->id}:::".shorten_string($comment->title,5)."]\" readonly=\"readonly\"/>";
				echo "<script type=\"text/javascript\">
					$('#discussion{$comment->id}link_code').click(function(){
						$(this).select();
					});
					$(function(){
						var link_input = $('#discussion{$comment->id}link_code');
						link_input.attr('size',link_input.val().length);
						link_input.qtip({
							content: { text: '".translate("Copy this line into your discussion body to create a link to this post","Chép hàng này vào nội dung bài để tạo liên kết đến bài này").".' },
							position: { my: 'bottom center', at: 'top center' }
						})
					});
				</script>";
				echo "</span></div>";
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
							
							if ($comment->author == $user->username OR $user->role > 0):
								echo "<div id=\"comment{$comment->id}edit\"><img src=\"files/site_images/layout/pencil-16.png\" style=\"vertical-align: middle;\"/>";
								echo "<span style=\"vertical-align: middle;\">&nbsp;<a href='javascript:;' onClick=\"javascript: load_page('edit_discussion.php?id={$comment->id}&postid={$post->id}');\" target='_self'>".translate("Edit this post","Soạn lại bài này")."</a></span>";
								echo "</div>";
							endif;
							
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
													load_page('view_single_discussion.php?id={$post->id}');
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
		<?php
		if ($comment_sort == "ASC") $commentWidth -= $widthIncrement;
		else $commentWidth += $widthIncrement;
	}
	
	echo "<div style=\"font-size: 0.8em; clear: both;\">";
	if ($start >= $numPerPage) echo "<a id='bottom_comment_first_page_link'>" . translate("Return to first page","Trở về trang nhất") . "</a><br/>
	<script type='text/javascript'>
		$('#bottom_comment_first_page_link').click(function(){
			$('#comments_area').load('pages/view_comments.php?id={$post->id}');
		});
	</script>";
	if ($start >= $numPerPage):
		$newStart = $start - $numPerPage;
		echo "<button id='bottom_comment_previous_button' type=\"button\" class=\"button\" style=\"font-size: 0.9em;\">« ".translate("Previous page","Trang trước")."</button>";
		echo "<script type='text/javascript'>
			$('#bottom_comment_previous_button').click(function(){
				$('#comments_area').load('pages/view_comments.php?id={$post->id}&start={$newStart}');
			});
		</script>";
	endif;
	echo "<label name=\"bottom_comment_current_page\" for=\"bottom_comment_current_page\">".translate("Page ","Trang ")."</label><input id=\"bottom_comment_current_page\" name=\"bottom_comment_current_page\" type=\"text\" value=\"{$current_page}\" size=\"2\" style=\"font-size: 0.9em; text-align: center;\"/>".translate(" of "," trên ").($current_page < $last_page ? "<a id='bottom_comment_last_page_link'>{$last_page}</a>" : "{$last_page}");
	echo "<script type=\"text/javascript\">
		$('#bottom_comment_last_page_link').click(function(){
			$('#comments_area').load('pages/view_comments.php?id={$post->id}&start=".(($last_page-1)*$numPerPage)."');
		});
		$('#bottom_comment_current_page').change(function(){
			if (($(this).val() > 0 && $(this).val() <= {$last_page}) && $(this).val() != {$current_page}){
				var newStart = ($(this).val() - 1) * {$numPerPage};
				$('#comments_area').load('pages/view_comments.php?id={$post->id}&start=' + newStart);
			}
		});
	</script>";
	if ($current_page < $last_page):
		$newStart = $start + $numPerPage;
		echo "<button id='bottom_comment_next_button' type=\"button\" class=\"button\" style=\"font-size: 0.9em;\">".translate("Next page","Trang kế")." »</button>";
		echo "<script type='text/javascript'>
			$('#bottom_comment_next_button').click(function(){
				$('#comments_area').load('pages/view_comments.php?id={$post->id}&start={$newStart}');
			});
		</script>";
	endif;
	echo "</div>";
	?>
</div>
<?php
endif;
?>