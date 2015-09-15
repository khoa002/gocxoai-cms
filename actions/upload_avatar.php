<?php
if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]);
session_start();
if (!isset($_SESSION["user"])) die();
if (empty($_FILES)) die();
require_once("{$_SESSION["root_path"]}/inc.php");

if (isset($_REQUEST["inactive_user"]) AND (mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `username` = '{$_REQUEST["inactive_user"]}' AND `active` = '0'"))) > 0){
    $user = new User($_REQUEST["inactive_user"]);
} else { $user = new User ( $_SESSION["user"] ); }

$fileParts  = pathinfo($_FILES['Filedata']['name']);
$fileExt = strtolower($fileParts['extension']);
$img = getimagesize($_FILES['Filedata']['tmp_name']);
if ( $img[0] < 100 OR $img[1] < 100 ) die("error_file_too_small");

if ($user->avatar_exists()){
    $current_avatar = "{$_SESSION["root_path"]}/{$user->avatar}";
    $parts = explode("/",$user->avatar);
    $filename = end($parts);
    $current_avatar_thumbnail = $_SESSION["root_path"]."/".str_replace($filename,"tn_{$filename}",$user->avatar);
    if (file_exists($current_avatar)) unlink($current_avatar);
    if (file_exists($current_avatar_thumbnail)) unlink($current_avatar_thumbnail);
}

$relative_folder_path = "files/user_avatars";
$absolute_folder_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$relative_folder_path}");
if ( !is_dir ( $absolute_folder_path ) ) { mkdir($absolute_folder_path, 0755, true); }
$random_fileNumber = gmdate("U_") . rand(0,1000);
$filename = "{$user->username}.{$fileExt}";
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

if (!mysql_query("UPDATE `users` SET `avatar` = '{$file_relative_path_fullsize}' WHERE `username` = '{$user->username}'")) die("database_error");
if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:avatar"); }
die("done");
?>