<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
defined('iPHP') OR exit('What are you doing?');

class favoriteFunc{
	public static function favorite_list($vars=null){
		$maxperpage = isset($vars['row'])?(int)$vars['row']:"10";
		$where_sql  = "WHERE 1=1 ";
		isset($vars['userid'])&& $where_sql .= " AND `uid`='".(int)$vars['userid']."' ";
		isset($vars['id'])   && $where_sql .= " AND `id`='".(int)$vars['id']."' ";
		isset($vars['mode'])  && $where_sql .= " AND `mode`='".(int)$vars['mode']."'";
		isset($vars['appid']) && $where_sql .= " AND `appid`='".(int)$vars['appid']."' ";

		$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;

		$vars['id'] && $where_sql .= iSQL::in($vars['id'], 'id');
		$vars['id!'] && $where_sql .= iSQL::in($vars['id!'], 'id', 'not');
		$by=$vars['by']=="ASC"?"ASC":"DESC";
		switch ($vars['orderby']) {
			case 'hot':
				$order_sql = " ORDER BY `count` $by";
				break;
			default: $order_sql = " ORDER BY `id` $by";
		}

		$md5	= md5($where_sql.$order_sql);
		$offset	= 0;
		if($vars['page']){
			$total	= iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__favorite` {$where_sql}",null,iCMS::$config['cache']['page_total']);
			iView::assign("fav_total",$total);
	        $multi	= iUI::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iUI::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
	        $offset	= $multi->offset;
		}
		if($vars['cache']){
			$cache_name = iPHP_DEVICE.'/favorite/'.$md5."/".(int)$GLOBALS['page'];
			$resource   = iCache::get($cache_name);
		}
		if(empty($resource)){
			$rs  = iDB::all("SELECT * FROM `#iCMS@__favorite` {$where_sql} {$order_sql} LIMIT {$offset},{$maxperpage}");
			$resource = array();
			if($rs)foreach ($rs as $key => $value) {
				$value['url']  = iURL::router(array('favorite:id',$value['id']));
				$vars['user'] && $value['user'] = user::info($value['uid'],$value['nickname']);
				if(isset($vars['loop'])){
					$resource[$key] = $value;
				}else{
					$resource[$value['id']]=$value;
				}
			}
			$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
		}
		return $resource;
	}
	public static function favorite_data($vars=null){
		$maxperpage = isset($vars['row'])?(int)$vars['row']:"10";
		$where_sql  = "WHERE 1=1 ";
		isset($vars['userid'])&& $where_sql .= " AND `uid`='".(int)$vars['userid']."' ";
		$vars['fid']          && $where_sql .= " AND `fid`='".(int)$vars['fid']."' ";
		isset($vars['appid']) && $where_sql .= " AND `appid`='".(int)$vars['appid']."' ";

		$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;

		$vars['id'] && $where_sql .= iSQL::in($vars['id'], 'id');
		$vars['id!'] && $where_sql .= iSQL::in($vars['id!'], 'id', 'not');
		$by=$vars['by']=="ASC"?"ASC":"DESC";
		switch ($vars['orderby']) {
			default: $order_sql = " ORDER BY `id` $by";
		}

		$md5	= md5($where_sql.$order_sql);
		$offset	= 0;
		if($vars['page']){
			$total	= iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__favorite_data` {$where_sql}",null,iCMS::$config['cache']['page_total']);
			iView::assign("fav_data_total",$total);
	        $multi	= iUI::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iUI::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
	        $offset	= $multi->offset;
		}
		if($vars['cache']){
			$cache_name = 'favorite_data/'.$md5."/".(int)$GLOBALS['page'];
			$resource   = iCache::get($cache_name);
		}
		if(empty($resource)){
			$resource  = iDB::all("SELECT * FROM `#iCMS@__favorite_data` {$where_sql} {$order_sql} LIMIT {$offset},{$maxperpage}");
			if($resource)foreach ($resource as $key => $value) {
				$value['param'] = array(
					"id"    => $value['id'],
					"fid"   => $value['fid'],
					"appid" => $value['appid'],
					"iid"   => $value['iid'],
					"uid"   => $value['uid'],
					"title" => $value['title'],
					"url"   => $value['url'],
				);
				$resource[$key] = $value;
			}
			$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
		}
		return $resource;
	}
}
