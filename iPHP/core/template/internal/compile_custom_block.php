<?php
/**
 * Template Lite compile custom block - template internal module
 *
 * Type:	 template
 * Name:	 compile_custom_block
 */

function compile_custom_block($function, $modifiers, $arguments, &$_result, &$object){
	if ($function[0] == '/'){
		$start_tag = false;
		$function = substr($function, 1);
	}else{
		$start_tag = true;
	}
	$reference = false;
	if ($function = $object->_plugin_exists($function, "block")){
		if ($start_tag){
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
			if($reference){
				$_result = "<?php \$block_args = &".$ref_args.";";
			}else{
				$_result = "<?php \$block_args = array(".implode(',', (array)$_args).");";
			}
			$_result .= '$block_content = '.$function . '($block_args, null, $this); ';
			$_result .= 'if(!$block_content){';
			$_result .= 'ob_start(); ?>';
		}else{
			$_result .= '<?php $block_content = ob_get_contents(); ob_end_clean(); ';
			$_result .= '$block_content = '.$function . '($block_args, $block_content, $this);';
			$modifiers && $_result .= '$block_content = ' . $object->_parse_modifier('$block_content', $modifiers) . '; ';
			$_result .= '}?>';
			$_result .= '<?php if($block_content!==true){ echo $block_content;} ?>';
			$_result .= '<?php unset($block_args,$block_content);?>';
		}
		return true;
	}else{
		return false;
	}
}
