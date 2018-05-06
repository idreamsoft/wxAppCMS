<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class propFunc{
    public static function prop_list($vars){
        $where_sql ="WHERE 1='1' ";
        $map_where = array();
        if(isset($vars['rootid'])){
            $where_sql.= " AND `rootid`='".(int)$vars['rootid']."'";
        }
        if(isset($vars['field'])){
            $where_sql.= " AND `field`='".$vars['field']."'";
        }
        if(isset($vars['appid'])){
            $where_sql.= " AND `appid`='".(int)$vars['appid']."'";
        }

        if($vars['sapp']){
            $where_sql.= " AND `app`='".$vars['sapp']."'";
        }

        if(isset($vars['cid'])){
            $cid = explode(',',$vars['cid']);
            $vars['sub'] && $cid+=categoryApp::get_cids($cid,true);
            $where_sql.= iSQL::in($cid,'cid');
        }

        if(isset($vars['cid!'])){
            $ncids    = explode(',',$vars['cid!']);
            $vars['sub'] && $ncids+=categoryApp::get_cids($ncids,true);
            $where_sql.= iSQL::in($ncids,'cid','not');
        }

        $vars['id'] && $where_sql .= iSQL::in($vars['id'], 'pid');
        $vars['id!'] && $where_sql .= iSQL::in($vars['id!'], 'pid', 'not');
        $maxperpage = isset($vars['row'])?(int)$vars['row']:"10";
        $cache_time = isset($vars['time'])?(int)$vars['time']:-1;
        $by = $vars['by']=='DESC'?"DESC":"ASC";
        switch ($vars['orderby']) {
            // case "hot":     $order_sql=" ORDER BY `count` $by"; break;
            case "new":     $order_sql=" ORDER BY `pid` $by";   break;
            case "sort":    $order_sql=" ORDER BY `sortnum` $by";   break;
            default:        $order_sql=" ORDER BY `sortnum` $by";
        }

        $offset = 0;
        $limit  = "LIMIT {$maxperpage}";
        if($vars['orderby']=='rand'){
            $ids_array = iSQL::get_rand_ids('#iCMS@__prop',$where_sql,$maxperpage,'pid');
        }
        $hash = md5($where_sql.$order_sql.$limit);

        if($vars['cache']){
            $cache_name = iPHP_DEVICE.'/prop/'.$hash;
            $resource = iCache::get($cache_name);
            if($resource){
                return $resource;
            }
        }

        $resource = iDB::all("SELECT * FROM `#iCMS@__prop` {$where_sql} {$order_sql} {$limit}");
        if($resource){
            $resource = self::prop_value($vars,$resource);
            $vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
        }
        return $resource;
    }
	public static function prop_array($vars){
        $field    = $vars['field'];
        $sapp     = $vars['sapp'];
        $variable = propApp::value($field,$sapp);
        $offset = $vars['start']?$vars['start']:0;
		$vars['row'] && $variable = array_slice($variable,$offset, $vars['row']);
        $variable = self::prop_value($vars,$variable);
		return $variable;
	}
    public static function prop_value($vars,$variable){
        foreach ($variable as $key => $value) {
            if($vars['field']){
                $value['url'] = propApp::url($value,$vars['url']);
                $value['link'] = '<a href="'.$value['url'].'" />'.$value['name'].'</a>';
            }else{
                foreach ($value as $k => $v) {
                    $v['url'] = propApp::url($v,$vars['url']);
                    $v['link'] = '<a href="'.$v['url'].'" />'.$v['name'].'</a>';
                    $value[$k] = $v;
                }
            }
            $variable[$key] = $value;
        }
        return $variable;
    }
}
