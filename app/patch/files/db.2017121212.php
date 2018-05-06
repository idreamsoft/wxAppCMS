<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    iDB::query("
      UPDATE `#iCMS@__apps`
      SET `config`=REPLACE(`config`,'\"menu\":\"main\"','\"menu\":\"default\"')
    ");
    $msg.='升级[apps]数据<iCMS>';
    $msg.='请更新菜单缓存<iCMS>';
    return $msg;
});

