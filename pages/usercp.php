<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
if (!isset($_SESSION["root_path"]) OR !isset($_SESSION["in"]) OR !isset($_SESSION["user"])) { header("Location: /index.php".(isset($queries) ? "?{$queries}" : ""),true); die(); }
require_once("{$_SESSION["root_path"]}/inc.php");
require_once("{$_SESSION["root_path"]}/scripts_each_page.php");
$user = new User($_SESSION["user"]);
$user->set_last_seen("user_cp");

if (isset($_REQUEST["inactive_user"]) AND (mysql_num_rows(mysql_query("SELECT * FROM `users` WHERE `username` = '{$_REQUEST["inactive_user"]}' AND `active` = '0'"))) > 0){
    $check = new User($_REQUEST["inactive_user"]);
    if ($check->active != 1) {
        $user = new User($_REQUEST["inactive_user"]);
        $current_user = new User($_SESSION["user"]);
        $editing_others = true;
    }
}

require_once("{$_SESSION["root_path"]}/page_top.php");
?>
<script type="text/javascript">
$(function(){
    // setting the page title
    top.document.title = "<?php echo translate("User control panel","Điều chỉnh cá nhân"); ?>";
    // qTip crap
    //$.fn.qtip.defaults.position.target = $(".mascot");
    $.fn.qtip.defaults.position.my = "center left";
    $.fn.qtip.defaults.position.at = "center right";
    $.fn.qtip.defaults.style.classes = "ui-tooltip-green ui-tooltip-shadow";
	$.fn.qtip.defaults.show.solo = true;
    $(".edit_icon").attr("title","<?php echo translate("Click here to change...","Bấm vào đây để thay đổi."); ?>");
	$("img[title]").qtip();
	
	$(".info_frame_td").prepend("<a class='frame_close' style='float: right;'><img src='/files/site_images/layout/close-32.png'/></a>");
	
	var color_changed = false;
    $('#user_color').ColorPicker({
        onSubmit: function(hsb, hex, rgb, el) {
            $(el).val(hex.toUpperCase());
            $(el).ColorPickerHide();
            $.post("/actions/change_user_info.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}&" : ""); ?>action=edit_color&session=<?php echo session_id(); ?>", {color: $("#user_color").val()}, function (data){
                alert("<?php echo translate("Your personal color has been changed.","Màu cá nhân của {$user->you} đã được thay đổi."); ?>");
                load_page("usercp.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}" : ""); ?>&selectedTab=" + $("#tabs").tabs("option", "selected"));
            });
        },
        onBeforeShow: function () {
            $(this).ColorPickerSetColor(this.value);
        },
        onChange: function (hsb, hex, rgb) {
            $('#user_color').css('backgroundColor', '#' + hex);
            $('#user_color').val(hex);
            color_changed = true;
        },
        onShow: function(){
            $("#user_color").qtip('destroy');
            $(".colorpicker_submit").qtip({
                show:{ ready: true },
                //style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                content: { text: "<?php echo translate("Click this button to save your selected color.","Xin bấm vào nút này để thay đổi qua màu đã chọn."); ?>" }
            });
        },
        onHide: function(){
            $(".colorpicker_submit").qtip('destroy');
            if (color_changed == true){
                $("#user_color").qtip({
                    position: {
                        target: this,
                        my: "center left",
                        at: "center right"
                    },
                    show:{ ready: true },
                    style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                    content: { text: "<?php echo translate("Your personal color has <strong>not</strong> been changed, make sure you click on the 'submit' button in the color dialog box.","Màu cá nhân của ".$user->you." <strong>chưa</strong> được thay đổi, anh phải bấm vào nút 'nhập' trong khung chọn màu."); ?>" }
                });
            }
        }
    })
    .bind('keyup', function(){ $(this).ColorPickerSetColor(this.value); });
	
	$(".frame_close").click(function(){ $(".qtip").remove(); $(".info_frame").fadeOut(); });
    $("#edit_pw_frame,#edit_display_name_frame,#edit_full_name_frame,#edit_email_frame").hide();
    $("#password_edit").click(function(){
		$("#edit_pw_frame").fadeIn();
	});
    $("#display_name_edit").click(function(){ $("#edit_display_name_frame").fadeIn(); });
    $("#name_edit").click(function(){ $("#edit_full_name_frame").fadeIn(); });
    $("#email_edit").click(function(){ $("#edit_email_frame").fadeIn(); });
	
	$(".dialog").dialog({ autoOpen: false });
	
	$("#tabs").tabs();
	<?php if (isset($_REQUEST["selectedTab"])) echo "$(\"#tabs\").tabs('option','selected',{$_REQUEST["selectedTab"]});"; ?>
})
</script>

