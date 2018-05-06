<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class indexApp {
	public $methods	= array('iCMS');
    public function __construct() {}
    public function do_iCMS($a = null) {
        if(iView::$gateway!="html"){
            $domain = $this->domain();
            if($domain) return;
        }
        return $this->index($a);
    }
    public function API_iCMS(){
        return $this->do_iCMS();
    }
    private function index($a = null){
        $index_name = $a[1]?:iCMS::$config['template']['index']['name'];
        $index_name OR $index_name = 'index';
        $index_tpl  = $a[0]?:iView::$config['template']['index'];
        $rule = '{PHP}';
        if(iView::$gateway=="html" || iCMS::$config['template']['index']['rewrite']){
            $rule = $index_name.iCMS::$config['router']['ext'];
        }
        $iurl = (array)iURL::get('index',array('rule'=>$rule));
        $rule=='{PHP}' OR iURL::page_url($iurl);

        if(iCMS::$config['template']['index']['mode'] && iPHP_DEVICE=="desktop"){
            appsApp::redirect_html($iurl);
        }

        iView::set_iVARS($iurl,'iURL');
        $view = iView::render($index_tpl,'index');
        if($view) return array($view,$iurl);
    }
    public function domain(){
        $domain = iCMS::$config['category']['domain'];
        if($domain){
            $host = iSecurity::escapeStr($_GET['host']);
            empty($host) && $host = iPHP_REQUEST_HOST;
            $cid = $domain[$host];
            if(empty($cid) && iPHP_REQUEST_SCHEME=='http'){
                $host = str_replace('http://', '', $host);//兼容无协义域名
                $cid = $domain[$host];
            }
            if($cid){
                categoryApp::category($cid);
                return true;
            }
        }
        return false;
    }
}
