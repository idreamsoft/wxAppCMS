<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    $fields  = apps_db::fields('#iCMS@__apps');
    if(empty($fields['router'])){
        iDB::query("
ALTER TABLE `#iCMS@__apps`
ADD COLUMN `router` TEXT NOT NULL COMMENT '应用路由' AFTER `menu` ,
CHANGE `addtime` `addtime` INT(10) UNSIGNED   NOT NULL DEFAULT 0 COMMENT '添加时间' AFTER `router` ;
        ");
    $msg.='升级[apps]表结构<iCMS>';
    }
    iDB::query("
UPDATE `#iCMS@__apps` SET `router` = '{\"favorite\":[\"\\/favorite\",\"api.php?app=favorite\"],\"favorite:id\":[\"\\/favorite\\/{id}\\/\",\"api.php?app=favorite&id={id}\"]}'
WHERE `app` = 'favorite';
    ");
    $msg.='增加favorite应用路由配置<iCMS>';
    iDB::query("
UPDATE `#iCMS@__apps` SET `router` = '{\"user\":[\"\\/user\",\"api.php?app=user\"],\"user:home\":[\"\\/user\\/home\",\"api.php?app=user&do=home\"],\"user:publish\":[\"\\/user\\/publish\",\"api.php?app=user&do=manage&pg=publish\"],\"user:article\":[\"\\/user\\/article\",\"api.php?app=user&do=manage&pg=article\"],\"user:category\":[\"\\/user\\/category\",\"api.php?app=user&do=manage&pg=category\"],\"user:comment\":[\"\\/user\\/comment\",\"api.php?app=user&do=manage&pg=comment\"],\"user:inbox\":[\"\\/user\\/inbox\",\"api.php?app=user&do=manage&pg=inbox\"],\"user:inbox:uid\":[\"\\/user\\/inbox\\/{uid}\",\"api.php?app=user&do=manage&pg=inbox&user={uid}\"],\"user:manage\":[\"\\/user\\/manage\",\"api.php?app=user&do=manage\"],\"user:manage:favorite\":[\"\\/user\\/manage\\/favorite\",\"api.php?app=user&do=manage&pg=favorite\"],\"user:manage:fans\":[\"\\/user\\/manage\\/fans\",\"api.php?app=user&do=manage&pg=fans\"],\"user:manage:follow\":[\"\\/user\\/manage\\/follow\",\"api.php?app=user&do=manage&pg=follow\"],\"user:profile\":[\"\\/user\\/profile\",\"api.php?app=user&do=profile\"],\"user:profile:base\":[\"\\/user\\/profile\\/base\",\"api.php?app=user&do=profile&pg=base\"],\"user:profile:avatar\":[\"\\/user\\/profile\\/avatar\",\"api.php?app=user&do=profile&pg=avatar\"],\"user:profile:setpassword\":[\"\\/user\\/profile\\/setpassword\",\"api.php?app=user&do=profile&pg=setpassword\"],\"user:profile:bind\":[\"\\/user\\/profile\\/bind\",\"api.php?app=user&do=profile&pg=bind\"],\"user:profile:custom\":[\"\\/user\\/profile\\/custom\",\"api.php?app=user&do=profile&pg=custom\"],\"user:register\":[\"\\/user\\/register\",\"api.php?app=user&do=register\"],\"user:logout\":[\"\\/user\\/logout\",\"api.php?app=user&do=logout\"],\"user:login\":[\"\\/user\\/login\",\"api.php?app=user&do=login\"],\"user:login:qq\":[\"\\/user\\/login\\/qq\",\"api.php?app=user&do=login&sign=qq\"],\"user:login:wb\":[\"\\/user\\/login\\/wb\",\"api.php?app=user&do=login&sign=wb\"],\"user:login:wx\":[\"\\/user\\/login\\/wx\",\"api.php?app=user&do=login&sign=wx\"],\"user:findpwd\":[\"\\/user\\/findpwd\",\"api.php?app=user&do=findpwd\"],\"uid:home\":[\"\\/{uid}\\/\",\"api.php?app=user&do=home&uid={uid}\"],\"uid:comment\":[\"\\/{uid}\\/comment\\/\",\"api.php?app=user&do=comment&uid={uid}\"],\"uid:share\":[\"\\/{uid}\\/share\\/\",\"api.php?app=user&do=share&uid={uid}\"],\"uid:favorite\":[\"\\/{uid}\\/favorite\\/\",\"api.php?app=user&do=favorite&uid={uid}\"],\"uid:fans\":[\"\\/{uid}\\/fans\\/\",\"api.php?app=user&do=fans&uid={uid}\"],\"uid:follower\":[\"\\/{uid}\\/follower\\/\",\"api.php?app=user&do=follower&uid={uid}\"],\"uid:cid\":[\"\\/{uid}\\/{cid}\\/\",\"api.php?app=user&do=home&uid={uid}&cid={cid}\"],\"uid:favorite:id\":[\"\\/{uid}\\/favorite\\/{id}\\/\",\"api.php?app=user&do=favorite&uid={uid}&id={id}\"]}'
WHERE `app` = 'user';
    ");
    $msg.='增加user应用路由配置<iCMS>';
    iDB::query("
UPDATE `#iCMS@__apps` SET `router` = '{\"search\":[\"\\/search\",\"api.php?app=search\"]}'
WHERE `app` = 'search';
    ");
    $msg.='增加search应用路由配置<iCMS>';
    iDB::query("
UPDATE `#iCMS@__apps` SET `router` = '{\"forms\":[\"\\/forms\",\"api.php?app=forms\"],\"forms:save\":[\"\\/forms\\/save\",\"api.php?app=forms&do=save\"],\"forms:id\":[\"\\/forms\\/{id}\\/\",\"api.php?app=forms&id={id}\"]}'
WHERE `app` = 'forms';
    ");
    $msg.='增加forms应用路由配置<iCMS>';
    iDB::query("
UPDATE `#iCMS@__apps` SET `router` = '{\"public:seccode\":[\"\\/public\\/seccode\",\"api.php?app=public&do=seccode\"],\"public:agreement\":[\"\\/public\\/agreement\",\"api.php?app=public&do=agreement\"]}'
WHERE `app` = 'public';
    ");
    $msg.='增加public应用路由配置<iCMS>';

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

    iDB::query("
UPDATE `#iCMS@__apps` SET `table` = '{\"apps\":[\"apps\",\"id\",\"\",\"应用\"],\"apps_store\":[\"apps_store\",\"id\",\"\",\"应用市场\"]}'
WHERE `app` = 'apps';
    ");

    iDB::query("
UPDATE `#iCMS@__apps` SET `menu` = '[{\"id\":\"system\",\"children\":[{\"id\":\"apps\",\"caption\":\"应用管理\",\"icon\":\"code\",\"sort\":\"0\",\"children\":[{\"caption\":\"应用管理\",\"href\":\"apps\",\"icon\":\"code\"},{\"caption\":\"添加应用\",\"href\":\"apps&do=add\",\"icon\":\"pencil-square-o\"},{\"caption\":\"-\"},{\"caption\":\"钩子管理\",\"href\":\"apps&do=hooks\",\"icon\":\"plug\"},{\"caption\":\"-\"},{\"caption\":\"应用市场\",\"href\":\"apps_store&do=store\",\"icon\":\"bank\"},{\"caption\":\"-\"},{\"caption\":\"模板市场\",\"href\":\"apps_store&do=template\",\"icon\":\"bank\"}]}]}]'
WHERE `app` = 'apps';
    ");
    menu::cache();

    return $msg;
});

