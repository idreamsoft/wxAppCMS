<?php
/*
 * template_lite plugin
 *
 * Type:     function
 * Name:     merge
 * Version:  0.1
 * Examples: {implode key="value" key1="value1"}

 */
function tpl_function_implode($params, &$tpl){
    $assign = $params['assign']?:'implode';
    $glue = $params['glue'];
    unset($params['assign'],$params['glue']);
    $result = implode($glue, $params);
	$tpl->assign($assign,$result);
}
