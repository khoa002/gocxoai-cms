<?php
if (!isset($_SESSION["in"])) { die("This page cannot be directly accessed."); }
if (!isset($_SESSION["root_path"])) die();
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="robots" content="noindex,nofollow" />
        <meta name="keywords" content="news,local news, world news, current events, events" />
        <link type="image/x-icon" rel="shortcut icon" href="favicon.ico"/>
        <link type="text/css" href="style.php" rel="stylesheet" />
        <!-- Google API Key -->
		<script type="text/javascript" src="https://www.google.com/jsapi?key=ABQIAAAAxi1nvIAAbP6ggOwPHqn0NBRezPq6ymBtDAQ75hIlLVy2Zs3JfRQkQRejPsXLLxoE5NTUy7-JMaii5w"></script>
		<!-- jQuery -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <!-- jQuery UI -->
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
		<link type="text/css" href="jquery-ui/jquery-ui-1.9.1.custom.css" rel="stylesheet" />
        <?php if ($_SESSION["language"] == "vi") echo "<script type='text/javascript' src='jquery-ui/jquery.ui.datepicker-vi.js'></script>"; ?>
        <!-- qTip -->
        <script type="text/javascript" src="scripts/qtip2/jquery.qtip.min.js"></script>
        <link type="text/css" rel="stylesheet" href="scripts/qtip2/jquery.qtip.css"/>
		<!-- SWFUpload -->
		<script type="text/javascript" src="scripts/swfupload/swfupload.js"></script>
		<script type="text/javascript" src="scripts/swfupload/jquery.swfupload.js"></script>
        <!-- shadow -->
        <script type="text/javascript" src="scripts/jquery.textshadow.js"></script>
        <script type="text/javascript" src="scripts/shadow/jquery.shadow.js"></script>
        <link type="text/css" href="scripts/shadow/jquery.shadow.css" rel="stylesheet"/>
        <!-- scrollto -->
        <script type="text/javascript" src="scripts/scrollto/jquery.scrollTo-min.js"></script>
        <!-- fancybox -->
        <script type="text/javascript" src="scripts/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
        <link rel="stylesheet" href="scripts/fancybox/source/jquery.fancybox.css?v=2.0.4" type="text/css" media="screen" />
        <script type="text/javascript" src="scripts/fancybox/source/jquery.fancybox.pack.js?v=2.0.4"></script>
        <link rel="stylesheet" href="scripts/fancybox/source/helpers/jquery.fancybox-buttons.css?v=2.0.4" type="text/css" media="screen" />
        <script type="text/javascript" src="scripts/fancybox/source/helpers/jquery.fancybox-buttons.js?v=2.0.4"></script>
        <link rel="stylesheet" href="scripts/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=2.0.4" type="text/css" media="screen" />
        <script type="text/javascript" src="scripts/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=2.0.4"></script>
        <!-- colorpicker -->
        <link rel="stylesheet" media="screen" type="text/css" href="scripts/colorpicker/css/colorpicker.css" />
        <script type="text/javascript" src="scripts/colorpicker/js/colorpicker.js"></script>
        <!-- Google WebFont -->
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/webfont/1.0.24/webfont.js"></script>
        <link href='http://fonts.googleapis.com/css?family=Noticia+Text:400,400italic,700,700italic&subset=vietnamese' rel='stylesheet' type='text/css'>
        <!-- mediaplayer -->
        <script type="text/javascript" src="scripts/mediaplayer/jwplayer.js"></script>
        <!-- tinymce -->
        <script type="text/javascript" src="scripts/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
        <!-- audio player -->
        <script type="text/javascript" src="scripts/audio-player/audio-player.js"></script>
        <?php require_once("scripts_each_page.php"); ?>