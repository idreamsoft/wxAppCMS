<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
  $indexs = apps_db::indexes('#iCMS@__user');
  if($indexs['email']){
    iDB::query("
    ALTER TABLE `#iCMS@__user`
      DROP INDEX `email`;
    ");
  }
  if($indexs['username']){
    iDB::query("
    ALTER TABLE `#iCMS@__user`
      DROP INDEX `username`,
      ADD  KEY `username` (`username`);
    ");
  }
  return '更新用户表索引';
});

