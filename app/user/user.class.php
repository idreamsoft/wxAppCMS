<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
defined('iPHP') OR exit('What are you doing?');

define("USER_AUTHASH",'#=(iCMS@'.iPHP_KEY.'@iCMS)=#');

class user {
	public static $openid     = null;
	public static $userid     = 0;
	public static $username   = '';
	public static $nickname   = '';
	public static $cookietime = 0;
	public static $format     = false;
	public static $callback   = array();//回调
	private static $AUTH      = 'USER_AUTH';

	public static function login_uri($uri=null){
		$login_uri = iURL::router('user:login','?&');
		$uri && $login_uri = str_replace(rtrim(iCMS_URL,'/'),$uri,$login_uri);
		return $login_uri;
	}
	public static function router($uid,$type,$size=0){
	    switch($type){
	        case 'avatar':return iCMS_FS_URL.get_user_pic($uid,$size);break;
	        case 'url':   return iURL::router(array('uid:home',$uid));break;
	        case 'coverpic':
	        	$dir = get_user_dir($uid,'coverpic');
				return array(
					'pc' => iFS::fp($dir.'/'.$uid.".jpg",'+http'),
					'mo' => iFS::fp($dir.'/m_'.$uid.".jpg",'+http')
				);
	        	break;
	        case 'urls':
	            return array(
					'inbox'    => iURL::router(array('user:inbox:uid',$uid)),
					'home'     => iURL::router(array('uid:home',$uid)),
					'comment'  => iURL::router(array('uid:comment',$uid)),
					'favorite' => iURL::router(array('uid:favorit',$uid)),
					'fans'     => iURL::router(array('uid:fans',$uid)),
					'follower' => iURL::router(array('uid:follower',$uid)),
	            );
	        break;
	    }
	}
	public static function empty_info($uid,$name){
        return array(
			'uid'    => $uid,
			'name'   => $name,
			//'inbox'   => 'javascript:;',
			'url'    => 'javascript:;',
			'avatar' => 'about:blank',
			'link'   => '<a href="javascript:;">'.$name.'</a>',
			'at'     => '<a href="javascript:;">'.$name.'</a>',
        );
	}
	public static function info($uid,$name=null,$size=0){
		if(empty($uid)){
			$info = self::empty_info($uid, $name);
		}else{
			$url = self::router($uid,"url");
			if($name===null){
				$name = self::value($uid,'nickname');
			}
			$info = array(
				'uid'    => $uid,
				'name'   => $name,
				//'inbox'  => $urls['inbox'],
				'url'    => $url,
				'avatar' => self::router($uid,"avatar",$size?$size:0),
				'at'     => '<a href="'.$url.'" class="iCMS_user_link" target="_blank" i="ucard:'.$uid.'">@'.$name.'</a>',
				'link'   => '<a href="'.$url.'" class="iCMS_user_link" target="_blank" i="ucard:'.$uid.'">'.$name.'</a>',
			);
		}
		self::$callback['info'] && iPHP::callback(self::$callback['info'],array(&$info));
		return $info;
	}
	public static function value($val,$field='username',$where='uid'){
		$row = iDB::row("SELECT {$field} FROM `#iCMS@__user` where `$where`='{$val}'");
		if(isset($row->setting)){
			$row->setting = (array)json_decode($row->setting,true);
		}
		if(strpos($field, ',') !== false||$field=='*'){
			return $row;
		}
		return $row->$field;
	}
	public static function check($val,$field='username'){
		$uid = self::value($val,'uid',$field);
		return empty($uid)?false:$uid;
	}
	public static function follow($uid=0,$fuid=0){
		if($uid==='all'){ //all fans
			$rs = iDB::all("SELECT `uid`,`name` FROM `#iCMS@__user_follow` where `fuid`='{$fuid}'");
		}
		if($fuid==='all'){ // all follow
			$rs = iDB::all("SELECT `fuid` AS `uid`,`fname` AS `name` FROM `#iCMS@__user_follow` where `uid`='{$uid}'");
		}
		if(isset($rs)){
			foreach ((array)$rs as $key => $value) {
				$follow[$value['uid']] = $value['name'];
			}
			return $follow;
		}
		$fuid = iDB::row("SELECT `fuid` FROM `#iCMS@__user_follow` where `uid`='{$uid}' and `fuid`='$fuid' limit 1");
		return $fuid?$fuid:false;
	}
	public static function update_count($uid=0,$field='article',$math='+',$count='1'){
		$math=='-' && $sql = " AND `{$field}`>0";
		iDB::query("
			UPDATE `#iCMS@__user`
			SET `{$field}` = {$field}{$math}{$count}
			WHERE `uid`='{$uid}' {$sql}
		");
	}

	public static function openid($openid=0,$platform=0,$appid=''){
		return user_openid::uid($openid,$platform,$appid);
	}
	public static function get_cache($uid){
		return iCache::get(iPHP_APP.':user:'.$uid);
	}
	public static function set_cache($uid){
		$user = iDB::row("SELECT * FROM `#iCMS@__user` where `uid`='{$uid}'",ARRAY_A);
		unset($user['password']);
		iCache::set('user/'.$user['uid'],$user,0);
	}
	public static function category($cid=0,$appid=1){
		if(empty($cid)) return false;

		$category = iDB::row("SELECT * FROM `#iCMS@__user_category` where `cid`='".(int)$cid."' AND `appid`='".$appid."' limit 1");
		return (array)$category;
	}
	public static function get($uids=0,$unpass=true,$field='uid'){
		if(empty($uids)) return array();

		list($uids,$is_multi)  = iSQL::multi_var($uids);

    	$sql = iSQL::in($uids,$field,false,true);
		$data = array();
		$rs = iDB::all("SELECT * FROM `#iCMS@__user` where {$sql} AND `status`='1'",OBJECT);
		if($rs){
			$_count = count($rs);
	        for ($i=0; $i < $_count; $i++) {
	        	if($unpass) unset($rs[$i]->password);
	        	$data[$rs[$i]->uid]= self::user_item($rs[$i]);
	        }
	        $is_multi OR $data = $data[$uids];
		}
		if(empty($data)){
			return;
		}
	   	return $data;
	}
    public static function data($uids=null,$uid=0){
    	if(empty($uids)){
    		return;
    	}
		list($uids,$is_multi)  = iSQL::multi_var($uids);
    	$sql = iSQL::in($uids,'uid',false,true);
		$data = array();
		$rs   = iDB::all("SELECT * FROM `#iCMS@__user_data` where {$sql};",OBJECT);
		if($rs){
			if($is_multi){
				$_count = count($rs);
		        for ($i=0; $i < $_count; $i++) {
		        	$data[$rs[$i]->uid]= self::user_item($rs[$i],true);
		        }
		        if($uid){
		        	$data = $data[$uid];
		        }
			}else{
				$data = self::user_item($rs[0],true);
			}
		}
        return $data;
    }
    private static function user_item($rs,$data=false){
    	if(empty($rs)){
    		return;
    	}
    	if($data){
	    	$rs->meta = json_decode($rs->meta,true);
    	}else{
			$rs->setting  = (array)json_decode($rs->setting,true);
			$rs->gender   = $rs->gender?'male':'female';
			$rs->avatar   = self::router($rs->uid,'avatar');
			$rs->urls     = self::router($rs->uid,'urls');
			$rs->coverpic = self::router($rs->uid,'coverpic');
			$rs->url      = $rs->urls['home'];
			$rs->inbox    = $rs->urls['inbox'];
    	}
    	return $rs;
    }
    public static function check_uname_type($uname){
    	return (strpos($uname, '@') === false ? 'nickname' : 'username');
    }
	public static function login($val,$pass='',$field=null){
		$field_map = array('uid','nickname','username');

		$field===null && $field = self::check_uname_type($val);

		if(!in_array($field, $field_map)||empty($field)){
			$field = 'username';
		}

		$user = iDB::row("
			SELECT `uid`,`nickname`,`password`,`username`,`status`
			FROM `#iCMS@__user`
			WHERE `{$field}`='{$val}' AND `password`='$pass'
		");

		if(empty($user)){
			return false;
		}
		if((int)$user->status!=1){
			return $user->status;
		}
		$lastloginip = iPHP::get_ip();

		iDB::update('user',
			array(
				'lastloginip'   => $lastloginip,
				'lastlogintime' => time(),
			),
			array('uid' => $user->uid)
		);

		self::set_cookie($user->username,$user->password,(array)$user);
		self::$userid   = $user->uid;
		self::$nickname = $user->nickname;
		self::$username = $user->username;
		if(self::$callback['set_cookie']){
			iPHP::callback(self::$callback['set_cookie'],array(&$user));
		}
		return true;
	}
	public static function get_cookie($unpw=false) {
		if(self::$callback['cookie']){
			return self::$callback['cookie'];
		}
		$auth     = auth_decode(iPHP::get_cookie(self::$AUTH));
		$userid   = auth_decode(iPHP::get_cookie('userid'));
		$nickname = auth_decode(iPHP::get_cookie('nickname'));

		list($_userid,$_username,$_password,$_nickname) = explode(USER_AUTHASH,$auth);

		if((int)$userid===(int)$_userid && $nickname===$_nickname){
			self::$userid   = (int)$_userid;
			self::$nickname = $_nickname;
			$u = array('uid'=>self::$userid,'userid'=>self::$userid,'nickname'=>self::$nickname);
			if($unpw){
				$u['username'] = $_username;
				$u['password'] = $_password;
			}
			return $u;
		}
		//self::logout();
		return false;
	}
	public static function set_cookie($username,$password,$user){
		iPHP::set_cookie(self::$AUTH, auth_encode((int)$user['uid'].USER_AUTHASH.$username.USER_AUTHASH.$password.USER_AUTHASH.$user['nickname'].USER_AUTHASH.$user['status']),self::$cookietime);
		iPHP::set_cookie('userid',    auth_encode($user['uid']),self::$cookietime);
		iPHP::set_cookie('nickname',  auth_encode($user['nickname']),self::$cookietime);
	}

	public static function status($url=null,$st=null) {
		$status = false;
		$auth   = self::get_cookie(true);

		if($auth){
			$user = self::get($auth['userid'],false);
			if($auth['username']==$user->username && $auth['password']==$user->password){
				$status = true;
			}
			unset($user->password);
		}
		unset($auth);

		if($url && $st){
			if($status){
				$st=="login" && $code = 1;
			}else{
				$st=="nologin" && $code = 0;
			}
			if(isset($code)){
				if(self::$format=='json'){
					return iUI::code($code,0,$url,'json');
				}
				iPHP::redirect($url,true);
			}
		}
		return $status?$user:false;
	}
	public static function logout(){
		iPHP::set_cookie(self::$AUTH, '',-31536000);
		iPHP::set_cookie('userid', '',-31536000);
		iPHP::set_cookie('nickname', '',-31536000);
		iPHP::set_cookie('seccode', '',-31536000);
		iPHP::set_cookie('captcha', '',-31536000);
	}
}
