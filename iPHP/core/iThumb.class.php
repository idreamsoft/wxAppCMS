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
/**
 * 缩略图生成程序
 */
class iThumb {
    public static $RES_PATH       = null;
    public static $RES_CACHE_TIME = null;
    public static $RES_CACHE_DIR  = null;

    public static $src_data     = null;
    public static $thumb_path   = null;
    protected static $cache_dir = null;
    /**
     * 显示缩略图
     */
    public static function create(){
        $expires = 31536000;
        header("Cache-Control: maxage=".$expires);
        header('Last-Modified: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']).' GMT');
        header('Expires: '.gmdate('D, d M Y H:i:s',$_SERVER['REQUEST_TIME']+$expires).' GMT');
        header('Content-type: image/jpeg');
        echo self::$src_data;
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
        strpos($path,'..') === false OR trigger_error('What are you doing?',E_USER_ERROR);

        $srcPath   = self::$RES_PATH.$path;
        $thumb_path = $srcPath.'_'.$tw.'x'.$th.'.jpg';

        if (empty($path)||!self::exists($srcPath))
            return self::blank();

        iPHP_RES_CACHE && self::$src_data = self::cache($thumb_path,'get');

        if(empty(self::$src_data)){
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
            self::$src_data = $gmagick->current();
            iPHP_RES_CACHE && self::cache($thumb_path,self::$src_data);
        }
    }
    /**
     * 生成无图标志
     * @return [type] [description]
     */
    private static function blank(){
        //1x1.gif
        $src_data = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEHAAEALAAAAAABAAEAAAICTAEAOw==';
        //nopic.gif
        $src_data = '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
        FxLJ9EJ0oH0YpaVAMJAAA7';
        // header('HTTP/1.1 404 Not Found');
        header('Content-type: image/gif');
        header('X-Thumb-Cache: BLANK-'.$_SERVER['REQUEST_TIME']);
        echo base64_decode($src_data);
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
        self::$cache_dir = rtrim(self::$RES_PATH,'/').'/'.trim(self::$RES_CACHE_DIR,'/').'/';
        $cachePath = self::cacheFilePath($path,($data==='get'?null:'add'));
        $cacheTime = @filemtime($cachePath);
        if($cacheTime===false ||(
            self::$RES_CACHE_TIME>0 &&
            $_SERVER['REQUEST_TIME']-(int)$cacheTime>self::$RES_CACHE_TIME))
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
        //  $url = str_replace(self::$cache_dir, iPHP_RES_HOST, $path);
        //  var_dump($url);
        //  exit();
        // }
    }
    public static function cacheFilePath($path,$method=null){
        $md5      = md5($path);
        $dirPath  = self::$cache_dir.substr($md5,-1).'/'.substr($md5,-3,2).'/';
        $fileName = pathinfo($path, PATHINFO_FILENAME);

        if (!file_exists($dirPath) && $method=='add'){
            self::mkdir($dirPath);
        }
        return $dirPath.$fileName.'.jpg';
    }
    private static function check($fn) {
        strpos($fn,'..')!==false && trigger_error('What are you doing?',E_USER_ERROR);
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
