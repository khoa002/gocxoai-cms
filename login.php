<?php
session_start();
if (isset($_SERVER["QUERY_STRING"])) $queries = $_SERVER["QUERY_STRING"];
require_once("{$_SERVER["DOCUMENT_ROOT"]}/inc.php");
if (isset($_COOKIE["user"])) { $_SESSION["user"] = $_COOKIE["user"]; }
if (isset($_SESSION["user"])) { header("Location: index.php".(isset($queries) ? "?{$queries}" : "")); }

$language_array = array("en","vi");
if (isset($_GET["language"]) AND in_array($_GET["language"],$language_array)){
    $_SESSION["language"] = $_GET["language"];
} elseif (!isset($_SESSION["language"])) { $_SESSION["language"] = "vi"; }

$max_width = (isMobile() ? "98%" : "500px"); // the maximum width of the content (in px)
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="<?php echo $_SESSION["language"]; ?>">
    <head>
        <title><?php echo translate("Please log in!","Xin nhập vào hệ thống!"); ?> | Gốc Xoài</title>
        <?php require_once("hscripts.php"); ?>
        <script type="text/javascript">
        $(function(){
			var current_input_border_color = $("input").css("border-color");
            // qTip crap
            $.fn.qtip.defaults.position.target = $(".mascot");
            $.fn.qtip.defaults.position.my = "center left";
            $.fn.qtip.defaults.position.at = "center right";
            $.fn.qtip.defaults.style.classes = "ui-tooltip-green ui-tooltip-shadow";
            // end qTip crap

			// loginForm submit
            $("#loginForm").submit(function(e){
                e.preventDefault();
                var pass = true;
				var msg = "";
				$("input").css("border-color",current_input_border_color);
                if( !$("#username").val() ){
                    pass = false;
                    $("#username").css("border-color","#F00");
					msg += "- <?php echo translate("Please fill out your username.","Xin điền vào tên sử dụng."); ?>\n";
                }
                
                if( !$("#password").val() ){
                    pass = false;
                    $("#password").css("border-color","#F00");
					msg += "- <?php echo translate("Please fill out your password.","Xin điền vào mật khẩu."); ?>\n";
                }
                
                if( !$("#captcha_code").val() ){
                    pass = false;
                    $("#captcha_code").css("border-color","#F00");
					msg += "- <?php echo translate("Please enter the verification code.","Xin điền vào mã xác nhận."); ?>\n";
                }
                
                if (!pass) {
					alert(msg);
					return false;
				} else {
                    $("#username,#password,#captcha_code").css("border-color",current_input_border_color);
                    $.post("actions/login.php?session=<?php echo session_id(); ?>", $("#loginForm").serialize(), function (response){
						if (response == "error_invalid_captcha"){
							$("#captcha_code").css("border-color","#F00");
							$("#captcha_code").val("");
							$("#new_captcha").click();
							alert("- <?php echo translate("Verification code is not correct, please try again!","Mã xác nhận không chính xác, xin nhập lại."); ?>");
                            return false;
                        }
                        if (response == "error_username_not_found"){
							$("#username").css("border-color","#F00");
							$("#captcha_code").val("");
							$("#new_captcha").click();
							alert("- <?php echo translate("Your username cannot be found in the system, please make sure it is correct; or contact the site administrator.","Tên nhập của bạn không tìm được trong hệ thống, xin coi lại; hoặc xin liên hệ với quản trị viên."); ?>");
                            return false;
                        }
                        
                        if (response == "error_password_not_correct"){
							$("#password").css("border-color","#F00");
							$("#password").val("");
							$("#new_captcha").click();
							alert("- <?php echo translate("Password not correct, please try again.","Mật khẩu không đúng, xin nhập lại."); ?>");
                            return false;
                        }
                        window.location.replace("index.php<?php echo (isset($queries) ? "?{$queries}" : ""); ?>");
                    });
                }
            });
        });
        </script>
		<?php if (isMobile()): ?>
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; minimum-scale=1.0; user-scalable=0;" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<?php endif; ?>
    </head>
    <body id="body">
		<div id="page_wrap" style="width: <?php echo $max_width; ?>; margin: 0 auto;">
			<div style="text-align: center;"><img src="files/site_images/layout/logo.png"/></div>
			<div style="font-size: 0.8em; margin: 10px 0;"><span class="wrap"><?php echo translate("<a href=\"".go("language=vi")."\"><img src=\"files/site_images/layout/flagvn-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\"> Xem trang này bằng tiếng Việt</span></a>","<a href=\"".go("language=en")."\"><img src=\"files/site_images/layout/flagus-16.png\" style=\"vertical-align: middle;\"/><span style=\"vertical-align: middle;\"> View this page in English</span></a>"); ?></span></div>
			<form id="loginForm" class="wrap" style="margin: 10px 0;">
				<table border="0" cellspacing="5" cellpadding="0" align="center">
				<tr class="line_uid">
					<td align="left" valign="top"><label class="label" for="username" style="font-weight: bold; white-space: nowrap;"><img src="files/site_images/layout/user-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Username","Tên nhập"); ?>: </span></label></td>
					<td align="left" valign="top"><input type="text" id="username" name="username" size="20"/></td>
					<td align="left" valign="top" valign="top"><img src="files/site_images/layout/info-16.png" style="vertical-align: middle;"/><span style="font-size: 0.8em; font-style: italic; vertical-align: middle;"> <?php echo translate("This is your log in username provided to you. If you do not have one, please contact the site administrator.","Đây là tên đăng nhập đã được cung cấp cho bạn. Nếu bạn không có tên này, xin vui lòng liện hệ quản trị viên."); ?></span></td>
				</tr><tr class="line_pwd">
					<td align="left" valign="top"><label class="label" for="password" style="font-weight: bold; white-space: nowrap;"><img src="files/site_images/layout/key-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Password","Mật khẩu"); ?>: </span></label></td>
					<td align="left" valign="top"><input type="password" id="password" name="password" size="20" /></td>
					<td align="left" valign="top" valign="top"><img src="files/site_images/layout/info-16.png" style="vertical-align: middle;"/><span style="font-size: 0.8em; font-style: italic; vertical-align: middle;"> <?php echo translate("This is your password, if you do not remember your password, please contact the site administrator.","Đây là mật khẩu của bạn, nếu bạn không nhớ mật khẩu, xin vui lòng liên hệ quản trị viên."); ?></span></td>
				</tr><tr class="line_vfy">
					<td align="left" valign="top" valign="top"><label class="label" for="captcha_code" style="font-weight: bold; white-space: nowrap;"><img src="files/site_images/layout/check-16.png" style="vertical-align: middle;"/><span style="vertical-align: middle;"> <?php echo translate("Verification code","Mã xác nhận"); ?>: </span></label></td>
					<td align="left" valign="top"><img id="captcha" src="scripts/securimage/securimage_show.php" alt="CAPTCHA Image" class="captcha_description" style="margin: 10px 0;" /><div style="text-align: center;"><a id="new_captcha" style="font-size: 0.8em;" onclick="document.getElementById('captcha').src = 'scripts/securimage/securimage_show.php?' + Math.random(); $('#captcha_code').val(''); return false;"><?php echo translate("Give me a different code","Cho tôi mã khác"); ?></a></div><input type="text" name="captcha_code" id="captcha_code" size="20" maxlength="6" /></td>
					<td align="left" valign="top" valign="top"><img src="files/site_images/layout/info-16.png" style="vertical-align: middle;"/><span style="font-size: 0.8em; font-style: italic; vertical-align: middle;"> <?php echo translate("For increased security, please enter the text shown in this image to the box below. This allows the system to prevent potential attacks by automated robots.","Để củng cố an toàn cho hệ thống, xin vui lòng điền những ký tự được hiện ra trong hình này vào ô trống bên dưới. Bổ sung này được dùng để tránh những robot tự động tìm tài khoản của hệ thống chúng ta."); ?></span></td>
				</tr>
				</table>
				<div class="line_save" style="text-align: center;">
					<input type="checkbox" name="save" value="save" id="save" />&nbsp;<label for="save"><strong><?php echo translate("Remember my session","Giữ phiên sử dụng này"); ?></strong>: <img src="files/site_images/layout/info-16.png" style="vertical-align: middle;"/><span style="font-size: 0.8em; font-style: italic; vertical-align: middle;"> <?php echo translate("If this option is checked, your session will be saved on this machine for 3 months. Please make sure that you do NOT check this on a public computer.","Nếu tùy chọn này được chọn, phiên sử dụng của bạn sẽ được lưu lại trong máy này cho tới 3 tháng. Xin vui lòng đừng chọn tùy chọn này nếu bạn đang dùng máy công cộng."); ?></span></label>
				</div>
				<button type="submit"><?php echo translate("Log me in!","Nhập vào hệ thống!"); ?></button>
			</form>
		</div>
		<div id="loadframe" style="display: none;"></div>
    </body>
</html>