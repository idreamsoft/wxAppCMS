<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
define('iPHP',TRUE);
//应用名
define('iPHP_APP','iCMS');
//支持邮箱
define('iPHP_APP_MAIL','support@iCMSdev.com');
//配置
require_once __DIR__.'/config.php';
//iPHP框架文件
require_once __DIR__.'/iPHP/iPHP.php';
//iCMS版本
require_once __DIR__.'/core/iCMS.version.php';
//iCMS主类
require_once __DIR__.'/core/iCMS.class.php';
//iCMS常用函数
require_once __DIR__.'/core/iCMS.func.php';

iPHP_APP_INIT && iCMS::init();
