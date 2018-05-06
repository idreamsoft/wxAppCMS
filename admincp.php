<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
define('iPHP_DEBUG', true);
define('iPHP_WAF_POST',false);
require dirname(__FILE__) . '/iCMS.php';
admincp::run();
