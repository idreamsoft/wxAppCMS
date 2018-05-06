<?php
class user_WX {
	public $appid  = '';
	public $appkey = '';
	public $scope  = "snsapi_login";
	public $openid = '';
	public $url    = '';

	public function login(){
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::set_cookie("WX_STATE",auth_encode($state));
	    $login_url = "https://open.weixin.qq.com/connect/qrconnect?response_type=code"
	        . "&appid=" . $this->appid
	        . "&redirect_uri=" . urlencode($this->url)
	        . "&state=" .$state
	        . "&scope=".$this->scope;
	    header("Location:$login_url");
	}
	public function callback(){
		$state	= auth_decode(iPHP::get_cookie("WX_STATE"));
		if($_GET['state']!=$state && empty($_GET['code'])){
			$this->login();
			exit;
		}

        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?grant_type=authorization_code&"
            . "appid=" . $this->appid
            . "&secret=" . $this->appkey
            . "&code=" . $_GET["code"];

        $response = $this->get_url_contents($token_url);
        $token    = json_decode($response, true);
		if ( is_array($token) && !isset($token['errcode']) ) {
			iPHP::set_cookie("WX_ACCESS_TOKEN",	auth_encode($token['access_token']));
	    	iPHP::set_cookie("WX_REFRESH_TOKEN",auth_encode($token['refresh_token']));
		    iPHP::set_cookie("WX_OPENID",		auth_encode($token['openid']));
		    $this->openid = $token['openid'];
		} else {
			$this->login();
			exit;
		}
	}
	public function get_openid(){
		$this->openid  = auth_decode(iPHP::get_cookie("WX_OPENID"));
		return $this->openid;
	}
	public function get_user_info(){
		$access_token  = auth_decode(iPHP::get_cookie("WX_ACCESS_TOKEN"));
		$openid        = auth_decode(iPHP::get_cookie("WX_OPENID"));
		$get_user_info = "https://api.weixin.qq.com/sns/userinfo?"
	        . "access_token=" . $access_token
	        . "&openid=" .$openid;

		$info = $this->get_url_contents($get_user_info);
		$arr  = json_decode($info, true);
		$arr['avatar'] = $arr['headimgurl'];
		$arr['gender'] = $arr['sex'];
	    return $arr;
	}
	public function cleancookie(){
		iPHP::set_cookie('WX_ACCESS_TOKEN', '',-31536000);
		iPHP::set_cookie('WX_OPENID', '',-31536000);
		iPHP::set_cookie('WX_STATE', '',-31536000);
	}
	public function get_url_contents($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response =  curl_exec($ch);
        curl_close($ch);
        //-------请求为空
        if(empty($response)){
            die("可能是服务器无法请求https协议</h2>可能未开启curl支持,请尝试开启curl支持，重启web服务器，如果问题仍未解决，请联系我们");
        }
	    return $response;
	}
}
