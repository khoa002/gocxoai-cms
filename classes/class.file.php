<?php
class File {
    var $id, $postid, $type, $path, $label, $description, $hits, $owner, $created, $special;
    var $current_user, $file_owner, $experience = 86;
    function __construct ( $id = null ) {
        $this->current_user = new User ( $_SESSION["user"] );
        if ( $id == null ) {
            if ( !$file = mysql_fetch_assoc ( mysql_query("SELECT * FROM `files` WHERE `type` = '-99' AND `owner` = '{$this->current_user->username}'") ) ) {
                $query = mysql_query( "INSERT INTO `files` (`type`,`owner`) VALUES ('-99','{$this->current_user->username}')" );
                $id = mysql_insert_id();
            } else $id = $file["id"];
        }
        $file = mysql_fetch_assoc( mysql_query ( "SELECT * FROM `files` WHERE `id` = '{$id}'" ) );
        $this->id = $file["id"];
        $this->postid = $file["postid"];
        $this->type = $file["type"];
        $this->path = $file["path"];
        $this->label = $file["label"];
        $this->description = $file["description"];
        $this->hits = $file["hits"];
        $this->owner = $file["owner"];
        $this->created = $file["created"];
        $this->special = $file["special"];
        $this->file_owner = new User ( $this->owner );
    }
    function set_postid ( $id ) {
        mysql_query ( "UPDATE `files` SET `postid` = '{$id}' WHERE `id` = '{$this->id}'" );
        $this->postid = $id;
    }
    function set_type( $key ) {
        mysql_query("UPDATE `files` SET `type` = '{$key}' WHERE `id` = '{$this->id}'");
        $this->type = $key;
    }
    function set_path ( $path ) {
        mysql_query ("UPDATE `files` SET `path` = '{$path}' WHERE `id` = '{$this->id}'");
        $this->path = $path;
    }
    function set_label ( $label ) {
        $label = htmlentities($label,ENT_QUOTES | 'ENT_HTML401',"UTF-8");
        mysql_query ("UPDATE `files` SET `label` = '{$label}' WHERE `id` = '{$this->id}'");
        $this->label = $label;
    }
    function set_description ( $description ) {
        mysql_query ("UPDATE `files` SET `description` = '{$description}' WHERE `id` = '{$this->id}'");
        $this->description = $description;
    }
    function increment_hits () {
        mysql_query ("UPDATE `files` SET `hits` = `hits` + 1 WHERE `id` = '{$this->id}'");
        $this->hits++;
    }
    function set_owner ( $owner ) {
        mysql_query ("UPDATE `files` SET `owner` = '{$owner}' WHERE `id` = '{$this->id}'");
        $this->owner = $owner;
        $this->file_owner = new User ($owner);
    }
    function set_created ( $datetime = null ) {
        if ( $datetime == null ) $datetime = date("Y-m-d H:i:s");
        mysql_query ("UPDATE `files` SET `created` = '{$datetime}' WHERE `id` = '{$this->id}'");
        $this->created = $datetime;
    }
    function set_special ( $path ){
        mysql_query ("UPDATE `files` SET `special` = '{$path}' WHERE `id` = '{$this->id}'");
        $this->special = $path;
    }
    function set_experience () {
        $this->file_owner->add_experience ( $this->experience );
    }
    function get_fileTypes_array() {
        $array = array();
        $query = mysql_query ( "SELECT * FROM `files_types`" );
        while ( $myrow = mysql_fetch_assoc ($query) ) {
            $extensions = explode("|",$myrow["extensions"]);
            $array[] = array( "id" => $myrow["id"] , "name" => $myrow["name"] , "label_en" => $myrow["label_en"] , "label_vi" => $myrow["label_vi"] , "extensions" => $extensions );
        }
        return $array;
    }
    function delete_file() {
        switch ($this->type){
            case 1:{
                $file_path = str_replace("//","/","{$_SERVER["DOCUMENT_ROOT"]}/{$this->path}");
                $thumbnail_path = str_replace("//","/","{$_SERVER["DOCUMENT_ROOT"]}/{$this->special}");
                if ( file_exists($file_path) ) unlink ($file_path);
                if ( file_exists($thumbnail_path) ) unlink ($thumbnail_path);
                mysql_query("DELETE FROM files WHERE `id` = '{$this->id}'");
                $this->file_owner->sub_experience ( $this->experience );
                unset($this);
                break;
            }
            case 2:{
                $file_path = str_replace("//","/","{$_SERVER["DOCUMENT_ROOT"]}/{$this->path}");
                if ( file_exists($file_path) ) unlink ($file_path);
                mysql_query("DELETE FROM files WHERE `id` = '{$this->id}'");
                $this->file_owner->sub_experience ( $this->experience );
                unset($this);
                break;
            }
            case 3:{
                $file_path = str_replace("//","/","{$_SERVER["DOCUMENT_ROOT"]}/{$this->path}");
                if ( file_exists($file_path) ) unlink ($file_path);
                mysql_query("DELETE FROM `files` WHERE `id` = '{$this->id}'");
                $this->file_owner->sub_experience ( $this->experience );
                unset($this);
                break;
            }
        }
    }
}
?>