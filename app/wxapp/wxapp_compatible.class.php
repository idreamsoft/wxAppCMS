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
        if($output['app'] && $output['do']){
            $url= '/pages/'.$output['app'].'/'.$output['do'];
            unset($output['app'],$output['do']);
            $url.='?'.http_build_query($output);
        }else{
            $url  = str_replace('.php','',$parse['path']);
            $url.=http_build_query($output);
        }
    }
    public static function iURL_url(&$i){
        self::iURL_path($i->href);
        $i->pageurl && self::iURL_path($i->pageurl);
    }

    public static function iURL_path(&$url){
        $parse = parse_url($url);
        $fi    = iFS::name($parse['path']);
        $name  = $fi['name'];
        $query = $parse['query'];
        $query && $query='?'.$query;
        $path  = "/pages/{$name}/{$name}";
        $url   = $path.$query;
    }
}
