<?php
if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]);
session_start();
require_once("{$_SESSION["root_path"]}/inc.php");
$user = new User ( $_SESSION["user"] );
$user->set_last_seen("logout");
setcookie("user",$user->username, time()-42000,"/");
unset ($_SESSION["{$user->username}"]);
unset ($_SESSION["in"]);
unset ($user);
session_destroy();
die("<script style='text/javascript'> top.location.replace('index.php'); </script>");
?>