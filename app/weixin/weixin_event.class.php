<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class weixin_event {
    public static $is_send = false;
    public static $instance = null;
    public static $callback = null;

    public static function init($obj){
        self::$instance = $obj;
    }
    public static function scancode_push($data){
    }
    public static function scancode_waitmsg($data){
    }
    public static function pic_sysphoto($data){
    }
    public static function pic_photo_or_album($data){
    }
    public static function pic_weixin($data){
    }
    /**
     * [上报地理位置]
     */
    public static function location_select($data){
    }
    public static function media_id($data){
    }
    public static function view_limited($data){
    }
    /**
     * [跳转URL]
     */
    public static function view($data){
    }
    /**
     * [点击事件]
     */
    public static function click($data){
        $eventkey = $data['EventKey'];
        self::get($data['EventKey'],'click',null);
    }
    /**
     * [关注]
     */
    public static function subscribe(){
        $event = self::get_data(array(
            'eventype'=>'subscribe'
        ));
        $event && self::send($event);
    }
    /**
     * [取消关注]
     */
    public static function unsubscribe(){
        $event = self::get_data(array(
            'eventype'=>'unsubscribe'
        ));
        $event && self::send($event);
    }
    /**
     * [关键词]
     */
    public static function msg_text($data){
        $content = trim($data['Content']);
        self::get($content);

        if (in_array($content,array("1", "2", "3", "？","?","你好"))) {
            $site_name = addslashes(iCMS::$config['site']['name']);
            $site_desc = addslashes(iCMS::$config['site']['description']);
            $site_key  = addslashes(iCMS::$config['site']['keywords']);
            $site_host = str_replace('http://', '', iCMS_URL);
            self::send($site_name.' ('.$site_host.') '.$site_desc."\n回复:".$site_key.' 将会收到我们最新为您准备的信息');
        }
    }
    public static function get($eventkey=null,$eventype='keyword',$operator='eq',$ret=null){
        $where = array(
            // 'operator' =>'eq', //完全匹配模式
            'eventype' =>$eventype,
            'eventkey' =>$eventkey,
        );
        $operator && $where['operator'] = $operator;
        $event = self::get_data($where);
        // var_dump($where,$event,iDB::$last_query);
        $event && self::send($event);

        if($ret){
            return;
        }
        //所有关键词
        $event = iDB::all("
            SELECT `msg`,`operator`,`eventkey`,`msgtype`
            FROM `#iCMS@__weixin_event`
            WHERE `eventype`='".$eventype."'
            AND `operator`!='eq'
            AND `appid` = '".weixin::$appid."'
            AND `status` = '1'
            ORDER BY `id` DESC
        ");

        if($event)foreach ($event as $key => $value) {
            $value['msg'] = self::_msg_decode($value['msg']);
            if($value['operator']=='in'){
                if (stripos($eventkey, $value['eventkey']) !== false) {
                    self::send($value);
                }
            }
            if($value['operator']=='re'){
                $value['eventkey'] = str_replace('@', '\@', $value['eventkey']);
                if (preg_match('@'.$value['eventkey'].'@is',$eventkey)) {
                    self::send($value);
                }
            }
        }
    }
    public static function get_data($vars=array(),$field="*",$orderby='id DESC'){
        $_vars = array(
            'appid'  => weixin::$appid,
            'status' => '1'
        );
        $sql = iSQL::where(array_merge($_vars,(array)$vars));
        $sql.= ' ORDER BY '.$orderby;
        $row = iDB::row("
            SELECT {$field}
            FROM `#iCMS@__weixin_event`
            WHERE {$sql} ",ARRAY_A);
        // echo iDB::$last_query;
        if($row){
            $row['msg'] = self::_msg_decode($row['msg']);
            if(self::$callback['get_data']){
                $row = iPHP::callback(
                    self::$callback['get_data'],
                    array($row)
                );
            }
        }
        return $row;
    }

    public static function send($event){
        if(self::$callback['send']){
            return iPHP::callback(
                self::$callback['send'],
                array($event)
            );
        }
        // var_dump($event);
        if(is_array($event)){
            if(isset($event['msgtype']) && $event['msgtype']=='tpl'){
                iView::assign('weixin',self::$instance->DATA);
                iView::display($event['msg']['Tpl']);
                self::$is_send = true;
                exit;
            }
            $msg = $event['msg'];
        }else{
            $msg = $event;
        }
        self::$is_send = true;
        weixin::msg_xml($msg,self::$instance->DATA['FromUserName'],self::$instance->DATA['ToUserName']);
        exit;
    }
    public static function _msg_decode($msg=null){
        $msg && $msg = json_decode($msg,true);
        return $msg;
    }
}
