<?php
/*
 * template_lite plugin
 *
 * Type:     function
 * Name:     array
 * Version:  0.1
 * Examples:
 * e1.{array key="value" key1="value1"}
 * e2.{array as="ar" flag="col" a[]="1" b[]="2"}
 * e3.{array as[]="ar" a="1" b="2"}
 */
function tpl_function_array($params, &$tpl){
    $key = $params['assign']?:'array';
    $params['as'] && $key = $params['as'];
    if($params['as[]']){
        $mas = $key = $params['as[]'];
        $array = $tpl->_vars[$key];
    }
    unset($params['assign'],$params['as'],$params['as[]']);
    // Examples:e1
    $value = $params;
    // Examples:e2
    if($params['flag']=='col'){
        unset($params['flag']);
        $_array = $tpl->_vars[$key];
        foreach ((array)$params as $pk => $pv) {
            if(substr($pk, -2)=='[]'){
                $_pk = substr($pk,0,-2);
                if($_array[$_pk]){
                    array_push($_array[$_pk],$pv);
                }else{
                    $_array[$_pk] = array($pv);
                }
            }
        }
        $tpl->assign($key,$_array);
        return;
    }
    // Examples:e3
    if($mas){
        if($array){
            array_push($array,$value);
            $value = $array;
        }else{
            $value = array($value);
        }
    }
    // var_dump($value);
    $tpl->assign($key,$value);
}
