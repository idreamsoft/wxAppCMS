<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
  $fileList=
'template/www/desktop/comment/
template/www/desktop/user/
template/www/mobile/comment/
template/www/mobile/user/
template/iCMS/desktop/comment/
template/iCMS/mobile/comment/';

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

