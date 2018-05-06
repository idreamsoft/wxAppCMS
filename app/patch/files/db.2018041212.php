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
  $msg.='更新用户表索引';
  $members = iDB::all("SELECT * FROM `#iCMS@__members` order by uid DESC");
  foreach ($members as $key => $value) {
      membersAdmincp::clone_touser($value['uid'],$value);
  }
  $msg.='完成在用户表创建管理员克隆账号<iCMS>';
  return $msg;
});

