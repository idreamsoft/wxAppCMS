<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
function tpl_modifier_map($variable,$mapkeys,$func){
    $arg_list = func_get_args();
    $args = (array)array_slice($arg_list, 3);
    if(is_array($variable)){
        if(strpos($mapkeys,',')!==false){
            $mapkeys = explode(',', $mapkeys);
        }
        foreach ($variable as $key => $value) {
            if(is_array($mapkeys)){
                foreach ($mapkeys as $i => $mk) {
                    map_value($value[$mk],$func,$args);
                }
            }else{
                map_value($value[$mapkeys],$func,$args);
            }

            $variable[$key] = $value;
        }
    }
    return $variable;
}
function map_value(&$value,$func,$args){
    if(isset($value)){
        array_unshift($args,$value);
        $value = iPHP::callback($func,$args);
    }
}
