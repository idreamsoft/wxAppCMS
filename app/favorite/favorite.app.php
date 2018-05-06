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

class favoriteApp {
	public $methods	= array('add','delete','create','list');
    public function __construct() {
        $this->id = (int)$_GET['id'];
    }
    private function __login(){
        user::get_cookie() OR iUI::code(0,'iCMS:!login',0,'json');
    }
    public function API_list(){
        user::get_cookie() OR iUI::code(0,'iCMS:!login',0,'json');
        $array = favoriteFunc::favorite_list(array('userid'=>user::$userid));
        iUI::json($array);
    }

    /**
     * [ACTION_delete 删除收藏]
     */
    public function ACTION_delete(){
        $this->__login();

        $uid     = user::$userid;
        $fid     = (int)$_POST['fid'];
        $id      = (int)$_POST['id'];
        $appid   = (int)$_POST['appid'];
        $iid     = (int)$_POST['iid'];
        $cid     = (int)$_POST['cid'];
        $suid    = (int)$_POST['suid'];
        $title   = iSecurity::escapeStr($_POST['title']);
        $url     = iSecurity::escapeStr($_POST['url']);

        if(empty($fid)){
            iUI::code(0,'iCMS:error',0,'json');
        }
        if(empty($url) && empty($id)){
            iUI::code(0,'iCMS:error',0,'json');
        }
        if($url){
            iDB::query("
                DELETE
                FROM `#iCMS@__favorite_data`
                WHERE `uid` = '$uid'
                AND `fid` = '$fid'
                AND `url` = '$url';
            ");
        }
        if($id){
            iDB::query("
                DELETE
                FROM `#iCMS@__favorite_data`
                WHERE `uid` = '$uid'
                AND `id`='$id'
            ");
        }
        iPHP::callback(array('apps','update_count'),array($iid,$appid,'favorite','-'));
        iPHP::callback(array('user','update_count'),array($uid,'favorite','-'));
        iPHP::callback(array('favorite','update_count'),array($uid,'count','-'));
        iUI::code(1,0,0,'json');
    }
    /**
     * [ACTION_add 添加到收藏夹]
     */
    public function ACTION_add(){
        $this->__login();

        $uid     = user::$userid;
        $appid   = (int)$_POST['appid'];
        $iid     = (int)$_POST['iid'];
        $cid     = (int)$_POST['cid'];
        $suid    = (int)$_POST['suid'];
        $id      = (int)$_POST['id'];
        $fid     = (int)$_POST['fid'];
        $title   = iSecurity::escapeStr($_POST['title']);
        $url     = iSecurity::escapeStr($_POST['url']);
        $addtime = time();

        $id  = iDB::value("
            SELECT `id` FROM `#iCMS@__favorite_data`
            WHERE `uid`='$uid'
             AND `fid`='$fid'
             AND `url`='$url'
             LIMIT 1
        ");
        $id && iUI::code(0,'iCMS:favorite:failure',0,'json');

        $fields = array('uid', 'appid', 'fid', 'iid', 'url', 'title', 'addtime');
        $data   = compact ($fields);
        $fdid   = iDB::insert('favorite_data',$data);
        if($fdid){
            iPHP::callback(array('apps','update_count'),array($iid,$appid,'favorite','+'));
            iPHP::callback(array('user','update_count'),array($fid,'favorite','+'));
            iPHP::callback(array('favorite','update_count'),array($uid,'count','+'));
            iUI::code(1,'iCMS:favorite:success',$fdid,'json');
        }
        iUI::code(0,'iCMS:favorite:error',0,'json');
    }
    /**
     * [ACTION_create 创建新收藏夹]
     */
    public function ACTION_create(){
        $this->__login();

        $uid         = user::$userid;
        $nickname    = user::$nickname;
        $title       = iSecurity::escapeStr($_POST['title']);
        $description = iSecurity::escapeStr($_POST['description']);
        $mode        = (int)$_POST['mode'];

        empty($title) && iUI::code(0,'iCMS:favorite:create_empty',0,'json');
        $fwd  = iPHP::callback(array("filterApp","run"),array(&$title),false);
        $fwd && iUI::code(0,'iCMS:favorite:create_filter',0,'json');

        if($description){
            $fwd  = iPHP::callback(array("filterApp","run"),array(&$description),false);
            $fwd && iUI::code(0,'iCMS:favorite:create_filter',0,'json');
        }

        $max  = iDB::value("SELECT COUNT(id) FROM `#iCMS@__favorite` WHERE `uid`='$uid'");
        $max >=10 && iUI::code(0,'iCMS:favorite:create_max',0,'json');
        $count  = 0;
        $follow = 0;
        $fields = array('uid', 'nickname', 'title', 'description', 'follow', 'count', 'mode');
        $data   = compact ($fields);
        $cid    = iDB::insert('favorite',$data);
        $cid && iUI::code(1,'iCMS:favorite:create_success',$cid,'json');
        iUI::code(0,'iCMS:favorite:create_failure',0,'json');
    }
}
