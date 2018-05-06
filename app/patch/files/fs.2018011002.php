<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
  $fileList=
'iPHP/core/iPgsql.class.php
iPHP/core/iPlugin.class.php
iPHP/core/iSession.class.php
iPHP/core/iSQLite.class.php
iPHP/core/memcached.class.php
iPHP/core/redis.class.php
iPHP/library/vendor/Vendor.TBAPI.php
iPHP/library/tbapi.class.php
iPHP/library/sphinx.class.php
iPHP/library/vendor/Vendor.SPHINX.php
iPHP/library/pclzip.class.php
iPHP/library/phpQuery.php
';

  $listArray = explode("\n", $fileList);
  $dirname = patch::$release?:str_replace('.php','',basename(__FILE__));
  $bakdir = iPATH.'.backup/patch.'.$dirname;
  iFS::mkdir($bakdir);

  foreach ($listArray as $key => $path) {
      $path = trim($path);
      if($path){
        $fp  = iPATH.$path;
        $bfp = $bakdir . '/' . $path;
        if(is_file($fp)){
          iFS::backup($fp,$bfp) && $msg.= '备份 [' . $fp . '] 文件 到 [' . $bfp . ']<iCMS>';
          iFS::del($fp);
          $msg.='清理多余文件['.$path.']<iCMS>';
        }elseif(is_dir($fp)){
          iFS::backup($fp,$bfp) && $msg.= '备份 [' . $fp . '] 目录 到 [' . $bfp . ']<iCMS>';
          iFS::rmdir($fp);
          $msg.='清理多余目录['.$path.']<iCMS>';
        }
      }
  }
  return $msg;
});

