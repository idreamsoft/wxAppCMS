<?php
function tpl_modifier_keys($array,$keys,$remove=false){
    // $array  = &$args[0];
    // $keys   = $args[1];
    // $remove = $args[2];
    $keyArray = explode(',', $keys);
    foreach ((array)$array as $key => $value) {
        if (in_array($key, $keyArray)) {
            if($remove) unset($array[$key]);
        }else{
            if(!$remove) unset($array[$key]);
        }
    }
    return $array;
}

