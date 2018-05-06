<?php
/*
 * Template Lite plugin
 */
function tpl_modifier_markdown($content,$htmlspecialchars=false){
    $a = array(
        'markdown'         => true,
        'htmlspecialchars' => $htmlspecialchars,
    );
    return iPHP::callback(array("plugin_markdown","HOOK"),array($content,&$a));
}