<?php if (isset($editing_others)){ echo "<div style='color: #F00; text-align: center;'>".translate("You are editing {$user->name}'s profile.","{$current_user->you} đang thay đổi thông tin của {$user->name}.")."</div>"; } ?>
<div class="helpmsg" style="text-align: center; margin: 10px auto !important; display: inline-block !important;"><?php echo translate("Click on the <img src='files/site_images/layout/edit-16.png'/> icon to edit the following information.","Xin bấm vào nút <img src='files/site_images/layout/edit-16.png'/> để thay đổi những thông tin sau."); ?></div>

<div id="edit_pw_dialog" class="dialog">
	<h2><img src="files/site_images/layout/key-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Change your password","Thay đổi mật khẩu"); ?></span></h2>
    <form id="edit_pw_form" class="info_frame_form">
        <table style="margin: auto;" cellspacing="0" cellpadding="0" border="0">
            <tr><td style="text-align: left;"><?php echo translate("New password","Mật khẩu mới"); ?>: </td><td><input type="password" id="password1" name="password1" /></td></tr>
            <tr><td style="text-align: left;"><?php echo translate("Enter again","Lập lại"); ?>: </td><td><input type="password" id="password2" name="password2" /></td></tr>
            <tr><td style="text-align: left;"><?php echo translate("Old password","Mật khẩu cũ"); ?>: </td><td><input type="password" id="cpassword" name="cpassword" /></td></tr>
            <tr><td colspan="2" style="text-align: center;"><button type="submit"><?php echo translate("Change password","Thay đổi mật khẩu"); ?></button></td></tr>
        </table>
    </form>
    <script type="text/javascript">
    $(function(){
        $("#password1").qtip({ content: { text: "<?php echo translate("Enter your new password.","Xin điền vào mật khẩu mới."); ?>" } });
        $("#password2").qtip({ content: { text: "<?php echo translate("Enter your new password again.","Xin nhập vào mật khẩu mới một lần nữa."); ?>" } });
        $("#cpassword").qtip({ content: { text: "<?php echo translate("Enter your <strong>current</strong> password.","Xin nhập vào mật khẩu <strong>hiện tại</strong>."); ?>" } });
		
		var default_border_css = $('input').css('border');
		var error_border_css = "1px solid #F00";
        
        $("#edit_pw_form").submit( function(e){
            e.preventDefault();
            if( !$("#password1").val() ){
                $("#password1").qtip({
                    show:{ ready: true },
                    style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                    content: { text: "<?php echo translate("Please pick a new password.","Xin điền vào mật khẩu mới."); ?>" }
                });
                $("#password1").css("border",error_border_css);
				return false;
            } else { $("#password1").qtip('destroy'); $("#password1").css("border",default_border_css); }
            
            if( !$("#password2").val() ){
                $("#password2").qtip({
                    show:{ ready: true },
                    style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                    content: { text: "<?php echo translate("Please verify your new password.","Xin xác nhận mật khẩu mới."); ?>" }
                });
                $("#password2").css("border",error_border_css);
				return false;
            } else { $("#password2").qtip('destroy'); $("#password2").css("border",default_border_css); }
            
            if( !$("#cpassword").val() ){
                $("#cpassword").qtip({
                    show:{ ready: true },
                    style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                    content: { text: "<?php echo translate("Please enter your current password for verification.","Xin điền vào mật khẩu hiện tại để xác nhận người dùng."); ?>" }
                });
                $("#cpassword").css("border",error_border_css);
				return false;
            } else { $("#cpassword").qtip('destroy'); $("#cpassword").css("border",default_border_css); }
			$("#password1,#password2,#cpassword").qtip('destroy');
			$("#password1,#password2,#cpassword").css("border",default_border_css);
			$.post("/actions/change_user_info.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}&" : ""); ?>action=edit_password&session=<?php echo session_id(); ?>", $("#edit_pw_form").serialize(), function (data){
				if (data == "error_newpw_mismatched"){
					$("#password1").qtip({
						show:{ ready: true },
						style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
						content: { text: "<?php echo translate("New passwords do not match, please check your password.","Hai hàng mật khẩu không giống nhau, xin coi lại."); ?>" }
					});
					$("#password1,#password2").css("border",error_border_css);
					return false;
				} else {
					$("#password1,#password2").qtip('destroy');
					$("#password1,#password2").css("border",default_border_css);
				}
				if (data == "error_newpw_too_short"){
					$("#password1").qtip({
						show:{ ready: true },
						style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
						content: { text: "<?php echo translate("Your new password is too short, please make sure it's at least 5 characters long.","Mật khẩu mới quá ngắn, xin tạo mật khẩu với ít nhất 5 ký tự."); ?>" }
					});
					$("#password1,#password2").css("border",error_border_css);
					return false;
				} else { 
					$("#password1,#password2").qtip('destroy');
					$("#password1,#password2").css("border",default_border_css);
				}
				if (data == "error_old_pw_incorrect"){
					$("#cpassword").qtip({
						show:{ ready: true },
						style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
						content: { text: "<?php echo translate("Current password isn't correct, try again.","Mật khẩu cũ không chính xác, xin coi lại."); ?>" }
					});
					$("#cpassword").css("border",error_border_css);
					return false;
				} else {
					$("#cpassword").qtip('destroy');
					$("#cpassword").css("border",default_border_css);
				}
				if (data == "error_same_new_pw"){
					$("#password1").qtip({
						show:{ ready: true },
						style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
						content: { text: "<?php echo translate("Your new password is the same as your old one; no change has been made.","Mật khẩu mới của {$user->you} không khác với mật khẩu củ; mật khẩu đã không thay đổi."); ?>" }
					});
					$("#password1").css("border",error_border_css);
					return false;
				} else {
					$("#password1").qtip('destroy');
					$("#password1").css("border",default_border_css);
				}
				alert("<?php echo translate("Your password has been changed.","Mật khẩu đã được thay đổi."); ?>");
				load_page("usercp.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}" : ""); ?>&selectedTab=" + $("#tabs").tabs("option", "selected"));
			});
        });
    });
    </script>
