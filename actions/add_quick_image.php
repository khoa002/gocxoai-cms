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

$relative_folder_path = "files/".gmdate("Y-m")."/images";
$absolute_folder_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$relative_folder_path}");
if ( !is_dir ( $absolute_folder_path ) ) { mkdir($absolute_folder_path, 0755, true); }
$random_fileNumber = gmdate("U_") . rand(0,1000);
$filename = "{$random_fileNumber}__{$user->username}___{$fileParts['basename']}";
$file_absolute_path_fullsize = "{$absolute_folder_path}/{$filename}";
$file_absolute_path_thumbnail = "{$absolute_folder_path}/tn_{$filename}";
$file_relative_path_fullsize = "{$relative_folder_path}/{$filename}";
$file_relative_path_thumbnail = "{$relative_folder_path}/tn_{$filename}";
copy($_FILES['Filedata']['tmp_name'],$file_absolute_path_thumbnail);
move_uploaded_file($_FILES['Filedata']['tmp_name'],$file_absolute_path_fullsize);
$img = new SimpleImage();
$img->load($file_absolute_path_thumbnail);
$filetype = $img->image_type;
if ( $img->getWidth() > $img->getHeight() ) $img->resizeToWidth(150);
else $img->resizeToHeight(150);
$img->save($file_absolute_path_thumbnail,$filetype);

$group = $_REQUEST["group"];
$created = gmdate("Y-m-d H:i:s");
if (!mysql_query("INSERT INTO `images_general` (`type`,`size`,`file_location`,`file_thumbnail`,`file_name`,`group`,`author`,`created`) VALUES ('{$fileExt}','".filesize($_FILES['Filedata']['tmp_name'])."','{$file_relative_path_fullsize}','{$file_relative_path_thumbnail}','{$fileParts['basename']}','{$group}','{$user->username}','{$created}')")) die("database_error");
die("done");
?>