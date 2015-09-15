<?php
session_start();
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["user"])) die();
require_once("{$_SESSION["root_path"]}/inc.php");
if (!isset($_REQUEST["id"])) die(translate("No ID specified.","Không số định bài."));
$user = new User($_SESSION["user"]);
$discussion = new QuickDiscussion();
$discussion->load($_REQUEST["id"]);

$image_query = mysql_query("SELECT * FROM `images_general` WHERE `attachedto` = 'quick_discussions:{$discussion->id}'");
$post_image_count = 0;
$thumbnail_images_general_id = -9;
echo "<fieldset style='display: inline-block;'><legend align='center' style='font-size: 0.8em;'>".translate("Images","Hình")."</legend>";
if (!empty($discussion->thumbnail)){
	if (substr($discussion->thumbnail,0,1) == "!"){
		$a = mysql_fetch_object(mysql_query("SELECT * FROM `images_general` WHERE `id` = '".substr($discussion->thumbnail,1)."'"));
		$thumbnail_images_general_id = $a->id;
		echo "<div style=\"display: inline-block; float: left; border: 1px solid #8dc63f;\">";
		echo "<div><a onClick=\"javascript: $('#quick_msg').tinymce().execCommand('mceInsertContent',false,'<img src=\'{$a->file_location}\'/>');\"><img style='margin: 5px;' src='get_file.php?type=images_general&id={$a->id}&thumbnail=true' height='75'/></a></div>";
		echo "<div style=\"font-size: 0.7em; vertical-align: middle;\"><a id=\"post{$discussion->id}image{$post_image_count}_remove_featured_image_link\"><img src=\"/files/site_images/layout/cancel-16.png\" style=\"vertical-align: middle;\"/></a></div>";
		echo "<script type=\"text/javascript\">
			$('#post{$discussion->id}image{$post_image_count}_remove_featured_image_link').click(function(){
				$('#post{$discussion->id}dummy_frame').load('/actions/quick_discussion.php?action=remove_feature_image&id={$discussion->id}',function(response){
					if (response == 'done'){
						$('#discussion{$discussion->id}image_area').load('/show/qd_images.php?id={$discussion->id}');
					} else { alert('".translate("Something went wrong","Có vấn đề gì đó")."...'); }
				});
			});
		</script>";
		echo "</div>";
	} else {
		echo "<div style=\"display: inline-block; float: left; border: 1px solid #8dc63f;\">";
		echo "<div><a onClick=\"javascript: $('#quick_msg').tinymce().execCommand('mceInsertContent',false,'<img src=\'{$discussion->thumbnail}\'/>');\"><img style='margin: 5px;' src='get_file.php?type=post_image&id={$discussion->id}&thumbnail=true' height='75'/></a></div>";
		echo "</div>";
	}
	$post_image_count++;
}
if (mysql_num_rows($image_query) > 0){
	while ($img_row = mysql_fetch_object($image_query)){
		if ($thumbnail_images_general_id == $img_row->id) continue;
		echo "<div style=\"display: inline-block; float: left;\">";
		echo "<div><a id=\"post{$discussion->id}image{$post_image_count}_image_link\" onClick=\"javascript: $('#quick_msg').tinymce().execCommand('mceInsertContent',false,'<img src=\'{$img_row->file_location}\'/>');\"><img style='margin: 5px;' src='get_file.php?type=images_general&id={$img_row->id}&thumbnail=true' height='75'/></a></div>";
		echo "<div style=\"vertical-align: middle;\"><span id=\"post{$discussion->id}image{$post_image_count}_set_featured_image_wrap\"><a id=\"post{$discussion->id}image{$post_image_count}_set_featured_image_link\"><img src=\"/files/site_images/layout/star-16.png\" style=\"vertical-align: middle;\"/></a></span><span id=\"post{$discussion->id}image{$post_image_count}_delete_image_wrap\"><a id=\"post{$discussion->id}image{$post_image_count}_delete_image_link\"><img src=\"/files/site_images/layout/close-16.png\" style=\"vertical-align: middle;\"/></a></span></div>";
		echo "</div>";
		echo "<script type=\"text/javascript\">
			$('#post{$discussion->id}image{$post_image_count}_set_featured_image_link').click(function(){
				$('#post{$discussion->id}dummy_frame').load('/actions/quick_discussion.php?action=set_feature_image&id={$discussion->id}&file=!{$img_row->id}',function(response){
					if (response == 'done'){
						$('#discussion{$discussion->id}image_area').load('/show/qd_images.php?id={$discussion->id}');
					} else { alert('".translate("Something went wrong","Có vấn đề gì đó")."...'); }
				});
			});
			$('#post{$discussion->id}image{$post_image_count}_delete_image_link').click(function(){
				if (confirm('".translate("Are you sure?",$user->you." chắc không?")."')){
					$('#post{$discussion->id}dummy_frame').load('/actions/files.php?type=images_general&action=delete&id={$img_row->id}',function(response){
						if (response == 'done'){
							$('#discussion{$discussion->id}image_area').load('/show/qd_images.php?id={$discussion->id}');
						} else { alert('".translate("Something went wrong","Có vấn đề gì đó")."...'); }
					});
				}
			});
			$('#post{$discussion->id}image{$post_image_count}_image_link').qtip({ content: { text: '".translate("Click to insert image into post","Bấm để đưa hình vào bài viết")."' } });
			$('#post{$discussion->id}image{$post_image_count}_set_featured_image_link').qtip({ content: { text: '".translate("Set as featured image","Chọn làm hình tượng trưng")."' } });
			$('#post{$discussion->id}image{$post_image_count}_delete_image_link').qtip({ content: { text: '".translate("Delete image","Xóa hình")."' } });
		</script>";
		$post_image_count++;
	}
}
echo "</fieldset>";
?>