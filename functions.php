<?php
$baseUrl  = "https://graph.facebook.com/v6.0/";
$pubtoken = "EAABsbCS1iHgBAO6yGg4vZBA8trGJNM4X7POWtNuODGLQ5agHDvoNnIZA8I1gmRF6EmWkqDVACemCIhKEANOnOwiMaZCEtHBu0bH2yjrqGnN07YUozrOfi8eaqlGBttYQKBiz3ZCySeOJYnp5eQvQlOJ54aKHkIBgaA3P0JIUJHmH8BZBB0ds5";

function sql($query){
    global $conn;
    mysqli_query($conn,$query);
    if(mysqli_error($conn))
        return sprintf("\n<b>Error in Adding in Database</b> : %s <br><b>Query</b> : %s", mysqli_error($conn),$query);
    else
        return mysqli_insert_id($conn);
}

function api($param, $end_point) {
    global $baseUrl, $token;
    $param["access_token"] = $token;
    $postData = http_build_query($param);
    $ch       = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $end_point);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($output, true);
    if (array_key_exists("error", $result)) {
        print_r($param);
        $errMsg = $result["error"]["error_user_msg"] ?? $result["error"]["message"];
        printf("<br><b>[Error Creating %s]</b><br><b>Message</b> : %s<br>", $end_point, $errMsg);
    }
    return $result;
}

function get($url, $method) {
    if ($method === TRUE) {
        global $baseUrl, $token;
        $paramUrl = $baseUrl . $url . "&access_token=". $token;
    }
    else if ($method === FALSE){
        $paramUrl = $url;
    }
    else{
        global $baseUrl,$pubtoken;
        $paramUrl = $baseUrl . $url . "&access_token=". $pubtoken;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paramUrl);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = json_decode(curl_exec($ch) , true);
    curl_close($ch);
    if (array_key_exists("error", $result)) {
        $errMsg = $result["error"]["error_user_msg"] ?? $result["error"]["message"];
        printf("<b>Message</b> : %s<br>", $errMsg);
    }
    return $result;
}

function loop($query) {
    $api  = get($query, true);
    $data = array();
    while (array_key_exists("next", $api["paging"]) || !empty($api["data"])) {
        if (count($api["data"]) > 0) 
            $data = array_merge($data, $api["data"]);
        $api  = get($api["paging"]["next"], false);
    }
    return $data;
}

function miniloop($query){
    $api  = get($query, true);
    $data = array();
    $i=1;
    while (array_key_exists("next", $api["paging"]) || !empty($api["data"])) {
        if (count($api["data"]) > 0) 
            $data = array_merge($data, $api["data"]);
        $api  = get($api["paging"]["next"], false);
        $i++;
        if($i == 5) break;
    }
    return $data;
}
