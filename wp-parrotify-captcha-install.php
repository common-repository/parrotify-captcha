<?php

function wp_parrotify_captcha_install() {
	$options = array(
		'wpparrotify_login' => 'yes',
		'wpparrotify_register' => 'yes',
		'wpparrotify_lost' => 'yes',
		'wpparrotify_comments' => 'yes',
		'wpparrotify_registered' => 'no',
	);

	foreach($options as $key => $val) {
		if (false === get_option( $key ))
			add_site_option( $key, $val );
	}
}
