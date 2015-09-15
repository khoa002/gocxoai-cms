<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
$user = new User($_SESSION["user"]);
$user->set_last_seen("viewpage:page_not_found:{$queries}");

require_once("{$_SESSION["root_path"]}/page_top.php");
?>
<script type="text/javascript">
$(function(){
    // setting the page title
    $(function(){ top.document.title = "<?php echo translate("Page not found","Trang không tồn tại"); ?>"; });
})
</script>
<div><?php echo translate("Page not found","Trang không tồn tại"); ?></div>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>