<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
defined('iPHP') OR exit('What are you doing?');

class wxapp_categoryAdmincp extends categoryAdmincp {
    public function __construct() {
        parent::__construct(iCMS_APP_WXAPP,'category');
        $this->category_name   = "分类";
        $this->_app            = 'wxapp';
        $this->_app_name       = '小程序';
        $this->_app_table      = 'wxapp';
        $this->_app_cid        = 'cid';
    }
    public function do_add($default=null){
        $this->_view_tpl_dir = $this->_app;
        parent::do_add(array('status'=> '2'));
    }
}
