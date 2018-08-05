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
class iFS {
	public static $force_ext = false;
	public static $valid_ext = true;
	public static $config = null;
	public static $data = null;

	public static $CALLABLE = null;
	public static $ERROR = null;
	public static $ERROR_TYPE = false;
	public static $EXTS = array(
		"png", "jpg", "jpeg", "gif", "bmp", "webp", "psd", "tif",
		"flv", "swf", "mkv", "avi", "rm", "rmvb", "mpeg", "mpg", "mp4",
		"ogg", "ogv", "mov", "wmv", "webm", "mp3","aac","m4a", "wav", "mid", "amr",
		"rar", "zip", "tar", "gz", "7z", "bz2", "cab", "iso",
		"doc", "docx", "xls", "xlsx", "ppt", "pptx", "pdf", "txt", "md", "xml",
		"apk", "ipa",
		"html", "htm", "shtml",
	);

	public static function init($config) {
		self::$config = $config;
	}

	public static function config($config) {
		self::$config = array_merge(self::$config, $config);
	}
	public static function url($urls=null) {
		$urls===null && $urls = self::$config['url'];
		return trim($urls);
	}
	public static function ex($f) {
		return @stat($f) === false ? false : true;
	}
	public static function is_url($url) {
		return self::checkHttp($url);
	}
	public static function is_file($file) {
		return @is_file($file);
	}

	public static function is_dir($path) {
		return @is_dir($path);
	}

	public static function is_readable($file) {
		return @is_readable($file);
	}

	public static function is_writable($file) {
		return @is_writable($file);
	}

	public static function atime($file) {
		return @fileatime($file);
	}

	public static function mtime($file) {
		return @filemtime($file);
	}

	public static function check($fn) {
		strpos($fn, '..') !== false && trigger_error('What are you doing?',E_USER_ERROR);
	}
	public static function checkHttp($url) {
		if (stripos($url, 'http://') === false && stripos($url, 'https://') === false) {
			return false;
		} else {
			return true;
		}
	}
	public static function del($fn, $check = 1) {
		return self::rm($fn, $check);
	}
    public static function rm($fn, $check = 1) {
		$check && self::check($fn);
		@chmod($fn, 0777);
		$del = @unlink($fn);
		self::hook('delete',array($fn));
		return $del;
	}

	public static function read($fn, $check = 1, $method = "rb") {
		$check && self::check($fn);
		if (function_exists('file_get_contents') && $method != "rb") {
			$filedata = file_get_contents($fn);
		} else {
			if ($handle = fopen($fn, $method)) {
				flock($handle, LOCK_SH);
				$filedata = @fread($handle, (int) filesize($fn));
				fclose($handle);
			}
		}
		return $filedata;
	}

