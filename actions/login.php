<?php
if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]);
session_start();
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts/securimage/securimage.php");
$securimage = new Securimage();
if (!$securimage->check($_REQUEST["captcha_code"])) die("error_invalid_captcha");
$query = mysql_query("SELECT * FROM `users` WHERE `username` = '{$_POST["username"]}'");
if (mysql_num_rows($query) == 0) die("error_username_not_found");
$salt = mysql_fetch_object(mysql_query("SELECT `value` FROM `system` WHERE `name` = 'salt'"));
if (mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `username` = '{$_POST["username"]}' AND `password` = MD5('{$salt->value}{$_POST["password"]}') AND `active` = '1'")) == 0) { if (mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `username` = '{$_POST["username"]}' AND `password` = MD5('{$_POST["password"]}')")) == 0) die("error_password_not_correct"); }
if ( isset( $_POST["save"] ) ){ $time = time() + (60*60*24*90); }
else { $time = 0; }
$user = new User($_POST["username"]);
setcookie("user",$user->username,$time,"/");
$_SESSION["user"] = $user->username;
$user->set_last_seen("login");
die("done");
?>