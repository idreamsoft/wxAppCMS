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
    public static $token  = null;
    public static $nonce  = null;
    public static $id     = null;

    protected static $instance  = null;
    protected static $appSecret = null;
    protected static $API_URL   = 'https://api.weixin.qq.com/sns';

    /**
     * [init 初始化]
     * @param  [type] $config [description]
     * @return [type]         [description]
     */
    public static function init($config=null){
        iPHP_DEBUG && iPHP::$callback['error'] = array(__CLASS__,'error_handler');

        $config===null && $config = self::get_config();
        if($config){
            self::$config    = $config;
            self::$appId     = $config['appid'];
            self::$appSecret = $config['appsecret'];
        }

        self::$token = rawurldecode($_SERVER['HTTP_AUTHORIZATION']);
        self::$nonce = rawurldecode($_GET['_nonce']);
        self::compatible();
	    iUtils::LOG(array($_SERVER['REQUEST_URI'],self::$token,self::$nonce),'wxapp.init');
    }

    public static function get_config($id=null,$vars=null){
        self::$id = (int)$_GET['wxAppId'];
        $id===null && $id = self::$id;

        $data = iDB::row("
            SELECT *
            FROM `#iCMS@__wxapp`
            WHERE `id`='".$id."'
            LIMIT 1;
        ",ARRAY_A);

        $data['config'] && $data['config'] = json_decode($data['config'],true);
        $data['payment'] && $data['payment'] = json_decode($data['payment'],true);

        $apps = new appsApp(__CLASS__);
        $apps->custom_data($data,$vars);
        $apps->hooked($data);

        $data['timestamp'] = time();
        $data['cachetime'] = 3600;

        return $data;
    }
    public static function setting(){
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
    /**
     * [run 接口执行]
     * @return [type] [description]
     */
    public static function run(){
        self::init();
        self::auth() && iCMS::API();
    }
    /**
     * [auth 验证登陆]
     * @return [type] [description]
     */
    public static function auth(){
        //登陆
        if(isset($_POST['LOGIN_CODE'])){
            self::login();
            return false;
        }
        //验证
        $error = array('code' => 0);
        if(self::$token && self::$nonce){
            $tokenArray = explode('#', auth_decode(self::$token));
            if($_GET['wxAppVersion']){
                list($sign,$json) = $tokenArray;
                $user  = json_decode($json,ture);
                $sign  = sha1($sign);
                $_sign = self::$nonce;
            }else{
                //兼容之前版本
                list($sign,$nonce) = explode('#', auth_decode(self::$nonce));
                list($_sign,$openid,$userid,$nickname) = $tokenArray;
                $user = compact(array('openid','userid','nickname'));
            }
            if($sign==$_sign){
                self::setting();
                return wxapp_user::cookie($user['openid'],$user);
            }else{
                $error['code']  = '-99';
                $error['error'] = 'session';
                $error['msg']   = 'session error';
            }
        }else{
            $error['code']  = '-99';
            $error['error'] = 'auth';
            $error['msg']   = 'auth error';
        }
        if($flag){
            return $error;
        }
        iUI::json($error);
        return false;
    }
    /**
     * [login 登陆]
     * @return [type] [description]
     */
    public static function login(){
        $data = self::get_session();
        $user = wxapp_user::data($data);
        if($user){
            $vendor = iPHP::vendor('Token');
            list($sign,$timestamp,$nonce) = $vendor->get();

            $token = auth_encode($sign.'#'.json_encode($user));
            // $nonce = auth_encode($sign.'#'.$nonce);
            $nonce   = sha1($sign);
            $appInfo = self::get_config();
            unset($appInfo['payment'],$appInfo['appsecret'],$appInfo['sapp']);
            unset($user['password'],$user['username']);
            iUI::json(array(
                'code'    => 1,
                'token'   => rawurlencode($token),
                'nonce'   => $nonce,
                'session' => $user,
                'appInfo' => $appInfo
            ));
        }
    }
    public static function get_qrcode_url($id=1,$userid=1){
        $auth = urlencode(auth_encode($id.'#'.$userid));
        return publicApp::url('wxapp','do=qrcode&auth='.$auth);
    }

    public static function get_auth_query($id,$array){
        $auth   = urlencode(auth_encode($id.'#'.implode('#', $array)));
        return 'id='.$id.'&'.http_build_query($array).'&auth='.$auth;
    }
    /**
     * [get_session 获取session]
     * @return [type] [description]
     */
    public static function get_session(){
        empty($_POST['LOGIN_CODE']) && iPHP::error_throw('missing code');

        $url = self::$API_URL.'/jscode2session?grant_type=authorization_code'.
            '&js_code='.$_POST['LOGIN_CODE'].
            '&appid='.self::$appId.
            '&secret='.self::$appSecret;

        $response = iHttp::send($url);

        if($response){
            if($response->errcode||empty($response->openid)){
                iUI::code(0,$response->errmsg.'[errcode:'.$response->errcode.']');
                // iPHP::error_throw($response->errmsg,$response->errcode);
           }
           return $response;
        }
    }

    public static function get_dir($str,$dir='wxa_qrcode'){
        $dir1 = substr($str, 0, 3);
        $dir2 = substr($str, 3, 2);
        return $dir.'/'.$dir1.'/'.$dir2;
    }

    public static function get_wxa_qrcode($param,$show=true,$remake=false){
        $json     = json_encode($param);

        $name     = md5($json);
        $path     = self::get_dir($name,'wxa_qrcode').'/'.$name.'.png';
        $qrurl    = iFS::fp($path, '+http');
        $rootpath = iFS::fp($path, '+iPATH');

        weixin::init(array(
            'appid'     =>self::$appId,
            'appsecret' =>self::$appSecret
        ));
        weixin::$API_URL = 'https://api.weixin.qq.com';
        $url = weixin::url('wxa/getwxacode');
        $response = iHttp::send($url,$json,'raw');
        return $response;
    }
    public static function send_template_message($param){
        $json = json_encode($param);

        weixin::init(array(
            'appid'     =>self::$appId,
            'appsecret' =>self::$appSecret
        ));
        $url = weixin::url('message/wxopen/template/send');
        $response = iHttp::send($url,$json);
        return $response;
    }
    public static function error_handler($html,$type=null){
        $html = html2text($html);
        $html = html2js($html);
        iUI::code(0,$html);
    }
    /**
     * [compatible 兼容处理]
     * @return [type] [description]
     */
    public static function compatible(){
        iDevice::$IS_IDENTITY_URL = false;
        iDevice::$config['callback']['router'] = array(__CLASS__,'compatible_iDevice_router');

        iURL::$callback['router'] = array(
            'rewrite' =>false,
            'data'    =>array(__CLASS__,'compatible_iURL_router')
        );
        iURL::$callback['url'] = array(
            'rule' =>'{PHP}',
            'data' =>array(__CLASS__,'compatible_iURL_url')
        );
        user::$callback['info'] = function(&$info){
            unset($info['at'],$info['link']);
        };
    }
    public static function compatible_iDevice_router($array){
        $search  = array(iCMS::$config['router']['url'],"{P}",'category.php');
        $replace = array('/pages','','category/category');
        $array   = str_replace($search,$replace,$array);
        return $array;
    }
    public static function compatible_iURL_router(&$url){
        // $search  = array(iCMS::$config['router']['public'],'/api.php');
        // $replace = array('/pages');
        // $url     = str_replace($search,$replace,$url);

        $parse   = parse_url($url);
        parse_str($parse['query'], $output);
        $url= $parse['path'].'/'.$output['app'].'/'.$output['do'];
        unset($output['app'],$output['do']);
        $url.='?'.http_build_query($output);
    }
    public static function compatible_iURL_url(&$i){
        self::__iurl_path($i->href);
        $i->pageurl && self::__iurl_path($i->pageurl);
    }

    public static function __iurl_path(&$url){
        $parse = parse_url($url);
        // $path  = str_replace('.php','',$parse['path']);
        $fi    = iFS::name($parse['path']);
        $name  = $fi['name'];
        $query = $parse['query'];
        $query && $query='?'.$query;
        $path  = "/pages/{$name}/{$name}";
        $url   = $path.$query;
    }
}
