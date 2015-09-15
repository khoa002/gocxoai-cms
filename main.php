<?php
if (isset($_SERVER['HTTP_USER_AGENT']) &&  (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
	die("Internet Explorer not supported, please use a better browser; like <a href='http://www.mozilla.org/en-US/firefox/fx/#desktop'>Firefox</a> or <a href='https://www.google.com/intl/en/chrome/browser/'>Chrome</a>. Internet Explorer không được hỗ trợ xin vui lòng sử dụng <a href='http://www.mozilla.org/en-US/firefox/fx/#desktop'>Firefox</a> hoặc <a href='https://www.google.com/intl/en/chrome/browser/'>Chrome</a>.");
}

session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: index.php".(isset($queries) ? "?{$queries}" : "")); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");

$user = new User($_SESSION["user"]);
/* Setting language session if the user is logged in or if the
 * language is passed. */
$language_array = array("en","vi");
$_SESSION["language"] = $user->language;
if (isset($_GET["language"]) AND in_array($_GET["language"],$language_array)){
    $_SESSION["language"] = $_GET["language"];
    $user->set_language($_GET["language"]);
}

// setting default user options if they're not set
mysql_add_column("users_prefs",$user->username,"VARCHAR( 32 ) NOT NULL");
if (mysql_num_rows(mysql_query("SELECT * FROM `users_prefs` WHERE `option` = 'comments_sort' AND `{$user->username}` = ''")) > 0) mysql_query("UPDATE `users_prefs` SET `{$user->username}` = 'ASC' WHERE `option` = 'comments_sort'");
mysql_add_column("quick_discussions_read_status",$user->username,"DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00'");

$global_errors = array(); // this array holds any arrays that should be outputted to the current user
$max_width = (isMobile() ? "98%" : "90%"); // the maximum width of the content (in px)
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="<?php echo $_SESSION["language"]; ?>">
    <head>
        <title><?php echo translate("Home page","Trang nhà"); ?> | Gốc Xoài</title>
        <?php require_once("hscripts.php"); ?>
        <script type="text/javascript">
        AudioPlayer.setup(
            "scripts/audio-player/player.swf",
            {   width: "100%",
                transparentpagebg: "yes",
                initialvolume: 100,
                animation: "no",
                leftbg: "8dc63f",
                lefticon: "006633",
                volslider: "006633",
                voltrack: "003333",
                rightbg: "8dc63f",
                rightbghover: "c4df9b",
                righticon: "006633",
                righticonhover: "ffff00",
                loader: "009900",
                track: "c4df9b",
                tracker: "7cc576",
                border: "006633",
                skip: "006633",
                text: "006633",
                bg: "c4df9b",
                autostart: "<?php echo isset($_REQUEST["autostart"]) ? "yes" : "no"; ?>"
            }
        );
        $(function(){
            // qTip crap
            $.fn.qtip.defaults.position.my = "center left";
            $.fn.qtip.defaults.position.at = "center right";
            $.fn.qtip.defaults.style.classes = "ui-tooltip-green ui-tooltip-shadow";
			
			$(".qtip_me").qtip();
            // end qTip crap

            page = "front.php"; // default home page
			<?php
				if (isset($_REQUEST["page"])) {
					if (isset($queries)) $page_queries = str_replace("page={$_REQUEST["page"]}","",$queries);
					if (file_exists("{$_SESSION["root_path"]}/pages/{$_REQUEST["page"]}.php")) echo "page = \"{$_REQUEST["page"]}.php".(isset($page_queries) ? "?{$page_queries}" : "")."\";";
					else {
						echo "alert(\"".translate("The request page doesn't exist ({$_REQUEST["page"]}). Returning you to the last page.","Trang được yêu không tồn tại ({$_REQUEST["page"]}). Hệ thống sẽ đưa " . $user->you . " về trang vừa qua.")."\"); window.history.back(); return false;";
					}
				}
			?>
			load_page(page);
        });
		
		var doPushState = true;
		
		function load_page(page){
			if (window.doPushState){
				var pageName = page.split(".");
				pageName = pageName[0];
				var stateUrl = "main.php?page=" + pageName;
				var a = page.split("?");
				if (typeof a[1] != "undefined") var pageQueries = a[1];
				if (typeof pageQueries != "undefined") stateUrl += "&" + pageQueries;
				stateUrl = stateUrl.replace("&&","&");
				window.history.pushState("","",stateUrl);
			} else window.doPushState = true;

            var fade_speed = 250;
			$("#content_loading").animate({opacity: 1},fade_speed);
			$("#content").fadeOut(fade_speed);
			$("#content").load("pages/" + page,function(){
				$("#content_loading").animate({opacity: 0},fade_speed);
				$("#content").fadeIn(fade_speed);
			});
        }
		function silent_load(page){
            var fade_speed = 250;
			$("#content_loading").animate({opacity: 1},fade_speed);
			$("#content").fadeOut(fade_speed);
			$("#loadframe").load(page,function(){
				$("#content_loading").animate({opacity: 0},fade_speed);
			});
		}
		window.onpopstate = function(event) {
			var queries = location.search;
			queries = queries.replace("?","");
			var queries2 = queries.split("&");
			for (var i = 0; i < queries2.length; i++){
				var query_array = queries2[i].split("=");
				var name = query_array[0];
				var value = query_array[1];
				if (name == "page"){
					var the_page = value;
					break;
				}
			}
			if (typeof the_page != "undefined"){
				queries2 = queries.replace("page=" + the_page + "&","");
				queries2 = queries2.replace("page=" + the_page,"");
				var page = the_page + ".php";
				if (queries2 != "") page += "?" + queries2;
				window.doPushState = false;
				load_page(page);
			}
		};
        </script>
		<?php if (isMobile()): ?>
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; minimum-scale=1.0; user-scalable=0;" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<?php endif; ?>
    </head>
    <body id="body">
		<div id="page_wrap" style="width: <?php echo $max_width; ?>; margin: 0 auto;">
			<div id="content_loading" style="clear: both; margin: 5px; vertical-align: middle;"><img src='files/site_images/layout/loading-circle-16.gif' style='vertical-align: middle;'/><span style="vertical-align: middle;"> <?php echo translate("Please wait","Xin chờ giây lát"); ?>... </span><img src='files/site_images/layout/loading-circle-16.gif' style='vertical-align: middle;'/></div>
			<div id="content" style="clear: both; padding: 10px 0;"></div>
		</div>
		<div id="loadframe" style="display: none;"></div>
    </body>
</html>