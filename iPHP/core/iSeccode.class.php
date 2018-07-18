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

    public static $_width      = 0;     //图片宽度
    public static $_height     = 0;     //图片高度
    public static $_fontNum    = 4;     //字符数
    public static $_fontSize   = 22;    //字体大小
    public static $_fontShadow = 0;     //字体阴影色差 0 无阴影
    public static $_fontAngle  = 0;     //旋转角度 0 无旋转
    public static $_fontRand   = 0;     //随机字体
    public static $_lineNum    = 0;     //干扰线数量
    public static $_pixelNum   = 0;     //干扰点数量
    public static $_curveNum   = 0;     //正弦曲线数量
    public static $_bgcharNum  = 4;     //背景字符数量组,每组5个
    public static $_contrast   = 125;   //颜色反差值,越大越好识别 最大200
    public static $_spacing    = 0;     //字符间距
    public static $_randcolor  = 0;     //随机颜色
    public static $_charset    = '2345789ABCDEFHJKLMNPQRTUVWXYZ';

    protected static $bgcolor    = array();
    protected static $color      = array();
    protected static $code       = null;
    protected static $curveColor = null;
    protected static $_image     = null;
    protected static $_color     = null;

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
        self::init();
        self::bgChar();

        function_exists('imagettftext')?self::ttf():self::gif();
        for ($i=0;$i<self::$_curveNum;$i++) {
            self::curve();
        }
        self::adulterate();

        if(function_exists('imagejpeg')) {
            header('Content-type:image/jpeg');
            $void = imagejpeg(self::$_image);
        } else if(function_exists('imagepng')) {
            header('Content-type:image/png');
            $void = imagepng(self::$_image);
        } else if(function_exists('imagegif')) {
            header('Content-type:image/gif');
            $void = imagegif(self::$_image);
        } else {
            return false;
        }
        imagedestroy(self::$_image);
        return $void;
    }
    //生成随机
    private static function mkcode() {
        $charset = self::$_charset;
        $_len = strlen($charset)-1;
        for ($i=0;$i<self::$_fontNum;$i++) {
            $code.= $charset[mt_rand(0,$_len)];
        }
        return $code;
    }

    //背景
    private static function init() {
        self::$_width   OR self::$_width   = self::$_fontNum * (self::$_fontSize * 1.25);
        self::$_height  OR self::$_height  = self::$_fontSize * 1.85;
        // self::$_lineNum OR self::$_lineNum = round(self::$_fontNum*1.2);
        self::$_image = imagecreate(self::$_width, self::$_height);

        for($i = 0;$i < 3;$i++) {
            $bgcolor[$i] = mt_rand(220, 240);
            $color[$i]   = abs($bgcolor[$i]-self::$_contrast);
        }
        self::$bgcolor = $bgcolor;
        self::$color   = $color;
        imagecolorallocate(self::$_image, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
        self::$_color = imagecolorallocate(self::$_image, $color[0], $color[1], $color[2]);
        self::$curveColor = self::$_color;
    }

    private static function adulterate() {
        $color = self::$_color;
        for($i=0; $i<self::$_lineNum; $i++) {
            if($i%2) {
                $x  = mt_rand(0, self::$_width);
                $y  = 0;
                $x2 = mt_rand(0,self::$_width);
                $y2 = self::$_height;
                $s = 0;
                $e = mt_rand(0, 360);
                imagearc(self::$_image, $x, $y, $x2,$y2,$s, $e, $color);
                imagearc(self::$_image, $x+1, $y,$x2+1,$y2,$s, $e, $color);
            } else {
                $x  = 0;
                $y  = mt_rand(0, self::$_height);
                $x2 = self::$_width;
                $y2 = mt_rand(0, self::$_height);
                imageline(self::$_image, $x, $y,$x2,$y2, $color);
                imageline(self::$_image, $x, $y+1,$x2,$y2+1, $color);
            }
        }
        for ($i=0; $i < self::$_pixelNum; $i++) {
            $x = mt_rand(0,self::$_width);
            $y = mt_rand(0,self::$_height);
            $color = self::randColor(0,1,-50);
            imagesetpixel(self::$_image,$x,$y,$color);
            imagesetpixel(self::$_image,$x+1,$y,$color);
            imagesetpixel(self::$_image,$x-1,$y,$color);
            imagesetpixel(self::$_image,$x,$y+1,$color);
            imagesetpixel(self::$_image,$x,$y-1,$color);
            //imagefilledrectangle(self::$_image,$x,$y, $x-1, $y-1, $color);
        }
    }
    private static function bgChar() {
        for($i = 0; $i < self::$_bgcharNum; $i++){
            $color = self::randColor(0,1,-50);
            for($j = 0; $j < 5; $j++) {
                imagestring(
                    self::$_image,
                    5,
                    mt_rand(-5, self::$_width),
                    mt_rand(-5, self::$_height),
                    self::$_charset[mt_rand(0, 28)], // 杂点文本为随机的字母或数字
                    $color
                );
            }
        }
    }
    private static function randColor($flag=false,$rand=false,$fix=0){
        if(self::$_randcolor||$rand){
            $R = mt_rand(self::$color[0],self::$bgcolor[0]+$fix);
            $G = mt_rand(self::$color[1],self::$bgcolor[1]+$fix);
            $B = mt_rand(self::$color[2],self::$bgcolor[2]+$fix);
            $color = imagecolorallocate(self::$_image,$R,$G,$B);
        }else{
            $R = self::$color[0];
            $G = self::$color[1];
            $B = self::$color[2];
            $color = self::$_color;
        }
        return $flag?array($color,$R,$G,$B):$color;
    }
    private static function getTtf(&$n=0) {
        $n = mt_rand(1, 10);
        return iPHP_CORE. '/seccode/ttf/t' .$n. '.ttf';;
    }
    private static function ttf() {
        $x     = 3;
        $y     = self::$_height*0.8;
        $ttf   = self::getTtf($ttf_num);
        $size  = self::$_fontSize;
        $ttf_num == "9" && $size = self::$_fontSize *0.8;
        $color   = self::$_color;
        $spacing = self::$_width/self::$_fontNum;
        for ($i = 0; $i<self::$_fontNum; $i++) {
            self::$_fontRand && $ttf = self::getTtf();
            $angle = self::$_fontAngle?mt_rand(0, self::$_fontAngle):0;
            $code  = self::$code[$i];
            list($color,$R,$G,$B) = self::randColor(true);
            if(self::$_fontShadow) {
                $sr = min(abs($R+self::$_fontShadow),255);
                $sg = min(abs($G+self::$_fontShadow),255);
                $sb = min(abs($B+self::$_fontShadow),255);
                $shadow = imagecolorallocate(self::$_image,$sr,$sg,$sb);
                imagettftext(self::$_image, $size, $angle, $x+1, $y+1, $shadow, $ttf, $code);
            }
            imagettftext(self::$_image,$size,$angle,$x,$y,$color,$ttf, $code);
            self::$curveColor = $color;
            if(self::$_spacing){
                $b  = imagettfbbox($size,$angle, $ttf, $code);
                $bw = abs($b[4] - $b[0]);
                $x += $bw+self::$_spacing;
            }else{
                $x += mt_rand($spacing*0.85,$spacing);
            }
        }

    }
    /**
     * 验证码干扰线。
     * @原作者： 流水孟春 <cmpan@qq.com>
     * @修  改： flymorn <www.piaoyi.org>
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     *      正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     */
    protected static function curve() {
        $color = self::$curveColor;

        $A = mt_rand(1, self::$_height/2);                  // 振幅
        $b = mt_rand(-self::$_height/4, self::$_height/4);   // Y轴方向偏移量
        $f = mt_rand(-self::$_height/4, self::$_height/4);   // X轴方向偏移量
        $T = mt_rand(self::$_height*1.5, self::$_width*2);  // 周期
        $w = (2* M_PI)/$T;
        $px1 = 0;  // 曲线横坐标起始位置
        $px2 = mt_rand(self::$_width/2, self::$_width * 0.667);  // 曲线横坐标结束位置
        for ($px=$px1; $px<=$px2; $px=$px+ 0.9) {
            // $color = self::randColor();
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + self::$_height/2;  // y = Asin(ωx+φ) + b
                $i = (int) ((self::$_fontSize - 6)/4);
                while ($i > 0) {
                    imagesetpixel(self::$_image, $px + $i, $py + $i, $color);
                    //这里画像素点比imagettftext和imagestring性能要好很多
                    $i--;
                }
            }
        }

        $A = mt_rand(1, self::$_height/2);                  // 振幅
        $f = mt_rand(-self::$_height/4, self::$_height/4);   // X轴方向偏移量
        $T = mt_rand(self::$_height*1.5, self::$_width*2);  // 周期
        $w = (2* M_PI)/$T;
        $b = $py - $A * sin($w*$px + $f) - self::$_height/2;
        $px1 = $px2;
        $px2 = self::$_width;
        for ($px=$px1; $px<=$px2; $px=$px+ 0.9) {
            // $color = self::randColor();
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + self::$_height/2;  // y = Asin(ωx+φ) + b
                $i = (int) ((self::$_fontSize - 8)/4);
                while ($i > 0) {
                    imagesetpixel(self::$_image, $px + $i, $py + $i, $color);
                    //这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出
                    //的（不用while循环）性能要好很多
                    $i--;
                }
            }
        }
    }
    private static function gif() {
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
        for($i = 0; $i < self::$_fontNum; $i++) {
            $gif_path = $gif_dir ? $gif_root.$gif_dir[array_rand($gif_dir)].'/'.strtolower(self::$code[$i]).'.gif' : '';
            if($gif_path && file_exists($gif_path)) {
                $font[$i]['file'] = $gif_path;
                $font[$i]['data'] = getimagesize($gif_path);
                $font[$i]['width'] = $font[$i]['data'][0] + mt_rand(0, 6) - 4;
                $font[$i]['height'] = $font[$i]['data'][1] + mt_rand(0, 6) - 4;
                $font[$i]['width'] += mt_rand(0, self::$_width / 5 - $font[$i]['width']);
                $width_total += $font[$i]['width'];
            } else {
                $font[$i]['file'] = '';
                $font[$i]['width'] = 8 + mt_rand(0, self::$_width / 5 - 5);
                $width_total += $font[$i]['width'];
            }
        }
        $x = mt_rand(1, self::$_width - $width_total);
        for($i = 0; $i < self::$_fontNum; $i++) {
            if($font[$i]['file']) {
                $imcode = imagecreatefromgif($font[$i]['file']);
                $y = mt_rand(0, self::$_height - $font[$i]['height']);
                if(self::$_fontShadow) {
                    $imcodeshadow = $imcode;
                    imagecolorset($imcodeshadow, 0 , 255 - self::$color[0], 255 - self::$color[1], 255 - self::$color[2]);
                    imagecopyresized(self::$_image, $imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
                }
                imagecolorset($imcode, 0 , self::$color[0], self::$color[1], self::$color[2]);
                imagecopyresized(self::$_image, $imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
            } else {
                $y = mt_rand(0, self::$_height - 20);
                if(self::$_fontShadow) {
                    $text_shadowcolor = imagecolorallocate(self::$_image, 255 - self::$color[0], 255 - self::$color[1], 255 - self::$color[2]);
                    imagechar(self::$_image, 5, $x + 1, $y + 1, self::$code[$i], $text_shadowcolor);
                }
                $text_color = imagecolorallocate(self::$_image, self::$color[0], self::$color[1], self::$color[2]);
                imagechar(self::$_image, 5, $x, $y, self::$code[$i], $text_color);
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
                $a1 = substr('012', mt_rand(0, 2), 1).substr('012345', mt_rand(0, 5), 1);
                $a2 = substr('012345', mt_rand(0, 5), 1).substr('0123', mt_rand(0, 3), 1);
                mt_rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
                mt_rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
            }
        }
        $bitmap = array();
        for($i = 0; $i < 20; $i++) {
            for($j = 0; $j < self::$_fontNum; $j++) {
                $n = substr(self::$code, $j, 1);
                $bytes = $numbers[$n][$i];
                $a = mt_rand(0, 14);
                array_push($bitmap, $bytes);
            }
        }
        for($i = 0; $i < 8; $i++) {
            $a = substr('012345', mt_rand(0, 2), 1) . substr('012345', mt_rand(0, 5), 1);
            array_unshift($bitmap, $a);
            array_push($bitmap, $a);
        }
        $image = pack('H*', '424d9e000000000000003e000000280000002000000018000000010001000000'.
                '0000600000000000000000000000000000000000000000000000FFFFFF00'.implode('', $bitmap));
        header('Content-Type: image/bmp');
        echo $image;
    }
}
