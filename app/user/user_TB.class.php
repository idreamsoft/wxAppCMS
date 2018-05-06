<?php
class user_TB {
	public $appid  = '';
	public $appkey = '';
	public $scope  = "promotion,item,usergrade";
	public $openid = '';
	public $url    = "";
	public $info   = '';

	public function login(){
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::set_cookie("TB_STATE",auth_encode($state));
	    $login_url = "https://oauth.taobao.com/authorize?response_type=code&client_id="
	        . $this->appid . "&redirect_uri=" . urlencode(CALLBACK_URL.$this->callback)
	        . "&state=" .$state
	        . "&scope=".$this->scope;
	    header("Location:$login_url");
	}
	public function callback(){
		$state	= auth_decode(iPHP::get_cookie("TB_STATE"));
		if($_GET['state']!=$state && empty($_GET['code'])){
			$this->login();
			exit;
		}

        $POST_FIELDS = "grant_type=authorization_code&"
            . "client_id=" . $this->appid. "&redirect_uri=" . urlencode(CALLBACK_URL.$this->callback)
            . "&client_secret=" . $this->appkey. "&code=" . $_GET["code"];

        $response	= $this->postUrl('https://oauth.taobao.com/token',$POST_FIELDS);
	    $this->info	= json_decode($response, true);
	    if($this->info['error']){
			$this->login();
			exit;
	    }
	    $this->openid	= $this->info['taobao_user_id'];
	    iPHP::set_cookie("TB_OPENID",$this->openid);
	}
	public function get_openid(){
		$this->openid  = auth_decode(iPHP::get_cookie("TB_OPENID"));
		return $this->openid;
	}
	public function get_user_info(){
		$user['nickname'] =$this->info['taobao_user_nick'];
		$user['gender']   =0; //$user['gender']=="??"?'1':0;
		$user['avatar']   =''; //$user['figureurl_2'];
		return $user;
	}
	public function cleancookie(){
		iPHP::set_cookie('TB_STATE', '',-31536000);
		iPHP::set_cookie('TB_OPENID', '',-31536000);
	}

	public function postUrl($url, $POSTFIELDS) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
	    $res = curl_exec ($ch);
	    curl_close ($ch);
	    return $res;
	}
}
