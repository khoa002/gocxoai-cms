<?php
//if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]);
session_start();
if (!isset($_SESSION["user"])) die();
require_once("{$_SESSION["root_path"]}/inc.php");
$user = new User($_SESSION["user"]);
$discussion = new QuickDiscussion();
switch ($_REQUEST["action"]){
    case "add":
		$title = false;
		if (!empty($_REQUEST["qd_title"])) $title = $_REQUEST["qd_title"];
        if (!$add = $discussion->add($title,$_REQUEST["quick_msg"],false,false,$_REQUEST["parent_id"],$_REQUEST["category"])) die("error");
		$newDiscussion = new QuickDiscussion();
		$newDiscussion->load($add);
        $group = $_REQUEST["group"];
        mysql_query("UPDATE `images_general` SET `group` = 'quick_discussions:{$newDiscussion->id}:{$group}' , `comment` = '{$_REQUEST["quick_msg"]}' , `attachedto` = 'quick_discussions:{$newDiscussion->id}' WHERE `group` = '{$group}'");
        mysql_query("UPDATE `videos_general` SET `group` = 'quick_discussions:{$newDiscussion->id}:{$group}' , `comment` = '{$_REQUEST["quick_msg"]}' , `attachedto` = 'quick_discussions:{$newDiscussion->id}' WHERE `group` = '{$group}'");
        mysql_query("UPDATE `files_general` SET `group` = 'quick_discussions:{$newDiscussion->id}:{$group}' , `comment` = '{$_REQUEST["quick_msg"]}' , `attachedto` = 'quick_discussions:{$newDiscussion->id}' WHERE `group` = '{$group}'");
        mysql_query("UPDATE `music_general` SET `group` = 'quick_discussions:{$newDiscussion->id}:{$group}' , `comment` = '{$_REQUEST["quick_msg"]}' , `attachedto` = 'quick_discussions:{$newDiscussion->id}' WHERE `group` = '{$group}'");
		$newDiscussion->qd_author->add_exp($newDiscussion->exp);
		$newDiscussion->qd_author->set_last_seen("added_discussion:{$newDiscussion->id}");
        break;
    case "edit":
        if ($discussion->load($_REQUEST["id"]) != true) die("loading error");
		$title = false;
		if (isset($_REQUEST["qd_title"])) $title = addslashes($_REQUEST["qd_title"]);
        if (!$edit = $discussion->edit($title,$_REQUEST["quick_msg"],false,false,$_REQUEST["parent_id"],false,false,$_REQUEST["category"])) die("editing error");
		$group = $_REQUEST["group"];
		$newDiscussion = new QuickDiscussion();
		$newDiscussion->load($discussion->id);
        mysql_query("UPDATE `images_general` SET `group` = 'quick_discussions:{$newDiscussion->id}:{$group}' , `comment` = '{$_REQUEST["quick_msg"]}' , `attachedto` = 'quick_discussions:{$newDiscussion->id}' WHERE `group` = '{$group}'");
        mysql_query("UPDATE `videos_general` SET `group` = 'quick_discussions:{$newDiscussion->id}:{$group}' , `comment` = '{$_REQUEST["quick_msg"]}' , `attachedto` = 'quick_discussions:{$newDiscussion->id}' WHERE `group` = '{$group}'");
        mysql_query("UPDATE `files_general` SET `group` = 'quick_discussions:{$newDiscussion->id}:{$group}' , `comment` = '{$_REQUEST["quick_msg"]}' , `attachedto` = 'quick_discussions:{$newDiscussion->id}' WHERE `group` = '{$group}'");
        mysql_query("UPDATE `music_general` SET `group` = 'quick_discussions:{$newDiscussion->id}:{$group}' , `comment` = '{$_REQUEST["quick_msg"]}' , `attachedto` = 'quick_discussions:{$newDiscussion->id}' WHERE `group` = '{$group}'");
		$newDiscussion->qd_author->sub_exp($discussion->exp);
		$newDiscussion->qd_author->add_exp($newDiscussion->exp);
		$user->set_last_seen("edited_discussion:{$newDiscussion->id}");
        break;
    case "delete":
        if ($discussion->load($_REQUEST["id"]) != true) die("error");
        // getting the file info if any exists
        $result = mysql_query("SELECT * FROM `files_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
        while ($row = mysql_fetch_object($result)){
            $file_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$row->file_location}");
            if ( file_exists($file_path) ) unlink ($file_path);
        }
        mysql_query("DELETE FROM `files_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
        
        // getting the image info if any exists
        $result = mysql_query("SELECT * FROM `images_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
        while ($row = mysql_fetch_object($result)){
            $file_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$row->file_location}");
            if ( file_exists($file_path) ) unlink ($file_path);
            $file_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$row->file_thumbnail}");
            if ( file_exists($file_path) ) unlink ($file_path);
        }
        mysql_query("DELETE FROM `images_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
        
        // getting the video info if any exists
        $result = mysql_query("SELECT * FROM `videos_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
        while ($row = mysql_fetch_object($result)){
            $file_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$row->file_location}");
            if ( file_exists($file_path) ) unlink ($file_path);
        }
        mysql_query("DELETE FROM `videos_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
        
        // getting the music info if any exists
        $result = mysql_query("SELECT * FROM `music_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
        while ($row = mysql_fetch_object($result)){
            $file_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$row->file_location}");
            if ( file_exists($file_path) ) unlink ($file_path);
        }
        mysql_query("DELETE FROM `music_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
		
		mysql_query("DELETE FROM `users_activities` WHERE `what` = 'added_discussion:{$discussion->id}'");
		mysql_query("DELETE FROM `users_activities` WHERE `what` = 'view_discussion:{$discussion->id}'");
		
		$discussion->qd_author->sub_exp($discussion->exp);
		$user->set_last_seen("deleted_discussion:{$discussion->id}");
        
        if (!$result = $discussion->delete()) die("error");
        break;
	case "remove_feature_image":
		if (!isset($_REQUEST["id"])) die("error");
		if (!mysql_query("UPDATE `quick_discussions` SET `thumbnail` = '' WHERE `id` = '{$_REQUEST["id"]}'")) die("update error");
		break;
	case "set_feature_image":
		if (!isset($_REQUEST["id"]) OR !isset($_REQUEST["file"])) die("error");
		if (substr($_REQUEST["file"],0,1) != "!"){
			$file_path = str_replace('//','/',"{$_SESSION["root_path"]}/{$_REQUEST["file"]}");
			if ( file_exists($file_path) )
				if (!unlink ($file_path)) die("error deleting");
		}
		if (!mysql_query("UPDATE `quick_discussions` SET `thumbnail` = '{$_REQUEST["file"]}' WHERE `id` = '{$_REQUEST["id"]}'")) die("update error");
		break;
}
die("done");
?>