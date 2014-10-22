<ul class="list-group">
    <li class="list-group-item">
                <span class="pull-left">
                    <?php if(isset($_SESSION['google_access_token'])) { ?>
                        <a class="btn btn-google-plus" href="<?php echo $logoutURL ?>"><i class="fa fa-google"></i> | Logout from Google+</a>
                    <?php } elseif ($_SESSION['twitter_access_token']) {?>
                        <a class="btn btn-twitter" href="<?php echo $logoutURL ?>"><i class="fa fa-twitter"></i> | Logout from Twitter</a>
                    <?php } elseif (isset($_SESSION['fb_session'])) {?>
                        <a class="btn btn-facebook" href="<?php echo $logoutURL ?>"><i class="fa fa-facebook"></i> | Logout from Facebook</a>
                    <?php } ?>
                </span>
        <br><br>
    </li>
</ul>