	public static function write($fn, $data, $check = 1, $method = "wb+", $iflock = 1, $chmod = 0) {
		$check && self::check($fn);
		@touch($fn);
		$handle = fopen($fn, $method);
		$iflock && flock($handle, LOCK_EX);
		fwrite($handle, $data);
		$method == "rb+" && ftruncate($handle, strlen($data));
		fclose($handle);
		$chmod && @chmod($fn, 0644);
		self::hook('write',array($fn,$data));
	}
	public static function backup($path, $target) {
		if (self::ex($path)) {
			self::mkdir(dirname($target));
			return @rename($path, $target);
		}
		return false;
	}
	public static function escape_dir($dir) {
		$dir = str_replace(array("'", '#', '=', '`', '$', '%', '&', ';',"\0"), '', $dir);
		return rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $dir), '/');
	}
	//创建目录
	public static function mkdir($d) {
		$d = self::escape_dir($d);
		$d = str_replace('//', '/', $d);
		if (file_exists($d)) {
			return @is_dir($d);
		}

		// Attempting to create the directory may clutter up our display.
		if (@mkdir($d)) {
			$stat = @stat(dirname($d));
			$dir_perms = $stat['mode'] & 0007777;  // Get the permission bits.
			@chmod($d, $dir_perms);
			return true;
		} elseif (is_dir(dirname($d))) {
			return false;
		}

		// If the above failed, attempt to create the parent node, then try again.
		if (($d != '/') && (self::mkdir(dirname($d)))) {
			return self::mkdir($d);
		}

		return false;
	}
	public static function checkdir($dirpath) {
		if (empty($dirpath)) {
			return false;
		}
		$dirpath = rtrim($dirpath, '/') . '/';
		if ($fp = @fopen($dirpath . 'iFS.test.txt', "wb")) {
			@fclose($fp);
			@unlink($dirpath . 'iFS.test.txt');
			return true;
		} else {
			return false;
		}
	}
	//删除目录
	public static function rmdir($dir, $df = true, $ex = NULL) {
		$exclude = array('.', '..');
		$ex && $exclude = array_merge($exclude, (array) $ex);
		if ($dh = @opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if (!in_array($file, $exclude)) {
					$path = $dir . '/' . $file;
					is_dir($path) ? self::rmdir($path, $df) : ($df ? @unlink($path) : null);
				}
			}
			closedir($dh);
		}
		return @rmdir($dir);
	}
	//获取文件夹下所有文件/文件夹列表
    public static function fileList($dir,$pattern='*'){
		$lists = array();
		$dir   = trim($dir, '/');
		foreach(glob($dir.'/'.$pattern) as $value){
			$lists[] = $value;
			if(is_dir($value)){
			  $_lists = self::fileList($value,$pattern);
			  $lists  = array_merge($lists,$_lists);
			}
		}
		return (array)$lists;
    }
	//获取文件夹列表
	public static function folder($dir = '', $type = NULL) {
		$dir = trim($dir, '/');
		$_GET['dir'] && $gDir = trim($_GET['dir'], '/');

		// print_r('$dir='.$dir.'<br />');
		// print_r('$gDir='.$gDir.'<br />');

		//$gDir && $dir = $gDir;

		//strstr($dir,'.')!==false  && self::alert('What are you doing?','',1000000);
		//strstr($dir,'..')!==false && self::alert('What are you doing?','',1000000);

		$sDir_PATH = self::path_join(iPATH, $dir);
		$iDir_PATH = self::path_join($sDir_PATH, $gDir);

		// print_r('$sDir_PATH='.$sDir_PATH."\n");
		// print_r('$iDir_PATH='.$iDir_PATH."\n");

		strpos($iDir_PATH, $sDir_PATH) === false && self::_error(array('code' => 0, 'state' => 'DIR_Error'));

		if (!is_dir($iDir_PATH)) {
			return false;
		}

		$url = iURL::make('dir');
		if ($handle = opendir($iDir_PATH)) {
			while (false !== ($rs = readdir($handle))) {
				// print_r('$rs='.$rs."\n");
				$filepath = self::path_join($iDir_PATH, $rs);
				$filepath = rtrim($filepath, '/');
//              print_r('$filepath='.$filepath."\n");
				$sFileType = @filetype($filepath);
//              print_r('$sFileType='.$sFileType."\n");
				// var_dump($sDir_PATH,$filepath);
				$path = str_replace($sDir_PATH, '', $filepath);
				$path = ltrim($path, '/');
				if ($sFileType == "dir" && !in_array($rs, array('.', '..', 'admincp'))) {
					$dirArray[] = array(
						'path' => $path,
						'name' => $rs,
						'url' => $url . urlencode($path),
					);
				}
				if ($sFileType == "file" && !in_array($rs, array('..', '.iPHP'))) {
					$filext = iFS::get_ext($rs);
					$fileinfo = array(
						'path' => $path,
						'dir' => dirname($path),
						'url' => iFS::fp($path, '+http'),
						'name' => $rs,
						'modified' => get_date(filemtime($filepath), "Y-m-d H:i:s"),
						'md5' => md5_file($filepath),
						'ext' => $filext,
						'size' => iFS::sizeUnit(filesize($filepath)),
					);
					if ($type) {
						in_array(strtolower($filext), $type) && $fileArray[] = $fileinfo;
					} else {
						$fileArray[] = $fileinfo;
					}
				}
			}
		}
		$a['DirArray'] = (array) $dirArray;
		$a['FileArray'] = (array) $fileArray;
		$a['pwd'] = str_replace($sDir_PATH, '', $iDir_PATH);
		$a['pwd'] = trim($a['pwd'], '/');
		$pos = strripos($a['pwd'], '/');
		$a['parent'] = ltrim(substr($a['pwd'], 0, $pos), '/');
		$a['URI'] = $url;
		// var_dump($a);
		//      exit;
		return $a;
	}

	public static function info($path) {
		return (OBJECT) pathinfo($path);
	}

	public static function path($p = '') {
		$p = str_replace("\0", '', $p);
		$end = substr($p, -1);
		$a = explode('/', $p);
		$o = array();
		$c = count($a);
		for ($i = 0; $i < $c; $i++) {
			if ($a[$i] == '.' || $a[$i] == '') {
				continue;
			}

			if ($a[$i] == '..' && $i > 0 && end($o) != '..') {
				array_pop($o);
			} else {
				$o[] = $a[$i];
			}
		}
		$o[0] == 'http:' && $o[0] = 'http:/';

		return ($p[0] == '/' ? '/' : '') . implode('/', $o) . ($end == '/' ? '/' : '');
	}

	public static function path_is_absolute($path) {
		// this is definitive if true but fails if $path does not exist or contains a symbolic link
		if (@realpath($path) == $path) {
			return true;
		}

		if (strlen($path) == 0 || $path[0] == '.') {
			return false;
		}

		// windows allows absolute paths like this
		if (preg_match('#^[a-zA-Z]:\\\\#', $path)) {
			return true;
		}

		// a path starting with / or \ is absolute; anything else is relative
		return (bool) preg_match('#^[/\\\\]#', $path);
	}

	public static function path_join($base, $path, $rtrim = false) {
		//if (!self::path_is_absolute($path))

		$path = rtrim($base, '/') . '/' . ltrim($path, '/');
		$path = self::path($path);
		$rtrim && $path = rtrim($path, '/') . '/';
		return $path;
	}

	//文件名
	public static function name($fn) {
		$_fn = substr(strrchr($fn, "/"), 1);
		return array('name' => substr($_fn, 0, strrpos($_fn, ".")),
			'path' => substr($fn, 0, strrpos($fn, ".")),
		);
	}

	// 获得文件扩展名
	public static function get_ext($fn) {
		return pathinfo($fn, PATHINFO_EXTENSION);
		//return substr(strrchr($fn, "."), 1);
	}

	// 获取文件大小
	public static function sizeUnit($filesize) {
		$SU = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		$n = 0;
		while ($filesize >= 1024) {
			$filesize /= 1024;
			$n++;
		}
		return round($filesize, 2) . ' ' . $SU[$n];
	}

