<?php
if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]); session_start();
if (!isset($_SESSION["in"])) die("This page cannot be directly accessed.");
if (!isset($_GET["type"])) die("No type.");
if (!isset($_GET["id"])) die();
$type = $_GET["type"];
require_once("{$_SESSION["root_path"]}/inc.php");
$user = new User ( $_SESSION["user"] );

switch($type){
    case "images_user_avatars":
        if (!isset($_SESSION["user"])) die("You cannot view this image.");
        if (!isset($_GET["id"])) $img = "files/user_avatars/default.png";
        else {
            $aaa = new User($_GET["id"]);
            if (isset($_GET["thumbnail"])){
                if ($aaa->avatar_exists()){
                    $parts = explode("/",$aaa->avatar);
                    $filename = end($parts);
                    $img = str_replace($filename,"tn_{$filename}",$aaa->avatar);
                } else $img = "files/user_avatars/default.png";
            } else {
                if ($aaa->avatar_exists()) $img = $aaa->avatar;
                else $img = "files/user_avatars/default.png";
            }
        }
        $parts = explode("/",$img);
        $filename = end($parts);
        $parts = explode(".",$filename);
        $filetype = end($parts);
        header("Content-Disposition: filename={$filename}");
        header("Content-Transfer-Encoding:­ binary");
        switch($filetype){
            case "jpg": case "jpeg":
                header("Content-type: image/jpeg");  break;
            default: header("Content-type: image/{$filetype}"); break;
        }
        die(file_get_contents("{$_SESSION["root_path"]}/{$img}"));
	case "post_image":
        if (!isset($_SESSION["user"])) die("You cannot view this image.");
		$img = "files/posts/default.png";
        if (isset($_GET["id"])) {
            $aaa = new QuickDiscussion();
			$aaa->load($_GET["id"]);
			if (isset($_GET["thumbnail"])){
                if (!empty($aaa->thumbnail)){
                    $parts = explode("/",$aaa->thumbnail);
                    $filename = end($parts);
                    $img = "files/posts/tn/{$filename}";
                }
            }
			elseif (!empty($aaa->thumbnail)) $img = $aaa->thumbnail;
        }
		if (substr($img,0,1) == "/") $img = substr($img,1);
        $parts = explode("/",$img);
        $filename = end($parts);
        $parts = explode(".",$filename);
        $filetype = end($parts);
        header("Content-Disposition: filename={$filename}");
        header("Content-Transfer-Encoding:­ binary");
        switch($filetype){
            case "jpg": case "jpeg":
                header("Content-type: image/jpeg");  break;
            default: header("Content-type: image/{$filetype}"); break;
        }
        die(file_get_contents("{$_SESSION["root_path"]}/{$img}"));
    case "images_general":
        if (!isset($_SESSION["user"])) die("You cannot view this image.");
        $result = mysql_query("SELECT * FROM `images_general` WHERE `id` = '{$_GET["id"]}'");
        if ($img = mysql_fetch_object($result)){
            // set the header for the image
            header("Content-type: image/{$img->type}");
            header("Content-Disposition: filename={$img->file_name}");
            header("Content-Transfer-Encoding:­ binary");
            if (isset($_GET["thumbnail"])) die(readfile($_SESSION["root_path"] . "/" . $img->file_thumbnail));
            else die(readfile($_SESSION["root_path"] . "/" . $img->file_location));
        }
    case "videos_general":
        if (!isset($_SESSION["user"])) die("You cannot view this video.");
        $result = mysql_query("SELECT * FROM `videos_general` WHERE `id` = '{$_GET["id"]}'");
        if ($vid = mysql_fetch_object($result)){
            // set the header for the video
            switch($vid->type){
				default: headet("Content-type: video/{$vid->type}"); break;
                case "mp4": header("Content-type: video/mp4"); break;
				case "avi": header("Content-type: application/x-troff-msvideo"); break;
				case "flv": header("Content-type: application/x-flv"); break;
            }
            header("Content-Disposition: filename={$vid->file_name}");
            header("Content-Length: ".filesize($_SESSION["root_path"] . "/" . $vid->file_location));
            die(readfile($_SESSION["root_path"] . "/" . $vid->file_location));
        }
    case "files_general":
        if (!isset($_SESSION["user"])) die("You cannot download this file.");
        $result = mysql_query("SELECT * FROM `files_general` WHERE `id` = '{$_GET["id"]}'");
        if ($file = mysql_fetch_object($result)){
            switch($file->type){
                case "doc": case "docx":
                    header("Content-type: application/msword"); break;
				case "xls": case "xlsx":
					header("Content-type: application/msexcel"); break;
                case "txt":
                    header("Content-type: text/plain"); break;
                default:
                    header("Content-type: application/{$file->type}"); break;
            }
            header("Content-Disposition: attachment; filename={$file->file_name}");
            header("Content-Transfer-Encoding:­ binary");
            header("Content-Length: ".filesize($_SESSION["root_path"] . "/" . $file->file_location));
            die(readfile($_SESSION["root_path"] . "/" . $file->file_location));
        }
    case "music_general":
        if (!isset($_SESSION["user"])) die("You cannot download this file.");
        $result = mysql_query("SELECT * FROM `music_general` WHERE `id` = '{$_GET["id"]}'");
        if ($file = mysql_fetch_object($result)){
            switch($file->type){
                case "mp3":
                    header("Content-type: audio/mpeg"); break;
                default:
                    header("Content-type: application/{$file->type}"); break;
            }
            header("Content-Disposition: attachment; filename={$file->file_name}");
            header("Content-Transfer-Encoding:­ binary");
            header("Content-Length: ".filesize($_SESSION["root_path"] . "/" . $file->file_location));
            die(readfile($_SESSION["root_path"] . "/" . $file->file_location));
        }
    default: die();
}
?>