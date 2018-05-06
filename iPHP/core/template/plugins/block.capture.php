<?php
/**
 * template_lite {capture}{/capture} block plugin
 *
 * Type:     block function
 * Name:     capture
 * Purpose:  removes content and stores it in a variable
 */
function tpl_block_capture($params, $content, &$tpl){
    if($content===null) return false;

	extract($params);

	if (isset($assign)){
		$tpl->assign($assign, $content);
	}else{
		$key = 'default';
		isset($name) && $key = $name;
		$tpl->_vars['capture'][$key] = $content;
	}
	return true;
}

