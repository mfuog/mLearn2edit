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
    $_SESSION['user_role'] = "admin"; # assign user role after login
}

if (isset($_SESSION['twitter_access_token'])) {
    $user = $twitter->get('account/verify_credentials');
    printf('%s, you are logged in as an %s (<i>via Twitter</i>)', $user->name, $_SESSION['user_role']);
}

# commonly used mlearn4web URLs
$serviceHost = "http://celtest1.lnu.se:3030";
$baseUrlAPI = $serviceHost . "/mlearn4web";

# Retrieve all datasets
$datasetsRequest = $baseUrlAPI . "/getalldata";
$datasets = trim(file_get_contents($datasetsRequest));
$datasets = json_decode($datasets, true);
?>


<?php include('header.php')?>

<div class ="centered">
    <ol>
    <?php foreach($datasets as $dataset) {
        $scenarioRequest = $baseUrlAPI . "/get/" . $dataset['scenarioId'];
        $scenario = json_decode(trim(file_get_contents($scenarioRequest)), true);?>

        <li>
            <b>Group name: </b> <?php echo $dataset['groupname'] ?><br>
            <b>From scenario: </b><?php echo $scenario['title'] ?><br>
            <b>Submitted data:</b>
            <ul>
                <?php foreach($dataset['data'] as $key=>$screen) { ?>
                <li>
                    <b><?php echo $key ?>:</b>
                    <ul>
                        <?php foreach($screen as $element) { ?>

                            <?php if ($element['type'] == 'image') { ?>
                                <li>
                                <b>Image element:</b> <?php var_dump($element)?>
                                </li>
                            <?php } ?>

                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </li>

    <?php } ?>
    </ol>


    <?php include('logoutGroup.php')?>

</div><!--END centered-->

<?php include('footer.php')?>