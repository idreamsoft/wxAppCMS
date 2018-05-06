<?php
/**
 * Basic Security Filter Service
 * @author liuhui@2010-6-30 zzZero.L@2010-9-15
 * @status building
 * @from phpwind
 */
class iSecurity {
	/**
	 * 整型数过滤
	 * @param $param
	 * @return int
	 */
	public static function int($param) {
		return intval($param);
	}
	/**
	 * 字符过滤
	 * @param $param
	 * @return string
	 */
	public static function str($param) {
		return trim($param);
	}
	/**
	 * 是否对象
	 * @param $param
	 * @return boolean
	 */
	public static function isObj($param) {
		return is_object($param) ? true : false;
	}
	/**
	 * 是否数组
	 * @param $params
	 * @return boolean
	 */
	public static function isArray($params) {
		return (!is_array($params) || !count($params)) ? false : true;
	}
	/**
	 * 变量是否在数组中存在
	 * @param $param
	 * @param $params
	 * @return boolean
	 */
	public static function inArray($param, $params) {
		return (!in_array((string)$param, (array)$params)) ? false : true;
	}
	/**
	 * 是否是布尔型
	 * @param $param
	 * @return boolean
	 */
	public static function isBool($param) {
		return is_bool($param) ? true : false;
	}
	/**
	 * 是否是数字型
	 * @param $param
	 * @return boolean
	 */
	public static function isNum($param) {
		return is_numeric($param) ? true : false;
	}

	/**
	 * html转换输出
	 * @param $param
	 * @return string
	 */
	public static function htmlEscape($param) {
		return trim(str_replace("\0", "&#0;", htmlspecialchars($param, ENT_QUOTES, 'utf-8')));
	}
	/**
	 * 过滤标签
	 * @param $param
	 * @return string
	 */
	public static function stripTags($param) {
		return trim(strip_tags($param));
	}
	/**
	 * 初始化$_GET/$_POST全局变量
	 * @param $keys
	 * @param $method
	 * @param $cvtype
	 */
	public static function GP($keys, $method = null, $cvtype = 1,$istrim = true) {
		!is_array($keys) && $keys = array($keys);
		foreach ($keys as $key) {
			if ($key == 'GLOBALS') continue;
			$GLOBALS[$key] = NULL;
			if ($method != 'P' && isset($_GET[$key])) {
				$GLOBALS[$key] = $_GET[$key];
			} elseif ($method != 'G' && isset($_POST[$key])) {
				$GLOBALS[$key] = $_POST[$key];
			}
			if (isset($GLOBALS[$key]) && !empty($cvtype) || $cvtype == 2) {
				$GLOBALS[$key] = self::escapeChar($GLOBALS[$key], $cvtype == 2, $istrim);
			}
		}
	}

