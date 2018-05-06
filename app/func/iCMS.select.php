<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
function iCMS_select($vars){
	$sql = rtrim($vars['sql'],';');
	$sql = trim($vars['sql']);
	if(empty($sql) ){
		return false;
	}
	if(stripos($sql, 'select')!==0){
		exit("iCMS:select 只支持 SELECT 语句格式");
	}
	$maxperpage = isset($vars['row']) ? (int) $vars['row'] : 10;
	$cache_time = isset($vars['time']) ? (int) $vars['time'] : -1;

	$limit      = null;
	if($vars['page']){
		if(stripos($sql, 'limit') === false){
			$count_sql = preg_replace('/select(.*?)from/is', 'SELECT count(*) FROM', $sql);
		}else{
			$count_sql = preg_replace('/select(.*?)from(.*?)limit(.*?)$/is', 'SELECT count(*) FROM$2', $sql);
			$sql       = preg_replace('/select(.*?)from(.*?)limit(.*?)$/is', 'SELECT $1 FROM$2', $sql);
		}
		$total	= iCMS::page_total_cache($count_sql,null,iCMS::$config['cache']['page_total']);
		$multi  = iUI::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iUI::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
		$offset = $multi->offset;
		$limit  = "LIMIT {$offset},{$maxperpage}";
		iView::assign("query_total",$total);
	}

	preg_match("/select(.*?)from\s*(.*?)\s+(.*?)$/is", $sql,$match);
	$fields     = $match[1];
	$table_name = $match[2];
	$_sql       = $match[3];

// var_dump($match);

	if(empty($table_name)){
		exit("SQL语句格式不正确");
	}

	$hash = md5($sql.$limit);

	if($vars['cache']){
		$cache_name = iPHP_DEVICE.'/query/'.$hash;
        $vars['page'] && $cache_name.= "/".(int)$GLOBALS['page'];
		$resource = iCache::get($cache_name);
        if($resource){
            return $resource;
        }
	}
    if($offset){
    	if(empty($vars['id'])){
    		exit('分页模式下,请设置主键 id="主键名"');
    	}
    	$primary = $vars['id'];
        if($vars['cache']){
			$ids_cache_name = iPHP_DEVICE.'/query_ids/'.$hash;
			$ids_array      = iCache::get($map_cache_name);
        }
        if(empty($ids_array)){
            $ids_array = iDB::all("SELECT {$primary} FROM {$table_name} {$_sql}");
            $vars['cache'] && iCache::set($ids_cache_name,$ids_array,$cache_time);
        }
    }
    if($ids_array){
        $ids       = iSQL::values($ids_array);
        $ids       = $ids?$ids:'0';
        $where_sql = "WHERE {$table_name}.{$primary} IN({$ids})";
        $limit     = '';
    }
    $sql = "SELECT {$fields} FROM {$table_name} {$_sql} {$limit}";
    if($vars['type']=='row'){
        $resource = iDB::row($sql,ARRAY_A);
    }else if($vars['type']=='value'){
        $resource = iDB::value($sql);
    }else{
        $resource = iDB::all($sql);
    }
	if($resource){
        $vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
    }
	return $resource;
}
