<?php
class User{
    var $username, $password, $display_name, $avatar, $active, $timezone, $region, $language, $role, $exp, $gold, $color, $last_seen, $last_awarded, $last_activity;
    var $dob, $email, $address, $phone_home, $phone_cell, $name_last, $name_middle, $name_first, $you, $me, $post_you, $post_me;
    var $dob_date, $dob_time, $dob_month, $dob_day, $dob_year;
    var $current_user, $title, $name, $name2, $post_name, $post_name2;
	const level_base_tnl = 5000, level_base_percentage = 100;
    
    function __construct($username){
        $query = mysql_query("SELECT * FROM `users` WHERE `username` = '{$username}'");
        if ($user = mysql_fetch_object($query)){
            $this->username = $user->username;
            $this->password = $user->password;
            $this->display_name = $user->display_name;
            $this->avatar = $user->avatar;
            $this->active = $user->active;
            $this->timezone = $user->timezone;
            $this->region = $user->region;
            $this->language = $user->language;
            $this->role = $user->role;
            $this->exp = $user->exp;
            $this->gold = $user->gold;
            $this->color = $user->color;
            $this->last_seen = $user->last_seen;
            $this->last_awarded = $user->last_awarded;
            $this->last_activity = $user->last_activity;
            
            $this->dob = $user->dob;
            $this->email = $user->email;
            $this->address = strtoupper($user->address);
            $this->phone_home = $user->phone_home;
            $this->phone_cell = $user->phone_cell;
            $this->name_last = $user->name_last;
            $this->name_middle = $user->name_middle;
            $this->name_first = $user->name_first;
            $this->you = mb_convert_case($user->you, MB_CASE_TITLE, "UTF-8");
            $this->me = mb_convert_case($user->me, MB_CASE_TITLE, "UTF-8");
            $this->post_you = mb_convert_case($user->you, MB_CASE_TITLE, "UTF-8");
            $this->post_me = mb_convert_case($user->me, MB_CASE_TITLE, "UTF-8");
            
            list($this->dob_date,$this->dob_time) = explode(" ", $this->dob);
            list($this->dob_year,$this->dob_month,$this->dob_day) = explode("-",$this->dob_date);
            
            if (isset($_SESSION["user"])) $this->current_user = mysql_fetch_object(mysql_query("SELECT * FROM `users` WHERE `username` = '{$_SESSION["user"]}'"));
            $this->title = "";
            $ignore = array("con","em","cháu");
            $title_only = array("ông ngoại","ông nội","ba","bà ngoại","bà nội","má");
            if ( isset($this->current_user) AND $this->current_user->username != $this->username ) {
				$select = mysql_query("SELECT * FROM `users_titles_you` WHERE `user` = '{$this->current_user->username}'");
				if ($row = mysql_fetch_assoc($select)){
					if (!in_array($row[$this->username],$ignore)){
                        $this->title = mb_convert_case($row[$this->username], MB_CASE_TITLE, "UTF-8");
                        $this->post_you = mb_convert_case($row[$this->username], MB_CASE_TITLE, "UTF-8");
                    }
				}
				$select = mysql_query("SELECT * FROM `users_titles_me` WHERE `user` = '{$this->current_user->username}'");
				if ($row = mysql_fetch_assoc($select)) $this->post_me = ucfirst($row[$this->username]);
            }
            
            $this->name = (!empty($this->title) ? ((!in_array(mb_convert_case($this->title, MB_CASE_LOWER, "UTF-8"),$title_only)) ? mb_convert_case($this->title, MB_CASE_TITLE, "UTF-8") . " " . $this->display_name : mb_convert_case($this->title, MB_CASE_TITLE, "UTF-8")) : $this->display_name);
            $this->name2 = (!empty($this->title) ? ((!in_array(mb_convert_case($this->title, MB_CASE_LOWER, "UTF-8"),$title_only)) ? mb_convert_case($this->title, MB_CASE_TITLE, "UTF-8") . " " . $this->name_first : mb_convert_case($this->title, MB_CASE_TITLE, "UTF-8")) : $this->name_first );
            $this->post_name = (!empty($this->title) ? ((!in_array(mb_convert_case($this->title, MB_CASE_LOWER, "UTF-8"),$title_only)) ? mb_convert_case($this->post_you, MB_CASE_TITLE, "UTF-8") . " " . $this->display_name : mb_convert_case($this->post_you, MB_CASE_TITLE, "UTF-8")) : $this->display_name);
            $this->post_name2 = (!empty($this->title) ? ((!in_array(mb_convert_case($this->title, MB_CASE_LOWER, "UTF-8"),$title_only)) ? mb_convert_case($this->post_you, MB_CASE_TITLE, "UTF-8") . " " . $this->name_first : mb_convert_case($this->post_you, MB_CASE_TITLE, "UTF-8")) : $this->name_first );
        } else unset($this);
    }
    function display(){
        return "<a onClick=\"javascript: load_page('view_image.php?type=images_user_avatars&id={$this->username}');\"><img style=\"border: 3px solid #{$this->color}\" src=\"get_file.php?type=images_user_avatars&id={$this->username}&thumbnail=true\" width=\"150\"/></a>";
    }
	function display_user_post_image(){
		return "<div><a class=\"post_image\" onClick=\"javascript: load_page('view_image.php?type=images_user_avatars&id={$this->username}');\"><img src=\"get_file.php?type=images_user_avatars&id={$this->username}&thumbnail=true\" height=\"50\" style=\"border: 3px solid #{$this->color};\"/></a></div><div><strong style=\"font-size: 0.8em;\"><a onClick=\"load_page('profile.php?who={$this->username}');\">{$this->name}</a></strong></div>";
	}
    function get_phone_home(){
        if ($this->phone_home == 0) return translate("Unknown","Không biết");
        if ($this->phone_home == 9999) return translate("None","Không có");
        if (isset($this->current_user) AND $this->current_user->region == "Asia/Ho_Chi_Minh"){
            switch(strlen($this->phone_home)){
                case 10:{
                    $first_part = substr($this->phone_home,0,4);
                    $second_part = substr($this->phone_home,4,3);
                    $third_part = substr($this->phone_home,7,3);
                    return "{$first_part} {$second_part} {$third_part}";
                }
                case 7:{
                    $first_part = substr($this->phone_home,0,4);
                    $second_part = substr($this->phone_home,4,3);
                    return "{$first_part} {$second_part}";
                }
                default: return $this->phone_home;
            }
        }
        switch(strlen($this->phone_home)){
            case 10:{
                $area_code = substr($this->phone_home,0,3);
                $first_part = substr($this->phone_home,3,3);
                $second_part = substr($this->phone_home,6,4);
                return "{$area_code}/{$first_part}-{$second_part}";
            }
            case 7:{
                $first_part = substr($this->phone_home,0,3);
                $second_part = substr($this->phone_home,3,4);
                return "{$first_part}-{$second_part}";
            }
            default: return $this->phone_home;
        }
    }
    function get_phone_cell(){
        if ($this->phone_cell == 0) return translate("Unknown","Không biết");
        if ($this->phone_cell == 9999) return translate("None","Không có");
        if (isset($this->current_user) AND $this->current_user->region == "Asia/Ho_Chi_Minh"){
            switch(strlen($this->phone_cell)){
                case 10:{
                    $first_part = substr($this->phone_cell,0,4);
                    $second_part = substr($this->phone_cell,4,3);
                    $third_part = substr($this->phone_cell,7,3);
                    return "{$first_part} {$second_part} {$third_part}";
                }
                case 7:{
                    $first_part = substr($this->phone_cell,0,4);
                    $second_part = substr($this->phone_cell,4,3);
                    return "{$first_part} {$second_part}";
                }
                default: return $this->phone_cell;
            }
        }
        switch(strlen($this->phone_cell)){
            case 10:{
                $area_code = substr($this->phone_cell,0,3);
                $first_part = substr($this->phone_cell,3,3);
                $second_part = substr($this->phone_cell,6,4);
                return "{$area_code}/{$first_part}-{$second_part}";
            }
            case 7:{
                $first_part = substr($this->phone_cell,0,3);
                $second_part = substr($this->phone_cell,3,4);
                return "{$first_part}-{$second_part}";
            }
            default: return $this->phone_cell;
        }
    }
    function get_address(){
        $address = $this->address;
        if (empty($address)) return translate("Unknown","Không biết");
        return $address;
    }
    function get_full_name(){
        $name = "";
        if (isset($this->current_user) AND $_SESSION["language"] == "vi"){
            if (!empty($this->name_last)) $name .= $this->name_last;
            if (!empty($this->name_middle)) $name .= " {$this->name_middle}";
            if (!empty($this->name_first)) $name .= " {$this->name_first}";
        } else {
            if (!empty($this->name_first)) $name .= "{$this->name_first}";
            if (!empty($this->name_middle)) $name .= " {$this->name_middle}";
            if (!empty($this->name_last)) $name .= " {$this->name_last}";
        }
        if (empty($name)) return translate("Unknown","Không biết");
        return $name;
    }
    function get_age( $full = true , $yearonly = false ) {
        if ($this->dob == "0000-00-00 00:00:00") return translate("Unknown","Không biết");
        $dobarray = explode(" ", $this->dob);
        list($year,$month,$day) = explode("-",$dobarray[0]);
        if (isset($dobarray[1]) AND $dobarray[1] != "00:00:00") { $time = $dobarray[1]; }
        $year_d = gmdate("Y") - $year;
        $month_d = gmdate("m") - $month;
        $day_d = gmdate("d") - $day;
        $current_months_number_of_days = gmdate("t");

        if ($day_d < 0) {
            $month_d--;
            $day_d += $current_months_number_of_days;
        }
        if ($month_d < 0) $year_d--;

        $fullval = "";

        if ($year_d > 0) {
            if ($year_d == 1) { $fullval = $year_d . " " . translate("year","năm"); }
            else { $fullval .= $year_d . " " . translate("years","năm"); }
        }
        if ($month_d > 0) {
            if ($month_d == 1) { $fullval .= ", " . $month_d . " " . translate("month","tháng"); }
            else { $fullval .= ", " . $month_d . " " . translate("months","tháng"); }
        }
        if ($day_d > 0) {
            if ($day_d == 1) { $fullval .= ", " . $day_d . " " . translate("day","ngày"); }
            else { $fullval .= ", " . $day_d . " " . translate("days","ngày"); }
        }
        $shortval = $year_d + ($month_d / 12) + ($day_d / 365);
        $shortval = sprintf("%2.2f " . translate("year-old","tuổi"),$shortval);

        if ($yearonly) { return $year_d; }
        if ($full) { return $fullval; }
        else { return $shortval; }
    }
    function get_next_birthday(){
        if ($this->dob == "0000-00-00 00:00:00") return false;
        list($date,$time) = explode(" ",$this->dob);
        list($year,$month,$day) = explode("-",$date);
        list($hour,$minute,$second) = explode(":",$time);
        $birthday_this_year = (date("Y") . "-{$month}-{$day} {$time}");
        if (time() < strtotime($birthday_this_year)){ return $birthday_this_year; }
        $birthday_next_year = ((date("Y") + 1) . "-{$month}-{$day} {$time}");
        return $birthday_next_year;
    }
    function set_password($password){
        $salt = mysql_fetch_object(mysql_query("SELECT `value` FROM `system` WHERE `name` = 'salt'"));
        $new_password = MD5($salt->value.$password);
        $query = mysql_query("UPDATE `users` SET `password` = '{$new_password}' WHERE `username` = '{$this->username}'");
        $this->password = $new_password;
    }
    function set_display_name($name){
        $query = mysql_query("UPDATE `users` SET `display_name` = '{$name}' WHERE `username` = '{$this->username}'");
        $this->display_name = $name;
    }
    function set_language($language){
        $query = mysql_query("UPDATE `users` SET `language` = '{$language}' WHERE `username` = '{$this->username}'");
        $this->language = $language;
    }
    function set_color($color){
        $query = mysql_query("UPDATE `users` SET `color` = '{$color}' WHERE `username` = '{$this->username}'");
        $this->color = $color;
    }
    function set_last_seen($activity){
		$max_db_user_activity_lines = 50;
        $now = gmdate("Y-m-d H:i:s");
        mysql_query("UPDATE `users` SET `last_seen` = '{$now}' , `last_activity` = '{$activity}', `exp` = `exp` +1 WHERE `username` = '{$this->username}'");
		if (mysql_num_rows(mysql_query("SELECT * FROM `users_activities` WHERE `username` = '{$this->username}' AND `what` = '{$activity}'")) > 0){
			$update_this_line = mysql_fetch_object(mysql_query("SELECT * FROM `users_activities` WHERE `username` = '{$this->username}' AND `what` = '{$activity}' ORDER BY `when` DESC"));
			mysql_query("UPDATE `users_activities` SET `when` = '{$now}' WHERE `id` = '{$update_this_line->id}'");
		} else {
			mysql_query("INSERT INTO `users_activities` (`username`,`when`,`what`) VALUES ('{$this->username}','{$now}','{$activity}')");
			$num_of_activities = mysql_num_rows(mysql_query("SELECT * FROM `users_activities` WHERE `username` = '{$this->username}'"));
			while ($num_of_activities > $max_db_user_activity_lines){
				$delete_this_line = mysql_fetch_object(mysql_query("SELECT * FROM `users_activities` WHERE `username` = '{$this->username}' ORDER BY `when` ASC"));
				mysql_query("DELETE FROM `users_activities` WHERE `id` = '{$delete_this_line->id}'");
				$num_of_activities = mysql_num_rows(mysql_query("SELECT * FROM `users_activities` WHERE `username` = '{$this->username}'"));
			}
		}
        $this->last_seen = $now;
        $this->last_activity = $activity;
    }
    function set_dob($year,$month,$day){
        $datetime = "{$year}-{$month}-{$day} 00:00:00";
        $query = mysql_query("UPDATE `users` SET `dob` = '{$datetime}' WHERE `username` = '{$this->username}'");
        $this->dob = $datetime;
    }
    function set_email($email){
        $query = mysql_query("UPDATE `users` SET `email` = '{$email}' WHERE `username` = '{$this->username}'");
        $this->email = $email;
    }
    function set_address($address){
        $query = mysql_query("UPDATE `users` SET `address` = '{$address}' WHERE `username` = '{$this->username}'");
        $this->address = $address;
    }
    function set_phone_home($number){
        $query = mysql_query("UPDATE `users` SET `phone_home` = '{$number}' WHERE `username` = '{$this->username}'");
        $this->phone_home = $number;
    }
    function set_phone_cell($number){
        $query = mysql_query("UPDATE `users` SET `phone_cell` = '{$number}' WHERE `username` = '{$this->username}'");
        $this->phone_cell = $number;
    }
    function set_full_name($name_last,$name_middle,$name_first){
        $query = mysql_query("UPDATE `users` SET `name_last` = '{$name_last}' WHERE `username` = '{$this->username}'");
        $query = mysql_query("UPDATE `users` SET `name_middle` = '{$name_middle}' WHERE `username` = '{$this->username}'");
        $query = mysql_query("UPDATE `users` SET `name_first` = '{$name_first}' WHERE `username` = '{$this->username}'");
        $this->name_last = $name_last;
        $this->name_middle = $name_middle;
        $this->name_first = $name_first;
    }
    function add_exp ( $exp ) {
        !mysql_query ( "UPDATE `users` SET `exp` = exp + {$exp} WHERE `username` = '{$this->username}'" );
        $this->exp += $exp;
    }
    function sub_exp ( $exp ) {
        mysql_query ( "UPDATE `users` SET `exp` = exp - {$exp} WHERE `username` = '{$this->username}'" );
        $this->exp -= $exp;
    }
    function avatar_exists(){
        if (empty($this->avatar)) return false;
        if (file_exists("{$_SESSION["root_path"]}/{$this->avatar}")) return true;
        return false;
    }
	function calculateLevel(){
		$inc = 5000;
		$prev = 0;
		$level_index = 1;
		$level = 1;
		$per = 0;
		$per2 = 0;
		$lexp = 0;
		while ($prev <= $this->exp){
			$prev = $prev + ($inc * $level_index);
			$level = $level_index;
			$lexp = $prev;
			$per = sprintf("%2.2f",($this->exp / $prev) * 100);
			$per2 = sprintf("%2.2f",($this->exp / $prev));
			$level_index++;
		}

		return array("level" => $level,"percentage" => $per,"percentage_decimal" => $per2, "current_level_exp" => $lexp);
	}
	function calc_level() {
        $level = 0;
        $previous_total = 0;
        $current_total = 0;
        $percentage_increment_per_level = self::level_base_percentage / 100;
		
		$the_current_exp = $this->exp;
        
        while ($current_total <= $the_current_exp){
            $level++;
            $previous_total = $current_total;
            $current_total += ((self::level_base_tnl * $level) * $percentage_increment_per_level);
        }
        $current_max_exp = $current_total - $previous_total;
        $current_exp = $the_current_exp - $previous_total;
        if ($current_max_exp > 0) $current_per = ($current_exp / $current_max_exp) * 100;
        else $current_per = 0;
		$current_tnl = $current_max_exp - $current_exp;
        $current_per_formatted = sprintf("%3.2f",$current_per);
        if ($this->current_user->region == "America/Phoenix") $current_per_formatted = number_format($current_per,2,".",",");
        else $current_per_formatted = number_format($current_per,2,",",".");
        if ($this->current_user->region == "America/Phoenix") $current_max_exp_formatted = number_format($current_max_exp,0,".",",");
        else $current_max_exp_formatted = number_format($current_max_exp,0,",",".");
        if ($this->current_user->region == "America/Phoenix") $current_exp_formatted = number_format($current_exp,0,".",",");
        else $current_exp_formatted = number_format($current_exp,0,",",".");
        if ($this->current_user->region == "America/Phoenix") $exp_formatted = number_format($the_current_exp,0,".",",");
        else $exp_formatted = number_format($the_current_exp,0,",",".");
		if ($this->current_user->region == "America/Phoenix") $current_tnl_formatted = number_format($current_tnl,0,".",",");
        else $current_tnl_formatted = number_format($current_tnl,0,",",".");
		
		$progressbar = "<div title='{$current_per_formatted}%' style='width: 100%; height: 5px; background: #FFF; border: 1px solid #000; margin: 1px 0px;'>" . ($current_per > 0 ? "<div style='width:{$current_per}%; height: 5px; background: #{$this->color}; float: left;'></div>" : "<div>&nbsp;</div>" ) . "</div>";
		$progressbar2 = "<div style='text-align: left; font-size: 0.9em; white-space: nowrap;'><img src='files/site_images/layout/star-16.png' style='vertical-align: middle;'/><span style='vertical-align: middle;'>{$level} | {$current_exp_formatted} / {$current_max_exp_formatted}</span></div><div title='{$current_per_formatted}%' style='width: 100%; height: 5px; background: #FFF; border: 1px solid #000; margin: 1px 0px;'>" . ($current_per > 0 ? "<div style='width:{$current_per}%; height: 5px; background: #{$this->color}; float: left;'></div>" : "<div>&nbsp;</div>" ) . "</div>";
		
		$levelbox = "<span style='line-height: 1em;'>";
        $levelbox .= "<div style='font-size: 0.9em;'><img src='files/site_images/layout/star-16.png' style='vertical-align: middle;'/><span style='vertical-align: middle;'> {$level}<br/>{$exp_formatted} ".translate("points","điểm")."</span></div>";
        $levelbox .= "<div style='font-size: 0.8em;'><span style='vertical-align: middle;'>{$current_exp_formatted} / {$current_max_exp_formatted} ({$current_per_formatted}%)</span></div>";
        $levelbox .= $progressbar;
		
		return array(
			"level" => $level,
			"display" => $levelbox,
			"progress_bar" => $progressbar,
			"progress_bar_with_info" => $progressbar2,
			
			"user_exp" => $the_current_exp,
			"user_exp_formatted" => $exp_formatted,
			"current_exp" => $current_exp,
			"current_exp_formatted" => $current_exp_formatted,
			"current_max_exp" => $current_max_exp,
			"current_max_exp_formatted" => $current_max_exp_formatted,
			"current_percentage" => $current_per,
			"current_percentage_formatted" => $current_per_formatted,
			"current_tnl" => $current_tnl,
			"current_tnl_formatted" => $current_tnl_formatted
		);
        
        /* $exp = $the_current_exp;
        $star1 = 5000;
        $star2 = $star1 * 5;
        $star3 = $star2 * 5;
        $star4 = $star3 * 5;
        $star5 = $star4 * 5;
        $fifth = (int) ($exp / $star5);
        if ($fifth >= 1) $exp -= $star5 * $fifth;
        $fourth = (int) ($exp / $star4);
        if ($fourth >= 1) $exp -= $star4 * $fourth;
        $third = (int) ($exp / $star3);
        if ($third >= 1) $exp -= $star3 * $third;
        $second = (int) ($exp / $star2);
        if ($second >= 1) $exp -= $star2 * $second;
        $first = (int) ($exp / $star1);
        if ($first >= 1) $exp -= $star1 * $first;

        $v = "<span>";
        if ( $fifth >= 1 ) $v .= "&nbsp;<span style='vertical-align: middle;'>{$fifth}</span>&nbsp;<img src='img/cayxoai-16.png' style='vertical-align: middle;'/>";
        if ( $fourth >= 1 ) $v .= "&nbsp;<span style='vertical-align: middle;'>{$fourth}</span>&nbsp;<img src='img/leaf-16.png' style='vertical-align: middle;'/>";
        if ( $third >= 1 ) $v .= "&nbsp;<span style='vertical-align: middle;'>{$third}</span>&nbsp;<img src='img/medal_gold-16.png' style='vertical-align: middle;'/>";
        if ( $second >= 1 ) $v .= "&nbsp;<span style='vertical-align: middle;'>{$second}</span>&nbsp;<img src='img/medal_silver-16.png' style='vertical-align: middle;'/>";
        if ( $first >= 1 ) $v .= "&nbsp;<span style='vertical-align: middle;'>{$first}</span>&nbsp;<img src='img/medal_bronze-16.png' style='vertical-align: middle;'/>";
        if ( $exp > 0 ){
            $per = sprintf("%2.2f",($exp / $star1) * 100);
            if ($this->region == "America/Phoenix") $per2 = number_format($per,2,".",",");
            else $per2 = number_format($per,2,",",".");
            $v .= "<div id='progressbar{$this->id}' title='{$per2}%' style='width: 100%; height: 5px; background: #FFF;border:1px solid #000;'>" . ($per > 0 ? "<div style='width:{$per}%;height: 5px; background:#{$this->color};float:left;'></div>" : "<div>&nbsp;</div>" ) . "</div>";
            $v .= "<script> $('#progressbar{$this->id}').qtip({position: { my: 'top center', at: 'bottom center' }, style: { classes: 'ui-tooltip-green ui-tooltip-shadow' }}); </script>";
        }
        $v .= "</span>";
        return $v; */
    }
}
?>