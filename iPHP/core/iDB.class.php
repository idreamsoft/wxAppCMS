<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) iiiPHP.com. All rights reserved.
 *
 * @author iPHPDev <master@iiiphp.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.1.0
 */
defined('iPHP') OR exit('What are you doing?');
defined('iPHP_CORE') OR exit('What are you doing?');

if(version_compare(PHP_VERSION,'5.5','>=') && extension_loaded('mysqli')){
    require_once iPHP_CORE.'/iMysqli.class.php';
}elseif(extension_loaded('mysql')){
    require_once iPHP_CORE.'/iMysql.class.php';
}else{
    trigger_error('您的 PHP 环境看起来缺少 MySQL 数据库支持扩展。',E_USER_ERROR);
}