</div>

<div id="edit_pw_frame" class="overlay info_frame"><table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0"><tr><td valign="middle"><table cellpadding="0" cellspacing="5" border="0" class="wrap"><tr><td valign="middle" class="info_frame_td">
    <h2><img src="files/site_images/layout/key-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Change your password","Thay đổi mật khẩu"); ?></span></h2>
    <form id="edit_pw_form" class="info_frame_form">
        <table style="margin: auto;" cellspacing="0" cellpadding="0" border="0">
            <tr><td style="text-align: left;"><?php echo translate("New password","Mật khẩu mới"); ?>: </td><td><input type="password" id="password1" name="password1" /></td></tr>
            <tr><td style="text-align: left;"><?php echo translate("Enter again","Lập lại"); ?>: </td><td><input type="password" id="password2" name="password2" /></td></tr>
            <tr><td style="text-align: left;"><?php echo translate("Old password","Mật khẩu cũ"); ?>: </td><td><input type="password" id="cpassword" name="cpassword" /></td></tr>
            <tr><td colspan="2" style="text-align: center;"><button type="submit"><?php echo translate("Change password","Thay đổi mật khẩu"); ?></button></td></tr>
        </table>
    </form>
    <script type="text/javascript">
    $(function(){
        $("#password1").qtip({ content: { text: "<?php echo translate("Enter your new password.","Xin điền vào mật khẩu mới."); ?>" } });
        $("#password2").qtip({ content: { text: "<?php echo translate("Enter your new password again.","Xin nhập vào mật khẩu mới một lần nữa."); ?>" } });
        $("#cpassword").qtip({ content: { text: "<?php echo translate("Enter your <strong>current</strong> password.","Xin nhập vào mật khẩu <strong>hiện tại</strong>."); ?>" } });
		
		var default_border_css = $('input').css('border');
		var error_border_css = "1px solid #F00";
        
        $("#edit_pw_form").submit( function(e){
            e.preventDefault();
            if( !$("#password1").val() ){
                $("#password1").qtip({
                    show:{ ready: true },
                    style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                    content: { text: "<?php echo translate("Please pick a new password.","Xin điền vào mật khẩu mới."); ?>" }
                });
                $("#password1").css("border",error_border_css);
				return false;
            } else { $("#password1").qtip('destroy'); $("#password1").css("border",default_border_css); }
            
            if( !$("#password2").val() ){
                $("#password2").qtip({
                    show:{ ready: true },
                    style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                    content: { text: "<?php echo translate("Please verify your new password.","Xin xác nhận mật khẩu mới."); ?>" }
                });
                $("#password2").css("border",error_border_css);
				return false;
            } else { $("#password2").qtip('destroy'); $("#password2").css("border",default_border_css); }
            
            if( !$("#cpassword").val() ){
                $("#cpassword").qtip({
                    show:{ ready: true },
                    style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                    content: { text: "<?php echo translate("Please enter your current password for verification.","Xin điền vào mật khẩu hiện tại để xác nhận người dùng."); ?>" }
                });
                $("#cpassword").css("border",error_border_css);
				return false;
            } else { $("#cpassword").qtip('destroy'); $("#cpassword").css("border",default_border_css); }
			$("#password1,#password2,#cpassword").qtip('destroy');
			$("#password1,#password2,#cpassword").css("border",default_border_css);
			$.post("/actions/change_user_info.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}&" : ""); ?>action=edit_password&session=<?php echo session_id(); ?>", $("#edit_pw_form").serialize(), function (data){
				if (data == "error_newpw_mismatched"){
					$("#password1").qtip({
						show:{ ready: true },
						style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
						content: { text: "<?php echo translate("New passwords do not match, please check your password.","Hai hàng mật khẩu không giống nhau, xin coi lại."); ?>" }
					});
					$("#password1,#password2").css("border",error_border_css);
					return false;
				} else {
					$("#password1,#password2").qtip('destroy');
					$("#password1,#password2").css("border",default_border_css);
				}
				if (data == "error_newpw_too_short"){
					$("#password1").qtip({
						show:{ ready: true },
						style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
						content: { text: "<?php echo translate("Your new password is too short, please make sure it's at least 5 characters long.","Mật khẩu mới quá ngắn, xin tạo mật khẩu với ít nhất 5 ký tự."); ?>" }
					});
					$("#password1,#password2").css("border",error_border_css);
					return false;
				} else { 
					$("#password1,#password2").qtip('destroy');
					$("#password1,#password2").css("border",default_border_css);
				}
				if (data == "error_old_pw_incorrect"){
					$("#cpassword").qtip({
						show:{ ready: true },
						style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
						content: { text: "<?php echo translate("Current password isn't correct, try again.","Mật khẩu cũ không chính xác, xin coi lại."); ?>" }
					});
					$("#cpassword").css("border",error_border_css);
					return false;
				} else {
					$("#cpassword").qtip('destroy');
					$("#cpassword").css("border",default_border_css);
				}
				if (data == "error_same_new_pw"){
					$("#password1").qtip({
						show:{ ready: true },
						style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
						content: { text: "<?php echo translate("Your new password is the same as your old one; no change has been made.","Mật khẩu mới của {$user->you} không khác với mật khẩu củ; mật khẩu đã không thay đổi."); ?>" }
					});
					$("#password1").css("border",error_border_css);
					return false;
				} else {
					$("#password1").qtip('destroy');
					$("#password1").css("border",default_border_css);
				}
				alert("<?php echo translate("Your password has been changed.","Mật khẩu đã được thay đổi."); ?>");
				load_page("usercp.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}" : ""); ?>&selectedTab=" + $("#tabs").tabs("option", "selected"));
			});
        });
    });
    </script>