	/**
	 * 指定key获取$_GET/$_POST变量
	 * @param $key
	 * @param $method
	 */
	public static function getGP($key, $method = null) {
		if ($method == 'G' || $method != 'P' && isset($_GET[$key])) {
			$value = $_GET[$key];
		}else{
			$value = $_POST[$key];
		}
		return self::escapeStr($value);
	}
	/**
	 * 全局变量过滤
	 */
	public static function filter() {
		$allowed = array('GLOBALS' => 1,'_GET' => 1,'_POST' => 1,'HTTP_RAW_POST_DATA' => 1,'_COOKIE' => 1,'_FILES' => 1,'_SERVER' => 1,'_APP' => 1);
		foreach ($GLOBALS as $key => $value) {
			if (!isset($allowed[$key])) {
				$GLOBALS[$key] = null;
				unset($GLOBALS[$key]);
			}
		}

		if (!get_magic_quotes_gpc()) {
			self::_addslashes($_POST);
			self::_addslashes($_GET);
			self::_addslashes($_COOKIE);
			self::_addslashes($_FILES);
		}
		self::getServer(array(
			'HTTP_REFERER','HTTP_HOST','HTTP_X_FORWARDED_FOR','HTTP_USER_AGENT',
			'HTTP_CLIENT_IP','HTTP_SCHEME','HTTPS','PHP_SELF','REMOTE_ADDR',
			'REQUEST_URI','REQUEST_METHOD','SCRIPT_NAME','REQUEST_TIME',
			'SERVER_SOFTWARE','SERVER_ADDR','SERVER_PORT',
			'X-Requested-With','HTTP_X_REQUESTED_WITH',
			'QUERY_STRING','argv','argc',
			'Authorization','HTTP_AUTHORIZATION'
		));

	}
	public static function filter_path($text) {
	    $text = str_replace('\\', '/', $text);
	    $text = str_replace(iPATH,iPHP_PROTOCOL,$text);
	    $pieces = explode('/', iPATH);
	    $count = count($pieces);
	    for ($i=0; $i < ceil($count/2); $i++) {
			$output = array_slice($pieces, 0, $count-$i);
			$path   = implode('/', $output);
	        if(stripos($text, $path)!==false){
	            $text = str_replace($path,iPHP_PROTOCOL,$text);
	        }
	    }
		return $text;
	}
	/**
	 * 路径转换
	 * @param $fileName
	 * @param $ifCheck
	 * @return string
	 */
	public static function escapePath($fileName, $ifCheck = true) {
		if (!self::_escapePath($fileName, $ifCheck)) {
			trigger_error('What are you doing?',E_USER_ERROR);
		}
		return $fileName;
	}
	/**
	 * 私用路径转换
	 * @param $fileName
	 * @param $ifCheck
	 * @return boolean
	 */
	public static function _escapePath($fileName, $ifCheck = true) {
		$tmpname = strtolower($fileName);
		$tmparray = array('://',"\0");
		$ifCheck && $tmparray[] = '..';
		if (str_replace($tmparray, '', $tmpname) != $tmpname) {
			return false;
		}
		return true;
	}
	/**
	 * 目录转换
	 * @param unknown_type $dir
	 * @return string
	 */
	public static function escapeDir($dir) {
		$dir = str_replace(array("'",'#','=','`','$','%','&',';'), '', $dir);
		return rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $dir), '/');
	}
	/**
	 * 通用多类型转换
	 * @param $mixed
	 * @param $isint
	 * @param $istrim
	 * @return mixture
	 */
	public static function escapeChar($mixed, $isint = false, $istrim = false) {
		if (is_array($mixed)) {
			foreach ($mixed as $key => $value) {
				$mixed[$key] = self::escapeChar($value, $isint, $istrim);
			}
		} elseif ($isint) {
			$mixed = (int) $mixed;
		} elseif (!is_numeric($mixed) && ($istrim ? $mixed = trim($mixed) : $mixed) && $mixed) {
			$mixed = self::escapeStr($mixed);
		}
		return $mixed;
	}
	/**
	 * 字符转换
	 * @param $string
	 * @return string
	 */
	public static function escapeStr($string) {
	    if(is_array($string)) {
	        foreach($string as $key => $val) {
	            $string[$key] = self::escapeStr($val);
	        }
	    } else {
	        $string = str_replace(array("\0","\x0B", "%00"), '', $string);
			$string = str_replace(array('&', '"',"'"), array('&amp;', '&quot;','&#039;'), $string);
	    	$string = str_replace('\\\\', '&#92;', $string);
	        $string = str_replace(array("%3C", '<'), '&lt;', $string);
	        $string = str_replace(array("%3E", '>'), '&gt;', $string);
			$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',$string);
	    }
	    return $string;
	}
	public static function html_decode($string) {
		$string = htmlspecialchars_decode($string);
		$string = str_replace('&#92;', '\\', $string);
		return $string;
	}
	/**
	 * 变量检查
	 * @param $var
	 */
	public static function checkVar(&$var) {
		if (is_array($var)) {
			foreach ($var as $key => $value) {
				self::checkVar($var[$key]);
			}
		} elseif (str_replace(array('<iframe','<meta','<script'), '', $var) != $var) {
			trigger_error('XXS',E_USER_ERROR);
		}else{
			$var = str_replace(array('..',')','<','='), array('&#46;&#46;','&#41;','&#60;','&#61;'), $var);
		}
	}

	/**
	 * 变量转义
	 * @param $array
	 */
	public static function _addslashes(&$array) {
		if (is_object($array)) {
			foreach ($array as $key => $value) {
				if (is_object($value)) {
					self::_addslashes($value);
				} else {
					$array->$key = addslashes($value);
				}
			}
		}elseif (is_array($array)) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					self::_addslashes($value);
				} else {
					$array[$key] = addslashes($value);
				}
			}
		}else{
			$array = addslashes($array);
		}
	}

	/**
	 * 获取服务器变量
	 * @param $keys
	 * @return string
	 */
	public static function getServer($keys) {
		// Fix for IIS when running with PHP ISAPI
		if ( empty($_SERVER['REQUEST_URI'] ) || ( php_sapi_name() != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//',$_SERVER['SERVER_SOFTWARE'] ) ) ) {
		    if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
		       $_SERVER['REQUEST_URI'] =$_SERVER['HTTP_X_ORIGINAL_URL'];
		    }else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		       $_SERVER['REQUEST_URI'] =$_SERVER['HTTP_X_REWRITE_URL'];
		    }else {
		        // Use ORIG_PATH_INFO if there is no PATH_INFO
		        if ( !isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO']) )
		           $_SERVER['PATH_INFO'] =$_SERVER['ORIG_PATH_INFO'];

		        // Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
		        if ( isset($_SERVER['PATH_INFO']) ) {
		            if ($_SERVER['PATH_INFO'] ==$_SERVER['SCRIPT_NAME'] )
		               $_SERVER['REQUEST_URI'] =$_SERVER['PATH_INFO'];
		            else
		               $_SERVER['REQUEST_URI'] =$_SERVER['SCRIPT_NAME'] .$_SERVER['PATH_INFO'];
		        }

		        // Append the query string if it exists and isn't null
		        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
		           $_SERVER['REQUEST_URI'] .= '?' .$_SERVER['QUERY_STRING'];
		        }
		    }
		}

		// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
		if ( isset($_SERVER['SCRIPT_FILENAME']) && ( strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7 ) )
		   $_SERVER['SCRIPT_FILENAME'] =$_SERVER['PATH_TRANSLATED'];

		// Fix for ther PHP as CGI hosts
		if (strpos($_SERVER['SCRIPT_NAME'], 'php.cgi') !== false)
		    unset($_SERVER['PATH_INFO']);

		if ( empty($_SERVER['PHP_SELF']) )
		   $_SERVER['PHP_SELF'] = preg_replace("/(\?.*)?$/",'',$_SERVER["REQUEST_URI"]);


		foreach ($_SERVER as $key=>$sval){
			if (in_array($key, $keys)) {
				$_SERVER[$key] = str_replace(array('<','>','"',"'",'%3C','%3E','%22','%27','%3c','%3e'), '',$sval);
			}else{
				unset($_SERVER[$key]);
			}
		}
	}
    public static function encoding($string,$code='UTF-8') {
        $encode = mb_detect_encoding($string, array("ASCII","UTF-8","GB2312","GBK","BIG5"));
        if(strtoupper($encode)!=$code){
            if (function_exists('mb_convert_encoding')) {
                $string = mb_convert_encoding($string,$code,$encode);
            } elseif (function_exists('iconv')) {
                $string = iconv($encode,$code, $string);
            }
        }
        return $string;
    }
}
