<?php
/**
* wxAppCMS - wxApp Content Management System
* Copyright (c) wxAppCMS.com. All rights reserved.
*
* @author wxappcms <master@wxappcms.com>
* @site https://www.wxappcms.com
* @licence https://www.wxappcms.com/LICENSE.html
*/
class wxapp_token{
    public static $token  = null;
    public static $nonce  = null;
    public static $callback  = null;

    /**
     * [run 接口执行]
     * @return [type] [description]
     */
    public static function init($callback=null){
        self::$callback = $callback;
        self::auth() && iCMS::API();
    }

    public static function get(){
        self::$token = rawurldecode($_SERVER['HTTP_AUTHORIZATION']);
        self::$nonce = rawurldecode($_GET['_nonce']);
	    iUtils::LOG(array($_SERVER['REQUEST_URI'],self::$token,self::$nonce),'wxapp_token.get');
    }
    /**
     * [auth 验证登陆]
     * @return [type] [description]
     */
    public static function auth($flag=false){
        self::get();
        //登陆
        if(isset($_POST['LOGIN_CODE'])){
            return self::login();
        }
        //验证
        $error = array('code' => 0);
        if(self::$token && self::$nonce){
            if($_GET['wxAppVersion']){
                $user = self::get_user();
            }else{
                //兼容之前版本
                list($sign,$nonce) = explode('#', auth_decode(self::$nonce));
                list($_sign,$openid,$userid,$nickname) = explode('#', auth_decode(self::$token));
                $user = ($sign==$_sign)?compact(array('openid','userid','nickname')):false;
            }
            if($user){
                if(self::$callback['auth:success']){
                    call_user_func_array(self::$callback['auth:success'],array());
                }
                return wxapp_user::cookie($user['openid'],$user);
            }else{
                $error['code']  = '-99';
                $error['error'] = 'session';
                $error['msg']   = 'session error,用户验证失败，请用户是否存在或小程序APPID、密钥是否正确';
            }
        }else{
            $error['code']  = '-99';
            $error['error'] = 'auth';
            $error['msg']   = 'auth error,token或nonce丢失，请检测服务器配置是否正确';
        }
        if($flag){
            return $error;
        }
        iUI::json($error);
        return false;
    }
    public static function get_user($token=null,$nonce=null){
        empty(self::$token) && self::get();

        $token===null && $token = self::$token;
        $nonce===null && $nonce = self::$nonce;

        list($sign,$json) = explode('#', auth_decode($token));
        $user  = json_decode($json,true);
        $sign  = sha1($sign);
        if($sign==$nonce){
            return $user;
        }
        return false;
    }
    /**
     * [login 登陆]
     * @return [type] [description]
     */
    public static function login(){
        $user = wxapp_user::login();
        if($user){
            list($token,$nonce) = self::create($user);
            unset($user['password'],$user['username']);
            iUI::json(array(
                'code'    => 1,
                'token'   => rawurlencode($token),
                'nonce'   => $nonce,
                'session' => $user,
                'appInfo' => wxapp::appinfo(true),
            ));
        }
    }
    public static function create($data){
        $vendor = iPHP::vendor('Token');
        list($sign,$timestamp,$nonce) = $vendor->get();
        $token   = auth_encode($sign.'#'.json_encode($data));
        $nonce   = sha1($sign);
        return array($token,$nonce);
    }
}
