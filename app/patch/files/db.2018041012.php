<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    if(!iDB::check_table('apps_store')){
        iDB::query("
    CREATE TABLE `#iCMS@__apps_store`(
      `id` int(10) unsigned NOT NULL  auto_increment ,
      `sid` int(10) NOT NULL  DEFAULT 0 ,
      `appid` int(10) NOT NULL  DEFAULT 0 COMMENT 'appid' ,
      `app` varchar(255) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT 'app' ,
      `name` varchar(255) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '名称' ,
      `version` varchar(255) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '版本' ,
      `authkey` varchar(255) COLLATE utf8_general_ci NOT NULL  DEFAULT '' ,
      `git_sha` varchar(255) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT 'git sha' ,
      `git_time` int(10) NOT NULL  DEFAULT 0 COMMENT 'git版本时间' ,
      `transaction_id` varchar(255) COLLATE utf8_general_ci NOT NULL  DEFAULT '' COMMENT '订单号' ,
      `data` text COLLATE utf8_general_ci NOT NULL  COMMENT '信息' ,
      `addtime` int(10) unsigned NOT NULL  DEFAULT 0 COMMENT '安装时间' ,
      `uptime` int(10) unsigned NOT NULL  DEFAULT 0 COMMENT '更新时间' ,
      `type` tinyint(1) unsigned NOT NULL  DEFAULT 0 COMMENT 'app:0 tpl:1' ,
      `status` tinyint(1) unsigned NOT NULL  DEFAULT 1 COMMENT '状态' ,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';
        ");
      $msg.='创建apps_store表<iCMS>';
    }
    $storeArray = configAdmincp::get('999999','store');
    foreach ($storeArray as $sid => $value) {
      $data = array(
        'sid'            =>$sid,
        'appid'          =>$value['appid'],
        'name'           =>$value['app'],
        'version'        =>$value['version'],
        'authkey'        =>$value['authkey'],
        'git_sha'        =>$value['git_sha'],
        'git_time'       =>$value['git_time'],
        'transaction_id' =>$value['transaction_id'],
        'addtime'        =>$value['addtime'],
        'uptime'         =>$value['uptime'],
        'type'           =>empty($value['appid'])?1:0,
        'status'         =>'1',
      );
      $id = iDB::value("SELECT `id` FROM `#iCMS@__apps_store` WHERE `name`='".$data['name']."'");
      if($id){
        iDB::update("apps_store",$data,array('id'=>$id));
      }else{
        iDB::insert("apps_store",$data);
      }
    }
    $msg.='更新apps_store数据<iCMS>';
    return $msg;
});

