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
	public $methods	= array('add','delete','create','list','data');
    public function __construct() {
        $this->id = (int)$_GET['id'];
    }
    private function __login(){
        user::get_cookie() OR iUI::code(0,'iCMS:!login',0,'json');
    }
    public function API_list(){
        user::get_cookie() OR iUI::code(0,'iCMS:!login',0,'json');
        $array = favoriteFunc::favorite_list(array('userid'=>user::$userid));
        $appid = (int)$_POST['appid'];
        $iid   = (int)$_POST['iid'];
        $cid   = (int)$_POST['cid'];
        $suid  = (int)$_POST['suid'];
        $row   = favorite::data_all(compact("appid","iid"));
        $fids  = array_column($row, 'fid','id');

        if($array)foreach ($array as $key => &$value) {
            $value['favorited'] = false;
            if(array_search($value['id'], $fids)!==false){
                $value['favorited'] = true;
            }
        }
        iUI::json($array);
    }
    public function API_data(){
        user::get_cookie() OR iUI::code(0,'iCMS:!login',0,'json');
        $uid     = user::$userid;
        $fid     = (int)$_POST['fid'];
        $appid   = (int)$_POST['appid'];
        $iid     = (int)$_POST['iid'];
        $cid     = (int)$_POST['cid'];
        $suid    = (int)$_POST['suid'];
        $url     = iSecurity::escapeStr($_POST['url']);

        isset($_POST['appid'])&& $sql.=" AND `appid`='$appid'";
        isset($_POST['iid'])  && $sql.=" AND `iid`='$iid'";
        isset($_POST['fid'])  && $sql.=" AND `fid`='$fid'";
        isset($_POST['url'])  && $sql.=" AND `url`='$url'";

        $array = array();
        $WHERE = " `uid`='$uid' ".$sql;
        $array['favorited']  = iDB::value("
            SELECT `id` FROM `#iCMS@__favorite_data`
            WHERE {$WHERE} LIMIT 1
        ");
        // $array['sql1'] = iDB::$last_query;

        $WHERE = " 1=1 ".$sql;
        $array['count'] = iDB::value("
            SELECT count(id) FROM `#iCMS@__favorite_data`
            WHERE {$WHERE} LIMIT 1
        ");
        // $array['sql2'] = iDB::$last_query;
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

        // if(isset($_POST['fid'])){
        //     empty($fid) && iUI::code(0,'iCMS:error',0,'json');
        // }
        // if(isset($_POST['url']) || empty($id)){
        //     iUI::code(0,'iCMS:error',0,'json');
        // }

        $WHERE = " `uid`='$uid' ";
        isset($_POST['appid'])&& $WHERE.=" AND `appid`='$appid'";
        isset($_POST['fid'])  && $WHERE.=" AND `fid`='$fid'";
        isset($_POST['iid'])  && $WHERE.=" AND `iid`='$iid'";
        isset($_POST['url'])  && $WHERE.=" AND `url`='$url'";

        if($appid && ($iid || $url)){
            iDB::query("
                DELETE
                FROM `#iCMS@__favorite_data`
                WHERE $WHERE
            ");//iUI::code(0,'出错了',iDB::$last_query,'json');

            iPHP::callback(array('apps','update_count'),array($iid,$appid,'favorite','-'));
            iPHP::callback(array('user','update_count'),array($uid,'favorite','-'));
            iPHP::callback(array('favorite','update_count'),array($fid,$uid,'count','-'));
            iUI::code(1,0,0,'json');
        }else{
            iUI::code(0,0,0,'json');
        }
    }
    /**
     * [ACTION_add 添加到收藏夹]
     */
    public function ACTION_add(){
        $this->__login();

        $uid     = user::$userid;
        $iid     = (int)$_POST['iid'];
        $cid     = (int)$_POST['cid'];
        $suid    = (int)$_POST['suid'];
        $id      = (int)$_POST['id'];
        $fid     = (int)$_POST['fid'];
        $appid   = (int)$_POST['appid'];
        $title   = iSecurity::escapeStr($_POST['title']);
        $url     = iSecurity::escapeStr($_POST['url']);
        $addtime = time();

        $WHERE = " `uid`='$uid' ";
        isset($_POST['appid'])&& $WHERE.=" AND `appid`='$appid'";
        isset($_POST['iid'])  && $WHERE.=" AND `iid`='$iid'";
        isset($_POST['fid'])  && $WHERE.=" AND `fid`='$fid'";
        isset($_POST['url'])  && $WHERE.=" AND `url`='$url'";

        $id  = iDB::value("
            SELECT `id` FROM `#iCMS@__favorite_data`
            WHERE {$WHERE} LIMIT 1
        ");
// echo iDB::$last_query;

        $id && iUI::code(0,'iCMS:favorite:failure',0,'json');

        $fields = array('uid', 'appid', 'fid', 'iid', 'url', 'title', 'addtime');
        $data   = compact ($fields);
        $fdid   = iDB::insert('favorite_data',$data);
        if($fdid){
            iPHP::callback(array('apps','update_count'),array($iid,$appid,'favorite','+'));
            iPHP::callback(array('user','update_count'),array($uid,'favorite','+'));
            iPHP::callback(array('favorite','update_count'),array($fid,$uid,'count','+'));
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
