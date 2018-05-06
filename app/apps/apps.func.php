<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class appsFunc{

    public static function apps_list($vars){
        if(is_array($vars['apps'])){
            $appArray = $vars['apps'];
            $app = $appArray['app'];
            if($appArray['apptype']=='2'){
                $vars['app'] = $app;
                $app = 'content';
            }
            unset($vars['apps'],$vars['_app']);
        }else{
            $app = $vars['app'];
            unset($vars['app'],$vars['_app']);
        }
        $class = $app.'Func';
        $func  = $app.'_list';

        return iPHP::callback(array($class,$func),array($vars),array());
    }
/**
 * [apps_data description]
 * @param  [type] $vars [description]
 * @return [type]       [description]
 */
    public static function apps_data($vars){
        return apps::get_app($vars['id']);
    }
}