</td></tr></table></td></tr></table></div>

<div id="edit_display_name_frame" class="overlay info_frame"><table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0"><tr><td valign="middle"><table cellpadding="0" cellspacing="5" border="0" class="wrap"><tr><td valign="middle" class="info_frame_td">
    <h2><img src="files/site_images/layout/user2-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Change your display name","Thay đổi tên hiển thị"); ?></span></h2>
    <?php echo translate("Please note that you are only changing your <strong>display name</strong>, your <strong>login name ({$user->username})</strong> is still the same.","Xin lưu ý rằng {$user->you} chỉ thay đổi <strong>tên hiển thị</strong> của {$user->you}, <strong>tên nhập ({$user->username})</strong> vẫn giữ nguyên."); ?>
    <form id="edit_display_name_form" class="info_frame_form">
        <div style="text-align: center;"><span style="font-weight: bold;"><?php echo translate("Display name","Tên hiển thị"); ?>: </span> <input type="text" id="display_name" name="display_name" value="<?php echo $user->display_name; ?>"/></div>
        <div style="text-align: center;"><button type="submit"><?php echo translate("Change display name","Thay đổi tên hiển thị"); ?></button></div>
    </form>
    <script type="text/javascript">
    $("#display_name").qtip({
        position: {
            target: this,
            my: "center left",
            at: "center right"
        },
        style: { classes: "ui-tooltip-green ui-tooltip-shadow" },
        content: { text: "<?php echo translate("This is your display name, which is displayed by your posts throughout the site.","Đây là tên hiển thị bên cạnh bài của ".$user->you." vòng quanh GCX."); ?>" }
    });
    $('#edit_display_name_form').submit( function(e){
        e.preventDefault();
        var pass = true;
        if( $.trim($("#display_name").val()) == "" ){
            pass = false;
            $("#display_name").qtip({
                position: {
                    target: this,
                    my: "center left",
                    at: "center right"
                },
                show:{ ready: true },
                style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                content: { text: "<?php echo translate("Please enter a display name.","Xin chọn tên hiển thị."); ?>" }
            });
            $("#display_name").css("border-color","#F00");
        }
        
        if($("#display_name").val() == "<?php echo $user->display_name; ?>"){
            pass = false;
            $("#display_name").qtip({
                position: {
                    target: this,
                    my: "center left",
                    at: "center right"
                },
                show:{ ready: true },
                style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                content: { text: "<?php echo translate("Your display is the same.","Tên hiển thị chưa thay đổi."); ?>" }
            });
            $("#display_name").css("border-color","#F00");
        }
        
        if (pass != true) { return false; }
        else {
            $("#display_name").qtip('destroy');
            $("#display_name").css("border-color","#d8e5b8");
            $.post("/actions/change_user_info.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}&" : ""); ?>action=edit_display_name&session=<?php echo session_id(); ?>", $("#edit_display_name_form").serialize(), function (data){
                if (data == "error_display_name_used"){
                    pass = false;
                    $("#display_name").qtip({
                        position: {
                            target: this,
                            my: "center left",
                            at: "center right"
                        },
                        show:{ ready: true },
                        style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                        content: { text: "<?php echo translate("Your chosen display name is being used by another member, please pick another one.","Tên hiển thị {$user->you} chọn đã có người dùng, xin chọn tên khác."); ?>" }
                    });
                    $("#display_name").css("border-color","#F00");
                    return false;
                } else {
                    $("#display_name").qtip('destroy');
                    $("#display_name").css("border-color","#d8e5b8");
                }
                alert("<?php echo translate("Your display name has been changed.","Tên hiển thị của {$user->you} đã được thay đổi."); ?>");
                load_page("usercp.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}" : ""); ?>&selectedTab=" + $("#tabs").tabs("option", "selected"));
            });
        }
    });
    </script>
