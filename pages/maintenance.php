<?php
if (!isset($_SESSION["in"]))  die("This page cannot be directly accessed.");
require_once("{$_SESSION["root_path"]}/page_top.php");
?>
<?php $user->set_last_seen("viewpage:maintenance"); ?>
<script type="text/javascript">
$(function(){
    // setting the page title
    $(function(){ top.document.title = "<?php echo translate("Maintenance page","Trang bảo trì"); ?>"; });
    // qTip crap
    $.fn.qtip.defaults.position.target = $(".mascot");
    $.fn.qtip.defaults.position.my = "center left";
    $.fn.qtip.defaults.position.at = "center right";
    $.fn.qtip.defaults.style.classes = "ui-tooltip-green ui-tooltip-shadow";
})
</script>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="wrap">
<tr><td valign="center" style="text-align: left;">
    <?php if ($user->role < 1) echo translate("There's nothing here.","Không có gì hết."); else {
    ?>
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="font-size: 1.2em; font-weight: bold; text-decoration: underline;"><?php echo translate("Self address","Tự xưng (tôi)"); ?></td>
            <td style="font-size: 1.2em; font-weight: bold; text-decoration: underline;"><?php echo translate("Addressing others","Gọi người khác"); ?></td>
        </tr>
    <tr><td>
    <?php
    $a = mysql_query("SELECT * FROM `users`");
    while($b = mysql_fetch_object($a)){
        $c = mysql_query("SELECT * FROM `users`");
        while($d = mysql_fetch_object($c)){
            if ($d->username != $b->username){
                if (mysql_num_rows(mysql_query("SELECT * FROM `users_me` WHERE `current_user` = '{$b->username}' AND `target_user` = '{$d->username}'")) == 0){
                    mysql_query("INSERT INTO `users_me` (`current_user`,`target_user`) VALUES ('{$b->username}','{$d->username}')");
                }
                if (mysql_num_rows(mysql_query("SELECT * FROM `users_you` WHERE `current_user` = '{$b->username}' AND `target_user` = '{$d->username}'")) == 0){
                    mysql_query("INSERT INTO `users_you` (`current_user`,`target_user`) VALUES ('{$b->username}','{$d->username}')");
                }
            }
        }
    }
    
    $a = mysql_query("SELECT * FROM `users`");
    while($b = mysql_fetch_object($a)){
        echo "<div style='padding-left: 10px; font-weight: bold; text-align: left;'>{$b->display_name}...</div>";
        $c = mysql_query("SELECT * FROM `users`");
        while($d = mysql_fetch_object($c)){
            if ($d->username != $b->username){
                $e = mysql_fetch_object(mysql_query("SELECT * FROM `users_me` WHERE `current_user` = '{$b->username}' AND `target_user` = '{$d->username}'"));
                $rand = rand();
                echo "<div style='padding-left: 25px; font-size: 0.9em; text-align: left;'><strong>{$b->display_name}</strong> ".translate("<em>refers to self</em> when talking to","<em>tự xưng mình</em> khi nói chuyện với")." <strong>{$d->display_name}</strong>: <input type='text' id='{$b->username}-{$d->username}{$rand}' value='{$e->me}' style='".(empty($e->me) ? "border: 1px solid #F00;" : "")."'/></div>";
                echo "<script type='text/javascript'>
                $('#{$b->username}-{$d->username}{$rand}').change(function(){
                    $.post('/maintenance/update_user_titles.php', { do: 'update_me', current_user: '{$b->username}', target_user: '{$d->username}', value: $('#{$b->username}-{$d->username}{$rand}').val() }, function(data){
                        if (data == 'done'){
                            $('#{$b->username}-{$d->username}{$rand}').css({'border':'1px solid #0F0'});
                        }
                    });
                });
                </script>
                ";
            }
        }
    }
    ?>
    </td><td>
    <?php    
    $a = mysql_query("SELECT * FROM `users`");
    while($b = mysql_fetch_object($a)){
        echo "<div style='padding-left: 10px; font-weight: bold; text-align: left;'>{$b->display_name}...</div>";
        $c = mysql_query("SELECT * FROM `users`");
        while($d = mysql_fetch_object($c)){
            if ($d->username != $b->username){
                $e = mysql_fetch_object(mysql_query("SELECT * FROM `users_you` WHERE `current_user` = '{$b->username}' AND `target_user` = '{$d->username}'"));
                $rand = rand();
                echo "<div style='padding-left: 25px; font-size: 0.9em; text-align: left;'><strong>{$b->display_name}</strong> ".translate("<em>refers to</em>","<em>gọi</em>")." <strong>{$d->display_name}</strong> ".translate("","bằng").": <input type='text' id='{$b->username}-{$d->username}{$rand}' value='{$e->you}' style='".(empty($e->you) ? "border: 1px solid #F00;" : "")."'/></div>";
                echo "<script type='text/javascript'>
                $('#{$b->username}-{$d->username}{$rand}').change(function(){
                    $.post('/maintenance/update_user_titles.php', { do: 'update_you', current_user: '{$b->username}', target_user: '{$d->username}', value: $('#{$b->username}-{$d->username}{$rand}').val() }, function(data){
                        if (data == 'done'){
                            $('#{$b->username}-{$d->username}{$rand}').css({'border':'1px solid #0F0'});
                        }
                    });
                });
                </script>
                ";
            }
        }
    }
    ?>
    </td></tr>
    </table>
    <?php } ?>
</td></tr></table>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>