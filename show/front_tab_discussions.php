<?php
session_start();
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["user"])) die();
require_once("{$_SESSION["root_path"]}/inc.php");
$user = new User($_SESSION["user"]);

$num_items = isMobile() ? 5 : 10;
$months_back = isMobile() ? 1 : 3;
?>
<style type="text/css">
	.box_title{
		font-size: 1.2em;
		font-weight: bold;
		color: #6B8E23;
	}
</style>
<?php if (!isMobile()): ?>
<table width="100%" cellspacing="5" cellpadding="0" border="0" align="center"><tr><td style="text-align: left; vertical-align: top;" valign="top">
<?php else: ?>
<div style="text-align: left; vertical-align: middle; margin: 2px 0;">
<?php endif; ?>
	<div class="box_title"><img src="files/site_images/layout/discussion-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Recent discussions","Thảo luận mới"); ?></span></div>
	<div style="margin-left: <?php echo (isMobile() ? "10" : "38"); ?>px;">
	<?php
	$q = mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` = '0' ORDER BY `created` DESC, `id` DESC LIMIT {$num_items}");
	while ($r = mysql_fetch_object($q)){
		$item = new QuickDiscussion();
		$item->load($r->id);
		echo "<div style=\"vertical-align: middle;\"><img src=\"files/site_images/layout/discussion-16.png\" style=\"vertical-align: middle;\"/>";
		echo "<span style=\"vertical-align: middle;\"> <a onClick=\"javascript: load_page('view_single_discussion.php?id={$item->id}');\">".(!empty($item->title) ? shorten_string($item->title,5) : shorten_string(strip_tags($item->body),5))."</a> </span>";
		echo "<img src=\"files/site_images/layout/user-16.png\" style=\"vertical-align: middle;\"/> <span style=\"font-size: 0.9em; border-bottom: 1px solid #{$item->qd_author->color}; vertical-align: middle;\"><strong><em><a onClick=\"load_page('profile.php?who={$item->qd_author->username}');\">{$item->qd_author->name}</a></em></strong></span> ";
		echo "<span style=\"font-size: 0.9em; vertical-align: middle;\">(".(date_diff_short($item->created) == "0" ? translate("just now","mới đây") : (date_diff_short($item->created) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($item->created)." ".translate("ago","trước"))).")</span>";
		echo "</div>";
	}
	?>
	</div>
</div>
<?php if (!isMobile()): ?>
</td><td style="text-align: left; vertical-align: top;" valign="top">
<?php else: ?>
</div>
<div style="text-align: left; vertical-align: middle;">
<?php endif; ?>
	<div class="box_title"><img src="files/site_images/layout/comment-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Recent comments","Phản hồi mới"); ?></span></div>
	<div style="margin-left: <?php echo (isMobile() ? "10" : "38"); ?>px;">
	<?php
	$q = mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` > '0' ORDER BY `created` DESC, `id` DESC LIMIT {$num_items}");
	while ($r = mysql_fetch_object($q)){
		$item = new QuickDiscussion();
		$item->load($r->id);
		echo "<div style=\"vertical-align: middle;\"><img src=\"files/site_images/layout/comment-16.png\" style=\"vertical-align: middle;\"/>";
		echo "<span style=\"vertical-align: middle;\"> <a onClick=\"javascript: load_page('view_single_discussion.php?id={$item->parent_id}');\">".(!empty($item->title) ? shorten_string($item->title,5) : shorten_string(strip_tags($item->body),5))."</a> </span>";
		echo "<img src=\"files/site_images/layout/user-16.png\" style=\"vertical-align: middle;\"/> <span style=\"font-size: 0.9em; border-bottom: 1px solid #{$item->qd_author->color}; vertical-align: middle;\"><strong><em><a onClick=\"load_page('profile.php?who={$item->qd_author->username}');\">{$item->qd_author->name}</a></em></strong></span> ";
		echo "<span style=\"font-size: 0.9em; vertical-align: middle;\">(".(date_diff_short($item->created) == "0" ? translate("just now","mới đây") : (date_diff_short($item->created) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($item->created)." ".translate("ago","trước"))).")</span>";
		echo "</div>";
	}
	?>
	</div>
<?php if (!isMobile()): ?>
</td><td style="text-align: left; vertical-align: top;" valign="top">
<?php else: ?>
</div>
<div style="text-align: left; vertical-align: middle;">
<?php endif; ?>
	<div class="box_title"><img src="files/site_images/layout/unread-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Unread posts","Bài chưa đọc"); ?></span></div>
	<div style="margin-left: <?php echo (isMobile() ? "10" : "38"); ?>px;">
	<?php
	$count = 0;
	$q = mysql_query("SELECT * FROM `quick_discussions` WHERE `last_touched` > '".gmdate("Y-m-d H:i:s",strtotime("-{$months_back} month"))."' ORDER BY `last_touched` DESC , `id` DESC");
	$parents_already_shown = array();
	while ($r = mysql_fetch_object($q)){
		if ($count == $num_items) break;
		$item = new QuickDiscussion();
		$item->load($r->id);
		if (in_array($item->id,$parents_already_shown) OR in_array($item->parent_id,$parents_already_shown)) continue;
		$show = false;
		$user_read_status = mysql_query("SELECT `{$user->username}` FROM `quick_discussions_read_status` WHERE `postid` = '{$r->id}' AND `{$user->username}` > '1970-01-01 00:00:00'");
		if (mysql_num_rows($user_read_status) == 0) $show = true;
		else {
			$a = mysql_fetch_object($user_read_status);
			if ((strtotime($a->{$user->username}) < strtotime($r->last_touched))) $show = true;
		}
		if ($show === true){
			$item_id = ($item->parent_id == 0 ? $item->id : $item->parent_id);
			echo "<div style=\"vertical-align: middle;\"><img src=\"".($item->parent_id == 0 ? "files/site_images/layout/discussion-16.png" : "files/site_images/layout/comment-16.png")."\" style=\"vertical-align: middle;\"/>";
			echo "<span style=\"vertical-align: middle;\"> <a onClick=\"javascript: load_page('view_single_discussion.php?id={$item_id}');\">".(!empty($item->title) ? $item->title : shorten_string(strip_tags($item->body),5))."</a></span>";
			echo "</div>";
			if ($item->parent_id == 0) $parents_already_shown[] = $item->id;
			else $parents_already_shown[] = $item->parent_id;
			$count++;
		}
	}
	if ($count == 0)
		echo "<div><em>".translate("No unread post found in the last {$months_back} month" . ($months_back > 1 ? "s" : ""),"Không bài nào chưa đọc trong {$months_back} tháng qua")."</em></div>";
	?>
	</div>
<?php if (!isMobile()): ?>
</td></tr></table>
<?php else: ?>
</div>
<?php endif; ?>