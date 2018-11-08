<?php
/**
 * On the initial setup run, change this to a string like 'Pacific/Honolulu'
 */

/**
 * When running the google calendar for the first time from the command line, change the values of CREDENTIALS_PATH and CLIENT_SECRET_PATH to the abs path of the files.
 * additionally, uncomment the date_default_timezone_set function call below and the $authorize_account assignment at the bottom.
 * once the API confirmation is complete, change the paths back to wp relative paths and recomment the two lines. you may need to reassign the wp timezone in the admin as well
 */
//date_default_timezone_set(get_option('timezone_string'));
require_once get_template_directory() . '/php/vendor/autoload.php';
define('APPLICATION_NAME', 'MVNP Events Manager');
define('CREDENTIALS_PATH', get_template_directory() . '/php/vendor/calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', get_template_directory() . '/php/vendor/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
	Google_Service_Calendar::CALENDAR)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient(){
	$client = new Google_Client();
	$client->setApplicationName(APPLICATION_NAME);
	$client->setScopes(SCOPES);
	$client->setAuthConfig(CLIENT_SECRET_PATH);
	$client->setAccessType('offline');

	// Load previously authorized credentials from a file.
	$credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
	if(file_exists($credentialsPath)){
		$accessToken = json_decode(file_get_contents($credentialsPath), true);
	} else {
		// Request authorization from the user.
		$authUrl = $client->createAuthUrl();
		printf("Open the following link in your browser:\n%s\n", $authUrl);
		print 'Enter verification code: ';
		$authCode = trim(fgets(STDIN));

		// Exchange authorization code for an access token.
		$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

		// Store the credentials to disk.
		if(!file_exists(dirname($credentialsPath))){
			mkdir(dirname($credentialsPath), 0700, true);
		}

		file_put_contents($credentialsPath, json_encode($accessToken));
		printf("Credentials saved to %s\n", $credentialsPath);
	}
	$client->setAccessToken($accessToken);

	// Refresh the token if it's expired.
	if($client->isAccessTokenExpired()){
		$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
		file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
	}

	return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path){
	$homeDirectory = getenv('HOME');
	if(empty($homeDirectory)){
		$homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
	}
	return str_replace('~', realpath($homeDirectory), $path);
}

//Uncomment this and run this file from the command line to authorize and new account
//$authorize_account = getClient();
