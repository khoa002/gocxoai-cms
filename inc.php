<?php
if (!isset($_SESSION["in"]) OR !isset($_SESSION["root_path"])) { die("This page cannot be directly accessed."); }

/* Database connection */
$server   = "localhost";
$username = "";
$password = "";
$db_name  = "";
if ( !$connection= mysql_connect( $server , $username , $password ) ) { die ( "Error connecting to the database... " . mysql_error()); }
if ( !$selection = mysql_select_db( $db_name , $connection ) ) { die ( "Error selecting the database... " . mysql_error()); }
mysql_query ( "SET NAMES 'utf8'" );
mysql_set_charset ( "utf8" , $connection );
//date_default_timezone_set("America/Phoenix");
error_reporting(-1);

/* File inclusions */
$include_folder = "{$_SESSION["root_path"]}/classes";
if (is_dir($include_folder) && $folder = opendir($include_folder)){
    while (false !== ($file = readdir($folder))){
        if ($file != "." && $file != ".."){ // ignore the . and .. folders
            $ext = substr($file,-3); // get the file extension
            if ($ext == "php"){ // check if it's a class file
                require_once ("{$include_folder}/{$file}");
            }
        }
    }
}

/* Special functions */
function replace_stuff($input){
	// translations
	$done = false;
	while (!$done){
		if (preg_match("/(?<=!t\[)(.*?)(?=\])/",$input,$match)){
			list($en,$vi) = explode(":::",$match[0]);
			$input = str_replace("!t[{$match[0]}]","<span class='translated' style='border-bottom: 1px dotted #fff;' title='".($_SESSION["language"] == "vi" ? $en : $vi)."'>".($_SESSION["language"] == "vi" ? $vi : $en)."</span>",$input);
		} else $done = true;
	}

	// internal links
	$done = false;
	$linkcount = 0;
	while (!$done){
		if (preg_match("/(?<=!l\[)(.*?)(?=\])/",$input,$match)){
			list($theid,$thetitle) = explode(":::",$match[0]);
			$link = "<a id='{$theid}{$linkcount}_titlelink'>{$thetitle}</a><script type='text/javascript'> $('#{$theid}{$linkcount}_titlelink').click(function(){ load_page('view_single_discussion.php?id={$theid}'); }); </script>";
			$input = str_replace("!l[{$match[0]}]",$link,$input);
			$linkcount++;
		} else $done = true;
	}

	// youtube videos
	// $done = false;
	// while (!$done){
		// if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $input, $match)) {
			// $video_id = $match[1];
			// $link = "<iframe width='420' height='315' src='http://www.youtube.com/embed/{$video_id}' frameborder='0' allowfullscreen></iframe>";
			// $input = str_replace($match[0],$link,$input);
		// } else $done = true;
	// }

    $user = new User($_SESSION["user"]);
    $input = str_replace("%title%",mb_convert_case($user->post_name, MB_CASE_TITLE, "UTF-8"),$input);
    $input = str_replace("%me%",mb_convert_case($user->post_me, MB_CASE_TITLE, "UTF-8"),$input);
    $input = str_replace("%you%",mb_convert_case($user->post_you, MB_CASE_TITLE, "UTF-8"),$input);

    // emoticons
    // $sad = array(":sad:",":(",":-(","):",")-:");
    // $input = str_replace($sad,"<img src='files/site_images/emoticons/sad.gif' style='vertical-align: middle;'/>",$input);

    // $cry = array(":cry:",":'(",":'-(",")':",")-':");
    // $input = str_replace($cry,"<img src='files/site_images/emoticons/cry.gif' style='vertical-align: middle;'/>",$input);

    // $happy = array(":happy:",":)","(:",":-)","(-:",":D",":-D");
    // $input = str_replace($happy,"<img src='files/site_images/emoticons/happy.gif' style='vertical-align: middle;'/>",$input);

    // $cool = array(":cool:");
    // $input = str_replace($cool,"<img src='files/site_images/emoticons/cool.gif' style='vertical-align: middle;'/>",$input);

    $input = make_clickable($input);

    return $input;
}

