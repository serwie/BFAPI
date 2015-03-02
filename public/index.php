<?php 
/**
 * FB PHP SDK https://developers.facebook.com/docs/reference/php/4.0.0
 * Dort ist auch die Doku. 
 * Vorgehen analog zu google API sowie jawbone API
 */



session_start();
set_include_path("/var/www/BFAPI/facebookPhpSdk/src" . PATH_SEPARATOR. get_include_path());


require_once 'Facebook/FacebookSession.php';
require_once 'Facebook/FacebookRedirectLoginHelper.php';
require_once 'Facebook/FacebookRequest.php';
require_once 'Facebook/FacebookSDKException.php';
require_once 'Facebook/FacebookRequestException.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;



// unset($_SESSION['access_token']);


FacebookSession::setDefaultApplication('1593262114252926', 'cf4af33edf5e6ee29f928be0b4772a03');

$helper = new FacebookRedirectLoginHelper('http://facebookApi.com');
$loginUrl = $helper->getLoginUrl();
?> <a class='login' href='<?php echo $loginUrl; ?>'>Authenticate</a>

<?php 

try {
	$session = $helper->getSessionFromRedirect();
} catch (FacebookRequestException $ex) {// When Facebook returns an error
	echo "Error from FB";
}catch(\Exception $ex) { // When validation fails or other local issues
	echo "Something's wrong with the validation";
}

/**
 * make some requests
 */
if ($session) { // Logged in
	echo "<b>-Graph API-</b><br><br>";
	$request = new FacebookRequest($session, 'GET', '/me');
	$response = $request->execute();
	$graphObject = $response->getGraphObject();
	var_dump($graphObject);
}


?>
