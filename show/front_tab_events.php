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
	<?php
	$result = mysql_query("SELECT * FROM `users`");
	$bds_this_month = array();
	$bds_next_month = array();
	while ($row = mysql_fetch_object($result)){
		list($date,$time) = explode(" ",$row->dob);
		list($year,$month,$day) = explode("-",$date);
		if (strtotime(date("Y")."-{$month}") == strtotime(date("Y-m")))
			$bds_this_month[] = array(date("Y")."-{$month}-{$day}",$row->username);
		if (strtotime(date("Y",strtotime("next month"))."-{$month}") == strtotime(date("Y-m",strtotime("next month"))))
			$bds_next_month[] = array(date("Y",strtotime("next month"))."-{$month}-{$day}",$row->username);
	}
	sort($bds_this_month);
	sort($bds_next_month);
?>
	<div class="box_title"><img src="files/site_images/layout/birthday-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Birthdays this month","Sinh nhật trong tháng"); ?></span></div>
	<div style="margin-left: <?php echo (isMobile() ? "10" : "38"); ?>px;">
	<?php
		$count = 0;
		foreach($bds_this_month as $bd){
			$theOne = new User($bd[1]);
			$bdThisYear = $bd[0] . " 00:00:00";
			?>
			<div><img src="files/site_images/layout/user-16.png" style="vertical-align: middle;"/><strong style="vertical-align: middle;"> <span style="border-bottom: 1px solid #<?php echo (empty($theOne->color) ? "FFF" : $theOne->color); ?>;"><a onClick="load_page('profile.php?who=<?php echo $theOne->username; ?>');"><?php echo $theOne->name; ?></a></span> (<?php echo (date_diff_short($theOne->dob,$bdThisYear,true,true) > 1 ? date_diff_short($theOne->dob,$bdThisYear,true,true) . " " . translate("years old","tuổi") : date_diff_short($theOne->dob,$bdThisYear,true,true) . " " . translate("year old","tuổi")); ?>)</strong><span style="font-size: 0.9em;"> <?php echo get_date($bd[0]." 00:00:00"); ?></span></div>
			<?php
			$count++;
		}
		if ($count == 0)
			echo "<div><em>".translate("No birthday this month","Không sinh nhật tháng nay")."</em></div>";
	?>
	</div>
</div>
<?php if (!isMobile()): ?>
</td><td style="text-align: left; vertical-align: top;" valign="top">
<?php else: ?>
</div>
<div style="text-align: left; vertical-align: middle;">
<?php endif; ?>
	<div class="box_title"><img src="files/site_images/layout/birthday_next-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Birthdays next month","Sinh nhật trong tháng tới"); ?></span></div>
	<div style="margin-left: <?php echo (isMobile() ? "10" : "38"); ?>px;">
	<?php
		$count = 0;
		foreach($bds_next_month as $bd){
			$theOne = new User($bd[1]);
			$bdThisYear = $bd[0] . " 00:00:00";
			?>
			<div><img src="files/site_images/layout/user-16.png" style="vertical-align: middle;"/><strong style="vertical-align: middle;"> <span style="border-bottom: 1px solid #<?php echo (empty($theOne->color) ? "FFF" : $theOne->color); ?>;"><a onClick="load_page('profile.php?who=<?php echo $theOne->username; ?>');"><?php echo $theOne->name; ?></a></span> (<?php echo (date_diff_short($theOne->dob,$bdThisYear,true,true) > 1 ? date_diff_short($theOne->dob,$bdThisYear,true,true) . " " . translate("years old","tuổi") : date_diff_short($theOne->dob,$bdThisYear,true,true) . " " . translate("year old","tuổi")); ?>)</strong> <span style="font-size: 0.9em;"><?php echo get_date($bd[0]." 00:00:00"); ?></span></div>
			<?php
			$count++;
		}
		if ($count == 0)
			echo "<div><em>".translate("No birthday next month","Không sinh nhật tháng tới")."</em></div>";
	?>
	</div>
