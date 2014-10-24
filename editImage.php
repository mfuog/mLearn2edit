<?php
$session = session_start();

##
# Authentication
##

# Set commonly used URLs
$baseURL = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$homeURL = $baseURL . '/' . basename($_SERVER['SCRIPT_NAME']);
$logoutURL = $baseURL . '/index.php?logout';

if (isset($_SESSION['twitter_access_token'])) {
    printf('%s, you are logged in as an %s (<i>via Twitter</i>)', $_SESSION['user_name'], $_SESSION['user_role']);
} else {
    # if the access token has expired, logout to acquire a new one by enforcing a new sign-in
    header('Location: ' . filter_var($logoutURL, FILTER_SANITIZE_URL) . '&expired');
}

##
# Image manipulation
##

# Check for the original image's URL
if (isset($_GET['imageURL'])) {
    $imageURL = $_GET['imageURL'];
}

# Check for the manipulated image's URL
if(isset($_POST['url'])) {
    $imageURL = $_POST['url'];
    # Read image path, base64 encode, get file extention
    $imageData = base64_encode(file_get_contents($imageURL));
    $extention = pathinfo($imageURL, PATHINFO_EXTENSION);

    # Create data URI with format: 'data:{mimeType};base64,{data}'
    $dataURL = 'data:image/' . $extention . ';base64,'.$imageData;
}?>


<?php include('header.php')?>

    <div id="content" class ="centered">
        <?php include('logoutGroup.php')?>
        <a href="<?php echo $_SERVER['HTTP_REFERER'] ?>" class="btn btn-default pull-right"><span class="glyphicon glyphicon-chevron-left"></span>Back</a>

        <h3>Image manipulation</h3>
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

        <?php if(isset($_GET['imageURL'])) { ?>
            <h4>Original image:</h4>
            <button class="btn btn-default" type="submit" form="sumoEdit" value="Submit">Edit</button>
        <?php } else { ?>
            <h4>Edited image:</h4>
            <button class="btn btn-default" type="submit" form="sumoEdit" value="Submit">Edit again</button>
            <a href="#" class="btn btn-default" disabled>Overwrite original</a>
            <a href="#" class="btn btn-default" disabled>Save as copy</a>
        <?php } ?>

        <form id="sumoEdit" action="http://www.sumoware.com/paint/" method="POST">
            <input type="hidden" name="cloud" value="false" />
            <input type="hidden" name="title" value="Image manipulation" />
            <input type="hidden" name="service" value="Save image to API target" />
            <input type="hidden" name="target" value="http://mylocaldomain.net/lnu/mlearn4web/editImage.php" />
            <input type="hidden" name="url" value="<?php echo $imageURL ?>" />
            <input type="image" src="<?php echo $imageURL ?>" />
        </form>
</div><!--END centered-->

<?php include('footer.php')?>