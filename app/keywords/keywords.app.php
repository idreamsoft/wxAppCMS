<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class keywordsApp {
    public $methods = array('iCMS');
    const CACHE_KEY = 'keywords';

    public function do_iCMS(){}
    public function API_iCMS(){}

    /**
     * [内链替换]
     * @param [type] $content [参数]
     * @return [string]       [返回替换过的内容]
     */
    public static function HOOK_run($content) {
        if (iCMS::$config['keywords']['limit'] == 0) {
            return $content;
        }
        $array = iCache::get(keywordsApp::CACHE_KEY);
        if($array){
            return self::replace($array,stripslashes($content),iCMS::$config['keywords']['limit']);
        }
        return $content;
    }
    public static function replace($array, $content, $limit='-1') {
        preg_match_all ("/<a[^>]*?>(.*?)<\/a>/si", $content, $matches);//链接不替换
        $linkArray  = array_unique($matches[0]);
        $linkArray && $linkflip = array_flip($linkArray);
        foreach((array)$linkflip AS $linkHtml=>$linkkey){
            $linkA[$linkkey]='@L_'.rand(1,1000).'_'.$linkkey.'@';
        }
        $content = str_replace($linkArray,$linkA,$content);

        preg_match_all ("/<[\/\!]*?[^<>]*?>/si", $content, $matches);
        $htmArray   = (array)array_unique($matches[0]);
        $htmArray && $htmflip = array_flip($htmArray);
        foreach((array)$htmflip AS $kHtml=>$vkey){
            $htmA[$vkey]="@H_".rand(1,1000).'_'.$vkey.'@';
        }
        $content = str_replace($htmArray,$htmA,$content);

        // constructing mask(s)...
        foreach ((array)$array as $k=>$v) {
            $search[$k]   = '@' .preg_quote($v[0],'@') . '@i';
            $replace[$k] = "@R_".rand(1,1000).'_'.$k.'@';
            $replaceArray[$k]  = $v[1];
        }

        $content = preg_replace($search, $replace, $content, $limit);
        $content = str_replace($replace,$replaceArray,$content);
        $content = str_replace($htmA,$htmArray,$content);
        $content = str_replace($linkA,$linkArray,$content);
        unset($linkArray,$linkflip,$linkA);
        unset($htmArray,$htmflip,$htmA);
        unset($replace,$replaceArray);
        unset($search,$matches);
        return $content;
    }
}
