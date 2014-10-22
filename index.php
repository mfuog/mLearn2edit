<?php
require_once __DIR__ . '/vendor/google/apiclient/autoload.php';
require_once __DIR__ . '/vendor/abraham/twitteroauth/twitteroauth/twitteroauth.php';
require_once __DIR__ . '/vendor/facebook/php-sdk-v4/autoload.php';
require_once __DIR__ . '/config.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequestException;

if ( session_id() == '' ) {
    $session = session_start();
}

# Commonly used URLs
$baseURL = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$homeURL = $baseURL . '/' . basename($_SERVER['SCRIPT_NAME']);
$logoutURL = $homeURL . '?logout';

# To handle logging out and expired access tokens, delete the local access token
# (usually saved in a database) and redirect to the home URL.
if (isset($_REQUEST['logout']) || isset($_REQUEST['expired'])) {

    unset($_SESSION['google_access_token']);
    unset($_SESSION['twitter_access_token']);
    unset($_SESSION['twitter_access_token_secret']);
    unset($_SESSION['twitter_request_token']);
    unset($_SESSION['twitter_request_token_secret']);
    unset($_SESSION['FBRLH_state']);
    if (isset($_SESSION) && !empty($_SESSION)) {
        $loggedOut = true;
    }
}

# Google setup
$googleClient = new Google_Client();
$googleClient->setClientId(GOOGLE_CLIENT_ID);
$googleClient->setClientSecret(GOOGLE_CLIENT_SECRET);
$googleClient->setDeveloperKey(GOOGLE_API_KEY);
$googleClient->setRedirectUri($baseURL);
$googleClient->addScope("https://www.googleapis.com/auth/youtube");
# Provide auth URL
$googleAuthURL = $googleClient->createAuthUrl();


##
# Twitter setup
##
$twitterConnection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
$requestToken = $twitterConnection->getRequestToken($baseURL . '/twitter_callback.php');
# Remember temporary token for use on callback page
$_SESSION['twitter_request_token'] = $requestToken['oauth_token'];
$_SESSION['twitter_request_token_secret'] = $requestToken['oauth_token_secret'];
# Provide auth URL
$twitterAuthURL = $twitterConnection->getAuthorizeURL($requestToken, false);

##
# Facebook setup
##
FacebookSession::setDefaultApplication(FACEBOOK_APP_KEY, FACEBOOK_APP_SECRET);
$facebookHelper = new FacebookRedirectLoginHelper($homeURL);
try {
    $_SESSION['FBRLH_state'] = $facebookHelper->getSessionFromRedirect();
} catch(FacebookRequestException $ex) {
    # When Facebook returns an error
} catch(\Exception $ex) {
    # When validation fails or other local issues
}
# Provide auth URL
$callbackURL = $baseURL;
$facebookHelper = new FacebookRedirectLoginHelper($callbackURL);
$facebookAuthURL = $facebookHelper->getLoginUrl();

include('header.php');?>


<div id="login-group" class ="centered">
<?php if (isset($googleAuthURL) && isset($twitterAuthURL) && isset($facebookAuthURL)) { ?>

    <?php if (isset($_REQUEST['expired'])) { ?>
    <div class="alert alert-warning" role="alert">Your access token has expired. Please log in again.</div>
    <?php } ?>

    <?php if (isset($loggedOut)) { ?>
    <div class="alert alert-info" role="alert">You have been logged out successfully.</div>
    <?php } ?>

    <p class="text-center">
        Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor.
    </p>

    <ul class="list-group">
        <li class="list-group-item">
            <span class="pull-left">
                Login as an administrator:
            </span>
            <a class="btn btn-twitter pull-right" href="<?php echo $twitterAuthURL; ?>"><i class="fa fa-twitter"></i> | Connect with Twitter</a>
            <br><br>
        </li>
        <li class="list-group-item">
            <span class="pull-left">
                To login as a teacher
            </span>
                <a class="btn btn-google-plus pull-right" href="<?php echo $googleAuthURL; ?>"><i class="fa fa-google"></i> | Connect with Google+</a>
            <br><br>
        </li>
        <li class="list-group-item">
            <span class="pull-left">
                Login as a student:
            </span>
            <a class="btn btn-facebook pull-right" href="<?php echo $facebookAuthURL; ?>"><i class="fa fa-facebook"></i> | Connect with Facebook</a>
            <br><br>
        </li>
    </ul>
    <?php } ?>

</div><!--END centered-->

<?php include('footer.php') ?>