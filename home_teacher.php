<?php
require_once __DIR__ . '/vendor/google/apiclient/autoload.php';
require_once __DIR__ . '/config.php';

$session = session_start();
$thisURL = BASE_URL . '/' . basename($_SERVER['SCRIPT_NAME']);

##
# Google Authentication
##

# Google client setup
$googleClient = new Google_Client();
$googleClient->setClientId(GOOGLE_CLIENT_ID);
$googleClient->setClientSecret(GOOGLE_CLIENT_SECRET);
$googleClient->setDeveloperKey(GOOGLE_API_KEY);
$googleClient->setRedirectUri($thisURL);
$googleClient->addScope("https://www.googleapis.com/auth/userinfo.profile");

# Attempt to exchange the GET response from the authentication URL, for a valid authentication token.
if (isset($_GET['code'])) {
    try {
        $_SESSION['google_access_token'] = $googleClient->authenticate($_GET['code']);
        header('Location: ' . filter_var($thisURL, FILTER_SANITIZE_URL));
    } catch (Google_Auth_Exception $ex) {
        // When Google returns an error
    } catch (\Exception $ex) {
        // When validation fails or other local issues
    }
}

# Check if user is logged in
if (isset($_SESSION['google_access_token'])) {
    # Reset authentication token to $googleClient (because site was reloaded)
    $googleClient->setAccessToken($_SESSION['google_access_token']);

    # If the access token has expired, logout to acquire a new one by enforcing a new sign-in.
    if($googleClient -> isAccessTokenExpired()){
        header('Location: ' . filter_var(LOGOUT_URL, FILTER_SANITIZE_URL) . '&expired');
    } else {
        # Remember the google access token for later use (this normally would be saved to a database).
        $_SESSION['google_access_token'] = $googleClient->getAccessToken();
        $_SESSION['user_role'] = "teacher"; # assign user role after login

        # retrieve user data
        $plus = new Google_Service_Plus($googleClient);
        $_SESSION['user_name'] = $plus->people->get('me')->getName()['givenName'];
        printf('%s, you are logged in as a %s (<i>via Google</i>)', $_SESSION['user_name'], $_SESSION['user_role']);
    }
} else {
    # Not logged in: Return to home page
    header('Location: ' . filter_var(LOGOUT_URL, FILTER_SANITIZE_URL) . '&prohibited');
}

##
# Manage content
##

# Retrieve all datasets
$datasetsRequest = MLEARN4WEB_API_URL . "/getalldata";
$datasets = trim(file_get_contents($datasetsRequest));
$datasets = json_decode($datasets, true);

# Retrieve all scenarios
$scenariosRequest = MLEARN4WEB_API_URL . "/getall";
$scenarios = trim(file_get_contents($scenariosRequest));
$scenarios = json_decode($scenarios, true);
# Retrieve all user IDs
$userIDs = getAllUserIDs($scenarios);

# Unset values previously used by editImage.php
unset($_SESSION['datasetID']);
unset($_SESSION['oldImagePath']);
unset($_SESSION['oldImageURL']);
unset($_SESSION['newImageData']);
?>

<?php include('header.php')?>

    <div id="content" class ="centered">
        <?php include('logoutGroup.php')?>

        <!--dropdown-->
        <div class="dropdown pull-right">
            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                Select your user ID
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo $thisURL ?>"><i>none</i></a></li>
                <?php foreach($userIDs as $id) {?>
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="?teacherID=<?php echo $id?>"><?php echo $id?></a></li>
                <?php }?>
            </ul>
        </div>

        <!--title-->
        <h3>Saved image data <span class="badge alert-warning"><?php echo $_SESSION['user_role'] ?></span></h3>

        <!--describtion-->
        <div class="well">
            Showing image-containing datasets that:
            <ul>
                <?php if(isset($_GET['teacherID'])){ ?>
                <li>belong to scenarios created by you <span class="alert-warning">(user ID: <?php echo $_GET['teacherID'] ?>)</span></li>
                <?php } ?>
                <li>originate from a scenario tagged <span class="badge alert-warning">[allTeachers]</span></li>
            </ul>
        </div>

        <!--dataset listing-->
        <ol>
            <?php foreach($datasets as $dataset) {
                $scenarioRequest = MLEARN4WEB_API_URL . "/get/" . $dataset['scenarioId'];
                $scenarioString = trim(file_get_contents($scenarioRequest));
                $scenario = json_decode($scenarioString, true);
                $datasetString = json_encode($dataset);

                # Only display a dataset, if it contains any images...
                if (strpos($datasetString, "image") !== false
                    # ...and if the scenario was authored by the user or made public for all teachers
                    && ((isset($_GET['teacherID']) && $scenario['user'] == $_GET['teacherID']) || strpos($scenarioString, "[allTeachers]"))) {?>

                    <li>
                        <h4>
                            <b>Scenario: </b><?php echo $scenario['title'] ?>
                            <?php if (strpos($scenarioString, "[allTeachers]")){?>
                                <span class="badge alert-warning">[allTeachers]</span>
                            <?php } ?>
                        </h4>

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
                                                        .'&oldImageURL=' . MLEARN4WEB . $element['value'];
                                                    ?>
                                                    <li>
                                                        <b>Image:</b>
                                                        <a href="<?php echo BASE_URL . '/editImage.php' . $getParams ?>" class="btn btn-default btn-xs">click to manipulate</a>
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

<?php
function getAllUserIDs($scenarios){
    $userIDs = array();
    foreach ($scenarios as $scenario) {
        $userIDs[] = $scenario['user'];
    }
    return array_unique($userIDs);
}
?>