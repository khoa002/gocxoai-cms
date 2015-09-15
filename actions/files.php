<?php
if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]);
session_start();
if (!isset($_SESSION["user"])) die();
require_once("{$_SESSION["root_path"]}/inc.php");
switch ($_REQUEST["type"]){
	case "images_general":
		if (!isset($_REQUEST["action"])) die("error");
		switch ($_REQUEST["action"]){
			case "delete":
				if (!isset($_REQUEST["id"])) die("error");
				$result = mysql_query("SELECT * FROM `images_general` WHERE `id` = '{$_REQUEST["id"]}'");
				while ($row = mysql_fetch_object($result)){
					$file_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$row->file_location}");
					if ( file_exists($file_path) ) unlink ($file_path);
					$file_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$row->file_thumbnail}");
					if ( file_exists($file_path) ) unlink ($file_path);
					mysql_query("DELETE FROM `images_general` WHERE `id` = '{$row->id}'");
				}
				break;
		}
		break;
}
die("done");
?>