<?php
/**
 * iPHP explode modifier plugin
 *
 * Type:     modifier<br>
 * Name:     method<br>
 * Date:     17:39 2009-7-31
 * Purpose:  call object method
 * Input:    object
 * Example:  {$object|method:'method()'}
 * @author   coolmoo
 * @version 1.0
 * @param string
 * @param string
 * @return string
 */
function tpl_modifier_method($object,$methodStr){
	if(empty($object)) return;

	$val	= array();
	if($methodArray=explode(':',$methodStr))foreach($methodArray AS $methods){
		list($method,$arg)=explode('(',$methods);
		$arg	= explode(',',str_replace(')','',$arg));
		$val[]	= call_user_func_array(array($object,$method),$arg);
	}
    return implode('',$val);
}

