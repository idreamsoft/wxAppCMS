<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class category {
    public static $appid = null;
    public static $priv = null;
    private static $instance = null;

    // static public function getInstance() {
    //     if (is_null ( self::$instance ) || isset ( self::$instance )) {
    //         self::$instance = new self();
    //     }
    //     return self::$instance;
    // }
    public static function appid($appid,$priv=null){
        $self = new self();
        $self::$appid = $appid;
        $priv && $self::$priv = $priv;
        return $self;
    }
    public static function unset_appid(){
        self::$appid = null;
    }
    public static function init_sql($appid=null,$_sql=null){
        self::$appid && $appid = self::$appid;

        if($appid && !is_numeric($appid)){
            $appid = apps::id($appid);
         }

        if(empty($appid)){
            $sql = '1 = 1';
            $_sql && $sql = $_sql;
        }else{
            $sql =" `appid`='$appid'";
            $_sql && $sql.=' AND '.$_sql;
        }

        return $sql;
    }

    public static function is_root($rootid="0"){
        $is = iDB::value("SELECT `cid` FROM `#iCMS@__category` where `rootid`='$rootid'");
        return $is?true:false;
    }
    public static function rootid($rootids=null,$appid=null) {
        if($rootids===null) return array();

        list($rootids,$is_multi)  = iSQL::multi_var($rootids);

        $sql  = iSQL::in($rootids,'rootid',false,true);
        $sql  = self::init_sql($appid,$sql);
        $data = array();
        $rs   = iDB::all("SELECT `cid`,`rootid` FROM `#iCMS@__category` where {$sql}",OBJECT);
        if($rs){
            $_count = count($rs);
            for ($i=0; $i < $_count; $i++) {
                if($is_multi){
                    $data[$rs[$i]->rootid][$rs[$i]->cid]= $rs[$i]->cid;
                }else{
                    $data[]= $rs[$i]->cid;
                }
            }
        }
        if(empty($data)){
            return;
        }
        return $data;
    }
    public static function multi_get($rs,$field,$appid=null) {
        $cids = iSQL::values($rs,$field,'array',null);
        $data = array();
        if($cids){
          $cids = iSQL::explode_var($cids);
          $appid && self::$appid = $appid;
          $data = (array) self::get($cids);
        }
        return $data;
    }
    public static function get($cids,$callback=null,$appid=null) {
        if(empty($cids)) return array();

        $field = '*';
        if(isset($callback['field'])){
            $field = $callback['field'];
        }

        list($cids,$is_multi)  = iSQL::multi_var($cids);

        $sql  = iSQL::in($cids,'cid',false,true);
        $sql  = self::init_sql($appid,$sql);
        $data = array();
        $rs   = iDB::all("SELECT {$field} FROM `#iCMS@__category` where {$sql}",OBJECT);
        if($rs){
            if($is_multi){
                $_count = count($rs);
                for ($i=0; $i < $_count; $i++) {
                    $data[$rs[$i]->cid]= category::item($rs[$i],$callback);
                }
            }else{
                if(isset($callback['field'])){
                    return $rs[0];
                }else{
                    $data = category::item($rs[0],$callback);
                }
            }
        }
        if(empty($data)){
            return;
        }
        return $data;
    }
    public static function item($category,$callback=null) {
        $category->iurl     = iURL::get('category',(array)$category);
        $category->href     = $category->iurl->href;
        $category->CP_ADD   = category::check_priv($category->cid,'a');
        $category->CP_EDIT  = category::check_priv($category->cid,'e');
        $category->CP_DEL   = category::check_priv($category->cid,'d');
        $category->rule     = json_decode($category->rule,true);
        $category->template = json_decode($category->template,true);
        $category->config   = json_decode($category->config,true);

        $callback && $category = iPHP::callback($callback,array($category));

        return $category;
    }
    public static function get_cid($rootid=null,$where=null,$appid=null) {
        $rootid===null OR $sql.= " `rootid`='$rootid'";

        $sql.= iSQL::where($where,true);
        $sql = self::init_sql($appid,$sql);
        $variable = iDB::all("SELECT `cid` FROM `#iCMS@__category` WHERE {$sql} ORDER BY `sortnum`  ASC",ARRAY_A);
        $category = array();
        foreach ((array)$variable as $key => $value) {
            if(self::$priv){
                if(category::check_priv($value['cid'],self::$priv,null)){
                    $category[] = $value['cid'];
                }
            }else{
                $category[] = $value['cid'];
            }

        }
        return $category;
    }

    public static function get_root($cid="0",$root=null) {
        empty($root) && $root = categoryApp::get_cahce('rootid');
        $ids = $root[$cid];
        if(is_array($ids)){
            $array = $ids;
            foreach ($ids as $key => $_cid) {
              $array+=self::get_root($_cid,$root);
            }
        }
        return (array)$array;
    }
    public static function get_parent($cid="0",$parent=null) {
        if($cid){
            empty($parent) && $parent = categoryApp::get_cahce('parent');
            $rootid = $parent[$cid];
            if($rootid){
                return self::get_parent($rootid,$parent);
            }
        }
        return $cid;
    }
    public static function cache($appid=null) {
        $sql = self::init_sql($appid);
        $rs  = iDB::all("SELECT * FROM `#iCMS@__category` WHERE {$sql}");
        foreach((array)$rs AS $C) {
            self::cahce_item($C);//临时缓存
        }

        foreach((array)$rs AS $C) {
            $C = self::data($C);
            self::cahce_item($C,'C');
        }

        foreach((array)$rs AS $C) {
            iCache::delete('category/'.$C['cid']);
        }
        unset($rs,$C);
        self::cache_common();
        gc_collect_cycles();
    }
    public static function cache_common() {
        $rs  = iDB::all("SELECT `cid`,`rootid`,`dir`,`status`,`domain`,`rule` FROM `#iCMS@__category` ORDER BY `sortnum`  ASC");
        $hidden = array();
        foreach((array)$rs AS $C) {
            $C['status'] OR $hidden[]        = $C['cid'];
            $dir2cid[$C['dir']]              = $C['cid'];
            $parent[$C['cid']]               = $C['rootid'];
            $rootid[$C['rootid']][$C['cid']] = $C['cid'];
        }
        iCache::set('category/dir2cid',$dir2cid,0);
        iCache::set('category/hidden', $hidden,0);
        iCache::set('category/rootid',$rootid,0);
        iCache::set('category/parent',$parent,0);

        $domain_rootid = array();
        foreach((array)$rs AS $C) {
            if($C['domain']){
                $root = self::get_root($C['cid'],$rootid);
                $root && $domain_rootid+= array_fill_keys($root, $C['cid']);
            }
            if($C['rule']){
                $rule = json_decode($C['rule'],true);
                $rule && $rules[$C['cid']] = $rule;
            }
        }
        iCache::set('category/domain_rootid',$domain_rootid,0);
        iCache::set('category/rules',$rules,0);
        unset($rootid,$parent,$dir2cid,$hidden,$rs,$domain_rootid,$root);
    }
    public static function cache_get($cid="0",$fix=null) {
        return iCache::get('category/'.$fix.$cid);
    }
    public static function cahce_item($C=null,$fix=null){
        is_array($C) OR $C = iDB::row("SELECT * FROM `#iCMS@__category` where `cid`='$C' LIMIT 1;",ARRAY_A);
        iCache::set('category/'.$fix.$C['cid'],$C,0);
    }

    public static function cache_all($offset,$maxperpage,$appid=null) {
        $sql = self::init_sql($appid);
        $ids_array  = iDB::all("
            SELECT `cid`
            FROM `#iCMS@__category` {$sql} ORDER BY cid
            LIMIT {$offset},{$maxperpage};
        ");
        $ids   = iSQL::values($ids_array,'cid');
        $ids   = $ids?$ids:'0';
        $rs  = iDB::all("SELECT * FROM `#iCMS@__category` WHERE `cid` IN({$ids});");
        foreach((array)$rs AS $C) {
            $C = self::data($C);
            self::cahce_item($C,'C');
        }
        unset($$rs,$C,$ids_array);
    }
    public static function cahce_del($cid=null){
        if(empty($cid)){
            return;
        }
        iCache::delete('category/'.$cid);
        iCache::delete('category/C'.$cid);
    }

    public static function data($C){
        if($C['url']){
            $C['iurl']   = array('href'=>$C['url']);
            $C['outurl'] = $C['url'];
        }else{
            $C['iurl'] = (array) iURL::get('category',$C);
        }

        $C['url']    = $C['iurl']['href'];
        $C['link']   = "<a href='{$C['url']}'>{$C['name']}</a>";
        $C['sname']  = $C['subname'];

        $C['subid']  = self::get_root($C['cid']);
        $C['counts'] = $C['count'];
        foreach ((array)$C['subid'] as $skey => $scid) {
            $sc = self::cache_get($scid);
            $C['counts']+=$sc['count'];
        }

        $C['child']  = $C['subid']?true:false;
        $C['subids'] = implode(',',(array)$C['subid']);
        $C['dirs']   = self::data_dirs($C['cid']);

        $C = self::data_pic($C);
        $C = self::data_parent($C);
        $C = self::data_nav($C);
        $C+= (array)apps_meta::data('category',$C['cid']);

        //category 应用信息
        $C['sappid'] = iCMS_APP_CATEGORY;
        $ca = apps::get_app($C['sappid']);
        $C['sapp'] = apps::get_app_lite($ca);
        $ca['fields'] && formerApp::data($C['cid'],$ca,'category',$C,null,$C);
        //category 绑定的应用
        $C['appid'] && $C['app'] = apps::get_app_lite($C['appid']);

        is_string($C['rule'])    && $C['rule']     = json_decode($C['rule'],true);
        is_string($C['template'])&& $C['template'] = json_decode($C['template'],true);
        is_string($C['config'])  && $C['config']   = json_decode($C['config'],true);

        empty($C['rule'])    && $C['rule']     = array();
        empty($C['template'])&& $C['template'] = array();
        empty($C['config'])  && $C['config']   = array();

		return $C;
    }
    public static function data_dirs($cid="0") {
        $C = self::cache_get($cid);
        $C['rootid'] && $dir.=self::data_dirs($C['rootid']);
        $dir.='/'.$C['dir'];
        return $dir;
    }
    public static function data_pic($C){
        $C['pic']  = is_array($C['pic'])?$C['pic']:filesApp::get_pic($C['pic']);
        $C['mpic'] = is_array($C['mpic'])?$C['mpic']:filesApp::get_pic($C['mpic']);
        $C['spic'] = is_array($C['spic'])?$C['spic']:filesApp::get_pic($C['spic']);
        return $C;
    }
    public static function data_parent($C){
        if($C['rootid']){
            $root = self::cache_get($C['rootid']);
            $C['parent'] = self::data($root);
        }
        return $C;
    }
    public static function data_nav($C){
        $nav      = '';
        $navArray = array();
        self::data_nav_array($C,$navArray);
        krsort($navArray);
        foreach ((array)$navArray as $key => $value) {
            $nav.="<li>
            <a href='{$value['url']}'>{$value['name']}</a>
            <span class=\"divider\">".iUI::lang('iCMS:navTag')."</span>
            </li>";
        }
        $C['nav'] = $nav;
        $C['navArray'] = $navArray;
        return $C;
    }
    public static function data_nav_array($C,&$navArray = array()) {
        if($C) {
            $navArray[]= array(
                'name' => $C['name'],
                'url'  => $C['iurl']['href'],
            );
            if($C['rootid']){
                $rc = (array)self::cache_get($C['rootid']);
                $rc['iurl'] = (array) iURL::get('category',$rc);
                self::data_nav_array($rc,$navArray);
            }
        }
    }
    public static function search_sql($cid,$field='cid'){
        if($cid){
            $cids  = (array)$cid;
            $_GET['sub'] && $cids+=categoryApp::get_cids($cid,true);
            $sql= iSQL::in($cids,$field);
        }
        return $sql;
    }
    public static function select_lite($scid="0",$cid="0",$level = 1,$url=false,$where=null) {
        $cid_array  = (array)category::get_cid($cid,$where);//获取$cid下所有子栏目ID
        $cate_array = (array)category::get($cid_array);     //获取子栏目数据
        $root_array = (array)category::rootid($cid_array);  //获取子栏目父栏目数据
        foreach($cid_array AS $root=>$_cid) {
            $C = (array)$cate_array[$_cid];
            if($C['status']) {
                $tag      = ($level=='1'?"":"├ ");
                $selected = ($scid==$_cid)?"selected":"";
                $text     = str_repeat("│　", $level-1).$tag.$C['name']."[cid:{$_cid}]".($C['url']?"[∞]":"");
                ($C['url'] && !$url) && $selected ='disabled';
                $option.="<option value='{$_cid}' $selected>{$text}</option>";
            }
            $root_array[$_cid] && $option.= self::select_lite($scid,$C['cid'],$level+1,$url);
        }
        return $option;
    }
    public static function select($scid="0",$cid="0",$level = 1,$url=false,$where=null) {
        $cc = iDB::value("SELECT count(*) FROM `#iCMS@__category`");
        return self::select_lite($scid,$cid,$level,$url,$where);

        // if($cc<=1000){
        // }else{
        //     $array = iCache::get('category/cookie');
        //     foreach((array)$array AS $root=>$_cid) {
        //         $C = category::cache_get($_cid);
        //         if($C['status']) {
        //             $selected = ($scid==$_cid)?"selected":"";
        //             $text     = $C['name']."[cid:{$_cid}][pid:{$C['pid']}]";
        //             $option  .= "<option value='{$_cid}' $selected>{$text}</option>";
        //         }
        //     }
        //     return $option;
        // }
    }
    public static function priv($p) {
        $category = new category();
        $category::$priv = $p;
        return $category;
    }
    public static function check_priv($p, $act = '', $ret = '') {
        if (members::is_superadmin()) {
            return true;
        }
        if ($p === 'CIDS') {
            foreach (members::$priv['category'] as $key => $value) {
                if (strpos($value, ':') !== false) {
                    list($cid,$priv) = explode(':', $value);
                    if($act){
                        if($priv==$act){
                            $cids[$cid] = $cid;
                        }
                    }
                    // else{
                    //     if(self::check_priv($cid, $act)){
                    //         $cids[$cid] = $cid;
                    //     }
                    // }
                }
            }
            return $cids;
        }
        if(members::$priv['category']){
            if($act){
                strpos($p, ':') === false && $p = $p . ':' . $act;
            }
            $priv = check_priv((string) $p, members::$priv['category']);
        }else{
            $priv = false;
        }
        $priv OR self::permission($p, $ret);
        return $priv;
    }
    public static function permission($p=null, $ret = '') {
        if($ret){
            $title = '栏目:cid='.$p;
            if($p=="0"){
                $title = "添加顶级栏目";
            }
            iUI::permission($title, $ret);
        }
    }
}
