<?php
/**
 * template_lite default modifier plugin
 *
 * Type:     modifier
 * Name:     default
 * Purpose:  designate default value for empty variables
 * Credit:   Taken from the original Smarty
 *           http://smarty.php.net
 */
function tpl_modifier_default($var, $default = '',$empty=true){
    if($empty){
        $is = empty($var);
    }else{
        $is = (!isset($string) || $string === '');
    }
    if ($is){
        return $default;
    }else{
        return $var;
    }
}
