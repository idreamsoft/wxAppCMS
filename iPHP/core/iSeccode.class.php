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

class iSeccode {
    public static $config = array(
        'num'    => 4,//字符数
        'size'   => 24,//字体大小
        'width'  => 90,//图片宽度
        'height' => 30,//图片高度
        'line'   => 3, //干扰线数量
        'pixel'  => 90, //干扰点数量
        'shadow' => 0  //阴影
    );
    public static $setcookie = array();

    protected static $im     = null;
    protected static $code   = null;
    protected static $color  = null;

	//检查验证码
	public static function check($seccode, $destroy = false, $cookie_name = 'captcha') {
		$_seccode = self::cookie($cookie_name);
		$_seccode && $cookie_seccode = auth_decode($_seccode);
		$destroy && self::cookie($cookie_name, '', -31536000);
		if (empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
			return false;
		} else {
			return true;
		}
	}
    public static function run($pre=null){
        ob_end_clean();
        @header("Expires: -1");
        @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");

        $name = 'captcha';
        $pre && $name = $pre.'_captcha';

        self::$code OR self::$code = self::mkcode();
        self::cookie($name, auth_encode(self::$code));

        if(function_exists('imagecreate') &&
          function_exists('imagecolorset') &&
          function_exists('imagecopyresized') &&
          function_exists('imagecreatetruecolor') &&
          function_exists('imagecolorallocate') &&
          function_exists('imagearc') &&
          function_exists('imageline') &&
          function_exists('imagesetpixel') &&
          (function_exists('imagettftext') || function_exists('imagechar')) &&
          (function_exists('imagegif') || function_exists('imagepng') || function_exists('imagejpeg')))
        {
            self::image();
        }else{
            self::bmp();
        }
    }
    private static function cookie($key,$value=null,$expires=0){
        if($value===null){
            return iPHP::get_cookie($key);
        }else{
            return iPHP::set_cookie($key, $value,$expires);
        }
    }

    private static function image(){
        self::background();
        self::adulterate();
        function_exists('imagettftext')?self::ttf_font():self::gif_font();

        if(function_exists('imagejpeg')) {
            header('Content-type:image/jpeg');
            $void = imagejpeg(self::$im);
        } else if(function_exists('imagepng')) {
            header('Content-type:image/png');
            $void = imagepng(self::$im);
        } else if(function_exists('imagegif')) {
            header('Content-type:image/gif');
            $void = imagegif(self::$im);
        } else {
            return false;
        }
        imagedestroy(self::$im);
        return $void;
    }
    //生成随机
    private static function mkcode() {
        $charset = '123456789ABCDEFGHIJKMNPQRSTUVWXYZ';
        $_len = strlen($charset)-1;
        for ($i=0;$i<self::$config['num'];$i++) {
            $code.= $charset[rand(0,$_len)];
        }
        return $code;
    }

    //背景
    private static function background() {
        //创建图片，并设置背景色
        self::$im = imagecreatetruecolor(self::$config['width'], self::$config['height']);
        for($i = 0;$i < 3;$i++) {
            $start[$i]       = rand(200, 255);
            $end[$i]         = rand(100, 200);
            $step[$i]        = ($end[$i] - $start[$i]) / self::$config['width'];
            self::$color[$i] = $start[$i];
        }

        for($i = 0;$i < self::$config['width'];$i++) {
            $color = imagecolorallocate(self::$im, self::$color[0], self::$color[1], self::$color[2]);
            imageline(self::$im, $i, 0, $i, self::$config['height'], $color);
            self::$color[0] += $step[0];
            self::$color[1] += $step[1];
            self::$color[2] += $step[2];
        }
        self::$color[0] -= 15;
        self::$color[1] -= 15;
        self::$color[2] -= 15;
    }

