<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class weixin_contact {
    public static $is_send = false;
    public static $instance = null;
    public static function init($obj){
        self::$instance = $obj;
        weixin_event::$callback['send'] = array(__CLASS__,'send');
        // weixin_event::$callback['get_data'] = array(__CLASS__,'get_data');
    }
    public static function user_enter_tempsession($data){
        $event = weixin_event::get_data(array(
            'eventype'=>'user_enter_tempsession'
        ));
        $event && self::send($event);
    }
    public static function msg_text($data){
        $content = trim($data['Content']);
        weixin_event::get($content,'contact_text');
    }
    /**
     * [image 图片消息]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function msg_image($data){

    }
    public static function msg_miniprogrampage($data){
    }
    // public static function get_data($row){
    //     $row['msgtype'] = str_replace('wxapp-', '', $row['msgtype']);
    //     return $row;
    // }
    public static function typing($openid){
        $param   = array(
            'touser'  => $openid,
            'command' => 'Typing',
        );
        $param    = json_encode($param);
        $url      = weixin::url('message/custom/typing');
        $response = iHttp::send($url,$param);
    }
    public static function send($event){
        weixin::init();
        $openid  = self::$instance->DATA['FromUserName'];
        self::typing($openid);

        if($event){
            $msgtype = str_replace('wxapp-', '', $event['msgtype']);
            $param   = array(
                'touser'  => $openid,
                'msgtype' => $msgtype,
                $msgtype  => $event['msg'][$msgtype]
            );
            $param    = cnjson_encode($param);
            $url      = weixin::url('message/custom/send');
            $response = iHttp::send($url,$param);
            if($response->errcode){
                weixin::error($response);
            }
        }
        if(self::$instance->DATA['Event']!='user_enter_tempsession'){
            self::transfer_customer_service();
        }
    }
    public static function transfer_customer_service(){
        echo iUtils::arrayToXml(array(
            'ToUserName'   =>self::$instance->DATA['FromUserName'],
            'FromUserName' =>self::$instance->DATA['ToUserName'],
            'CreateTime'   =>time(),
            'MsgType'      =>'transfer_customer_service',
        ));
        exit;
    }
    public static function error_handler($html,$type=null){
        $array = iUtils::INPUT($input);
        // $html = html2text($html);
        iUtils::LOG(html2text($html),'weixin_contact.error');
        exit($html);
    }
}
