<?php
if (isset($_REQUEST["session"])) session_id($_REQUEST["session"]);
session_start();
if (!isset($_SESSION["user"])) die();
require_once("{$_SESSION["root_path"]}/inc.php");

$user = new User($_SESSION["user"]);

if (isset($_REQUEST["toggleshow"])){
    $boGoVN_show = 1;
    $result = mysql_query("SELECT * FROM `users_options` WHERE `user` = '{$user->username}' AND `option` = 'boGoVN_show'");
    if (mysql_num_rows($result) == 0){
        mysql_query("INSERT INTO `users_options` (`user`,`option`,`value`) VALUES ('{$user->username}','boGoVN_show','1')");
    }else{
        $i = mysql_fetch_object($result);
        if ($i->value == 0) { mysql_query("UPDATE `users_options` SET `value` = '1' WHERE `user` = '{$user->username}' AND `option` = 'boGoVN_show'"); }
        else { mysql_query("UPDATE `users_options` SET `value` = '0' WHERE `user` = '{$user->username}' AND `option` = 'boGoVN_show'"); }
    }
}else{
    if (isset($_REQUEST["method"])){
        $allowed = array(0,1,2,3,4);
        $boGoVN_method = 4;
        if (in_array($_REQUEST["method"],$allowed)){ $boGoVN_method = $_REQUEST["method"]; }
        
        $result = mysql_query("SELECT * FROM `users_options` WHERE `user` = '{$user->username}' AND `option` = 'boGoVN_method'");
        if (mysql_num_rows($result) == 0){ mysql_query("INSERT INTO `users_options` (`user`,`option`,`value`) VALUES ('{$user->username}','boGoVN_method','{$boGoVN_method}')"); }
        else { mysql_query("UPDATE `users_options` SET `value` = '{$boGoVN_method}' WHERE `user` = '{$user->username}' AND `option` = 'boGoVN_method'"); }
    }
}
?>