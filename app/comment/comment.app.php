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

class commentApp {
	public $methods = array('like','widget', 'json', 'add', 'form', 'list', 'redirect');
	public $config  = null;
	public function __construct() {
		$this->config = iCMS::$config['comment'];
		$this->id     = (int) $_GET['id'];
	}
	public function API_redirect() {
		$appid = (int) $_GET['appid'];
		$iid   = (int) $_GET['iid'];
		// $_GET  = iSecurity::escapeStr($_GET);

		$url = apps::get_url($appid, $iid);
		iPHP::redirect($url);
	}
	public function API_widget() {
		$name = iSecurity::escapeStr($_GET['name']);
		iView::display('iCMS://comment/widget.'.$name.'.htm');
	}
	public function API_list() {
		$_GET['_display'] = $_GET['display'];
		$_GET['display'] = 'default';
		$_GET = iSecurity::escapeStr($_GET);
		return commentFunc::comment_list($_GET);
	}
	public function API_form() {
		$_GET['_display'] = $_GET['display'];
		$_GET['display'] = 'default';
		$_GET = iSecurity::escapeStr($_GET);
		return commentFunc::comment_form($_GET);
	}

	public function API_like() {
		if ($this->config['like']['login']){
			user::get_cookie() OR iUI::code(0,'iCMS:!login',0,'json');
		}
		empty($this->config['like']['time']) && $this->config['like']['time'] = 86400;

		$this->id OR iUI::code(0, 'iCMS:comment:empty_id', 0, 'json');
		if ($this->config['like']['time']){
			$lckey = 'like_comment_' . $this->id;
			$like = iPHP::get_cookie($lckey);
			$like && iUI::code(0, 'iCMS:comment:!like', 0, 'json');
		}
		//$ip = iPHP::get_ip();
		iDB::query("UPDATE `#iCMS@__comment` SET `up`=up+1 WHERE `id`='$this->id'");
		$this->config['like']['time'] && iPHP::set_cookie($lckey, $_SERVER['REQUEST_TIME'], $this->config['like']['time']);
		iUI::code(1, 'iCMS:comment:like', 0, 'json');
	}
	public function API_json() {
		$vars = array(
			'appid' => iCMS_APP_ARTICLE,
			'id' => (int) $_GET['id'],
			'iid' => (int) $_GET['iid'],
			'date_format' => 'Y-m-d H:i',
		);
		$_GET['by'] && $vars['by'] = iSecurity::escapeStr($_GET['by']);
		$_GET['date_format'] && $vars['date_format'] = iSecurity::escapeStr($_GET['date_format']);
		$vars['page'] = true;
		// $array = comment_list($vars);
		// iUI::json($array);
		iView::assign('vars',$vars);
		iView::display('iCMS://comment/api.json.htm');
	}

	public function ACTION_add() {
		if (!$this->config['enable']) {
			iUI::code(0, 'iCMS:comment:close', 0, 'json');
		}

		user::get_cookie() OR iUI::code(0, 'iCMS:!login', 0, 'json');

		if ($this->config['seccode']) {
			$seccode = iSecurity::escapeStr($_POST['seccode']);
			iSeccode::check($seccode, true) OR iUI::code(0, 'iCMS:seccode:error', 'seccode', 'json');
		}

		$appid = (int) $_POST['appid'];
		$iid = (int) $_POST['iid'];
		$cid = (int) $_POST['cid'];
		$suid = (int) $_POST['suid'];
		$reply_id = (int) $_POST['id'];
		$reply_uid = (int) $_POST['userid'];
		$reply_name = iSecurity::escapeStr($_POST['name']);
		$title = iSecurity::escapeStr($_POST['title']);
		$content = iSecurity::escapeStr($_POST['content']);
		$iid OR iUI::code(0, 'iCMS:comment:empty_id', 0, 'json');
		$content OR iUI::code(0, 'iCMS:comment:empty', 0, 'json');

		$fwd = iPHP::callback(array("filterApp","run"),array(&$content),false);
		$fwd && iUI::code(0, 'iCMS:comment:filter', 0, 'json');

		$appid OR $appid = iCMS_APP_ARTICLE;
		$addtime = $_SERVER['REQUEST_TIME'];
		$ip = iPHP::get_ip();
		$userid = user::$userid;
		$username = user::$nickname;
		$status = $this->config['examine'] ? '0' : '1';
		$up = '0';
		$down = '0';
		$quote = '0';
		$floor = '0';

		$fields = array('appid', 'cid', 'iid', 'suid', 'title', 'userid', 'username',
			'content', 'reply_id', 'reply_uid', 'reply_name', 'addtime',
			'status', 'up', 'down', 'ip', 'quote', 'floor'
		);
		$data = compact($fields);
		$id = iDB::insert('comment', $data);

		iPHP::callback(array('apps','update_count'),array($iid,$appid,'comments','+'));
		iPHP::callback(array('user','update_count'),array($userid,'comments','+'));

		if ($this->config['examine']) {
			iUI::code(0, 'iCMS:comment:examine', $id, 'json');
		}
		iUI::code(1, 'iCMS:comment:success', $id, 'json');
	}
	public static function value($value,$vars) {
		if($vars['date_format']){
			$value['addtime'] = get_date($value['addtime'],$vars['date_format']);
		}
		$value['url'] = self::redirect_url($value);
		$value['content'] = nl2br($value['content']);
		$value['user']    = user::info($value['userid'],$value['username'],$vars['facesize']);
		$value['reply_uid'] && $value['reply'] = user::info($value['reply_uid'],$value['reply_name'],$vars['facesize']);
        $value['param'] = array(
			"sappid" => iCMS_APP_COMMENT,
			"appid"  => (int)$value['appid'],
			"iid"    => (int)$value['iid'],
			"id"     => (int)$value['id'],
			"cid"    => (int)$value['cid'],
			"userid" => (int)$value['userid'],
			"name"   => iSecurity::escapeStr($value['username']),
			'suid'   => (int)$value['userid'],
			'title'  => iSecurity::escapeStr($value['title']),
        );
        return $value;
	}
	public static function redirect_url($var) {
		$url = iCMS_API.'?app=comment&do=redirect&iid='.$var['iid'].'&appid='.$var['appid'].'&cid='.$var['cid'];
		return $url;
	}
}
