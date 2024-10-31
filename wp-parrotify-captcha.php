<?php
/*
Plugin Name: Parrotify Captcha
Plugin URI: http://parrotify.com
Description: Adds parrotify captcha to user front-end WordPress forms. <a href="http://parrotify.com/#register">Add your site in Parrotify, to start making!</a>
Version: 1.0.0
Author: Parrotify Team 
Author URI: http://www.parrotify.com
License: GPL2

Text Domain: wpparrotifydomain
Domain Path: /languages
*/

define( 'WP_PARROTIFY_CAPTCHA_DIR_URL', plugin_dir_url(__FILE__) );
define( 'WP_PARROTIFY_CAPTCHA_DIR', dirname(__FILE__) );
define( 'WP_PARROTIFY_VALIDATE_URL', 'http://api.parrotify.com/validate' );

$plugin_header_translate = __( 'Adds parrotify captcha to user front-end WordPress forms. <a href="http://parrotify.com/#register">Add your site in Parrotify, to start making!</a>', 'wpparrotifydomain' );

require 'general_options.php';

/* Hook to initalize the admin menu */
add_action('admin_menu', 'wp_parrotify_captcha_admin_menu');
/* Hook to initialize sessions */
add_action('init', 'wp_parrotify_captcha_init_sessions');

/* Hook to store the plugin status */
register_activation_hook(__FILE__, 'wp_parrotify_captcha_enabled');
register_deactivation_hook(__FILE__, 'wp_parrotify_captcha_disabled');

function wp_parrotify_captcha_enabled(){
	update_option('wpparrotifycaptcha_status', 'enabled');

	include_once plugin_dir_path( __FILE__ ) . 'wp-parrotify-captcha-install.php';
	wp_parrotify_captcha_install();
}
function wp_parrotify_captcha_disabled(){
	update_option('wpparrotifycaptcha_status', 'disabled');
}

/* To add the menus in the admin section */
function wp_parrotify_captcha_admin_menu(){
    add_menu_page(
		'Parrotify Captcha',
		'Parrotify Captcha',
		'manage_options',
		'wp_parrotify_captcha_slug',
		'wp_parrotify_captcha_general_options'
	);
}

function wp_parrotify_captcha_init_sessions(){
	if(!session_id()){
		session_start();
	}
	load_plugin_textdomain('wpparrotifydomain', false, dirname( plugin_basename(__FILE__)).'/languages/');
}

/* Captcha for login authentication starts here */ 

$login_captcha = get_option('wpparrotify_login');
if($login_captcha == 'yes'){
	add_action('login_form', 'include_wp_parrotify_login');
	add_filter( 'login_errors', 'include_pcaptcha_login_errors' );
	add_filter( 'login_redirect', 'include_pcaptcha_login_redirect', 10, 3 );	

	wp_register_style('wp_parrotify_login_css', WP_PARROTIFY_CAPTCHA_DIR_URL . 'css/login.css');
	wp_enqueue_style( 'wp_parrotify_login_css');
}

/* Function to include captcha for login form */
function include_wp_parrotify_login(){
	/* Will retrieve the get varibale and prints a message from url if the captcha is wrong */
	if (isset( $_GET['captcha'] ) and $_GET['captcha'] == 'confirm_error') {
		echo '<label style="color:#FF0000;" id="capt_err" for="captcha_code_error">'.$_SESSION['captcha_error'].'</label><div style="clear:both;"></div>';;
		unset(  $_SESSION['captcha_error'] );
	}

	echo '
		<p class="login-form-captcha">
			<script src="http://api.parrotify.com/start.js"></script>
			<div style="clear:both;"></div>
	';

	echo '
		</p>
	';

	return true;
}

/* Hook to find out the errors while logging in */
function include_pcaptcha_login_errors($errors){
	if( isset( $_REQUEST['action'] ) && 'register' == $_REQUEST['action'] )
		return($errors);

	if (!isset( $_SESSION['pcaptcha_result'] ) or !$_SESSION['pcaptcha_result'] )
		return $errors.__('<strong>ERROR</strong>: Captcha confirmation error!', 'wpparrotifydomain');

	return $errors;
}

function check_parrotify_code() {
	if (empty( $_REQUEST['captcha_name'] )
		or empty( $_COOKIE['_cpathca'] ))
	{
		return false;
	}

    $data = array(
        'captcha[value]' => $_REQUEST['captcha_name'],
        'captcha[key]'   => $_COOKIE['_cpathca'],
    );

	$result = (
		extension_loaded('curl')
		?
		check_parrotify_curl( $data )
		:
		check_parrotify_fopen( $data )
	);

	$_SESSION['pcaptcha_result'] = $result;

	return $result;
}

function check_parrotify_curl( $data ) {
	$c = curl_init( WP_PARROTIFY_VALIDATE_URL );
	curl_setopt( $c, CURLOPT_POST, true );
	curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $c, CURLOPT_POSTFIELDS, http_build_query( $data ) );
    $result = curl_exec($c);
    curl_close($c);

	return ( 1 == $result );
}

function check_parrotify_fopen( $data ) {
	$options = array(
		'http'=> array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded',
			'content' => http_build_query( $data ),
		),
	);
	$context = stream_context_create( $options );
	$fp = fopen( WP_PARROTIFY_VALIDATE_URL, 'rb', false, $context );
	$result = stream_get_contents( $fp );
	fclose( $fp );

	return ( 1 == $result );
}

