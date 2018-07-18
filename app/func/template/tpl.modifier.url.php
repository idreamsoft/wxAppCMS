<?php
/*
 * Template Lite plugin
 */
function tpl_modifier_url($query,$url=null){
    return iURL::make($query,$url);
}
