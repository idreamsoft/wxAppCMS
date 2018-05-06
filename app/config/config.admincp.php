<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class configAdmincp extends config {
    public function __construct() {}
    /**
     * [配置管理]
     */
    public function do_iCMS(){
    	$config	= $this->get();
        $redis    = extension_loaded('redis');
        $memcache = extension_loaded('memcached');
        menu::$url = __ADMINCP__.'='.admincp::$APP_NAME;
    	include admincp::view("config");
    }
    /**
     * [保存配置]
     */
    public function do_save(){
        $config = iSecurity::escapeStr($_POST['config']);

        iFS::allow_files($config['FS']['allow_ext']) OR iUI::alert("附件设置 > 允许上传类型设置不合法!");
        iFS::allow_files(trim($config['router']['ext'],'.')) OR iUI::alert('URL设置 > 文件后缀设置不合法!');

        $desktop_tpl_ext = iFS::get_ext($config['template']['desktop']['tpl']);
        if($desktop_tpl_ext) iFS::allow_files($desktop_tpl_ext) OR iUI::alert("桌面端模板不合法!");

        $config['router']['ext']    = '.'.trim($config['router']['ext'],'.');
        $config['router']['url']    = trim($config['router']['url'],'/');
        $config['router']['public'] = rtrim($config['router']['public'],'/');
        $config['router']['user']   = rtrim($config['router']['user'],'/');
        $config['router']['dir']    = rtrim($config['router']['dir'],'/').'/';
        $config['FS']['url']        = trim($config['FS']['url'],'/').'/';
        $config['template']['desktop']['domain'] = $config['router']['url'];

        if($config['cache']['engine']!='file'){
            iPHP::callback(array("cacheAdmincp","test"),array($config['cache']));
        }

    	foreach($config AS $n=>$v){
    		$this->set($v,$n,0);
    	}
    	config::cache();
    	iUI::success('更新完成','js:1');
    }
}
