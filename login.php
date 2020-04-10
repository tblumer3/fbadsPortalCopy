<?php
include __DIR__ . "/config.php";
include __DIR__ . "/header.php";

if(isset($_GET["logout"]) == 1){
	$_SESSION["uid"] = "";
	session_destroy();
    setcookie("uid", "", time() - 3600, "/");
}
if(!empty($_SESSION["uid"]) && !empty($_COOKIE["uid"])){
	header("location: index.php");
	exit(0);
}
?>
<title>Login | FB Ads Portal</title>
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="well well-sm">
                <legend class="text-center">Welcome to Ads Portal</legend>
                <div class="text-center">
                    Please login and authenticate to continue.
                    <br><br>
                    <a href="facebook.php" class="btn btn-primary">Login with Facebook</a> 
                    <br><br>
                </div>
            </div>
        </div>
    </div>
</div>