<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
define('iCMS_WEIXIN_COMPONENT',"https://weixin.icmsdev.com");//iCMS微信第三方平台

class weixin {
    public static $debug     = true;
    public static $component = false;
    public static $token     = null;
    public static $config    = array();
    public static $id        = 0;
    public static $appid     = null;
    public static $appsecret = null;

    public static $API_URL   = 'https://api.weixin.qq.com/cgi-bin';

    protected static $token_cache = 'weixin/token';

    public static function init($c=null){
        empty(self::$config) && self::set_config($c);

        self::$component && self::$API_URL = iCMS_WEIXIN_COMPONENT.'/cgi-bin';

        self::$token_cache = 'weixin/token_'.substr(md5(self::$appid.self::$appsecret), 8,16);
        self::$token===null && self::$token = iCache::get(self::$token_cache);
        self::$token OR self::get_access_token();
    }
    public static function set_config($config=null,$title='weixin'){
        empty($config) && $config = self::get_config();
        empty($config) && trigger_error("{$title} config is missing.",E_USER_ERROR);
        empty($config['appid']) && trigger_error("{$title} appid is missing.",E_USER_ERROR);
        empty($config['appsecret']) && trigger_error("{$title} appsecret is missing.",E_USER_ERROR);

        self::$config    = $config;
        self::$appid     = $config['appid'];
        self::$appsecret = $config['appsecret'];
    }
    public static function get_config($id=null,$field='id'){
        $id===null && $id = (int)$_GET['id'];
        self::$id  && $id = self::$id;
        if(empty($id) && self::$appid){
            $id    = self::$appid;
            $field = 'appid';
        }
        empty($id) && trigger_error("{$field} is missing.",E_USER_ERROR);
        $data = iDB::row("SELECT * FROM `#iCMS@__weixin` WHERE `{$field}`='{$id}' LIMIT 1",ARRAY_A);
        self::process_config($data);
        return $data;
    }
    public static function process_config(&$data,$flag=true){
        if($data){
            $data['menu']   && $data['menu'] = json_decode($data['menu'],true);
            $data['config'] && $data['config'] = json_decode($data['config'],true);
            $data['payment']&& $data['payment'] = json_decode($data['payment'],true);

            if($flag){
                $apps = new appsApp('weixin');
                $apps->custom_data($data);
                $apps->hooked($data);
                unset($data['sapp']);
            }
        }
        return $data;
    }
    public static function get_access_token(){
        $url = self::$API_URL.'/token?grant_type=client_credential'.
        '&appid='.self::$appid.
        '&secret='.self::$appsecret;

        $response = iHttp::send($url);
        if($response->errcode){
            self::error($response);
        }
        self::$token = $response->access_token;
        iCache::set(self::$token_cache,self::$token,$response->expires_in);
    }
    public static function error($e){
        //if(self::$debug){
        trigger_error("errcode:".$e->errcode." errmsg:".$e->errmsg,E_USER_ERROR);
        //}
    }
    public static function url($uri,$query=null){
        $url = self::$API_URL.'/'.$uri.'?access_token='.self::$token;
        self::$component && $url.= '&appid='.self::$appid;
        $query && $url.= '&'.http_build_query((array)$query);
        // self::$debug && var_dump($url);
        return $url;
    }
    public static function setMenu($param=null){
        $param===null && $param = self::$config['menu'];
        $param    = array('button'=>self::cn_urlencode($param));
        $param    = json_encode($param);
        $param    = urldecode($param);
        $url      = self::url('menu/create');
        $response = iHttp::send($url,$param);
        return $response;
    }
    protected static function cn_urlencode($variable){
        foreach ((array)$variable as $i => $param) {
            foreach ((array)$param as $key => $value) {
                if($key=='name'){
                    $value = trim($value);
                    if(empty($value)){
                        unset($variable[$i]);
                        continue;
                    }

                    $variable[$i][$key] = urlencode(trim($value));
                }
                if($key=='sub_button'){
                    $variable[$i][$key] = self::cn_urlencode($value);
                }
            }
        }
        return $variable;
    }
    public static function getMenu(){
        $url      = self::url('menu/get');
        $response = iHttp::send($url);
        // if($response->errcode=="46003"){
        //     return false;
        // }else if($response->errcode){
        //     self::error($response);
        // }
        return $response;
    }
    /**
     * [mediaList 获取素材列表]
     * @param  integer $offset [从全部素材的该偏移位置开始返回，0表示从第一个素材 返回]
     * @param  integer $count  [返回素材的数量，取值在1到20之间]
     * @param  string  $type   [素材的类型，图片(image)、视频(video)、语音 (voice)、图文(news)]
     * @return [array]  [永久图文消息素材列表]
     */
    public static function mediaList($type='news',$offset=0,$count=20){
        $url   = self::url('material/batchget_material');
        $param = array(
            'type'   => $type,
            'offset' => $offset,
            'count'  => $count,
        );
        $cache_name = 'weixin/media_'.$type.'_list_'.$offset.'_'.$count;
        $post_data  = json_encode($param);
        $response   = iCache::get($cache_name);
        if(empty($response)){
            $response = iHttp::send($url,$post_data);
            iCache::set($cache_name,$response,300);
        }else{
            $response = unserialize($response);
        }
        if($response->errcode){
            self::error($response);
        }
        if($response->total_count){
            $media_list_array = array();
            $media_list_array['total_count'] = $response->total_count;
            $media_list_array['item_count']  = $response->item_count;
            $items = array();
            foreach ($response->item as $key => $value) {
                $items[$key]['media_id']    = $value->media_id;
                $items[$key]['name']        = $value->name;
                $items[$key]['url']         = $value->url;
                $items[$key]['update_time'] = $value->update_time;
                if(isset($value->content->news_item)){
                    $media_item = self::media_item($value->content->news_item);
                    $items[$key]['content'] = $media_item;
                    // $items[$key]['name']    = $media_item[0]['title'];
                }
            }
            $media_list_array['items']  = $items;
            return $media_list_array;
        }
        return $response;
    }
    public static function media_item($itemArray){
        $items = array();
        if($itemArray)foreach ($itemArray as $k => $v) {
            $items[$k] = (array)$v;
        }
        return $items;
    }
    public static function qrcode_create($info) {
        $param =  array(
            'expire_seconds' => 2592000,
            'action_name'    => 'QR_LIMIT_STR_SCENE',
            'action_info'    => array('scene'=>
                array(
                    'scene_id'       => '1',
                    'scene_str'      => $info
                )
            )
        );
        $param    = json_encode($param);
        $param    = urldecode($param);
        $url      = self::url('qrcode/create');
        $response = iHttp::send($url,$param);
        return $response;
    }


