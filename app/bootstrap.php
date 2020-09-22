<?php
//Init Session
session_set_cookie_params('','/;SameSite=Lax','',true,true);
session_start();
//Import Configs
require_once("config/config.php");
//Set Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: must-revalidate, no-cache="Set-Cookie, Set-Cookie2"');
header('pragma: no-cache');

//Autoload libraries
spl_autoload_register(function($class_name)
{
    require_once 'libraries/' . $class_name . '.php';
});

?>
