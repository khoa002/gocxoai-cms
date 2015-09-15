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
	<div class="box_title"><img src="files/site_images/layout/activity_monitor-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("User activities","Hoạt động thành viên"); ?></span></div>
	<div style="margin-left: <?php echo (isMobile() ? "10" : "38"); ?>px;">
	<?php
	$users_already_displayed = array();
	$a = mysql_query("SELECT * FROM `users_activities` ORDER BY `when` DESC");
	while ($b = mysql_fetch_object($a)){
		if (in_array($b->username,$users_already_displayed)) continue;
		$c = new User($b->username);
		echo "<div style=\"vertical-align: middle;\"><img src=\"files/site_images/layout/activity_monitor-16.png\" style=\"vertical-align: middle;\"/>";
		echo "<span style=\"vertical-align: middle; border-bottom: 1px solid #{$c->color};\"> <strong><a onClick=\"load_page('profile.php?who={$c->username}');\">{$c->name}</a></strong></span>";
		echo "<span style=\"vertical-align: middle;\"> (".(date_diff_short($b->when) == "0" ? translate("just now","mới đây") : (date_diff_short($b->when) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($b->when)." ".translate("ago","trước"))).") - <em>";
		echo get_defined_activity($b->what);
		echo "</em></span></div>";
		$users_already_displayed[] = $c->username;
	}
	// $a = mysql_query("SELECT * FROM `users` WHERE `last_seen` > '0000-00-00 00:00:00' ORDER BY `last_seen` DESC");
	// while ($b = mysql_fetch_object($a)){
		// if (mysql_num_rows(mysql_query("SELECT 1 FROM `users_activities` WHERE `username` = '{$b->username}'")) > 0){
			// $c = new User($b->username);
			// $activity = mysql_fetch_object(mysql_query("SELECT 1 FROM `users_activities` WHERE `username` = '{$c->username}' ORDER BY `when` DESC LIMIT 1"));
			// echo "<div style=\"vertical-align: middle;\"><img src=\"files/site_images/layout/activity_monitor-16.png\" style=\"vertical-align: middle;\"/>";
			// echo "<span style=\"vertical-align: middle; border-bottom: 1px solid #{$c->color};\"> <strong><a onClick=\"load_page('profile.php?who={$c->username}');\">{$c->name}</a></strong></span>";
			// echo "<span style=\"vertical-align: middle;\"> (".(date_diff_short($c->last_seen) == "0" ? translate("just now","mới đây") : (date_diff_short($c->last_seen) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($c->last_seen)." ".translate("ago","trước"))).") - <em>";
			// echo get_defined_activity($c->last_activity);
			// echo "</em></span></div>";
		// }
		// continue;
	// }
	?>
	</div>
</div>
<?php if (!isMobile()): ?>
</td><td style="text-align: left; vertical-align: top;" valign="top">
<?php else: ?>
</div>
<div style="text-align: left; vertical-align: middle;">
<?php endif; ?>
	
<?php if (!isMobile()): ?>
</td><td style="text-align: left; vertical-align: top;" valign="top">
<?php else: ?>
</div>
<div style="text-align: left; vertical-align: middle;">
<?php endif; ?>
	
<?php if (!isMobile()): ?>
</td></tr></table>
<?php else: ?>
</div>
<?php endif; ?>