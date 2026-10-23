<?php
if (getenv('APP_ENV') !== 'testing' || getenv('DB_DATABASE') !== '/tmp/adsr-baseline.sqlite') { exit(2); }
putenv('SPEEDRUN_DECIMALS=5'); // documented example default
require '/app/vendor/autoload.php';
if (getenv('ADSR_AUTH_BASELINE') === 'source') { return require '/app/bootstrap/app.php'; }
// Reproduce only the previously observed live auth differences, without editing app sources.
$source=file_get_contents('/app/app/Models/User.php');
$source=str_replace([' implements MustVerifyEmail','    use TwoFactorAuthenticatable;'],['',''], $source);
file_put_contents('/tmp/BaselineUser.php',$source);
require '/tmp/BaselineUser.php';
$source=file_get_contents('/app/app/Actions/Fortify/UpdateUserProfileInformation.php');
$source=str_replace('use Illuminate\\Contracts\\Auth\\MustVerifyEmail;', '',$source);
file_put_contents('/tmp/BaselineUpdateUserProfileInformation.php',$source);
require '/tmp/BaselineUpdateUserProfileInformation.php';
$app=require '/app/bootstrap/app.php';
return $app;
