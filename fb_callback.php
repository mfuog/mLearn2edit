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
}


# commonly used mlearn4web URLs
$serviceHost = "http://celtest1.lnu.se:3030";
$baseUrlAPI = $serviceHost . "/mlearn4web";

# No need to retrieve datasets if form for group name retrieval is active
if(isset($_GET['groupname'])) {
    # Retrieve all datasets
    $datasetsRequest = $baseUrlAPI . "/getalldata";
    $datasets = trim(file_get_contents($datasetsRequest));
    $datasets = json_decode($datasets, true);
}

?>


<?php include('header.php')?>

    <div id="content" class ="centered">
        <?php include('logoutGroup.php')?>
        <h3>Saved image data <span class="badge alert-success"><?php echo $_SESSION['user_role'] ?></span></h3>

        <?php if(!isset($_GET['groupname'])) { ?>
            <div class="well">Enter your group name in order to list image containing datasets submitted by your group.</div>

            <form method="GET" role="search">
            <button type="submit" class="btn btn-primary pull-right">Proceed</button>
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">Group name:</span>
                    <input class="form-control" type="search" id="groupname" name="groupname" placeholder="e.g. Steve & David">
                </div>
            </div>

        </form>
        <hr>
        <?php } else { ?>

        <div class="well">Showing image containing datasets, submitted by <i>"<?php echo $_GET['groupname'] ?>"</i>.</div>
        <ol>
            <?php foreach($datasets as $dataset) {
                $scenarioRequest = $baseUrlAPI . "/get/" . $dataset['scenarioId'];
                $scenario = json_decode(trim(file_get_contents($scenarioRequest)), true);
                # only display a dataset, if it contains any images and was authored by the group
                if (strpos(json_encode($dataset), "image") !== false && $dataset['groupname'] == $_GET['groupname']) {?>

                    <li>
                        <h4><b>For scenario: </b><?php echo $scenario['title'] ?></h4>
                        <b>Group: </b> <?php echo $dataset['groupname'] ?><br>
                        <b>Submitted data:</b>
                        <ul>
                            <?php foreach($dataset['data'] as $key=>$screen) {
                                # only display a screen, if it contains any images
                                if (strpos(json_encode($screen), "image") !== false ) {?>
                                    <li>
                                        <b><?php echo $key ?>:</b>
                                        <ul>
                                            <?php foreach($screen as $element) { ?>

                                                <?php if ($element['type'] == 'image') { ?>
                                                    <li>
                                                        <b>Image element:</b> <a href="<?php echo $serviceHost . $element['value']?>">image</a>
                                                    </li>
                                                <?php } ?>

                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </li>
                    <hr>
                <?php } ?>
            <?php } ?>
        </ol>
        <?php } ?>
    </div><!--END centered-->

<?php include('footer.php')?>