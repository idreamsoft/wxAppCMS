<?php
error_reporting(0);
if (!class_exists('Gmagick')) {
	class Gmagick {
		public $width  = 0;
		public $height = 0;
		public $type   = 0;
		protected $image  = null;
		protected $im     = null;
		public function __construct(){}
		public function readImage($src){
			if(empty($src)){
				return;
			}
			list($this->width,$this->height,$this->type) = @getimagesize($src);
			$this->image = $this->imagecreate($this->type,$src);
		}
		public function getImageWidth(){
			return $this->width;
		}
		public function getImageHeight(){
			return $this->height;
		}
		public function resizeImage($width,$height,$filter=null,$blur=0){
			$this->im = imagecreatetruecolor($width,$height);
			imagecopyresampled($this->im,$this->image, 0, 0, 0, 0,$width,$height,$this->width,$this->height);
		}
		public function cropImage($width,$height,$x,$y){
			if($this->im){
				$this->image  = $this->im;
				$this->width  = imagesx($this->image);
				$this->height = imagesy($this->image);
			}
			$this->im = imagecreatetruecolor($width,$height);
			imagecopyresampled($this->im,$this->image, 0, 0, $x, $y,$this->width,$this->height,$this->width,$this->height);
		}

	    public function current() {
	    	ob_start();
	    	switch($this->type){
	    		case 1:
		    		header('Content-Type: image/gif');
		    		imagegif($this->im,null);
		    		break;
	    		case 2:
		    		header('Content-Type: image/jpeg');
		    		imagejpeg($this->im,null,100);
		    		break;
	    		case 3:
		    		header('Content-Type: image/png');
		    		imagepng($this->im,null);
		    		break;
	    	}
	        imagedestroy($this->im);
	    	$image = ob_get_contents();
	    	ob_end_clean();
	    	return $image;
	    }
	    public function imagecreate($type,$src) {
	    	switch($type){
	    		case 1:$res = imagecreatefromgif($src);break;
	    		case 2:$res = imagecreatefromjpeg($src);break;
	    		case 3:$res = imagecreatefrompng($src);break;
	    	}
	        return $res;
	    }
	}
}

/**
 * 缩略图生成程序
 */
