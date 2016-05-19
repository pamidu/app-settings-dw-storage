<?php
$mainDomain="duoworld.com";
$authURI="auth.duoworld.com:3048/";
$objURI="obj.duoworld.com:3000/";
$fullhost=strtolower($_SERVER['HTTP_HOST']);
define("ROOT_PATH", $_SERVER['DOCUMENT_ROOT']);
define("MEDIA_PATH", "/var/media");
define("APPICON_PATH", "/var/www/html/devportal/appicons");
        //define("BASE_PATH", "/var/www/html/medialib");
        //define("STORAGE_PATH", BASE_PATH . "/media");
        //define("THUMB_PATH", BASE_PATH . "/thumbnails");
define("SVC_OS_URL", "http://obj.duoworld.com:3000");
define("SVC_OS_BULK_URL", "http://obj.duoworld.com:3001/transfer");
define("SVC_AUTH_URL", "http://auth.duoworld.com:3048");
define ("STRIPE_KEY","");
?>
