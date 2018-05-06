<?php
/**
 * iPHP explode modifier plugin
 *
 * Type:     modifier<br>
 * Name:     call<br>
 * Date:     2017-04-01
 * Purpose:  call object method
 * Input:    object
 * Example:  {$object|call:'method':'1':'2':'3'}
 * @author   coolmoo
 * @version 1.0
 * @param string
 * @param string
 * @return string
 */
function tpl_modifier_call($object,$method){
    $arg_list = func_get_args ();
    if(strpos($method,'::')!==false){
        $array = array_merge((array)$object,array_slice($arg_list, 2));
        $call  = $method;
    }else if(!is_object($object) && strpos($object,'::')!==false){
        $array = array_slice($arg_list, 1);
        $call  = $object;
    }else {
        $array = array_slice($arg_list, 2);
        $call  = array($object,$method);
    }

	if(!is_callable($call)) return;
    return call_user_func_array($call,$array);
}

