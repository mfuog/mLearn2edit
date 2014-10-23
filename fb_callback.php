<?php
require_once __DIR__ . '/vendor/facebook/php-sdk-v4/autoload.php';
require_once __DIR__ . '/config.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;

$session = session_start();

# commonly used URLs
$baseURL = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$homeURL = $baseURL . '/' . basename($_SERVER['SCRIPT_NAME']);
$logoutURL = $baseURL . '/index.php?logout';

# Facebook setup
FacebookSession::setDefaultApplication(FACEBOOK_APP_KEY, FACEBOOK_APP_SECRET);
FacebookSession::enableAppSecretProof(false); # avoid error: 'Invalid appsecret_proof provided'
$facebookHelper = new FacebookRedirectLoginHelper($homeURL);

try {
    # Retrieve Facebook session after coming to this page for the first time only
    $session = $facebookHelper->getSessionFromRedirect();
    if (!isset($_SESSION['fb_session'])){
        $_SESSION['fb_session']  = $session;
        $_SESSION['user_role'] = "student"; # assign user role after login
    }
} catch(FacebookRequestException $ex) {
    // When Facebook returns an error
} catch(\Exception $ex) {
    // When validation fails or other local issues
}

# With a non-expired access token (saved in the session), we can make requests, else we generate an authentication URL.
if(isset($_SESSION['fb_session'])) {
    # Make a request to the facebook Graph API
    $request = new FacebookRequest($_SESSION['fb_session'], 'GET', '/me');
    $response = $request->execute();
    $graphObject = $response->getGraphObject();
    $userName= $graphObject->getProperty('first_name');

    printf('%s, you are logged in as a %s (<i>via Facebook</i>)', $userName, $_SESSION['user_role']);

    # Provide a logout URL
    $logoutURL = $baseURL . '/index.php?logout';
} else {
    $callbackURL = $homeURL . 'fb_callback.php';
    $facebookHelper = new FacebookRedirectLoginHelper($callbackURL);
    $facebookAuthURL = $facebookHelper->getLoginUrl();
}?>

<?php include('header.php')?>

    <div id="logout" class ="centered">

        <?php include('logoutGroup.php')?>

    </div><!--END centered-->

<?php include('footer.php')?>