function translate($en,$vi){
    if (isset($_SESSION["language"])){
        switch($_SESSION["language"]){
            case "vi": return $vi;
            default: return $en;
        }
    }
    return $en;
}
function gogo($new_query){
    return $_SERVER['PHP_SELF'] . "?" . $new_query;
}
function go($new_query) {
    if (empty($_SERVER["QUERY_STRING"])) return "main.php?" . $new_query;

    $queries = array(); // the array to hold all queries

    // process the current query string
    $current_query_array = explode("&",$_SERVER["QUERY_STRING"]);
    foreach($current_query_array as $a){ // go through each query
        $a = explode("=",$a);
        if (isset($a[1])) $queries[$a[0]] = $a[1];
        else $queries[] = $a[0];
    }

    // process the new query
    $new_query_array = explode("&",$new_query);
    foreach($new_query_array as $a){ // go through each query
        $a = explode("=",$a);
        if (isset($a[1])) $queries[$a[0]] = $a[1];
        else $queries[] = $a[0];
    }

    // remove all uniques
    $queries = array_unique($queries);

    // put them together
    $str = "";
    foreach($queries as $k=>$a){
        if (!empty($str)) $str .= "&";
        if (!is_numeric($k)) $str .= "{$k}={$a}";
        else $str .= "{$a}";
    }

    return "main.php?{$str}";
}
function get_date($datetime){
    if ((!preg_match( "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/",$datetime)) OR ($datetime == "0000-00-00 00:00:00")) return translate("Unknown","Không biết");

    $day_names = array();
    $day_names[] = array("sunday","chủ nhật"); // 0
    $day_names[] = array("monday","thứ hai"); // 1
    $day_names[] = array("tuesday","thứ ba"); // 2
    $day_names[] = array("wednesday","thứ tư"); // 3
    $day_names[] = array("thursday","thứ năm"); // 4
    $day_names[] = array("friday","thứ sáu"); // 5
    $day_names[] = array("saturday","thứ bảy"); // 6

    $parts = explode(" ",$datetime);
    if (count($parts) > 1) { list($thedate,$thetime) = $parts; }
    else { $thedate = $parts[0]; $thetime = "00:00:00"; }
    if ($thetime == "00:00:00") { $time = strtotime($datetime); }
    else {
        if (isset($_SESSION["user"])){
            $user = new User($_SESSION["user"]);
            $offset = $user->timezone;
        } else $offset = -7;
        $time = strtotime($datetime) + ($offset * 60 * 60);
    }
    if ($_SESSION["language"] == "vi"){
        return ucfirst($day_names[date("w",$time)][1]) . ", " . strftime("%e-tháng %m, %Y",$time) . ($thetime == "00:00:00" ? "" : strftime(" %H:%M:%S",$time));
    } else return strftime("%A, %e-%B, %Y",$time) . ($thetime == "00:00:00" ? "" : strftime(" %H:%M:%S",$time));
}
function get_time($datetime){
    if ((!preg_match( "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/",$datetime)) OR ($datetime == "0000-00-00 00:00:00")) return translate("Unknown","Không biết");
    $parts = explode(" ",$datetime);
    if (count($parts) > 1) { list($thedate,$thetime) = $parts; }
    else { $thedate = $parts[0]; $thetime = "00:00:00"; }
    if ($thetime == "00:00:00") { $time = strtotime($datetime); }
    else {
        if (isset($_SESSION["user"])){
            $user = new User($_SESSION["user"]);
            $offset = $user->timezone;
        } else $offset = -7;
        $time = strtotime($datetime) + ($offset * 60 * 60);
    }
    return strftime("%H:%M:%S",$time);
}
function date_diff_short($start, $end = false, $offset = true, $num_only = false){
    if ((!preg_match( "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/",$start)) OR ($start == "0000-00-00 00:00:00")) return -1;
    if (($end) AND ((!preg_match( "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/",$end)) OR ($end == "0000-00-00 00:00:00"))) return -1;
    $user = new User($_SESSION["user"]);
    if ($offset) {
        $tz = date_default_timezone_get();
        date_default_timezone_set("UTC");
    }
    if (!$end) { $end = time(); }
    else { $end = strtotime($end); }

    $diff = abs($end - strtotime($start));

    $years = floor($diff / 31556926);
    if ($years >= 1){
        $output = round($diff / 31556926, 1);
        if ($offset) { date_default_timezone_set($tz); }
		if ($num_only) return $output;
        return $_SESSION["language"] == "vi" ? ("{$output} năm") : ("{$output} " . ($output > 1 ? "years" : "year"));
    }
    $diff -= ($years * 31556926);
    $months = floor($diff / 2629743.83);
    if ($months >= 1) {
        $output = round($diff / 2629743.83, 1);
        if ($offset) { date_default_timezone_set($tz); }
		if ($num_only) return $output;
        return $_SESSION["language"] == "vi" ? ("{$output} tháng") : ("{$output} " . ($output > 1 ? "months" : "month"));
    }
    $diff -= ($months*2629743.83);
    $days = floor($diff / 86400);
    if ($days >= 1) {
        $output = round($diff / 86400, 1);
        if ($offset) { date_default_timezone_set($tz); }
		if ($num_only) return $output;
        return $_SESSION["language"] == "vi" ? ("{$output} ngày") : ("{$output} " . ($output > 1 ? "days" : "day"));
    }
    $diff -= ($days*86400);
    $hours = floor($diff / 3600);
    if ($hours >= 1) {
        $output = round($diff / 3600, 1);
        if ($offset) { date_default_timezone_set($tz); }
		if ($num_only) return $output;
        return $_SESSION["language"] == "vi" ? ("{$output} tiếng") : ("{$output} " . ($output > 1 ? "hours" : "hour"));
    }
    $diff -= ($hours*3600);
    $minutes = floor($diff / 60);
    if ($minutes >= 1) {
        $output = round($diff / 60, 1);
        if ($offset) { date_default_timezone_set($tz); }
        return $_SESSION["language"] == "vi" ? ("{$output} phút") : ("{$output} " . ($output > 1 ? "minutes" : "minute"));
    }
    $diff -= ($minutes*60);
    $seconds = floor($diff);
    if ($seconds >= 1) {
        if ($offset) { date_default_timezone_set($tz); }
		if ($num_only) return $output;
        return $_SESSION["language"] == "vi" ? ("{$seconds} giây") : ("{$seconds} " . ($seconds > 1 ? "seconds" : "second"));
    }
    if ($offset) { date_default_timezone_set($tz); }
    return "0";
}
function date_diff_full($start, $end = false){
    if ((!preg_match( "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/",$start)) OR ($start == "0000-00-00 00:00:00")) return -1;
    if (($end) AND ((!preg_match( "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/",$end)) OR ($end == "0000-00-00 00:00:00"))) return -1;
    $user = new User($_SESSION["user"]);
    $tz = date_default_timezone_get();
    date_default_timezone_set("UTC");
    if (!$end) { $end = gmdate("U"); }
    else { $end = strtotime($end); }

    $diff = abs($end - strtotime($start));
    $years = floor($diff / 31556926); $diff -= ($years * 31556926);
    $months = floor($diff / 2629743.83); $diff -= ($months*2629743.83);
    $days = floor($diff / 86400); $diff -= ($days*86400);
    $hours = floor($diff / 3600); $diff -= ($hours*3600);
    $minutes = floor($diff / 60); $diff -= ($minutes*60);
    $seconds = floor($diff);

    date_default_timezone_set($tz);
    if ($years > 0) return ($_SESSION["language"] == "vi" ? ("{$years} năm") : ("{$years} " . ($years > 1 ? "years" : "year"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$months} tháng") : ("{$months} " . ($months > 1 ? "months" : "month"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$days} ngày") : ("{$days} " . ($days > 1 ? "days" : "day"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$hours} tiếng") : ("{$hours} " . ($hours > 1 ? "hours" : "hour"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$minutes} phút") : ("{$minutes} " . ($minutes > 1 ? "minutes" : "minute"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$seconds} giây") : ("{$seconds} " . ($seconds > 1 ? "seconds" : "second")));
    if ($months > 0) return ($_SESSION["language"] == "vi" ? ("{$months} tháng") : ("{$months} " . ($months > 1 ? "months" : "month"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$days} ngày") : ("{$days} " . ($days > 1 ? "days" : "day"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$hours} tiếng") : ("{$hours} " . ($hours > 1 ? "hours" : "hour"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$minutes} phút") : ("{$minutes} " . ($minutes > 1 ? "minutes" : "minute"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$seconds} giây") : ("{$seconds} " . ($seconds > 1 ? "seconds" : "second")));
    if ($days > 0) return ($_SESSION["language"] == "vi" ? ("{$days} ngày") : ("{$days} " . ($days > 1 ? "days" : "day"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$hours} tiếng") : ("{$hours} " . ($hours > 1 ? "hours" : "hour"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$minutes} phút") : ("{$minutes} " . ($minutes > 1 ? "minutes" : "minute"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$seconds} giây") : ("{$seconds} " . ($seconds > 1 ? "seconds" : "second")));
    if ($hours > 0) return ($_SESSION["language"] == "vi" ? ("{$hours} tiếng") : ("{$hours} " . ($hours > 1 ? "hours" : "hour"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$minutes} phút") : ("{$minutes} " . ($minutes > 1 ? "minutes" : "minute"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$seconds} giây") : ("{$seconds} " . ($seconds > 1 ? "seconds" : "second")));
    if ($minutes > 0) return ($_SESSION["language"] == "vi" ? ("{$minutes} phút") : ("{$minutes} " . ($minutes > 1 ? "minutes" : "minute"))) . ", " . ($_SESSION["language"] == "vi" ? ("{$seconds} giây") : ("{$seconds} " . ($seconds > 1 ? "seconds" : "second")));
    if ($seconds > 0) return $_SESSION["language"] == "vi" ? ("{$seconds} giây") : ("{$seconds} " . ($seconds > 1 ? "seconds" : "second"));
    return "0";
}

function get_next_date($input){
    if (!preg_match( "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/",$input)) return false;
    if ($input == "0000-00-00 00:00:00") return false;
    list($date,$time) = explode(" ",$input);
    list($year,$month,$day) = explode("-",$date);
    list($hour,$minute,$second) = explode(":",$time);
    $user = new User($_SESSION["user"]);
    $tz = date_default_timezone_get();
    date_default_timezone_set($user->region);
    $val = (date("Y") . "-{$month}-{$day} {$time}");
    if (date("U") < strtotime($val)) return $val;
    $val = ((date("Y") + 1) . "-{$month}-{$day} {$time}");
    date_default_timezone_set($tz);
    return $val;
}

function get_defined_activity($input){
	$last_activity = explode(":",$input);
	switch ($last_activity[0]){
		default: return "<span style=\"color: #FF0000; font-style: italic;\">{$input}</span>";
		case "added_discussion":
			$act_dis = new QuickDiscussion();
			$act_dis->load($last_activity[1]);
			switch($act_dis->parent_id){
				default:
					$the_parent_discussion = new QuickDiscussion();
					$the_parent_discussion->load($act_dis->parent_id);
					return translate("Posted comment for the discussion ","Đăng phản hồi cho bài ") . "\"<a onClick=\"load_page('view_single_discussion.php?id={$the_parent_discussion->id}')\">{$the_parent_discussion->title}</a>\"";
				case -1:
					$category = explode(":",$act_dis->category);
					switch($category[0]){
						default: return "<span style=\"color: #FF0000; font-style: italic;\">{$input}</span>";
						case "images_user_avatars":
							$user_being_viewed = new User($category[1]);
							return translate("Posted comment on {$user_being_viewed->name}'s avatar","Đăng phản hồi trong hình tượng trưng của {$user_being_viewed->name}");
					}
				case 0: return translate("Posted new discussion ","Đăng thảo luận mới ") . "\"<a onClick=\"load_page('view_single_discussion.php?id={$act_dis->id}')\">{$act_dis->title}</a>\"";
			}
		case "addnew_discussion": return translate("Adding a new post","Bắt đầu đăng bài mới");
		case "deleted_discussion": return translate("Deleted post #","Xóa bài #") . $last_activity[1];
		case "edited_discussion":
			if (mysql_num_rows(mysql_query("SELECT 1 FROM `quick_discussions` WHERE `id` = '{$last_activity[1]}'")) == 0){
				mysql_query("DELETE FROM `users_activities` WHERE `what` = '{$input}'");
				return false;
			}
			$act_dis = new QuickDiscussion();
			$act_dis->load($last_activity[1]);
			switch($act_dis->parent_id){
				default:
					$the_parent_discussion = new QuickDiscussion();
					$the_parent_discussion->load($act_dis->parent_id);
					return translate("Edited comment #{$act_dis->id} for the discussion ","Soạn lại phản hồi ##{$act_dis->id} cho bài ") . "\"<a onClick=\"load_page('view_single_discussion.php?id={$the_parent_discussion->id}')\">{$the_parent_discussion->title}</a>\"";
				case -1:
					$category = explode(":",$act_dis->category);
					switch($category[0]){
						default: return "<span style=\"color: #FF0000; font-style: italic;\">{$input}</span>";
						case "images_user_avatars":
							$user_being_viewed = new User($category[1]);
							return translate("Edited comment #{$act_dis->id} on {$user_being_viewed->name}'s avatar","Đăng phản hồi #{$act_dis->id} trong hình tượng trưng của {$user_being_viewed->name}");
					}
				case 0: return translate("Edited discussion ","Soạn lại thảo luận ") . "\"<a onClick=\"load_page('view_single_discussion.php?id={$act_dis->id}')\">{$act_dis->title}</a>\"";
			}
		case "editing_discussion":
			if (mysql_num_rows(mysql_query("SELECT 1 FROM `quick_discussions` WHERE `id` = '{$last_activity[1]}'")) == 0){
				mysql_query("DELETE FROM `users_activities` WHERE `what` = '{$input}'");
				return false;
			}
			$act_dis = new QuickDiscussion();
			$act_dis->load($last_activity[1]);
			return translate("Editing post ","Bắt đầu soạn lại bài ") . "\"<a onClick=\"load_page('view_single_discussion.php?id={$act_dis->id}')\">{$act_dis->title}</a>\"";
		case "front_page": return translate("Viewed the front page","Xem trang nhất");
		case "login": return translate("Logged in","Nhập vào hệ thống");
		case "logout": return translate("Logged out","Thoát khỏi hệ thống");
		case "search": return translate("Searching for ","Tìm từ ") . "\"{$last_activity[1]}\"";
		case "user_cp": return translate("Viewed the personal control panel","Xem bảng điều chỉnh cá nhân");
		case "view_discussion":
			if (mysql_num_rows(mysql_query("SELECT 1 FROM `quick_discussions` WHERE `id` = '{$last_activity[1]}'")) == 0){
				mysql_query("DELETE FROM `users_activities` WHERE `what` = '{$input}'");
				return false;
			}
			$act_dis = new QuickDiscussion();
			$act_dis->load($last_activity[1]);
			switch($act_dis->parent_id){
				default: return translate("Viewed comment ","Xem phản hồi ") . "\"<a onClick=\"load_page('view_single_discussion.php?id={$act_dis->id}')\">{$act_dis->title}</a>\"";
				case -1:
					$category = explode(":",$act_dis->category);
					switch($category[0]){
						default: return "<span style=\"color: #FF0000; font-style: italic;\">{$input}</span>";
						case "images_user_avatars":
							$user_being_viewed = new User($category[1]);
							return translate("Viewed comment on {$user_being_viewed->name}'s avatar","Xem phản hồi trong hình tượng trưng của {$user_being_viewed->name}");
					}
				case 0: return translate("Viewed discussion ","Xem thảo luận ") . "\"<a onClick=\"load_page('view_single_discussion.php?id={$act_dis->id}')\">{$act_dis->title}</a>\"";
			}
		case "view_discussions": return translate("Viewed the discussions list","Xem bảng thảo luận");
		case "view_image":
			switch($last_activity[1]){
				default: return "<span style=\"color: #FF0000; font-style: italic;\">{$input}</span>";
				case "images_user_avatars":
					$user_being_viewed = new User($last_activity[2]);
					return translate("Viewed <a onClick=\"javascript: load_page('view_image.php?type=images_user_avatars&id={$user_being_viewed->username}');\">{$user_being_viewed->name}'s avatar</a>","Xem hình tượng trưng của <a onClick=\"javascript: load_page('view_image.php?type=images_user_avatars&id={$user_being_viewed->username}');\">{$user_being_viewed->name}</a>");
			}
		case "viewpage":
			$retval = translate("Viewed ","Xem ") . " ";
			switch($last_activity[1]){
				default: $retval .= $c->last_activity; break;
				case "userslist": $retval .= "<a onClick=\"load_page('userslist.php');\">" . translate("user list","bảng danh thành viên") . "</a>"; break;
				case "profile":
					$user_profile_viewed = new User($last_activity[2]);
					$retval .= translate("<a onClick=\"load_page('profile.php?who={$user_profile_viewed->username}');\">{$user_profile_viewed->name}</a>'s profile","thông tin của <a onClick=\"load_page('profile.php?who={$user_profile_viewed->username}');\">{$user_profile_viewed->name}</a>");
					break;
			}
			return $retval;
	}
}

function _make_url_clickable_cb($matches) {
	$ret = '';
	$url = $matches[2];
	if ( empty($url) )
		return $matches[0];
	// removed trailing [.,;:] from URL
	if ( in_array(substr($url, -1), array('.', ',', ';', ':')) === true ) {
		$ret = substr($url, -1);
		$url = substr($url, 0, strlen($url)-1);
	}
	return $matches[1] . "<a href=\"$url\" rel=\"nofollow\" target=\"_blank\">$url</a>" . $ret;
}

// Wordpress make link clicklable functions
function _make_web_ftp_clickable_cb($matches) {
	$ret = '';
	$dest = $matches[2];
	$dest = 'http://' . $dest;
	if ( empty($dest) )
		return $matches[0];
	// removed trailing [,;:] from URL
	if ( in_array(substr($dest, -1), array('.', ',', ';', ':')) === true ) {
		$ret = substr($dest, -1);
		$dest = substr($dest, 0, strlen($dest)-1);
	}
	return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>" . $ret;
}
function _make_email_clickable_cb($matches) {
	$email = $matches[2] . '@' . $matches[3];
	return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
}
function make_clickable($ret) {
	$ret = ' ' . $ret;
	// in testing, using arrays here was found to be faster
	$ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_url_clickable_cb', $ret);
	$ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_web_ftp_clickable_cb', $ret);
	$ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);
	// this one is not in an array because we need it to run last, for cleanup of accidental links within links
	$ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
	$ret = trim($ret);
	return $ret;
}
// Wordpress make link clicklable functions end

function shorten_string($string, $wordsreturned) {
    $retval = $string;
    $array = explode(" ", $string);
    if (count($array)<=$wordsreturned) $retval = $string;
    else {
        array_splice($array, $wordsreturned);
        $retval = implode(" ", $array)." ...";
    }
    return $retval;
}

function scroll_to ( $element ) { return "$.scrollTo('{$element}',500);"; }
function boGoVN() {
    $user = new User($_SESSION["user"]);
    $boGoVN_method = 4;
    $result = mysql_query("SELECT * FROM `users_options` WHERE `user` = '{$user->username}' AND `option` = 'boGoVN_method'");
    if (mysql_num_rows($result) == 0){
        mysql_query("INSERT INTO `users_options` (`user`,`option`,`value`) VALUES ('{$user->username}','boGoVN_method','4')");
    }else{
        $i = mysql_fetch_object($result);
        $boGoVN_method = $i->value;
    }
    $var = "<table id='boGoVN-wrap' border='0' cellspacing='0' cellpadding='0'><tr><td valign='middle'><h2><img src='files/site_images/layout/keyboard-32.png' style='vertical-align: middle;'/><span style='vertical-align: middle;'> ".translate('Vietnamese keyboard','Bộ gõ tiếng Việt')."</span>:</h2><script type='text/javascript'> Mudim.DISPLAY_ID=['my-off','my-vni','my-telex','my-viqr','my-auto']; Mudim.SPELLCHECK_ID='my-checkspell'; Mudim.ACCENTRULE_ID='my-accentrule'; </script> <span style='display: inline-block;'><input id='my-off' onclick='Mudim.SetMethod(0);' type=radio name=my-method />Tắt </span><span style='display: inline-block;'><input id='my-vni' onclick='Mudim.SetMethod(1);' type=radio name=my-method />Vni </span><span style='display: inline-block;'><input id='my-telex' onclick='Mudim.SetMethod(2);' type=radio name=my-method />Telex  </span><span style='display: inline-block;'><input id='my-viqr' onclick='Mudim.SetMethod(3);' type=radio name=my-method />Viqr </span><span style='display: inline-block;'><input id='my-auto' onclick='Mudim.SetMethod(4);' type=radio name=my-method />Tự động</span><br/><span style='display: inline-block;'><input id='my-checkspell' onclick='Mudim.ToggleSpeller();' type=checkbox />Kiểm tra chính tả </span><span style='display: inline-block;'><input id='my-accentrule' onclick='Mudim.ToggleAccentRule();' type=checkbox />Bỏ dấu kiểu mới</span></td></tr></table>";
    $var .= "<script type='text/javascript'>
        $(function(){";
    switch($boGoVN_method){
        case 0: $var .= "$('input#my-off').attr('checked',true); Mudim.SetMethod(0);"; break;
        case 1: $var .= "$('input#my-vni').attr('checked',true); Mudim.SetMethod(1);"; break;
        case 2: $var .= "$('input#my-telex').attr('checked',true); Mudim.SetMethod(2);"; break;
        case 3: $var .= "$('input#my-viqr').attr('checked',true); Mudim.SetMethod(3);"; break;
        default: $var .= "$('input#my-auto').attr('checked',true); Mudim.SetMethod(4);"; break;
    }
    $var .= "$(\"input#my-off\").click(function(){
        $(\"#loadframe\").load(\"actions/boGoVN_settings.php?method=0\");
    });
    $(\"input#my-vni\").click(function(){
        $(\"#loadframe\").load(\"actions/boGoVN_settings.php?method=1\");
    });
    $(\"input#my-telex\").click(function(){
        $(\"#loadframe\").load(\"actions/boGoVN_settings.php?method=2\");
    });
    $(\"input#my-viqr\").click(function(){
        $(\"#loadframe\").load(\"actions/boGoVN_settings.php?method=3\");
    });
    $(\"input#my-auto\").click(function(){
        $(\"#loadframe\").load(\"actions/boGoVN_settings.php?method=4\");
    });
    });
    </script>";
    return $var;
}

function global_check(){
    unset($_SESSION["global_qtip"]);
    unset($_SESSION["global_errors"]);
    unset($_SESSION["page_path"]);
    $user = new User($_SESSION["user"]);
    $global_errors = array(); // this array holds any arrays that should be outputted to the current user
    // check user profile to see if it's missing anything
    if ($user->dob == "0000-00-00 00:00:00" OR empty($user->dob)){
        $global_errors[] = translate("The system does not have your <strong>date of birth</strong> on file, please update this now.","Hệ thống không có <strong>ngày, tháng năm sinh</strong> của {$user->you}, xin vui lòng cập nhật thông tin này.") . "<script type='text/javascript'> $(function(){ $('#line_birthday').css('background-color','#FFCCCC'); });</script>";
        $global_qtip = translate("Please provide the missing information. You will not being to view the website if you have not completed this section of the site.","Xin vui lòng cung cấp thông tin thiếu xót. {$user->you} sẻ không được vào hệ thống nếu {$user->you} chưa cung cấp đầy đủ phần thông tin cá nhân này.");
        $_SESSION["page_path"] = "{$_SESSION["root_path"]}/pages/usercp.php";
    }
    if ($user->email == "" OR empty($user->email)){
        $global_errors[] = translate("The system does not have your <strong>email</strong> on file, please update this now.","Hệ thống không có <strong>địa chỉ điện thư</strong> của {$user->you}, xin vui lòng cập nhật thông tin này.") . "<script type='text/javascript'> $(function(){ $('#line_email').css('background-color','#FFCCCC'); });</script>";
        $global_qtip = translate("Please provide the missing information. You will not being to view the website if you have not completed this section of the site.","Xin vui lòng cung cấp thông tin thiếu xót. {$user->you} sẻ không được vào hệ thống nếu {$user->you} chưa cung cấp đầy đủ phần thông tin cá nhân này.");
        $_SESSION["page_path"] = "{$_SESSION["root_path"]}/pages/usercp.php";
    }
    if ($user->address == "" OR empty($user->address)){
        $global_errors[] = translate("The system does not have your <strong>home address</strong> on file, please update this now.","Hệ thống không có <strong>địa chỉ nhà</strong> của {$user->you}, xin vui lòng cập nhật thông tin này.") . "<script type='text/javascript'> $(function(){ $('#line_address').css('background-color','#FFCCCC'); });</script>";
        $global_qtip = translate("Please provide the missing information. You will not being to view the website if you have not completed this section of the site.","Xin vui lòng cung cấp thông tin thiếu xót. {$user->you} sẻ không được vào hệ thống nếu {$user->you} chưa cung cấp đầy đủ phần thông tin cá nhân này.");
        $_SESSION["page_path"] = "{$_SESSION["root_path"]}/pages/usercp.php";
    }
    if ($user->phone_home  == "" OR empty($user->phone_home) OR $user->phone_home == 0){
        $global_errors[] = translate("The system does not have your <strong>home phone number</strong> on file, please update this now.","Hệ thống không có <strong>số điện thoại nhà</strong> của {$user->you}, xin vui lòng cập nhật thông tin này.") . "<script type='text/javascript'> $(function(){ $('#line_phone_home').css('background-color','#FFCCCC'); });</script>";
        $global_qtip = translate("Please provide the missing information. You will not being to view the website if you have not completed this section of the site.","Xin vui lòng cung cấp thông tin thiếu xót. {$user->you} sẻ không được vào hệ thống nếu {$user->you} chưa cung cấp đầy đủ phần thông tin cá nhân này.");
        $_SESSION["page_path"] = "{$_SESSION["root_path"]}/pages/usercp.php";
    }
    if ($user->phone_cell   == "" OR empty($user->phone_cell ) OR $user->phone_cell  == 0){
        $global_errors[] = translate("The system does not have your <strong>cell phone number</strong> on file, please update this now.","Hệ thống không có <strong>số điện thoại di động</strong> của {$user->you}, xin vui lòng cập nhật thông tin này.") . "<script type='text/javascript'> $(function(){ $('#line_phone_cell').css('background-color','#FFCCCC'); });</script>";
        $global_qtip = translate("Please provide the missing information. You will not being to view the website if you have not completed this section of the site.","Xin vui lòng cung cấp thông tin thiếu xót. {$user->you} sẻ không được vào hệ thống nếu {$user->you} chưa cung cấp đầy đủ phần thông tin cá nhân này.");
        $_SESSION["page_path"] = "{$_SESSION["root_path"]}/pages/usercp.php";
    }
    if (!$user->avatar_exists()){
        $global_errors[] = translate("The system does not have your <strong>avatar</strong> on file, please update this now.","Hệ thống không có <strong>hình tượng trưng</strong> của {$user->you}, xin vui lòng cập nhật thông tin này.") . "<script type='text/javascript'> $(function(){ $('#line_avatar').css('background-color','#FFCCCC'); });</script>";
        $global_qtip = translate("Please provide the missing information. You will not being to view the website if you have not completed this section of the site.","Xin vui lòng cung cấp thông tin thiếu xót. {$user->you} sẻ không được vào hệ thống nếu {$user->you} chưa cung cấp đầy đủ phần thông tin cá nhân này.");
        $_SESSION["page_path"] = "{$_SESSION["root_path"]}/pages/usercp.php";
    }
    if (isset($global_qtip)){ $_SESSION["global_qtip"] = $global_qtip; }
    if (!empty($global_errors)){ $_SESSION["global_errors"] = $global_errors; }
}

function isAnimatedGif($filename) { return (bool)preg_match('#(\x00\x21\xF9\x04.{4}\x00\x2C.*){2,}#s', file_get_contents($filename)); }
function isMobile(){
	if(strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') || strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')) return true;
	return false;
}
function invert_color($color){
    $color = str_replace('#', '', $color);
    if (strlen($color) != 6){ return '000000'; }
    $rgb = '';
    for ($x=0;$x<3;$x++){
        $c = 255 - hexdec(substr($color,(2*$x),2));
        $c = ($c < 0) ? 0 : dechex($c);
        $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
    }
    return '#'.$rgb;
}

function mysql_add_column($db, $column, $column_attr = "VARCHAR( 255 ) NULL" ){
    $exists = false;
    $columns = mysql_query("SHOW COLUMNS FROM `{$db}`");
    while($c = mysql_fetch_assoc($columns)){
        if($c['Field'] == $column){
            $exists = true;
            break;
        }
    }
    if(!$exists){
        if (!mysql_query("ALTER TABLE `{$db}` ADD `{$column}` {$column_attr}")) return false;
		return true;
    }
	return false;
}

// categories functions
$categories = array(
	"entertainment" => translate("Entertainment","Giải trí"),
	"general" => translate("General discussion","Thảo luận chung"),
	"history" => translate("History","Lịch sử"),
	"journal" => translate("Journal","Nhật ký"),
	"knowledge" => translate("Knowledge","Kiến thức"),
	"learning" => translate("Learning","Học hỏi"),
	"news" => translate("News","Tin tức")
);
function get_qd_categories(){
	global $categories;
	return $categories;
}
function get_qd_category_desc($cat){
	global $categories;
	if (!array_key_exists($cat,$categories)) return false;
	return $categories[$cat];
}

// specific functions
function single_discussion_usort_read_status($a, $b){
	if (strtotime($a["when"]) == strtotime($b["when"])) return 0;
	if (strtotime($a["when"]) < strtotime($b["when"])) return 1;
	return -1;
}
function calculatePostEXP($body){
	$exp_per_char = 0.8;
	$bodystripped = strip_tags($body);
	$bodystripped = trim($bodystripped);
	$bodystripped = str_replace(" ","",$bodystripped);
	$len = strlen($bodystripped);
	return floor(($exp_per_char * $len) + 94);
}

/* creates a compressed zip file */
function create_zip($files = array(),$destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file) {
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		//add the files
		foreach($valid_files as $file) {
			$zip->addFile($file,$file);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

		//close the zip -- done!
		$zip->close();

		//check to make sure the file exists
		return file_exists($destination);
	}
	else return false;
}
?>