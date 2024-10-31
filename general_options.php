<?php
/* Function to configure Parrotify Captcha for Wordpress */
function wp_parrotify_captcha_general_options(){
?>
	<div class="wrap">
	<h1><?php _e('PARROTIFY CAPTCHA', 'wpparrotifydomain');?></h1>
<?php
if(isset($_POST['submit'])){
?>
    <div id="message" class="updated fade"><p><strong><?php _e('Options saved.', 'wpparrotifydomain'); ?></strong></p></div>
<?php
	if(isset($_POST['pcaptcha_login']))
		update_option('wpparrotify_login', $_POST['pcaptcha_login']);
	if(isset($_POST['pcaptcha_register']))
		update_option('wpparrotify_register', $_POST['pcaptcha_register']);
	if(isset($_POST['pcaptcha_lost']))
		update_option('wpparrotify_lost', $_POST['pcaptcha_lost']);
	if(isset($_POST['pcaptcha_comments']))
		update_option('wpparrotify_comments', $_POST['pcaptcha_comments']);
	if(isset($_POST['pcaptcha_registered']))
		update_option('wpparrotify_registered', $_POST['pcaptcha_registered']);
}

	$c_login = get_option('wpparrotify_login');
	$c_login_yes = null;
	$c_login_no = null;
	if(!empty($c_login) && $c_login == 'yes') $c_login_yes = 'selected="selected"';
	else $c_login_no = 'selected="selected"';
	
	$c_register = get_option('wpparrotify_register');
	$c_register_yes = null;
	$c_register_no = null;
	if(!empty($c_register) && $c_register == 'yes') $c_register_yes = 'selected="selected"';
	else $c_register_no = 'selected="selected"';
	
	$c_lost = get_option('wpparrotify_lost');
	$c_lost_yes = null;
	$c_lost_no = null;
	if(!empty($c_lost) && $c_lost == 'yes') $c_lost_yes = 'selected="selected"';
	else $c_lost_no = 'selected="selected"';
	
	$c_comments = get_option('wpparrotify_comments');
	$c_comments_yes = null;
	$c_comments_no = null;
	if(!empty($c_register) && $c_comments == 'yes') $c_comments_yes = 'selected="selected"';
	else $c_comments_no = 'selected="selected"';
	
	$c_registered = get_option('wpparrotify_registered');
	$c_registered_yes = null;
	$c_registered_no = null;
	if(!empty($c_registered) && $c_registered == 'yes') $c_registered_yes = 'selected="selected"';
	else $c_registered_no = 'selected="selected"';
?>
	<form method="post" action="">
		<h3><?php _e('Captcha display Options', 'wpparrotifydomain');?></h3>
    	<table class="form-table">
            <tr valign="top">
                    <th scope="row" style="width:260px;"><?php _e("Enable Captcha for Login form", "wpparrotifydomain");?>: </th>
                    <td>
                            <select name="pcaptcha_login" style="width:75px;margin:0;">
                                    <option value="yes" <?php echo $c_login_yes;?>><?php _e('Yes', 'wpparrotifydomain');?></option>
                                    <option value="no" <?php echo $c_login_no;?>><?php _e('No', 'wpparrotifydomain');?></option>
                            </select>			
                    </td>
            </tr>
            <tr valign="top">
                    <th scope="row"><?php _e('Enable Captcha for Register form', 'wpparrotifydomain');?>: </th>
                    <td>
                            <select name="pcaptcha_register" style="width:75px;margin:0;">
                                    <option value="yes" <?php echo $c_register_yes;?>><?php _e('Yes', 'wpparrotifydomain');?></option>
                                    <option value="no" <?php echo $c_register_no;?>><?php _e('No', 'wpparrotifydomain');?></option>
                            </select>			
                    </td>
            </tr>
            <tr valign="top">
                    <th scope="row"><?php _e('Enable Captcha for Lost Password form', 'wpparrotifydomain');?>: </th>
                    <td>
                            <select name="pcaptcha_lost" style="width:75px;margin:0;">
                                    <option value="yes" <?php echo $c_lost_yes;?>><?php _e('Yes', 'wpparrotifydomain');?></option>
                                    <option value="no" <?php echo $c_lost_no;?>><?php _e('No', 'wpparrotifydomain');?></option>
                            </select>			
                    </td>
            </tr>
            <tr valign="top">
                    <th scope="row"><?php _e('Enable Captcha for Comments form', 'wpparrotifydomain');?>: </th>
                    <td>
                            <select name="pcaptcha_comments" style="width:75px;margin:0;">
                                    <option value="yes" <?php echo $c_comments_yes;?>><?php _e('Yes', 'wpparrotifydomain');?></option>
                                    <option value="no" <?php echo $c_comments_no;?>><?php _e('No', 'wpparrotifydomain');?></option>
                            </select>			
                    </td>
            </tr>
            <tr valign="top">
                    <th scope="row"><?php _e('Hide Captcha for logged in users', 'wpparrotifydomain');?>: </th>
                    <td>
                            <select name="pcaptcha_registered" style="width:75px;margin:0;">
                                    <option value="yes" <?php echo $c_registered_yes;?>><?php _e('Yes', 'wpparrotifydomain');?></option>
                                    <option value="no" <?php echo $c_registered_no;?>><?php _e('No', 'wpparrotifydomain');?></option>
                            </select>			
                    </td>
            </tr>
            <tr height="60">
                <td><?php submit_button();?></td>
                <td></td>
            </tr>
	</table>
	
	</form>
	</div>
<?php
}
?>