<?php
/*通用漏洞防护补丁v1.1
来源：阿里云
更新时间：2013-05-25
功能说明：防护XSS,SQL,代码执行，文件包含等多种高危漏洞
*/
defined('iPHP_WAF_POST') OR define('iPHP_WAF_POST',true);// 检测POST

class iWAF {
	public static $URL_ARRAY = array(
		'xss'=>"\\=\\+\\/v(?:8|9|\\+|\\/)|\\%0acontent\\-(?:id|location|type|transfer\\-encoding)",
	);
	public static $ARGS_ARRAY=array(
		'xss'   =>"[\\'\\\"\\;\\*\\<\\>].*\\bon[a-zA-Z]{3,15}[\\s\\r\\n\\v\\f]*\\=|\\b(?:expression)\\(|\\<script[\\s\\\\\\/]|\\<\\!\\[cdata\\[|\\b(?:eval|alert|prompt|msgbox)\\s*\\(|url\\((?:\\#|data|javascript)",
		'sql'   =>"[^\\{\\s]{1}(\\s|\\b)+(?:select\\b|update\\b|insert(?:(\\/\\*.*?\\*\\/)|(\\s)|(\\+))+into\\b).+?(?:from\\b|set\\b)|[^\\{\\s]{1}(\\s|\\b)+(?:create|delete|drop|truncate|rename|desc)(?:(\\/\\*.*?\\*\\/)|(\\s)|(\\+))+(?:table\\b|from\\b|database\\b)|into(?:(\\/\\*.*?\\*\\/)|\\s|\\+)+(?:dump|out)file\\b|\\bsleep\\([\\s]*[\\d]+[\\s]*\\)|benchmark\\(([^\\,]*)\\,([^\\,]*)\\)|(?:declare|set|select)\\b.*@|union\\b.*(?:select|all)\\b|(?:select|update|insert|create|delete|drop|grant|truncate|rename|exec|desc|from|table|database|set|where)\\b.*(charset|ascii|bin|char|uncompress|concat|concat_ws|conv|export_set|hex|instr|left|load_file|locate|mid|sub|substring|oct|reverse|right|unhex)\\(|(?:master\\.\\.sysdatabases|msysaccessobjects|msysqueries|sysmodules|mysql\\.db|sys\\.database_name|information_schema\\.|sysobjects|sp_makewebtask|xp_cmdshell|sp_oamethod|sp_addextendedproc|sp_oacreate|xp_regread|sys\\.dbms_export_extension)",
		'other' =>"\\.\\.[\\\\\\/].*\\%00([^0-9a-fA-F]|$)|%00[\\'\\\"\\.]"
	);
	public static function filter(){
		$referer      = empty($_SERVER['HTTP_REFERER']) ? array() : array($_SERVER['HTTP_REFERER']);
		$query_string = empty($_SERVER["QUERY_STRING"]) ? array() : array($_SERVER["QUERY_STRING"]);
		self::check_data($query_string,self::$URL_ARRAY);

		self::check_data($_GET);
		iPHP_WAF_POST && self::check_data($_POST);
		self::check_data($_COOKIE);
		self::check_data($_FILES);
		self::check_data($referer);
	}

	public static function check_data($arr,$waf=null) {
		$waf===null && $waf = self::$ARGS_ARRAY;
		foreach($arr as $key=>$value){
			if(is_array($key)||is_object($key)){
				self::check_data($key,$waf);
			}else{
				self::check($key,$waf);
			}

			if(is_array($value)||is_object($value)){
				self::check_data($value,$waf);
			}else{
				self::check($value,$waf);
			}
		}
	}
	public static function check($str,$waf=null){
		$waf===null && $waf = self::$ARGS_ARRAY;
		foreach($waf as $key=>$value){
			if (preg_match("/".$value."/is",$str)==1||preg_match("/".$value."/is",urlencode($str))==1){
				trigger_error('iWAF安全提示:当前操作可能存在某种危险代码',E_USER_ERROR);
			}
		}
	}
	public static function CSRF_token($key=null){
		$key===null &&$key = iPHP_KEY;
		define('iPHP_WAF_CSRF_TOKEN',sha1(md5(iPHP_KEY)));
		return iPHP_WAF_CSRF_TOKEN;
	}
	public static function CSRF_check(){
		$token = $_GET['CSRF_TOKEN']?$_GET['CSRF_TOKEN']:$_POST['CSRF_TOKEN'];

		if(defined('iPHP_WAF_CSRF') && iPHP_CSRF_POST){
			return true;
		}
		if(stripos($_SERVER['HTTP_REFERER'],iPHP_SELF)){
			return true;
		}
		if($_POST){
			if($token!==iPHP_WAF_CSRFKEY){
				trigger_error("TOKEN error",E_USER_ERROR);
			}
		}
	}
}