//-----------upload-------------
	public static function get_dir() {
		$dir = self::path_join(iPATH, self::$config['dir']);
		return rtrim($dir, '/') . '/';
	}

	public static function mk_udir($_dir = '') {
		$FileDir = $_dir ? $_dir : get_date(0, self::$config['dir_format']);
		$FileDir = rtrim($FileDir, '/') . '/';
		$FileDir = ltrim($FileDir, './');
		$RootPath = self::get_dir() . $FileDir;
		$RootPath = rtrim($RootPath, '/') . '/';
		self::mkdir($RootPath);
		return array($RootPath, $FileDir);
	}

	public static function save_ufile($tn, $fp) {
		if (function_exists('move_uploaded_file') && @move_uploaded_file($tn, $fp)) {
			@chmod($fp, 0644);
		} elseif (@copy($tn, $fp)) {
			@chmod($fp, 0644);
		} elseif (is_readable($tn) && is_writable($fp)) {
			if ($fp = @fopen($tn, 'rb')) {
				@flock($fp, 2);
				$filedata = @fread($fp, @filesize($tn));
				@fclose($fp);
			}
			if ($fp = @fopen($fp, 'wb')) {
				@flock($fp, 2);
				@fwrite($fp, $filedata);
				@fclose($fp);
				@chmod($fp, 0644);
			}
		} else {
			return self::_error(array('code' => 0, 'state' => 'Error'));
		}
		return true;
	}
	public static function _data($value) {
		$keys = array(
			'code','fid','md5','size',
			'oname','name','fname','dir','ext',
			'RootPath','path','dirRootPath'
		);
		return array_combine($keys ,$value);
	}
	public static function _array($code, $frs, $RP) {
		$value = array(
			$code,$frs->id,$frs->filename,$frs->size,
			$frs->ofilename,$frs->filename, $frs->filename . "." . $frs->ext,
			$frs->path,$frs->ext,
			$RP . '/' . $frs->path . $frs->filename . "." . $frs->ext,
			$frs->filepath,
			$RP . '/' . $frs->path,
		);
		return self::_data($value);
	}

	public static function IO($FileName = '', $udir = '', $FileExt = 'jpg',$type='3',$filedata=null) {
		$filedata===null && $filedata = file_get_contents('php://input');
		if (empty($filedata)) {
			return false;
		}

		list($RootPath, $FileDir) = self::mk_udir($udir); // 文件保存目录方式
		$file_md5 = md5($filedata);
		$FileName OR $FileName = $file_md5;
		$FileSize = strlen($filedata);
		$FileExt = self::valid_ext($FileName . "." . $FileExt); //判断文件类型
		if ($FileExt === false) {
			return false;
		}

		$FilePath = $FileDir . $FileName . "." . $FileExt;
		$FileRootPath = $RootPath . $FileName . "." . $FileExt;
		self::write($FileRootPath, $filedata);
		$fid = self::insert_filedata(array($FileName,'',$FileDir,'',$FileExt,$FileSize), $type);
		self::hook('upload',array($FileRootPath,$FileExt));
		$value = array(
			1,$fid,$file_md5,$FileSize,
			'',$FileName,$FileName.".".$FileExt,
			$FileDir,$FileExt,
			$FileRootPath,$FilePath,$RootPath
		);
		return self::_data($value);
	}
	public static function base64ToFile($base64Data, $udir = '', $FileExt = 'png') {
		if (empty($base64Data)) {return false;}

		$filedata = base64_decode($base64Data);
		return self::IO(null, $udir,$FileExt,'2',$filedata);
	}

	public static function upload($field, $udir = '', $FileName = '', $ext = '') {

		if ($_FILES[$field]['name']) {
			$tmp_file = $_FILES[$field]['tmp_name'];
			if (!is_uploaded_file($tmp_file)) {
				return self::_error(array('code' => 0, 'state' => 'UNKNOWN'));
			}
			if ($_FILES[$field]['error'] > 0) {
				switch ((int) $_FILES[$field]['error']) {
				case UPLOAD_ERR_NO_FILE:
					@unlink($tmp_file);
					return self::_error(array('code' => 0, 'state' => 'NOFILE'));
					break;
				case UPLOAD_ERR_FORM_SIZE:
					@unlink($tmp_file);
					return self::_error(array('code' => 0, 'state' => 'UPLOAD_MAX'));
					break;
				}
				return self::_error(array('code' => 0, 'state' => 'UNKNOWN'));
			}
			$oFileName = $_FILES[$field]['name'];
			$FileExt = self::valid_ext($oFileName); //判断文件类型
			if ($FileExt === false) {
				return false;
			}

			list($RootPath, $FileDir) = self::mk_udir($udir); // 文件保存目录方式

			if (self::$data) {
				$fid       = self::$data->id;
				$file_md5  = self::$data->filename;
				$oFileName = self::$data->ofilename;
				$FileDir   = self::$data->path;
				$FileExt   = self::$data->ext;
				$FileSize  = self::$data->size;
			} else {
				$file_md5 = md5_file($tmp_file);
				$frs = self::get_filedata('filename', $file_md5);
				if ($frs) {
					return self::_array(1, $frs, $RootPath);
				}
				$ext && $FileExt = $ext;
				$FileSize = @filesize($tmp_file);
			}
			$FileName OR $FileName = $file_md5;
			$FilePath = $FileDir . $FileName . "." . $FileExt;
			$FileRootPath = self::fp($FilePath, "+iPATH");
			$ret = self::save_ufile($tmp_file, $FileRootPath);
			@unlink($tmp_file);

			if($ret!==true){
				return self::_error(array('code' => 0, 'state' => 'Error'));
			}

			if ($fid) {
				self::update_filedata(array(
					'ofilename' => $oFileName,
					'ext'       => $FileExt,
					'size'      => $FileSize,
				), $fid);
			} else {
				$fid = self::insert_filedata(array($file_md5,$oFileName,$FileDir,'',$FileExt,$FileSize), 0);
			}
			self::hook('upload',array($FileRootPath,$FileExt));
			$value =array(
				1,$fid,$file_md5,$FileSize,
				$oFileName,$FileName,$FileName.".".$FileExt,
				$FileDir,$FileExt,
				$FileRootPath,$FilePath,$RootPath
			);
			return self::_data($value);
		} else {
			return false;
		}
	}
	public static function check_image_bin($path,$bin=false){
		if(empty($path)){
			return false;
		}
		if($bin){
			$head = substr($path, 0,16);
		}else{
			if(!is_file($path)) return false;

		    $fh = fopen($path, "rb");
		    //必须使用rb来读取文件，这样能保证跨平台二进制数据的读取安全
		    //仅读取前面的8个字节
		    $head = fread($fh, 16);
		    fclose($fh);
		}

	    $arr = unpack("C*", $head);
	    $string = null;
	    foreach ($arr as $k => $C) {
	        if($C>=48 && $C<=127){
	            $string.=chr($C);
	        }
	    }
		if(empty($string)){
			return false;
		}

	    $string = strtoupper($string);
	    $format = array(
	        'JFIF','TIFF','RIFF',
	        'PNG','GIF89A','GIF87A','JPEG',
	        'BMP','WEBP',
	        'JPEG 2000','EXIF','BPG','SVG'
	    );
	    foreach ($format as $key => $f) {
	       if(strpos($string, $f)!==false){
	            return $f;
	       }
	    }
	    return false;
	}
	public static function allow_files($exts) {
		$exts_array = explode(',', $exts);
		foreach ($exts_array as $key => $ext) {
			if (!in_array($ext, self::$EXTS)) {
				return false;
			}
		}
		return true;
	}
	public static function check_ext($ext, $path = true) {
		$path && $ext = self::get_ext($ext);
		$ext = strtolower($ext);
		$allow = self::allow_files($ext);
		return $allow ? true : false;
	}
	public static function valid_ext($fn) {
		$_ext = strtolower(self::get_ext($fn));
		$ext = self::check_ext($_ext, 0) ? $_ext : 'file';

		if (self::$force_ext !== false) {
			(empty($_ext) || strlen($_ext) > 4 || $ext == 'file') && $ext = self::$force_ext;
			return $ext;
		}
		if (!self::$valid_ext) {
			return $ext;
		}

		$ext_array = explode(',', strtolower(self::$config['allow_ext']));
		if (in_array($_ext, $ext_array)) {
			return $ext;
		} else {
			self::$ERROR = self::_error(array('code' => 0, 'state' => 'TYPE','file'=>$fn));
			return false;
		}
	}

	public static function fp($f, $m = '+http', $_config = null) {
		$config = $_config ? $_config : self::$config;
		$url = self::$config['url'];
		switch ($m) {
			case '+http':
				$fp = rtrim($url, '/') . '/' . ltrim($f, '/');
				break;
			case '-http':
				$fp = str_replace($url, '', $f);
				break;
			case 'http2iPATH':
				$f = str_replace($url, '', $f);
				$fp = self::path_join(iPATH, $config['dir'], '/') . ltrim($f, '/');
				break;
			case 'iPATH2http':
				$f = str_replace(self::path_join(iPATH, $config['dir']), '', $f);
				$fp = $url . $f;
				break;
			case '+iPATH':
				$fp = self::path_join(iPATH, $config['dir'], '/') . ltrim($f, '/');
				break;
			case '-iPATH':
				$fp = str_replace(self::path_join(iPATH, $config['dir']), '', $f);
				break;
		}
		return $fp;
	}
	public static function filename($path) {
		$path = trim($path);
		if(self::checkHttp($path)){
			$url = self::url();
			$uri = parse_url($url);
			if (stripos($path,$uri['host']) !== false){
				$path = self::fp($path,'-http');
			}else{
				return false;
			}
		}
		$name = basename($path);
		$name = substr($name,0, 32);
		return $name;
	}
