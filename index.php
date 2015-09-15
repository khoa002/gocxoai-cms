<?php
if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
	die("Internet Explorer not supported, please use a better browser; like <a href='http://www.mozilla.org/en-US/firefox/fx/#desktop'>Firefox</a> or <a href='https://www.google.com/intl/en/chrome/browser/'>Chrome</a>. Internet Explorer không được hỗ trợ xin vui lòng sử dụng <a href='http://www.mozilla.org/en-US/firefox/fx/#desktop'>Firefox</a> hoặc <a href='https://www.google.com/intl/en/chrome/browser/'>Chrome</a>.");
}
session_start();
$_SESSION["root_path"] = $_SERVER["DOCUMENT_ROOT"];
$_SESSION["in"] = true;
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (isset($_SESSION["user"])) { header("Location: main.php".(isset($queries) ? "?{$queries}" : "")); die(); }
else { header("Location: login.php".(isset($queries) ? "?{$queries}" : "")); die(); }
?>