<?php if (!isMobile()): ?>
</td></tr><tr><td style="text-align: left; vertical-align: top;" valign="top">
<?php else: ?>
</div>
<div style="text-align: left; vertical-align: middle;">
<?php endif; ?>
	<?php
	$result = mysql_query("SELECT * FROM `events`");
	$events_this_month = array();
	$events_next_month = array();
	$insert = false;
	while ($row = mysql_fetch_object($result)){
		switch($row->repeat){
			case -1:
				$string = $row->repeat_special . " " . date("Y");
				//$string = str_replace("of ","",$string); // PHP 5.2 doesn't deal with 'of' in the relative date format
				$date_this_year = date("Y-m-d H:i:s",strtotime($string));
				$insert = true;
				break;
			case 0:
				$date_this_year = $row->when;
				$insert = true;
				break;
			case 1:
				$date_this_year = get_next_date($row->when);
				$insert = true;
				break;
			case 2:
				// no need right now
				break;
			case 3:
				// no need right now
				break;
		}
		if ($insert){
			list($date,$time) = explode(" ",$date_this_year);
			list($year,$month,$day) = explode("-",$date);
			if (strtotime("{$year}-{$month}") == strtotime(date("Y-m")))
				$events_this_month[] = array(date("Y")."-{$month}-{$day}",$row->id);
			if (strtotime("{$year}-{$month}") == strtotime(date("Y-m",strtotime("next month"))))
				$events_next_month[] = array(date("Y",strtotime("next month"))."-{$month}-{$day}",$row->id);
			$insert = false;
		}
	}
	sort($events_this_month);
	sort($events_next_month);
?>
	<div class="box_title"><img src="files/site_images/layout/calendar-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Events this month","Lể lộc trong tháng"); ?></span></div>
	<div style="margin-left: <?php echo (isMobile() ? "10" : "38"); ?>px;">
		<?php
		$age = false;
		foreach ($events_this_month as $event){
			$theEvent = mysql_fetch_object(mysql_query("SELECT * FROM `events` WHERE `id` = '{$event[1]}'"));
			$theEventThisYear = $event[0] . " 00:00:00";
			switch($theEvent->category){
				case "misc":
				default:
					$event_image = "/files/site_images/layout/calendar-16.png";
					break;
				case "holiday_f":
				case "holiday_v":
					$event_image = "/files/site_images/layout/mistletoe-16.png";
					break;
				case "get_together":
					$event_image = "/files/site_images/layout/get_together-16.png";
					break;
				case "anniversary":
					$age = true;
					$event_image = "/files/site_images/layout/anniversary-16.png";
					break;
			}
			echo "<div><img src='{$event_image}' style='vertical-align: middle;'/><span style='vertical-align: middle;'> <strong>{$theEvent->name}".($age ? " (" . (date_diff_short($theEvent->when,$theEventThisYear,true,true) > 1 ? date_diff_short($theEvent->when,$theEventThisYear,true,true) . " " . translate("years","năm") : date_diff_short($theEvent->when,$theEventThisYear,true,true) . " " . translate("year","năm")) . ")" : "")."</strong> <span style='font-size: 0.9em;'>".get_date($event[0] . " 00:00:00")."</span></span></div>";
			$age = false;
		}
		?>
	</div>
<?php if (!isMobile()): ?>
</td><td style="text-align: left; vertical-align: top;" valign="top">
<?php else: ?>
</div>
<div style="text-align: left; vertical-align: middle;">
<?php endif; ?>
	<div class="box_title"><img src="files/site_images/layout/calendar2-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Events next month","Lể lộc tháng tới"); ?></span></div>
	<div style="margin-left: <?php echo (isMobile() ? "10" : "38"); ?>px;">
		<?php
		$age = false;
		foreach ($events_next_month as $event){
			$theEvent = mysql_fetch_object(mysql_query("SELECT * FROM `events` WHERE `id` = '{$event[1]}'"));
			$theEventThisYear = $event[0] . " 00:00:00";
			switch($theEvent->category){
				case "misc":
				default:
					$event_image = "/files/site_images/layout/calendar-16.png";
					break;
				case "holiday_f":
					$event_image = "/files/site_images/layout/mistletoe-16.png";
					break;
				case "holiday_v":
					$event_image = "/files/site_images/layout/shamrock-16.png";
					break;
				case "get_together":
					$event_image = "/files/site_images/layout/get_together-16.png";
					break;
				case "anniversary":
					$age = true;
					$event_image = "/files/site_images/layout/anniversary-16.png";
					break;
			}
			echo "<div><img src='{$event_image}' style='vertical-align: middle;'/><span style='vertical-align: middle;'> <strong>{$theEvent->name}".($age ? " (" . (date_diff_short($theEvent->when,$theEventThisYear,true,true) > 1 ? date_diff_short($theEvent->when,$theEventThisYear,true,true) . " " . translate("years","năm") : date_diff_short($theEvent->when,$theEventThisYear,true,true) . " " . translate("year","năm")) . ")" : "")."</strong> <span style='font-size: 0.9em;'>".get_date($event[0] . " 00:00:00")."</span></span></div>";
			$age = false;
		}
		?>
	</div>
<?php if (!isMobile()): ?>
</td></tr></table>
<?php else: ?>
</div>
<?php endif; ?>