//--------upload---end-------------------------------
	public static function http($http, $ret = '', $times = 0) {
		$frs = self::get_filedata('ofilename', $http);

		if ($frs) {
			if ($ret == 'array') {
				return self::_array(1, $frs, $RootPath);
			}
			return $frs->filepath;
		}
		$FileExt = self::valid_ext($http); //判断过滤文件类型
		if ($FileExt === false) {
			return false;
		}

		$fdata = iHttp::remote($http);
		if ($fdata) {
			list($RootPath, $FileDir) = self::mk_udir($udir); // 文件保存目录方式

			$file_md5 = md5($fdata);
			$frs = self::get_filedata('filename', $file_md5);
			if ($frs) {
				$FilePath = $frs->filepath;
				$FileRootPath = iFS::fp($FilePath, "+iPATH");
				if (!is_file($FileRootPath)) {
					self::mkdir(dirname($FileRootPath));
					self::write($FileRootPath, $fdata);
					self::hook('upload',array($FileRootPath,$FileExt));
					// self::watermark($FileExt, $FileRootPath);
					// self::cloud_write($FileRootPath);
				}
				if ($ret == 'array') {
					return self::_array(1, $frs, $RootPath);
				}
			} else {
				$FileName = $file_md5 . "." . $FileExt;
				$FilePath = $FileDir . $FileName;
				$FileRootPath = $RootPath . $FileName;
				self::write($FileRootPath, $fdata);
				$FileSize = @filesize($FileRootPath);
				empty($FileSize) && $FileSize = 0;
				$fid = self::insert_filedata(array($file_md5,$http,$FileDir,$intro,$FileExt,$FileSize),1);
				self::hook('upload',array($FileRootPath,$FileExt));
				if ($ret == 'array') {
					$value =array(
						1,$fid,$file_md5,$FileSize,
						$http,$FileName,$FileName.".".$FileExt,
						$FileDir,$FileExt,
						$FileRootPath,$FilePath,$RootPath
					);
					return self::_data($value);
				}
			}
			return $FilePath;
		} else {
			// if ($times < 3) {
			//     $times++;
			//     return self::http($http,$ret,$times);
			// } else {
			return false;
			// }
		}
	}
	public static function hook($h,$args) {
		$call = self::$CALLABLE[$h];
		is_array($call) && $_call = reset($call);
		if(is_array($_call)){
			foreach ($call as $key => $cb) {
				is_callable($cb) && call_user_func_array($cb, $args);
			}
		}else{
			if ($call && is_callable($call)) {
				return call_user_func_array($call, $args);
			}
		}
	}
