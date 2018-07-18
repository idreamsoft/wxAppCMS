<?php
/**
* wxAppCMS - wxApp Content Management System
* Copyright (c) wxAppCMS.com. All rights reserved.
*
* @author wxappcms <master@wxappcms.com>
* @site https://www.wxappcms.com
* @licence https://www.wxappcms.com/LICENSE.html
*/
class wxapp{
    public static $config = array();
    public static $appId  = null;
    public static $id     = null;
    public static $appSecret = null;

    protected static $instance  = null;

    /**
     * [init 初始化]
     * @param  [type] $config [description]
     * @return [type]         [description]
     */
    public static function init($config=null){
        iPHP_DEBUG && iPHP::$callback['error'] = array(__CLASS__,'error_handler');
        self::get_config($config);
        wxapp_compatible::init();
    }
    /**
     * [run 接口执行]
     * @return [type] [description]
     */
    public static function run(){
        self::init();
        wxapp_token::init(array(
            'auth:success'=> array('wxapp','setting')
        ));
    }

    public static function set_app($appid,$appsecret=null){
        if($appid && empty($appsecret)){
            $data = wxapp::get($appid,'appid');
            $appsecret = $data['appsecret'];
        }
        self::$appId     = $appid;
        self::$appSecret = $appsecret;
    }
    public static function get_config($data=null,$self=true){
        if(!is_array($data)){
            self::$id = (int)$_GET['wxAppId'];
            $data = self::get(self::$id);
        }
        self::$config = $data;
        self::set_app($data['appid'],$data['appsecret']);
        return $data;
    }
    public static function value($val,$where='id',$field='*'){
        $data = iDB::row("SELECT {$field} FROM `#iCMS@__wxapp` where `$where`='{$val}'",ARRAY_A);
        if($data){
            $data['config'] && $data['config']  = json_decode($data['config'],true);
            $data['payment']&& $data['payment'] = json_decode($data['payment'],true);

            $apps = new appsApp('wxapp');
            $apps->custom_data($data,$vars);
            $apps->hooked($data);
        }
        $data['timestamp'] = time();
        empty($data['config'])&& $data['config'] = array();
        empty($data['meta'])  && $data['meta'] = array();
        return $data;
    }
    public static function get($id,$where='id'){
        $data = iCache::get('wxapp/'.$id);
        empty($data) && $data = wxapp::value($id,$where);
        return $data;
    }
    public static function appinfo($flag=false){
        empty(wxapp::$config) && self::get_config();

        $config = wxapp::$config;
        $config['cachetime'] = 3600;
        $config['title']     = $config['name'];
        unset($config['payment'],$config['account'],$config['appsecret']);
        unset($config['sapp'],$config['tpl'],$config['index']);

        if($flag) return $config;
        iUI::json($config);
    }
    public static function setting($a=null){
        if($_GET['wxAppVersion']){
            // $version = $_GET['wxAppVersion'];
            // if(!preg_match('/^v\d+\.\d+\.\d+$/', $version)){
            //     iUI::code(0,'version error');
            // }
        }

        iView::$config['template'] = array(
            'device' => 'wxapp',//设备
            'dir'    => self::$config['tpl'],   //模板名
            'index'  => self::$config['index'], //模板首页
        );
        iCMS::$config['payment']['wx'] = self::$config['payment'];
    }

    public static function get_qrcode_url($id=1,$userid=1){
        $auth = urlencode(auth_encode($id.'#'.$userid));
        return publicApp::url('wxapp','do=qrcode&auth='.$auth);
    }

    public static function get_auth_query($id,$array){
        $auth   = urlencode(auth_encode($id.'#'.implode('#', $array)));
        return 'id='.$id.'&'.http_build_query($array).'&auth='.$auth;
    }


    public static function error_handler($html,$type=null){
        $html = html2text($html);
        $html = html2js($html);
        iUI::code(0,$html);
    }
}
