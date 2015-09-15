<?php
class QuickDiscussion{
    var $id, $author, $title, $body, $created, $parent_id, $edited, $editor, $category, $thumbnail, $last_touched, $comment_count, $exp;
    var $current_user, $qd_title, $qd_author, $qd_editor;
    
    function __construct(){
        $this->current_user = new User($_SESSION["user"]);
        $this->id = false;
        $this->author = false;
		$this->title = false;
        $this->body = false;
        $this->created = false;
        $this->parent_id = false;
        $this->edited = false;
        $this->editor = false;
        $this->category = false;
        $this->thumbnail = false;
		$this->last_touched = false;
		$this->comment_count = false;
		$this->exp = false;
		$this->qd_title = false;
        $this->qd_author = false;
        $this->qd_editor = false;
    }
    function load($id,$raw = false){
        if (empty($id)) return false;
        $result = mysql_query("SELECT * FROM `quick_discussions` WHERE `id` = '{$id}'");
        if (!$a = mysql_fetch_object($result)) return "does_not_exist";
        $this->id = $a->id;
        $this->author = $a->author;
		$this->title = htmlspecialchars($a->title,ENT_QUOTES);
        $this->body = stripslashes($a->body);
		
		$this->qd_title = "<span style=\"vertical-align: middle;\">";
		if (mysql_num_rows(mysql_query("SELECT * FROM `quick_discussions_read_status` WHERE `postid` = '{$this->id}' AND `{$this->current_user->username}` > '1970-01-01 00:00:00'")) == 0) $this->qd_title .= "<img src=\"files/site_images/layout/new.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\">&nbsp;" . $this->title . "</span>";
		else $this->qd_title .= $this->title;
		$this->qd_title .= "</span>";
        
        if (!$raw){
            $result = mysql_query("SELECT * FROM `users`");
            while ($row = mysql_fetch_object($result)){
                $member = new User($row->username);
				$select = mysql_query("SELECT * FROM `users_titles_you` WHERE `user` = '{$this->author}'");
                $ignore = array("con","em","cháu");
                $title_only = array("ông ngoại","ông nội","ba","bà ngoại","bà nội","má");
                if ($title_row = mysql_fetch_object($select)){
                    if (in_array(mb_convert_case($title_row->{$row->username}, MB_CASE_LOWER, "UTF-8"),$title_only)){
                        $this->body = str_replace("%{$member->username}%", mb_convert_case($title_row->{$row->username}, MB_CASE_TITLE, "UTF-8"),$this->body);
                        $this->qd_title = str_replace("%{$member->username}%", mb_convert_case($title_row->{$row->username}, MB_CASE_TITLE, "UTF-8"),$this->qd_title);
                    } elseif (!in_array($title_row->{$row->username},$ignore)){
                        $this->body = str_replace("%{$member->username}%", mb_convert_case($title_row->{$row->username}, MB_CASE_TITLE, "UTF-8") . " " . $member->display_name,$this->body);
                        $this->qd_title = str_replace("%{$member->username}%", mb_convert_case($title_row->{$row->username}, MB_CASE_TITLE, "UTF-8") . " " . $member->display_name,$this->qd_title);
                    } else {
                        $this->body = str_replace("%{$member->username}%", $member->display_name,$this->body);
                        $this->qd_title = str_replace("%{$member->username}%", $member->display_name,$this->qd_title);
                    }
                }
            }
            $this->body = replace_stuff($this->body);
        }
        
        $this->created = $a->created;
        $this->parent_id = $a->parent_id;
        $this->edited = $a->edited;
        $this->editor = $a->editor;
        $this->category = $a->category;
		$this->thumbnail = $a->thumbnail;
        
		$this->last_touched = $a->last_touched;
		$this->comment_count = $a->comment_count;
		$this->exp = $a->exp;
		
        $this->qd_author = new User($this->author);
        if (!empty($this->editor)) $this->qd_editor = new User($this->editor);
		else $this->qd_editor = new User($this->author);
		
        return true;
    }
    function add($title = "",$body, $author = false, $created = false, $parent_id = false, $category = "general"){
        if (empty($body)) return false;
        if (!$author) $author = $this->current_user->username;
        if (!$created) $created = gmdate("Y-m-d H:i:s");
		$exp = calculatePostEXP($body);
        $body = addslashes($body);
        if (!mysql_query("INSERT INTO `quick_discussions` (`author`,`title`,`body`,`created`,`parent_id`,`last_touched`,`category`,`exp`) VALUES ('{$author}','{$title}','{$body}','{$created}','{$parent_id}','{$created}','{$category}','{$exp}')")) return false;
		$insert_id = mysql_insert_id();
		if (($parent_id !== false AND $parent_id != "0") AND !mysql_query("UPDATE `quick_discussions` SET `last_touched` = '{$created}' WHERE `id` = '{$parent_id}'")) return false;
		if (($parent_id !== false AND $parent_id != "0") AND !mysql_query("UPDATE `quick_discussions` SET `comment_count` = `comment_count` + 1 WHERE `id` = '{$parent_id}'")) return false;
        return $insert_id;
    }
    function edit($title = false, $body, $author = false, $created = false, $parent_id = false, $edited = false, $editor = false, $category = false){
        if (empty($body)) return false;
        if (!$edited) $edited = gmdate("Y-m-d H:i:s");
        if (!$editor) $editor = $this->current_user->username;
		if (!$category) $category = $this->category;
		$exp = calculatePostEXP($body);
        $body = addslashes($body);
        $query = "UPDATE `quick_discussions` SET `body` = '{$body}', `edited` = '{$edited}', `editor` = '{$editor}', `category` = '{$category}', `exp` = '{$exp}'";
		if ($title):
			$query .= ", `title` = '{$title}'";
			mysql_query("UPDATE `quick_discussions` SET `title` = 'RE: {$title}' WHERE `parent_id` = '{$this->id}'");
		endif;
        if ($author) $query .= ", `author` = '{$author}'";
        if ($created) $query .= ", `created` = '{$created}'";
        if ($parent_id !== false AND $parent_id !== $this->parent_id AND ( mysql_num_rows(mysql_query("SELECT * FROM `quick_discussions` WHERE `id` = '{$parent_id}'")) > 0 OR $parent_id == 0 )) {
            if ($parent_id != 0) mysql_query("UPDATE `quick_discussions` SET `parent_id` = '{$parent_id}' WHERE `parent_id` = '{$this->id}'");
            $query .= ", `parent_id` = '{$parent_id}'";
        }
        $query .= " WHERE `id` = '{$this->id}'";
        if (!mysql_query($query)) return false;
		if (!mysql_query("UPDATE `quick_discussions` SET `last_touched` = '{$edited}' WHERE `id` = '{$this->id}'")) return false;
		if (($parent_id != 0 AND !$parent_id) AND !mysql_query("UPDATE `quick_discussions` SET `last_touched` = '{$edited}' WHERE `id` = '{$parent_id}'")) return false;
        return true;
    }
	
