
<div id="logout" class="pull-right">
<?php if(isset($_SESSION['google_access_token'])) { ?>
    <a class="btn btn-google-plus" href="<?php echo LOGOUT_URL ?>"><i class="fa fa-google"></i> | Logout</a>
<?php } elseif (isset($_SESSION['twitter_access_token'])) {?>
    <a class="btn btn-twitter" href="<?php echo LOGOUT_URL ?>"><i class="fa fa-twitter"></i> | Logout</a>
<?php } elseif (isset($_SESSION['fb_session'])) {?>
    <a class="btn btn-facebook" href="<?php echo LOGOUT_URL ?>"><i class="fa fa-facebook"></i> | Logout</a>
<?php } ?>
</div>