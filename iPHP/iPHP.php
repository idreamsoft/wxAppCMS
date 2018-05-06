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
ini_set('display_errors','ON');
error_reporting(E_ALL & ~E_NOTICE);
version_compare('5.3',PHP_VERSION,'>') && die('iPHP requires PHP version 5.3 or higher. You are running version '.PHP_VERSION.'.');
header('Content-Type: text/html; charset=UTF-8');
//框架版本
require_once __DIR__.'/iPHP.version.php';
//常量定义
require_once __DIR__.'/iPHP.define.php';
//兼容性处理
require_once __DIR__.'/iPHP.compat.php';
//框架主类
require_once __DIR__.'/iPHP.class.php';
iPHP::bootstrap();
