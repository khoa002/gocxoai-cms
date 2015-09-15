<?php
class Event {
    var $id, $postid, $type, $title, $description, $author, $created, $start, $end, $recurrence, $recurrence_end_date;
    var $current_user, $event_author, $experience = 176;
    function __construct( $id = null ) {
        $this->current_user = new User ($_SESSION["user"]);
        if ( $id == null ) {
            if ( !$event = mysql_fetch_assoc ( mysql_query("SELECT * FROM `events` WHERE `type` = '0' AND `author` = '{$this->current_user->username}'") ) ) {
                $query = mysql_query( "INSERT INTO `events` (`type`,`author`) VALUES ('0','{$this->current_user->username}')" );
                $id = mysql_insert_id();
            } else $id = $event["id"];
        }
        $event = mysql_fetch_assoc(mysql_query("SELECT * FROM `events` WHERE `id` = '{$id}'"));

        $this->id = $event["id"];
        $this->postid = $event["postid"];
        $this->type = $event["type"];
        $this->title = html_entity_decode($event["title"],ENT_QUOTES,"UTF-8");
        $this->description = $event["description"];
        $this->author = $event["author"];
        $this->created = $event["created"];
        $this->start = $event["start"];
        $this->end = $event["end"];
        $this->recurrence = $event["recurrence"];
        $this->recurrence_end_date = $event["recurrence_end_date"];

        $this->event_author = new User ($this->author);
    }
    function set_postid ($postid){
        mysql_query("UPDATE `events` SET `postid` = '{$postid}' WHERE `id` = '{$this->id}'");
        $this->postid = $postid;
    }
    function set_type ($type){
        mysql_query("UPDATE `events` SET `type` = '{$type}' WHERE `id` = '{$this->id}'");
        $this->type = $type;
    }
    function set_start ($datetime = null){
        if ( $datetime == null ) $datetime = date("Y-m-d H:i:s");
        mysql_query("UPDATE `events` SET `start` = '{$datetime}' WHERE `id` = '{$this->id}'");
        $this->start = $datetime;
    }
    function set_end ($datetime = null){
        if ( $datetime == null ) $datetime = date("Y-m-d H:i:s");
        mysql_query("UPDATE `events` SET `end` = '{$datetime}' WHERE `id` = '{$this->id}'");
        $this->end = $datetime;
    }
    function set_created ($datetime = null){
        if ( $datetime == null ) $datetime = date("Y-m-d H:i:s");
        mysql_query("UPDATE `events` SET `created` = '{$datetime}' WHERE `id` = '{$this->id}'");
        $this->created = $datetime;
    }
    function set_title ($title){
        $title = htmlentities($title,ENT_QUOTES | 'ENT_HTML401',"UTF-8");
        mysql_query ( "UPDATE `events` SET `title` = '{$title}' WHERE `id` = '{$this->id}'");
        $this->title = $title;
    }
    function set_description ( $description ){
        mysql_query("UPDATE `events` SET `description` = '{$description}' WHERE `id` = '{$this->id}'");
        $this->description = $description;
    }
    function set_author ($author = null){
        if ($author == null) $author = $this->current_user->username;
        mysql_query ( "UPDATE `events` SET `author` = '{$author}' WHERE `id` = '{$this->id}'" );
        $this->author = $author;
        $this->event_author = new User ($author);
    }
    function set_experience(){
        $this->event_author->add_experience ( $this->experience );
    }
    function set_recurrence( $recurrence ){
        mysql_query("UPDATE `events` SET `recurrence` = '{$recurrence}' WHERE `id` = '{$this->id}'");
        $this->recurrence = $recurrence;
    }
    function set_recurrence_end_date ($datetime){
        if ( $datetime == null ) $datetime = date("Y-m-d H:i:s");
        mysql_query("UPDATE `events` SET `recurrence_end_date` = '{$datetime}' WHERE `id` = '{$this->id}'");
        $this->recurrence_end_date = $datetime;
    }
    function delete_event(){
        mysql_query("DELETE FROM `events` WHERE `id` = '{$this->id}'");
        $this->event_author->sub_experience($this->experience);
        unset($this);
    }
}
?>