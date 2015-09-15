<?php
if (!isset($_SESSION["in"]) OR !isset($_SESSION["root_path"]) OR !isset($_SESSION["user"])) { die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
$user = new User($_SESSION["user"]);
$level = $user->calc_level();
?>

<?php if (!isMobile()): ?>
<table align="center" cellpadding="0" cellspacing="3" border="0"><tr><td class="wrap">
<?php else: ?>
<div class="wrap" style="margin: 0 3px;">
<?php endif; ?>
	<style type="text/css"> #site_nav a{ margin: 0 3px; } </style>
	<div id="site_nav" style="vertical-align: middle; font-size: 0.75em; font-weight: bold;<?php echo !isMobile() ? " display: inline-block; float: left; margin: 2px;" : " white-space: nowrap;"; ?>">
		<a title="<?php echo translate("Go back to the main page","Trở về trang chính"); ?>" onClick="javascript: load_page('front.php');" style="display: inline-block; vertical-align: middle;"><img src="files/site_images/layout/home-32.png" style="vertical-align: middle;"/><?php if (!isMobile()) echo "<span style=\"font-size: 1.2em; vertical-align: middle;\"> ".translate("Homepage","Trang nhà")."</span>"; ?></a>
		<a title="<?php echo translate("New discussion","Thảo luận mới"); ?>" onClick="javascript: load_page('addnew_discussion.php');" style="display: inline-block; vertical-align: middle;"><img src="files/site_images/layout/add-32.png" style="vertical-align: middle;"/><?php if (!isMobile()) echo "<span style=\"font-size: 1.2em; vertical-align: middle;\"> ".translate("Add a new discussion","Đăng thảo luận mới")."</span>"; ?></a>
		<a title="<?php echo translate("View the discussions page","Xem trang thảo luận"); ?>" onClick="javascript: load_page('discussions.php?start=0&sortmethod=1&numPerPage=0');" style="display: inline-block; vertical-align: middle;"><img src="files/site_images/layout/discussion-32.png" style="vertical-align: middle;"/><?php if (!isMobile()) echo "<span style=\"font-size: 1.2em; vertical-align: middle;\"> ".translate("Discussions","Thảo luận")."</span>"; ?></a>
		<a title="<?php echo translate("The site's users list","Bảng danh thành viên"); ?>" onClick="javascript: load_page('userslist.php?who=<?php echo $user->username; ?>');" style="display: inline-block; vertical-align: middle;"><img src="files/site_images/layout/contact_list-32.png" style="vertical-align: middle;"/><?php if (!isMobile()) echo "<span style=\"font-size: 1.2em; vertical-align: middle;\"> ".translate("User list","Bảng danh thành viên")."</span>"; ?></a>
		<a title="<?php echo translate("Your control panel and settings for the site","Bảng điều chỉnh và tùy chọn cho hệ thống"); ?>" onClick="javascript: load_page('usercp.php');" style="display: inline-block; vertical-align: middle;"><img src="files/site_images/layout/id-32.png" style="vertical-align: middle;"/><?php if (!isMobile()) echo "<span style=\"font-size: 1.2em; vertical-align: middle;\"> ".translate("Control panel","Bảng điều khiển")."</span>"; ?></a>
		<a title="<?php echo translate("Log out","Thoát khỏi hệ thống"); ?>" onClick="javascript: silent_load('actions/logout.php');" style="display: inline-block; vertical-align: middle;"><img src="files/site_images/layout/logout-32.png" style="vertical-align: middle;"/><?php if (!isMobile()) echo "<span style=\"font-size: 1.2em; vertical-align: middle;\"> ".translate("Log out","Thoát khỏi hệ thống")."</span>"; ?></a>
	</div>
<?php if (!isMobile()): ?></td><td class="wrap">
<?php else: ?>
</div>
<div class="wrap" style="margin: 0 3px;">
<?php endif; ?>
	<form id="search_form" name="search_form"<?php if (!isMobile()) echo " style=\"display: inline-block; float: left; margin: 2px; white-space: nowrap;\""; ?>>
		<input type="text" id="search_query" name="search_query"/>
		<button type="submit" style="font-size: 0.8em;"><?php echo translate("Search","Tìm"); ?></button>
	</form>
	<script type="text/javascript">
		$("#search_form").submit(function(e){
			e.preventDefault();
			if ( (!$.trim($("#search_query").val())) ){
				alert("<?php echo translate("Enter a search term","Điền vào từ để tìm"); ?>");
				$("#search_query").focus();
				return false;
			}
			if ( $("#search_query").val().length < 4 && isNumber($("#search_query").val()) == false ){
				alert("<?php echo translate("Please enter at least 4 characters","Xin điền vào ít nhất 4 ký tự"); ?>");
				return false;
			}
			load_page("search.php?q=" + $("#search_query").val());
		});
	</script>
<?php if (!isMobile()): ?>
</td></tr></table>
<?php else: ?>
</div>
<?php endif; ?>

<div class="wrap" style="margin: 5px auto;">
	<div style="text-align: left;"><strong><?php echo translate("Welcome,","Chào mừng,"); ?> <a onClick="javascript: load_page('profile.php?who=<?php echo $user->username; ?>');"><?php echo $user->name; ?></a>!</strong></div>
	<div id="<?php echo $user->username; ?>levelbox">
		<div id="progressbar<?php echo $user->username; ?>" title="<?php echo $level["current_percentage_formatted"]; ?>%" style="width: 100%; height: 5px; background: #FFF; border: 1px solid #000; margin: 1px 0px;"><?php echo ($level["current_percentage"] > 0 ? "<div style=\"width:{$level["current_percentage"]}%; height: 5px; background: #{$user->color}; float:left;\"></div>" : "<div>&nbsp;</div>" ); ?></div>
		<script> $("#progressbar<?php echo $user->username; ?>").qtip({position: { my: "top center", at: "bottom center" }, style: { classes: "ui-tooltip-green ui-tooltip-shadow" }}); </script>
		<div style="text-align: left;">
			<?php
			$rank = 0;
			$rank_user_query = mysql_query("SELECT * FROM `users` ORDER BY `exp` DESC");
			$rank_max = mysql_num_rows($rank_user_query);
			while ($rank_user_object = mysql_fetch_object($rank_user_query)){
				$rank++;
				if ($rank_user_object->username == $user->username) break;
			}
			?>
			<span style="font-size: 1.1em; color: #5e8b1d; display: inline-block; vertical-align: middle; margin: 0 5px;"><img src="files/site_images/layout/star-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <strong><?php echo translate("Level","Cấp") . " " . $level["level"]; ?></strong> <span class="qtip_me" title="<?php echo translate("You are ranked #{$rank}/{$rank_max} among the members of the site.","{$user->you} được xếp hạng {$rank}/{$rank_max} trong số thành viên của hệ thống."); ?>">(<a onClick="javascript: load_page('userslist.php?who=<?php echo $user->username; ?>&sort=exp___DESC');">#<?php echo $rank; ?></a>)</span></span></span>
			<span style="font-size: 0.9em; display: inline-block; vertical-align: middle; margin: 0 5px;"><?php echo $level["user_exp_formatted"] . " " . translate("total points","tổng số điểm"); ?> &mdash; <?php echo "<strong>" . translate("Next level","Cấp kế") . ":</strong> " . $level["current_exp_formatted"]; ?> / <?php echo $level["current_max_exp_formatted"]; ?> (<?php echo $level["current_percentage_formatted"]; ?>%) &mdash; <em><?php echo $level["current_tnl_formatted"] . " " . translate("points until next level","điểm để nâng cấp"); ?></em></span>
		</div>
	</div>
</div>