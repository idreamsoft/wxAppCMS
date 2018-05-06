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
if (!class_exists('Gmagick',false)) {
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
