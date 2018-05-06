<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) iiiPHP.com. All rights reserved.
 *
 * @author iPHPDev <master@iiiphp.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.1.0
 */
/**
 * 多终端适配
 */
class iDevice {
    public static $config   = null;
    public static $callback   = array();

    public static $domain       = null;
    public static $router_url   = null;

    public static $device_name  = null;
    public static $device_tpl   = null;
    public static $device_index = null;

    public static $IS_MOBILE = false;
    public static $IS_IDENTITY_URL = true;

    public static function init($config=null,$common=array()) {
        self::$router_url = iPHP_URL;
        self::$config = $config;
        //有设置其它设备
        if(self::$config['device']){
            iSecurity::getGP('device')&& $device = self::check('device');   //判断指定设备
            empty($device)    && $device = self::check('domain');   //无指定设备 判断域名模板
            empty($device)    && $device = self::check('ua');       //无指定域名 判断USER_AGENT
            $device && list($device_name, $device_tpl,$device_index,self::$domain) = $device;
        }

        self::$IS_MOBILE = false;
        if (empty($device_tpl)) {
            //检查是否移动设备 USER_AGENT 或者 域名
            $is_m_domain = (
                self::$config['mobile']['domain']==iPHP_REQUEST_HOST
                &&
                self::$config['mobile']['domain']!=self::$router_url
            );
            if (self::agent(self::$config['mobile']['agent'])||$is_m_domain) {
                self::$IS_MOBILE = true;
                $device_name  = 'mobile';
                $device_tpl   = self::$config['mobile']['tpl'];
                $device_index = self::$config['mobile']['index'];
                self::$domain = self::$config['mobile']['domain'];
            }
        }

        if (empty($device_tpl)) {
            $device_name  = 'desktop';
            $device_tpl   = self::$config['desktop']['tpl'];
            $device_index = self::$config['desktop']['index'];
            self::$domain = self::$router_url;
        }

        self::$device_name  = $device_name;
        self::$device_tpl   = $device_tpl;
        self::$device_index = $device_index;

        self::$IS_IDENTITY_URL = (self::$domain == self::$router_url);

        // define('iPHP_DEFAULT_TPL', $device_tpl);
        // define('iPHP_INDEX_TPL', $device_index);
        // define('iPHP_DEVICE', $device_name);

        // return array($device_name, $device_tpl,$device_index);

        // self::$IS_IDENTITY_URL OR self::router($config['router']);
        // self::$IS_IDENTITY_URL OR self::router($config['FS']);

        $common['redirect'] && self::redirect();
    }
    public static function identity(&$array) {
        self::$IS_IDENTITY_URL OR self::router($array);
    }
    public static function domain(&$urls=array()) {
        if(self::$config['device']){
            foreach (self::$config['device'] as $key => $value) {
                if($value['domain']){
                    $name = trim($value['name']);
                    $urls[$name] = $value['domain'];
                }
            }
        }
        return $urls;
    }
    public static function router(&$router,$deep=false) {
        if(is_array($router) && $deep){
            $router = array_map(array('iDevice','router'), $router);
        }else{
            if(self::$config['callback']['router']){
                $router = iPHP::callback(self::$config['callback']['router'],array($router));
            }else{
                $router = str_replace(self::$router_url, self::$domain, $router);
            }
        }
        return $router;
    }
    //所有设备网址
    public static function urls($array) {
        $array = (array)$array;
        $urls = array();
        if($array){
            $iurl = array(
                'url' => $array['href']
            );
            $array['pageurl'] && $iurl['pageurl'] = $array['pageurl'];

            if(self::$config['desktop']['domain']){
                $urls['desktop'] = str_replace(self::$domain, self::$config['desktop']['domain'], $iurl);
            }
            if(self::$config['mobile']['domain']){
                $urls['mobile'] = str_replace(self::$domain, self::$config['mobile']['domain'], $iurl);
            }
            if(self::$config['device'])foreach (self::$config['device'] as $key => $value) {
                if($value['domain']){
                    $name = trim($value['name']);
                    $urls[$name] = str_replace(self::$domain, $value['domain'], $iurl);
                }
            }
        }
        return $urls;
    }

    private static function redirect(){
        if(stripos(iPHP_REQUEST_URL, self::$domain) === false && !iPHP_SHELL){
            $redirect_url = str_replace(iPHP_REQUEST_HOST,self::$domain, iPHP_REQUEST_URL);
            header("Expires:1 January, 1970 00:00:01 GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            header("X-REDIRECT-REF: ".iPHP_REQUEST_URL);
            header("X-iPHP-DOMAIN: ".self::$domain);
            header("X-REDIRECT-URL: ".$redirect_url);
            iPHP::http_status(301);
            iPHP::redirect($redirect_url);
        }
    }
    private static function check($flag = false) {
        foreach ((array) self::$config['device'] as $key => $device) {
            if ($device['tpl']) {
                $check = false;
                if ($flag == 'ua') {
                    $device['ua'] && $check = self::agent($device['ua']);
                } elseif ($flag == 'device') {
                    $_device = iSecurity::getGP('device');
                    if ($device['ua'] == $_device || $device['name'] == $_device) {
                        $check = true;
                    }
                } elseif ($flag == 'domain') {
                    if ($device['domain']==iPHP_REQUEST_HOST && empty($device['ua'])) {
                        $check = true;
                    }
                }
                if ($check) {
                    return array($device['name'], $device['tpl'], $device['index'], $device['domain']);
                }
            }
        }
    }
    private static function agent($user_agent) {
        $user_agent = str_replace(',','|',preg_quote($user_agent,'/'));
        return ($user_agent && preg_match('@'.$user_agent.'@i',$_SERVER["HTTP_USER_AGENT"]));
    }
}
