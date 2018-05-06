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

if(iPHP_DEBUG){
    iUtils::LOG($_SERVER['REQUEST_URI'],'weixin.input');
    iUtils::LOG('RAW','weixin.input');
}

class weixinApp {
    public $methods = array('interface','wxapp');
    public $FromUserName = null;
    public $ToUserName   = null;
    public $encrypt_type = null;
    public $DATA = null;
    public function __construct($config=null) {
        weixin::set_config($config);
    }

    public function API_wxapp(){
        $method = $_GET['method'];
        if (method_exists("weixin_wxapp", $method)) {
            weixin_wxapp::init(weixin::$config['wxapp']);
            weixin_wxapp::$method();
        }
    }
    public function API_interface(){
        iPHP_DEBUG && iPHP::$callback['error'] = array(__CLASS__,'error_handler');

        weixin::checkSignature();

        $signature     = $_GET["signature"];
        $timestamp     = $_GET["timestamp"];
        $nonce         = $_GET["nonce"];
        $openid        = $_GET["openid"];
        $encrypt_type  = $_GET["encrypt_type"];
        $msg_signature = $_GET["msg_signature"];

        $input = null;
        if($encrypt_type=="aes"){
            weixin_crypt::$token     = weixin::$config['token'];
            weixin_crypt::$aeskey    = weixin::$config['AESKey'];
            weixin_crypt::$appId     = weixin::$appid;
            weixin_crypt::$timeStamp = $timestamp;
            weixin_crypt::$nonce     = $nonce;
            $errCode = weixin_crypt::decrypt($msg_signature, $input);
            if ($errCode == 0) {
            } else {
                trigger_error($errCode,E_USER_ERROR);
            }
        }
        $this->DATA = iUtils::INPUT($input);
        if (is_array($this->DATA)){
            $this->insert_api_log();

            $msgType = $this->DATA['MsgType'];
            //接收信息类型
            if($msgType=="event"){//事件
                $method = strtolower($this->DATA['Event']);
            }else{
                $method = 'msg_'.strtolower($msgType);
            }
            //小程序客服信息
            if(weixin::$config['type']=='3'){
                iPHP_DEBUG && iPHP::$callback['error'] = array('weixin_contact','error_handler');
                weixin_contact::init($this);
                $this->call_method('weixin_contact',$method);
            }else{//订阅号 or 服务号
                weixin_event::init($this);
                $this->call_method('weixin_event',$method);

                if(!weixin_event::$is_send){
                    //查找空白事件
                    weixin_event::get('null','keyword','eq',true);
                    //默认回复
                    weixin_event::send('对不起，没找到相关内容[1]');
                }
            }
        }
    }
    public function call_method($class,$method){
        $callback = array($class,$method);
        is_callable($callback) OR trigger_error("$class::$method no found",E_USER_ERROR);
        iPHP::callback($callback,array($this->DATA));
    }
    public function insert_api_log(){
        $data = array(
            'appid'        => weixin::$appid,
            'ToUserName'   => $this->DATA['ToUserName'],
            'FromUserName' => $this->DATA['FromUserName'],
            'CreateTime'   => $this->DATA['CreateTime'],
            'content'      => $this->DATA['Content'],
            'dayline'      => get_date(null,'Y-m-d H:i:s'),
        );
        $array = $this->DATA;
        unset($array['ToUserName'],$array['FromUserName'],$array['CreateTime']);
        $data['content'] = addslashes(json_encode($array));
        iDB::insert('weixin_api_log',$data);
    }
    public static function error_handler($html,$type=null){
        $array = iUtils::INPUT($input);
        $html = html2text($html);
        iUtils::LOG($html,'weixin.error');
        weixin::msg_xml($html,$array['FromUserName'],$array['ToUserName']);
    }
}
