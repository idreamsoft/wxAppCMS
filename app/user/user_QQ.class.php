<?php
class user_QQ {
    public $appid  = '';
    public $appkey = '';
    public $openid = '';
    public $url    = '';
    public $scope  = "get_user_info,add_topic,add_one_blog,add_album,upload_pic,list_album,add_share,check_page_fans,do_like,get_tenpay_address,get_info,get_other_info,get_fanslist,get_idolist,add_idol";
    public $access_token  = '';

	public function login(){
        $this->cleancookie();
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::set_cookie("QQ_STATE",auth_encode($state));
	    $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code"
	        . "&client_id=" . $this->appid
	        . "&redirect_uri=" . urlencode($this->url)
	        . "&state=" .$state
	        . "&scope=".$this->scope;
	    header("Location:$login_url");
        exit;
	}
	public function callback(){
		if(empty($_GET['state']) || empty($_GET['code'])){
			$this->login();
		}
        if($_GET['state']){
            $state  = auth_decode(iPHP::get_cookie("QQ_STATE"));
            if($_GET['state']!=$state){
                $this->login();
            }
        }

        $url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code"
            . "&client_id=" . $this->appid
            . "&redirect_uri=" . urlencode($this->url)
            . "&client_secret=" . $this->appkey
            . "&code=" . $_GET["code"];

        $response = $this->get_url_contents($url);

        if (strpos($response, "callback") !== false){
			$lpos     = strpos($response, "(");
			$rpos     = strrpos($response, ")");
			$response = substr($response, $lpos + 1, $rpos - $lpos -1);
			$msg      = json_decode($response);
            if(isset($msg->error)){
                // $this->cleancookie();
                // trigger_error($msg->error_description,E_USER_ERROR);
                $this->login();
            }
        }
        $params = array();
        parse_str($response, $params);
        $this->access_token = $params["access_token"];
		iPHP::set_cookie("QQ_ACCESS_TOKEN",auth_encode($params["access_token"]));

        $this->openid($params["access_token"]);
	}
	public function openid($access_token=""){
	    $url = "https://graph.qq.com/oauth2.0/me?access_token=".$access_token;
	    $response  = $this->get_url_contents($url);
	    if (strpos($response, "callback") !== false){
	        $lpos = strpos($response, "(");
	        $rpos = strrpos($response, ")");
	        $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
	    }

        $me = json_decode($response);
        if(isset($me->error)){
            // trigger_error($me->error_description,E_USER_ERROR);
            $this->login();
        }

		$this->openid = $me->openid;
	    iPHP::set_cookie("QQ_OPENID",auth_encode($me->openid));
	}

	public function get_openid(){
		$this->openid  = auth_decode(iPHP::get_cookie("QQ_OPENID"));
		return $this->openid;
	}
    public function get_token(){
        $this->access_token  = auth_decode(iPHP::get_cookie("QQ_ACCESS_TOKEN"));
        return $this->access_token;
    }
	public function get_user_info(){
		$url = "https://graph.qq.com/user/get_user_info?"
	        . "access_token=" . $this->access_token
	        . "&oauth_consumer_key=" .$this->appid
	        . "&openid=" .$this->openid
	        . "&format=json";

	    $info = $this->get_url_contents($url);
	    $arr = json_decode($info, true);
        if($arr['ret']!='0'){
            var_dump($url);
            trigger_error($arr['msg'],E_USER_ERROR);
        }

        $arr['avatar'] = $arr['figureurl_2'];
        $arr['gender'] = $arr['gender']=="??"?'1':0;
	    return $arr;
	}
	public function cleancookie(){
		iPHP::set_cookie('QQ_ACCESS_TOKEN', '',-31536000);
		iPHP::set_cookie('QQ_OPENID', '',-31536000);
		iPHP::set_cookie('QQ_STATE', '',-31536000);
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
