<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class menu {
    public static $menu_array = array();
    public static $href_array = array();
    public static $callback   = array();
    public static $url        = null;
    public static $priv_key   = 0;

	public static function init() {
        self::get_cache();
        // self::get_array(true);
	}
    public static function set($d='manage') {
        self::$url = __ADMINCP__.'='.admincp::$APP_NAME.'&do='.$d;
    }
    public static function mid($vars,$sort=0,$parent=null,$level=0){
        foreach ((array)$vars as $k => $v) {
            ++$sort;
            $key = $v['id']?$v['id']:$k;
            if(!isset($v['sort'])) $v['sort']= $sort;
            //权限
            $v['priv'] = $v['id']?$v['id']:$v['href'];
            if($v['caption']=="-"){
                $v['priv'] = $parent.'-'.self::$priv_key.'-'.$level;
                ++$level;
                ++self::$priv_key;
            }

            $array[$key] = $v;
            if($v['children']){
                $array[$key]['children'] = self::mid($v['children'],$sort,$v['id'],$level);
            }
        }
        return $array;
    }

    public static function get_array($cache=false){
        $variable = array();
        $rs = apps::get_array(array('!menu'=>'','status'=>'1'),'id,app,name,title,config,menu','app ASC');
        foreach ($rs as $appid=> $app) {
            $menuArray = apps::menu($app);
            $sort = $app['id']*1000;
            if($menuArray){
                $menuArray = self::mid($menuArray,$sort);
                $variable[] = $menuArray;
            }
        }

        // if(self::$callback['array'] && is_callable(self::$callback['array'])){
        //    $variable2 = call_user_func_array(self::$callback['array'],array(__CLASS__));
        //     $variable2 && $variable = array_merge($variable,$variable2);
        // }

        if($variable){
            $variable = call_user_func_array('array_merge_recursive',$variable);
            array_walk($variable,array(__CLASS__,'item_unique'));
            self::item_sort($variable);
            self::href_array($variable,self::$href_array,$caption);
            self::$menu_array = $variable;
            unset($variable);
            if($cache){
                $iCache = iCache::file_cache();
                $iCache->add(iPHP_APP.'/menu/array', self::$menu_array,0);
                $iCache->add(iPHP_APP.'/menu/href', self::$href_array,0);
                $iCache->add(iPHP_APP.'/menu/caption',$caption,0);
            }
        }
    }
    public static function cache(){
        self::get_array(true);
        return true;
    }
    public static function get_caption(){
        $iCache = iCache::file_cache();
        return $iCache->get(iPHP_APP.'/menu/caption');
    }
    public static function get_cache(){
         $iCache = iCache::file_cache();
         self::$menu_array  = $iCache->get(iPHP_APP.'/menu/array');
         self::$href_array  = $iCache->get(iPHP_APP.'/menu/href');
         if(empty(self::$menu_array)||empty(self::$href_array)){
            self::cache();
         }
    }

    public static function href_array($variable,&$out,&$caption,$id=null){
        // $array = array();
        foreach ($variable as $key => $value) {
            $_id = $id?$id:$value['id'];
            if(!$value['-'] && $value['href']){
                $out[$value['href']] = $_id;
                $caption[$value['href']] = $value['caption'];
            }
            if($value['children']){
                self::href_array($value['children'],$out,$caption,$_id);
            }

        }
        // return $array;
    }
    public static function item_sort(&$variable){
        uasort ($variable,array(__CLASS__,'array_sort'));
    	foreach ($variable as $key => $value) {
    		if($value['children']){
	    		self::item_sort($variable[$key]['children']);
    		}
    	}
    }
    public static function array_sort($a,$b){
        if ( $a['sort']  ==  $b['sort'] ) {
            return  0 ;
        }
        return ( $a['sort']  <  $b['sort'] ) ? - 1  :  1 ;
        // return @strnatcmp($a['sort'],$b['sort']);
    }
    public static function item_unique (&$items){
        if(is_array($items)){
            foreach ($items as $key => $value) {
                if(in_array($key, array('id','name','icon','caption','sort','priv'))){
                    is_array($value) &&$items[$key] = $value[0];
                }
                if(is_array($items['children'])){
                    array_walk ($items['children'],array(__CLASS__,'item_unique'));
                }
            }
        }
    }

    public static function href($a){
        $a['href'] && $href = __ADMINCP__.'='.$a['href'];
        $a['target']=='iPHP_FRAME' && $href.='&frame=iPHP';
        $a['href']=='iPHP_SELF' && $href = iPHP_SELF;
        $a['href'] OR $href = 'javascript:;';
        strstr($a['href'], 'http://') && $href = $a['href'];
        return $href;
    }
	public static function a($a){
		if(empty($a)||$a['caption']=='-') return;

        $a['title'] OR $a['title'] = $a['caption'];
		$a['icon'] && $icon='<i class="'.$a['icon'].'"></i> ';
		$link = '<a href="'.self::href($a).'"';
		$a['title']  && $link.= ' title="'.$a['title'].'"';
		$link.= ' class="tip-bottom '.$a['a_class'].'"';
		$link.='>';
		return $link.$icon.' '.$a['caption'].'</a>';
	}

    public static function history($url=null,$get=false){
        $url===null OR self::$url = $url;
        $iCache    = iCache::file_cache();
        $key       = iPHP_APP.'/menu/history'.self::$callback['hkey'];
        $history   = (array)$iCache->get($key);
        if($get){
            return $history;
        }
        array_unshift($history,$url);
        $history = array_unique ($history);
        if(count($history)>20){
            array_pop($history);
        }
        $iCache->add($key, $history,0);
    }

    public static function search_href($url=null){
        $url===null OR self::$url = $url;
        $path =  str_replace(__ADMINCP__.'=', '', self::$url);
        foreach (self::$href_array as $key => $value) {
            if($path==$key){
               return $value;
            }
        }
    }
    public static function app_memu($app){
        $rs = apps::get($app,'app');
        $array = $rs['menu'];
        $array = self::mid($array);
        $key   = self::search_href();
        $array = $array[$key]['children'][$app]['children'];

        foreach((array)$array AS $_array) {
            $nav.= self::li('sidebar',$_array,0);
        }
        return $nav;

    }
	public static function sidebar(){
        $key = self::search_href();
        $menu_array = self::$menu_array[$key]['children'];
        foreach((array)$menu_array AS $array) {
            $nav.= self::li('sidebar',$array,0);
        }
        if(self::$callback['sidebar'] && is_callable(self::$callback['sidebar'])){
            $nav.= call_user_func_array(self::$callback['sidebar'], array(__CLASS__));
        }
        return $nav;
	}
	public static function nav(){
        foreach((array)self::$menu_array AS $array) {
            $nav.= self::li('nav',$array,0);
        }
		return $nav;
	}

    public static function children_count($variable){
        $count = 0;
        foreach ((array)$variable as $key => $value) {
            $value['caption']=='-' OR $count++;
        }
        return $count;
    }
	public static function li($mType,$a,$level = 0){
        if(self::$callback['priv'] && is_callable(self::$callback['priv'])){
           $priv = call_user_func_array(self::$callback['priv'],array($a,null));
           if($priv===false) return null;
        }

        $a = (array)$a;
		if($a['caption']=='-'){
			return '<li menu-sort="'.$a['sort'].'" class="'.(($level||$mType=='sidebar')?'divider':'divider-vertical').'"></li>';
		}

        $href = self::href($a);
		$a['children'] && $children = count($a['children']);

		if($children && $mType=='nav'){
			$a['class']	= $level?'dropdown-submenu':'dropdown';
			$a['a_class'] = 'dropdown-toggle';
			$level==0 && $caret = true;
		}

		if($mType=='sidebar' && $children && $level==0){
            // $href       = 'javascript:;';
			$a['class']	= 'submenu';
			$label		= '<span class="label">'.self::children_count($a['children']).'</span>';
		}

		if($mType=='tab'){
			$href = "#".$a['href'];
		}

        empty($a['title']) && $a['title'] = $a['caption'];

		$li = '<li class="'.$a['class'].'" title="'.$a['title'].'" menu-sort="'.$a['sort'].'">';
		$link = '<a href="'.$href.'"';
		$link.= ' title="'.$a['title'].'"';
		$a['a_class']&& $link.= ' class="'.$a['a_class'].'"';
		$a['target'] && $link.= ' target="'.$a['target'].'"';

		if($a['data-toggle']=='modal'){
			$link.= ' data-toggle="modal"';
			$link.= ' data-target="#iCMS-MODAL"';
            if($a['data-meta']){
                if(is_array($a['data-meta'])){
                    $link.= " data-meta='".json_encode($a['data-meta'])."'";
                }else{
                    $link.= " data-meta='".$a['data-meta']."'";
                }
            }
		}elseif($mType=='nav'){
			$children && $link.= ' data-toggle="dropdown"';
		}elseif($mType=='tab'){
			$link.= ' data-toggle="tab"';
		}
		$link.=">";
		$li.=$link;
		$a['icon'] && $li.='<i class="fa fa-'.$a['icon'].'"></i> ';
		$li.='<span>'.$a['caption'].'</span>'.$label;
		$caret && $li.='<b class="caret"></b>';
		$li.='</a>';
		if($children){
			$SMli	= '';
			foreach((array)$a['children'] AS $id=>$ca) {
				$SMli.= self::li($mType,$ca,$level+1);
			}
			$mType =='nav' && $SMul='<ul class="dropdown-menu">'.$SMli.'</ul>';
			if($mType=='sidebar'){
				$SMul = $level>1?$SMli:'<ul style="display: none;">'.$SMli.'</ul>';
			}
		}
		$li.=$SMul.'</li>';
		return $li;
	}
}
