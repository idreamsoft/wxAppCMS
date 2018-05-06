<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* $Id: config.php 2349 2013-02-25 04:10:05Z coolmoo $
*/
//---------------数据库配置------------------
define('iPHP_DB_TYPE','mysql');//数据库类型 mysql sqlite (SQLite3)
define('iPHP_DB_HOST','localhost');// 服务器名或服务器ip,一般为localhost
define('iPHP_DB_PORT','3306'); //数据库端口
define('iPHP_DB_USER','root');// 数据库用户
define('iPHP_DB_PASSWORD','123456');//数据库密码
define('iPHP_DB_NAME','wxappcms'); // 数据库名
define('iPHP_DB_PREFIX','icms_');// 表名前缀, 同一数据库安装多个请修改此处
define('iPHP_DB_CHARSET','utf8');//MYSQL编码设置.如果您的程序出现乱码现象，需要设置此项来修复. 请不要随意更改此项，否则将可能导致系统出现乱码现象
define('iPHP_DB_PREFIX_TAG','#iCMS@__');// SQL表名前缀替换
//define('iPHP_DB_COLLATE', 	'');
//----------------------------------------
define('iPHP_KEY','7rkyfYXYpkKXBYdfx7eNaGt54tT5y5e5wdXEvXcZQZa6jrYK7eb3FEebd3rjnGTr');
define('iPHP_CHARSET','utf-8');
//---------------cookie设置-------------------------
define('iPHP_COOKIE_DOMAIN','');
define('iPHP_COOKIE_PATH','/');
define('iPHP_COOKIE_PRE','iCMS');
define('iPHP_COOKIE_TIME','86400');
define('iPHP_AUTH_IP',true);
define('iPHP_UAUTH_IP',false);
//---------------时间设置------------------------
define('iPHP_TIME_ZONE',"Asia/Shanghai");
define('iPHP_DATE_FORMAT','Y-m-d H:i:s');
//define('iPHP_TIME_CORRECT',"0"); //网站动态配置
//---------------模板------------------------
define('iPHP_TPL_VAR','iCMS');//模板标签定义
//---------------DEBUG------------------------
//define('iPHP_DEBUG',false);
//define('iPHP_TPL_DEBUG',false);
//define('iPHP_URL_404','');
//---------------多站点模式------------------------
//define('iPHP_MULTI_SITE',true);
//define('iPHP_MULTI_DOMAIN',true);