    private static function adulterate() {
        for($i=0; $i<self::$config['line']; $i++) {
            $color = imagecolorallocate(self::$im, self::$color[0], self::$color[1], self::$color[2]);
            $x  = rand(0, self::$config['width']);
            $y  = 0;
            $x2 = rand(0,self::$config['width']);
            $y2 = self::$config['height'];

            if($i%2) {
                imagearc(self::$im, $x, $y, $x2,$y2,rand(0, 360), rand(0, 360), $color);
                imagearc(self::$im, $x+1, $y,$x2+1,$y2,rand(0, 360), rand(0, 360), $color);
            } else {
                imageline(self::$im, $x, $y,$x2,$y2, $color);
                imageline(self::$im, $x+1, $y,$x2+1,$y2, $color);
            }
        }
        for ($i=0; $i < self::$config['pixel']; $i++) {
            $color = imagecolorallocate(self::$im, self::$color[0], self::$color[1], self::$color[2]);
            $x = rand(0,self::$config['width']);
            $y = rand(0,self::$config['height']);
            imagesetpixel(self::$im,$x,$y,$color);
            //imagefilledrectangle(self::$im,$x,$y, $x-1, $y-1, $color);
        }
    }

    private static function ttf_font() {
        $font_file  = iPHP_CORE.'/seccode.otf';
        $font       = array();
        $font_size  = self::$config['size'];
        // $ttfb_box = imagettfbbox($font_size,$angle,$font_file,self::$code[0]);
	    $ttfb_box = imagettfbbox($font_size,0,$font_file,self::$code[0]);
	    $font_w = abs($ttfb_box[4] - $ttfb_box[0]);
	    $font_h = abs($ttfb_box[5] - $ttfb_box[1]);
        for ($i=0; $i < self::$config['num']; $i++) {
            $x = floor(self::$config['width']/self::$config['num'])*$i+3;
            $y = $font_h+5;
            $text_color = imagecolorallocate(self::$im, self::$color[0], self::$color[1], self::$color[2]);
            $angle = rand(-10, 20);
            if(self::$config['shadow']) {
                $text_shadowcolor = imagecolorallocate(self::$im, 255 - self::$color[0], 255 - self::$color[1], 255 - self::$color[2]);
                imagettftext(self::$im, $font_size, $angle, $x+1, $y+1, $text_shadowcolor, $font_file, self::$code[$i]);
            }
            imagettftext(self::$im, $font_size, $angle, $x, $y, $text_color, $font_file, self::$code[$i]);
            // imagechar(self::$im,5,$x,$y,self::$code[$i],$text_color);
        }
    }
    private static function gif_font() {
        $gif_dir = array();
        if(function_exists('imagecreatefromgif')) {
            $gif_root = dirname(strtr(__FILE__,'\\','/')).'/seccode/';
            $dirs = opendir($gif_root);
            while($dir = readdir($dirs)) {
                if($dir != '.' && $dir != '..' && file_exists($gif_root.$dir.'/9.gif')) {
                    $gif_dir[] = $dir;
                }
            }
        }

        $width_total = 0;
        for($i = 0; $i < self::$config['num']; $i++) {
            $gif_path = $gif_dir ? $gif_root.$gif_dir[array_rand($gif_dir)].'/'.strtolower(self::$code[$i]).'.gif' : '';
            if($gif_path && file_exists($gif_path)) {
                $font[$i]['file'] = $gif_path;
                $font[$i]['data'] = getimagesize($gif_path);
                $font[$i]['width'] = $font[$i]['data'][0] + rand(0, 6) - 4;
                $font[$i]['height'] = $font[$i]['data'][1] + rand(0, 6) - 4;
                $font[$i]['width'] += rand(0, self::$config['width'] / 5 - $font[$i]['width']);
                $width_total += $font[$i]['width'];
            } else {
                $font[$i]['file'] = '';
                $font[$i]['width'] = 8 + rand(0, self::$config['width'] / 5 - 5);
                $width_total += $font[$i]['width'];
            }
        }
        $x = rand(1, self::$config['width'] - $width_total);
        for($i = 0; $i < self::$config['num']; $i++) {
            if($font[$i]['file']) {
                $imcode = imagecreatefromgif($font[$i]['file']);
                $y = rand(0, self::$config['height'] - $font[$i]['height']);
                if(self::$config['shadow']) {
                    $imcodeshadow = $imcode;
                    imagecolorset($imcodeshadow, 0 , 255 - self::$color[0], 255 - self::$color[1], 255 - self::$color[2]);
                    imagecopyresized(self::$im, $imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
                }
                imagecolorset($imcode, 0 , self::$color[0], self::$color[1], self::$color[2]);
                imagecopyresized(self::$im, $imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
            } else {
                $y = rand(0, self::$config['height'] - 20);
                if(self::$config['shadow']) {
                    $text_shadowcolor = imagecolorallocate(self::$im, 255 - self::$color[0], 255 - self::$color[1], 255 - self::$color[2]);
                    imagechar(self::$im, 5, $x + 1, $y + 1, self::$code[$i], $text_shadowcolor);
                }
                $text_color = imagecolorallocate(self::$im, self::$color[0], self::$color[1], self::$color[2]);
                imagechar(self::$im, 5, $x, $y, self::$code[$i], $text_color);
            }
            $x += $font[$i]['width'];
        }
    }
    /**
    * BMP生成代码 来自网络 出处未知
    **/
    private static function bmp(){
        $numbers = array(
            'B' => array('00','fc','66','66','66','7c','66','66','fc','00'),
            'C' => array('00','38','64','c0','c0','c0','c4','64','3c','00'),
            'E' => array('00','fe','62','62','68','78','6a','62','fe','00'),
            'F' => array('00','f8','60','60','68','78','6a','62','fe','00'),
            'G' => array('00','78','cc','cc','de','c0','c4','c4','7c','00'),
            'H' => array('00','e7','66','66','66','7e','66','66','e7','00'),
            'J' => array('00','f8','cc','cc','cc','0c','0c','0c','7f','00'),
            'K' => array('00','f3','66','66','7c','78','6c','66','f7','00'),
            'M' => array('00','f7','63','6b','6b','77','77','77','e3','00'),
            'P' => array('00','f8','60','60','7c','66','66','66','fc','00'),
            'Q' => array('00','78','cc','cc','cc','cc','cc','cc','78','00'),
            'R' => array('00','f3','66','6c','7c','66','66','66','fc','00'),
            'T' => array('00','78','30','30','30','30','b4','b4','fc','00'),
            'V' => array('00','1c','1c','36','36','36','63','63','f7','00'),
            'W' => array('00','36','36','36','77','7f','6b','63','f7','00'),
            'X' => array('00','f7','66','3c','18','18','3c','66','ef','00'),
            'Y' => array('00','7e','18','18','18','3c','24','66','ef','00'),
            '2' => array('fc','c0','60','30','18','0c','cc','cc','78','00'),
            '3' => array('78','8c','0c','0c','38','0c','0c','8c','78','00'),
            '4' => array('00','3e','0c','fe','4c','6c','2c','3c','1c','1c'),
            '6' => array('78','cc','cc','cc','ec','d8','c0','60','3c','00'),
            '7' => array('30','30','38','18','18','18','1c','8c','fc','00'),
            '8' => array('78','cc','cc','cc','78','cc','cc','cc','78','00'),
            '9' => array('f0','18','0c','6c','dc','cc','cc','cc','78','00')
        );
        foreach($numbers as $i => $number) {
            for($j = 0; $j < 6; $j++) {
                $a1 = substr('012', rand(0, 2), 1).substr('012345', rand(0, 5), 1);
                $a2 = substr('012345', rand(0, 5), 1).substr('0123', rand(0, 3), 1);
                rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
                rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
            }
        }
        $bitmap = array();
        for($i = 0; $i < 20; $i++) {
            for($j = 0; $j < self::$config['num']; $j++) {
                $n = substr(self::$code, $j, 1);
                $bytes = $numbers[$n][$i];
                $a = rand(0, 14);
                array_push($bitmap, $bytes);
            }
        }
        for($i = 0; $i < 8; $i++) {
            $a = substr('012345', rand(0, 2), 1) . substr('012345', rand(0, 5), 1);
            array_unshift($bitmap, $a);
            array_push($bitmap, $a);
        }
        $image = pack('H*', '424d9e000000000000003e000000280000002000000018000000010001000000'.
                '0000600000000000000000000000000000000000000000000000FFFFFF00'.implode('', $bitmap));
        header('Content-Type: image/bmp');
        echo $image;
    }
}
