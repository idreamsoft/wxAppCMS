<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class plugin{
    public static $flag = array();
    public static function init($class=null) {
        $class = str_replace('plugin_', '', $class);
        $class && self::$flag[$class] = true;
    }
    public static function library($file) {
        $path = iPHP_APP_DIR . '/'.__CLASS__.'/library/'.$file.'.php';
        iPHP::import($path);
    }
    public static function import($file) {
        $path = iPHP_APP_DIR . '/'.__CLASS__.'/'.$file.'.php';
        iPHP::import($path);
    }
}

