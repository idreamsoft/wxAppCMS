<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class tagApp extends appsApp {
    public function __construct() {
        parent::__construct('tag');
    }

    public function do_iCMS($a = null) {
        if ($_GET['name']) {
            $name  = iSecurity::encoding($_GET['name']);
            $val   = iSecurity::escapeStr($name);
            $field = 'name';
        } elseif ($_GET['tkey']) {
            $field = 'tkey';
            $val   = iSecurity::escapeStr($_GET['tkey']);
        } elseif ($_GET['id']) {
            $field = 'id';
            $val   = (int)$_GET['id'];
        }else{
            iPHP::error_404('标签请求出错', 30001);
        }
        return $this->tag($val, $field);
    }

    public function tag($val, $field = 'name', $tpl = 'tag') {
        $val OR iPHP::error_404('TAG不能为空', 30002);
        is_array($val) OR $tag = iDB::row("
            SELECT * FROM `#iCMS@__tag`
            WHERE `$field`='$val'
            AND `status`='1'
        ", ARRAY_A);

        if(empty($tag)){
            if($tpl){
                iPHP::error_404('找不到标签: <b>'.$field.':'. $val.'</b>', 30003);
            }else{
                return false;
            }
        }

        $tag = $this->value($tag,array('page_url'=>true));
        if ($tag === false) {
            return false;
        }

        self::custom_data($tag,$vars);
        self::hooked($tag);

        $view_tpl = $tpl;
        $view_app = "tag";

        if ($tpl) {
            $view_tpl = $tag['tpl'];
            $view_tpl OR $view_tpl = $tag['tag_category']['template']['tag'];
            $view_tpl OR $view_tpl = $tag['category']['template']['tag'];
            $view_tpl OR $view_tpl = self::$config['tpl'];
            $view_tpl OR $view_tpl = '{iTPL}/tag.htm';
            strstr($tpl, '.htm') && $view_tpl = $tpl;
            $tag['category']['app']['app'] && $view_app = $tag['category']['app']['app'];
            iView::assign('tag_category',$tag['tag_category']);
            unset($tag['tag_category']);
        }
        return self::render($tag,$view_tpl,'tag',$view_app);
    }
    public static function value($tag,$vars=null) {
        $tag['appid'] = iCMS_APP_TAG;

        if($tag['cid']){
            //多选只用第一个
            if(strpos($tag['cid'], ',')!==false){
                $cidArray = explode(',', $tag['cid']);
                $tag['cid'] = $cidArray[0];
            }

            $category        = categoryApp::category($tag['cid'],false);
            $tag['category'] = categoryApp::get_lite($category);
        }
        if($tag['tcid']){
            $tag_category        = categoryApp::category($tag['tcid'],false);
            $tag['tag_category'] = categoryApp::get_lite($tag_category);
        }

        $tag['iurl'] = (array)iURL::get('tag', array($tag, $category, $tag_category));
        if($vars['url']=='self'){
            $fkey = 'tids';
            $vars['field'] && $fkey = $vars['field'];
            $nurl = iURL::make(array($fkey=>$tag['id']),null);
            $tag['iurl']['href'] = $nurl;
            $tag['iurl']['url'] = $nurl;
            foreach ($tag['iurl'] as $key => $value) {
                is_array($value) && $tag['iurl'][$key]['url'] = $nurl;
            }
        }
        $tag['url'] OR $tag['url'] = $tag['iurl']['href'];

        if(stripos($tag['url'], '.php?')===false && isset($vars['page_url']) && $vars['page_url']){
            iURL::page_url($tag['iurl']);
        }
        $tag['related']  && $tag['relArray'] = explode(',', $tag['related']);

        apps_common::init($tag,'tag',$vars);
        apps_common::link($tag['name']);
        apps_common::comment();
        apps_common::pic();
        apps_common::hits();
        apps_common::param($tag['name']);

        return $tag;
    }
    public static function get_array(&$rs=array(),$fname=null,$key='tags',$value=null,$id='id') {
            $rs[$key.'_fname'] = $fname;
            $value===null && $value = $rs[$key];
            if ($value) {
                $multi_tag = self::multi_tag(array($rs[$id]=>$value),$key);
                $rs+= (array)$multi_tag[$rs[$id]];
            }
            if(is_array($rs[$key.'_array'])){
                $tagsarray = array();
                foreach ($rs[$key.'_array'] as $tid => $value) {
                    $tagsarray[] = $value;
                }
                $rs[$key.'_array'] = $tagsarray;
                $rs[$key.'_fname'] = $tagsarray[0]['name'];
                $rs[$key.'_ftid']  = $tagsarray[0]['id'];
                $rs[$key.'_furl']  = $tagsarray[0]['url'];
                $rs[$key.'_farray']  = array(
                    'id'   =>$tagsarray[0]['id'],
                    'url'  =>$tagsarray[0]['url'],
                    'name' =>$tagsarray[0]['name'],
                );
            }

            unset($multi_tag,$tagsarray);
    }

    public static function multi_tag($tags=null,$tkey='tags'){
        if(empty($tags)) return array();

        if(!is_array($tags) && strpos($tags, ',') !== false){
            $tags = explode(',', $tags);
        }

        foreach ($tags as $id => $value) {
            if($value){
                $a = explode(',', $value);
                foreach ($a as $ak => $av) {
                    $tMap[$av][] = 't:'.$id; //self::map 中array_merge 必需以字符串合并 才不会重建索引
                    $tArray[$av] = $av;
                }
            }
        }
        if($tArray){
            $tagArray = tag::get($tArray,'name',array("tagApp","value"));
            $tagArray = self::map($tagArray,$tMap);
            $tagArray = self::tpl_var($tagArray,$tkey);
            return $tagArray;
        }
        return false;
    }

    private static function tpl_var($array,$tkey){
        $tArray = array();
        foreach ((array) $array AS $iid => $tag) {
            $iid = substr($iid, 2);
            foreach ($tag as $key => $value) {
                $tArray[$iid][$tkey.'_array'][$value['id']] = $value;
                $tArray[$iid][$tkey.'_link'].= $value['link'];
            }
        }
        return $tArray;
    }
    private static function map($tagArray,$tMap,$field='name'){
        $array = array();
        foreach ((array)$tagArray as $tid => $tag) {
            $iidArray = $tMap[$tag[$field]];
            if(is_array($iidArray)){
                $a = array_fill_keys($iidArray,array($tid=>$tag));
                $array = array_merge_recursive($array,$a);
                unset($a);
            }
        }
        return $array;
    }

}
