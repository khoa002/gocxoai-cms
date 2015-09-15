<?php
if (!isset($_SESSION["in"])) { die("This page cannot be directly accessed."); }
if (!isset($_SESSION["root_path"])) die();
?>
<script type="text/javascript">
$(function(){
	$(".shadowmybox").shadow("raise");
	$("button").shadow();
	$(".wrap,.wrap-2pxborder").shadow();
	$(".shadowme-reg").shadow();
	$(".qtip_me").qtip();
});
function isNumber(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}
</script>