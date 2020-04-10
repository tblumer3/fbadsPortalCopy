<?php
include __DIR__ . "/config.php";
echo "<pre>";

$delete         = array(
    'method' => "DELETE"
);

if (isset($_GET["run"])) {
    $run            = $_GET["run"];
    $data           = mysqli_query($conn, "SELECT * FROM fb_ads WHERE sno='$run'"); //success='0'
}
else {
    $data           = mysqli_query($conn, "SELECT * FROM fb_ads WHERE result='0' LIMIT 1");
}
while ($r           = mysqli_fetch_assoc($data)) {
    $errors         = array();
    $sno            = $r["sno"];
    $uid            = $r["uid"];
    $getUser        = mysqli_query($conn, "SELECT * FROM fb_user WHERE uid='$uid'");
    $user           = mysqli_fetch_assoc($getUser);
    $token          = $user["token"];
    $accountID      = $user["adaccount"];
    $pageID         = $user["pageID"];
    $url            = $user["url"];

    //Campaign
    $campaign_name  = $r["campaign_name"];
    $campstatus     = "PAUSED";
    $objective      = "CONVERSIONS";

    //Ad Creative
    $headline       = $r["headline"];
    $message        = $r["ptext"];
    $description    = $r["description"];
    $videoURL       = $r["videoURL"];

    //AdSet
    $adset_name     = "Test";
    $gender         = $r["gender"];
    $countries      = $r["countries"];
    $status         = "ACTIVE";
    $start_time     = $r["start_time"];
    $pixelID        = 12345;
/*
    $creative       = array(
                        'name'                  => "My Creative_".mt_rand(111,9999),
                        'object_story_spec'     => array(
                          'link_data'           => array(
                            'link'              => $url,
                            'name'              => $headline,
                            'picture'           => $image,
                            'message'           => $message,
                            'description'       => $description,
                            'attachment_style'  => "link",
                            'call_to_action'    => array( 
                            	'type' => "SHOP_NOW"
                            	),
                            ),
                            'page_id'           => $pageID
                        )
                    );
    //print_r($creative);
    $makeCreative   = api($creative, $accountID . "/adcreatives");
    if (array_key_exists("id", $makeCreative)) {
        $creativeID     = $makeCreative["id"];
*/
        $campaign       = array(
            'name'                  => $campaign_name,
            'objective'             => $objective,
            'status'                => $campstatus,
            'special_ad_category'   => "NONE"
        );
        $createCampaign = api($campaign, $accountID . "/campaigns");
        if (array_key_exists("id", $createCampaign)) {
        	$campaignID     = $createCampaign["id"];
        	$targeting      = array(
    		"age_min"       => "21",
    		"age_max"       => "45",
   			"genders" 		=> array($gender),
    		"geo_locations" => array(
        		"countries" => $countries,
        		"location_types" => array(
            		"home"
        			)
    			),
    		"locales"				=> [24,6],
    		"publisher_platforms"   => ["facebook","instagram"],
    		"facebook_positions"    => ["feed","video_feeds"],
    		"instagram_positions"   => ["stream"],
    		"device_platforms"      => ["mobile", "desktop"],
    		"targeting_optimization"=> "none"
    		);

        	$adset         		= array(
                        'name'                => $adset_name,
                        'optimization_goal'   => "OFFSITE_CONVERSIONS",
                        'billing_event'       => "IMPRESSIONS",
                        'campaign_id'         => $campaignID,
                        'status'              => "ACTIVE",
                        'targeting'           => $targeting,
                        'daily_budget'		  => 10000,
                        'bid_strategy'		  => "LOWEST_COST_WITHOUT_CAP",
                        'pacing_type'		  => ["standard"],
                        'destination_type'	  => "WEBSITE",
                        'start_time'		  => $start_time
                    );

            //change it ==
            if ($objective == "CONVERSIONS") {
                        $adset['promoted_object'] = [
                            "pixel_id"            => $pixelID, 
                            "custom_event_type"   => "PURCHASE"
                        ];
                    }
                       

            $createAdset    = api($adset, $accountID . "/adsets");
            if (array_key_exists("id", $createAdset)) {
                $adsetID        = $createAdset["id"];
                $ads            = array(
                    'name'                => $adset_name,
                    'adset_id'            => $adsetID,
                    'status'              => $status,
                    'creative'            => array(
                        'creative_id'     => $creativeID
                    )
                );
                if ($pixelID != "none") {
                    $ads["tracking_specs"]            = array(
                        array(
                            "action.type"             => ["offsite_conversion"],
                            "fb_pixel"                => [$pixelID]
                        )
                    );
                }

                $ad             = api($ads, $accountID . "/ads");
                if (array_key_exists("id", $ad)) {
                    echo "Success\n";
                    $adID = $ad["id"];
                    mysqli_query($conn, "UPDATE fb_ads SET result='1', date_created='$datetime', crID = '$creativeID', adID = '$adID', adsetID ='$adsetID', campaignID='$campaignID' WHERE sno = '$sno'");
                    echo mysqli_error($conn);
                    //break;
                    
                }
                else {
                    //Error in Creating Ad
                    array_push($errors, $ad);
                    api($delete, $creativeID);
                    api($delete, $adsetID);
                }
            }
            else {
                //Error in Creating Adset
                array_push($errors, $createAdset);
                //api($delete, $campaignID); //Creative tha YAHA
            }
        }
        else {
            //Error in Creating Campaign
            array_push($errors, $createCampaign);
        }
    }
    /*
    else {
        //Error in Creating Creative.
        array_push($errors, $makeCreative);
    }
    */
    if (!empty($errors)) {
        $code = 404;
        if ($errors[0]["error"]["code"] == 80004) $code = 0;
        print_r($errors);
        mysqli_query($conn, "UPDATE fb_ads SET result='$code', result ='" . json_encode($errors) . "', date_created='" . date('Y-m-d H:i:s') . "',crID = '$creativeID', adID = '$adID', adsetID ='$adsetID', campaignID='$campaignID' WHERE sno = '$sno'");
    }
//}
else {
   // mysqli_query($conn, "UPDATE fb_ads SET result='1' WHERE sno = '$sno'");
}
//}
//23844772624680579?fields= bid_strategy, billing_event,targeting, instagram_actor_id,adcreatives{object_story_spec}, destination_type, attribution_spec, start_time