    public static function msg_xml($content,$FromUserName,$ToUserName){
        $CreateTime = time();
        echo "<xml>";
        echo "<ToUserName><![CDATA[".$FromUserName."]]></ToUserName>";
        echo "<FromUserName><![CDATA[".$ToUserName."]]></FromUserName>";
        echo "<CreateTime>".$CreateTime."</CreateTime>";
        if(is_array($content)){
            foreach ($content as $key => $value) {
                if($key=='Articles'){
                    echo "<MsgType><![CDATA[news]]></MsgType>";
                    echo "<ArticleCount>".count($value)."</ArticleCount>";
                    echo "<Articles>";
                    foreach ($value as $kk => $vv) {
                        echo "<item>";
                        foreach ($vv['item'] as $k => $v) {
                            echo "<{$k}><![CDATA[".$v."]]></{$k}>";
                        }
                        echo "</item>";
                    }
                    echo "</Articles>";
                }else{
                    echo "<MsgType><![CDATA[".strtolower($key)."]]></MsgType>";
                    if(is_array($value)){
                        echo "<{$key}>";
                        foreach ($value as $k => $v) {
                            echo "<{$k}><![CDATA[".$v."]]></{$k}>";
                        }
                        echo "</{$key}>";
                    }else{
                        echo "<Content><![CDATA[".$value."]]></Content>";
                    }
                }
            }
        }else{
            echo "<MsgType><![CDATA[text]]></MsgType>";
            echo "<Content><![CDATA[".$content."]]></Content>";
        }
        echo "</xml>";
        exit;
    }
    public static  function checkSignature(){
        self::$config['token'] OR trigger_error('TOKEN is not defined!',E_USER_ERROR);

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce     = $_GET["nonce"];

        $token  = self::$config['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            $_GET["echostr"] && exit($_GET["echostr"]);
            // return true;
        }else{
            trigger_error('signature is error!',E_USER_ERROR);
            // return false;
        }
    }
}
