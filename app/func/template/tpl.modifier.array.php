<?php
/*
 * Template Lite plugin
 */
function tpl_modifier_array($array,$key,$value=null){
    $array[$key] = $value;
    return $array;
}
