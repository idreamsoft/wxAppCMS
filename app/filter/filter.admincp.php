<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class filterAdmincp{
    public function __construct() {
        $this->appid = iCMS_APP_FILTER;
    }
    public function do_iCMS(){
        $this->do_config();
    }
    public function do_config(){
        configAdmincp::app('999999','filter');
    }
    public function do_save_config(){
        $filter  = explode("\n",$_POST['config']['filter']);
        $disable = explode("\n",$_POST['config']['disable']);
        $_POST['config']['filter']  = array_unique($filter);
        $_POST['config']['disable'] = array_unique($disable);

        configAdmincp::save('999999','filter',array($this,'cache'));
    }
    public static function cache($config=null){
        $config===null && $config  = configAdmincp::get('999999','filter');
    	iCache::set('filter/array',$config['filter'],0);
    	iCache::set('filter/disable',$config['disable'],0);
    }
}
