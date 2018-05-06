<?php
/*
 * Template Lite plugin
 */
function tpl_modifier_uri($query,$url=null){
    return iURL::make($query,$url);
}