</td></tr></table></td></tr></table></div>

<div id="edit_full_name_frame" class="overlay info_frame"><table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0"><tr><td valign="middle"><table cellpadding="0" cellspacing="5" border="0" class="wrap"><tr><td valign="middle" class="info_frame_td">
    <h2><img src="files/site_images/layout/id-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Change your full name","Thay đổi tên họ"); ?></span></h2>
    <form id="edit_full_name_form" class="info_frame_form">
        <table style="margin: auto;" cellspacing="0" cellpadding="0" border="0">
        <tr><td style="text-align: left;"><?php echo translate("Last name","Tên họ"); ?>: </td><td><input type="text" id="name_last" name="name_last" value="<?php echo $user->name_last; ?>"/></td></tr>
        <tr><td style="text-align: left;"><?php echo translate("Middle name","Tên đệm"); ?>: </td><td><input type="text" id="name_middle" name="name_middle" value="<?php echo $user->name_middle; ?>"/></td></tr>
        <tr><td style="text-align: left;"><?php echo translate("First name","Tên"); ?>: </td><td><input type="text" id="name_first" name="name_first" value="<?php echo $user->name_first; ?>"/></td></tr>
        <tr><td colspan="2"><button type="submit"><?php echo translate("Change your full name","Thay đổi tên họ"); ?></button></td></tr>
        </table>
    </form>
    <script type="text/javascript">
    $('#edit_full_name_form').submit( function(e){
        e.preventDefault();
        var pass = true;
        if( !$.trim($("#name_last").val()) ){
            pass = false;
            $("#name_last").qtip({
                position: {
                    target: this,
                    my: "center left",
                    at: "center right"
                },
                show:{ ready: true },
                style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                content: { text: "<?php echo translate("Please enter your last name.","Xin điền vào tên họ."); ?>" }
            });
            $("#name_last").css("border-color","#F00");
        }
        
        if( !$.trim($("#name_middle").val()) ){
            pass = false;
            $("#name_middle").qtip({
                position: {
                    target: this,
                    my: "center left",
                    at: "center right"
                },
                show:{ ready: true },
                style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                content: { text: "<?php echo translate("Please enter your middle name.","Xin điền vào tên đệm."); ?>" }
            });
            $("#name_middle").css("border-color","#F00");
        }
        
        if( !$.trim($("#name_first").val()) ){
            pass = false;
            $("#name_first").qtip({
                position: {
                    target: this,
                    my: "center left",
                    at: "center right"
                },
                show:{ ready: true },
                style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                content: { text: "<?php echo translate("Please enter your first name.","Xin điền vào tên."); ?>" }
            });
            $("#name_first").css("border-color","#F00");
        }
        
        if (pass != true) { return false; }
        else {
            $("#name_middle").qtip('destroy');
            $("#name_middle").css("border-color","#d8e5b8");
            $.post("/actions/change_user_info.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}&" : ""); ?>action=edit_name&session=<?php echo session_id(); ?>", $("#edit_full_name_form").serialize(), function (data){
                alert("<?php echo translate("Your full name has been changed.","Tên họ của {$user->you} đã được thay đổi."); ?>");
                load_page("usercp.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}" : ""); ?>&selectedTab=" + $("#tabs").tabs("option", "selected"));
            });
        }
    });
    </script>
</td></tr></table></td></tr></table></div>

