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

##
# Twitter Authentication
##

# Exchange temporary for real access token
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
    $_SESSION['user_role'] = "admin"; # assign user role after login
    $_SESSION['user_name'] = $twitter->get('account/verify_credentials')->name;
}

if (isset($_SESSION['twitter_access_token'])) {
    printf('%s, you are logged in as an %s (<i>via Twitter</i>)', $_SESSION['user_name'], $_SESSION['user_role']);
} else {
    # Not logged in: Return to home page
    header('Location: ' . filter_var($logoutURL, FILTER_SANITIZE_URL) . '&prohibited');
}

##
# Manage content
##

# commonly used mlearn4web URLs
$serviceHost = "http://celtest1.lnu.se:3030";
$baseUrlAPI = $serviceHost . "/mlearn4web";

# Retrieve all datasets
$datasetsRequest = $baseUrlAPI . "/getalldata";
$datasets = trim(file_get_contents($datasetsRequest));
$datasets = json_decode($datasets, true);

# Unset values previously used by editImage.php
unset($_SESSION['scenarioID']);
unset($_SESSION['datasetID']);
unset($_SESSION['oldImagePath']);
unset($_SESSION['oldImageURL']);
unset($_SESSION['newImageData']);
?>


<?php include('header.php')?>

<div id="content" class ="centered">
    <?php include('logoutGroup.php')?>
    <h3>Saved image data for all scenarios <span class="badge alert-info"><?php echo $_SESSION['user_role'] ?></span></h3>
    <div class="well">Below, all datasets that contain any images are listed.</div>
    <ol>
    <?php foreach($datasets as $dataset) {
        $scenarioRequest = $baseUrlAPI . "/get/" . $dataset['scenarioId'];
        $scenario = json_decode(trim(file_get_contents($scenarioRequest)), true);
        # only display a dataset, if it contains any images
        if (strpos(json_encode($dataset), "image") !== false ) {?>

        <li>
            <h4><b>Scenario: </b><?php echo $scenario['title'] ?></h4>
            <b>Group: </b> <?php echo $dataset['groupname'] ?><br>
            <b>Submitted data:</b>
            <ul>
                <?php foreach($dataset['data'] as $screenKey=>$screen) {
                    # only display a screen, if it contains any images
                    if (strpos(json_encode($screen), "image") !== false ) {?>
                        <li>
                            <b><?php echo $screenKey ?>:</b>
                            <ul>
                                <?php foreach($screen as $element) { ?>

                                    <?php if ($element['type'] == 'image') {
                                        # Remember params for saving the image in updateData.php (after the edit process).
                                        $getParams = '?scenarioID=' . $dataset['scenarioId']
                                                    .'&datasetID=' . $dataset['_id']
                                                    .'&oldImagePath=' . $element['value']
                                                    .'&oldImageURL=' . $serviceHost . $element['value'];
                                        ?>
                                        <li>
                                            <b>Image:</b>
                                            <a href="<?php echo $baseURL . '/editImage.php' . $getParams ?>" class="btn btn-default btn-xs">click to manipulate</a>
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
</div><!--END centered-->

<?php include('footer.php')?>