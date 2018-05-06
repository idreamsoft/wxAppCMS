<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class plugin_download{
    /**
     * [插件:正文文件下载]
     * @param [type] $content  [参数]
     * @param [type] $resource [返回替换过的内容]
     */
    public static function HOOK($content) {
        plugin::init(__CLASS__);
        preg_match_all('#<a\s*class="attachment".*?href=["|\'](.*?)["|\'].*?</a>#is',$content, $variable);
        foreach ((array)$variable[1] as $key => $path) {
           $urlArray[$key]= filesApp::get_url(basename($path));
        }
        if($urlArray){
            $content = str_replace($variable[1], $urlArray, $content);
        }
        return $content;
    }
    public static function markdown($content) {
        preg_match_all('/!\[(.*?)\]\((.+)\)/', $content, $matches);

        foreach ((array)$matches[2] as $key => $url) {
            $path = iFS::fp($url,'-http');
            // $rootpath = iFS::fp($url,'http2iPATH');
            // list($w,$h,$type) = @getimagesize($rootpath);
            // if(empty($type)){
            $ext = iFS::get_ext($path);
            if (!in_array($ext, files::$IMG_EXT)) {
                $name = basename($path);
                $title = trim($matches[1][$key]);
                empty($title) && $title = $name;
                $durl = filesApp::get_url($name);
                $title = iSecurity::escapeStr(addslashes($title));
                $replace[$key] = '<a class="attachment" href="'.$durl.'" target="_blank" title="点击下载['.$title.']">'.$title.'</a>';
                $search [$key] = $matches[0][$key];
            }
        }

        if($replace && $search){
            $content = str_replace($search, $replace, $content);
        }
        return $content;
    }
}
