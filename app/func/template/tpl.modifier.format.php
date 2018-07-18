<?php
/*
 * Template Lite plugin
 */
function tpl_modifier_format($value,$type){
    switch ($type) {
        case 'time':
            is_numeric($value) OR $value = strtotime($value);
            $h=$m=$s=0;
            if($value >= 60){
              $h = floor($value/60);
              $value = ($value%60);
            }
            if($value >= 1){
              $m = floor($value/60);
              $value = ($value%60);
            }
            $s = floor($value);

            return sprintf('%02d:%02d:%02d',$h,$m,$s);
        break;

        default:
            return $value;
        break;
    }
}
