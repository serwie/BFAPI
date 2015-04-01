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
require_once 'Facebook/GraphUser.php';

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
use Facebook\GraphUser;
// unset($_SESSION['token']);

$appid = '1593262114252926'; // your AppID
$secret = 'cf4af33edf5e6ee29f928be0b4772a03'; // your secret


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
	
	echo '<a href="' . $helper->getLogoutUrl ($session, 'http://BFAPI.com/NotLoggedIn.php?'. $helper->getLoginUrl () ) . '">Logout</a>';//Logout if you want
	//echo "<br>".$helper->getLoginUrl (); 
	echo "<br><br><br><b>-Session Info-</b><br>";
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
		// get response
	$graphObject = $response->getGraphObject (); // hier kann man auch pageid erhalten
	var_dump ( $graphObject );
	retrieveUserProfile($session);
	getPostsFromProfile ( $session );
	usingSocialPlugin ();
	//displayCustomFBPageFeed ( $session ); //use to show more post types
} else {
	// show login url
	echo '<a href="' . $helper->getLoginUrl () . '">Login</a>';
}

function retrieveUserProfile($session) {
	echo "<hr NOSHADE SIZE=10 COLOR=#00CC00>";
	echo "<h2> Retrieve user profile</h2>";
	try {
		$user_profile = (new FacebookRequest($session, 'GET', '/me' ))->execute()->getGraphObject(GraphUser::className());
		echo "Name: " . $user_profile->getName()."<br>";
		echo "User Id: ". $user_profile->getId()."<br>";
		echo "Zum Profil: ".'<a href="'.$user_profile->getLink().'">'.$user_profile->getLink().'</a>';
	} catch(FacebookRequestException $e) {
		echo "Exception occured, code: " . $e->getCode();
		echo " with message: " . $e->getMessage();
	}
	
}

/**
 * Use Embedded Posts https://developers.facebook.com/docs/plugins/embedded-posts
 *
 */
function usingSocialPlugin(){

	echo "<hr NOSHADE SIZE=10 COLOR=#00CC00>";
	echo "<h2> Use the Social PlugIn </h2>";
	echo "<h3> 1. Getting code from a post </h3>";
	//echo $this->inlineScript()->prependFile($this->basePath('js/fbSocialPlugin.html'));
	include '/var/www/BFAPI/public/html/gettingCodeFromPost.html';

}

//http://www.finalwebsites.com/facebook-api-php-tutorial/
function getPostsFromProfile($session){
	echo "<hr NOSHADE SIZE=10 COLOR=#00CC00>";
	echo "<h2> Get posts from a profile </h2>";
	try {

		$user_feed = (new FacebookRequest($session, 'GET', '/173352899844/feed' ))->execute()->getGraphObject();
		$user_feed=$user_feed->asArray();
		echo "<div >";
		// set counter to 0, because we only want to display 10 posts
		$i = 0;

		foreach($user_feed['data'] as $post) {
			if ($post->type == 'status' || $post->type == 'link' || $post->type == 'photo') {
				if ($post->type == 'photo') {
					//var_dump($post);
					echo "<h3>Photo posted on: " . date("jS M, Y", (strtotime($post->created_time))) . "</h3>";

					if (empty($post->story) === false) {
						echo "<p><b> Story:</b> <br>" .htmlentities($post->story, ENT_QUOTES). "</p>";
					}if (empty($post->message) === false) {
						echo "<p><b>Message: </b><br>" . htmlentities($post->message, ENT_QUOTES) . "</p>";
					}
					echo "<p><a href=\"" . $post->link . "\" target=\"_blank\">View photo &rarr;</a></p>";

					?><p>
					<img src="<?php echo $post->picture; ?>" />
					</p>
					<?php
					getPictureComments($post->object_id, $session);
				}
			} 
			$i++;
			if ($i == 10){
				break;
			}
		}
		echo"</div>";
		
	} catch(FacebookRequestException $e) {
		echo "Exception occured, code: " . $e->getCode();
		echo " with message: " . $e->getMessage();
	}
}

function getPictureComments($objectId, $session) {
	$photo_comments = (new FacebookRequest ( $session, 'GET', '/' . $objectId . '/comments' ))->execute ()->getGraphObject ();
	$photo_comments = $photo_comments->asArray ();
	echo "<p>Comments : <br></p>";
	foreach ( $photo_comments ['data'] as $comment ) {
			echo "<ul>";
			echo "<li>" . htmlentities($comment->message, ENT_QUOTES) . "</li>";
			echo "</ul>";
	}

}

/**
 * from callmenick.com/2013/03/14/displaying-a-custom-facebook-page-feed/
 * used to show several posttypes
 */ 
function displayCustomFBPageFeed($session){
	
	echo "<hr NOSHADE SIZE=10 COLOR=#00CC00>";
	echo "<h2> Display custom facebook page feed</h2>";
	$pageid = '318251298186105'; // from a site in FB
	
	$request = new FacebookRequest ( $session, 'GET', '/' . $pageid );
	$response = $request->execute ();
	$generalInfo = $response->getGraphObject ()->asArray ();
	
	$request = new FacebookRequest ( $session, 'GET', '/' . $pageid . '/feed' );
	$response = $request->execute ();
	$contentFeed = $response->getGraphObject()->asArray();
	
	
	?>
<h1><?php echo $generalInfo['name']; ?> (<?php echo $generalInfo['id']; ?>)</h1>
<p>
	<img
		src="http://graph.facebook.com/<?php echo $generalInfo['id'];?>/picture?width=180&height=180" />
</p>
<p>About: <?php echo $generalInfo['about']; ?></p>
<?php 
	
	echo "<div >";
	// set counter to 0, because we only want to display 10 posts
	$i = 0;
	foreach($contentFeed['data'] as $post) {
		
		if ($post->type == 'status' || $post->type == 'link' || $post->type == 'photo') {
	
			// open up an fb-update div
			echo "<div >";
	
			// check if post type is a status
			if ($post->type == 'status') {
				echo "<h3>Status updated on: " . date("jS M, Y", (strtotime($post->created_time))) . "</h3>";
				echo "<p>" . $post->message . "</p>";
			}
	
			// check if post type is a link
			if ($post->type == 'link') {	
				$creationDate = strtotime($post->created_time);
				$creationDate = date('jS M, Y', $creationDate);
				echo "<hr>";
				echo "<h3>Link posted on: " . $creationDate."</h3>";
				echo "<p>" . $post->name . "</p>";
				echo "<p><a href=\"" . $post->link . "\" target=\"_blank\">" . $post->link . "</a></p>";
				echo "</hr>";
			}
	
			// check if post type is a photo
			if ($post->type == 'photo') {
				echo "<h3>Photo posted on: " . date("jS M, Y", (strtotime($post->created_time))) . "</h3>";
				if (empty($post->story) === false) {
					echo "<p>" . $post->story . "</p>";
				} elseif (empty($post->message) === false) {
					echo "<p>" . $post->message . "</p>";
				}
				echo "<p><a href=\"" . $post->link . "\" target=\"_blank\">View photo &rarr;</a></p>";
			}
	
			echo "</div>"; 
	
			$i++; // add 1 to the counter if our condition for $post['type'] is met
		}
	
		//  break out of the loop if counter has reached 10
		if ($i == 10) {
			break;
		}
	} // end the foreach statement
	
	echo "</div>";

}








