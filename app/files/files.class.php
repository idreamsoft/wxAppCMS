<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) 2012 iiiphp.com. All rights reserved.
 *
 * @author icmsdev <iiiphp@qq.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.0.0
 *
 * CREATE TABLE `iPHP_files` (
 *   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 *   `userid` int(10) unsigned NOT NULL DEFAULT '0',
 *   `filename` varchar(255) NOT NULL DEFAULT '',
 *   `ofilename` varchar(255) NOT NULL DEFAULT '',
 *   `path` varchar(255) NOT NULL DEFAULT '',
 *   `intro` varchar(255) NOT NULL DEFAULT '',
 *   `ext` varchar(10) NOT NULL DEFAULT '',
 *   `size` int(10) unsigned NOT NULL DEFAULT '0',
 *   `time` int(10) unsigned NOT NULL DEFAULT '0',
 *   `type` tinyint(1) NOT NULL DEFAULT '0',
 *   PRIMARY KEY (`id`),
 *   KEY `ext` (`ext`),
 *   KEY `path` (`path`),
 *   KEY `ofilename` (`ofilename`),
 *   KEY `fn_userid` (`filename`,`userid`)
 * ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
 *
 * CREATE TABLE `iPHP_files_map` (
 * `fileid` int(10) unsigned NOT NULL,
 * `appid` int(10) NOT NULL,
 * `indexid` int(10) NOT NULL,
 * `addtime` int(10) NOT NULL,
 * PRIMARY KEY (`fileid`),
 * UNIQUE KEY `unique` (`fileid`,`appid`,`indexid`)
 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8
 *
 */

class files {
    public static $TABLE_DATA       = null;
    public static $TABLE_MAP        = null;
    public static $check_data       = true;
    public static $userid           = false;
    public static $watermark_enable = true;
    public static $watermark_config = null;
    public static $cloud_enable     = true;

    public static $_DATA_TABLE     = null;
    public static $_MAP_TABLE      = null;

    public static $PREG_IMG        = '@<img[^>]+src=(["\']?)(.*?)\\1[^>]*?>@is';
    public static $IMG_EXT         = array('jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp');

    public static function config($table = array()) {
        empty($table) && $table = array('files','files_map');

        list(self::$TABLE_DATA,self::$TABLE_MAP) = $table;
        self::$_DATA_TABLE = '`'.iPHP_DB_PREFIX . self::$TABLE_DATA.'`';
        self::$_MAP_TABLE  = '`'.iPHP_DB_PREFIX . self::$TABLE_MAP.'`';
    }

    public static function init($vars=null){
        files::config();

        isset($vars['userid']) && files::$userid = $vars['userid'];

        iFS::$CALLABLE = array(
            'insert' => array('files','insert'),
            'update' => array('files','update'),
            'get'    => array('files','get'),
            // 'write'  => array('files','cloud_write'),
            'upload' => array(),
        );

        if (self::$cloud_enable) {
            files_cloud::init(iCMS::$config['cloud']);
        }
        if(self::$watermark_enable){
            self::$watermark_config = iCMS::$config['watermark'];
            iFS::$CALLABLE['upload']['mark']= array('files','mark');
        }
    }
    public static function mark($fp,$ext=null) {
        if(!files::$watermark_enable) return;
        if(!self::$watermark_config['enable']) return;

        $config = self::$watermark_config;
        $allow_ext = self::$IMG_EXT;
        $config['allow_ext'] && $allow_ext = explode(',', $config['allow_ext']);
        $ext OR $ext = iFS::get_ext($fp);
        if (in_array($ext, $allow_ext)) {
            iPicture::init($config);
            if($config['mode']){
                return iPicture::mosaics($fp);
            }else{
                return iPicture::watermark($fp);
            }
        }
    }
    public static function update_size($id,$size='0'){
        iDB::query("
            UPDATE ".self::$_DATA_TABLE."
            SET `size`='$size'
            WHERE `id` = '$id'
        ");
    }
    public static function index_fileid($indexid,$appid='1'){
        $rs      = iDB::all("SELECT `fileid` FROM " . self::$_MAP_TABLE . " WHERE indexid = '{$indexid}'  AND appid = '{$appid}' ");
        $fileid0 = iSQL::values($rs,'fileid','array',null);
        $result  = array();
        if($fileid0){
            $rs = iDB::all("SELECT `fileid` FROM " . self::$_MAP_TABLE . " WHERE `fileid` IN(".implode(',', $fileid0).") and indexid <> '{$indexid}'");
            $fileid1 = iSQL::values($rs,'fileid','array',null);
            if($fileid1){
                $result  = array_diff((array)$fileid0 , (array)$fileid1);
            }else{
                $result  = $fileid0;
            }
        }
        return $result;
    }
    public static function delete_file($ids){
        if(empty($ids)) return array();

        $ids  = iSQL::multi_var($ids,true);
        $sql  = iSQL::in($ids,'id',false,true);
        $rs   = iDB::all("SELECT * FROM ".self::$_DATA_TABLE." where {$sql}");
        $ret  = array();
        foreach ((array)$rs as $key => $value) {
            $path = self::path($value);
            $filepath = iFS::fp($path,'+iPATH');
            iFS::del($filepath);
            $ret[] = $path;
        }
        return $ret;
    }
    public static function delete_fdb($ids,$indexid=0,$appid='1'){
        if(empty($ids)) return array();

        $ids  = iSQL::multi_var($ids,true);
        $sql  = iSQL::in($ids,'id',false,true);
        $sql && iDB::query("DELETE FROM ".self::$_DATA_TABLE." where {$sql}");
        $msql = iSQL::in($ids,'fileid',false,true);
        $indexid && $msql.= iSQL::in($indexid,'indexid');
        $appid && $msql.= iSQL::in($appid,'appid');
        $msql && iDB::query("DELETE FROM ".self::$_MAP_TABLE." where {$msql}");

    }
    public static function del_app_data($appid=null){
        if($appid){
            iDB::query("
                DELETE FROM ".self::$_DATA_TABLE." where `id` IN(
                    SELECT `fileid` FROM ".self::$_MAP_TABLE." WHERE `appid` = '{$appid}'
                )
            ");
            iDB::query("DELETE FROM ".self::$_MAP_TABLE." where `appid` = '{$appid}'");
        }
    }

    public static function path($F,$root=false){
        $path = $F['path'].$F['filename'].'.'.$F['ext'];
        $root&& $path = iFS::fp($path,'+iPATH');
        return $path;
    }
    public static function insert($data, $type = 0,$status=1) {
        if (!self::$check_data) {
            return;
        }
        $userid = self::$userid === false ? 0 : self::$userid;
        $data['userid'] = $userid;
        $data['time']   = time();
        $data['type']   = $type;
        $data['status'] = $status;
        iSecurity::_addslashes($data);
        $data = iSecurity::escapeStr($data);
        iWAF::check_data($data);
        iDB::insert(self::$TABLE_DATA, $data);
        return iDB::$insert_id;
    }
    public static function update($data, $fid = 0) {
        if (empty($fid)) {
            return;
        }

        $userid = self::$userid === false ? 0 : self::$userid;
        $data['userid'] = $userid;
        $data['time'] = time();
        iSecurity::_addslashes($data);
        $data = iSecurity::escapeStr($data);
        iWAF::check_data($data);
        iDB::update(self::$TABLE_DATA, $data, array('id' => $fid));
    }
    public static function get($f, $v,$s='*') {
        if (!self::$check_data) {
            return;
        }

        $sql = self::$userid === false ? '' : " AND `userid`='" . self::$userid . "'";
        $rs = iDB::row("SELECT {$s} FROM " . self::$_DATA_TABLE. " WHERE `$f`='$v' {$sql} LIMIT 1");

        if ($rs&&$s=='*') {
            $rs->filepath = $rs->path . $rs->filename . '.' . $rs->ext;
            if ($f == 'ofilename') {
                $filepath = iFS::fp($rs->filepath, '+iPATH');
                if (is_file($filepath)) {
                    return $rs;
                } else {
                    return false;
                }
            }
        }
        return $rs;
    }
    public static function set_map($appid,$indexid,$value,$field='id'){
        switch ($field) {
            case 'path':
                $filename = iFS::filename($value);
            case 'filename':
                $info     = self::get('filename',$filename,'id');
                $fileid   = $info->id;
            break;
            case 'id':
                $fileid   = $value;
            break;
        }

        if($fileid){
            $userid  = self::$userid;
            $addtime = time();
            $data    = compact('fileid','userid','appid','indexid','addtime');
            self::idb_map($data);
        }
    }

    public static function idb_map($data,$where=null) {
        if($where){
            return iDB::update(self::$TABLE_MAP, $data,$where);
        }
        return iDB::insert(self::$TABLE_MAP, $data,true);
    }

    public static function set_file_iid($content,$iid,$appid) {
        if(empty($content)) return;

        is_array($content) && $content = implode('', $content);
        $content = stripslashes($content);
        $array   = self::preg_img($content,$match);
        foreach ($array as $key => $value) {
            files::set_map($appid,$iid,$value,'path');
        }
    }
    public static function preg_img($content,&$match=array()) {
        $match   = (array)$match;
        $content = str_replace("<img", "\n\n<img", $content);
        preg_match_all(self::$PREG_IMG, $content, $match);
        return array_unique($match[2]);
    }
    public static function icon($fn, $dir = null) {
        $ext = strtolower(iFS::get_ext($fn));
        $iconArray = array(
        "aac","ace","ai","ain","amr","app","arj","asf","asp","aspx","audio","av","avi","bin","bmp","cab","cad","cat","cdr","chm",
        "code","com","common","css","cur","dat","db","dll","dmv","doc","dot","dps","dpt","dwg","dxf","emf","eps","et","ett","exe",
        "fla","flash","ftp","gif","help","hlp","htm","html","icl","ico","image","img","inf","ini","iso","jpeg","jpg","js","m3u",
        "max","mdb","mde","mht","mid","midi","mov","mp","mp3","mp4","mpeg","mpg","msi","nrg","ocx","ogg","ogm","pdf","php","pic",
        "png","pot","ppt","psd","pub","qt","ra","ram","rar","rm","rmvb","rtf","swf","tar","tif","tiff","txt","unknow","unknown",
        "url","vbs","vsd","vss","vst","wav","wave","wm","wma","wmd","wmf","wmv","wps","wpt","xls","xlt","xml","zip");
        $key = array_search($ext,$iconArray);
        $src = $ext.'.gif';
        $key===false && $src = "common.gif";
        $dir OR $dir = "./app/files/ui";
        return '<img border="0" src="' . $dir . '/icon/' . $src . '" align="absmiddle" class="icon">';
    }
    public static function thumb($sfp,$w='',$h='',$scale=true) {
        if(empty($sfp)){
            return iCMS_FS_URL.'1x1.gif';
        }
        if(strpos($sfp,'_')!==false){
            if(preg_match('|.+\d+x\d+\.jpg$|is', $sfp)){
                return $sfp;
            }
        }
        $uri = parse_url(iCMS_FS_URL);
        if(stripos($sfp,$uri['host']) === false){
            return $sfp;
        }
        $size = $w.'x'.$h;

        if(empty(iCMS::$config['thumb']['size'])){
            return $sfp;
        }

        $size_map = explode("\n", iCMS::$config['thumb']['size']);
        $size_map = array_map('trim', $size_map);
        $size_map = array_flip($size_map);
        if(!isset($size_map[$size])){
            return $sfp;
        }

        if(iCMS::$config['FS']['yun']['enable']){
            if(iCMS::$config['FS']['yun']['sdk']['QiNiuYun']['Bucket']){
                return $sfp.'?imageView2/1/w/'.$w.'/h/'.$h;
            }
            if(iCMS::$config['FS']['yun']['sdk']['TencentYun']['Bucket']){
                return $sfp.'?imageView2/2/w/'.$w.'/h/'.$h;
            }
        }
        return $sfp.'_'.$size.'.jpg';
    }
}
