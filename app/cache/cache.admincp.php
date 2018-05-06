<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class cacheAdmincp{
    public $appAdmincp = array('configAdmincp','propAdmincp','filterAdmincp','keywordsAdmincp');
    public function __construct() {}
    /**
     * [更新所有缓存]
     * @return [type] [description]
     */
    public function do_all(){
        $this->do_app();
        foreach ($this->appAdmincp as $key => $acp) {
            iPHP::callback(array($acp,'cache'));
        }
        $this->do_menu(false);
        $this->do_category(false);
        $this->do_article_category(false);
        $this->do_tag_category(false);
        $this->do_filecache(false);
        $this->do_tpl(false);
        iUI::success('全部缓存更新完成');
    }
    /**
     * [执行更新缓存]
     * @return [type] [description]
     */
    public function do_iCMS($dialog=true){
		if (in_array($_GET['acp'], $this->appAdmincp)) {
	    	$acp = $_GET['acp'];
	    	iPHP::callback(array($acp,'cache'));
	    	$dialog && iUI::success('更新完成');
		}
    }
    /**
     * [更新菜单缓存]
     * @return [type] [description]
     */
    public function do_menu($dialog=true){
    	menu::cache();
    	$dialog && iUI::success('更新完成','js:1');
    }
    /**
     * [更新所有分类缓存]
     * @return [type] [description]
     */
    public function do_category($dialog=true){
        categoryAdmincp::config();
    	category::cache();
    	$dialog && iUI::success('更新完成');
    }
    /**
     * [更新文章分类缓存]
     * @return [type] [description]
     */
    public function do_article_category($dialog=true){
        $categoryAdmincp = new article_categoryAdmincp();
        $categoryAdmincp->do_cache($dialog);
    }
    /**
     * [更新标签分类缓存]
     * @return [type] [description]
     */
    public function do_tag_category($dialog=true){
        $categoryAdmincp = new tag_categoryAdmincp();
        $categoryAdmincp->do_cache($dialog);
    }
    /**
     * [更新模板缓存]
     * @return [type] [description]
     */
    public function do_tpl($dialog=true){
    	iView::clear_tpl();
    	$dialog && iUI::success('清理完成');
    }
    /**
     * [重计文章数]
     * @return [type] [description]
     */
    public function do_article_count($dialog=true){
        $categoryAdmincp = new article_categoryAdmincp();
    	$categoryAdmincp->re_app_count();
    	$dialog && iUI::success('更新完成');
    }
    /**
     * [更新应用缓存]
     * @return [type] [description]
     */
    public function do_app($dialog=true){
        apps::cache();
    }
    public function do_filecache($dialog=true){
        if(iCMS::$config['cache']['engine']=='file'){
            @set_time_limit(0);
            $prefix = iCache::prefix();
            iCache::$handle->clear_all($prefix);
            $dialog && iUI::success('过期文件类缓存清理完成');
        }
    }
    public static function test($config){
        set_error_handler(function($errno, $errstr, $errfile, $errline){
            $errno = $errno & error_reporting();
            if($errno == 0) return;

            $cache = $_POST['config']['cache'];
            $encode = mb_detect_encoding($errstr, array("ASCII","UTF-8","GB2312","GBK","BIG5"));
            $errstr= mb_convert_encoding($errstr,'UTF-8',$encode);
            iUI::$dialog['width'] = "450";
            iUI::dialog(
                "warning:#:warning:#:
                系统缓存配置出错!<br />
                请确认服务器是否支持".$cache['engine']."或者".$cache['engine']."服务器是否正常运行
                <hr />{$errstr}",
            'js:1', 30000000);
        },E_ALL & ~E_NOTICE);

        $cache = iCache::init($config,true);
        $cache->set('cache_test',1);
        $cache->delete('cache_test');
    }
}
