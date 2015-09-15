<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
$user = new User($_SESSION["user"]);
$user->set_last_seen("front_page");

require_once("{$_SESSION["root_path"]}/page_top.php");

$num_items = isMobile() ? 5 : 10;
$months_back = isMobile() ? 1 : 3;
?>
<script type="text/javascript">
$(function(){
	// setting the page title
	top.document.title = "<?php echo translate("Welcome to GCX!","Chào mừng đến với GCX!"); ?>";
	
	$("#tabs").tabs({
		cache:true,
		load: function (e, ui) {
			$(ui.panel).find(".tab-loading").remove();
		},
		select: function (e, ui) {
			var $panel = $(ui.panel);

			if ($panel.is(":empty")) {
				$panel.append("<div class=\"tab-loading\"><div id=\"content_loading\" style=\"clear: both; margin: 5px; vertical-align: middle;\"><img src='files/site_images/layout/loading-circle-16.gif' style='vertical-align: middle;'/><span style=\"vertical-align: middle;\"> <?php echo translate("Please wait","Xin chờ giây lát"); ?>... </span><img src='files/site_images/layout/loading-circle-16.gif' style='vertical-align: middle;'/></div></div>")
			}
		},
		show: function (e, ui) {
			var $panel = $(ui.panel);

			if ($panel.is(":empty")) {
				$panel.append("<div class=\"tab-loading\"><div id=\"content_loading\" style=\"clear: both; margin: 5px; vertical-align: middle;\"><img src='files/site_images/layout/loading-circle-16.gif' style='vertical-align: middle;'/><span style=\"vertical-align: middle;\"> <?php echo translate("Please wait","Xin chờ giây lát"); ?>... </span><img src='files/site_images/layout/loading-circle-16.gif' style='vertical-align: middle;'/></div></div>")
			}
		}
	});
	<?php if (isset($_REQUEST["selectedTab"])) echo "$('#tabs').tabs('option','selected',{$_REQUEST["selectedTab"]});"; ?>
});
</script>
<style type="text/css">
	.box_title{
		font-size: 1.2em;
		font-weight: bold;
		color: #6B8E23;
	}
</style>
<div style="font-size: 1.2em; font-weight: bold; margin: 10px;">
	<span style="display: inline-block;"><a onClick="javascript: load_page('addnew_discussion.php');"><img src="files/site_images/layout/add-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Add new discussion","Đăng thảo luận mới"); ?></span></a></span>
</div>

<div id="tabs">
	<ul>
		<li><a href="show/front_tab_discussions.php"><img src="files/site_images/layout/discussion-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Discussions & comments","Thảo luận & phản hồi"); ?></span></a></li>
		<li><a href="show/front_tab_activities.php"><img src="files/site_images/layout/activity_monitor-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Site's & users' activities","Hoạt động hệ thống & của thành viên"); ?></span></a></li>
		<li><a href="show/front_tab_events.php"><img src="files/site_images/layout/birthday_next-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Birthdays & events","Sinh nhật & sự kiện"); ?></span></a></li>
	</ul>
</div>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>