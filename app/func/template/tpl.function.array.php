<?php
/*
 * template_lite plugin
 *
 * Type:     function
 * Name:     array
 * Version:  0.1
 * Examples: {array key="value" key1="value1"}

 */
function tpl_function_array($params, &$tpl){
    $key = $params['assign']?:'array';
    $params['as'] && $key = $params['as'];
    if($params['as[]']){
        $mkey  = $params['as[]'];
        $array = $tpl->_vars[$mkey];
    }
    unset($params['assign'],$params['as'],$params['as[]']);

    $value = $params;

    if($array){
        array_push($array,$value);
        $value = $array;
    }else{
        $mkey && $value = array($value);
    }

    if($mkey){
        $tpl->assign($mkey,$value);
    }else{
        $tpl->assign($key,$value);
    }
}
