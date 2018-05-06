<?php
/**
 * template_lite replace modifier plugin
 *
 * Type:     modifier
 * Name:     replace
 * Purpose:  Wrapper for the PHP 'str_replace' function
 * Credit:   Taken from the original Smarty
 *           http://smarty.php.net
 * ADDED: { $text|replace:",,,,":",,,," }
 */
function tpl_modifier_replace($string, $search, $replace)
{
	if(strpos($search,',')){
		$s=explode(',',$search);
		$r=explode(',',$replace);
		foreach($s AS $k=>$v){
			$string=str_replace($v,$r[$k], $string);
		}
	}else{
		$string=str_replace($search, $replace, $string);
	}
		return $string;
}