<div id="edit_email_frame" class="overlay info_frame"><table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0"><tr><td valign="middle"><table cellpadding="0" cellspacing="5" border="0" class="wrap"><tr><td valign="middle" class="info_frame_td">
    <h2><img src="files/site_images/layout/email-32.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Change your email","Thay đổi địa chỉ điện thư"); ?></span></h2>
    <form id="edit_email_form" class="info_frame_form">
        <p style="text-align: center;"><input type="text" id="email" name="email" value="<?php echo $user->email; ?>" style="text-align: center;"/></p>
        <p style="text-align: center;"><button type="submit"><?php echo translate("Change email","Thay đổi địa chỉ điện thư"); ?></button></p>
    </form>
    <script type="text/javascript">
    $('#edit_email_form').submit( function(e){
        e.preventDefault();
        var pass = true;
        if( !$.trim($("#email").val()) ){
            pass = false;
            $("#email").qtip({
                position: {
                    target: this,
                    my: "center left",
                    at: "center right"
                },
                show:{ ready: true },
                style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                content: { text: "<?php echo translate("Please enter your email address.","Xin cho biết địa chỉ điện thư."); ?>" }
            });
            $("#email").css("border-color","#F00");
        }
        
        if($("#email").val() == "<?php echo $user->email; ?>"){
            pass = false;
            $("#email").qtip({
                position: {
                    target: this,
                    my: "center left",
                    at: "center right"
                },
                show:{ ready: true },
                style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                content: { text: "<?php echo translate("Email unchanged.","Địa chỉ điện thư chưa được thay đổi."); ?>" }
            });
            $("#email").css("border-color","#F00");
        }
        
        if (pass != true) { return false; }
        else {
            $("#email").qtip('destroy');
            $("#email").css("border-color","#d8e5b8");
            $.post("/actions/change_user_info.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}&" : ""); ?>action=edit_email&session=<?php echo session_id(); ?>", $("#edit_email_form").serialize(), function (data){
                if (data == "error_invalid"){
                    pass = false;
                    $("#email").qtip({
                        position: {
                            target: this,
                            my: "center left",
                            at: "center right"
                        },
                        show:{ ready: true },
                        style: { classes: "ui-tooltip-red ui-tooltip-shadow" },
                        content: { text: "<?php echo translate("Email address is invalid.","Địa chỉ điện thư không hợp lệ."); ?>" }
                    });
                    $("#email").css("border-color","#F00");
                    return false;
                } else {
                    $("#email").qtip('destroy');
                    $("#email").css("border-color","#d8e5b8");
                }
                alert("<?php echo translate("Your email has been changed.","Địa chỉ điện thư của {$user->you} đã được thay đổi."); ?>");
                load_page("usercp.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}" : ""); ?>&selectedTab=" + $("#tabs").tabs("option", "selected"));
            });
        }
    });
    </script>
</td></tr></table></td></tr></table></div>

