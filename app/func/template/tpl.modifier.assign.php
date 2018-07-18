<?php
/*
 * Template Lite plugin
 */
function tpl_modifier_assign($value,$key){
    iView::assign($key,$value);
}
