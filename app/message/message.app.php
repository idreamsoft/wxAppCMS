<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

class messageApp {
    public $methods = array('iCMS','pm','manage');

    public static function API_iCMS() {
    }
    public static function set_read($id,$userid) {
        if($id && $userid){
           $readtime = time();
            return iDB::query("
                UPDATE `#iCMS@__message`
                SET `readtime` ='{$readtime}'
                WHERE (`userid` = '{$userid}' OR (`userid` = '".message::SYS_UID."' AND `friend` = '{$userid}'))
                AND `id`='{$id}';
            ");
        }
    }
    public static function set_status($id,$userid,$friend=0,$status=0) {
        if ($friend) {
            return iDB::query("
                UPDATE `#iCMS@__message`
                SET `status` ='{$status}'
                WHERE `userid` = '{$userid}'
                AND `friend`='{$friend}';
            ");
        } else{
            $id && iDB::query("
                UPDATE `#iCMS@__message`
                SET `status` ='{$status}'
                WHERE (`userid` = '{$userid}' OR (`userid` = '".message::SYS_UID."' AND `friend` = '{$userid}'))
                AND `id`='{$id}';
            ");
        }
    }
    public static function API_manage() {
        $act = $_POST['act'];
        $id  = (int) $_POST['id'];
        if ($act == "read") {
            $id OR iUI::code(0, 'iCMS:error', 0, 'json');
            self::set_read($id,user::$userid);
            iUI::code(1, 0, 0, 'json');
        }
        if ($act == "del") {
            $id OR iUI::code(0, 'iCMS:error', 0, 'json');
            $user = (int) $_POST['user'];
            self::set_status($id,user::$userid,$user);
            iUI::code(1, 0, 0, 'json');
        }
    }
    public function ACTION_pm() {
        user::get_cookie() OR iUI::code(0, 'iCMS:!login', 0, 'json');

        $receiv_uid  = (int) $_POST['uid'];
        $receiv_name = iSecurity::escapeStr($_POST['name']);
        $content     = iSecurity::escapeStr($_POST['content']);

        $receiv_uid OR iUI::code(0, 'iCMS:error', 0, 'json');
        $content OR iUI::code(0, 'iCMS:pm:empty', 0, 'json');

        $send_uid  = user::$userid;
        $send_name = user::$nickname;

        $setting = (array)user::value($receiv_uid,'setting');
        if($setting['inbox']['receive']=='follow'){
            if($mid){
                $mid = iSecurity::escapeStr($_POST['mid']);
                $mid = auth_decode($mid);
                // $row = iDB::row("SELECT `send_uid`,`receiv_uid` FROM `#iCMS@__message` where `id`='$mid'");
                $muserid = iDB::value("SELECT `userid` FROM `#iCMS@__message` where `id`='$mid'");
            }
            if($muserid!=user::$userid){
                $check = user::follow($receiv_uid, $send_uid);
                $check OR iUI::code(0, 'iCMS:pm:nofollow', 0, 'json');
            }
        }

        $fields = array('send_uid', 'send_name', 'receiv_uid', 'receiv_name', 'content');
        $data = compact($fields);
        message::send($data, 1);
        iUI::code(1, 'iCMS:pm:success', $id, 'json');
    }
    public static function _count($userid=null) {
        return iDB::value("
            SELECT count(id)  FROM `#iCMS@__message`
            WHERE (`userid` = '{$userid}' OR (`userid` = '".message::SYS_UID."' AND `friend` = '{$userid}'))
            AND `readtime` ='0' AND `status` ='1'
        ");
    }
}
