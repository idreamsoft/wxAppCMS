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
class iPinyin {
    public static function utf2uni($c) {
        switch(strlen($c)) {
            case 1:
                return ord($c);
            case 2:
                $n = (ord($c[0]) & 0x3f) << 6;
                $n += ord($c[1]) & 0x3f;
                return $n;
            case 3:
                $n = (ord($c[0]) & 0x1f) << 12;
                $n += (ord($c[1]) & 0x3f) << 6;
                $n += ord($c[2]) & 0x3f;
                return $n;
            case 4:
                $n = (ord($c[0]) & 0x0f) << 18;
                $n += (ord($c[1]) & 0x3f) << 12;
                $n += (ord($c[2]) & 0x3f) << 6;
                $n += ord($c[3]) & 0x3f;
                return $n;
        }
    }
    public static function table() {
        return unserialize(gzuncompress(file_get_contents(iPHP_CORE.'/pinyin.table')));
    }
	public static function get($str,$split="",$pn=true) {
        if(!isset($GLOBALS["iPHP_PINTIN"])) {
            $GLOBALS["iPHP_PINTIN"] = self::table();
        }
        preg_match_all('/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/',trim($str),$match);
        $s = $match[0];
        $c = count($s);
        for ($i=0;$i<$c;$i++) {
            $uni = strtoupper(dechex(self::utf2uni($s[$i])));
            if(strlen($uni)>2) {
                $pyArr = $GLOBALS["iPHP_PINTIN"][$uni];
                $py    = is_array($pyArr)?$pyArr[0]:$pyArr;
                $pn && $py=str_replace(array('1','2','3','4','5'), '', $py);
                $zh && $split && $res[]=$split;
                $res[]  = strtolower($py);
                $zh   = true;
                $az09 = false;
            }else if(preg_match("/[a-z0-9]/i",$s[$i])) {
                $zh && $i!=0 && !$az09 && $split && $res[]=$split;
                $res[]  = $s[$i];
                $zh   = true;
                $az09 = true;
            }else {
                $sp=true;
                if($split){
                    if($s[$i]==' ') {
                        $res[]=$sp?'':$split;
                        $sp=false;
                    }else {
                        $res[]=$sp?$split:'';
                        $sp=true;
                    }
                }else {
                    $res[]='';
                }
                $zh   = false;
                $az09 = false;
            }
        }
        return str_replace(array('Üe','Üan','Ün','lÜ','nÜ'),array('ue','uan','un','lv','nv'),implode('',(array)$res));
    }
}
