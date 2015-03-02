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


FacebookSession::setDefaultApplication('xxx', 'xxx');

$helper = new FacebookRedirectLoginHelper('http://BFAPI.com');
$loginUrl = $helper->getLoginUrl();
?> <a class='login' href='<?php echo $loginUrl; ?>'>Authenticate</a>

<?php 

try {
var_dump($_GET ['code']);
	$session = $helper->getSessionFromRedirect();
	var_dump($session);
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