class Thumb {
	public static $srcData     = null;
	public static $thumbPath   = null;
	protected static $cacheDir = null;
	/**
	 * 显示缩略图
	 */
	public static function create(){
		$expires = 31536000;
		header("Cache-Control: maxage=".$expires);
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']).' GMT');
		header('Expires: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']+$expires).' GMT');
		header('Content-type: image/jpeg');
		echo self::$srcData;
		self::finish();
	}
	/**
	 * 生成缩略图
	 * @param  [type]  $path [原图路径]
	 * @param  integer $tw   [缩略图宽度]
	 * @param  integer $th   [缩略图高度]
	 * @return [image]        [缩略图资源]
	 */
	public static function make($path,$tw=1,$th=1){
		strpos($path,'..') === false OR exit('What are you doing?');

		$srcPath   = iPHP_RES_PAHT.$path;
		$thumbPath = $srcPath.'_'.$tw.'x'.$th.'.jpg';

		if (empty($path)||!self::exists($srcPath))
			return self::blank();

		iPHP_RES_CACHE && self::$srcData = self::cache($thumbPath,'get');

		if(empty(self::$srcData)){
			$gmagick = new Gmagick();
			$gmagick->readImage($srcPath);
			$scale = array(
					"tw" => $tw,
					"th" => $th,
					"w"  => $gmagick->getImageWidth(),
					"h"  => $gmagick->getImageHeight()
			);
			if($tw>0 && $th>0){
				$im = self::scale($scale);
				$gmagick->resizeImage($im['w'],$im['h'], null, 1);
			  	$x = $y = 0;
				$im['w']>$im['tw'] && $x = ceil(($im['w']-$im['tw'])/3);
				$im['h']>$im['th'] && $y = ceil(($im['h']-$im['th'])/3);
				$gmagick->cropImage($tw,$th,$x,$y);
			}else{
				empty($scale['th']) && $scale['th']=9999999;
				$im = self::bitScale($scale);
				$gmagick->resizeImage($im['w'],$im['h'], null, 1);
			}
			header('X-Thumb-Cache: MAKE-'.$_SERVER['REQUEST_TIME']);
			self::$srcData = $gmagick->current();
			iPHP_RES_CACHE && self::cache($thumbPath,self::$srcData);
		}
	}
	/**
	 * 生成无图标志
	 * @return [type] [description]
	 */
	private static function blank(){
		//1x1.gif
		$srcData = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEHAAEALAAAAAABAAEAAAICTAEAOw==';
		//nopic.gif
		$srcData = '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
		FxLJ9EJ0oH0YpaVAMJAAA7';
		// header('HTTP/1.1 404 Not Found');
		header('Content-type: image/gif');
		header('X-Thumb-Cache: BLANK-'.$_SERVER['REQUEST_TIME']);
		echo base64_decode($srcData);
		exit;
	}
	private static function exists($file) {
		return @stat($file)===false?false:true;
	}
	/**
	 * 等高/宽缩放
	 * @param  [type]  $a      [description]
	 * @param  boolean $reSize [description]
	 * @return [type]          [description]
	 */
	private static function scale($a,$reSize=true) {
		if($reSize){
			if($a['w'] > $a['h'] ||$a['w'] == $a['h']){
				$s = ($a['h'] > $a['th'])? $a['th']/$a['h'] : $a['h']/$a['th'];
				$a['w'] = ceil($s * $a['w']);
				$a['h'] = ($a['h'] > $a['th'])? $a['th'] : $a['h'];
			}else if($a['h'] > $a['w']){
				$s = ($a['w'] > $a['tw']) ? $a['tw']/$a['w'] : $a['w']/$a['tw'];
				$a['h'] = ceil($s * $a['h']);
				$a['w'] = ($a['w'] > $a['tw']) ? $a['tw'] : $a['w'];
			}
		}
	    return $a;
	}
	/**
	 * 等比缩放
	 * @param  [type]  $a      [description]
	 * @param  boolean $reSize [description]
	 * @return [type]          [description]
	 */
	private static function bitScale($a,$reSize=true) {
		if($reSize){
			if( $a['w']/$a['h'] > $a['tw']/$a['th']  && $a['w'] >$a['tw'] ){
				$a['h'] = ceil($a['h'] * ($a['tw']/$a['w']));
				$a['w'] = $a['tw'];
			}else if( $a['w']/$a['h'] <= $a['tw']/$a['th'] && $a['h'] >$a['th']){
				$a['w'] = ceil($a['w'] * ($a['th']/$a['h']));
				$a['h'] = $a['th'];
			}
		}
	    return $a;
	}
	private static function finish() {
		function_exists('fastcgi_finish_request') && fastcgi_finish_request();
	}

	public static function cache($path,$data=null){
		self::$cacheDir = rtrim(iPHP_RES_PAHT,'/').'/'.trim(iPHP_RES_CACHE_DIR,'/').'/';
		$cachePath = self::cacheFilePath($path,($data==='get'?null:'add'));
		$cacheTime = @filemtime($cachePath);
		if($cacheTime===false ||(
			iPHP_RES_CACHE_TIME>0 &&
			$_SERVER['REQUEST_TIME']-(int)$cacheTime>iPHP_RES_CACHE_TIME))
		{
			if($data==='get') return null;

			self::write($cachePath,$data);
			header('X-Thumb-Cache: SET-'.$_SERVER['REQUEST_TIME']);
			return true;
		}
		header('X-Thumb-Cache: HIT-'.$_SERVER['REQUEST_TIME']);
		return file_get_contents($cachePath);
	}
	public static function cacheUrl($path,$xxx){
		// var_dump($xxx);
		// if(iPHP_RES_HOST){
		// 	$url = str_replace(self::$cacheDir, iPHP_RES_HOST, $path);
		// 	var_dump($url);
		// 	exit();
		// }
	}
   	public static function cacheFilePath($path,$method=null){
		$md5      = md5($path);
		$dirPath  = self::$cacheDir.substr($md5,-1).'/'.substr($md5,-3,2).'/';
		$fileName = pathinfo($path, PATHINFO_FILENAME);

		if (!file_exists($dirPath) && $method=='add'){
			self::mkdir($dirPath);
		}
		return $dirPath.$fileName.'.jpg';
   	}
    private static function check($fn) {
        strpos($fn,'..')!==false && exit('What are you doing?');
    }
    private function delete($fn) {
        self::check($fn);
        @chmod ($fn, 0777);
        return @unlink($fn);
    }
    private static function write($fn,$data,$method="wb+",$iflock=1,$chmod=1) {
        self::check($fn);
        // @touch($fn);
        $handle = fopen($fn,$method);
        $iflock && flock($handle,LOCK_EX);
        fwrite($handle,$data);
        // $method=="rb+" && ftruncate($handle,strlen($data));
        fclose($handle);
        $chmod && @chmod($fn,0777);
    }
    private static function escapeDir($dir) {
        $dir = str_replace(array("'",'#','=','`','$','%','&',';'), '', $dir);
        return rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $dir), '/');
    }
    private static function mkdir($d) {
    	$d = self::escapeDir($d) ;
        $d = str_replace( '//', '/', $d );
        if ( file_exists($d) )
            return @is_dir($d);

        // Attempting to create the directory may clutter up our display.
        if ( @mkdir($d) ) {
            $stat = @stat(dirname($d));
            $dir_perms = $stat['mode'] & 0007777;  // Get the permission bits.
            @chmod($d, $dir_perms );
            return true;
        } elseif (is_dir(dirname($d))) {
            return false;
        }

        // If the above failed, attempt to create the parent node, then try again.
        if ( ( $d != '/' ) && ( self::mkdir(dirname($d))))
            return self::mkdir( $d );

        return false;
    }

}

// error_reporting(E_ALL ^ E_NOTICE); //调试
/**
 * 图片目录绝对路径 最后带 /
 */
define('iPHP_RES_PAHT','/data/www/ooxx.com/res/');
/**
 * 缓存缩略图配置
 */
define('iPHP_RES_CACHE',false); 			//是否开启缓存
define('iPHP_RES_CACHE_DIR','thumbCache');	//缓存目录名
define('iPHP_RES_CACHE_TIME',2592000);		//缓存时间

define('THUMB_PATH',$_GET['fp']);		//原图地址
define('THUMB_WIDTH',(int)$_GET['w']);	//缩略图宽度
define('THUMB_HEIGHT',(int)$_GET['h']); //缩略图高度

/**
 * 可用尺寸 开启缓存后建议填写
 * @var array
 */
$thumbSizeMap = array(
	// '90x90',
	// '110x140'
	// '240x240'
);
$thumbSize = THUMB_WIDTH.'x'.THUMB_HEIGHT;

/**
 * 生成缩略图
 */
if(in_array($thumbSize, $thumbSizeMap)||empty($thumbSizeMap)){
	Thumb::make(THUMB_PATH,THUMB_WIDTH,THUMB_HEIGHT);
	Thumb::create();
}

