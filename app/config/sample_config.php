<?php
//Once the config is configured to your liking, rename it to config.php to use this file in your build.

//Database Setup
define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'db_name');
define('SUPPORT', 'support@localhost.com');
//App Root
define('APPROOT', dirname(dirname(__FILE__)));
//Encryption Method
define('ENC_METHOD', 'YOUR_ENCRYPTION_METHOD');
//URL Root
define('URLROOT', 'your_url_here');
define('URLROOT_ADMIN', URLROOT.'/admin');

//Site Name
define('SITENAME', 'American Dad Speedruns');
define('EMAIL_DOMAIN', 'americandadspeedruns.com');
?>
