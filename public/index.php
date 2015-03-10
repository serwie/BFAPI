<?php 
/**
 * FB PHP SDK https://developers.facebook.com/docs/reference/php/4.0.0
 * Dort ist auch die Doku.  
 * Wichtig auch App Domains[BFAPI.com] und Site Url[http://BFAPI.com] anzugeben
 * Vorgehen analog zu google API sowie jawbone API
 */



session_start();
set_include_path("/var/www/BFAPI/facebookPhpSdk/src" . PATH_SEPARATOR. get_include_path());


require_once 'Facebook/FacebookSession.php';
require_once 'Facebook/FacebookRedirectLoginHelper.php';
require_once 'Facebook/FacebookRequest.php';
require_once 'Facebook/FacebookResponse.php';
require_once 'Facebook/FacebookSDKException.php';
require_once 'Facebook/FacebookRequestException.php';
require_once 'Facebook/FacebookAuthorizationException.php';
require_once 'Facebook/GraphObject.php';
require_once 'Facebook/Entities/AccessToken.php';
require_once 'Facebook/HttpClients/FacebookCurlHttpClient.php';
require_once 'Facebook/HttpClients/FacebookHttpable.php';
require_once 'Facebook/GraphSessionInfo.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;
use Facebook\GraphSessionInfo;


$appid = 'xxx'; // your AppID
$secret = 'xxx'; // your secret


// unset($_SESSION['access_token']);

// init app with app id (APPID) and secret (SECRET)
FacebookSession::setDefaultApplication($appid ,$secret);
	
// login helper with redirect_uri
$helper = new FacebookRedirectLoginHelper ( 'http://BFAPI.com/');
	

// $loginUrl = $helper->getLoginUrl ();


try {
	// var_dump ( $_GET ['code'] );
	$session = $helper->getSessionFromRedirect ();
	// var_dump ( $session );
} catch ( FacebookRequestException $ex ) { // When Facebook returns an error
	echo "Error from FB <br>" . $ex;
} catch ( \Exception $ex ) { // When validation fails or other local issues
	echo "Something's wrong with the validation <br>" . $ex;
}

// see if we have a session in $_SESSION[]
if( isset($_SESSION['token']))
{
	// We have a token, is it valid?
	$session = new FacebookSession($_SESSION['token']);
	try
	{
		$session->Validate($appid ,$secret);
	}
	catch( FacebookAuthorizationException $ex)
	{
		// Session is not valid any more, get a new one.
		$session ='';
	}
}


 //see if we have a session
if ($session) { // Logged in
	
	echo "<b>-Session Info-</b><br><br>";
	// set the PHP Session 'token' to the current session token
	$_SESSION['token'] = $session->getToken();
	// SessionInfo
	$info = $session->getSessionInfo();
	var_dump($info);
	// getAppId
	echo "Appid: " . $info->getAppId() . "<br />";
	// session expire data
	$expireDate = $info->getExpiresAt()->format('Y-m-d H:i:s');
	echo 'Session expire time: ' . $expireDate . "<br />";
	// session token
	echo 'Session Token: ' . $session->getToken() . "<br />";
	
	
	echo "<br /><b>-Graph API-</b><br><br>";
	// graph api request for user data
	$request = new FacebookRequest($session, 'GET', '/me');
	$response = $request->execute();
	//get response
	$graphObject = $response->getGraphObject();
	// print data
	//echo  print_r( $graphObject, 1 );
	var_dump($graphObject);
	
	
	displayCustomFBPageFeed($session);
	
	
	
} else {
  // show login url
  echo '<a href="' . $helper->getLoginUrl() . '">Login</a>';
}

// from callmenick.com/2013/03/14/displaying-a-custom-facebook-page-feed/
function displayCustomFBPageFeed($session){
	// first get pageid
	
	
	
	$pageid = '100001456337135';
	//$request = new FacebookRequest($session, 'GET', '/{'.$pageid.'}/feed');
	$request = new FacebookRequest($session, 'GET', '/100001456337135/feed');
	$response = $request->execute();
	var_dump($response);
}



?>
