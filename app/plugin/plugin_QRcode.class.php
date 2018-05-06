<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class plugin_QRcode {
    /**
     * [插件:生成二维码]
     * @param [type] $content  [参数]
     */
    public static function HOOK($content,$output=false) {
        plugin::init(__CLASS__);
        plugin::library('phpqrcode');
        if($output){
            //text($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4)
            $margin  = 2;
            $size    = 4;
            $frame   = QRcode::text($content, false, 'L', $size, $margin);
            $maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($frame)+2*$margin));
            // QRimage::png($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin,$saveandprint);
            $pixelPerPoint = min(max(1, $size), $maxSize);
            //png($frame, $filename = false, $pixelPerPoint = 4, $outerFrame = 4,$saveandprint=FALSE)
            $im = QRimage::image($frame, $pixelPerPoint, $margin);
            ob_start();
            imagepng($im);
            $image = ob_get_contents();
            ob_clean();
            return $image;
        }
        $expires = 86400;
        header("Cache-Control: maxage=" . $expires);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
        header('Content-type: image/png');
		$filepath = false;
		if (isset($_GET['QRcode_cache'])) {
			$name = substr(md5($content), 8, 16);
			$filepath = iPHP_APP_CACHE . '/QRcode.' . $name . '.png';
		}
		is_file($filepath) OR QRcode::png($content, $filepath, 'L', 4, 2);
		$filepath && $content = readfile($filepath);
        return $content;
    }
}