<div id="tabs">
	<ul>
		<li><a href="#options"><?php echo translate("Options","Điều chỉnh"); ?></a></li>
		<li><a href="#info"><?php echo translate("Personal information","Thông tin cá nhân"); ?></a></li>
		<li><a href="#advanced_options"><?php echo translate("Advanced options","Tùy chọn cao cấp"); ?></a></li>
		<li><a href="#avatar"><?php echo translate("Personal avatar","Hình tượng trưng"); ?></a></li>
	</ul>
	<div id="options" style="text-align: left;">
		<div id="line_username"><img src="files/site_images/layout/user-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <span style="font-weight: bold"><?php echo translate("Username","Tên nhập"); ?>: </span> <?php echo $user->username; ?> <em>(<?php echo translate("cannot be changed","không thay đổi được"); ?>)</em></span></div>
		<div id="line_password"><img src="files/site_images/layout/key-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <span style="font-weight: bold"><?php echo translate("Password","Mật khẩu"); ?>: </span> *** </span><img class="edit_icon" id="password_edit" src="files/site_images/layout/edit-16.png" style="vertical-align: middle; cursor: pointer;"/></a><!-- - <a onClick="javascript: $('#edit_pw_dialog').dialog('open');">testing</a> --></div>
		<div id="line_display_name"><img src="files/site_images/layout/user2-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <span style="font-weight: bold"><?php echo translate("Display name","Tên hiển thị"); ?>: </span> <?php echo $user->display_name; ?> </span><img class="edit_icon" id="display_name_edit" src="files/site_images/layout/edit-16.png" style="vertical-align: middle; cursor: pointer;" /></div>
		<div id="line_color"><img src="files/site_images/layout/color-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <span style="font-weight: bold"><?php echo translate("Personal color","Màu cá nhân"); ?>: </span><input id="user_color" name="user_color" style="background-color: #<?php echo $user->color; ?>; text-align: center;" value="<?php echo $user->color; ?>" size="6" readonly="readonly"/></span></div>
		<div id="line_language"><img src="files/site_images/layout/chat-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <span style="font-weight: bold"><?php echo translate("Site language","Ngôn ngữ hệ thống"); ?>: </span><select id="site_language" name="site_language" onChange="javascript: changeLanguage(this.options[this.selectedIndex].value);"><option value="en"<?php if ($user->language == "en") echo " selected"; ?>>English</option><option value="vi"<?php if ($user->language == "vi") echo " selected"; ?>>Tiếng Việt</option></select> <img id="line_language_help" src="files/site_images/layout/help-16.png" title="<?php echo translate("Changing this option will refresh the system","Thay đổi tùy chọn này sẻ tải lại hệ thống"); ?>"/></div>
		<script type="text/javascript">
		function changeLanguage(lang){
			$.post("/actions/change_user_info.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}&" : ""); ?>action=change_language&session=<?php echo session_id(); ?>",{ language: lang },function(){ location.reload(true); });
		}
		</script>
	</div>
	<div id="info" style="text-align: left;">
		<div id="line_full_name"><img src="files/site_images/layout/id-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <span style="font-weight: bold"><?php echo translate("Full name","Tên đầy đủ"); ?>: </span> <?php echo $user->get_full_name(); ?> </span> <img class="edit_icon" id="name_edit" src="files/site_images/layout/edit-16.png" style="vertical-align: middle; cursor: pointer;" /> </div>
		<div id="line_email"><img src="files/site_images/layout/email-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <span style="font-weight: bold"><?php echo translate("Email address","Địa chỉ điện thư"); ?>: </span> <?php if (empty($user->email)) { echo translate("Unknown","Không biết"); } else { echo $user->email; } ?> </span><img class="edit_icon" id="email_edit" src="files/site_images/layout/edit-16.png" style="vertical-align: middle; cursor: pointer;" /></div>
	</div>
	<div id="advanced_options" style="text-align: left;">
		<?php
		$i = mysql_fetch_object(mysql_query("SELECT * FROM `users_prefs` WHERE `option` = 'comments_sort'"));
		$comment_sort = $i->{$user->username};
		?>
		<div id="line_comments_sort"><img src="files/site_images/layout/sort-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <span style="font-weight: bold"><?php echo translate("Comments order","Thứ tự phản hồi"); ?>: </span><select id="comments_sort" name="comments_sort" onChange="javascript: changeCommentsSort(this.options[this.selectedIndex].value);"><option value="ASC"<?php if ($comment_sort == "ASC") echo " selected"; ?>><?php echo translate("Display oldest comments first","Xếp phản hồi từ củ tới mới"); ?></option><option value="DESC"<?php if ($comment_sort == "DESC") echo " selected"; ?>><?php echo translate("Display newest comments first","Xếp phản hồi từ mới tới củ"); ?></option></select></div>
		<script type="text/javascript">
		function changeCommentsSort(sortMethod){
			$.post("/actions/change_user_info.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}&" : ""); ?>action=change_comment_sort&session=<?php echo session_id(); ?>",{ sort: sortMethod },function(){ load_page("usercp.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}" : ""); ?>&selectedTab=" + $("#tabs").tabs("option", "selected")); });
		}
		</script>
	</div>
	<div id="avatar">
		<style type="text/css">
		#avatar_upload_control p { margin: 10px auto; font-size: 0.9em; }
		#log { margin: 5px auto; padding: 0; width: 500px;}
		#log { text-align: left; }
		#log li { list-style-position: inside; margin: 2px; border: 1px solid #ccc; padding: 10px; font-size: 12px; color: #000; background: #fff; position: relative;}
		#log li .progressbar { border: 1px solid #ddd; height: 5px; background: #fff; }
		#log li .progress { background: #7cc576; width: 0%; height: 5px; }
		#log li p { margin: 0; line-height: 18px; }
		#log li.success { border: 1px solid #339933; background: #ccf9b9; }
		#log li span.cancel { position: absolute; top: 5px; right: 5px; width: 20px; height: 20px;   background: url('files/site_images/layout/close-16.png') no-repeat; cursor: pointer; }
		</style>
		<script type="text/javascript">
		$(function(){
			var max_file_size = "50 MB";
			var allowed_types = "*.jpg;*.jpeg;*.gif;*.png;*.bmp";
			$('#avatar_upload_control').swfupload({
				flash_url : "/scripts/swfupload/Flash/swfupload.swf",
				upload_url: "/actions/upload_avatar.php",
				post_params: {"fileTypeExts" : allowed_types, "session" : "<?php echo session_id(); ?>"},
				file_size_limit: max_file_size,
				file_types: allowed_types,
				file_types_description: "<?php echo translate("Images","Hình ảnh"); ?> (JPG,JPEG,GIF,PNG,BMP)",
				file_upload_limit : 1,
				file_queue_limit : 1,
				
				// Button settings
				button_image_url: "files/site_images/layout/buttons/selectAvatar_<?php echo translate("en","vi"); ?>_150x32.png",
				button_width: "150",
				button_height: "32",
				button_placeholder_id: "avatarUploadButton",
				button_cursor: SWFUpload.CURSOR.HAND,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				
				debug: false
			})
				.bind('fileQueued', function(event, file){
					var listitem='<li id="'+file.id+'" >'+
						'<?php echo translate("File","Tư liệu"); ?>: <em>'+file.name+'</em> ('+Math.round(file.size/1024)+' KB) <span class="progressvalue" ></span>'+
						'<div class="progressbar"><div class="progress"></div></div>'+
						'<p class="status" ><?php echo translate("Pending","Đang chờ"); ?></p>'+
						'<span class="cancel" >&nbsp;</span>'+
						'</li>';
					$('#log').append(listitem);
					$('li#'+file.id+' .cancel').bind('click', function(){
						var swfu = $.swfupload.getInstance('#avatar_upload_control');
						swfu.cancelUpload(file.id);
						$('li#'+file.id).slideUp('fast');
					});
					// start the upload since it's queued
					$(this).swfupload('startUpload');
				})
				.bind('fileQueueError', function(event, file, errorCode, message){
					if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
						alert("<?php echo translate("You selected too many files. Please select one photo to use as an avatar.","{$user->you} đã chọn quá nhiều tư liệu. Xin chỉ chọn một hình để dùng làm tượng trưng."); ?>");
						return;
					}

					switch (errorCode) {
						case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
							alert("<?php echo translate("The image is too big, please reduce the size of the image before uploading.","Hình {$user->you} chọn quá lớn, xin thu nhỏ hình lại trước khi tải vào hệ thống."); ?>");
							break;
						case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
							alert("<?php echo translate("You file has 0 bytes.","Tư liệu của {$user->you} có 0 bytes."); ?>");
							break;
						case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
							alert("<?php echo translate("File type not allowed, please only upload images.","Loại tư liệu không cho phép, xin chỉ tải hình ảnh thôi."); ?>");
							break;
						default:
							alert("<?php echo translate("Something's wrong with your file...","Tư liệu có vấn đề gì đó..."); ?>");
							break;
					}
				})
				.bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued){
					$('#queuestatus').text('<?php echo translate("Files Selected","Tư liệu được chọn"); ?>: '+numFilesSelected+' / <?php echo translate("Queued Files","Tư liệu trong hàng"); ?>: '+numFilesQueued);
				})
				.bind('uploadStart', function(event, file){
					$('#log li#'+file.id).find('p.status').text('<?php echo translate("Uploading","Đang tải"); ?>...');
					$('#log li#'+file.id).find('span.progressvalue').text('0%');
					$('#log li#'+file.id).find('span.cancel').hide();
				})
				.bind('uploadProgress', function(event, file, bytesLoaded){
					//Show Progress
					var percentage=Math.round((bytesLoaded/file.size)*100);
					$('#log li#'+file.id).find('div.progress').css('width', percentage+'%');
					$('#log li#'+file.id).find('span.progressvalue').text(percentage+'%');
				})
				.bind('uploadSuccess', function(event, file, serverData){
					if (serverData == "error_file_too_small"){
						alert("<?php echo translate("The selected image is too small, please select an image with at least 100 pixels in width or height.","Hình {$user->you} chọn quá nhỏ, xin chọn hình nào với chiều dài hoặc chiều cao ít nhất 100 pixels."); ?>");
						var swfu = $.swfupload.getInstance('#avatar_upload_control');
						swfu.cancelUpload(file.id);
						$('li#'+file.id).slideUp('fast');
					} else {
						var item=$('#log li#'+file.id);
						item.find('div.progress').css('width', '100%');
						item.find('span.progressvalue').text('100%');
						var pathtofile='<a href="uploads/'+file.name+'" target="_blank" >view &raquo;</a>';
						item.addClass('success').find('p.status').html('Done!!! | '+pathtofile);
					}
				})
				.bind('uploadComplete', function(event, file){
					$("#loading_frame").fadeIn(500);
					load_page("usercp.php?<?php echo (isset($_REQUEST["inactive_user"]) ? "inactive_user={$_REQUEST["inactive_user"]}" : ""); ?>&selectedTab=" + $("#tabs").tabs("option", "selected"));
				})
		});
		</script>
		<div id="avatar_upload_control">
			<div style="text-align: center;">
				<span class="button" id="avatarUploadButton"></span>
				<div id="queuestatus"></div>
				<ol id="log"></ol>
			</div>
			<div style="text-align: center;"><?php echo $user->display(); ?></div>
		</div>
	</div>
</div>

<?php require_once("{$_SESSION["root_path"]}/page_bottom.php"); ?>