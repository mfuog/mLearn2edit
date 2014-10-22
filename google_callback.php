<?php
require_once __DIR__ . '/vendor/google/apiclient/autoload.php';
require_once __DIR__ . '/config.php';

if ( session_id() == '' ) {
    $session = session_start();
}
/** Setup: Set commonly used, relative URLs */
$baseURL = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$homeURL = $baseURL . '/' . basename($_SERVER['SCRIPT_NAME']);
$logoutURL = $baseURL . '/index.php?logout';

# Google client setup
$googleClient = new Google_Client();
$googleClient->setClientId(GOOGLE_CLIENT_ID);
$googleClient->setClientSecret(GOOGLE_CLIENT_SECRET);
$googleClient->setDeveloperKey(GOOGLE_API_KEY);
$googleClient->setRedirectUri($homeURL);
$googleClient->addScope("https://www.googleapis.com/auth/userinfo.profile");


# With a non-expired access token (saved in the session), we can make requests, else we generate an authentication URL.
if (isset($_SESSION['google_access_token'])) {
    $googleClient->setAccessToken($_SESSION['google_access_token']);
    # for debugging: Exchange with previous line to simulate expiration of token
    #$googleClient->setAccessToken('{"access_token":"ya29.nAC6RJ_DxJjyQFa9R_zT1JNp3WMXwwUU7f6iQ6H3auVq5BVCasnACbL1","token_type":"Bearer","expires_in":30,"created":1413112056}');
}

# If we have a GET response from the authentication URL, exchange it with final the access token and
# store that (bundle) in the session. Redirect to homeURL.
if (isset($_GET['code'])) {

    try {
        $googleClient->authenticate($_GET['code']);
        $_SESSION['google_access_token'] = $googleClient->getAccessToken();
        header('Location: ' . filter_var($homeURL, FILTER_SANITIZE_URL));
    } catch (Google_Auth_Exception $ex) {
        // When Google returns an error
    } catch (\Exception $ex) {
        // When validation fails or other local issues
    }
}

if ($googleClient->getAccessToken()) {
    if($googleClient -> isAccessTokenExpired()){
        echo "Access has expired, please login again:";
        $googleAuthURL = $googleClient->createAuthUrl();
        # if the access token has expired, logout to acquire a new one by enforcing a new sign-in
        header('Location: ' . filter_var($logoutURL, FILTER_SANITIZE_URL) . '&expired');
    } else {

        # remember the google access token for later use (this normally would be saved to a database)
        $_SESSION['google_access_token'] = $googleClient->getAccessToken();

        $plus = new Google_Service_Plus($googleClient);
        $user = $plus->people->get('me');
        printf('%s, you are logged in as an admin (<i>via Google</i>)', $user->displayName);

    }
}?>

<?php include('header.php')?>

    <div id="logout" class ="centered">

        <?php include('logoutGroup.php')?>

    </div><!--END centered-->

<?php include('footer.php')?>