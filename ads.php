<?php
include __DIR__ . "/config.php";
include __DIR__ . "/header.php";

$uid = $_SESSION["uid"];

if (empty($uid))
{
    header("location: login.php");
    exit(0);
}
$getUser = mysqli_query($conn, "SELECT * FROM fb_user WHERE uid='$uid'");
$user = mysqli_fetch_assoc($getUser);
$token = $user["token"];

$gender = array(
    "0" => "Both",
    "1" => "Male",
    "2" => "Female"
);
$getLast = mysqli_query($conn, "SELECT * FROM fb_ads WHERE uid='$uid' ORDER BY sno DESC");
while ($r = mysqli_fetch_assoc($getLast))
{
	if ($r["result"] == 404) $color  = "red";
    else if ($r["result"]) $color  = "green";
    else $color  = "orange";
    $countries = implode(", ", json_decode($r["countries"], true));
    $data .= sprintf("
                <tr style='color:%s'>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>
                    	<details> 
                    		<b>Headline</b> : %s <br>
                    		<b>Primarty</b> Text : %s<br>
                    		<b>Description</b> : %s<br>
                    	</details>
                    </td>
                    <td><a href='%s' >%s</a></td>
                    <td>%s</td>
                    <td><details>%s</details></td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td><a href='execute.php?run=%s' class='btn btn-success btn-sm'>Run</a></td>
                </tr>", $color, $r["sno"], $r["campaign_name"], $r["interests"], $gender[$r["gender"]], $r["headline"], $r["description"], $r["ptext"], $r["videoURL"], $r["videoURL"],$countries, json_decode($r["result_status"], true) [0]["error"]["error_user_msg"], $r["adID"], $r["adsetID"], $r["campaignID"], $r["date_created"],$r["sno"]);
}
?>
<style>
th:nth-child(3),td:nth-child(3) {
  display: none;
}
</style>
<title>My Ads</title>
<div class="container-fluid">
	<div class='well well-sm'>    
		<legend class="text-center">My Ads</legend>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:3%">SNo</th>
                        <th>C Name</th>
                        <th>Interests</th>
                        <th>Gender</th>
                        <th>Ad Creative</th>
                        <th>Video URL</th>
                        <th>Country</th>
                        <th>Result</th>
                        <th>Ad ID</th>
                        <th>AdSet ID</th>
                        <th>Campaign ID</th>
                        <th>Created Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <?=$data;?>
            </table>
        </div>
    </div>
</div>