//------callable-----
	public static function insert_filedata($value, $type = 0,$status=1) {
		$keys = array('filename','ofilename','path','intro','ext','size');
		$data = array_combine($keys ,$value);
		return self::hook('insert',array($data, $type,$status));
	}
	public static function update_filedata($data, $fid = 0) {
		if (empty($fid)) {
			return;
		}
		return self::hook('update',array($data, $fid));
	}
	public static function get_filedata($f, $v,$s='*') {
		return self::hook('get',array($f,$v,$s));
	}
//-------------
	public static function _error($e, $break = false) {
		$stateMap = array(
			"UPLOAD_MAX" => "文件大小超出 upload_max_filesize 限制",
			"MAX_FILE_SIZE" => "文件大小超出 MAX_FILE_SIZE 限制",
			"文件未被完整上传",
			"没有文件被上传",
			"NOFILE" => "上传文件为空",
			"POST" => "文件大小超出 post_max_size 限制",
			"SIZE" => "文件大小超出网站限制",
			"TYPE" => "不允许的文件类型",
			"DIR" => "目录创建失败",
			"IO" => "输入输出错误",
			"UNKNOWN" => "未知错误",
			"Error" => "Upload Unknown Error",
			"MOVE" => "文件保存时出错",
			"DIR_Error" => "您访问的目录有问题",
		);
		$msg = $e['file'].$stateMap[$e['state']];
		if (self::$ERROR_TYPE) {
			$e['state'] = $msg;
			if (self::$ERROR_TYPE === 'json') {
				return json_encode($e);
			}
			return $e;
		} else {
			exit('<script type="text/javascript">window.top.alert("' . $msg . '");</script>');
		}
	}

}
