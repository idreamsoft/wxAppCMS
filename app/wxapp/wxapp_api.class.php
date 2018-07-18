<?php

class wxapp_api {
    public static $API_URL   = 'https://api.weixin.qq.com/sns';

    public static function get_dir($str,$dir='wxa_qrcode'){
        $dir1 = substr($str, 0, 3);
        $dir2 = substr($str, 3, 2);
        return $dir.'/'.$dir1.'/'.$dir2;
    }

    public static function weixin_init(){
        weixin::init(array(
            'appid'     => wxapp::$appId,
            'appsecret' => wxapp::$appSecret
        ));
    }
    /**
     * [get_session 获取session]
     * @return [type] [description]
     */
    public static function get_session(){
        empty($_POST['LOGIN_CODE']) && iPHP::error_throw('missing code');

        $url = self::$API_URL.'/jscode2session?grant_type=authorization_code'.
            '&js_code='.$_POST['LOGIN_CODE'].
            '&appid='.wxapp::$appId.
            '&secret='.wxapp::$appSecret;

        $response = iHttp::send($url);

        if($response){
            if($response->errcode||empty($response->openid)){
                iUI::code(0,$response->errmsg.'[errcode:'.$response->errcode.']');
                // iPHP::error_throw($response->errmsg,$response->errcode);
           }
           return $response;
        }
    }
    public static function get_wxa_qrcode($param,$show=true,$remake=false){
        $json     = json_encode($param);
        // $name     = md5($json);
        // $path     = self::get_dir($name,'wxa_qrcode').'/'.$name.'.png';
        // $qrurl    = iFS::fp($path, '+http');
        // $rootpath = iFS::fp($path, '+iPATH');

        self::weixin_init();
        weixin::$API_URL = 'https://api.weixin.qq.com';
        $url = weixin::url('wxa/getwxacode');
        $response = iHttp::send($url,$json,'raw');
        return $response;
    }
}
