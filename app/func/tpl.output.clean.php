<?php
/*
 * iCMS for Template Lite plugin
 */
function tpl_output_clean(&$output,&$tpl){
    $output = preg_replace('/\t*/is', '', $output);
    $output = preg_replace('/\n+/is', '', $output);
    $output = preg_replace('/>\s+</is', '><', $output);
}
