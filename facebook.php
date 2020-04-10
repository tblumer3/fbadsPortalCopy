<?php
include __DIR__."/config.php";

$app = array(
    "appid" => $app_id,
    "appsecret" => $app_sceret,
    "permissions" => "manage_pages,ads_management"
);

$fb   = new Facebook_API($app["appid"], $app["appsecret"]);
$redi = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

$re = substr($redi, 0, strpos($redi, '?'));

if ($re == NULL) {
    $redi = $redi;
} else {
    $redi = $re;
}

if (!isset($_REQUEST['code'])) {
    header("location: https://www.facebook.com/dialog/oauth?client_id=" . $app['appid'] . "&redirect_uri=" . $redi . "&scope=" . $app['permissions'] . "&response_type=code");
    exit;
}

$h = "https://graph.facebook.com/oauth/access_token?client_id=" . $app['appid'] . "&redirect_uri=" . $redi . "&client_secret=" . $app['appsecret'] . "&code=" . $_REQUEST['code'];
$a = json_decode(file_get_contents($h), true);
$token = $a["access_token"];
$fb -> access_token = "access_token=" .$token;
$me   = $fb->get("me",true);
$uid  = $me["id"];
$name = $me["name"];
$_SESSION["uid"] = $uid;
setcookie("uid", $uid, time() + (86400 * 30), "/");

//INSERT INTO `fb_user`(`sno`, `uid`, `name`, `token`, `adaccount`, `pageID`, `url`, `active`, `last_update`, `date_created`)
$check = mysqli_query($conn,"SELECT * FROM fb_user WHERE uid = '$uid'");
if(mysqli_num_rows($check) > 0){
    sql("UPDATE fb_user SET 'token'='$token', 'last_update' = '$datetime' WHERE uid = '$uid'");
    header("location: index.php");
}
else{
    $query = sprintf("INSERT INTO fb_user (`sno`, `uid`, `name`, `token`,  `active`, `last_update`, `date_created`) 
        VALUES(NULL,'%s','%s','%s','%s','%s','%s')", $uid,$name,$token,'1',$datetime,$datetime);
    sql($query);
    header("location: settings.php");
}
exit(0);
?>