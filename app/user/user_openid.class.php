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

class user_openid {
	public static function save($uid,$openid='',$platform=0,$appid=''){
		iDB::insert('user_openid',array(
			'uid'      => $uid,
			'openid'   => $openid,
			'platform' => $platform,
			'appid'    => $appid,
		));
	}
	public static function uid($openid=0,$platform=0,$appid=''){
		$appid && $sql =" AND `appid`='{$appid}'";
		$uid = iDB::value("
			SELECT `uid` FROM `#iCMS@__user_openid`
			WHERE `openid`='{$openid}'
			AND `platform`='{$platform}'
			{$sql}
		");
		return $uid;
	}
}
