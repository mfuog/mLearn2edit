<?php
require_once __DIR__ . '/vendor/facebook/php-sdk-v4/autoload.php';
require_once __DIR__ . '/config.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;

$session = session_start();
$thisURL = BASE_URL . '/' . basename($_SERVER['SCRIPT_NAME']);

##
# Facebook Authentication
##

# Setup
FacebookSession::setDefaultApplication(FACEBOOK_APP_KEY, FACEBOOK_APP_SECRET);
FacebookSession::enableAppSecretProof(false); # avoid error: 'Invalid app secret_proof provided'
$facebookHelper = new FacebookRedirectLoginHelper($thisURL);

try {
    # Retrieve Facebook session after coming to this page for the first time only
    $session = $facebookHelper->getSessionFromRedirect();
    if (!isset($_SESSION['fb_session'])){
        $_SESSION['fb_session']  = $session;
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
    $_SESSION['user_name'] = $graphObject->getProperty('first_name');
    $_SESSION['user_role'] = "student"; # assign user role after login
    printf('%s, you are logged in as a %s (<i>via Facebook</i>)', $_SESSION['user_name'], $_SESSION['user_role']);
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
# Retrieve all group names
$groupNames = getGroupNames($datasets);

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
                Select your group
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo $thisURL ?>"><i>none</i></a></li>
                <?php foreach($groupNames as $groupName) {?>
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="?groupname=<?php echo urlencode($groupName)?>"><?php echo $groupName?></a></li>
                <?php }?>
            </ul>
        </div>

        <!--title-->
        <h3>Saved image data <span class="badge alert-success"><?php echo $_SESSION['user_role'] ?></span></h3>
        <hr>

        <!--describtion-->
        <div class="well">
            Showing image-containing datasets that:
            <ul>
                <?php if(isset($_GET['groupname'])){ ?>
                <li>were submitted by group <span class="alert-success"><?php echo( !empty($_GET['groupname']) ? $_GET['groupname'] :  '-') ?></span></li>
                <?php } ?>
                <li>originate from a scenario tagged <span class="alert-success"><b>[allStudents]</b></span></li>
            </ul>
            Images collected by students while solving a scenario, are marked as <span class="badge alert-info">originals</span>
            while manipulated copies are marked as <span class="badge alert-danger">edited</span>.
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
                    # ...and was authored by the group or made public for all students
                    && ((isset($_GET['groupname']) && $dataset['groupname'] == $_GET['groupname'])|| strpos($scenarioString, "[allStudents]"))) {?>

                    <li>
                        <h4>
                            <b>Scenario: </b><?php echo $scenario['title'] ?>
                            <?php if (strpos($scenarioString, "[allStudents]")){?>
                                <span class="badge alert-success">[allStudents]</span>
                            <?php } ?>
                        </h4>
                        <?php if (isset($_GET['groupname']) && $dataset['groupname'] == $_GET['groupname']) { ?>
                            <b>Group: </b> <span class="alert-success"><?php echo $dataset['groupname'] ?></span><br>
                        <?php } else { ?>
                            <b>Group: </b> <?php echo $dataset['groupname'] ?><br>
                        <?php } ?>
                        <b>Submitted data:</b>
                        <ul>
                            <?php foreach($dataset['data'] as $key=>$screen) {
                                # only display a screen, if it contains any images
                                if (strpos(json_encode($screen), "image") !== false ) {?>
                                    <li>
                                        <b><?php echo $key ?>:</b>
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
                                                        <b>Image</b>
                                                        <?php if (isset($version)) { ?>
                                                            <span class="badge alert-danger">edited (<?php echo $version ?>)</span>
                                                        <?php } else {?>
                                                            <span class="badge alert-info">original</span>
                                                        <?php }?>
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
function getGroupNames($datasets){
    $groupNames = array();
    foreach ($datasets as $dataset) {
        if (isset($dataset['groupname'])) {
            $groupNames[] = $dataset['groupname'];
        }
    }
    return array_unique($groupNames);
}
?>