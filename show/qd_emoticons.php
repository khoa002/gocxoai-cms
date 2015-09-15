<?php
session_start();
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["user"])) die();
require_once("{$_SESSION["root_path"]}/inc.php");

$folder = "/files/site_images/emoticons/monkey_msn";
$array = scandir("{$_SESSION["root_path"]}/{$folder}");
echo "<div style=\"text-align: left;\">";
foreach($array as $icon){
    if ($icon === "." OR $icon === "..") continue;
	echo "<a onClick=\"javascript: $('#quick_msg').tinymce().execCommand('mceInsertContent',false,'<img src=\'{$folder}/{$icon}\'/>');\"><img src=\"{$folder}/{$icon}\" width=\"30\"/></a>";
}

$folder = "/files/site_images/emoticons/onion_msn";
$array = scandir("{$_SESSION["root_path"]}/{$folder}");
foreach($array as $icon){
    if ($icon === "." OR $icon === "..") continue;
	echo "<a href=\"javascript:;\" onClick=\"javascript: $('#quick_msg').tinymce().execCommand('mceInsertContent',false,'<img src=\'{$folder}/{$icon}\'/>');\"><img src=\"{$folder}/{$icon}\" width=\"30\"/></a>";
}
echo "</div>";
?>