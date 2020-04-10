<?php
include __DIR__ . "/config.php";
include __DIR__ . "/header.php";
$uid =  $_SESSION["uid"] ?? $_COOKIE["uid"];

if(empty($uid)){
    header("location: login.php");
    exit(0);
}

if (isset($_POST['update'])) {
    $accountID    = $_POST["accountID"];
    $pageID       = $_POST["pageID"];
    $url      = $_POST["url"];
    $success = mysqli_query($conn,"UPDATE fb_user SET url = '$url', pageID = '$pageID', adaccount= '$accountID' WHERE uid = '$uid'");
    echo mysqli_error($conn);
}

$getUser = mysqli_query($conn,"SELECT * FROM fb_user WHERE uid='$uid'");
$user = mysqli_fetch_assoc($getUser);
$token = $user["token"];

//Get ad accounts
$adaccs = "";
$adaccsData = loop("me/adaccounts?fields=name,account_status&limit=100");
foreach ($adaccsData as $adacc) {
    if ($adacc["account_status"] == 2) continue;
    $adid = $adacc["id"];
    $check = (strcmp($adid,$user["adaccount"]) == 0 ) ? "selected" : null;
    $adaccs .= sprintf('<option value="%s" %s>%s</option>
        ', $adid, $check, $adacc["name"]);
}

//Get pages
$pages  = "";
$pageData = loop("me/accounts?limit=100");
foreach ($pageData as $page) {
    $pageid   = $page["id"];
    $check    = ($pageid == $user["pageID"]) ? "selected" : null;
    $pages .= sprintf('<option value="%lu" %s>%s</option>
        ', $pageid, $check, $page["name"]);
}


?>
<title>Settings | FB Ads Portal</title>
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <? if($success == 1){
                echo '<div class="alert alert-success alert-dismissible fade in">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> 
                        Settings <strong>successfully</strong> updated. <a href="index.php"><strong>Create Ads</strong></a> Now!
                    </div>';
            } ?>
            <div class="well well-sm">
                <form method='POST' class="form-horizontal">
                    <fieldset>
                        <legend class="text-center">Settings</legend>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="type">Ad Account</label>
                            <div class="col-md-9">
                                <select name="accountID" class="form-control" required>
                                    <option value="">Select Ad Account</option>
                                    <?=$adaccs; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label" for="type">Facebook Page</label>
                            <div class="col-md-9">
                                <select name="pageID" class="form-control" required>
                                    <option value="">Select Page</option>
                                    <?=$pages; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Website URL </label>
                            <div class="col-md-9">
                                <input type="text" name="url" placeholder="Enter the product URL" value="<?=$user['url']; ?>" class="form-control" required>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-md-12 text-center">
                                <button type="submit" name="update" class="btn btn-primary">Update Settings</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>