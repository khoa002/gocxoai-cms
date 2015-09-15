<?php
session_start();
if (!isset($_SESSION["in"]) OR !isset($_SESSION["root_path"])) { header("Location: index.php",true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
header("Content-type: text/css");
$font_family = "'Noticia Text', serif";
//$font_family = "'Arial', sans-serif";
?>
body{
    font-family: <?php echo $font_family; ?>;
    font-size: 12pt;
    margin: 0;
    text-align: center;
    overflow: auto;
    background-color: #FFF;
}
#body{
    background: url(files/site_images/layout/pagebgs/light-tile.gif) transparent;
    background-attachment: fixed;
    background-repeat: repeat;
    <?php
        $alignment = array("left","center","right");
        echo "background-position: top {$alignment[array_rand($alignment,1)]};\n";
    ?>
}
a, a:link, a:visited{
    color: #DA251D;
    text-decoration: none;
    cursor: pointer;
    border: 0;
}
a:hover{
    color: #284000;
    background-color: #FFFF99;
    /*border-bottom: 1px dotted #284000;*/
}
input, select, textarea, button{
    font-family: <?php echo $font_family; ?>;
    font-size: 10pt;
    padding: 5px;
    margin: 5px;
    border: 1px solid #d8e5b8;
    background: #FFF;
}
button[type="submit"], .button{
    font-size: 1.15em;
    border: 3px solid #FFF;
    background: #669933;
    color: #FFF;
	font-weight: bold;
	text-transform: uppercase;
}
button[type="reset"], button.reset, button#new_qd, .resetbutton{
    font-size: 1em;
    border: 3px solid #FFF;
    background: #ff6666;
    color: #FFF;
}
button[type="submit"]:hover , button[type="reset"]:hover{
    color: #000 !important;
}
input:hover, select:hover, option:hover, textarea:hover, button:hover{
    background: #fff799;
	color: #000;
}
button[type="submit"]:hover, button.reset:hover, button#new_qd:hover{
    background: #fff799;
    color: #669933;
}
h2{
    color: #6B8E23;
    /* border-bottom: 1px dotted #6B8E23; */
    padding: 0px;
    margin: 0px;
    text-align: left;
    vertical-align: top;
}
h2.outside{
	padding: 3px;
}
h2.inside{
	border: none !important;
}
h4{
    border-bottom: 1px dotted #ccc;
    padding: 0;
    margin: 0;
    margin-bottom: 3px;
    text-align: left;
    vertical-align: top;
}
img {
    border: 0px;
    vertical-align: middle;
}
ul.list-qd-info{
    list-style-type: none;
    padding: 0px;
    margin: 0 0 0 16px;
}
ul.list-qd-info li{
    background-image: url('files/site_images/layout/info-16.png');
    background-repeat: no-repeat;
    background-position: 0px 0px;
    padding-left: 20px;
    line-height: 16px;
    margin: 5px 0 5px 0px;
}
hr{
    height: 1px;
    color: #EEB422;
}
fieldset legend{
	color: #6B8E23;
	font-weight: bold;
}
#content{
    padding: 0px;
}
#boGoVN-wrap, #boGoVN-wrap tr, #boGoVN-wrap td{
    font-size: 0.95em;
}
.wrap, .wrap-2pxborder{
    border: 5px solid #F0F0F0;
    background: #EEF3E2;
    margin: auto;
    padding: 3px;
}
.wrap-2pxborder{
	border-size: 2px !important;
}
.overlay{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    background-color: #d6ebc1;
    background: url('files/site_images/layout/overlaybg.png') !important;
}
.vline{
    width: 1px;
    height: 100%;
    background: none;
    border-left:1px solid #ccc;
    margin: 0 3px;
    display: inline-block;
}
.valign-middle, .valign-middle *{
    vertical-align: middle;
}
.helpmsg{
    border: 1px solid #7cc576;
    padding: 5px;
    padding-left: 40px;
    background-image: url('files/site_images/layout/help-32.png');
    background-color: #fff;
    background-repeat: no-repeat;
    background-position: 3px 50%;
    display: table-cell;
    vertical-align: middle;
    font-style: italic;
}

.qd_content_text{
    font-family: 'Noticia Text';
    font-size: 1.10em;
    letter-spacing: 1px;
    word-spacing: 2px;
}
p{ margin: 1.05em 0; }