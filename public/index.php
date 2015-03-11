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
	$graphObject = $response->getGraphObject();// hier kann man auch pageid erhalten
	var_dump($graphObject);
	displayCustomFBPageFeed($session);
	usingSocialPlugin();
	
} else {
  // show login url
  echo '<a href="' . $helper->getLoginUrl() . '">Login</a>';
}

// from callmenick.com/2013/03/14/displaying-a-custom-facebook-page-feed/
function displayCustomFBPageFeed($session){
	
	$pageid = '318251298186105'; // from a site in FB
	
	$request = new FacebookRequest($session, 'GET', '/'.$pageid);
	$response = $request->execute();	
	$generalInfo = $response->getGraphObject()->asArray();
	
	
 	$request = new FacebookRequest($session, 'GET', '/'.$pageid.'/feed');
	$response = $request->execute();
	$contentFeed = $response->getGraphObject()->asArray();
	
	
	?>
	<h1><?php echo $generalInfo['name']; ?> (<?php echo $generalInfo['id']; ?>)</h1>
	<p><img src="http://graph.facebook.com/<?php echo $generalInfo['id'];?>/picture?width=180&height=180" /></p>
	<p>About: <?php echo $generalInfo['about']; ?></p>
	<?php 



// 	echo "<div class=\"fb-feed\">";
	
	echo "<div >";
	// set counter to 0, because we only want to display 10 posts
	$i = 0;
	foreach($contentFeed['data'] as $post) {
		
		if ($post->type == 'status' || $post->type == 'link' || $post->type == 'photo') {
	
			// open up an fb-update div
// 			echo "<div class=\"fb-update\">";
			echo "<div >";
			// post the time
	
			// check if post type is a status
			if ($post->type == 'status') {
				echo "<h2>Status updated on: " . date("jS M, Y", (strtotime($post->created_time))) . "</h2>";
				echo "<p>" . $post->message . "</p>";
			}
	
			// check if post type is a link
			if ($post->type == 'link') {
				//echo "<h2>Link posted on: " . date("jS M, Y", (strtotime(strtotime($post->created_time)))) . "</h2>";
				$creationDate = strtotime($post->created_time);
				$creationDate = date('jS M, Y', $creationDate);
				echo "<hr>";
				echo "<h2>Link posted on: " . $creationDate."</h2>";
				echo "<p>" . $post->name . "</p>";
				echo "<p><a href=\"" . $post->link . "\" target=\"_blank\">" . $post->link . "</a></p>";
				echo "</hr>";
			}
	
			// check if post type is a photo
			if ($post->type == 'photo') {
				echo "<h2>Photo posted on: " . date("jS M, Y", (strtotime(strtotime($post->created_time)))) . "</h2>";
				if (empty($post->story) === false) {
					echo "<p>" . $post->story . "</p>";
				} elseif (empty($post->message) === false) {
					echo "<p>" . $post->message . "</p>";
				}
				echo "<p><a href=\"" . $post->link . "\" target=\"_blank\">View photo &rarr;</a></p>";
			}
	
			echo "</div>"; // close fb-update div
	
			$i++; // add 1 to the counter if our condition for $post['type'] is met
		}
	
		//  break out of the loop if counter has reached 10
		if ($i == 10) {
			break;
		}
	} // end the foreach statement
	
	echo "</div>";

}

function usingSocialPlugin(){
	
include '/var/www/BFAPI/public/socialPlugin.html'; 
}




