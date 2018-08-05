<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class categoryApp{
    public $methods = array('iCMS','category','list');
    public function __construct($appid = iCMS_APP_ARTICLE) {
    	// $this->appid = iCMS_APP_ARTICLE;
    	// $appid && $this->appid = $appid;
    	// $_GET['appid'] && $this->appid	= (int)$_GET['appid'];
    }
    public function do_iCMS($tpl='index',$is_list=null) {
        $cid = (int)$_GET['cid'];
        $dir = iSecurity::escapeStr($_GET['dir']);
		if(empty($cid) && $dir){
			$cid = categoryApp::get_cahce('dir2cid',$dir);
            $cid OR iPHP::error_404(array('category:not_found','dir',$dir), 20002);
		}
    	return $this->category($cid,$tpl,$is_list);
    }
    public function do_list($tpl='index') {
        return $this->do_iCMS($tpl,true);
    }
    public function API_iCMS(){
        return $this->do_iCMS();
    }
    /**
     * [hooked 钩子]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    // public static function hooked(&$data){
    //     iPHP::hook('category',$data,iCMS::$config['hooks']['category']);
    // }
    /**
     * 该方法超多次调用 禁止SQL查询
     */
    public static function category($cid,$tpl='index',$is_list=null) {
        $category = categoryApp::get_cahce_cid($cid);
        if(empty($category) && $tpl){
            iPHP::error_404(array('category:not_found','cid',$category['cid']),20001);
        }
        if($category['status']==0) return false;

        if($tpl){
            if(iView::$gateway=="html"){
                $isphp = strpos($category['rule']['index'], '{PHP}');
                if($isphp !== false||$category['outurl']||!$category['mode']){
                    return false;
                }
            }
            $category['outurl'] && iPHP::redirect($category['outurl']);
            $category['mode']=='1' && appsApp::redirect_html($category['iurl']);
        }
        self::router($category);
        $category['param'] = array(
            "sappid" => $category['sappid'],
            "appid"  => $category['appid'],
            "iid"    => $category['cid'],
            "cid"    => $category['rootid'],
            "suid"   => $category['userid'],
            "title"  => $category['name'],
            "url"    => $category['url']
        );
        // self::hooked($category);
        if($tpl) {
            iView::set_iVARS($category['iurl'],'iURL');
            $category['mode'] && iURL::page_url($category['iurl']);
            if($category['app']['type']=="2"){ //自定义应用模板信息
                iPHP::callback(array("contentFunc","interfaced"),array($category['app']));
            }
            $view_app = "category";
            $category['app']['app'] && $view_app = $category['app']['app'];
            iView::assign('APP', $category['app']); //绑定的应用信息
            unset($category['app']);
            iView::assign('category',$category);
            if(strpos($tpl, '.htm')!==false){
            	return iView::render($tpl,$view_app);
            }
            $GLOBALS['page']>1 && $is_list = true;
            $is_list && $tpl='list';
            if($category['template']){
                $view = iView::render($category['template'][$tpl],$view_app);
            }else{
                iPHP::error_404('找不到该栏目的模板配置,请设置栏目'.$tpl.'模板', 20002);
            }
            if($view) return array($view,$category);
        }else{
        	return $category;
        }
    }
    public static function router(&$category){
        if($category && !$category['iDevice']){
            if(!iDevice::$IS_IDENTITY_URL){
                iDevice::router($category);
                iDevice::router($category['iurl']);
                iDevice::router($category['navArray'],true);
                $category['parent'] && self::router($category['parent']);
                $category['iDevice'] = true;
            }
        }
    }
    public static function get_lite($category){
        $keyArray = array('sortnum','password','mode','domain','config','addtime');
        foreach ($keyArray as $i => $key) {
             unset($category[$key]);
        }
        self::router($category);
        return $category;
    }
    public static function get_cids($cid = "0",$all=true,$root_array=null) {
        $root_array OR $root_array = categoryApp::get_cahce("rootid");
        $cids = array();
        is_array($cid) OR $cid = explode(',', $cid);
        foreach($cid AS $_id) {
            $cids+=(array)$root_array[$_id];
        }
        if($all){
            foreach((array)$cids AS $_cid) {
                $root_array[$_cid] && $cids+= self::get_cids($_cid,$all,$root_array);
            }
        }
        $cids = array_unique($cids);
        $cids = array_filter($cids);

        return $cids;
    }
    public static function get_cahce_cid($cid="0") {
        return iCache::get('category/C'.$cid);
    }
    public static function get_cahce($key=null,$value=null){
        if($value){
            return iCache::get('category/'.$key,$value);
        }
        return iCache::get('category/'.$key);
    }
    //绑定域名 iURL 回调函数
    public static function domain($i,$cid,$base_url) {
        $domain_array = (array)iCMS::$config['category']['domain'];
        if($domain_array){
            $domain_array = array_flip($domain_array);
            $domain = $domain_array[$cid];
            if(empty($domain)){
                $rootid_array = categoryApp::get_cahce("domain_rootid");
                if($rootid_array){
                    $rootid = $rootid_array[$cid];
                    $rootid && $domain = $domain_array[$rootid];
                }
            }
        }
        if($domain){
            $urls = parse_url($base_url);
            $urls['scheme'].='://';
            iFS::checkHttp($domain) OR $domain = $urls['scheme'].$domain;

            $i->href    = str_replace($base_url, $domain, $i->href);
            $i->hdir    = str_replace($base_url, $domain, $i->hdir);
            $i->pageurl = str_replace($base_url, $domain, $i->pageurl);

        }
        return $i;
    }
}
