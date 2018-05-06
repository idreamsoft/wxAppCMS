<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

class forms_zip {
    public static $zipName = null;
    public static $zipFile = null;
    public static $next = false;
    public static $test = false;
    public static $msg_mode = null;
    public static $app_id = null;
    public static $app_name = null;
    public static $app_data = false;

    public static function install() {
        $zipFile = self::$zipFile;
        if(!file_exists($zipFile)){
            return self::msg("安装包不存在",false);
        }

        iPHP::vendor('PclZip'); //加载zip操作类
        $zip = new PclZip($zipFile);
        if (false == ($archive_files = $zip->extract(PCLZIP_OPT_EXTRACT_AS_STRING))) {
          return self::msg("ZIP包错误",false);
        }

        if (0 == count($archive_files)) {
          return self::msg("空的ZIP文件",false);
        }
        $msg = null;
        //安装表单数据
        $setup_msg = self::setup_data($archive_files);
        if($setup_msg===true){
            $msg.= self::msg('表单数据安装完成',true);
        }else{
            return self::msg($setup_msg.'安装出错',false);
        }
        //创建表单表
        if(self::setup_table($archive_files)){
            $msg.= self::msg('表单表创建完成',true);
        }

        self::$test OR iFS::rm(self::$zipFile);
        $msg.= self::msg('表单安装完成',true);
        return $msg;
    }

    public static function setup_data(&$archive_files){
        foreach ($archive_files AS $key => $file) {
            $filename = basename($file['filename']);
            if($filename=="iCMS.APP.DATA.php"){
              $content = get_php_content($file['content']);
              $content = base64_decode($content);
              $array   = unserialize($content);
              $check_app = iDB::value("
                SELECT `id` FROM `#iCMS@__forms`
                WHERE `app` ='".$array['app']."'
              ");
              if($check_app){
                $_msg = self::msg('检测表单是否存在',false);
                return self::msg($_msg.'该表单已经存在',false);
              }

              if($array['table']){
                $tableArray = apps::table_item($array['table']);
                foreach ($tableArray AS $value) {
                  if(iDB::check_table($value['table'],false)){
                    $_msg = self::msg('检测表单表是否存在',false);
                    return self::msg($_msg.'['.$value['table'].']数据表已经存在');
                  }
                }
              }
              $array['addtime'] = time();
              $array = array_map('addslashes', $array);
              self::$test OR self::$app_id = iDB::insert("forms",$array);
              unset($archive_files[$key]);
              self::$app_data = $array;
              self::$app_name = $array['name'];
              return true;
            }
        }
        return false;
    }
    public static function setup_table(&$archive_files){
        foreach ($archive_files AS $key => $file) {
            $filename = basename($file['filename']);
            if($filename=="iCMS.APP.TABLE.php"){
              $content = get_php_content($file['content']);
              if(!self::$test){
                $content && apps_db::multi_query($content);
              }
              unset($archive_files[$key]);
              return true;
            }
        }
        return false;
    }
    public static function msg($text,$s=0){
        $text = iSecurity::filter_path($text);
        if(self::$msg_mode=='alert'){
            $s OR iUI::alert($text);
        }else{
            return str_pad($text,80,'.').iUI::check($s).'<iCMS>';
        }
    }
}
