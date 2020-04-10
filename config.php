<?php
ob_start();
session_start();
set_time_limit(0);
ini_set('display_errors', 0);
include __DIR__."/Oauth.php";
include __DIR__."/functions.php";

$app_id     = "791645947977135";
$app_sceret = "cb3f206c1dd047b018ed11eac99c858f";

$datetime	= date('Y-m-d G:i:s');

$dbhost = "localhost";
$dbuser = "shotnjmy_sd";
$dbpass = "hello@123";
$dbname = "shotnjmy_sd";
$conn   = mysqli_connect($dbhost, $dbuser, $dbpass) or die("Can't Connect");
mysqli_select_db($conn, $dbname) or die("Can't select Db");
