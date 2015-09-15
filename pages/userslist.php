<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
$user = new User($_SESSION["user"]);
$user->set_last_seen("viewpage:userslist");

require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
require_once("{$_SESSION["root_path"]}/page_top.php");

$sortby = "exp";
$sortorder = "DESC";
if (isset($_REQUEST["sort"])){
    // 'sort' must be 'name___order' format
    list($sortby,$sortorder) = explode("___",$_REQUEST["sort"]);
}
?>
<script type="text/javascript">
$(function(){
    // setting the page title
    $(function(){ top.document.title = "<?php echo translate("User list","Bảng danh thành viên"); ?>"; });
    // qTip crap
    $.fn.qtip.defaults.position.target = $(".mascot");
    $.fn.qtip.defaults.position.my = "center left";
    $.fn.qtip.defaults.position.at = "center right";
    $.fn.qtip.defaults.style.classes = "ui-tooltip-green ui-tooltip-shadow";
})
</script>
<table class="wrap" width="100%" cellspacing="0" cellpadding="1" border="0" style="font-size: 1em; margin: 10px 0; text-align: left;">
	<tr style="font-size: 1.1em;">
		<th><a onClick="javascript: load_page('userslist.php?<?php echo "sort=display_name___".($sortorder == "ASC" ? "DESC" : "ASC"); ?>');"><img src="files/site_images/layout/user-16.png" style="vertical-align: middle;"/> <span style="vertical-align: middle;"><?php echo translate("Name","Tên"); ?></span></a></th>
		<th><a onClick="javascript: load_page('userslist.php?<?php echo "sort=exp___".($sortorder == "ASC" ? "DESC" : "ASC"); ?>');"><img src="files/site_images/layout/check-16.png" style="vertical-align: middle;"/> <span style="vertical-align: middle;"><?php echo translate("Achievement","Thành tích"); ?></span></a></th>
	</tr>
	<?php
	$result = mysql_query("SELECT * FROM `users` WHERE `active` = '1' ORDER BY `{$sortby}` {$sortorder}");
	$even = false;
	$rank = 1;
	while ($row = mysql_fetch_object($result)){
		$member = new User($row->username);
		$level = $member->calc_level();
		echo "<tr style='font-size: 0.9em; ".((isset($_REQUEST["who"]) AND $_REQUEST["who"] == $member->username) ? " background-color: #c4df9b" : ($even ? " background-color: #eee" : ""))."'>";
		echo "<td><a id='{$member->username}_image' href='get_file.php?type=images_user_avatars&id={$member->username}'><img src='get_file.php?type=images_user_avatars&id={$member->username}&thumbnail=true' width='50' height='50'/></a> <strong><a onClick=\"javascript: load_page('profile.php?who={$member->username}');\">{$rank}. {$member->name}</a></strong>".($member->active == 0 ? "<span style='font-size: 0.7em; font-style: italic;'> * ".translate("inactive","không hoạt động")."</span>":"")."</td>";
		echo "<script type='text/javascript'>$('#{$member->username}_image').fancybox({ openEffect	: 'elastic', closeEffect	: 'elastic', type : 'image', padding : 5, helpers : { title: { type : 'over' } } });</script>";
		echo "<td style='".((isset($_REQUEST["who"]) AND $_REQUEST["who"] == $member->username) ? " background-color: #c4df9b" : ($even ? " background-color: #eee" : ""))."'>{$level["progress_bar_with_info"]}</td>";
		echo "</tr>";
		$even = !$even;
		$rank++;
	}
	?>
</table>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>