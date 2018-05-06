<?php
/**
 * Template Lite compile custom function - template internal module
 *
 * Type:	 template
 * Name:	 compile_custom_function
 */

function compile_custom_function($function, $modifiers, $arguments, &$_result, &$object){
	$reference = false;
	if ($function = $object->_plugin_exists($function, "function")){
		$_args = $object->_parse_arguments($arguments);

		if(isset($_args['&'])){
			$reference = true;
			$ref_args = $_args['&'];
		}
		foreach($_args as $key => $value){
			if (is_bool($value)){
				$value = $value ? 'true' : 'false';
			}
			if (is_null($value)){
				$value = 'null';
			}
			$_args[$key] = "'$key' => $value";
		}
		$_result = '<?php ';
		$reference OR $_result.= ' echo ';
		if ($modifiers){
			$_result .= $object->_parse_modifier($function . '(array(' . implode(',', (array)$_args) . '), $this)', $modifiers) . '; ';
		}else{
			if($reference){
				$_result .= $function . '(' . $ref_args  . ', $this);';
			}else{
				$_result .= $function . '(array(' . implode(',', (array)$_args) . '), $this);';
			}
		}
		$_result .= '?>';
		return true;
	}
	else
	{
		return false;
	}
}
