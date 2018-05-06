<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
  $fileList='
  patch.db.2017042401.php
  patch.db.2017042513.php
  patch.db.2017042814.php
  patch.db.2017043023.php
  patch.db.2017060218.php
  patch.db.2017060408.php
  patch.db.2017072000.php
  patch.db.2017072700.php
  patch.db.2017111000.php
  patch.db.2017111012.php
  patch.db.2017111308.php
  app/admincp/ui/editor.md/lib/codemirror/mode/groovy/index.html
  app/article/admincp/article.markdown.php
  iPHP/core/template/plugins/function.wxml.php
  iPHP/core/template/plugins/output.clean.php
  iPHP/core/template/plugins/shared.escape_chars.php
  iPHP/core/template/plugins/shared.make_timestamp.php
  template/weapp/api/index.category.htm
  template/weapp/pages/index/category.js
  template/weapp/pages/index/category.wxml
  template/weapp/pages/index/category.wxss
  template/www/desktop/static/css/passport.css
  template/www/desktop/static/js/meitu/xiuxiu.js
  template/www/desktop/static/js/ui.plugin.js
  template/www/mobile/static/css/bootstrap-switch.min.css
  template/www/mobile/static/js/bootstrap-switch.min.js
  template/www/mobile/static/js/city_utf8.js
  template/www/mobile/static/js/ui.plugin.js
  template/weapp/utils/wxParse/
  template/www/desktop/comment/
  template/www/desktop/user/
  template/www/mobile/comment/
  template/www/mobile/user/
  template/iCMS/desktop/comment/
  template/iCMS/mobile/comment/
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

