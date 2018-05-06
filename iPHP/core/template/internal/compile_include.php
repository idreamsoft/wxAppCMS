<?php
/**
 * Template Lite
 *
 * Type:	 compile
 * Name:	 section_start
 *
 * ADDED: { include file='./filename' import=true }
 */

function compile_include($arguments, &$object){
	$_args            = $object->_parse_arguments($arguments);
	$arg_list         = array();
	$_args['file'] OR $object->trigger_error("missing 'file' attribute in include tag", E_USER_ERROR, __FILE__, __LINE__);
	foreach ($_args as $arg_name => $arg_value){
		if ($arg_name == 'file'){
			strpos($arg_value,'..') && $object->trigger_error("'file' attribute has '..'", E_USER_ERROR);
			$include_file = $arg_value;
			continue;
		}else if ($arg_name == 'assign'){
			$assign_var = $arg_value;
			continue;
		}
		if (is_bool($arg_value)){
			$arg_value = $arg_value ? 'true' : 'false';
		}
		if(isset($assign_var)){
			$arg_list[] = "'$arg_name' => $arg_value";
		}else{
			$object->_vars[$arg_name] = $object->_dequote($arg_value);
		}
	}

	$object->_include_file = true;
	if (isset($assign_var)){
		$output = '<?php $_templatelite_tpl_vars = $this->_vars; $this->_include_file = true;' .
			"\$this->assign(" . $assign_var . ", \$this->_fetch_compile_include(" . $include_file . ", array(".implode(',', (array)$arg_list).")));\n" .
			"\$this->_vars = \$_templatelite_tpl_vars;\n" .
			"\$this->_include_file = false;unset(\$_templatelite_tpl_vars);\n" .
			' ?>';
	}else{
		$include_file = $object->_dequote($include_file);
		if ($_args['import']){
			if($_args['import']=="html"){
				$output = $object->_fetch_compile($include_file,true);
			}else{
				$file   = $object->_fetch_compile($include_file,'file');
				$output = '<?php include "'.$file.'"; ?>';
			}
		}else{
			$output = $object->_fetch_compile($include_file,'code');
		}
	}
	$object->_include_file = false;
	return $output;
}
