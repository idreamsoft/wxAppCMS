<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class appsFunc{
    public $params    = array();

    public $appid     = 1;
    public $name      = null;
    public $table     = null;
    public $primary   = 'id';
    public $vars      = array();

    public $where_sql = null;
    public $order_sql = null;
    public $map_where = array();

    public $rows      = 10;
    public $offset    = 0;
    public $limit     = null;
    public $by        = 'DESC';

    public $keywords  = null;
    public $cache     = array();
    public $config    = array();
    public $distinct  = null;

    public function __construct($vars,$name,$primary='id',$appid=null,$table=null){
        $table===null && $table = iDB::table($name);
        $appid===null && $appid = @constant(iPHP_APP.'_APP_'.strtoupper($name));

        // $this->where_sql = null;
        // $this->order_sql = null;
        // $this->map_where = array();
        // $this->default   = array();
        // $this->cache     = array();
        $this->keywords  = 'title,keywords,description';

        list(
            $this->vars,
            $this->appid,$this->name,
            $this->primary,$this->table
        ) = $this->params
          = array($vars,$appid,$name,$primary,$table);

        empty($vars['default:rows']) && $vars['default:rows'] = 10;
        $this->rows  = isset($vars['row']) ? (int) $vars['row'] : $vars['default:rows'];
        $this->limit = "LIMIT {$this->rows}";
        $this->cache['time'] = isset($vars['time']) ? (int) $vars['time'] : -1;
        $this->set_default_orderby();
        iMap::reset();
    }

    /**
     * [set_default_orderby 设置排序]
     * @param string $by [默认排序]
     * @param string $field [默认排序字段]
     */
    public function set_default_orderby($by = 'DESC',$field=null){
        $this->by = $by;
        switch (strtoupper($this->vars['by'])) {
            case "ASC":  $this->by = "ASC";  break;
            case "DESC": $this->by = "DESC"; break;
        }
        $field===null && $field = $this->primary;
        $this->set_sql_order($field);
    }
    /**
     * [set_keywords_field 设置搜索字段]
     * @param [type] $field [字段]
     */
    public function set_keywords_field($field){
        $this->keywords = $field;
    }
    public function set_sql_order($field,$by=null){
        $by===null && $by = $this->by;
        $this->order_sql = " ORDER BY `{$field}` {$by}";
    }

    public function add_sql_in($field,$value=null,$flag=null){
        $value===null && $value = $this->vars[$field];
        is_array($value) OR $value = addslashes($value);
        if (substr($field,-1)=='!') {
            $field = substr($field, 0,-1);
        }
        $this->where_sql.= iSQL::in($value,$field,$flag);
    }
    public function add_sql_and($field,$value=null,$opt='='){
        // if(isset($this->vars[$field])){
            $value===null && $value = $this->vars[$field];
            $value = addslashes($value);
            if (substr($field,-1)=='!') {
                $field = substr($field, 0,-1);
            }
            $this->where_sql.= " AND `{$field}`{$opt}'{$value}'";
        // }
    }

    public function add_sql_by_rand(){
        $array = iSQL::get_rand_ids($this->table, $this->where_sql, $this->rows, $this->primary);
        $this->get_sql_ids($array);
    }
    public function add_sql_where($sql=null){
        if(isset($this->vars['where']) && $sql===null){
            $sql = $this->vars['where'];
        }
        $sql && $this->where_sql.= $sql;
    }
    public function add_map_where($value,$app=null,$field=null,$appid=null){
        if($app && $field){
            $appid===null && $appid = $this->appid;
            iMap::init($app,$appid,$field);
        }

        $map_where = iMap::where($value);
        $this->map_where = array_merge($this->map_where,$map_where);
    }
    public function get_sql_ids($array){
        if ($array) {
            $ids = iSQL::values($array,$this->primary);
            $ids = $ids ? $ids : '0';
            $this->where_sql = " WHERE `{$this->table}`.`{$this->primary}` IN({$ids})";
            $this->limit = '';
        }
    }
    public function get_hash($text=null){
        return md5($this->where_sql.$this->order_sql.$this->limit.$text);
    }
    public function get_sql($sql=null){
        return $this->where_sql.' '.$this->order_sql.' '.$this->limit.' '.$sql;
    }
    public function get_resource($sql=null){
        $sql===null && $sql = $this->distinct.' `'.$this->table.'`.*';
        $resource = iDB::all(
            "SELECT {$sql} FROM `{$this->table}`"
            .$this->get_sql()
        );
        // echo iDB::$last_query;
        return (array)$resource;
    }
    public function process_list($callback=null){
        list($vars,$appid,$name,$primary,$table) = $this->params;

        if ($vars['loop'] === "rel" && empty($vars[$primary])) {
            return array();
        }

        $this->process_sql_status();
        $this->process_sql_hidden();
        $this->process_sql_common();

        $this->process_sql_id();
        $this->process_sql_cid();
        $this->process_sql_pid();
        $this->process_sql_tid();
        $this->process_sql_keywords();
        $this->process_sql_orderby($this->config['orderby']);
        isset($vars['where']) && $this->add_sql_where();

        $map_order_sql = $this->process_map_where();

        isset($vars['page']) && list($total,$multi) = $this->process_page();

        $this->process_ids_array($total,$map_order_sql);

        $resource = $this->process_get_cache();

        if($callback){
            if (empty($resource)) {
                $resource = $this->get_resource();
                if($resource){
                    $resource = iPHP::callback($callback, array($vars, $resource));
                    $this->process_set_cache($resource);
                }
            }
        }
        return $resource;
    }

    public function process_sql_status($flag=true){
        if($flag===false){
            $sql = " WHERE 1=1 ";
        }else{
            $status = isset($this->vars['status'])?(int)$this->vars['status']:"1";
            $sql    = " WHERE `{$this->table}`.`status`='{$status}'";
        }
        if($this->where_sql){
            $this->where_sql = $sql.$this->where_sql;
        }else{
            $this->where_sql = $sql;
        }
    }
    public function process_sql_id(){
        isset($this->vars['id']) && $this->add_sql_in($this->primary,$this->vars['id']);
        isset($this->vars['id!'])&& $this->add_sql_in($this->primary,$this->vars['id!'],'not');
    }
    public function process_sql_hidden(){
        $hidden = categoryApp::get_cahce('hidden');
        $hidden && $this->add_sql_in('cid',$hidden,'not');
    }
    public function process_sql_common(){
        $vars = $this->vars;

        $vars['ucid'] && $this->add_sql_and('ucid');
        isset($vars['userid'])   && $this->add_sql_and('userid');
        isset($vars['weight'])   && $this->add_sql_and('weight');

        isset($vars['pic'])      && $this->add_sql_and('haspic','1');
        isset($vars['nopic'])    && $this->add_sql_and('haspic','0');

        isset($vars['startdate'])&& $this->add_sql_and('pubdate',strtotime($vars['startdate']),'>=');
        isset($vars['enddate'])  && $this->add_sql_and('pubdate',strtotime($vars['enddate']),'<=');
        isset($vars['starttime'])&& $this->add_sql_and('postime',strtotime($vars['starttime']),'>=');
        isset($vars['endtime'])  && $this->add_sql_and('postime',strtotime($vars['endtime']),'<=');
    }
    public function process_sql_cid($key='cid'){
        $vars = $this->vars;

        if (isset($vars['cid!'])) {
            $ncids = explode(',', $vars['cid!']);
            $vars['sub'] && $ncids += categoryApp::get_cids($ncids, true);
            $this->add_sql_in('cid',$ncids,'not');
        }

        if ($vars['cid'] && !isset($vars['cids'])) {
            $cids = explode(',', $vars['cid']);
            $vars['sub'] && $cids += categoryApp::get_cids($cids, true);
            $this->add_sql_in('cid',$cids);
        }

        if (isset($vars['cids']) && !$vars['cid']) {
            $cids = explode(',', $vars['cids']);
            $vars['sub'] && $cids += categoryApp::get_cids($cids, true);
            $cids && $this->add_map_where($cids,'category','cid');
        }
    }
    public function process_sql_pid($exists=false){
        $vars = $this->vars;
        $pfield = $vars['pfield']?$vars['pfield']:'pid';

        if (isset($vars['pid']) && !isset($vars['pids'])) {
            iSQL::$check_numeric = true;
            $this->add_sql_in($pfield,$vars['pid']);
        }

        if(isset($vars['pid!'])){
            iSQL::$check_numeric = true;
            $this->add_sql_in($pfield,$vars['pid!'],'not');
        }

        if (isset($vars['pids']) && !isset($vars['pid'])) {
            iMap::init('prop', $this->appid,$pfield);
            if($exists){
                $this->where_sql.= iMap::exists($vars['pids'],$this->table.'.'.$this->primary); //主表小 map表大
            }else{
                $this->add_map_where($vars['pids']);//主表大 map表大
            }
        }
    }
    public function process_sql_tid(){
        if (isset($this->vars['tids'])) {
            $tfield = $this->vars['tfield']?$this->vars['tfield']:'tags';
            $this->add_map_where($this->vars['tids'],'tag',$tfield);
        }
    }
    public function process_sql_keywords(){
        $vars = $this->vars;
        if (isset($vars['keywords'])) {
            if (empty($vars['keywords'])) {
                return array();
            }
            $keywords_field  = isset($vars['keywords_field'])?$vars['keywords_field']:$this->keywords;

            if (strpos($vars['keywords'], ',') === false) {
                $kw = str_replace(array('%', '_'), array('\%', '\_'),  $vars['keywords']);
                $this->where_sql .= " AND CONCAT(".$keywords_field.") like '%" . addslashes($kw) . "%'";
            } else {
                $kws = explode(',', $vars['keywords']);
                $kws = array_filter ($kws);
                $kwa = array();
                foreach ($kws AS $kwv) {
                    $kwv   = str_replace(array('%', '_'), array('\%', '\_'),  $kwv);
                    $kwa[] = addslashes($kwv);
                }
                $keywords = implode('|', $kwa);
                $keywords && $this->where_sql .= " AND CONCAT(".$keywords_field.") REGEXP '$keywords' ";
            }
        }
    }
    public function process_map_where(){
        $map_order_sql = null;
        if ($this->map_where) {
            $map_sql = iSQL::select_map($this->map_where, 'join');
            //join
            //empty($vars['cid']) && $map_order_sql = " ORDER BY map.`iid` $by";
            $map_table = 'map';
            $this->vars['map_order_table'] && $map_table = $this->vars['map_order_table'];
            $map_order_sql = " ORDER BY {$map_table}.`iid` {$this->by}";
            //$map_order_sql = " ORDER BY `icms_article`.`id` $by";
            //
            $this->where_sql.= ' AND ' . $map_sql['where'];
            $this->where_sql = ",".$map_sql['from']." ".$this->where_sql." AND {$this->table}.`{$this->primary}` = {$map_table}.`iid`";
            //derived
            // $this->where_sql = ",({$map_sql}) map {$this->where_sql} AND `id` = map.`iid`";
        }
        //distinct=true 强制使用去重
        isset($this->vars['distinct']) && iMap::$distinct = true;
        $this->distinct = iMap::distinct($this->table);

        return $map_order_sql;
    }
    public function process_sql_orderby($array=null){
        if(isset($this->vars['orderby'])){
            switch ($this->vars['orderby']) {
                case "id":       $field = $this->primary; break;
                case "new":      $field = $this->primary; break;
                case "hot":      $field = 'hits'; break;
                case "week":     $field = 'hits_week'; break;
                case "month":    $field = 'hits_month'; break;
                case "comment":  $field = 'comments'; break;
                case "pubdate":  $field = 'pubdate'; break;
                case "sort":     $field = 'sortnum'; break;
                case "weight":   $field = 'weight'; break;
            }
            if($array){
                // 'new => id'
                $_field = $array[$this->vars['orderby']];
                // 'pubdate => pubdate'
                in_array($this->vars['orderby'],$array) && $_field = $this->vars['orderby'];
                $_field && $field = $_field;
            }
            $field && $this->set_sql_order($field);
            // $this->vars['orderby'] == 'rand' && $this->add_sql_by_rand();
        }
    }

    public function process_ids_array($total=0,$map_order_sql=null){
        list($vars,$appid,$name,$primary,$table) = $this->params;

        //随机特别处理
        if ($vars['orderby'] == 'rand') {
            $ids_array = iSQL::get_rand_ids($table, $this->where_sql, $this->rows, $primary);
            if ($map_order_sql) {
                $map_order_sql = " ORDER BY {$table}.`{$primary}` {$this->by}";
            }
        }

        $hash = $this->get_hash();

        if ($this->offset) {
            if($total > 2000 && $this->offset >= $total/2) {
                $_offset = $total-$this->offset-$this->rows;
                $_offset < 0 && $_offset = 0;
                $this->order_sql = " ORDER BY `{$table}`.`{$primary}` ASC";
                $this->limit = 'LIMIT '.$_offset.','.$this->rows;
                $hash = $this->get_hash();
            }
            if ($vars['cache']) {
                $map_cache_name = iPHP_DEVICE . "/{$name}_page/" . $hash;
                $ids_array = iCache::get($map_cache_name);
            }
            if (empty($ids_array)) {
                $ids_order_sql = $map_order_sql ? $map_order_sql : $this->order_sql;
                $ids_array = iDB::all("
                    SELECT {$table}.`{$primary}`
                    FROM {$table} {$this->where_sql} {$ids_order_sql} {$this->limit}
                ");
                if(isset($_offset)){
                    $ids_array = array_reverse($ids_array, TRUE);
                    $this->order_sql = " ORDER BY `{$table}`.`{$primary}` DESC";
                }
                $vars['cache'] && iCache::set($map_cache_name, $ids_array, $this->cache['time']);
            }
        } else {
            $map_order_sql && $this->order_sql = $map_order_sql;
        }
        $this->get_sql_ids($ids_array);
    }
    public function process_get_cache($name=null,$hash=null){
        if ($this->vars['cache']) {
            $hash===null && $hash = $this->get_hash();
            $name===null && $name = $this->name;
            $this->cache['name'] = iPHP_DEVICE . "/{$name}/" . $hash;
            return iCache::get($this->cache['name']);
        }
    }
    public function process_set_cache($resource){
        $this->vars['cache'] && iCache::set($this->cache['name'],$resource,$this->cache['time']);
    }
    public function process_keys(&$resource){
        $this->vars['keys'] && iSQL::pickup_keys($resource,$this->vars['keys'],$this->vars['is_remove_keys']);
    }
    public function process_page($conf=null,$count_field=null){
        $total_type = $this->vars['total_cache'] ? $this->vars['total_cache']: null;
        $count_field===null && $count_field = 'count(*)';
        $this->distinct     && $count_field = "count(DISTINCT `{$this->table}`.`{$this->primary}`)";

        $total = iPagination::totalCache(
            "SELECT {$count_field} FROM {$this->table} {$this->where_sql}",
            $total_type,
            iCMS::$config['cache']['page_total']
        );
        $pgconf = array(
            'total'      => $total,
            'total_type' => $total_type,
            'perpage'    => $this->rows,
            'nowindex'   => (int)$GLOBALS['page'],
            'unit'       => iUI::lang('iCMS:page:list'),
            'pagenav'    => isset($this->vars['pagenav']) ? $this->vars['pagenav'] : null,
            'pnstyle'    => isset($this->vars['pnstyle']) ? $this->vars['pnstyle'] : 0,
            'ajax'       => isset($this->vars['page_ajax']) ? $this->vars['page_ajax'] : false,
        );
        $conf && $pgconf = array_merge($pgconf,$conf);
        $multi = iPagination::make($pgconf);
        $this->offset = $multi->offset;
        $this->limit  = "LIMIT {$this->offset},{$this->rows}";
        iView::assign("{$this->name}_list_total", $total);
        return array($total,$multi);
    }

    public function process_sphinx($callback){
        if (empty(iCMS::$config['sphinx']['host'])) {
            return array();
        }

        list($vars,$appid,$name,$primary,$table) = $this->params;

        $resource = array();
        $hidden = categoryApp::get_cahce('hidden');
        $hidden && $where_sql .= iSQL::in($hidden, 'cid', 'not');
        $SPH = iPHP::vendor('SPHINX',iCMS::$config['sphinx']['host']);
        $SPH->init();
        $SPH->SetArrayResult(true);
        if (isset($vars['weights'])) {
            //weights='title:100,tags:80,keywords:60,name:50'
            $wa = explode(',', $vars['weights']);
            foreach ($wa AS $wk => $wv) {
                $waa = explode(':', $wv);
                $FieldWeights[$waa[0]] = $waa[1];
            }
            $FieldWeights OR $FieldWeights = array("title" => 100, "tags" => 80, "name" => 60, "keywords" => 40);
            $SPH->SetFieldWeights($FieldWeights);
        }

        $page = (int) $_GET['page'];
        $rows = isset($vars['row']) ? (int) $vars['row'] : 10;
        $start = ($page && isset($vars['page'])) ? ($page - 1) * $rows : 0;
        $SPH->SetMatchMode(SPH_MATCH_EXTENDED);
        if ($vars['mode']) {
            $vars['mode'] == "SPH_MATCH_BOOLEAN" && $SPH->SetMatchMode(SPH_MATCH_BOOLEAN);
            $vars['mode'] == "SPH_MATCH_ANY" && $SPH->SetMatchMode(SPH_MATCH_ANY);
            $vars['mode'] == "SPH_MATCH_PHRASE" && $SPH->SetMatchMode(SPH_MATCH_PHRASE);
            $vars['mode'] == "SPH_MATCH_ALL" && $SPH->SetMatchMode(SPH_MATCH_ALL);
            $vars['mode'] == "SPH_MATCH_EXTENDED" && $SPH->SetMatchMode(SPH_MATCH_EXTENDED);
        }

        isset($vars['userid']) && $SPH->SetFilter('userid', array($vars['userid']));
        isset($vars['postype']) && $SPH->SetFilter('postype', array($vars['postype']));

        if (isset($vars['cid'])) {
            $cid = explode(',', $vars['cid']);
            $vars['sub'] && $cid += categoryApp::get_cids($cid, true);
            $cids = array_map("intval", $cid);
            $SPH->SetFilter('cid', $cids);
        }

        if (isset($vars['startdate'])) {
            $startime = strtotime($vars['startdate']);
            $enddate = empty($vars['enddate']) ? time() : strtotime($vars['enddate']);
            $SPH->SetFilterRange('pubdate', $startime, $enddate);
        }
        $max_matches = $vars['max_matches']?$vars['max_matches']:10000;
        $SPH->SetLimits($start, $rows, $max_matches);

        $orderby = '@id DESC, @weight DESC';
        $order_sql = ' order by id DESC';

        $vars['orderby'] && $orderby = $vars['orderby'];
        $vars['ordersql'] && $order_sql = ' order by ' . $vars['ordersql'];

        $vars['pic'] && $SPH->SetFilter('haspic', array(1));
        $vars['id!'] && $SPH->SetFilter('@id', array($vars['id!']), true);

        $SPH->setSortMode(SPH_SORT_EXTENDED, $orderby);

        $query = str_replace(',', '|', $vars['q']);
        $vars['acc'] && $query = '"' . $vars['q'] . '"';
        $vars['@'] && $query = '@(' . $vars['@'] . ') ' . $query;

        $index_page = iCMS::$config['sphinx']['index'];
        isset($vars['index']) && $index_page = $vars['index'];

        $res = $SPH->Query($query,  $index_page);

        if (is_array($res["matches"])) {
            foreach ($res["matches"] as $docinfo) {
                $iid[] = $docinfo['id'];
            }
            $iids = implode(',', (array) $iid);
        }
        if (empty($iids)) {
            return array();
        }

        $where_sql = " `id` in($iids)";
        $offset = 0;
        if ($vars['page']) {
            $total  = $res['total'];
            $pgconf = array(
                'total'      => $total,
                'total_type' => $vars['total_cache'] ? $vars['total_cache']: 'G',
                'perpage'    => $rows,
                'nowindex'   => (int)$GLOBALS['page'],
                'unit'       => iUI::lang('iCMS:page:list'),
                'pagenav'    => isset($vars['pagenav']) ? $vars['pagenav'] : null,
                'pnstyle'    => isset($vars['pnstyle']) ? $vars['pnstyle'] : 0,
                'ajax'       => isset($vars['page_ajax']) ? $vars['page_ajax'] : false,
            );
            $multi  = iPagination::make($pgconf);
            $offset = $multi->offset;
            iView::assign("{$name}_search_total", $total);
        }

        $hash = md5($this->where_sql . $this->order_sql . $rows . $GLOBALS['page']);
        $resource = null;
        if ($vars['cache']) {
            $this->cache['name'] = iPHP_DEVICE . "/{$name}_search/" . $hash;
            $resource = iCache::get($this->cache['name']);
        }
        if (empty($resource)) {
            $resource = iDB::all("
                SELECT `{$table}`.*
                FROM `{$table}` {$this->where_sql} {$this->order_sql} {$rows}
            ");
            if($resource){
                $resource = iPHP::callback($callback, array($vars, $resource));
                $vars['cache'] && iCache::set($this->cache['name'], $resource, $this->cache['time']);
            }
        }
        return $resource;
    }
    public function process_prev_next($order=null){
        list($vars,$appid,$name,$primary,$table) = $this->params;

        empty($order) && $order = 'next';

        $this->cache['time'] = isset($vars['time']) ? (int) $vars['time'] : -1;
        if (isset($vars['cid'])) {
            $sql = " AND `cid`='{$vars['cid']}' ";
        }
        if ($order == 'prev') {
            $sql .= " AND `{$primary}` < '{$vars['id']}' ORDER BY {$primary} DESC LIMIT 1";
        } else if ($order == 'next') {
            $sql .= " AND `{$primary}` > '{$vars['id']}' ORDER BY {$primary} ASC LIMIT 1";
        }
        $hash = md5($sql);
        if ($vars['cache']) {
            $cache = iPHP_DEVICE . "/{$name}/" . $hash;
            $resource = iCache::get($cache);
            if(is_array($resource)) return $resource;
        }
        if (empty($resource)) {
            $rs = iDB::row("SELECT * FROM `{$table}` WHERE `status`='1' {$sql}");
            if ($rs) {
                $category = categoryApp::get_cahce_cid($rs->cid);
                $resource = array(
                    'id'    => $rs->id,
                    'title' => $rs->title,
                    'url'   => iURL::get($name, array((array) $rs, $category))->href,
                );
                isset($rs->pic) && $resource['pic'] = filesApp::get_pic($rs->pic);
            }
            $vars['cache'] && iCache::set($cache, $resource, $this->cache['time']);
        }
        return $resource;
    }

    public static function apps_list($vars){
        if(is_array($vars['apps'])){
            $appArray = $vars['apps'];
            $app = $appArray['app'];
            if($appArray['apptype']=='2'){
                $vars['app'] = $app;
                $app = 'content';
            }
            unset($vars['apps'],$vars['_app']);
        }else{
            $app = $vars['app'];
            unset($vars['app'],$vars['_app']);
        }
        $class = $app.'Func';
        $func  = $app.'_list';

        return iPHP::callback(array($class,$func),array($vars),array());
    }
/**
 * [apps_data description]
 * @param  [type] $vars [description]
 * @return [type]       [description]
 */
    public static function apps_data($vars){
        return apps::get_app($vars['id']);
    }
}
