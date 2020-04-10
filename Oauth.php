<?php
class Facebook_API{
    public $client_id;
    public $client_secret;
    public $access_token;
    public $redirect;
    public $proxy = false;
    function __construct($client_id, $client_secret){
        $this -> client_id = $client_id;
        $this -> client_secret = $client_secret;
    }
    public function ajax($url, $post = false){    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        if($this -> proxy){
            curl_setopt($ch, CURLOPT_PROXY, $this -> proxy);
        }
        if($post){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }	  
        $rr = curl_exec($ch);
        curl_close($ch);
        return $rr;
    }
    public function active_token(){
        $fo = $this -> get("me/friends", true);
        return (isset($fo['error'])) ? false : true;
    }
    public function app_access_token(){
        
        return $this -> ajax("https://graph.facebook.com/oauth/access_token?client_id=" . $this -> client_id . "&client_secret=" . $this -> client_secret . "&grant_type=client_credentials");
    }
    public function init($callback, $scope = false, $display = "page"){
        if(isset($_COOKIE['ac'])){
            $this -> access_token(false, false, $_COOKIE['ac']);
        }else{
            if(!isset($_GET['code'])){
                $this -> authorize($callback, $scope, $display);
            }else{
                setcookie("ac", $this -> access_token($_GET['code'], $callback), mktime(0, 0, 0, 12, 31, 2015));
                header("Location: " . $_SERVER['PHP_SELF']);
            }
        }
    }
    public function access_token($code = false, $redirect = false, $access = false, $sc = false){
        if(!$code && $access) return $this -> access_token = $access;
        $acx = $this -> ajax("https://graph.facebook.com/oauth/access_token?client_id=" . $this -> client_id . "&client_secret=" . $this -> client_secret . "&redirect_uri=" . $redirect . "&code=" . $code);
        if(preg_match("/access_token=/", $acx, $ai)){
            $this -> access_token = $acx;
            return $acx;
        }else{
            $this -> authorize($redirect, $sc, "page");
        }
    }
    public function create_user($scope){
        return $this -> go("https://graph.facebook.com/" . $this -> client_id . "/accounts/test-users?installed=true&name=Barney Stinson&permissions=$scope&method=post&access_token=" . $this -> client_id . "|" . $this -> client_secret);
    }
    public function authorize($redirect, $scope = false, $display = "page"){
        if($scope){
            $ret = ("https://www.facebook.com/dialog/oauth?client_id=" . $this -> client_id . "&scope=" . $scope . "&redirect_uri=" . $redirect . "&display=" . $display);
        }else{
            $ret = ("https://www.facebook.com/dialog/oauth?client_id=" . $this -> client_id . "&redirect_uri=" . $redirect . "&display=" . $display);
        }
        $this -> go($ret);
    }
    public function set_proxy($fname){
        $ex = explode("\n", file_get_contents($fname));
        $this -> proxy = $ex[rand(0, count($ex) - 1)];
    }
    public function go($url){
        echo "<script>window.top.location = \"$url\";</script>";
//          header("Location: $url");
    }
    public function get($n, $arr = false, $post = false){
        return json_decode($this -> ajax("https://graph.facebook.com/$n?" . $this -> access_token, $post), $arr);
    }
    public function fql($n, $arr = false){
        return json_decode($this -> ajax("https://graph.facebook.com/fql?q=" . str_replace(" ", "+", $n) . "&" . $this -> access_token, false), $arr);
    }
    public function pget($n, $arr = false, $post = false){
        return json_decode($this -> ajax("https://graph.facebook.com/$n&" . $this -> access_token, $post), $arr);
    }
    public function fget($n, $arr = false, $post = false){
        return json_decode($this -> ajax($n, $post), $arr);
    }
    public function rest($n, $json = false, $get = false){
        return $this -> ajax("https://api.facebook.com/method/$n?" . $this -> access_token . ($get ? "&" . $get : "") . ($json ? "&format=json" : ""), false);
    }
}
?>