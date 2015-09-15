<?php
if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]);
session_start();
if (!isset($_SESSION["user"])) die();
require_once("{$_SESSION["root_path"]}/inc.php");
$user = new User ( $_SESSION["user"] );
$fileTypes  = str_replace('*.','',$_REQUEST['fileTypeExts']);
$fileTypes  = str_replace(';','|',$fileTypes);
$typesArray = explode('|',$fileTypes);
$fileParts  = pathinfo($_FILES['Filedata']['name']);
$fileExt = strtolower($fileParts['extension']);
if (!in_array( $fileExt , $typesArray )) die("error_filetype_not_allowed");

$relative_folder_path = "files/".gmdate("Y-m")."/music";
$absolute_folder_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$relative_folder_path}");
if ( !is_dir ( $absolute_folder_path ) ) { mkdir($absolute_folder_path, 0755, true); }
$random_fileNumber = gmdate("U_") . rand(0,100);
$filename = "{$random_fileNumber}__{$user->username}.{$fileExt}";
$absolute_path = str_replace('//','/',"{$absolute_folder_path}/{$filename}");
$relative_path = str_replace('//','/',"{$relative_folder_path}/{$filename}");
$file_size = filesize($_FILES['Filedata']['tmp_name']);
move_uploaded_file($_FILES['Filedata']['tmp_name'],$absolute_path);
$group = $_REQUEST["group"];
$created = gmdate("Y-m-d H:i:s");
if (!mysql_query("INSERT INTO `music_general` (`file_location`,`file_name`,`type`,`size`,`group`,`author`,`created`) VALUES ('{$relative_path}','{$fileParts['basename']}','{$fileExt}','{$file_size}','{$group}','{$user->username}','{$created}')")) die("database_error");
die("done");
?>