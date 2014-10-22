<?php
require_once __DIR__ . '/vendor/abraham/twitteroauth/twitteroauth/twitteroauth.php';
require_once __DIR__ . '/config.php';

if ( session_id() == '' ) {
    $session = session_start();
}
# Set commonly used URLs
$baseURL = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$homeURL = $baseURL . '/' . basename($_SERVER['SCRIPT_NAME']);
$logoutURL = $baseURL . '/index.php?logout';

# Twitter oauth: Exchange temporary for real access token
if (isset($_GET['oauth_token'])) {

    # use the user's previously stored temporary credentials here
    $twitter = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET,
        $_SESSION['twitter_request_token'], $_SESSION['twitter_request_token_secret']);
    $version = $twitter->useAPIVersion("1.1");

    # uses the oauth_token from the request
    $credentials = $twitter->getAccessToken($_GET['oauth_verifier']);

    # save real access token (user's credentials are normally to be stored in a database)
    $_SESSION['twitter_access_token'] = $credentials['oauth_token'];
    $_SESSION['twitter_access_token_secret'] = $credentials['oauth_token_secret'];
}

if (isset($_SESSION['twitter_access_token'])) {
    $user = $twitter->get('account/verify_credentials');
    printf('%s, you are logged in as: admin', $user->name);
}?>

<?php include('header.php')?>

<div id="logout" class ="centered">

    <?php include('logoutGroup.php')?>

</div><!--END centered-->

<?php include('footer.php')?>