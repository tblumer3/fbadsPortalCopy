<?php
include __DIR__ . "/config.php";

$uid    =  $_SESSION["uid"] ?? $_COOKIE["uid"];

if(empty($uid)){
    header("location: login.php");
    exit(0);
}
$getUser = mysqli_query($conn,"SELECT * FROM fb_user WHERE uid='$uid'");
$user = mysqli_fetch_assoc($getUser);
$token = $user["token"];

if(isset($_POST['getSuggestions'])) {
    $searchText = $_POST['getSuggestions'];
    $interestParam = array(
        'q'=> $searchText,
        'type'=> 'adinterest'
    );
    $fbinterets = get("/search?".http_build_query($interestParam),true);
    echo json_encode($fbinterets);
    return;
}

$getLast = mysqli_query($conn,"SELECT * FROM fb_ads WHERE uid='$uid' ORDER BY sno DESC LIMIT 1");
$row = mysqli_fetch_assoc($getLast);

$list ="Australia
Austria
Belgium
Brazil
Canada
Croatia
Denmark
Estonia
Finland
France
Germany
Gibraltar
Great Britian
Greece
Hong Kong
Hungary
Ireland
Israel
Italy
Japan
Latvia
Lithuania
Luxembourg
Malaysia
Malta
Mexico
Netherlands
New Zealand
Norway
Poland
Portugal
Russia
Saudi Arabia
Singapore
Spain
South Korea
Sweden
Switzerland
Thailand
Turkey
Ukraine
United Kingdom
United States
Vietnam";
$ePacket = explode("\n",$list);

//Get Country List
$countrySql  = mysqli_query($conn, "SELECT * FROM countries");
while ($country     = mysqli_fetch_assoc($countrySql)) {
    if(!in_array($country["country"],$ePacket)) continue;
    $check       = (in_array($country["code"], json_decode($row["countries"], true))) ? "selected" : null;
    $countryList .= sprintf('<option value="%s" %s>%s - %s</option>', $country["code"], $check, $country["country"], $country["code"]);
}

$me     = get("me?",true);
function check($term, $condn, $inputtype) {
    global $row;
    if ($row[$term] == $condn) {
        echo ($inputtype == "radio") ? "checked" : "selected";
    }
}

function checkArr($term, $condn) {
    global $row;
    if (in_array($condn, json_decode($row[$term], true))) echo "selected";
}

$alert= '';
if (isset($_POST['createAd'])) {
    date_default_timezone_set('Asia/Calcutta'); 
    $start_time = date('Y-m-d 00:00:0', strtotime(' +1 day'));
    $campaign_name = $_POST["campaign_name"];
    $headline      = $_POST["headline"];
    $ptext         = $_POST["ptext"];
    $description   = $_POST["description"];
    $gender         = $_POST["gender"];
    $videoURL       = $_POST["videoURL"];
    $interests      = json_encode($_POST["interests"]);
    $countries     = json_encode($_POST["country"]);
//INSERT INTO `fb_ads`(`sno`, `uid`, `campaign_name`, `interests`, `gender`, `headline`, `ptext`, `description`, `videoURL`, `start_time`, `crID`, `adsetID`, `adID`, `campaignID`, `result`, `result_status`, `date_created`, `misc`, `misc2`)
    $insert = sprintf("INSERT INTO fb_ads (`sno`, `uid`, `campaign_name`, `interests`, `gender`, `headline`, `ptext`, `description`, `videoURL`, `countries`, `start_time`, `result`,`date_created`) VALUES (NULL,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','0','%s')",
$uid,$campaign_name,$interests ,$gender ,$headline,$ptext ,$description ,$videoURL,$countries,$start_time,$datetime);
    $up = sql($insert);
    if(is_numeric($up)){
                $alert=  '<div class="alert alert-success alert-dismissible fade in">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> 
                        Request added <strong>successfully</strong>. Check <a href="ads.php"><strong>My Ads</strong></a>.
                    </div>';
            } 
    else{
        $alert=  '<div class="alert alert-danger alert-dismissible fade in">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> 
                        '.$up.'
                    </div>';
    }
}
include __DIR__ . "/header.php";   
?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css" />
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/lodash@4.17.15/lodash.min.js"></script>
<script src="auto-complete.js"></script>
<title>FB Ads Portal</title>
<div class="container">
    <div class="row">
        <div class="text-center">
            Welcome <b> <?=$me["name"];?> </b>
        </div>
        <br>
         
        <div class="col-md-6 col-md-offset-3">
            <?=$alert;?>
            <div class="well well-sm">
                <form method='POST' action='index.php' class="form-horizontal">
                    <fieldset>
                        <legend class="text-center">Ads Launcher Settings</legend>
                        <div class="form-group">
                            <label class="col-md-5 control-label" for="">Campaign Name </label>
                            <div class="col-md-6">
                                <input name="campaign_name" type="text" placeholder="Enter the Product name"  value="<?=$row['campaign_name']; ?>" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label" for="">Interests</label>
                            <div class="col-md-6">
                                <input id="interestsText" name="interests" type="text" placeholder="Enter the Interests" class="mb-2 form-control" required>

                                <div id="load">
                                    <div class="loader"></div>
                                </div>

                                <select id="interestsResult" class="selectpicker form-control" data-show-subtext="true" data-live-search="true" data-actions-box="true" data-select-all-text="Select All" data-deselect-all-text="Deselect all" title="Select Interests" multiple="multiple" name="interests[]" required></select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label" for="type">Gender</label>
                            <div class="col-md-6">
                                <select name="gender" class="form-control" required>
                                    <option value="0">Both</option>
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label">Headline </label>
                            <div class="col-md-6">
                                <input type="text" name="headline" placeholder="Enter your Headline" value="<?=$row['headline']; ?>" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label">Primary Text </label>
                            <div class="col-md-6">
                                <input type="text" name="ptext" placeholder="Enter your Primary Text" value="<?=$row['ptext']; ?>" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label">Description </label>
                            <div class="col-md-6">
                                <input type="text" name="description" placeholder="Enter your Description" value="<?=$row['description']; ?>" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label">Video URL </label>
                            <div class="col-md-6">
                                <input type="text" name="videoURL" placeholder="Enter a downloadable link" value="<?=$row['videoURL']; ?>" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-5 control-label" for="type">Target Country</label>
                            <div class="col-md-6">
                                <select class="selectpicker form-control" data-show-subtext="true" data-live-search="true" data-actions-box="true" data-select-all-text="Select All" data-deselect-all-text="Deselect all" title="Select Countries" multiple="multiple" name="country[]" required>
                                    <?=$countryList; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12 text-center">
                                <button type="submit" name="createAd" class="btn btn-primary">Create Ad</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>