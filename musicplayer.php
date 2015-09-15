<?php
session_start();
if (!isset($_SESSION["in"]) OR !isset($_SESSION["root_path"]) OR !isset($_SESSION["user"])) { die(); }
require_once("{$_SESSION["root_path"]}/inc.php");

$sortby = "RAND()";
if(isset($_REQUEST["newest"])){ $sortby = "`created` DESC"; }

$result = mysql_query("SELECT * FROM `music_general` ORDER BY {$sortby}");
$soundFile = array();
while ($music = mysql_fetch_object($result)){ $soundFile[] = $music->file_location; }
if (!empty($soundFile)){
    $soundFile = implode(",",$soundFile); ?>
	<div id="musicPlayer">
		<div id="audioplayer" style="display: inline-block; float: left;"></div>
		<div style="text-align: center; font-size: 0.7em;"><a id="musicPlayer_randomize"><?php echo translate("Random songs","Chọn nhạc ngẩu nhiên"); ?></a> | <a id="musicPlayer_newest"><?php echo translate("New &rarr; Old","Mới &rarr; Củ"); ?></a></div>
	</div>
    <script type='text/javascript'>
        AudioPlayer.setup(
            "scripts/audio-player/player.swf",
            {   width: "100%",
                transparentpagebg: "yes",
                initialvolume: 100,
                animation: "no",
                leftbg: "8dc63f",
                lefticon: "006633",
                volslider: "006633",
                voltrack: "003333",
                rightbg: "8dc63f",
                rightbghover: "c4df9b",
                righticon: "006633",
                righticonhover: "ffff00",
                loader: "009900",
                track: "c4df9b",
                tracker: "7cc576",
                border: "006633",
                skip: "006633",
                text: "006633",
                bg: "c4df9b",
                autostart: "<?php echo isset($_REQUEST["autostart"]) ? "yes" : "no"; ?>"
            }
        );
        AudioPlayer.embed("audioplayer", {soundFile: "<?php echo $soundFile; ?>"});
        $("#musicPlayer_randomize").click(function(){
            $("#musicPlayer").hide("slide",{direction: "left"},500,function(){
                $("#musicPlayer").load("musicplayer.php?autostart",function(){
                    $("#musicPlayer").show("slide",{direction: "right"},500);
                });
            });
        });
        $("#musicPlayer_newest").click(function(){
            $("#musicPlayer").hide("slide",{direction: "left"},500,function(){
                $("#musicPlayer").load("musicplayer.php?newest&autostart",function(){
                    $("#musicPlayer").show("slide",{direction: "right"},500);
                });
            });
        });
    </script>
    
<?php
}
?>
