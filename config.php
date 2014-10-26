<?php
/**
 * Google+ application and developer constants.
 * Naming of application credentials:
 * - client ID (for web applications)
 * - client secret
 * - API key (for server applications)
 *
 * Look up and adjust at: https://console.developers.google.com
 */
define('GOOGLE_CLIENT_ID', '136937553860-37ncd9iu5q08j3ok44nrri8patdc1fjo.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'bnhMeFS4v2jS1RyWGbILHQ19');
define('GOOGLE_API_KEY', 'AIzaSyCI_-Oi61V30dNrSwxu5QmXY4tFA-2nacM');

/**
 * Twitter application and developer constants.
 * Naming of application credentials:
 * - consumer key (API key)
 * - consumer key (API secret)
 *
 * Look up and adjust at: https://apps.twitter.com/
 */
define('TWITTER_CONSUMER_KEY', 'JHh47boM5DboDAhYJxJhrLJmn');
define('TWITTER_CONSUMER_SECRET', 'hb463hsWg8DxUBnieO36tSceEDnoFXuQUJHPNGRqxNPDTkikuC');

/**
 * Facebook application and developer constants.
 * Naming of application credentials:
 * - App key
 * - App secret
 *
 * Look up and adjust at: https://developers.facebook.com/apps
 */
define('FACEBOOK_APP_KEY', '774184132645579');
define('FACEBOOK_APP_SECRET', 'fcf08b757efe2ef7f9d6c4d1b7d993b4');

/**
 * Frequently used URLs.
 */
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));
define('LOGOUT_URL', BASE_URL . '?logout');


