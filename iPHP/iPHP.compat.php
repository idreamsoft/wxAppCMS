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
if (!function_exists('get_magic_quotes_gpc')) {
	function get_magic_quotes_gpc(){
		return false;
	}
}
if (!function_exists('gc_collect_cycles')) {
	function gc_collect_cycles(){
		return false;
	}
}
if (!function_exists('json_last_error_msg')){
    function json_last_error_msg(){
        switch (json_last_error()) {
            case  JSON_ERROR_NONE :
            break;
            case JSON_ERROR_DEPTH:
                $msg = 'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg = 'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $msg = 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $msg = 'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
        }
        return $msg;
    }
}

function days_in_month($year,$month) {
    if (!function_exists('cal_days_in_month')) {
        return date('t',mktime(0,0,0,$month+1,0,$year));
    } else {
        return cal_days_in_month(CAL_GREGORIAN,$month,$year);
    }
}

function count_days($formdate,$todate){
    return round(abs(strtotime($formdate)-strtotime($todate))/3600/24);
}
function sortnum($a, $b){
    if ($a["sortnum"] == $b["sortnum"]) {
        return 0;
    }
    return ($a["sortnum"] < $b["sortnum"]) ? -1 : 1;
}

function bitscale($a) {
	$a['th']==0 && $a['th']=9999;
	if( $a['w']/$a['h'] > $a['tw']/$a['th']  && $a['w'] >$a['tw'] ){
		$a['h'] = ceil($a['h'] * ($a['tw']/$a['w']));
		$a['w'] = $a['tw'];
	}else if( $a['w']/$a['h'] <= $a['tw']/$a['th'] && $a['h'] >$a['th']){
		$a['w'] = ceil($a['w'] * ($a['th']/$a['h']));
		$a['h'] = $a['th'];
	}
	return $a;
}
function number($val) {
  return preg_replace('~[^0-9]+~', '', $val);
}
function num10K($num){
    if($num<10000){
        return $num;
    }else{
        return round($num/10000,1) . 'K';
    }
}
function format_time($time, $flag = 's'){
    $value = array(
        "years" => 0, "days" => 0, "hours" => 0,
        "minutes" => 0, "seconds" => 0,
    );
    if($time >= 31556926){
        $value["years"] = floor($time/31556926);
        $time = ($time%31556926);
    }
    if($time >= 86400){
        $value["days"] = floor($time/86400);
        $time = ($time%86400);
    }
    if($time >= 3600){
        $value["hours"] = floor($time/3600);
        $time = ($time%3600);
    }
    if($time >= 60){
        $value["minutes"] = floor($time/60);
        $time = ($time%60);
    }
    $value["seconds"] = floor($time);

    $unit_map = array(
        's'=>array('d','h','m','s'),
        'l'=>array('days','hours','minutes','seconds'),
        'cn'=>array('天','小时','分钟','秒'),
    );
    $t = '';
    $unit = $unit_map[$flag];
    $value["days"]   && $t.= $value["days"] .$unit[0].' ';
    $value["hours"]  && $t.= $value["hours"] .$unit[1].' ';
    $value["minutes"]&& $t.= $value["minutes"] .$unit[2].' ';
    $value["seconds"]&& $t.= $value["seconds"] .$unit[3];

    return $t;
}
function format_date($date,$isShowDate=true){
    $limit = time() - $date;
    if($limit < 60){
        return '刚刚';
    }
    if($limit >= 60 && $limit < 3600){
        return floor($limit/60) . '分钟之前';
    }
    if($limit >= 3600 && $limit < 86400){
        return floor($limit/3600) . '小时之前';
    }
    if($limit >= 86400 and $limit<259200){
        return floor($limit/86400) . '天之前';
    }
    if($limit >= 259200 and $isShowDate){
        return get_date($date,'Y-m-d H:i');
    }else{
        return '';
    }
}
function str2time($str = "0") {
    $correct = 0;
    $str OR $str = 'now';
    $time = strtotime($str);
    (int) iPHP_TIME_CORRECT && $correct = (int) iPHP_TIME_CORRECT * 60;
    return $time + $correct;
}
// 格式化时间
function get_date($timestamp=0,$format='') {
	$correct = 0;
	$format OR $format            = iPHP_DATE_FORMAT;
	$timestamp OR $timestamp      = time();
	(int)iPHP_TIME_CORRECT && $correct = (int)iPHP_TIME_CORRECT*60;
    return date($format,$timestamp+$correct);
}
//中文长度
function cstrlen($str) {
    return csubstr($str,'strlen');
}
//中文截取
function csubstr($str,$len,$end=''){
	$len!='strlen' && $len=$len*2;
    //获取总的字节数
    $ll = strlen($str);
    //字节数
    $i = 0;
    //显示字节数
    $l = 0;
    //返回的字符串
    $s = $str;
    while ($i < $ll)  {
        //获取字符的asscii
        $byte = ord($str{$i});
        //如果是1字节的字符
        if ($byte < 0x80)  {
            $l++;
            $i++;
        }elseif ($byte < 0xe0){  //如果是2字节字符
            $l += 2;
            $i += 2;
        }elseif ($byte < 0xf0){   //如果是3字节字符
            $l += 2;
            $i += 3;
        }else{  //其他，基本用不到
            $l += 2;
            $i += 4;
        }
        if($len!='strlen'){
	        //如果显示字节达到所需长度
	        if ($l >= $len){
	            //截取字符串
	            $s = substr($str, 0, $i);
	            //如果所需字符串字节数，小于原字符串字节数
	            if($i < $ll){
	                //则加上省略符号
	                $s = $s . $end; break;
	            }
	            //跳出字符串截取
	            break;
	        }
        }
    }
    //返回所需字符串
    return $len!='strlen'?$s:$l;
}

