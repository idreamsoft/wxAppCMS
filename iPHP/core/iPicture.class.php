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
class iPicture {
    protected static $config = null;
    protected static $DIR    = null;

    public static function init($config) {
        self::$config = $config;
        self::$DIR    = iPHP_APP_CONF;
    }
    /** 图片局部打马赛克
    * @param  String  $source 原图
    * @param  int     $x1     起点横坐标
    * @param  int     $y1     起点纵坐标
    * @param  int     $x2     终点横坐标
    * @param  int     $y2     终点纵坐标
    * @param  int     $deep   深度，数字越大越模糊
    * @return boolean
    */
    // public static function mosaics($source,$x1, $y1, $x2, $y2, $deep='9'){
    public static function mosaics($source){
        if(!self::$config['enable']) return false;

        // 获取原图信息
        $info = self::getinfo($source);

        if(!$info) return false;

        list($source_w, $source_h,$source_type,$source_im) = $info;

        $w    = self::$config['mosaics']['width']?:150;
        $h    = self::$config['mosaics']['height']?:90;
        $deep = self::$config['mosaics']['deep']?:9;

        if( ($source_w<$w) || ($source_h<$h) ){
            // echo "需要加水印的图片的长度或宽度比水印".$label."还小，无法生成水印！";
            return false;
        }

        $source_img = self::imagecreate($source_type,$source);
        list($x1,$y1) = self::getpos($source_w,$source_h,$w,$h);
        $x2 = $x1+$w;
        $y2 = $y1+$h;
        // 打马赛克
        for($x=$x1; $x<$x2; $x=$x+$deep){
            for($y=$y1; $y<$y2; $y=$y+$deep){
                $color = imagecolorat($source_img, $x+round($deep/2), $y+round($deep/2));
                imagefilledrectangle($source_img, $x, $y, $x+$deep, $y+$deep, $color);
            }
        }
        @unlink($source);
        self::image($source_img,$source_type,$source);
        //释放内存
        // isset($source_img) && imagedestroy($source_img);
        return is_file($source)? true : false;
    }
    public static function getinfo($source) {
        if($source && file_exists($source)) {
            list($source_w, $source_h,$source_type) = @getimagesize($source);
            $source_im = self::imagecreate($source_type,$source);//取得图片资源
            $formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG等格式。";
            if(empty($source_im)){
                // echo $formatMsg;
                return false;
            }
        }else {
            // echo "需要加水印的图片不存在！";
            return false;
        }
        return array($source_w, $source_h,$source_type,$source_im);
    }
    public static function watermark($source) {
        if(!self::$config['enable']) return false;

        // 获取原图信息
        $info = self::getinfo($source);

        if(!$info) return false;

        list($source_w, $source_h,$source_type,$source_im) = $info;

        if ( $source_w < self::$config['width'] || $source_h<self::$config['height'] ) {
            return false;
        }
        //读取水印文件
        $waterImgPath = self::$DIR.'/'.self::$config['img'];
        $water_info   = self::getinfo($waterImgPath);
        if($water_info){
            list($water_w, $water_h,$water_type,$water_im) = $water_info;
            $isWaterImage = true;
        }else{
            $isWaterImage = false;
            $fontfile     = self::$DIR.'/'.self::$config['font'];
        }

        //水印位置
        if($isWaterImage){ //图片水印
            $w = $water_w;
            $h = $water_h;
        }else { //文字水印
            if($fontfile && is_file($fontfile)) {
                $isWaterFont = true;
                $temp = imagettfbbox(self::$config['fontsize'],0,$fontfile,self::$config['text']);//取得使用 TrueType 字体的文本的范围
                $w = $temp[2] - $temp[6];
                $h = $temp[3] - $temp[7];
                unset($temp);
            }else {
                $isWaterFont = false;
                $w = self::$config['fontsize']*cstrlen(self::$config['text']);
                $h = self::$config['fontsize']+5;
            }
        }
        if( ($source_w<$w) || ($source_h<$h) ){
            // echo "需要加水印的图片的长度或宽度比水印".$label."还小，无法生成水印！";
            return false;
        }

        list($posX,$posY) = self::getpos($source_w,$source_h,$w,$h);

        //设定图像的混色模式
        imagealphablending($source_im, true);

        if($isWaterImage) {
            //图片水印
        	if(strtolower(substr(strrchr($waterImgPath, "."),1))=='png'){
	            imagecopy ($source_im,$water_im,$posX, $posY, 0,0,$water_w,$water_h);
        	}else{
				imagecopymerge($source_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h,self::$config['transparent']);//拷贝水印到目标文件
        	}
        }else{
            //文字水印
            if(empty(self::$config['color'])||strlen(self::$config['color'])!=7) {
                self::$config['color']="#FFFFFF";
            }
            $R = hexdec(substr(self::$config['color'],1,2));
            $G = hexdec(substr(self::$config['color'],3,2));
            $B = hexdec(substr(self::$config['color'],5));
            $textcolor = imagecolorallocate($source_im, $R, $G, $B);
            if($isWaterFont) {
                imagettftext($source_im,self::$config['fontsize'], 0, $posX, $posY, $textcolor,$fontfile, self::$config['text']);
            }else {
                imagestring ($source_im, self::$config['fontsize'], $posX, $posY, self::$config['text'],$textcolor);
            }
        }

        //生成水印后的图片
        @unlink($source);
        self::image($source_im,$source_type,$source);
        //释放内存
        unset($info);unset($water_info);
        unset($source_im);unset($water_im);
        return is_file($source)? true : false;
    }
    public static function getpos($source_w,$source_h,$w,$h) {
        switch(self::$config['pos']) {
            case '-1'://自定义
                $posX = $source_w - $w-self::$config['x'];
                $posY = $source_h - $h-self::$config['y'];
                break;
            case 1://1为顶端居左
                $posX = 0;
                $posY = 0;
                break;
            case 2://2为顶端居中
                $posX = ($source_w - $w) / 2;
                $posY = 0;
                break;
            case 3://3为顶端居右
                $posX = $source_w - $w;
                $posY = 0;
                break;
            case 4://4为中部居左
                $posX = 0;
                $posY = ($source_h - $h) / 2;
                break;
            case 5://5为中部居中
                $posX = ($source_w - $w) / 2;
                $posY = ($source_h - $h) / 2;
                break;
            case 6://6为中部居右
                $posX = $source_w - $w;
                $posY = ($source_h - $h) / 2;
                break;
            case 7://7为底端居左
                $posX = 0;
                $posY = $source_h - $h;
                break;
            case 8://8为底端居中
                $posX = ($source_w - $w) / 2;
                $posY = $source_h - $h;
                break;
            case 9://9为底端居右
                $posX = $source_w - $w;
                $posY = $source_h - $h;
                break;
            default://随机
                $posX = rand(0,($source_w - $w));
                $posY = rand($h,($source_h - $h));
                break;
        }
        $posX = $posX-self::$config['x'];
        $posY = $posY-self::$config['y'];
        return array($posX,$posY);
    }
    public static function thumbnail($src,$tw="0",$th="0",$scale=true) {
    	if(!self::$config['thumb']['enable']) return;

        $rs	= array();
        $tw	= empty($tw)?self::$config['thumb']['width']:(int)$tw;
        $th	= empty($th)?self::$config['thumb']['height']:(int)$th;

        if ($tw && $th){
            list($width, $height,$type) = getimagesize($src);
            if ( $width < 1 && $height < 1 ) {
                $rs['width']	= $tw;
                $rs['height']   = $th;
                $rs['src'] 		= $src;
                return $rs;
            }

            if ( $width > $tw || $height >$th ) {
	            $rs['src'] = $src.'_'.$tw.'x'.$th.'.jpg';
				if (in_array('Gmagick', get_declared_classes() )){
					$image = new Gmagick();
					$image->readImage($src);
					$im = self::scale(array("tw"  => $tw,"th" => $th,"w"  => $image->getImageWidth(),"h" => $image->getImageHeight()));
					$image->resizeImage($im['w'],$im['h'], null, 1);
					$image->cropImage($tw,$th, 0, 0);
					//$image->thumbnailImage($gm_w,$gm_h);
					$image->writeImage($rs['src']);
					$image->destroy();
				}else{
	                $im = self::scale(array("tw"  => $tw,"th" => $th,"w"  => $width,"h" => $height ),$scale);
	                $ret= self::imagecreate($type,$src);
	                $rs['width']   = $im['w'];
	                $rs['height']  = $im['h'];
	                if ($ret) {
	                    $thumb = imagecreatetruecolor($im['w'], $im['h']);
	                    imagecopyresampled($thumb,$ret, 0, 0, 0, 0, $im['w'], $im['h'], $width, $height);
	                    self::image($thumb,$type,$rs['src']);
	                } else {
	                    $rs['src'] = $src;
	                }
                }
            } else {
                $rs['src'] 		= $src;
                $rs['width']	= $width;
                $rs['height']   = $height;
            }
            return $rs;
        }

    }
    public static function image($res,$type,$fn) {
    	switch($type){
    		case 1:imagegif($res,$fn);break;
    		case 2:imagejpeg($res,$fn);break;
    		case 3:imagepng($res,$fn);break;
    	}
        imagedestroy($res);
    }
    public static function imagecreate($type,$src) {
    	switch($type){
    		case 1:$res = imagecreatefromgif($src);break;
    		case 2:
            ini_set('gd.jpeg_ignore_warning',1);
            $res = @imagecreatefromjpeg($src);
            break;
    		case 3:$res = @imagecreatefrompng($src);break;
    	}
        return $res;
    }
	public static function scale($a) {
		if( $a['w']/$a['h'] > $a['tw']/$a['th']  && $a['w'] >$a['tw'] ){
			$a['h'] = ceil($a['h'] * ($a['tw']/$a['w']));
			$a['w'] = $a['tw'];
		}else if( $a['w']/$a['h'] <= $a['tw']/$a['th'] && $a['h'] >$a['th']){
			$a['w'] = ceil($a['w'] * ($a['th']/$a['h']));
			$a['h'] = $a['th'];
		}
		return $a;
	}
}
