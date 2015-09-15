<?php
class Comment {
    var $id, $postid, $comment, $author, $created, $updated, $updater, $experience;
    var $comment_author, $comment_updater, $current_user, $comment_raw;
    var $exp_per_char = 0.5, $exp_per_comment = 50;

    function __construct ( $id = null ){
        $this->current_user = new User ($_SESSION["user"]);
        // comment with postid 0 is a temporary one
        if ( $id == null ) {
            if ( !$comment = mysql_fetch_assoc ( mysql_query("SELECT * FROM `comments` WHERE `postid` = '0' AND `author` = '{$this->current_user->username}'") ) ) {
                $query = mysql_query( "INSERT INTO `comments` (`postid`,`author`) VALUES ('0','{$this->current_user->username}')" );
                $id = mysql_insert_id();
            } else $id = $comment["id"];
        }
        $comment = mysql_fetch_assoc(mysql_query("SELECT * FROM `comments` WHERE `id` = '{$id}'"));

        $this->id = $comment["id"];
        $this->postid = $comment["postid"];
        $this->comment = $comment["comment"];
        $this->author = $comment["author"];
        $this->created = $comment["created"];
        $this->updated = $comment["updated"];
        $this->updater = $comment["updater"];
        $this->experience = $comment["experience"];

        $this->comment_author = new User ($this->author);
        if ($this->updater != "0") $this->comment_updater = new User ($this->updater);
        $this->comment_raw = strip_tags($this->comment,"<p><strong><b><br/><br><a>");
    }
    function get_created_date_line (){
        $datetime = $this->created;
        return translate("Posted on ","Đăng vào ") . get_date($this->created) . " « " . translate("about","khoảng") . " " . get_date_length($this->created) . " " .translate("ago","trước"). " »" . ($this->updated == "0000-00-00 00:00:00" ? "" : (" | " . translate("Modified on ","Soạn lại vào ") . get_date($this->updated) . " « " . translate("about","khoảng") . " " . get_date_length($this->updated) . " " .translate("ago","trước"). " »"));
    }
    function get_edit_line(){
        if ( $this->current_user->role == 99 OR $this->current_user->role == 1 OR $this->current_user->username == $this->author) {
            $v = "<span><a id='comment{$this->id}edit'>&nbsp;<img src='/lib/interface/img/edit-16.png' style='vertical-align: middle;'/> ".translate("Edit","Soạn")."</a>&nbsp;<a id='comment{$this->id}delete'>&nbsp;<img src='/lib/interface/img/close-16.png' style='vertical-align: middle;'/> ".translate("Delete","Xóa")."</a></span>";
            $v .= "<script> $('#comment{$this->id}edit').click(function(){ ".loadIn("#comment_area","lib/show/comment_form.php?commentid={$this->id}&session=".session_id(),scroll_to("#comment_area"))." }); $('#comment{$this->id}delete').click(function(){ $.post('/lib/show/delete_comment.php?session=".session_id()."', {'commentid': '{$this->id}'}, function (data){ $('#error_message').html(data); }); }); </script>";
            return $v;
        }
    }
    function get_comment_read ( $v = 1 ){
        if ($v == 1){
            $var = "";
            $select_users = mysql_query("SELECT * FROM `users` ORDER BY `username`");
            $read = mysql_fetch_assoc( mysql_query("SELECT * FROM `comments_read` WHERE `commentid` = '{$this->id}'"));
            while ( $user_row = mysql_fetch_assoc($select_users) ) {
                $user = new User ( $user_row["id"] );
                if ( isset($read[$user_row["id"]]) AND $read[$user_row["id"]] == 1 ) $var .= "<div title='{$user->name}' id='commentread{$this->id}{$user->id}' style='border: 1px dotted #000; margin: 1px; background: #{$user->color}; width: 8px; height: 8px; float: left;'></div><script> $('#commentread{$this->id}{$user->id}').qtip({position: { my: 'bottom right', at: 'top left' }, style: { classes: 'ui-tooltip-green ui-tooltip-shadow' }}); </script>";
            }
        } else {
        }
        return $var;
    }
    function set_comment_read( $author = null ) {
        if ( isset($author) ) {
            if (!mysql_query("SELECT * FROM `comments_read` WHERE `{$author}` IN ('0','1')")) mysql_query("ALTER TABLE `comments_read` ADD `{$author}` ENUM('0','1') NOT NULL DEFAULT '0'");
            if ( mysql_num_rows (mysql_query("SELECT * FROM `comments_read` WHERE `commentid` = '{$this->id}'")) == 0) mysql_query("INSERT INTO `comments_read` (`commentid`,`{$author}`) VALUES ('{$this->id}','1')");
            else mysql_query ("UPDATE `comments_read` SET `{$author}` = '1' WHERE `commentid` = '{$this->id}'");
        } else {
            if (!mysql_query("SELECT * FROM `comments_read` WHERE `{$this->current_user->username}` IN ('0','1')")) mysql_query("ALTER TABLE `comments_read` ADD `{$this->current_user->username}` ENUM('0','1') NOT NULL DEFAULT '0'");
            if ( mysql_num_rows (mysql_query("SELECT * FROM `comments_read` WHERE `commentid` = '{$this->id}'")) == 0) mysql_query("INSERT INTO `comments_read` (`commentid`,`{$this->current_user->username}`) VALUES ('{$this->id}','1')");
            else mysql_query ("UPDATE `comments_read` SET `{$this->current_user->username}` = '1' WHERE `commentid` = '{$this->id}'");
        }
        if (!mysql_query("SELECT * FROM `comments_read` WHERE `{$this->author}` IN ('0','1')")) mysql_query("ALTER TABLE `comments_read` ADD `{$this->author}` ENUM('0','1') NOT NULL DEFAULT '0'");
        if ( mysql_num_rows (mysql_query("SELECT * FROM `comments_read` WHERE `commentid` = '{$this->id}'")) == 0) mysql_query("INSERT INTO `comments_read` (`commentid`,`{$this->author}`) VALUES ('{$this->id}','1')");
        else mysql_query ("UPDATE `comments_read` SET `{$this->author}` = '1' WHERE `commentid` = '{$this->id}'");
    }
    function set_experience () {
        $old_exp = $this->experience;
        $comment = str_replace(" ","",trim( strip_tags( html_entity_decode($this->comment, ENT_QUOTES, "UTF-8") ) ));
        $len = strlen($comment);
        $new_exp = round( ($this->exp_per_char * $len) + $this->exp_per_comment );
        mysql_query("UPDATE `comments` SET `experience` = '{$new_exp}' WHERE `id` = '{$this->id}'");
        $this->comment_author->add_experience ( $new_exp );
        $this->comment_author->sub_experience ( $old_exp );
        $this->experience = $new_exp;
    }
    function set_comment ( $comment ){
        mysql_query("UPDATE `comments` SET `comment` = '{$comment}' WHERE `id` = '{$this->id}'");
        $this->comment = $comment;
        $this->comment_raw = strip_tags($this->comment);
    }
    function set_postid ( $postid ) {
        mysql_query("UPDATE `comments` SET `postid` = '{$postid}' WHERE `id` = '{$this->id}'");
        $this->postid = $postid;
    }
    function set_created ( $datetime = null ){
        if ( $datetime == null ) $datetime = date("Y-m-d H:i:s");
        mysql_query("UPDATE `comments` SET `created` = '{$datetime}' WHERE `id` = '{$this->id}'");
        $this->created = $datetime;
    }
    function set_author( $author ){
        mysql_query("UPDATE `comments` SET `author` = '{$author}' WHERE `id` = '{$this->id}'");
        $this->author = $author;
        $this->comment_author = new User ( $this->author );
    }
    function set_updated ( $datetime = null ){
        if ( $datetime == null ) $datetime = date("Y-m-d H:i:s");
        mysql_query("UPDATE `comments` SET `updated` = '{$datetime}' WHERE `id` = '{$this->id}'");
        $this->updated = $datetime;
    }
    function set_updater( $updater ){
        mysql_query("UPDATE `comments` SET `updater` = '{$updater}' WHERE `id` = '{$this->id}'");
        $this->updater = $updater;
        $this->post_updater = new User($this->updater);
    }
    function delete_comment () {
        mysql_query("DELETE FROM `comments_read` WHERE `commentid` = '{$this->id}'");
        mysql_query("DELETE FROM `comments` WHERE `id` = '{$this->id}'");
        $this->comment_author->sub_experience ( $this->experience );
        unset ($this);
    }
}
?>