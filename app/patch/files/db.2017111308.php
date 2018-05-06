<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    $fields  = apps_db::fields('#iCMS@__tag');
    if(empty($fields['related'])){
        iDB::query("
            ALTER TABLE `#iCMS@__tag`
            ADD COLUMN `related` VARCHAR(1024) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL;
        ");
    }
    iDB::query("
        ALTER TABLE `#iCMS@__tag`
          CHANGE `tpl` `tpl` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL  AFTER `url`,
          CHANGE `clink` `clink` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL  AFTER `tpl`,
          CHANGE `title` `title` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题'  AFTER `tkey`,
          CHANGE `editor` `editor` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '编辑或用户名'  AFTER `description`,
          CHANGE `userid` `userid` INT(10) UNSIGNED NOT NULL COMMENT '栏目'  AFTER `editor`,
          CHANGE `count` `count` INT(10) UNSIGNED DEFAULT 0 NOT NULL  AFTER `hits_month`,
          CHANGE `comments` `comments` INT(10) UNSIGNED DEFAULT 0 NOT NULL  AFTER `count`,
          CHANGE `rootid` `rootid` INT(10) UNSIGNED DEFAULT 0 NOT NULL  AFTER `field`,
          CHANGE `related` `related` VARCHAR(1024) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL  AFTER `description`,
          CHANGE `weight` `weight` INT(10) DEFAULT 0 NOT NULL  AFTER `tpl`,
          CHANGE `status` `status` TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL  AFTER `postype`;
    ");
    $msg.='升级[tag]表结构<iCMS>';

    return $msg;
});

