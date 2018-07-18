<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) iiiPHP.com. All rights reserved.
 *
 * @author iPHPDev <master@iiiphp.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.1.0
 */
defined('iPHP') OR exit('What are you doing?');

class iUI extends iPagination{

	public static $break      = true;
	public static $dialog     = array();

	public static function lang($keys = '', $throw = true) {
		if (empty($keys)) {
			return false;
		}
        if(is_array($keys)){
            $args = $keys;
            $keys = $args[0];
        }

		$keyArray = explode(':', $keys);
		$count = count($keyArray);
		list($app, $do, $key, $flag) = $keyArray;


        if($app!=iPHP_APP){
            $path = iPHP_APP_DIR.'/'.$app.'/'.$app . '.lang.php';
            if (is_file($path)) {
                $langArray = iPHP::import($path, true);
               switch ($count) {
                    case 1:$msg = $langArray;break;
                    case 2:$msg = $langArray[$do];break;
                    case 3:$msg = $langArray[$do][$key];break;
                    case 4:$msg = $langArray[$do][$key][$flag];break;
                }
            }
        }
        if(empty($msg)){
            $def_path = iPHP_APP_CORE.'/'.iPHP_APP.'.lang.php';
            $langArray = iPHP::import($def_path, true);
            switch ($count) {
                case 1:$msg = $langArray;break;
                case 2:$msg = $langArray[$do];break;
                case 3:$msg = $langArray[$do][$key];break;
                case 4:$msg = $langArray[$do][$key][$flag];break;
            }
        }

        if(empty($msg)){
            return $keys;
        }
        if($args){
          $args[0] = $msg;
          $msg = call_user_func_array("sprintf", $args);
        }

        return $msg;
	}
	public static function json($a, $break = true, $ret = false) {
		$json = json_encode($a);
		$_GET['callback'] && $json = htmlspecialchars($_GET['callback']).'(' . $json . ')';
		$_GET['script'] && exit("<script>{$json};</script>");
		if ($ret) {
			return $json;
		}
		echo $json;
		$break && exit();
	}
	public static function js_callback($a, $callback = null, $node = 'parent') {
		$callback === null && $callback = htmlspecialchars($_GET['callback']);
		empty($callback) && $callback = 'callback';
		$json = json_encode($a);
		echo "<script>window.{$node}.{$callback}($json);</script>";
		exit;
	}
	public static function code($code = 0, $msg = '', $forward = '', $format = 'json') {
        if(is_array($msg)||@strstr($msg, ':')){
            $msg = iUI::lang($msg, false);
        }
		$a = array('code' => $code, 'msg' => $msg, 'forward' => $forward);
		if ($format == 'json') {
			iUI::json($a);
		}
		return $a;
	}
	public static function msg($info, $ret = false) {
        if(strpos($info,':#:')===false){
            $msg = $info;
        }else{
			list($label, $icon, $content) = explode(':#:', $info);
	        if(iPHP_SHELL){
	        	if($label=="success"){
	        		$msg ="\033[32m {$content} \033[0m";//green
	        	}else{
	        		$msg ="\033[31m {$content} \033[0m";//red
	        	}
	        }else{
	            $msg = '<div class="iPHP-msg"><span class="label label-'.$label.'">';
				$icon && $msg .= '<i class="fa fa-' . $icon . '"></i> ';
				if (strpos($content, ':') !== false &&!preg_match("/<\/([^>]+?)>/is",$content)) {
					$lang = iUI::lang($content, false);
					$lang && $content = $lang;
				}
            	$msg.= $content.'</span></div>';
	        }
		}
        if(strtoupper(self::$dialog['msgType'])=='ARRAY'){
            return compact('label', 'icon', 'content');
        }
    	if($ret) return $msg;
		echo $msg;
	}
	public static function js($str = "js:", $ret = false) {
		$type = substr($str, 0, strpos($str, ':'));
		$act = substr($str, strpos($str, ':') + 1);
		switch ($type) {
			case 'js':
				$act && $code = $act;
                $act == "-1" && $code = 'iTOP.history.go(-1);';
                $act == "0" && $code = '';
				$act == "1" && $code = 'iTOP.location.href=iTOP.location.href;';
			break;
			case 'url':
                $act == "-1" && $act = iPHP_REFERER;
                $act == "1" && $act = iPHP_REFERER;
				$code = "iTOP.location.href='" . $act . "';";
			break;
			case 'src':
				$code = "iTOP.$('#iPHP_FRAME').attr('src','" . $act . "');";
			break;
			default:$code = '';
		}

		if ($ret) {
			return $code;
		}

		echo '<script type="text/javascript">' . $code . '</script>';
		self::$break && exit();
	}
    public static function error($value,$type='app') {
        if(iPHP_SHELL){
            $value = str_replace(array("<b>", "</b>"), array("\033[31m","\033[0m"), $value);
            $value = html2text($value);
            echo $value.PHP_EOL;
            exit;
        }
        if (isset($_GET['frame'])) {
            self::$dialog['modal'] = true;
            $type =='system' && $wrong = "The system has been wrong!\n".
                "You can send a message to ".iPHP_APP_MAIL." feedback this error!\n".
                "We will deal with it in time. Thank you.\n\n";
            $value = str_replace("\n", '<br />', $wrong.$value);
            self::dialog("warning:#:warning:#:{$value}",'js:1',30000000);
            exit;
        }
        if ($_POST) {
            if(iHttp::is_ajax()){
                self::code(0,$value);
            }else{
                $value = html2text($value);
                $value = html2js($value);
                self::js('js:window.alert("'.$value.'")');
            }
            exit;
        }
        @header('HTTP/1.1 500 Internal Server Error');
        @header('Status: 500 Internal Server Error');
        @header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        @header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        @header("Cache-Control: no-store, no-cache, must-revalidate");
        @header("Cache-Control: post-check=0, pre-check=0", false);
        @header("Pragma: no-cache");
        @header("X-iPHP-ERROR:" . $errstr);
        $value = str_replace("\n", '<br />', $value);
        exit($value);
    }
	public static function warning($info) {
		return self::msg('warning:#:warning:#:' . $info);
	}
	public static function alert($msg, $js = null, $s = 3,$flag='warning:#:warning:#:') {
		if (iUI::$dialog['alert'] === 'window') {
			iUI::js("js:window.alert('{$msg}')");
		}

		self::$dialog = array_merge(
            (array)self::$dialog,
            array(
    			'id'         => iPHP_APP.'-DIALOG-ALERT',
    			'skin'       => iPHP_APP.'_dialog_alert',
    			'modal'      => true,
    			'quickClose' => true,
    			'width'      => 360,
    			'height'     => 120,
		    )
        );
		return self::dialog($flag.$msg, $js, $s);
	}
	public static function success($msg, $js = null, $s = 3) {
        return self::alert($msg, $js, $s,'success:#:check:#:');
	}
    public static function set_dialog($key,$value) {
        self::$dialog[$key] = $value;
    }
    public static function close_dialog($top=true) {
        $obj = ($top?'top.':'').'iCMS.UI.$dialog';
        echo '<script>if('.$obj.') '.$obj.'.close().remove();</script>';
    }
	public static function dialog($info = array(), $js = 'js:', $s = 3, $buttons = null, $update = false) {
		$info = (array) $info;
		$title = $info[1] ? $info[1] : '提示信息';
        $content = self::msg($info[0],true);
        if(self::$dialog['callback']){
            return iPHP::callback(self::$dialog['callback'],array($content));
        }
        if(iPHP_SHELL){
        	echo $content;
        	return false;
        }
		$content =
			'<table class="ui-dialog-table" align="center">'.
				'<tr>'.
					'<td valign="middle">' . $content . '</td>'.
				'</tr>'.
			'</table>';
		$content = str_replace(array("\n","\r","\\"), array('','',"\\\\"), $content);
		$content = addslashes($content);
        $dialog_id = self::$dialog['id'] ? self::$dialog['id'] : 'iPHP-DIALOG';
		$options = array(
			"time:null","api:'iPHP'",
			"id:'" . $dialog_id. "'",
			"title:'" . (self::$dialog['title'] ? self::$dialog['title'] : iPHP_APP) . " - {$title}'",
			"modal:" . (self::$dialog['modal'] ? 'true' : 'false'),
			"width:'" . (self::$dialog['width'] ? self::$dialog['width'] : 'auto') . "'",
			"height:'" . (self::$dialog['height'] ? self::$dialog['height'] : 'auto') . "'",
		);
		if(isset(self::$dialog['quickClose'])){
			$options[] = "quickClose:" . (self::$dialog['quickClose'] ? 'true' : 'false');
		}
		if(isset(self::$dialog['skin'])){
			$options[] = "skin:'" . self::$dialog['skin']. "'";
		}

		//$content && $options[]="content:'{$content}'";
		$auto_func = 'd.close().remove();';
		$func = iUI::js($js, true);
		if ($func) {
			$ok = 'okValue: "确 定",ok: function(){' . $func . '}';
			// $buttons OR $options[] = $ok
			$auto_func = $func . 'd.close().remove();';
		}
        $IS_FRAME = false;
		if (is_array($buttons)) {
			$okbtn = "{value:'确 定',callback:function(){" . $func . "},autofocus: true}";
			foreach ($buttons as $key => $val) {
				$val['id'] && $id = "id:'" . $val['id'] . "',";
				$val['js'] && $func = $val['js'] . ';';
				$val['url'] && $func = "iTOP.location.href='{$val['url']}';";
                if($val['src']){
                    $func = "iTOP.$('#iPHP_FRAME').attr('src','{$val['src']}');return false;";
                    $IS_FRAME = true;
                }
				$val['target'] && $func = "iTOP.window.open('{$val['url']}','_blank');";
                if($val['close']===false){
                    $func.= "return false;";
                }
                $val['time'] && $s = $val['time'];

                if($func){
                    $buttonA[]="{".$id."value:'".$val['text']."',callback:function(){".$func."}}";
                    $val['next'] && $auto_func = $func;
                }
            }
			$button = implode(",", $buttonA);
		}else{
			self::$dialog['ok'] OR $options[] = $ok;
		}
		self::$dialog['ok'] && $options[] = 'okValue: "确 定",ok: function(){'.self::$dialog['ok:js'].'}';
		self::$dialog['cancel'] && $options[] = 'cancelValue: "取 消",cancel: function(){'.self::$dialog['cancel:js'].'}';

		$dialog = '';
        if ($update) {
            if($update==='FRAME'||$IS_FRAME){
                $dialog = 'var iTOP = window.top,d = iTOP.dialog.get("'.$dialog_id.'");';
            }
			$auto_func = $func;
		} else {
            $dialog.= 'var iTOP = window.top,';
			$dialog.= 'options = {' . implode(',', $options) . '},d = iTOP.' . iPHP_APP . '.UI.dialog(options);';
			// if(self::$dialog_lock){
			// 	$dialog.='d.showModal();';
			// }else{
			// 	$dialog.='d.show();';
			// }
		}
		$button && $dialog .= "d.button([$button]);";
		$content && $dialog .= "d.content('$content');";

		$s <= 30 && $timeout = $s * 1000;
		$s > 30 && $timeout = $s;
		$s === false && $timeout = false;
		if ($timeout) {
			$dialog .= 'window.setTimeout(function(){' . $auto_func . '},' . $timeout . ');';
		} else {
			$update && $dialog .= $auto_func;
		}
		echo self::$dialog['code'] ? $dialog : '<script>' . $dialog . '</script>';
		self::$break && exit();
	}

