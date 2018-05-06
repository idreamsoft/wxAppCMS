<?php

class iDefine {
    public static function set($vars,$val=null) {
        if(is_array($vars)){
            foreach ($vars as $key => $value) {
                self::set($key,$value);
            }
        }else{
            $name = 'iPHP_'.strtoupper($vars);
            defined($name) OR define($name,$val);
        }
    }

    public static function request() {
        define('iPHP_SELF', $_SERVER['PHP_SELF']);
        define('iPHP_REFERER',  $_SERVER['HTTP_REFERER']);

        define('iPHP_REQUEST_SCHEME',($_SERVER['SERVER_PORT'] == 443)?'https':'http');
        define('iPHP_REQUEST_HOST',iPHP_REQUEST_SCHEME.'://'.($_SERVER['HTTP_X_HTTP_HOST']?$_SERVER['HTTP_X_HTTP_HOST']:$_SERVER['HTTP_HOST']));
        define('iPHP_REQUEST_URI',$_SERVER['REQUEST_URI']);
        define('iPHP_REQUEST_URL',iPHP_REQUEST_HOST.iPHP_REQUEST_URI);
    }
    public static function router($conf) {
        define('iPHP_URL', $conf['url']);
        define('iPHP_URL_404', $conf['404']); //404定义
        define('iPHP_ROUTER_REWRITE', $conf['rewrite']);
    }
    public static function datatime($conf) {
        defined('iPHP_TIME_CORRECT') OR define('iPHP_TIME_CORRECT', (int)$conf['cvtime']);
        $conf['zone'] && @date_default_timezone_set($conf['zone']);//设置时区
    }
    public static function debug($conf) {
        defined('iPHP_DEBUG') OR define('iPHP_DEBUG', $conf['php']); //程序调试模式
        defined('iPHP_DEBUG_TRACE') OR define('iPHP_DEBUG_TRACE', $conf['php_trace']); //程序调试模式
        defined('iPHP_DB_DEBUG') OR define('iPHP_DB_DEBUG', $conf['db']); //数据调试
        defined('iPHP_DB_TRACE') OR define('iPHP_DB_TRACE', $conf['db_trace']); //SQL跟踪
        defined('iPHP_DB_EXPLAIN') OR define('iPHP_DB_EXPLAIN', $conf['db_explain']); //SQL解释
        defined('iPHP_TPL_DEBUG') OR define('iPHP_TPL_DEBUG', $conf['tpl']); //模板调试
        defined('iPHP_TPL_DEBUGGING') OR define('iPHP_TPL_DEBUGGING', $conf['tpl_trace']); //模板数据调试

        ini_set('display_errors', 'OFF');
        error_reporting(0);

        if (iPHP_DEBUG ||iPHP_DB_DEBUG||iPHP_TPL_DEBUG) {
            ini_set('display_errors', 'ON');
            error_reporting(E_ALL & ~E_NOTICE);
        }
        iPHP_DB_DEBUG   && iDB::$show_errors  = true;
        iPHP_DB_TRACE   && iDB::$show_trace   = true;
        iPHP_DB_EXPLAIN && iDB::$show_explain = true;
    }

}
