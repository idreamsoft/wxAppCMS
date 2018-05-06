<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
function iCMS_json($vars){
    $url  = trim($vars['url']);
    $json = trim($vars['json']);
	if(empty($url) && empty($json)){
		return false;
	}
    $hash = md5($json);
    $url && $hash = md5($url);
    $cache_time = isset($vars['time']) ? (int) $vars['time'] : -1;

	if($vars['cache']){
		$cache_name = 'json/'.$hash;
        $vars['page'] && $cache_name.= "/".(int)$GLOBALS['page'];
		$resource = iCache::get($cache_name);
        if($resource){
            return $resource;
        }
	}

    $url && $json = iHttp::remote($url);
    $resource = json_decode($json,true);
    if(json_last_error()){
        $error = json_last_error_msg();
        print_r("JSON -{$error}");
        return array();
    }
    $vars['cache'] && iCache::set($cache_name,$resource,$cache_time);

	return $resource;
}