	public static function page($conf) {
        return iPagination::make($conf);
	}
    public static function page_content($content,$page,$total,$count,$mode=null,$chapterArray=null){
        return iPagination::content($content,$page,$total,$count,$mode,$chapterArray);
    }
    public static function permission($p = '', $ret = 'alert') {
    	$msg = "您没有[$p]的访问权限!";
    	if(iPHP_SHELL){
    		echo $msg."\n";
	        exit;
    	}
    	if (isset($_GET['frame'])) {
    		iUI::alert($msg);
    		exit;
    	}
        if(iHttp::is_ajax()){
            $array = array('code'=>0,'msg'=>$msg);
            echo json_encode($array);
            exit;
        }
		if ($_POST) {
	        echo '<script>top.alert("' . $msg . '")</script>';
	        exit;
	    }
        if ($ret == 'alert') {
            iUI::alert($msg);
            exit;
        } elseif ($ret == 'page') {
            exit($msg);
        }
    }
    public static function check($o) {
        return $o?'<font color="green"><i class="fa fa-check"></i></font>':'<font color="red"><i class="fa fa-times"></i></font>';
    }
    public static function flush_start() {
		@header('X-Accel-Buffering: no');
        ob_start();
        ob_end_clean() ;
        ob_end_flush();
        ob_implicit_flush(true);
    }
    public static function flush() {
		flush();
		ob_flush();
    }
}
