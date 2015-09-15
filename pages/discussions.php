<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");

$user = new User($_SESSION["user"]);
$user->set_last_seen("view_discussions");

$total_discussions = mysql_num_rows(mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` = '0'"));
$perRow = (isMobile() ? 1 : 3);
$numPerPage_default = 30;
$numPerPage = $numPerPage_default;
if (isMobile()) $numPerPage = 10;
elseif (isset($_REQUEST["discussion_numperpage"]) AND ($_REQUEST["discussion_numperpage"] != 0)) $numPerPage = $_REQUEST["discussion_numperpage"];
if (isset($_REQUEST["numPerPage"]) AND $_REQUEST["numPerPage"] != 0) $numPerPage = $_REQUEST["numPerPage"];
elseif (isset($_REQUEST["numPerPage"]) AND $_REQUEST["numPerPage"] == 0) $numPerPage = $numPerPage_default;
setcookie("discussion_numperpage",$numPerPage,0,"/");
$numPerPage_array = array();
$i = $numPerPage_default;
while ($i < $total_discussions){
	$numPerPage_array[] = $i;
	$i += $numPerPage_default;
}
$col_percentage = (isMobile() ? 95 : floor((100 / $perRow) - 1));

$start = (isset($_REQUEST["discussion_start"]) ? $_REQUEST["discussion_start"] : 0);
$sort_method = (isset($_REQUEST["discussion_sortmethod"]) ? $_REQUEST["discussion_sortmethod"] : 1);
$sort_method_array = array(1,2,3,4,5);
if (isset($_REQUEST["start"])){
	$start = $_REQUEST["start"];
	setcookie("discussion_start",$start,0,"/");
}
if (isset($_REQUEST["sortmethod"]) AND in_array($_REQUEST["sortmethod"],$sort_method_array)){
	$sort_method = $_REQUEST["sortmethod"];
	setcookie("discussion_sortmethod",$sort_method,0,"/");
}

require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
require_once("{$_SESSION["root_path"]}/page_top.php");
// add a column to the quick_discussion_read_status table if doesn't exist
mysql_add_column("quick_discussions_read_status",$user->username,"DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00'");
?>
<script type="text/javascript">
$(function(){
    // setting the page title
	top.document.title = "<?php echo translate("Discussions","Thảo luận"); ?>";
	$(".post_image").fancybox({
		openEffect	: "elastic",
		closeEffect	: "elastic",
		type : "image",
		padding : 5,
		helpers : { title : { type : "over" } }
	});
});
</script>
<div style="font-size: 1.2em; font-weight: bold; margin: 10px;"><a href="javascript:;" onClick="javascript: load_page('addnew_discussion.php?start=<?php echo $start; ?>');"><img src="files/site_images/layout/add-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Add new discussion","Đăng thảo luận mới"); ?></span></a></div>
<?php
	$last_page = ceil($total_discussions / $numPerPage);

	if ($start == 0) $current_page = 1;
	else $current_page = floor(($start / $numPerPage) + 1);
	
	$categories = get_qd_categories();
	if (isset($_REQUEST["category"]) AND array_key_exists($_REQUEST["category"],$categories)) $display_category = $_REQUEST["category"];
	
	switch($sort_method){
		default: $sort_string = "`last_touched` DESC, `edited` DESC, `created` DESC"; break;
		case 1: $sort_string = "`last_touched` DESC, `edited` DESC, `created` DESC"; break;
		case 2: $sort_string = "`last_touched` ASC, `edited` ASC, `created` ASC"; break;
		case 3: $sort_string = "`title` ASC, `last_touched` DESC, `edited` DESC, `created` DESC"; break;
		case 4: $sort_string = "`title` DESC, `last_touched` DESC, `edited` DESC, `created` DESC"; break;
		case 5: $sort_string = "`comment_count` DESC, `last_touched` DESC, `edited` DESC, `created` DESC"; break;
	}
	
	$query_string = "SELECT * FROM `quick_discussions` WHERE `parent_id` = '0'";
	if (isset($display_category)) $query_string .= " AND `category` = '{$display_category}'";
	$query_string .= " ORDER BY {$sort_string} LIMIT {$start}, {$numPerPage}";
	$result = mysql_query($query_string);
?>
	<div style="margin: 5px 0; font-size: 0.8em; clear: both;">
		<span class="wrap" style="margin: 5px;">
			<img src="files/site_images/layout/sort-16.png" style="vertical-align: middle;"/>
			<span style="display: inline-block; vertical-align: middle;">
				<strong><?php echo translate("Sort by","Sắp xếp theo"); ?>:</strong>
				<select style="margin: 5px;" onChange="javascript: changeSort(this.options[this.selectedIndex].value);">
					<?php
					if ($sort_method == 1) echo "<option selected>" . translate("Newest","Mới nhất") . "</option>";
					else echo "<option value=\"1\">".translate("Newest","Mới nhất")."</option>";
					echo " | ";
					if ($sort_method == 2) echo "<option selected>" . translate("Oldest","Củ nhất") . "</option>";
					else echo "<option value=\"2\">".translate("Oldest","Củ nhất")."</option>";
					echo " | ";
					if ($sort_method == 3) echo "<option selected>A &rarr; Z</option>";
					else echo "<option value=\"3\">A &rarr; Z</option>";
					echo " | ";
					if ($sort_method == 4) echo "<option selected>Z &rarr; A</option>";
					else echo "<option value=\"4\">Z &rarr; A</option>";
					if ($sort_method == 5) echo "<option selected>" . translate("Most commented","Trả lời nhiều nhất") . "</option>";
					else echo "<option value=\"5\">" . translate("Most commented","Trả lời nhiều nhất") . "</option>";
					?>
				</select>
				<script type="text/javascript">
				function changeSort(m){
					if (m === "") return false;
					load_page("discussions.php?sortmethod=" + m + "&start=0");
				}
				</script>
			</span>
			<?php if (!isMobile()): ?>
			<span style="display: inline-block; vertical-align: middle;">
				<strong><?php echo translate("Discussions per page","Số bài mổi trang"); ?>:</strong>
				<select style="margin: 5px;" onChange="javascript: changeNumPerPage(this.options[this.selectedIndex].value);">
					<?php
					foreach($numPerPage_array as $a){
						if ($a == $numPerPage) echo "<option selected>{$a}</option>";
						else echo "<option value=\"{$a}\">{$a}</option>";
					}
					if ($numPerPage == $total_discussions) echo "<option selected>".translate("All","Tất cả")."</option>";
					else echo "<option value=\"{$total_discussions}\">".translate("All","Tất cả")."</option>";
					?>
				</select>
				<script type="text/javascript">
				function changeNumPerPage(m){
					if (m === "") return false;
					load_page("discussions.php?numPerPage=" + m);
				}
				</script>
			</span>
			<?php endif; ?>
		</span>
	</div>
<?php
	if ($numPerPage < $total_discussions):
		echo "<div style=\"font-size: 0.8em; clear: both;\">";
		if ($start >= $numPerPage):
			$newStart = $start - $numPerPage;
			echo "<button type=\"button\" class=\"button\" style=\"font-size: 0.9em;\" onClick=\"javascript: load_page('discussions.php?start={$newStart}');\">< ".translate("Previous page","Trang trước")."</button>";
		endif;
		echo "<label name=\"current_page_top\" for=\"current_page_top\">".translate("Page ","Trang ")."</label><input id=\"current_page_top\" name=\"current_page_top\" type=\"text\" value=\"{$current_page}\" size=\"2\" style=\"font-size: 0.9em; text-align: center;\"/>".translate(" of "," trên ").($current_page < $last_page ? "<a href=\"javascript:;\" onClick=\"javascript: load_page('discussions.php?start=" . (($last_page-1)*$numPerPage) . "');\">{$last_page}</a>" : "{$last_page}");
		echo "<script type=\"text/javascript\">
			$('#current_page_top').change(function(){
				if (($(this).val() > 0 && $(this).val() <= {$last_page}) && $(this).val() != {$current_page}){
					var newStart = ($(this).val() - 1) * {$numPerPage};
					load_page('discussions.php?start=' + newStart);
				}
			});
		</script>";
		if ($current_page < $last_page):
			$newStart = $start + $numPerPage;
			echo "<button type=\"button\" class=\"button\" style=\"font-size: 0.9em;\" onClick=\"javascript: load_page('discussions.php?start={$newStart}');\">".translate("Next page","Trang kế")." ></button>";
		endif;
		echo "</div>";
	endif;
?>
<?php
$count = 0;
$column = 1;
while ($row = mysql_fetch_object($result)){
	if ($count > 0 AND $column > $perRow) $column = 1;
	$post = new QuickDiscussion();
	$post->load($row->id);
	
	$numComments = $post->comment_count;
	if ($numComments > 0){
		$object = mysql_fetch_object(mysql_query("SELECT * FROM `quick_discussions` WHERE `parent_id` = '{$post->id}' ORDER BY `last_touched` DESC LIMIT 1"));
		$last_comment = new QuickDiscussion();
		$last_comment->load($object->id);
	}
	?>
	<div class="wrap" style="text-align: left; font-size: 0.9em; border-width: 2px; <?php echo isMobile() ? "margin: 2px 0;" : "margin: 2px 0px; display: inline-block;"; ?> width: <?php echo $col_percentage; ?>%; vertical-align: middle; padding: 3px;<?php if($count % $perRow === 0) echo " clear: both;"; ?>">
		<span id="postline_postid<?php echo $post->id; ?>" style="display: inline-block; font-size: 0.75em; vertical-align: middle;"><?php echo $post->id; ?></span>
		<span id="postline_userpost<?php echo $post->id; ?>" style="display: inline-block; width: 10px; height: 10px; background-color: #<?php echo $post->qd_author->color; ?>; margin: auto; vertical-align: middle;"></span>
		<span style="display: inline-block; vertical-align: middle; font-size: 0.8em; vertical-align: middle;"><a onClick="javascript: load_page('view_single_discussion.php?id=<?php echo $post->id; ?>');"><?php echo empty($post->title) ? translate("No title","Không chủ đề") : shorten_string($post->title,5); ?></a></span>
		<?php if ($numComments > 0): ?>
		<span id="postline_comments<?php echo $post->id; ?>" style="vertical-align: middle;"><span style="font-size: 0.8em; vertical-align: middle;"><?php echo $numComments; ?></span> <img src="files/site_images/layout/comment-16.png" style="vertical-align: middle;"/></span>
		<?php endif; ?>
	</div>
	<script type="text/javascript">
	$("#postline_userpost<?php echo $post->id; ?>").qtip({
		content: '<span style=\"font-size: 0.75em;\"><?php echo (!empty($post->edited) AND ($post->edited != "1970-01-01 00:00:00")) ? "<strong style=\"border-bottom: 1px solid #{$post->qd_author->color};\">".($post->qd_author->username == $user->username ? translate("You",$user->you) : $post->qd_author->name)."</strong>".translate(" edited this post "," soạn lại bài này ").(date_diff_short($post->edited) == "0" ? translate("just now","mới đây") : (date_diff_short($post->edited) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($post->edited)." ".translate("ago","trước"))) : "<strong style=\"border-bottom: 1px solid #{$post->qd_author->color};\">".($post->qd_editor->username == $user->username ? translate("You",$user->you) : $post->qd_editor->name)."</strong>".translate(" posted this "," đăng bài này ").(date_diff_short($post->created) == "0" ? translate("just now","mới đây") : (date_diff_short($post->created) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($post->created)." ".translate("ago","trước"))) ?></span>'
		<?php
		if (isMobile() OR $column == 3) echo ",position: { my: 'center right', at: 'center left' }";
		elseif ($column == 2) echo ",position: { my: 'bottom center', at: 'top center' }";
		?>
	});
	<?php if ($numComments > 0): ?>
	$("#postline_comments<?php echo $post->id; ?>").qtip({
		content: '<?php echo "<span style=\"font-size: 0.75em;\"><strong style=\"border-bottom: 1px solid #{$last_comment->qd_author->color};\">".($last_comment->qd_author->username == $user->username ? translate("You",$user->you) : $last_comment->qd_author->name)."</strong> ".translate("replied ","trả lời ").(date_diff_short($last_comment->last_touched) == "0" ? translate("just now...","mới đây...") : (date_diff_short($last_comment->last_touched) === -1 ? translate("[unknown]","[không biết]") : date_diff_short($last_comment->last_touched)." ".translate("ago","trước")))."</span>"; ?>'
		<?php
		if (isMobile() OR $column == 3) echo ",position: { my: 'center right', at: 'center left' }";
		elseif ($column == 2) echo ",position: { my: 'bottom center', at: 'top center' }";
		?>
	});
	<?php endif; ?>
	</script>
	<?php
	$column++;
	$count++;
}
?>
<?php
	if ($numPerPage < $total_discussions):
		echo "<div style=\"font-size: 0.8em; clear: both;\">";
		if ($start >= $numPerPage):
			$newStart = $start - $numPerPage;
			echo "<button type=\"button\" class=\"button\" style=\"font-size: 0.9em;\" onClick=\"javascript: load_page('discussions.php?start={$newStart}');\">< ".translate("Previous page","Trang trước")."</button>";
		endif;
		echo "<label name=\"current_page_top\" for=\"current_page_top\">".translate("Page ","Trang ")."</label><input id=\"current_page_top\" name=\"current_page_top\" type=\"text\" value=\"{$current_page}\" size=\"2\" style=\"font-size: 0.9em; text-align: center;\"/>".translate(" of "," trên ").($current_page < $last_page ? "<a href=\"javascript:;\" onClick=\"javascript: load_page('discussions.php?start=" . (($last_page-1)*$numPerPage) . "');\">{$last_page}</a>" : "{$last_page}");
		echo "<script type=\"text/javascript\">
			$('#current_page_top').change(function(){
				if (($(this).val() > 0 && $(this).val() <= {$last_page}) && $(this).val() != {$current_page}){
					var newStart = ($(this).val() - 1) * {$numPerPage};
					load_page('discussions.php?start=' + newStart);
				}
			});
		</script>";
		if ($current_page < $last_page):
			$newStart = $start + $numPerPage;
			echo "<button type=\"button\" class=\"button\" style=\"font-size: 0.9em;\" onClick=\"javascript: load_page('discussions.php?start={$newStart}');\">".translate("Next page","Trang kế")." ></button>";
		endif;
		echo "</div>";
	endif;
?>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>