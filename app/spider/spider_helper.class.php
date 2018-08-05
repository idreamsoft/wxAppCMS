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

class spider_helper {
    public static $content = null;
    public static $data    = null;
    public static $rule    = null;
    public static function init($content,$data,$rule){
        self::$content = $content;
        self::$data    = $data;
        self::$rule    = $rule;
    }

    public static function trim() {
        if(is_array($content)){
            self::$content = array_map('trim', self::$content);
        }else{
            self::$content = str_replace('&nbsp;','',trim(self::$content));
        }
    }
    public static function json_decode() {
    }
}
