<?php
require_once __DIR__ . '/vendor/google/apiclient/autoload.php';
require_once __DIR__ . '/config.php';

if ( session_id() == '' ) {
    $session = session_start();
}
# Commonly used URLs
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
        $_SESSION['user_role'] = "teacher"; # assign user role after login

        $plus = new Google_Service_Plus($googleClient);
        $user = $plus->people->get('me');
        printf('%s, you are logged in as a %s (<i>via Google</i>)', $user->displayName, $_SESSION['user_role']);
    }
}

# commonly used mlearn4web URLs
$serviceHost = "http://celtest1.lnu.se:3030";
$baseUrlAPI = $serviceHost . "/mlearn4web";

# No need to retrieve datasets if form for teacher ID retrieval is active
if(isset($_GET['teacherID'])) {
    # Retrieve all datasets
    $datasetsRequest = $baseUrlAPI . "/getalldata";
    $datasets = trim(file_get_contents($datasetsRequest));
    $datasets = json_decode($datasets, true);
}

?>



<?php include('header.php')?>

    <div id="content" class ="centered">
        <?php include('logoutGroup.php')?>
        <h3>Saved image data <span class="badge alert-warning"><?php echo $_SESSION['user_role'] ?></span></h3>

        <?php if(!isset($_GET['teacherID'])) { ?>
            <div class="well">Enter your <i>mlearn4web</i> user ID in order to list image containing datasets that were committed for scenarios created by you.</div>

            <form method="GET" role="search">
                <button type="submit" class="btn btn-primary pull-right">Proceed</button>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">Teacher's ID:</span>
                        <input class="form-control" type="search" id="teacherID" name="teacherID" placeholder="e.g. 544510f3f70096dc60645672">
                    </div>
                </div>

            </form>
            <hr>
        <?php } else { ?>
            <div class="well">Showing image containing datasets that:
                <ul>
                    <li>belong to scenarios created by you <i>(user ID: <?php echo $_GET['teacherID'] ?>)</i></li>
                    <li>originate from a scenario tagged <i>[allTeachers]</i></li>
                </ul>
            </div>
            <ol>
                <?php foreach($datasets as $dataset) {
                    $scenarioRequest = $baseUrlAPI . "/get/" . $dataset['scenarioId'];
                    $scenarioString = trim(file_get_contents($scenarioRequest));
                    $scenario = json_decode($scenarioString, true);
                    $datasetString = json_encode($dataset);

                    # Only display a dataset, if it contains any images...
                    if (strpos($datasetString, "image") !== false
                        # ...and if the scenario was authored by the user or made public for all teachers
                        && ($scenario['user'] == $_GET['teacherID'] || strpos($scenarioString, "[allTeachers]"))) {?>

                        <li>
                            <h4><b>Scenario: </b><?php echo $scenario['title'] ?></h4>
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