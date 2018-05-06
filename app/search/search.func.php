<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class searchFunc{
    public static function search_list($vars){
    	$maxperpage = isset($vars['row'])?(int)$vars['row']:"100";
    	$cache_time	= isset($vars['time'])?(int)$vars['time']:"-1";
        $where_sql  = '';

        $vars['id'] && $where_sql .= iSQL::in($vars['id'], 'id');
        $vars['id!'] && $where_sql .= iSQL::in($vars['id!'], 'id', 'not');
    	$by=$vars['by']=="ASC"?"ASC":"DESC";
        switch ($vars['orderby']) {
            case "id":      $order_sql = " ORDER BY `id` $by";      break;
            case "addtime":	$order_sql = " ORDER BY `addtime` $by"; break;
            case "times":   $order_sql = " ORDER BY `times` $by";   break;
            default:        $order_sql = " ORDER BY `id` DESC";
        }
    	if($vars['cache']){
            $cache_name = iPHP_DEVICE.'/search/'.md5($where_sql.$order_sql);
            $resource   = iCache::get($cache_name);
    	}
    	if(empty($resource)){
            $resource = iDB::all("SELECT * FROM `#iCMS@__search_log` {$where_sql} {$order_sql} LIMIT $maxperpage");
            if($resource)foreach ($resource as $key => $value) {
                $value['name']  = $value['search'];
                $value['url']   = searchFunc::search_url(array('query'=>$value['name'],'ret'=>true));
                $resource[$key] = $value;
            }
    		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
    	}
    	return $resource;
    }
    public static function search_url($vars){
        $q = rawurlencode($vars['query']);
        if(empty($q)){
            return;
        }
        $query = array('app'=>'search','q'=>$q);
        if(isset($vars['_app'])){
            $query['app'] = $vars['_app'];
            $query['do']  = 'search';
        }
        $iURL = searchApp::iurl($q,$query,false);
        if($vars['ret']){
            return $iURL->url;
        }
        echo $iURL->url;
    }
}
