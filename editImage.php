<?php
$session = session_start();

# Set commonly used URLs
$baseURL = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$homeURL = $baseURL . '/' . basename($_SERVER['SCRIPT_NAME']);
$logoutURL = $baseURL . '/index.php?logout';

##
# Authentication
##

if ($_SESSION['user_role'] == 'admin') {
    printf('%s, you are logged in as an %s (<i>via Twitter</i>)', $_SESSION['user_name'], $_SESSION['user_role']);
    $imageListURL = $baseURL . '/twitter_callback.php';
} else if ($_SESSION['user_role'] == 'teacher') {
    printf('%s, you are logged in as a %s (<i>via Google</i>)', $_SESSION['user_name'], $_SESSION['user_role']);
    $imageListURL = $baseURL . '/google_callback.php';
} else if ($_SESSION['user_role'] == 'student') {
    printf('%s, you are logged in as a %s (<i>via Facebook</i>)', $_SESSION['user_name'], $_SESSION['user_role']);
    $imageListURL = $baseURL . '/fb_callback.php';
} else {
    # if the access token has expired, logout to acquire a new one by enforcing a new sign-in
    header('Location: ' . filter_var($logoutURL, FILTER_SANITIZE_URL) . '&expired');
}

##
# Manage needed params for displaying (here) and updating (updateData.php) the image.
##

# Check for the manipulated image's URL (from sumopaint)
if(isset($_POST['url'])) {
    $imageURL = $_POST['url'];
    # Extract image data from path and base64 encode it
    $image = file_get_contents($_POST['url']);
    $_SESSION['newImageData'] = base64_encode($image);
} else if (isset($_GET['oldImageURL'])) {
    # Check for the original image's URL
    $imageURL = $_GET['oldImageURL'];
    $_SESSION['scenarioID'] = $_GET['scenarioID'];
    $_SESSION['datasetID'] = $_GET['datasetID'];
    $_SESSION['oldImagePath'] = $_GET['oldImagePath'];
}?>

<?php include('header.php')?>

    <div id="content" class ="centered">
        <?php include('logoutGroup.php')?>
        <a href="<?php echo $imageListURL ?>" class="btn btn-default pull-right"><span class="glyphicon glyphicon-chevron-left"></span>Back</a>

        <h3>Image manipulation</h3>

        <?php
        # Only show instruction, if user has not yet saved any changes.
        #   (This is due to the limitation of the API which doesn't allow retrieving an image by a fixed ID. With the
        #   path having changed after updating the image, it can't be used to identify the element anymore.)
        if(isset($_POST['url']) || isset($_GET['oldImageURL'])){ ?>
        <div class="well">
            <ol>
                <li>Click on image to <button class="btn btn-default btn-xs" type="submit" form="sumoEdit" value="Submit">Edit</button></li>
                <li>In <a href="http://www.sumopaint.com/">Sumopaint</a> editor, after editing:
                    <ul>
                        <li>hit <span class="alert-info"><i>Ctrl+S</i></span></li>
                        <li>or <span class="alert-info"><i>File > Save image to API target</i></span></li>
                    </ul>
                </li>
            </ol>
        </div>
        <?php } ?>

        <?php if(isset($_POST['url'])){ ?>
            <h4>Edited image:</h4>
            <button class="btn btn-default" type="submit" form="sumoEdit" value="Submit">Edit again</button>
            <a href="updateData.php" class="btn btn-default" >Overwrite original</a>
            <a href="#" class="btn btn-default" disabled>Save as copy</a>
        <?php } else if(isset($_GET['oldImageURL'])) { ?>
            <h4>Original image:</h4>
            <button class="btn btn-default" type="submit" form="sumoEdit" value="Submit">Edit</button>
        <?php } else {
            # Neither POST nor GET: User returned from updateData.php. See comment above. ?>
            <div class="alert alert-success" role="alert">The edited image has been saved and its dataset was updated.</div>
        <?php } ?>

        <?php
        # Only show form to edit picture, if user has not yet saved any changes. See comment above.
        if(isset($_POST['url']) || isset($_GET['oldImageURL'])){ ?>
        <form id="sumoEdit" action="http://www.sumoware.com/paint/" method="POST">
            <input type="hidden" name="cloud" value="false" />
            <input type="hidden" name="title" value="Image manipulation" />
            <input type="hidden" name="service" value="Save image to API target" />
            <input type="hidden" name="target" value="http://mylocaldomain.net/lnu/mlearn4web/editImage.php" />
            <input type="hidden" name="url" value="<?php echo $imageURL ?>" />
            <input type="image" src="<?php echo $imageURL ?>" />
        </form>
        <?php } ?>
</div><!--END centered-->
<?php include('footer.php');