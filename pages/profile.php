<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
$user = new User($_SESSION["user"]);
if (!isset($_REQUEST["who"])) die();
$a = new User($_REQUEST["who"]);

$user->set_last_seen("viewpage:profile:{$a->username}");

require_once("{$_SESSION["root_path"]}/page_top.php");
?>
<script type="text/javascript">
// setting the page title
$(function(){
	top.document.title = "<?php echo translate(ucfirst($a->name)."'s profile","Thông tin của ".$a->name); ?>";
});
</script>
<div class="wrap" style="margin: 5px 0;">
	<fieldset>
		<legend style="font-size: 1.1em;"><img src="files/site_images/layout/user-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo $a->get_full_name(); ?> (<?php echo $a->display_name; ?>)</span></legend>
		
		<div style="margin: 5px;"><?php echo $a->display(); ?></div>
		<div style="margin: 5px; text-align: left;">
			<div><img src="files/site_images/layout/email-16.png" style="vertical-align: middle; margin: 0 5px;"/><span style="vertical-align: middle;"><?php if (empty($a->email)) { echo translate("Unknown","Không biết"); } else { echo "<a href='mailto:{$a->email}'>{$a->email}</a>"; } ?></span></div>
			<div><img src="files/site_images/layout/key-16.png" style="vertical-align: middle; margin: 0 5px;"/><span style="vertical-align: middle;"><?php
			switch($a->role){
				default: echo translate("Member","Thành viên"); break;
				case 99: echo translate("Administrator","Quản trị viên"); break;
				case 1: echo translate("Moderator","Điều hành viên"); break;
			} ?></span></div>
			<div><img src="files/site_images/layout/birthday-16.png" style="vertical-align: middle; margin: 0 5px;"/><span style="vertical-align: middle;"><?php echo get_date($a->dob); ?> (<?php echo date_diff_short($a->dob) . " " . translate("old","tuổi"); ?>)</span></div>
			<div><img src="files/site_images/layout/home-16.png" style="vertical-align: middle; margin: 0 5px;"/><span style="vertical-align: middle;"><?php echo $a->get_address(); ?></span></div>
			<div><img src="files/site_images/layout/phone-16.png" style="vertical-align: middle; margin: 0 5px;"/><span style="vertical-align: middle;"><?php echo $a->get_phone_home(); ?></span></div>
			<div><img src="files/site_images/layout/cellphone-16.png" style="vertical-align: middle; margin: 0 5px;"/><span style="vertical-align: middle;"><?php echo $a->get_phone_cell(); ?></span></div>
		</div>
	</fieldset>
	
	<?php $level = $a->calc_level(); ?>
	<fieldset>
		<legend style="font-size: 1.1em;"><img src="files/site_images/layout/check-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Achievements","Thành tích"); ?></span></legend>
		<div id="<?php echo $a->username; ?>levelbox">
			<div id="progressbar<?php echo $a->username; ?>" title="<?php echo $level["current_percentage_formatted"]; ?>%" style="width: 100%; height: 5px; background: #FFF; border: 1px solid #000; margin: 1px 0px;"><?php echo ($level["current_percentage"] > 0 ? "<div style=\"width:{$level["current_percentage"]}%; height: 5px; background: #{$a->color}; float:left;\"></div>" : "<div>&nbsp;</div>" ); ?></div>
			<script> $("#progressbar<?php echo $a->username; ?>").qtip({position: { my: "top center", at: "bottom center" }, style: { classes: "ui-tooltip-green ui-tooltip-shadow" }}); </script>
			<div style="text-align: left;">
				<?php
				$rank = 0;
				$rank_user_query = mysql_query("SELECT * FROM `users` ORDER BY `exp` DESC");
				$rank_max = mysql_num_rows($rank_user_query);
				while ($rank_user_object = mysql_fetch_object($rank_user_query)){
					$rank++;
					if ($rank_user_object->username == $a->username) break;
				}
				?>
				<span style="font-size: 1.1em; color: #5e8b1d; display: inline-block; vertical-align: middle; margin: 0 5px;"><img src="files/site_images/layout/star-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <strong><?php echo translate("Level","Cấp") . " " . $level["level"]; ?></strong> <span class="qtip_me" title="<?php echo translate("{$a->name} is ranked #{$rank}/{$rank_max} among the members of the site.","{$a->name} được xếp hạng {$rank}/{$rank_max} trong số thành viên của hệ thống."); ?>">(<a onClick="javascript: load_page('userslist.php?who=<?php echo $a->username; ?>&sort=exp___DESC');">#<?php echo $rank; ?></a>)</span></span></span>
				<span style="font-size: 0.9em; display: inline-block; vertical-align: middle; margin: 0 5px;"><?php echo $level["user_exp_formatted"] . " " . translate("total points","tổng số điểm"); ?> &mdash; <?php echo "<strong>" . translate("Next level","Cấp kế") . ":</strong> " . $level["current_exp_formatted"]; ?> / <?php echo $level["current_max_exp_formatted"]; ?> (<?php echo $level["current_percentage_formatted"]; ?>%) &mdash; <em><?php echo $level["current_tnl_formatted"] . " " . translate("points until next level","điểm để nâng cấp"); ?></em></span>
			</div>
		</div>
		<div style="text-align: left; font-size: 0.9em;">
			<?php
			$select = mysql_query("SELECT * FROM `quick_discussions` WHERE `author` = '{$a->username}' AND `parent_id` = '0'");
			$discussion_num = 0;
			$total_discussion_exp = 0;
			while ($row = mysql_fetch_object($select)) { $total_discussion_exp += $row->exp; $discussion_num++; }
			?>
			<ul>
			<?php
			if ($discussion_num > 0){
				$requiredForNextLevel = ceil($level["current_tnl"] / ($total_discussion_exp / $discussion_num));
			?><li><?php echo $a->name . " " . translate("posted {$discussion_num} discussions, earning {$total_discussion_exp} points, averaging about " . round($total_discussion_exp / $discussion_num, 2) . " points per post; about {$requiredForNextLevel} discussions needed to level up!","đã đăng {$discussion_num} bài thảo luận, và được {$total_discussion_exp} điểm, trung bình khoảng " . round($total_discussion_exp / $discussion_num, 2) . " điểm mổi bài; khoảng {$requiredForNextLevel} thảo luần nữa để tăng cấp!"); ?></li>
			<?php }
			$select = mysql_query("SELECT * FROM `quick_discussions` WHERE `author` = '{$a->username}' AND `parent_id` != '0'");
			$comments_num = 0;
			$total_comment_exp = 0;
			while ($row = mysql_fetch_object($select)) { $total_comment_exp += $row->exp; $comments_num++; }
			if ($comments_num > 0){
				$requiredForNextLevel = ceil($level["current_tnl"] / ($total_comment_exp / $comments_num));
			?><li><?php echo $a->name . " " . translate("posted {$comments_num} comments, earning {$total_comment_exp} points, averaging about " . round($total_comment_exp / $comments_num, 2) . " points per post; about {$requiredForNextLevel} comments needed to level up!","đã đăng {$comments_num} bài phản hồi, và được {$total_comment_exp} điểm, trung bình khoảng " . round($total_comment_exp / $comments_num, 2) . " điểm mổi bài; khoảng {$requiredForNextLevel} phản hồi nữa để tăng cấp!"); ?></li>
			<?php }
			$remaining_exp = $a->exp - $total_discussion_exp - $total_comment_exp;
			if ($remaining_exp > 0){ ?>
			<li><?php echo $a->name . " " . translate("acquired {$remaining_exp} points from viewing the pages of the site","đạt {$remaining_exp} điểm từ những lần xem qua các bài của hệ thống"); ?>.</li>
			<?php }
			?>
			</ul>
		</div>
	</fieldset>
	
	<?php
	$num_of_user_activities = mysql_num_rows(mysql_query("SELECT * FROM `users_activities` WHERE `username` = '{$a->username}'"));
	if ($num_of_user_activities > 0):
	?>
	<fieldset>
		<legend style="font-size: 1.1em;"><img src="files/site_images/layout/activity_monitor-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("User activities","Hoạt động của thành viên"); ?></span></legend>
		<ol style="text-align: left; font-size: 0.9em;">
		<?php
		$result = mysql_query("SELECT * FROM `users_activities` WHERE `username` = '{$a->username}' ORDER BY `when` DESC, `id` DESC");
		while ($row = mysql_fetch_object($result)){
			if (get_defined_activity($row->what) !== false) echo "<li>" . get_defined_activity($row->what) . ", " . (date_diff_short($row->when) == 0 ? translate("just now","mới đây") : translate("about","khoảng") . " " . date_diff_short($row->when) . " " . translate("ago","trước")) . ".</li>";
		}
		?>
		</ol>
	</fieldset>
	<?php endif; ?>
</div>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>