//截取HTML
function htmlcut($content,$maxlen=300,$suffix=FALSE) {
	$content   = preg_split("/(<[^>]+?>)/si",$content, -1,PREG_SPLIT_NO_EMPTY| PREG_SPLIT_DELIM_CAPTURE);
	$wordrows  = 0;
	$outstr    = "";
	$wordend   = false;
	$beginTags = 0;
	$endTags   = 0;
    foreach($content as $value) {
        if (trim($value)=="") continue;

        if (strpos(";$value","<")>0) {
            if (!preg_match("/(<[^>]+?>)/si",$value) && cstrlen($value)<=$maxlen) {
                $wordend=true;
                $outstr.=$value;
            }
            if ($wordend==false) {
                $outstr.=$value;
                if (!preg_match("/<img([^>]+?)>/is",$value)&& !preg_match("/<param([^>]+?)>/is",$value)&& !preg_match("/<!([^>]+?)>/is",$value)&& !preg_match("/<br([^>]+?)>/is",$value)&& !preg_match("/<hr([^>]+?)>/is",$value)&&!preg_match("/<\/([^>]+?)>/is",$value)) {
                    $beginTags++;
                }else {
                    if (preg_match("/<\/([^>]+?)>/is",$value,$matches)) {
                        $endTags++;
                    }
                }
            }else {
                if (preg_match("/<\/([^>]+?)>/is",$value,$matches)) {
                    $endTags++;
                    $outstr.=$value;
                    if ($beginTags==$endTags && $wordend==true) break;
                }else {
                    if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<[br|BR]([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)&& !preg_match("/<\/([^>]+?)>/is",$value)) {
                        $beginTags++;
                        $outstr.=$value;
                    }
                }
            }
        }else {
            if (is_numeric($maxlen)) {
                $curLength=cstrlen($value);
                $maxLength=$curLength+$wordrows;
                if ($wordend==false) {
                    if ($maxLength>$maxlen) {
                        $outstr.=csubstr($value,$maxlen-$wordrows,FALSE,0);
                        $wordend=true;
                    }else {
                        $wordrows=$maxLength;
                        $outstr.=$value;
                    }
                }
            }else {
                if ($wordend==false) $outstr.=$value;
            }
        }
    }
    while(preg_match("/<([^\/][^>]*?)><\/([^>]+?)>/is",$outstr)) {
        $outstr=preg_replace_callback("/<([^\/][^>]*?)><\/([^>]+?)>/is","strip_empty_html",$outstr);
    }
    if (strpos(";".$outstr,"[html_")>0) {
        $outstr=str_replace("[html_&lt;]","<",$outstr);
        $outstr=str_replace("[html_&gt;]",">",$outstr);
    }
    if($suffix&&cstrlen($outstr)>=$maxlen)$outstr.="......";
    return $outstr;
}
//去掉多余的空标签
function strip_empty_html($matches) {
    $arr_tags1=explode(" ",$matches[1]);
    if ($arr_tags1[0]==$matches[2]) {
        return "";
    }else {
        $matches[0]=str_replace("<","[html_&lt;]",$matches[0]);
        $matches[0]=str_replace(">","[html_&gt;]",$matches[0]);
        return $matches[0];
    }
}
/** Escape for HTML
* @param string
* @return string
*/
function h($string) {
    return str_replace("\0", "&#0;", htmlspecialchars($string, ENT_QUOTES, 'utf-8'));
}
function sechtml($string) {
	$search  = array("/\s+/","/<(\/?)(script|iframe|style|object|html|body|title|link|meta|\?|\%)([^>]*?)>/isU","/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU");
	$replace = array(" ","&lt;\\1\\2\\3&gt;","\\1\\2",);
	$string  = preg_replace ($search, $replace, $string);
    return $string;
}
//HTML TO TEXT
function html2text($value) {
    $value = is_array($value) ?
        array_map('html2text', $value) :
        preg_replace('/<[\/\!]*?[^<>]*?>/is','',$value);

    return $value;
}
function html2js($value) {
    $value = is_array($value) ?
            array_map('html2js', $value) :
            str_replace(array("\\","\"","\n","\r"), array("\\\\","\\\"","\\n","\\r"), $value);

    return $value;
}

