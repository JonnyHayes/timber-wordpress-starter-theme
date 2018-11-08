<?php
/**
 * Logs the user in.
 */
function user_login(){
	$response = new stdClass();
	$response->errors = [];
	$response->status = 0;

	if(!empty($_POST['data']['user_email']) && !empty($_POST['data']['user_password'])){
		$user_login = esc_attr($_POST['data']['user_email']);
		$user_password = esc_attr($_POST['data']['user_password']);
		$user_remember = isset($_POST['data']['user_remember']);

		unset($_POST['data']);

		$creds = array();
		$creds['user_login'] = $user_login;
		$creds['user_password'] = $user_password;
		$creds['remember'] = $user_remember;

		if(!is_user_logged_in()){
			$user = wp_signon($creds, false);
			wp_set_current_user($user->ID);

			if(is_wp_error($user)){
				$response->errors[] = strip_tags($user->get_error_message());
			}
		}
	} else {
		$response->errors[] = __('Both username and password are required.', 'mvnp_basic');
	}

	if(empty($response->errors)){
		$response->status = 1;
		$response->name = $user->display_name;
		$response->cleanName = $user->user_nicename;
	} else {
		$response->status = -count($response->errors);
	}
	header('Content-Type:application/json;');
	echo json_encode($response);
	wp_die();
}
add_action('wp_ajax_nopriv_user_login', 'user_login');

/**
 * Logs the user out.
 */
function user_logout(){
	$response = new stdClass();
	$response->errors = [];
	$response->status = 0;

	if(!empty($_POST['data']['user_logout']) && ($_POST['data']['user_logout'] == '1')){
		unset($_POST['data']);

		if(is_user_logged_in()){
			wp_logout();
			wp_set_current_user(0);
		}
	} else {
		$response->errors[] = __('The form is empty somehow.', 'mvnp_basic');
	}

	if(empty($response->errors)){
		$response->status = 1;
	} else {
		$response->status = -count($response->errors);
	}
	header('Content-Type:application/json;');
	echo json_encode($response);
	wp_die();
}
add_action('wp_ajax_user_logout', 'user_logout');

/**
 * Signs the user up. New user has no role and can't access anything. They do get their own page though.
 */
function user_signup(){
	$response = new stdClass();
	$response->errors = [];
	$response->status = 0;

	if(!empty($_POST['data']['user_email']) && !empty($_POST['data']['user_password']) && !empty($_POST['data']['user_verify_password'])){
		$username = sanitize_email($_POST['data']['user_email']);
		$password = esc_attr($_POST['data']['user_password']);
		$verify_password = esc_attr($_POST['data']['user_verify_password']);
		$email = sanitize_email($_POST['data']['user_email']);

		$captcha = $_POST['data']['g-recaptcha-response'];
		$captcha_secret_key = '6LeVER8UAAAAADpqS1UFMLyWF0OVHvWiarCEozZ7';
		$user_ip = $_SERVER['REMOTE_ADDR'];

		$captcha_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captcha_secret_key . '&response=' . $captcha . '&remoteip=' . $user_ip);
		$response_keys = json_decode($captcha_response, true);

		unset($_POST['data']);

		if(intval($response_keys['success']) === 1){
			if(filter_var($email, FILTER_VALIDATE_EMAIL)){
				if($verify_password === $password){
					$signup = wp_create_user($username, $password, $email);

					if(is_wp_error($signup)){
						$response->errors[] = strip_tags($signup->get_error_message());
					} else {
						$change_role = new WP_User($signup);
						$change_role->set_role('');

						user_login();
					}
				} else {
					$response->errors[] = __('Your passwords do not match.', 'mvnp_basic');
				}
			} else {
				$response->errors[] = __('Invalid email address.', 'mvnp_basic');
			}
		} else {
			$response->errors[] = __('Captcha Failed.', 'mvnp_basic');
		}

	} else {
		$response->errors[] = __('Missing information. Please try again', 'mvnp_basic');
	}

	if(empty($response->errors)){
		$response->status = 1;
		$response->name = $user->display_name;
	} else {
		$response->status = -count($response->errors);
	}
	header('Content-Type:application/json;');
	echo json_encode($response);
	wp_die();
}
add_action('wp_ajax_nopriv_user_signup', 'user_signup');
