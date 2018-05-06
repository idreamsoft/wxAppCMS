<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class tagFunc{
    public static function tag_list($vars){
        iMap::reset();

    	$where_sql ="WHERE status='1' ";
    	$map_where = array();
        if(isset($vars['rootid'])){
            $where_sql.= " AND `rootid`='".(int)$vars['rootid']."'";
        }
        if(isset($vars['field'])){
            $where_sql.= " AND `field`='".$vars['field']."'";
        }
        if(!isset($vars['tcids']) && isset($vars['tcid'])){
            $where_sql.= iSQL::in($vars['tcid'],'tcid');
        }
        if(isset($vars['tcids']) && !isset($vars['tcid'])){
            iMap::init('category',iCMS_APP_TAG,'cid');
            //$where_sql.= iMap::exists($vars['tcid'],'`#iCMS@__tag`.id'); //map 表大的用exists
            $map_where+=iMap::where($vars['tcid']);
        }
        if(isset($vars['tcid!'])){
            $where_sql.= iSQL::in($vars['tcid!'],'tcid','not');
        }

        if (isset($vars['pid']) && !isset($vars['pids'])) {
            iSQL::$check_numeric = true;
            $where_sql .= iSQL::in($vars['pid'], 'pid');
        }
        if(isset($vars['pid!'])){
            iSQL::$check_numeric = true;
            $where_sql.= iSQL::in($vars['pid!'],'pid','not');
        }

        if(isset($vars['pids']) && !isset($vars['pid'])){
            iMap::init('prop',iCMS_APP_TAG,'pid');
            //$where_sql.= iMap::exists($vars['pids'],'`#iCMS@__tag`.id'); //map 表大的用exists
            $map_where+= iMap::where($vars['pids']);
        }


        if(!isset($vars['cids']) && isset($vars['cid'])){
            $cid = explode(',',$vars['cid']);
            $vars['sub'] && $cid+=categoryApp::get_cids($cid,true);
            $where_sql.= iSQL::in($cid,'cid');
        }
        if(isset($vars['cids']) && !isset($vars['cid'])){
            $cids = explode(',',$vars['cids']);
            $vars['sub'] && $cids+=categoryApp::get_cids($vars['cids'],true);

            if($cids){
                iMap::init('category',iCMS_APP_TAG,'cid');
                $map_where+=iMap::where($cids);
            }
        }
        if(isset($vars['cid!'])){
            $ncids    = explode(',',$vars['cid!']);
            $vars['sub'] && $ncids+=categoryApp::get_cids($ncids,true);
            $where_sql.= iSQL::in($ncids,'cid','not');
        }

        if(isset($vars['keywords'])){//最好使用 iCMS:tag:search
            if(empty($vars['keywords'])) return;

            if(strpos($vars['keywords'],',')===false){
                $vars['keywords'] = str_replace(array('%','_'),array('\%','\_'),$vars['keywords']);
                $where_sql.= " AND CONCAT(tkey,name,seotitle,keywords) like '%".addslashes($vars['keywords'])."%'";
            }else{
                $kws = explode(',',$vars['keywords']);
                foreach($kws AS $kwv){
                    $keywords.= addslashes($kwv)."|";
                }
                $keywords = substr($keywords,0,-1);
                $where_sql.= " AND CONCAT(tkey,name,seotitle,keywords) REGEXP '$keywords' ";
            }
        }
        $vars['id'] && $where_sql .= iSQL::in($vars['id'], 'id');
        $vars['id!'] && $where_sql .= iSQL::in($vars['id!'], 'id', 'not');
        $maxperpage	= isset($vars['row'])?(int)$vars['row']:"10";
    	$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;
    	$by			= $vars['by']=='ASC'?"ASC":"DESC";
    	switch ($vars['orderby']) {
    		case "hot":		$order_sql=" ORDER BY `count` $by";		break;
    		case "new":		$order_sql=" ORDER BY `id` $by";			break;
    		case "sort":	$order_sql=" ORDER BY `sortnum` $by";	break;
    		default:		$order_sql=" ORDER BY `id` $by";
    	}
        if($map_where){
            $map_sql   = iSQL::select_map($map_where);
            $where_sql = ",({$map_sql}) map {$where_sql} AND `id` = map.`iid`";
        }

    	$offset	= 0;
    	$limit  = "LIMIT {$maxperpage}";
    	if($vars['page']){
    		$total	= iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__tag` {$where_sql}",null,iCMS::$config['cache']['page_total']);
    		$multi  = iUI::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iUI::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
    		$offset = $multi->offset;
    		$limit  = "LIMIT {$offset},{$maxperpage}";
            iView::assign("tag_list_total",$total);
    	}

        if($vars['orderby']=='rand'){
            $ids_array = iSQL::get_rand_ids('#iCMS@__tag',$where_sql,$maxperpage,'id');
        }

    	$hash = md5($where_sql.$order_sql.$limit);

    	if($vars['cache']){
    		$cache_name = iPHP_DEVICE.'/tag/'.$hash;
            $vars['page'] && $cache_name.= "/".(int)$GLOBALS['page'];
    		$resource = iCache::get($cache_name);
            if($resource){
                return $resource;
            }
    	}
        if($map_sql || $offset){
            if($vars['cache']){
    			$map_cache_name = iPHP_DEVICE.'/tag_map/'.$hash;
    			$ids_array      = iCache::get($map_cache_name);
            }
            if(empty($ids_array)){
                $ids_array = iDB::all("SELECT `id` FROM `#iCMS@__tag` {$where_sql} {$order_sql} {$limit}");
                $vars['cache'] && iCache::set($map_cache_name,$ids_array,$cache_time);
            }
        }
        if($ids_array){
            $ids       = iSQL::values($ids_array);
            $ids       = $ids?$ids:'0';
            $where_sql = "WHERE `#iCMS@__tag`.`id` IN({$ids})";
            $limit     = '';
        }

    	$resource = iDB::all("SELECT * FROM `#iCMS@__tag` {$where_sql} {$order_sql} {$limit}");
    	if($resource){
            $resource = self::tag_array($vars,$resource);
            $vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
        }
    	return $resource;
    }

    public static function tag_array($vars,$resource=null){
        if($resource===null){
            if(isset($vars['name'])){
                $array = array($vars['name'],'name');
            }else if(isset($vars['id'])){
                $array = array($vars['id'],'id');
            }
            if($array){
                return tagApp::tag($array[0],$array[1],false);
            }else{
                iUI::warning('iCMS&#x3a;tag&#x3a;array 标签出错! 缺少参数"id"或"name".');
            }
        }
        if($resource){
            if($vars['meta']){
                $idArray = iSQL::values($resource,'id','array',null);
                $idArray && $meta_data = (array)apps_meta::data('tag',$idArray);
                unset($idArray);
            }
            foreach ($resource as $key => $value) {
                if($vars['meta'] && $meta_data){
                    $value+= (array)$meta_data[$value['id']];
                }
        		$resource[$key] = tagApp::value($value,$vars);
            }
            $vars['keys'] && iSQL::pickup_keys($resource,$vars['keys'],$vars['is_remove_keys']);
        }
        return $resource;
    }
}
