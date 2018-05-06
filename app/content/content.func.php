<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

class contentFunc {
    public static $apps    = null; //应用信息接口
    public static $app     = null;
    public static $table   = null;
    public static $primary = null;
    /**
    * 已在 categoryApp contentApp 设置数据回调,
    * 在应用范围内可以不用设置 app="应用名/应用ID"
    **/
    public static function interfaced($value=null) {
        self::$apps = $value;
    }
    private static function data($vars,$func='list'){
        if((empty($vars['app'])||$vars['app']=='content') && self::$apps){
            $vars['app'] = self::$apps['app'];
        }
        self::$app = apps::get_app($vars['app']);
        if(empty(self::$app)||$vars['app']=='content'){
            iUI::warning('iCMS&#x3a;content&#x3a;'.$func.' 标签出错! 缺少参数"app"或"app"值为空.');
        }
        self::$table   = apps::get_table(self::$app);
        self::$primary = self::$table['primary'];
    }

    public static function content_list($vars) {
        self::data($vars,'list');

        if ($vars['loop'] === "rel" && empty($vars['id'])) {
            return false;
        }
        iMap::reset();

        $resource  = array();
        $map_where = array();
        $status    = '1';
        isset($vars['status']) && $status = (int) $vars['status'];
        $where_sql = "WHERE `status`='{$status}'";
        $vars['call'] == 'user' && $where_sql .= " AND `postype`='0'";
        $vars['call'] == 'admin' && $where_sql .= " AND `postype`='1'";
        $hidden = categoryApp::get_cahce('hidden');
        $hidden && $where_sql .= iSQL::in($hidden, 'cid', 'not');
        $maxperpage = isset($vars['row']) ? (int) $vars['row'] : 10;
        $cache_time = isset($vars['time']) ? (int) $vars['time'] : -1;
        isset($vars['userid']) && $where_sql .= " AND `userid`='{$vars['userid']}'";
        isset($vars['weight']) && $where_sql .= " AND `weight`='{$vars['weight']}'";

        if (isset($vars['ucid']) && $vars['ucid'] != '') {
            $where_sql .= " AND `ucid`='{$vars['ucid']}'";
        }

        if (isset($vars['cid!'])) {
            $ncids = explode(',', $vars['cid!']);
            $vars['sub'] && $ncids += categoryApp::get_cids($ncids, true);
            $where_sql .= iSQL::in($ncids, 'cid', 'not');
        }
        if ($vars['cid'] && !isset($vars['cids'])) {
            $cid = explode(',', $vars['cid']);
            $vars['sub'] && $cid += categoryApp::get_cids($cid, true);
            $where_sql .= iSQL::in($cid, 'cid');
        }
        if (isset($vars['cids']) && !$vars['cid']) {
            $cids = explode(',', $vars['cids']);
            $vars['sub'] && $cids += categoryApp::get_cids($vars['cids'], true);

            if ($cids) {
                iMap::init('category', self::$app['id'],'cid');
                $map_where += iMap::where($cids);
            }
        }
        if (isset($vars['pid']) && !isset($vars['pids'])) {
            iSQL::$check_numeric = true;
            $where_sql .= iSQL::in($vars['pid'], 'pid');
        }
        if(isset($vars['pid!'])){
            iSQL::$check_numeric = true;
            $where_sql.= iSQL::in($vars['pid!'],'pid','not');
        }
        if (isset($vars['pids']) && !isset($vars['pid'])) {
            iMap::init('prop', self::$app['id'],'pid');
            $map_where += iMap::where($vars['pids']);
        }

        if (isset($vars['tids'])) {
            iMap::init('tag', self::$app['id'],'tags');
            $map_where += iMap::where($vars['tids']);
        }
        if ($vars['keywords']) {
            if (strpos($vars['keywords'], ',') === false) {
                $vars['keywords'] = str_replace(array('%', '_'), array('\%', '\_'), $vars['keywords']);
                $where_sql .= " AND CONCAT(title) like '%" . addslashes($vars['keywords']) . "%'";
            } else {
                $pieces   = explode(',', $vars['keywords']);
                $pieces   = array_filter ($pieces);
                $pieces   = array_map('addslashes', $pieces);
                $keywords = implode('|', $pieces);
                $where_sql.= " AND CONCAT(title) REGEXP '$keywords' ";
            }
        }

        $vars['id'] && $where_sql .= iSQL::in($vars['id'], 'id');
        $vars['id!'] && $where_sql .= iSQL::in($vars['id!'], 'id', 'not');
        $by = $vars['by'] == "ASC" ? "ASC" : "DESC";

        switch ($vars['orderby']) {
            case "id":       $order_sql = " ORDER BY `id` $by"; break;
            case "hot":      $order_sql = " ORDER BY `hits` $by"; break;
            case "week":     $order_sql = " ORDER BY `hits_week` $by"; break;
            case "month":    $order_sql = " ORDER BY `hits_month` $by"; break;
            case "comment":  $order_sql = " ORDER BY `comments` $by"; break;
            case "pubdate":  $order_sql = " ORDER BY `pubdate` $by"; break;
            case "sort": $order_sql = " ORDER BY `sortnum` $by"; break;
            case "weight":   $order_sql = " ORDER BY `weight` $by"; break;
            default:$order_sql = " ORDER BY `id` $by";
        }
        isset($vars['startdate']) && $where_sql .= " AND `pubdate`>='" . strtotime($vars['startdate']) . "'";
        isset($vars['enddate']) && $where_sql .= " AND `pubdate`<='" . strtotime($vars['enddate']) . "'";
        isset($vars['where']) && $where_sql .= $vars['where'];

        if ($map_where) {
            $map_sql = iSQL::select_map($map_where, 'join');
            //join
            //empty($vars['cid']) && $map_order_sql = " ORDER BY map.`iid` $by";
            $map_table = 'map';
            $vars['map_order_table'] && $map_table = $vars['map_order_table'];
            $map_order_sql = " ORDER BY {$map_table}.`iid` $by";

            $where_sql .= ' AND ' . $map_sql['where'];
            $where_sql = ",{$map_sql['from']} {$where_sql} AND `".self::$table['table']."`.`id` = {$map_table}.`iid`";
            //derived
            // $where_sql = ",({$map_sql}) map {$where_sql} AND `id` = map.`iid`";
        }
        $offset = 0;
        $limit = "LIMIT {$maxperpage}";
        if ($vars['page']) {
            $total_type = $vars['total_cache'] ? 'G' : null;
            $total      = iCMS::page_total_cache("SELECT count(*) FROM ".self::$table['table']." {$where_sql}", $total_type,iCMS::$config['cache']['page_total']);
            $pagenav    = isset($vars['pagenav']) ? $vars['pagenav'] : "pagenav";
            $pnstyle    = isset($vars['pnstyle']) ? $vars['pnstyle'] : 0;
            $multi      = iUI::page(array('total_type' => $total_type, 'total' => $total, 'perpage' => $maxperpage, 'unit' => iUI::lang('iCMS:page:list'), 'nowindex' => $GLOBALS['page']));
            $offset     = $multi->offset;
            $limit      = "LIMIT {$offset},{$maxperpage}";
            iView::assign(self::$app['app']."_list_total", $total);
        }
        //随机特别处理
        if ($vars['orderby'] == 'rand') {
            $ids_array = iSQL::get_rand_ids(self::$table['table'], $where_sql, $maxperpage, 'id');
            if ($map_order_sql) {
                $map_order_sql = " ORDER BY `".self::$table['table']."`.`id` $by";
            }
        }
        $hash = md5($where_sql . $order_sql . $limit);
        if ($offset) {
            if ($vars['cache']) {
                $map_cache_name = iPHP_DEVICE . '/'.self::$app['app'].'_page/' . $hash;
                $ids_array = iCache::get($map_cache_name);
            }
            if (empty($ids_array)) {
                $ids_order_sql = $map_order_sql ? $map_order_sql : $order_sql;
                $ids_array = iDB::all("SELECT `".self::$table['table']."`.`id` FROM `".self::$table['table']."` {$where_sql} {$ids_order_sql} {$limit}");
                $vars['cache'] && iCache::set($map_cache_name, $ids_array, $cache_time);
            }
        } else {
            if ($map_order_sql) {
                $order_sql = $map_order_sql;
            }
        }
        if ($ids_array) {
            $ids = iSQL::values($ids_array);
            $ids = $ids ? $ids : '0';
            $where_sql = "WHERE `".self::$table['table']."`.`id` IN({$ids})";
            $limit = '';
        }
        if ($vars['cache']) {
            $cache_name = iPHP_DEVICE . '/'.self::$app['app'].'/' . $hash;
            $resource = iCache::get($cache_name);
        }

        if (empty($resource)) {
            $resource = iDB::all("SELECT `".self::$table['table']."`.* FROM `".self::$table['table']."` {$where_sql} {$order_sql} {$limit}");
            $resource = contentFunc::content_array($vars, $resource);
            $vars['cache'] && iCache::set($cache_name, $resource, $cache_time);
        }
        return $resource;
    }
    public static function content_prev($vars) {
        $vars['order'] = 'p';
        return contentFunc::content_next($vars);
    }
    public static function content_next($vars) {
        self::data($vars,'next');

        empty($vars['order']) && $vars['order'] = 'n';

        $cache_time = isset($vars['time']) ? (int) $vars['time'] : -1;
        if (isset($vars['cid'])) {
            $sql = " AND `cid`='{$vars['cid']}' ";
        }
        if ($vars['order'] == 'p') {
            $sql .= " AND `id` < '{$vars['id']}' ORDER BY id DESC LIMIT 1";
        } else if ($vars['order'] == 'n') {
            $sql .= " AND `id` > '{$vars['id']}' ORDER BY id ASC LIMIT 1";
        }
        $hash = md5($sql);
        if ($vars['cache']) {
            $cache = iPHP_DEVICE . '/'.self::$app['app'].'/' . $hash;
            $array = iCache::get($cache);
        }
        if (empty($array)) {
            $rs = iDB::row("SELECT * FROM `".self::$table['table']."` WHERE `status`='1' {$sql}");
            if ($rs) {
                $category = categoryApp::get_cahce_cid($rs->cid);
                $array = array(
                    'title' => $rs->title,
                    'url'   => iURL::get(self::$app['app'], array((array) $rs, $category))->href,
                );
            }
            $vars['cache'] && iCache::set($cache, $array, $cache_time);
        }
        return $array;
    }
    private static function content_array($vars, $variable) {
        $resource = array();
        if ($variable) {
            $contentApp = new contentApp(self::$app);
            if($vars['data']){
                $idArray = iSQL::values($variable,'id','array',null);
                $idArray && $content_data = (array)$contentApp->data($idArray);
                unset($idArray);
            }
            if($vars['meta']){
                $idArray = iSQL::values($variable,'id','array',null);
                $idArray && $meta_data = (array)apps_meta::data(self::$app['app'],$idArray);
                unset($idArray);
            }
            if($vars['tags']){
                $tagArray = iSQL::values($variable,'tags','array',null,'id');
                $tagArray && $tags_data = (array)tagApp::multi_tag($tagArray);
                unset($tagArray);
                $vars['tags'] = false;
            }
            foreach ($variable as $key => $value) {
                $value = $contentApp->value($value,$vars);

                if ($value === false) {
                    continue;
                }
                if(($vars['data']) && $content_data){
                    $value['data']  = (array)$content_data[$value['id']];
                }

                if($vars['tags'] && $tags_data){
                    $value+= (array)$tags_data[$value['id']];
                }
                if($vars['meta'] && $meta_data){
                    $value+= (array)$meta_data[$value['id']];
                }

                if ($vars['page']) {
                    $value['page'] = $GLOBALS['page'] ? $GLOBALS['page'] : 1;
                    $value['total'] = $total;
                }
                $resource[$key] = $value;
            }
        }
        return $resource;
    }
}
