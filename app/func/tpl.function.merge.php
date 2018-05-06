<?php
/*
 * template_lite plugin
 *
 * Type:     function
 * Name:     cycle
 * Version:  0.1
 * Purpose:  cycle through given values

 * Examples: {merge values="#eeeeee,#d0d0d0d"}

 */
function tpl_function_merge($params, &$tpl){
    if (empty($params['assign'])){
        $tpl->trigger_error("merge: missing assign parameter");
        return;
    }
    $assign = $params['assign'];
    $glue = $params['glue'];
    unset($params['assign'],$params['glue']);
    $result = implode($glue, $params);
	$tpl->assign($assign,$result);
}