	function update_last_touched($last_touched = false){
		if (!$this->id) return false;
		if (!$last_touched) $last_touched = gmdate("Y-m-d H:i:s");
		if ($last_touched != $this->last_touched)
			if (!mysql_query("UPDATE `quick_discussions` SET `last_touched` = '{$last_touched}' WHERE `id` = '{$this->id}'")) return false;
		return true;
	}
    function can_delete(){
        if (!$this->id) return false;
        $result = mysql_query("SELECT * FROM `quick_discussions` WHERE `id` = '{$this->id}'");
        if (!$a = mysql_fetch_object($result)) return "does_not_exist";
        if (mysql_num_rows(mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` = '{$a->id}'")) > 0) return "not_empty";
        if (mysql_num_rows(mysql_query("SELECT * FROM `quick_discussions` WHERE `created` > '{$a->created}' AND `parent_id` != '0' AND `parent_id` = '{$a->parent_id}'")) > 0) return "not_empty";
        $tz = date_default_timezone_get();
        date_default_timezone_set("UTC");
        $prev_time = strtotime("-3 days");
        $this_time = strtotime($this->created);
        date_default_timezone_set($tz);
        if ($prev_time >= $this_time) return "too_old";
        return "yes";
    }
    function delete(){
        if ($this->can_delete() != "yes") return false;
        if (!mysql_query("DELETE FROM `quick_discussions` WHERE `id` = '{$this->id}'")) return false;
		if (!mysql_query("DELETE FROM `quick_discussions_read_status` WHERE `postid` = '{$this->id}'")) return false;
		if ($this->parent_id != "0"):
			$post = mysql_fetch_object(mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` = '{$this->parent_id}' ORDER BY `last_touched` DESC LIMIT 1"));
			if (!$post) $post = mysql_fetch_object(mysql_query("SELECT * FROM `quick_discussions` WHERE `id` = '{$this->parent_id}'"));
			if (date("U",strtotime($post->edited)) >= date("U",strtotime($post->created))) $last_touched = $post->edited;
			else $last_touched = $post->created;
			if (!mysql_query("UPDATE `quick_discussions` SET `last_touched` = '{$last_touched}' WHERE `id` = '{$this->parent_id}'")) return false;
			if (!mysql_query("UPDATE `quick_discussions` SET `comment_count` = `comment_count` - 1 WHERE `id` = '{$this->parent_id}'")) return false;
		endif;
        $this->__construct();
        return true;
    }
	function mark_read(){
		if (!$this->id) return false;
		$sql = mysql_query("SELECT * FROM `quick_discussions_read_status` WHERE `postid` = '{$this->id}'");
		$now = gmdate("Y-m-d H:i:s");
		if (mysql_num_rows($sql) == 0){
			if (!mysql_query("INSERT INTO `quick_discussions_read_status` (`postid`,`{$this->current_user->username}`) VALUES ('{$this->id}','{$now}')")) return false;
		} else {
			if (!mysql_query("UPDATE `quick_discussions_read_status` SET `{$this->current_user->username}` = '{$now}' WHERE `postid` = '{$this->id}'")) return false;
		}
		return true;
	}
}
?>