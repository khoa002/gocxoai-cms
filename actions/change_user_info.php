<?php
if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]);
session_start();
if (!isset($_SESSION["user"])) die();
if (!isset($_GET["action"])) die();
require_once("{$_SESSION["root_path"]}/inc.php");

if (isset($_REQUEST["inactive_user"]) AND (mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `username` = '{$_REQUEST["inactive_user"]}' AND `active` = '0'"))) > 0){
    $user = new User($_REQUEST["inactive_user"]);
} else { $user = new User ( $_SESSION["user"] ); }

switch($_GET["action"]){
    case "edit_password": {
        if ($_POST["password1"] != $_POST["password2"]) die("error_newpw_mismatched");
        if (strlen($_POST["password1"]) <= 5) die("error_newpw_too_short");
        $salt = mysql_fetch_object(mysql_query("SELECT `value` FROM `system` WHERE `name` = 'salt'"));
        $current_password = MD5($salt->value.$_POST["cpassword"]);
        $current_password_no_salt = MD5($_POST["cpassword"]);
        if ($current_password != $user->password AND $current_password_no_salt != $user->password) die("error_old_pw_incorrect");
		$new_password = MD5($salt->value.$_POST["password1"]);
		$new_password_no_salt = MD5($_POST["password1"]);
		if ($new_password == $user->password OR $new_password_no_salt == $user->password) die("error_same_new_pw");
        $user->set_password($_POST["password1"]);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:password"); }
        die("done");
    }
    case "edit_display_name":{
        $query = mysql_query("SELECT * FROM `users`");
        while ($row = mysql_fetch_object($query)){
            if (strtolower($row->display_name) == strtolower($_POST["display_name"])) die("error_display_name_used");
        }
        $user->set_display_name($_POST["display_name"]);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:display_name"); }
        die("done");
    }
    case "edit_name":{
        $user->set_full_name(ucfirst($_POST["name_last"]),ucfirst($_POST["name_middle"]),ucfirst($_POST["name_first"]));
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:name"); }
        die("done");
    }
    case "edit_dob":{
        list($year,$month,$day) = explode("-",$_POST["datepicker"]);
        $user->set_dob($year,$month,$day);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:dob"); }
        die("done");
    }
    case "edit_email":{
        if ( !preg_match("/^(([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})+([;.](([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})+)*$/" , $_POST["email"] ) ) die("error_invalid");
        $user->set_email($_POST["email"]);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:email"); }
        die("done");
    }
    case "edit_phone_home":{
        if (!is_numeric($_POST["phone_home"])) die("error_invalid");
        $user->set_phone_home($_POST["phone_home"]);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:phone_home"); }
        die("done");
    }
    case "edit_cell_home":{
        if (!is_numeric($_POST["phone_cell"])) die("error_invalid");
        $user->set_phone_cell($_POST["phone_cell"]);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:phone_cell"); }
        die("done");
    }
    case "edit_address":{
        $user->set_address($_POST["address"]);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:address"); }
        die("done");
    }
    case "edit_color":{
        $user->set_color($_POST["color"]);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:color"); }
        die("done");
    }
    case "change_language":{
        $user->set_language($_REQUEST["language"]);
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:language"); }
        die("done");
    }
	case "change_comment_sort":{
        $i = mysql_fetch_object(mysql_query("SELECT * FROM `users_prefs` WHERE `option` = 'comments_sort'"));
		if ($i->{$user->username} != $_REQUEST["sort"]) mysql_query("UPDATE `users_prefs` SET `{$user->username}` = '{$_REQUEST["sort"]}' WHERE `option` = 'comments_sort'");
        if (!isset($_REQUEST["inactive_user"])) { $user->set_last_seen("change_user_info:comment_sort"); }
        die("done");
    }
}
?>