<?php
/**
* wxAppCMS - wxApp Content Management System
* Copyright (c) wxAppCMS.com. All rights reserved.
*
* @author wxappcms <master@wxappcms.com>
* @site https://www.wxappcms.com
* @licence https://www.wxappcms.com/LICENSE.html
*/
/**
 * [URL兼容处理]
 * @return [type] [description]
 */
class wxapp_compatible{

    public static function init(){
        iDevice::$IS_IDENTITY_URL = false;
        iDevice::$config['callback']['router'] = array(__CLASS__,'iDevice_router');

        iURL::$callback['router'] = array(
            'rewrite' =>false,
            'data'    =>array(__CLASS__,'iURL_router')
        );
        iURL::$callback['url'] = array(
            'rule' =>'{PHP}',
            'data' =>array(__CLASS__,'iURL_url')
        );
        user::$callback['info'] = function(&$info){
            unset($info['at'],$info['link']);
        };
    }
    public static function iDevice_router(&$array){
        $urls = array();iDevice::domain($urls);sort($urls);

        $array   = str_replace($urls,'/pages',$array);
        $search  = array("{P}",'category.php');
        $replace = array('','category/category');
        $array   = str_replace($search,$replace,$array);
        return $array;
    }
    public static function iURL_router(&$url){
        $parse = parse_url($url);
        parse_str($parse['query'], $output);
        if($output['app']){
            $url = self::qs_transform($output);
        }else{
            $url = str_replace('.php','',$parse['path']);
            $url.= http_build_query($output);
        }
    }
    public static function iURL_url(&$i){
        self::iurl_transform($i->href);
        $i->pageurl && self::iurl_transform($i->pageurl);
    }
    public static function iurl_transform(&$url){
        $parse = parse_url($url);
        $query = $parse['query'];
        parse_str($query, $output);
        if($output['app']){
            $url  = self::qs_transform($output);
        }else{
            $fi   = iFS::name($parse['path']);
            $name = $fi['name'];
            $url  = "/pages/{$name}/{$name}";
            $query && $url.='?'.$query;
        }
    }
    public static function qs_transform($output){
        empty($output['do']) && $output['do'] = 'content';
        $path= '/pages/'.$output['app'].'/'.$output['do'];
        unset($output['app'],$output['do']);
        $path.='?'.http_build_query($output);
        return $path;
    }
}