/* Hook to redirect after captcha confirmation */
function include_pcaptcha_login_redirect($url) {
	/* Captcha mismatch */
	if (!check_parrotify_code()){
		$_SESSION['captcha_error'] = __('Incorrect captcha confirmation!', 'wpparrotifydomain');
		wp_clear_auth_cookie();
		return $_SERVER["REQUEST_URI"]."/?captcha='confirm_error'";
	}
	/* Captcha match: take to the admin panel */
	else{
		return home_url('/wp-admin/');	
	}
}

/* <!-- Captcha for login authentication ends here --> */

/* Captcha for Comments ends here */
$comment_captcha = get_option('wpparrotify_comments');
if($comment_captcha == 'yes'){
	global $wp_version;
	if( version_compare($wp_version,'3','>=') ) { // wp 3.0 +
		add_action( 'comment_form_after_fields', 'include_wp_parrotify_comment_form_wp3', 1 );
		add_action( 'comment_form_logged_in_after', 'include_wp_parrotify_comment_form_wp3', 1 );
	}	
	// for WP before WP 3.0
	add_action( 'comment_form', 'include_pcaptcha_comment_form' );	
	add_filter( 'preprocess_comment', 'include_captcha_comment_post' );
}

/* Function to include captcha for comments form */
function include_pcaptcha_comment_form(){
	$c_registered = get_option('wpparrotify_registered');
	if ( is_user_logged_in() && $c_registered == 'yes')
		return true;

	echo '
		<p class="comment-form-captcha">
			<script src="http://api.parrotify.com/start.js"></script>
			<div style="clear:both;"></div>
		</p>
	';
	return true;
}

/* Function to include captcha for comments form > wp3 */
function include_wp_parrotify_comment_form_wp3(){
	$c_registered = get_option('wpparrotify_registered');
	if ( is_user_logged_in() && $c_registered == 'yes')
		return true;

	echo '
		<p class="comment-form-captcha">
			<script src="http://api.parrotify.com/start.js"></script>
			<div style="clear:both;"></div>
		</p>
	';

	remove_action( 'comment_form', 'include_pcaptcha_comment_form' );
	
	return true;
}

// this function checks captcha posted with the comment
function include_captcha_comment_post($comment) {	
	$c_registered = get_option('wpparrotify_registered');
	if (is_user_logged_in() && $c_registered == 'yes')
		return $comment;

	// skip captcha for comment replies from the admin menu
	if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'replyto-comment' &&
	( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ) ) ) {
		// skip capthca
		return $comment;
	}

	// Skip captcha for trackback or pingback
	if ( $comment['comment_type'] != '' && $comment['comment_type'] != 'comment' ) {
		 // skip captcha
		 return $comment;
	}

	if (check_parrotify_code())
		return($comment);
	else
		wp_die( __('Error: Incorrect CAPTCHA. Press your browser\'s back button and try again.', 'wpparrotifydomain'));
} 

/* <!-- Captcha for Comments authentication ends here --> */

// Add captcha in the register form
$register_captcha = get_option('wpparrotify_register');
if($register_captcha == 'yes'){
	add_action('register_form', 'include_wp_parrotify_register');
	add_action( 'register_post', 'include_pcaptcha_register_post', 10, 3 );
	add_action( 'signup_extra_fields', 'include_wp_parrotify_register' );
	add_filter( 'wpmu_validate_user_signup', 'include_pcaptcha_register_validate' );

	wp_register_style('wp_parrotify_login_css', WP_PARROTIFY_CAPTCHA_DIR_URL . 'css/login.css');
	wp_enqueue_style( 'wp_parrotify_login_css');
}

/* Function to include captcha for register form */
function include_wp_parrotify_register($default){
	echo '
		<p class="register-form-captcha">	
			<script src="http://api.parrotify.com/start.js"></script>
			<div style="clear:both;"></div>
		</p>
	';

	return true;
}

/* This function checks captcha posted with registration */
function include_pcaptcha_register_post($login,$email,$errors) {
	if (!check_parrotify_code())
		$errors->add('captcha_wrong', '<strong>'.__('ERROR', 'wpparrotifydomain').'</strong>: '.__('That CAPTCHA was incorrect.', 'wpparrotifydomain'));

	return($errors);
} 
/* End of the function include_pcaptcha_register_post */

function include_pcaptcha_register_validate($results) {
	if (!check_parrotify_code())
		$results['errors']->add('captcha_wrong', '<strong>'.__('ERROR', 'wpparrotifydomain').'</strong>: '.__('That CAPTCHA was incorrect.', 'wpparrotifydomain'));

	return($results);
}
/* End of the function include_pcaptcha_register_validate */

$lost_captcha = get_option('wpparrotify_lost');
// Add captcha into lost password form
if ($lost_captcha == 'yes'){
	add_action( 'lostpassword_form', 'include_wp_parrotify_lostpassword' );
	add_action( 'lostpassword_post', 'include_wp_parrotify_lostpassword_post', 10, 3 );

	wp_register_style('wp_parrotify_login_css', WP_PARROTIFY_CAPTCHA_DIR_URL . 'css/login.css');
	wp_enqueue_style( 'wp_parrotify_login_css');
}

/* Function to include captcha for lost password form */
function include_wp_parrotify_lostpassword($default){
	echo '
		<p class="lost-form-captcha">
			<script src="http://api.parrotify.com/start.js"></script>
			<div style="clear:both;"></div>
		</p>
	';
}

function include_wp_parrotify_lostpassword_post() {
	if (isset( $_REQUEST['user_login'] ) and '' == $_REQUEST['user_login'])
		return;

	// Check entered captcha
	if (check_parrotify_code())
		return;
	else
		wp_die( __( 'Error: Incorrect CAPTCHA. Press your browser\'s back button and try again.', 'wpparrotifydomain' ) );
}
