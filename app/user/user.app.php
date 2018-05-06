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

class userApp {
	public $methods = array('iCMS', 'home', 'favorite', 'article', 'publish', 'manage', 'profile', 'data', 'hits', 'check', 'follow', 'follower', 'fans', 'login', 'findpwd', 'logout', 'register', 'add_category', 'upload', 'uploadpic', 'mobileUp','config', 'uploadvideo', 'uploadimage', 'catchimage', 'report', 'fav_category', 'ucard', 'pm');
	public $openid = null;
	public $user   = array();
	public $me     = array();
	public $config = array();
	private $auth  = false;

	public function __construct() {
		$this->config    = iCMS::$config['user'];
		$this->uid       = (int) $_GET['uid'];
		$this->ajax      = (bool) $_GET['ajax'];
		$this->auth      = user::get_cookie();
		$this->login_uri = user::login_uri();
		files::init(array('userid'=> user::$userid));
		$this->forward();
	}

	private function __user($userdata = false) {
		$status = array('logined' => false, 'followed' => false, 'isme' => false);
		if ($this->uid) {
			// &uid=
			$this->user = user::get($this->uid);
			empty($this->user) && iPHP::error_404(array('user:not_found',$this->uid), 'U10001');
		}
		$this->me = user::status(); //判断是否登陆
		if (empty($this->me) && empty($this->user)) {
			// $this->forward('C');
			iPHP::redirect($this->login_uri);
		}

		if ($this->me) {
			$status['logined'] = true;
			$status['followed'] = (int) user::follow($this->me->uid, $this->user->uid);
			empty($this->user) && $this->user = $this->me;
			if ($this->user->uid == $this->me->uid) {
				$status['isme'] = true;
				$this->user = $this->me;
			}
			iView::assign('me', (array) $this->me);
		}
		$this->user->hits_script = iCMS_API . '?app=user&do=hits&uid=' . $this->user->uid;
		$user = (array) $this->user;
		$userdata && $user['data'] = (array) user::data($this->user->uid);

		iView::assign('status', $status);
		iView::assign('user',$user);
	}

	public function API_iCMS($a = null) {
		$this->API_home();
	}
	public function API_home($category = true) {
		$this->__user(true);
		$category && $u['category'] = user::category((int) $_GET['cid'], iCMS_APP_ARTICLE);
		iView::append('user', $u, true);
		iView::display('iCMS://user/home.htm');
	}
	public function API_fans() {
		$this->API_home();
	}
	public function API_follower() {
		$this->API_home();
	}

	public function API_favorite() {
		$this->API_home();
	}
	public function API_manage() {
		$pgArray = array('publish', 'category', 'article', 'comment', 'inbox', 'favorite', 'follow', 'fans');
		$pg = iSecurity::escapeStr($_GET['pg']);
		$pg OR $pg = 'article';
		if (in_array($pg, $pgArray)) {
			if ($_GET['pg'] == 'comment') {
				$app_array = iCache::get('app/cache_id');
				iView::assign('iAPP', $app_array);
			}
			$this->__user(true);
			$funname = '__API_manage_' . $pg;
			$class_methods = get_class_methods(__CLASS__);
			in_array($funname, $class_methods) && $this->$funname();
			iView::assign('manage', array(
				'url' => iURL::router('user:'.$pg, '?&'),
			));
			iView::assign('pg', $pg);
			iView::assign('pg_file', "./manage/$pg.htm");
			iView::display("iCMS://user/manage.htm");
		}
	}

	private function __API_manage_article() {
		iView::assign('status', isset($_GET['status']) ? (int) $_GET['status'] : '1');
		iView::assign('cid', (int) $_GET['cid']);
		iView::assign('article', array(
			'manage' => iURL::router('user:article', '?&'),
			'edit' => iURL::router('user:publish', '?&'),
		));
	}
	private function __API_manage_favorite() {
		iView::assign('favorite', array(
			'fid' => (int) $_GET['fid'],
			'manage' => iURL::router('user:manage:favorite', '?&'),
		));
	}

	private function __API_manage_publish() {
		$id = (int) $_GET['id'];
		list($article, $article_data) = article::data($id, 0, user::$userid);
		$cid = empty($article['cid']) ? (int) $_GET['cid'] : $article['cid'];

		if (iPHP_DEVICE !== "desktop" && empty($article)) {
			$article['mobile'] = "1";
		}

		iView::assign('article', $article);
		iView::assign('article_data', $article_data);
	}
	/**
	 * [ACTION_manage description]
	 */
	public function ACTION_manage() {
		$this->me = user::status($this->login_uri, "nologin");

		$pgArray = array('publish', 'category', 'article', 'comment', 'message', 'favorite', 'follow', 'fans');
		$pg = iSecurity::escapeStr($_POST['pg']);
		$funname = '__ACTION_manage_' . $pg;
		//print_r($funname);
		$methods = get_class_methods(__CLASS__);
		if (in_array($pg, $pgArray) && in_array($funname, $methods)) {
			$this->$funname();
		}
	}

