<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class plugin_baidu{
    /**
     * [百度站长平台 主动推送(实时)]
     */
    public static function ping($urls,$type=null) {
        $site          = iCMS::$config['api']['baidu']['sitemap']['site'];
        $access_token  = iCMS::$config['api']['baidu']['sitemap']['access_token'];
        if(empty($site)||empty($access_token)){
            return false;
        }
        $api ='http://data.zz.baidu.com/urls?site='.$site.'&token='.$access_token;
        $type && $api.='&type='.$type;
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL            => $api,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => implode("\n",(array)$urls),
            CURLOPT_HTTPHEADER     => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $json   = json_decode($result);
        if($json->success){
            return true;
        }
        return $json;
    }

}