if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($value) {
        $value = is_array($value) ?
                array_map('htmlspecialchars_decode', $value) :
                str_replace (array('&amp;','&#039;','&quot;','&lt;','&gt;'), array('&','\'','\"','<','>'), $value );

        return $value;
    }
}
function stripslashes_deep($value) {
    $value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);

    return $value;
}

function random($length, $numeric = 0) {
    if($numeric) {
        $hash = sprintf('%0'.$length.'d', rand(0, pow(10, $length) - 1));
    } else {
		$hash  = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max   = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[rand(0, $max)];
        }
    }
    return $hash;
}
function get_user_dir($uid,$dir='avatar'){
    $nuid = abs(intval($uid));
    $nuid = sprintf("%08d", $nuid);
    $dir1 = substr($nuid, 0, 3);
    $dir2 = substr($nuid, 3, 2);
    $path = $dir.'/'.$dir1.'/'.$dir2;
    return $path;
}
function get_user_pic($uid,$size=0,$dir='avatar') {
    $path = get_user_dir($uid,$dir).'/'.$uid.".jpg";
	if ($size) {
		$path.= '_'.$size.'x'.$size.'.jpg';
	}
	return $path;
}

function auth_encode($string,$expiry = 0){
    return authcode($string,"ENCODE",null,$expiry);
}
function auth_decode($string){
    return authcode($string);
}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length   = 8;
	$key           = md5($key ? $key : iPHP_KEY);
	$keya          = md5(substr($key, 0, 16));
	$keyb          = md5(substr($key, 16, 16));
	$keyc          = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey      = $keya.md5($keya.$keyc);
	$key_length    = strlen($cryptkey);

	$string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result        = '';
	$box           = range(0, 255);

	$rndkey        = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
		$j       = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp     = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a       = ($a + 1) % 256;
		$j       = ($j + $box[$a]) % 256;
		$tmp     = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result  .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}
function str_exists($string, $find) {
    return !(strpos($string, $find) === FALSE);
}
function array_diff_values(array $N, array $O){
 	$diff['+'] = array_diff($N, $O);
 	$diff['-'] = array_diff($O, $N);
    return $diff;
}

function get_dir_name($path=null){
	if (!empty($path)) {
		if (strpos($path,'\\')!==false) {
			return substr($path,0,strrpos($path,'\\')).'/';
		} elseif (strpos($path,'/')!==false) {
			return substr($path,0,strrpos($path,'/')).'/';
		}
	}
	return './';
}
function get_unicode($string){
	if(empty($string)) return;

	$array = (array)$string;
	$json  = json_encode($array);
	return str_replace(array('["','"]'), '', $json);
}


function select_fields($array,$fields='',$map=false){
    $fields_array = explode(',', $fields);
    foreach ($fields_array as $key => $field) {
        $rs[$field] = $array[$field];
    }
    return $rs;
}
function get_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}
function unicode_convert_encoding($code){
    return mb_convert_encoding(pack("H*", $code[1]), "UTF-8", "UCS-2BE");
}
function unicode_encode($value){
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i','unicode_convert_encoding',$value);
}
function cnjson_encode($array){
    $json = json_encode($array);
    $json = unicode_encode($json);
    return $json;
}
