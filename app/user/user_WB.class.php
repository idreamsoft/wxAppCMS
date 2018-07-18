<?php
class user_WB {
	public $appid  = '';
	public $appkey = '';
	public $scope  = "promotion,item,usergrade";
	public $openid = '';
	public $url    = '';
	private static $info  = '';


	public function login(){
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::set_cookie("WB_STATE",auth_encode($state));
	    $login_url = "https://api.weibo.com/oauth2/authorize?response_type=code&client_id="
	        . $this->appid . "&redirect_uri=" . urlencode($this->url)
	        . "&state=" .$state
	        . "&scope=".$this->scope;
		header("Location:$login_url");
	}
	public function callback(){
		$state	= auth_decode(iPHP::get_cookie("WB_STATE"));
		if($_GET['state']!=$state && empty($_GET['code'])){
			$this->login();
			exit;
		}

        $POST_FIELDS = "grant_type=authorization_code&"
            . "client_id=" . $this->appid. "&redirect_uri=" . urlencode($this->url)
            . "&client_secret=" . $this->appkey. "&code=" . $_GET["code"];

		$response = $this->postUrl('https://api.weibo.com/oauth2/access_token',$POST_FIELDS);
		$token    = json_decode($response, true);
		if ( is_array($token) && !isset($token['error']) ) {
			iPHP::set_cookie("WB_ACCESS_TOKEN",	auth_encode($token['access_token']));
	    	iPHP::set_cookie("WB_REFRESH_TOKEN",auth_encode($token['refresh_token']));
		    iPHP::set_cookie("WB_OPENID",		auth_encode($token['uid']));
		    $this->openid = $token['uid'];
		} else {
			$this->login();
			exit;
		}
	}
	public function get_openid(){
		$this->openid  = auth_decode(iPHP::get_cookie("WB_OPENID"));
		return $this->openid;
	}
	public function get_user_info(){
		$access_token  = auth_decode(iPHP::get_cookie("WB_ACCESS_TOKEN"));
		$refresh_token = auth_decode(iPHP::get_cookie("WB_REFRESH_TOKEN"));
		$this->openid  = auth_decode(iPHP::get_cookie("WB_OPENID"));
		$url  = "https://api.weibo.com/2/users/show.json?uid=".$this->openid;
		$info = $this->get_url_contents($url,$access_token);
		$arr  = json_decode($info, true);
		$arr['nickname'] = $arr['screen_name'];
		$arr['avatar']   = $arr['avatar_large'];
		$arr['gender']   = $arr['gender']=="m"?'1':'0';
	    return $arr;
	}
	public function cleancookie(){
		iPHP::set_cookie('WB_ACCESS_TOKEN', '',-31536000);
		iPHP::set_cookie('WB_REFRESH_TOKEN', '',-31536000);
		iPHP::set_cookie('WB_OPENID', '',-31536000);
		iPHP::set_cookie('WB_STATE', '',-31536000);
	}
	public function get_url_contents($url,$access_token=""){
		$headers[] = "Authorization: OAuth2 ".$access_token;
		$headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Sae T OAuth2 v0.1');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
	    curl_setopt($ch, CURLOPT_URL, $url);
	    $result =  curl_exec($ch);
	    curl_close($ch);
	    return $result;
	}

	public function postUrl($url, $POSTFIELDS) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Sae T OAuth2 v0.1');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
		$res  = curl_exec ($ch);
		//$info = curl_getinfo($ch);
	    curl_close ($ch);
	    return $res;
	}
}
