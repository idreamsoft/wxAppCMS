<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) 2012 iiiphp.com. All rights reserved.
 *
 * @author icmsdev <iiiphp@qq.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.0.0
 */
class files_cloud{
    public static $config = null;
    public static $error  = null;

    public static function init($config) {
        if(!$config['enable']) return false;

        self::$config = $config;
        iFS::$CALLABLE['upload'][] = array(__CLASS__,'upload');
        iFS::$CALLABLE['delete']   = array(__CLASS__,'delete');
    }
    public static function sdk($sdk=null) {
        if($sdk===null) return false;

        $conf = self::$config['sdk'][$sdk];
        if($conf['AccessKey'] && $conf['SecretKey']){
            $class = 'files_cloud_'.$sdk;
            return new $class($conf);
        }else{
            return false;
        }
    }
    /**
     * [上传文件]
     * @param  [type] $fileRootPath  [文件绝对路径]
     * @param  [type] $ext [description]
     * @return [type]      [description]
     */
    public static function upload($fileRootPath,$ext) {
        $res = self::upload_file($fileRootPath);
        //不保留本地功能
        if(self::$config['local']){
            //删除delete hook阻止云端删除动作
            $cb = iFS::$CALLABLE['delete'];
            iFS::$CALLABLE['delete'] = null;
            iFS::del($fileRootPath);
            iFS::$CALLABLE['delete'] = $cb;
        }
        return $res;
    }
    /**
     * [上传文件]
     * @param  [type] $fileRootPath   [文件绝对路径]
     * @return [type]        [description]
     */
    public static function upload_file($fileRootPath){
        if(!self::$config['enable']) return false;

        foreach ((array)self::$config['sdk'] as $sdk => $conf) {
            $filePath = ltrim(iFS::fp($fileRootPath,'-iPATH'),'/');
            $client = self::sdk($sdk);
            if($client){
                $res = $client->_upload_file($fileRootPath,$filePath);
                $res = json_decode($res,true);
                if($res['error']){
                    self::$error[$sdk] = array(
                        'action' => 'upload',
                        'code'   => 0,
                        'state'  => 'Error',
                        'msg'    => $res['msg']
                    );
                }
            }
        }
    }
    /**
     * [删除文件]
     * @param  [type] $fileRootPath   [文件绝对路径]
     * @return [type]        [description]
     */
    public static function delete($fileRootPath) {
        if(!self::$config['enable']) return false;

        foreach ((array)self::$config['sdk'] as $sdk => $conf) {
            $filePath     = ltrim(iFS::fp($fileRootPath,'-iPATH'),'/');
            $client = self::sdk($sdk);
            if($client){
                $res = $client->_delete_file($filePath);
                $res = json_decode($res,true);
                if($res['error']){
                    self::$error[$sdk] = array(
                        'action' => 'delete',
                        'code'   => 0,
                        'state'  => 'Error',
                        'msg'    => $res['msg']
                    );
                }
            }
        }
    }
}