	private function __ACTION_manage_category() {
		$name_array = (array) $_POST['name'];
		$cid_array = (array) $_POST['_cid'];
		foreach ($name_array as $cid => $name) {
			$name = iSecurity::escapeStr($name);
			iDB::update("user_category",
				array('name' => $name),
				array(
					'cid' => $cid,
					'uid' => user::$userid,
					'appid' => iCMS_APP_ARTICLE,
				)
			);
		}
		foreach ($cid_array as $key => $_cid) {
			$_cid = (int) $_cid;
			if (!$name_array[$_cid]) {
				iDB::update("article",
					array('ucid' => '0'),
					array('userid' => user::$userid)
				);
				iDB::query("
                    DELETE FROM `#iCMS@__user_category`
                    WHERE `cid` = '$_cid'
                    AND `uid`='" . user::$userid . "'
                    AND `appid`='" . iCMS_APP_ARTICLE . "'
                ;");
			}
		}
		if ($_POST['newname']) {
			$_GET['callback'] = 'window.top.callback';
			$_GET['script'] = true;
			$_POST['name'] = $_POST['newname'];
			$this->ACTION_add_category();
		}

		iUI::success('user:category:update', 'js:1');
	}
	private function __ACTION_manage_publish() {
		$aid = (int) $_POST['id'];
		$cid = (int) $_POST['cid'];
		$_cid = (int) $_POST['_cid'];
		$ucid = (int) $_POST['ucid'];
		$_ucid = (int) $_POST['_ucid'];
		$mobile = (int) $_POST['mobile'];
		$title = iSecurity::escapeStr($_POST['title']);
		$pic = iSecurity::escapeStr($_POST['pic']);
		$source = iSecurity::escapeStr($_POST['source']);
		$keywords = iSecurity::escapeStr($_POST['keywords']);
		$description = iSecurity::escapeStr($_POST['description']);
		$creative = (int) $_POST['creative'];
		$userid = user::$userid;
		$author = user::$nickname;
		$editor = user::$nickname;

		if($pic){
			strpos($pic, '..') !== false && iUI::alert('iCMS:file:invaild');
			iFS::check_ext($pic) OR iUI::alert('iCMS:file:failure');
		}

		if ($this->config['post']['seccode']) {
			$seccode = iSecurity::escapeStr($_POST['seccode']);
			iSeccode::check($seccode, true) OR iUI::alert('iCMS:seccode:error');
		}

		if ($this->config['post']['interval']) {
			$last_postime = iDB::value("
                SELECT MAX(postime)
                FROM `#iCMS@__article`
                WHERE userid='" . user::$userid . "' LIMIT 1;
            ");

			if ($_SERVER['REQUEST_TIME'] - $last_postime < $this->config['post']['interval']) {
				iUI::alert('user:publish:interval');
			}
		}

		if ($mobile) {
			$_POST['body'] = ubb2html($_POST['body']);
			$_POST['body'] = trim($_POST['body']);
		}
		$body = iPHP::vendor('CleanHtml', array($_POST['body']));
		empty($title) && iUI::alert('user:publish:empty:title');
		empty($cid) && iUI::alert('user:publish:empty:cid');
		empty($body) && iUI::alert('user:publish:empty:body');

		$fwd = iPHP::callback(array("filterApp","run"),array(&$title),false);
		$fwd && iUI::alert('user:publish:filter_title');
		$fwd = iPHP::callback(array("filterApp","run"),array(&$description),false);
		$fwd && iUI::alert('user:publish:filter_desc');
		$fwd = iPHP::callback(array("filterApp","run"),array(&$body),false);
		$fwd && iUI::alert('user:publish:filter_body');

		empty($description) && $description = articleAdmincp::autodesc($body);

		$pubdate = time();
		$postype = "0";

		$category = categoryApp::get_cahce_cid($cid);
		$status = $category['config']['examine'] ? 3 : 1;

		$fields = article::fields($aid);
		$data_fields = article::data_fields($aid);
		if (empty($aid)) {
			$postime = $pubdate;
			$chapter = $hits = $good = $bad = $comments = 0;

			$data = compact($fields);
			$aid = article::insert($data);
			$article_data = compact($data_fields);
			article::data_insert($article_data);

			iMap::init('category', iCMS_APP_ARTICLE,'cid');
			iMap::add($cid, $aid);
			iDB::query("
                UPDATE `#iCMS@__user_category`
                SET `count` = count+1
                WHERE `cid` = '$ucid'
                AND `uid`='" . user::$userid . "'
                AND `appid`='" . iCMS_APP_ARTICLE . "'
            ");
			user::update_count(user::$userid,'article');
			$lang = array(
				'1' => 'user:article:add_success',
				'3' => 'user:article:add_examine',
			);
		} else {
			$update = article::update(
				compact($fields),
				array('id' => $aid, 'userid' => user::$userid)
			);

			$update && article::data_update(compact($data_fields), array('aid' => $aid));

			iMap::init('category', iCMS_APP_ARTICLE,'cid');
			iMap::diff($cid, $_cid, $aid);
			if ($ucid != $_ucid) {
				iDB::query("
                    UPDATE `#iCMS@__user_category`
                    SET `count` = count+1
                    WHERE `cid` = '$ucid'
                    AND `uid`='" . user::$userid . "'
                    AND `appid`='" . iCMS_APP_ARTICLE . "'
                ");
				iDB::query("
                    UPDATE `#iCMS@__user_category`
                    SET `count` = count-1
                    WHERE `cid` = '$_ucid'
                    AND `uid`='" . user::$userid . "
                    AND `count`>0'
                    AND `appid`='" . iCMS_APP_ARTICLE . "'
                ");
			}
			$lang = array(
				'1' => 'user:article:update_success',
				'3' => 'user:article:update_examine',
			);
		}
		$url = iURL::router('user:article');
		iUI::success($lang[$status], 'url:' . $url);
	}
	private function __ACTION_manage_article() {
		$actArray = array('delete', 'renew', 'trash');
		$act = iSecurity::escapeStr($_POST['act']);
		if (in_array($act, $actArray)) {
			$id = (int) $_POST['id'];
			$id OR iUI::code(0, 'iCMS:error', 0, 'json');
			$sql = null;
			if($act == "renew"){
				$sql = "`status` ='1'";
				$cid = article::value('cid',$id);
				$C = categoryApp::get_cahce_cid($cid);
				$C['config']['examine'] && $sql = "`status` ='3'";
			}
			$act == "delete" && $sql = "`status` ='2',`postype`='3'";
			$act == "trash" && $sql = "`status` ='2'";
			$sql && iDB::query("
                UPDATE `#iCMS@__article`
                SET $sql
                WHERE `userid` = '" . user::$userid . "'
                AND `id`='$id'
                LIMIT 1;
            ");
			iUI::code(1, 0, 0, 'json');
		}
	}
	private function __ACTION_manage_comment() {
		$act = iSecurity::escapeStr($_POST['act']);
		if ($act == "del") {
			$id = (int) $_POST['id'];
			$id OR iUI::code(0, 'iCMS:error', 0, 'json');
			$comment = commentAdmincp::get($id,user::$userid);
			commentAdmincp::del($comment);

			iUI::code(1, 0, 0, 'json');
		}
	}
	private function __ACTION_manage_message() {
		messageApp::API_manage();
	}
	public function API_profile() {
		$pgArray = array('base', 'avatar', 'setpassword', 'bind', 'custom');
		$pg = iSecurity::escapeStr($_GET['pg']);
		$pg OR $pg = 'base';
		if (in_array($pg, $pgArray)) {
			$this->__user();
			iView::assign('pg', $pg);
			if ($pg == 'bind') {
				$platform = user::openid(user::$userid);
				iView::assign('platform', $platform);
			}
			if ($pg == 'base') {
				iView::assign('userdata', (array) user::data(user::$userid));
			}
			iView::display("iCMS://user/profile.htm");
		}
	}
	/**
	 * [ACTION_profile description]
	 */
	public function ACTION_profile() {
		$this->me = user::status($this->login_uri, "nologin");

		$pgArray = array('base', 'avatar', 'setpassword', 'bind', 'custom');
		$pg = iSecurity::escapeStr($_POST['pg']);
		$funname = '__ACTION_profile_' . $pg;
		$methods = get_class_methods(__CLASS__);
		if (in_array($pg, $pgArray) && in_array($funname, $methods)) {
			$this->$funname();
		}
	}
	private function __ACTION_profile_base() {
		$nickname      = iSecurity::escapeStr($_POST['nickname']);
		$gender        = iSecurity::escapeStr($_POST['gender']);
		$province      = iSecurity::escapeStr($_POST['province']);
		$city          = iSecurity::escapeStr($_POST['city']);
		$year          = iSecurity::escapeStr($_POST['year']);
		$month         = iSecurity::escapeStr($_POST['month']);
		$day           = iSecurity::escapeStr($_POST['day']);
		$constellation = iSecurity::escapeStr($_POST['constellation']);
		$profession    = iSecurity::escapeStr($_POST['profession']);
		$personstyle   = iSecurity::escapeStr($_POST['personstyle']);
		$slogan        = iSecurity::escapeStr($_POST['slogan']);
		$meta          = iSecurity::escapeStr($_POST['meta']);
		$setting       = iSecurity::escapeStr($_POST['setting']);

		($personstyle == iUI::lang('user:profile:personstyle')) && $personstyle = "";
		($slogan == iUI::lang('user:profile:slogan')) && $slogan = "";
		$meta && $meta = addslashes(json_encode($meta));

		// if($nickname!=user::$nickname){
		//     $has_nick = iDB::value("SELECT uid FROM `#iCMS@__user` where `nickname`='{$nickname}' AND `uid` <> '".user::$userid."'");
		//     $has_nick && iUI::alert('user:profile:nickname');
		//     $userdata = user::data(user::$userid);
		//     if($userdata->unickEdit>1){
		//         iUI::alert('user:profile:unickEdit');
		//     }
		//     if($nickname){
		//         iDB::update('user',array('nickname'=>$nickname),array('uid'=>user::$userid));
		//         $unickEdit = 1;
		//     }
		// }
		if ($gender != $this->me->gender) {
			iDB::update('user', array('gender' => $gender), array('uid' => user::$userid));
		}
		if ($setting) {
			$setting = array_merge((array)$this->me->setting,(array)$setting);
			$setting = addslashes(json_encode($setting));

			iDB::update('user',
				array('setting' => $setting),
				array('uid' => user::$userid)
			);
		}

		$uid = iDB::value("
            SELECT `uid`
            FROM `#iCMS@__user_data`
            WHERE `uid`='" . user::$userid . "' LIMIT 1;
        ");

		$fields = array(
			'province', 'city', 'year', 'month', 'day',
			'constellation','profession','personstyle','slogan',
			'meta',
		);
		if ($uid) {
			$fdata = compact($fields);
			$unickEdit && $fdata['unickEdit'] = 1;
			iDB::update('user_data', $fdata, array('uid' => user::$userid));
		} else {
			$unickEdit = 0;
			$uid       = user::$userid;
			$_fields   = array('uid', 'realname', 'unickEdit', 'mobile','address');
			$fields    = array_merge($fields, $_fields);
			$fdata     = compact($fields);
			iDB::insert('user_data', $fdata);
		}
		iUI::success('user:profile:success');
	}
	private function __ACTION_profile_custom() {
		files::$watermark_enable = false;
		files::$check_data       = false;
		iFS::$CALLABLE['upload'] = null;
		$dir = get_user_dir(user::$userid, 'coverpic');
		$filename = user::$userid;

		$isBlob = false;
		if($_FILES['upfile']['name']=='blob'){
			$isBlob = true;
		    $_FILES['upfile']['name'] = uniqid().'.jpg';
			$filename = 'm_' . user::$userid;
		}

		$F = iFS::upload('upfile', $dir, $filename, 'jpg');
		if (empty($F)) {
			iUI::code(0, 'user:iCMS:error', 0, 'json');
		}

		if($isBlob){
			//在cropper.min.js 没有找到更好的解决办法前只能先用PHP强制生成
			require iPHP_CORE.'/Gmagick.class.php';
			$srcPath = $F['RootPath'];
            $gmagick = new Gmagick();
            $gmagick->readImage($srcPath);
            $gmagick->resizeImage(480,300, null, 1);
            $srcData = $gmagick->current();
            file_put_contents($srcPath, $srcData);
		}

		$url = iFS::fp($F['path'], '+http');
		iUI::code(1, 'user:profile:custom', $url, 'json');
	}
	private function __ACTION_profile_avatar() {
		files::$watermark_enable = false;
		files::$check_data       = false;
		iFS::$CALLABLE['upload'] = null;

		$isBlob = false;
		if($_FILES['upfile']['name']=='blob'){
			$isBlob = true;
		    $_FILES['upfile']['name'] = uniqid().'.jpg';
		}

		$dir = get_user_dir(user::$userid);
		$F = iFS::upload('upfile', $dir, user::$userid, 'jpg');

		if (empty($F)) {
			iUI::code(0, 'user:iCMS:error', 0, 'json');
		}

		if($isBlob){
			//在cropper.min.js 没有找到更好的解决办法前只能先用PHP强制生成
			require iPHP_CORE.'/Gmagick.class.php';
			$srcPath = $F['RootPath'];
            $gmagick = new Gmagick();
            $gmagick->readImage($srcPath);
            $gmagick->resizeImage(300,300, null, 1);
            $srcData = $gmagick->current();
            file_put_contents($srcPath, $srcData);
		}
		$url = iFS::fp($F['path'], '+http');
		iUI::code(1, 'user:profile:avatar', $url, 'json');
	}

	private function __ACTION_profile_setpassword() {
		iSeccode::check($_POST['seccode'], true) OR iUI::alert('iCMS:seccode:error');

		$oldPwd = md5($_POST['oldPwd']);
		$newPwd1 = md5($_POST['newPwd1']);
		$newPwd2 = md5($_POST['newPwd2']);

		$newPwd1 != $newPwd2 && iUI::alert("user:password:unequal");

		$password = iDB::value("
            SELECT `password`
            FROM `#iCMS@__user`
            WHERE `uid`='" . user::$userid . "' LIMIT 1;
        ");
		$oldPwd != $password && iUI::alert("user:password:original");
		iDB::query("
            UPDATE `#iCMS@__user`
            SET `password` = '$newPwd1'
            WHERE `uid` = '" . user::$userid . "';
        ");
		iUI::alert("user:password:modified", 'js:parent.location.reload();');
	}
	public function ACTION_findpwd() {
		$seccode = iSecurity::escapeStr($_POST['seccode']);
		iSeccode::check($seccode, true) OR iUI::code(0, 'iCMS:seccode:error', 'seccode', 'json');

		$uid = (int) $_POST['uid'];
		$auth = iSecurity::escapeStr($_POST['auth']);
		if ($auth && $uid) {
			//print_r($_POST);
			$authcode = rawurldecode($auth);
			$authcode = base64_decode($authcode);
			$authcode = auth_decode($authcode);

			if (empty($authcode)) {
				iUI::code(0, 'user:findpwd:error', 'uname', 'json');
			}
			list($uid, $username, $password, $timeline) = explode(USER_AUTHASH, $authcode);
			$uid = (int)$uid;
			$now = time();
			if ($now - $timeline > 86400) {
				iUI::code(0, 'user:findpwd:error', 'time', 'json');
			}
			$user = user::get($uid, false);
			if ($username != $user->username || $password != $user->password) {
				iUI::code(0, 'user:findpwd:error', 'user', 'json');
			}
			$rstpassword = md5(trim($_POST['rstpassword']));
			if ($rstpassword == $user->password) {
				iUI::code(0, 'user:findpwd:same', 'password', 'json');
			}
			iDB::update("user", array('password' => $rstpassword), array('uid' => $uid));
			iUI::code(1, 'user:findpwd:success', 0, 'json');
		} else {
			$uname = iSecurity::escapeStr($_POST['uname']);
			$uname OR iUI::code(0, 'user:findpwd:username:empty', 'uname', 'json');
			$uid = user::check($uname, 'username');
			$uid OR iUI::code(0, 'user:findpwd:username:noexist', 'uname', 'json');
			$user = user::get($uid, false);
			$user OR iUI::code(0, 'user:findpwd:username:noexist', 'uname', 'json');

			$authcode = auth_encode($uid .
				USER_AUTHASH . $user->username .
				USER_AUTHASH . $user->password .
				USER_AUTHASH . time()
			);
			$authcode = base64_encode($authcode);
			$authcode = rawurlencode($authcode);
			$find_url = iURL::router('user:findpwd', '?&');
			$find_url.= 'auth=' . $authcode;
			$config = iCMS::$config['mail'];
			$config['title'] = iCMS::$config['site']['name'];
			$config['subject'] = iUI::lang(array('user:findpwd:subject',$config['title']));
			$config['body'] = iUI::lang(array(
				'user:findpwd:body',
				$user->nickname,$config['title'],$find_url,$find_url,$config['replyto']
			));
			$config['address'] = array(
				array($user->username, $user->nickname),
			);
			//var_dump(iCMS::$config);
			$result = iPHP::vendor('SendMail', array($config));
			if ($result === true) {
				iUI::code(1, 'user:findpwd:send:success', 'mail', 'json');
			} else {
				iUI::code(0, 'user:findpwd:send:failure', 'mail', 'json');
			}
		}
	}
	public function ACTION_login() {
		$this->config['login']['enable'] OR iUI::code(0, 'user:login:forbidden', 'uname', 'json');

		$uname = iSecurity::escapeStr($_POST['uname']);
		$pass = md5(trim($_POST['pass']));
		$remember = (bool) $_POST['remember'] ? ture : false;

		$openid = iSecurity::escapeStr($_POST['openid']);
		$platform = iSecurity::escapeStr($_POST['platform']);

		if ($this->config['login']['seccode']) {
			$seccode = iSecurity::escapeStr($_POST['seccode']);
			iSeccode::check($seccode, true) OR iUI::code(0, 'iCMS:seccode:error', 'seccode', 'json');
		}
		$u_field = user::check_uname_type($uname);

		if ($this->config['login']['interval']) {
			$lastloginip   = iPHP::get_ip();
			$logintime     = time();
			$lastlogintime = iDB::value("
                SELECT `lastlogintime`
                FROM `#iCMS@__user`
                WHERE `lastloginip`='$lastloginip'
                AND `$u_field`!='$uname'
                ORDER BY uid DESC LIMIT 1;"
            );
			//判断同IP不同账号 登陆间隔
			$interval = $logintime - (int)$lastlogintime;
			if ($interval < $this->config['login']['interval']) {
				iUI::code(0, array(
					'user:login:same_ip',
					format_time($this->config['login']['interval']-$interval,'cn')
				), 'username', 'json');
			}
			//5次登陆错误
			$cache_name = "error/login." . md5($uname);
			$login_error = iCache::get($cache_name);
			if ($login_error) {
				$interval = $logintime - (int)$login_error[2];
				if ($login_error[1] >= 5 && $interval < $this->config['login']['interval']) {
					iUI::code(0,array(
							'user:login:interval',
							format_time($this->config['login']['interval']-$interval,'cn')
						),
						'uname', 'json'
					);
				}
			}
		}

		$remember && user::$cookietime = 14 * 86400;

		$user = user::login($uname, $pass);
		if ($user === true) {
			if ($openid) {
				iDB::insert('user_openid',array(
					'uid'      => user::$userid,
					'openid'   => $openid,
					'platform' => $platform,
				));
			}
			iUI::code(1, 0, $this->forward, 'json');
		} else {
			if ($this->config['login']['interval']) {
				if ($login_error) {
					++$login_error[1];
					$login_error[2] = $logintime;
				} else {
					$login_error = array($uname, 1,$logintime);
				}
				iCache::set($cache_name, $login_error, $this->config['login']['interval']);
			}
			iUI::code(0, 'user:login:error', 'uname', 'json');
		}
	}

	public function ACTION_register() {
		$this->config['register']['enable'] OR exit(iUI::lang('user:register:forbidden'));

		if ($this->config['register']['seccode']) {
			$seccode = iSecurity::escapeStr($_POST['seccode']);
			iSeccode::check($seccode, true) OR iUI::code(0, 'iCMS:seccode:error', 'seccode', 'json');
		}

		$regip = iPHP::get_ip();
		$regdate = time();

		if ($this->config['register']['interval']) {
			$ip_regdate = iDB::value("
                SELECT `regdate`
                FROM `#iCMS@__user`
                WHERE `regip`='$regip'
                ORDER BY uid DESC LIMIT 1;
            ");

			if ($ip_regdate - $regdate > $this->config['register']['interval']) {
				iUI::code(0, 'user:register:interval', 'username', 'json');
			}
		}

		$username = iSecurity::escapeStr($_POST['username']);
		$nickname = iSecurity::escapeStr($_POST['nickname']);
		$gender = ($_POST['gender'] == 'girl' ? 0 : 1);
		$password = md5(trim($_POST['password']));
		$rstpassword = md5(trim($_POST['rstpassword']));
		$refer = iSecurity::escapeStr($_POST['refer']);

		$openid = iSecurity::escapeStr($_POST['openid']);
		$type = iSecurity::escapeStr($_POST['platform']);
		$avatar = iSecurity::escapeStr($_POST['avatar']);

		$province = iSecurity::escapeStr($_POST['province']);
		$city = iSecurity::escapeStr($_POST['city']);

		$agreement = iSecurity::escapeStr($_POST['agreement']);

		$username OR iUI::code(0, 'user:register:username:empty', 'username', 'json');
		preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/i", $username) OR iUI::code(0, 'user:register:username:error', 'username', 'json');
		user::check($username, 'username') && iUI::code(0, 'user:register:username:exist', 'username', 'json');

		$nickname OR iUI::code(0, 'user:register:nickname:empty', 'nickname', 'json');
		(cstrlen($nickname) > 20 || cstrlen($nickname) < 4) && iUI::code(0, 'user:register:nickname:error', 'nickname', 'json');
		user::check($nickname, 'nickname') && iUI::code(0, 'user:register:nickname:exist', 'nickname', 'json');

		$fwd = iPHP::callback(array("filterApp","run"),array(&$nickname),false);
		$fwd && iUI::alert('user:register:nickname:filter');

		trim($_POST['password']) OR iUI::code(0, 'user:password:empty', 'password', 'json');
		trim($_POST['rstpassword']) OR iUI::code(0, 'user:password:rst_empty', 'rstpassword', 'json');
		$password == $rstpassword OR iUI::code(0, 'user:password:unequal', 'password', 'json');

		$_setting = array();
		$_setting['inbox']['receive'] = 'follow';
		$setting = addslashes(json_encode($_setting));

		$gid = 0;
		$pid = 0;
		$fans = $follow = $article = $comments = $favorite = $credit = 0;
		$hits = $hits_today = $hits_yday = $hits_week = $hits_month = 0;
		$lastloginip   = $regip;
		$lastlogintime = $regdate;
		$status = 1;
		$fields = array(
			'gid', 'pid', 'username', 'nickname', 'password',
			'gender', 'fans', 'follow', 'article', 'comments','favorite', 'credit',
			'regip', 'regdate', 'lastloginip','lastlogintime',
			'hits', 'hits_today', 'hits_yday', 'hits_week','hits_month',
			'setting','type', 'status',
		);
		$data = compact($fields);
		$uid = iDB::insert('user', $data);

		user::set_cookie(
			$username,$password,
			array('uid' => $uid,
				'username' => $username,
				'nickname' => $nickname,
				'status' => $status,
			)
		);

		if ($openid) {
			iDB::insert('user_openid',array(
				'uid'      => $uid,
				'openid'   => $openid,
				'platform' => $type,
			));
		}
		if ($avatar) {
			$avatarData = iHttp::remote($avatar);
			if ($avatarData) {
				$avatarpath = iFS::fp(get_user_pic($uid), '+iPATH');
				iFS::mkdir(dirname($avatarpath));
				iFS::write($avatarpath, $avatarData);
				iFS::yun_write($avatarpath);
			}
		}

		//user::set_cache($uid);
		iUI::json(array('code' => 1, 'forward' => $this->forward));
	}
	public function ACTION_add_category() {
		$uid = user::$userid;
		$name = iSecurity::escapeStr($_POST['name']);
		empty($name) && iUI::code(0, 'user:category:empty', 'add_category', 'json');
		$fwd = iPHP::callback(array("filterApp","run"),array(&$name),false);
		$fwd && iUI::code(0, 'user:category:filter', 'add_category', 'json');
		$max = iDB::value("
            SELECT COUNT(cid)
            FROM `#iCMS@__user_category`
            WHERE `uid`='$uid'
            AND `appid`='" . iCMS_APP_ARTICLE . "' LIMIT 1;"
		);
		$max >= 10 && iUI::code(0, 'user:category:max', 'add_category', 'json');
		$count = 0;
		$appid = iCMS_APP_ARTICLE;
		$fields = array('uid', 'name', 'description', 'count', 'mode', 'appid');
		$data = compact($fields);
		$cid = iDB::insert('user_category', $data);
		$cid && iUI::code(1, 'user:category:success', $cid, 'json');
		iUI::code(0, 'user:category:failure', 0, 'json');
	}
	public function ACTION_report() {
		$this->auth OR iUI::code(0, 'iCMS:!login', 0, 'json');

		$iid = (int) $_POST['iid'];
		$uid = (int) $_POST['userid'];
		$appid = (int) $_POST['appid'];
		$reason = (int) $_POST['reason'];
		$content = iSecurity::escapeStr($_POST['content']);

		$iid OR iUI::code(0, 'iCMS:error', 0, 'json');
		$uid OR iUI::code(0, 'iCMS:error', 0, 'json');
		$reason OR $content OR iUI::code(0, 'iCMS:report:empty', 0, 'json');

		$addtime = time();
		$ip = iPHP::get_ip();
		$userid = user::$userid;
		$status = 0;

		$fields = array('appid', 'userid', 'iid', 'uid', 'reason', 'content', 'ip', 'addtime', 'status');
		$data = compact($fields);
		$id = iDB::insert('user_report', $data);
		iUI::code(1, 'iCMS:report:success', $id, 'json');
	}

	public function ACTION_follow() {
		$this->auth OR iUI::code(0, 'iCMS:!login', 0, 'json');

		$uid = (int) user::$userid;
		$name = user::$nickname;
		$fuid = (int) $_POST['uid'];
		$follow = (bool) $_POST['follow'];

		$uid OR iUI::code(0, 'iCMS:error', 0, 'json');
		$fuid OR iUI::code(0, 'iCMS:error', 0, 'json');

		if ($follow) {
			//1 关注
			$uid == $fuid && iUI::code(0, 'user:follow:self', 0, 'json');
			$check = user::follow($uid, $fuid);
			if ($check) {
				iUI::code(1, 'user:follow:success', 0, 'json');
			} else {
				$fname  = user::value($fuid,'nickname');
				$fields = array('uid', 'name', 'fuid', 'fname');
				$data   = compact($fields);
				iDB::insert('user_follow', $data);
				user::update_count($uid, 'follow');
				user::update_count($fuid,'fans');
				iUI::code(1, 'user:follow:success', 0, 'json');
			}
		} else {
			iDB::query("
                DELETE FROM `#iCMS@__user_follow`
                WHERE `uid` = '$uid'
                AND `fuid`='$fuid'
                LIMIT 1;
            ");
			user::update_count($uid,'follow', '-');
			user::update_count($fuid,'fans', '-');
			iUI::code(1, 0, 0, 'json');
		}
	}

	public function API_hits($uid = null) {
		$uid === null && $uid = (int) $_GET['uid'];
		if ($uid) {
			$sql = iSQL::update_hits();
			iDB::query("UPDATE `#iCMS@__user` SET {$sql} WHERE `uid` ='$uid'");
		}
	}
	public function API_check() {
		$name  = iSecurity::escapeStr($_GET['name']);
		$value = iSecurity::escapeStr($_GET['value']);
		$a = iUI::code(1, '', $name,'array');
		switch ($name) {
			case 'username':
				if (!preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/i", $value)) {
					$a = iUI::code(0, 'user:register:username:error', 'username','array');
				} else {
					if (user::check($value, 'username')) {
						$a = iUI::code(0, 'user:register:username:exist', 'username','array');
					}
				}
			break;
			case 'nickname':
				if (preg_match("/\d/", $value[0]) || cstrlen($value) > 20 || cstrlen($value) < 4) {
					$a = iUI::code(0, 'user:register:nickname:error', 'nickname','array');
				} else {
					if (user::check($value, 'nickname')) {
						$a = iUI::code(0, 'user:register:nickname:exist', 'nickname','array');
					}
				}
			break;
			case 'password':
				strlen($value) < 6 && $a = iUI::code(0, 'user:password:error', 'password','array');
			break;
			case 'seccode':
				iSeccode::check($value) OR $a = iUI::code(0, 'iCMS:seccode:error', 'seccode','array');
			break;
		}
		iUI::json($a);
	}

	public function API_register() {
		if ($this->config['register']['enable']) {
			$this->forward('r');
			user::status($this->forward, "login");
			iView::display('iCMS://user/register.htm');
		} else {
			iView::display('iCMS://user/register.close.htm');
		}
	}
	public function API_data($uid = 0) {
		$user = user::status();
		if ($user) {
			$array = array(
				'code'        => 1,
				'uid'         => $user->uid,
				'url'         => $user->url,
				'avatar'      => $user->avatar,
				'nickname'    => $user->nickname,
				'message_num' => messageApp::_count($user->uid),
			);
			iView::assign('data', $array);
			iView::display('iCMS://user/api.data.htm');
			// iUI::json($array);
		} else {
			iUI::code(0, 0, $this->forward, 'json');
		}
	}
	public function API_logout() {
		user::logout();
		iUI::code(1, 0, $this->forward, 'json');
	}
	public function API_findpwd() {
		$auth = iSecurity::escapeStr($_GET['auth']);
		if ($auth) {
			$authcode = rawurldecode($auth);
			$authcode = base64_decode($authcode);
			$authcode = auth_decode($authcode);

			if (empty($authcode)) {
				exit;
			}
			list($uid, $username, $password, $timeline) = explode(USER_AUTHASH, $authcode);
			$now = time();
			if ($now - $timeline > 86400) {
				exit;
			}
			$user = user::get($uid, false);
			if ($username != $user->username || $password != $user->password) {
				exit;
			}
			unset($user->password);
			iView::assign('auth', $auth);
			iView::assign('user', (array) $user);
			iView::display('iCMS://user/resetpwd.htm');
		} else {
			iView::display('iCMS://user/findpwd.htm');
		}
	}
	public function API_login() {
		if ($this->config['login']['enable']) {
			$this->forward('r');
			$this->openid();
			user::status($this->forward, "login");
			iView::display('iCMS://user/login.htm');
		} else {
			iView::display('iCMS://user/login.close.htm');
		}
	}
	public function API_config() {
		$this->auth OR iUI::code(0, 'iCMS:!login', 0, 'json');
		$editorAdmincp = new editorAdmincp;
		$editorAdmincp->do_config();
	}
	public function API_catchimage() {
		$this->auth OR iUI::code(0, 'iCMS:!login', 0, 'json');
		//iCMS::$config['FS']['allow_ext'] = 'gif,jpg,jpeg,png';
		$editorAdmincp = new editorAdmincp;
		$editorAdmincp->do_catchimage();
	}
	public function API_uploadimage() {
		$this->auth OR iUI::code(0, 'iCMS:!login', 0, 'json');
		//iCMS::$config['FS']['allow_ext'] = 'gif,jpg,jpeg,png';
		$editorAdmincp = new editorAdmincp;
		$editorAdmincp->do_uploadimage();
	}
	public function API_uploadvideo() {
		$this->auth OR iUI::code(0, 'iCMS:!login', 0, 'json');
		$editorAdmincp = new editorAdmincp;
		$editorAdmincp->do_uploadvideo();
	}
	public function API_uploadpic() {
		$this->auth OR iUI::code(0, 'iCMS:!login', 0, 'json');
		//iCMS::$config['FS']['allow_ext'] = 'gif,jpg,jpeg,png';
		$F = iFS::upload('upfile');
		iUI::js_callback(array(
			'url'  => iFS::fp($F['path'], '+http'),
			'path' => $F['path'],
			'code' => $F['code'],
		));
	}
	//手机上传
	public function API_mobileUp() {
		$this->auth OR iUI::code(0, 'iCMS:!login', 0, 'json');
		//iCMS::$config['FS']['allow_ext'] = 'gif,jpg,jpeg,png';
		$F = iFS::upload('upfile');
		$F['path'] && $url = iFS::fp($F['path'], '+http');
		iUI::js_callback(array(
			'url' => $url,
			'code' => $F['code'],
		));
	}
	public function API_collections() {

		//iView::display('iCMS://user/card.htm');
	}
	public function API_ucard() {
		$this->__user(true);
		if ($this->auth) {
			$secondary = $this->__secondary();
			iView::assign('secondary', $secondary);
		}
		iView::display('iCMS://user/user.card.htm');
	}

	private function __secondary() {
		if ($this->uid == user::$userid) {
			return;
		}

		$follow = user::follow(user::$userid, 'all'); //你的所有关注者
		$fans = user::follow('all', $this->uid); //他的所有粉丝
		$links = array();
		foreach ((array) $fans as $uid => $name) {
			if ($follow[$uid]) {
				$url = user::router($uid, "url");
				$links[$uid] = '<a href="' . $url . '" class="user-link" title="' . $name . '">' . $name . '</a>';
			}
		}
		if (empty($links)) {
			return;
		}
		$_count = count($links);
		$text = iUI::lang('user:follow:text1');
		if ($_count > 3) {
			$links = array_slice($links, 0, 3);
			$text = iUI::lang(array('user:follow:text2',$_count));
		}
		return implode('、', $links) . $text;
	}

	public function openid() {
		if (!isset($_GET['sign'])) {
			return;
		}
		$sign  = strtoupper($_GET['sign']);
		$code  = $_GET['code'];
		$state = $_GET['state'];
		$bind  = $sign;
		$platform_map = array('WX' => 1, 'QQ' => 2, 'WB' => 3, 'TB' => 4);
		$platform     = $platform_map[$sign];

		if ($platform) {
			$class_name   = 'user_'.$sign;
			$open = new $class_name;
			$open->appid = $this->config['open'][$sign]['appid'];
			$open->appkey = $this->config['open'][$sign]['appkey'];
			$redirect_uri = rtrim($this->config['open'][$sign]['redirect'], '/');
			$open->url = user::login_uri($redirect_uri) . 'sign=' . $sign;
			$this->forward && $open->url.='&forward='.urlencode($this->forward);

			if (isset($_GET['bind']) && $_GET['bind'] == $sign) {
				$open->get_openid();
			} else {
				$open->callback();
			}

			$userid = user::openid($open->openid, $platform);
			if ($userid) {
				$user = user::get($userid, false);
				user::set_cookie($user->username, $user->password, array(
					'uid' => $userid,
					'username' => $user->username,
					'nickname' => $user->nickname,
					'status' => $user->status,
				)
				);
				$open->cleancookie();
				iPHP::redirect($this->forward);
			} else {
				if (isset($_GET['bind'])) {
					$user = array();
					$user['openid'] = $open->openid;
					$user['platform'] = $platform;
					$open->cleancookie();
					iView::assign('user', $user);
					iView::display('iCMS://user/login.htm');
				} else {
					$user = $open->get_user_info();
					$user['openid'] = $open->openid;
					$user['platform'] = $platform;
					user::check($user['nickname'],'nickname') && $user['nickname'] = $sign . '_' . $user['nickname'];
					$open->cleancookie();
					iView::assign('user', $user);
					iView::assign('query', compact(array('sign', 'code', 'state', 'bind')));
					iView::display('iCMS://user/register.htm');
				}
				exit;
			}
		}
	}
	public function forward($flag=null) {
		$this->forward = iSecurity::getGP('forward');
		if(empty($this->forward)){
			$this->forward = iPHP::get_cookie('forward');
			if(empty($this->forward)){
				$this->forward = $_SERVER['HTTP_REFERER'];
				$this->forward OR $this->forward = iCMS_URL;
			}
			if(strpos($this->forward, 'forward=') !== false){
		        $parse  = parse_url($this->forward);
		        parse_str($parse['query'], $qs);
		        $qs['forward'] && $this->forward = $qs['forward'];
			}
			$flag==='c' && iPHP::set_cookie('forward', $this->forward);
			if($flag==='r' && $this->config['forward']){
				$url = iURL::make('forward='.$this->forward);
				iPHP::redirect($url);
			}
		}
		iView::assign('forward', $this->forward);
	}
	public static function at_user_list($content) {
		return self::at($content);
	}
	public static function at_content($content) {
		return self::at($content,false);
	}
	public static function at($content,$user=true) {
		preg_match_all('/@(.+?[^@])\s/is', str_replace('@', "\n@", $content), $matches);
		$user_list = array_unique($matches[1]);
		if($user_list){
			foreach ($user_list as $key => $nk) {
				$userArray[$key] = array('nickname'=>$nk);
				if(!$user){
					$search[$nk]  = '@'.$nk;
					$replace[$nk] = '@'.$nk;
				}
			}
			$values = iSQL::values($userArray,'nickname','array',null);
			$values && $user_data = (array) user::get($values,true,'nickname');
			foreach ((array)$user_data as $key => $value) {
				if(!$user){
					$U = user::info($value->uid, $value->nickname);
					$replace[$value->nickname] = $U['at'];
				}else{
					$remindUser[$value->uid] = $value->nickname;
				}
			}
			!$user && $content = str_replace($search, $replace, $content);
		}
		return $user?$remindUser:$content;
	}
}
