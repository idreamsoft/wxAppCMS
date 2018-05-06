<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    $fields  = apps_db::fields('#iCMS@__tag');
    if(empty($fields['postype'])){
        iDB::query("
            ALTER TABLE `#iCMS@__tag`
                CHANGE `uid` `userid` int(10) unsigned   NOT NULL COMMENT '栏目',
                ADD COLUMN `title` varchar(255)  COLLATE utf8_general_ci NOT NULL COMMENT '标题',
                ADD COLUMN `editor` varchar(255)  COLLATE utf8_general_ci NOT NULL COMMENT '编辑或用户名',
                ADD COLUMN `clink` VARCHAR(255) DEFAULT '' NOT NULL,
                ADD COLUMN `hits` int(10) unsigned   NOT NULL COMMENT '总点击数' ,
                ADD COLUMN `hits_today` int(10) unsigned   NOT NULL COMMENT '当天点击数' ,
                ADD COLUMN `hits_yday` int(10) unsigned   NOT NULL COMMENT '昨天点击数' ,
                ADD COLUMN `hits_week` int(10) unsigned   NOT NULL COMMENT '周点击' ,
                ADD COLUMN `hits_month` int(10) unsigned   NOT NULL COMMENT '月点击' ,
                ADD COLUMN `favorite` int(10) unsigned   NOT NULL COMMENT '收藏数' ,
                ADD COLUMN `good` int(10) unsigned   NOT NULL COMMENT '顶' ,
                ADD COLUMN `bad` int(10) unsigned   NOT NULL COMMENT '踩' ,
                ADD COLUMN `creative` tinyint(1) unsigned   NOT NULL COMMENT '0:转载;1:原创' ,
                ADD COLUMN `postype` tinyint(1) unsigned   NOT NULL COMMENT '0:用户;1:管理员' ,
                ADD KEY `cid_hits`(`status`,`cid`,`hits`) ,
                ADD KEY `hits`(`status`,`hits`) ,
                ADD KEY `hits_month`(`status`,`hits_month`) ,
                ADD KEY `hits_week`(`status`,`hits_week`) ,
                ADD KEY `id`(`status`,`id`) ,
                ADD KEY `pubdate`(`status`,`pubdate`);
        ");
    }
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

