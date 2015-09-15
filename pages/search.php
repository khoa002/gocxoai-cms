<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"]) OR !isset($_REQUEST["q"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
require_once("{$_SESSION["root_path"]}/page_top.php");
$user = new User($_SESSION["user"]);
$user->set_last_seen("search:{$_REQUEST["q"]}");
if (is_numeric($_REQUEST["q"])):
	$result = mysql_query("SELECT * FROM `quick_discussions` WHERE `id` = '{$_REQUEST["q"]}'");
	if (mysql_num_rows($result) > 0):
		echo "<script type='text/javascript'> load_page('view_single_discussion.php?id={$_REQUEST["q"]}'); </script>";
	else:
		echo "<p>".translate("No post found...","Không tìm được bài nào...")."</p>";
	endif;
else:
	$search_query = addslashes(strip_tags($_REQUEST["q"]));
	$result = mysql_query("SELECT * FROM `quick_discussions` WHERE `title` LIKE '%{$search_query}' OR `body` LIKE '%{$search_query}%' ORDER BY `last_touched` DESC");
	if (mysql_num_rows($result) === 0):
		echo "<p>".translate("No post found...","Không tìm được bài nào...")."</p>";
	else:
		echo "<div style='text-align: left;'>
			<strong>".translate(mysql_num_rows($result) . " post(s) found","Tìm được " . mysql_num_rows($result) . " bài").":</strong>
			<ul>";
		while($row = mysql_fetch_object($result)){
			$post = new QuickDiscussion();
			$post->load($row->id);
			$body = shorten_string(strip_tags($post->body),100);
			echo "<li><strong><a onClick=\"javascript: load_page('view_single_discussion.php?id={$post->id}');\">".(empty($post->title) ? "[ ".translate("No title","Không chủ đề")." ]" : $post->title)."</a></strong> ---- <em>{$body}</em></li>";
		}
		echo "	</ul>
		</div>";
	endif;
endif;
?>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>