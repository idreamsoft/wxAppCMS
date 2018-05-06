<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    iDB::query("
UPDATE `#iCMS@__apps` SET
    `table` = '{\"weixin\":[\"weixin\",\"id\",\"\",\"公众平台\"],\"weixin_api_log\":[\"weixin_api_log\",\"appid\",\"\",\"记录\"],\"weixin_event\":[\"weixin_event\",\"appid\",\"\",\"事件\"]}',
    `menu` = '[{\"id\":\"weixin\",\"sort\":\"3\",\"caption\":\"微信\",\"icon\":\"weixin\",\"children\":[{\"caption\":\"分类管理\",\"href\":\"weixin_category\",\"icon\":\"sitemap\"},{\"caption\":\"添加分类\",\"href\":\"weixin_category&do=add\",\"icon\":\"edit\"},{\"caption\":\"-\"},{\"caption\":\"公众号管理\",\"href\":\"weixin&do=manage\",\"icon\":\"list-alt\"},{\"caption\":\"添加公众号\",\"href\":\"weixin&do=add\",\"icon\":\"edit\"},{\"caption\":\"-\"},{\"caption\":\"小程序配置\",\"href\":\"weixin&do=config\",\"icon\":\"edit\"},{\"caption\":\"-\"},{\"caption\":\"自定义菜单\",\"href\":\"weixin&do=menu\",\"icon\":\"bars\"},{\"caption\":\"-\"},{\"caption\":\"事件管理\",\"href\":\"weixin&do=event\",\"icon\":\"cubes\"},{\"caption\":\"添加事件\",\"href\":\"weixin&do=event_add\",\"icon\":\"plus\"}]}]'
    WHERE `app` = 'weixin';
    ");
    $msg.='更新微信应用数据<iCMS>';

    if(!iDB::check_table('weixin')){
        iDB::query("
CREATE TABLE `#iCMS@__weixin`(
  `id` int(10) unsigned NOT NULL  auto_increment ,
  `cid` int(10) unsigned NOT NULL  DEFAULT 0 COMMENT '分类' ,
  `type` tinyint(1) unsigned NOT NULL  COMMENT '类型' ,
  `appid` varchar(255) NOT NULL  DEFAULT '' COMMENT 'appID' ,
  `appsecret` varchar(255) NOT NULL  DEFAULT '' COMMENT 'appsecret' ,
  `name` varchar(255) NOT NULL  DEFAULT '' COMMENT '名称' ,
  `token` varchar(255) NULL  DEFAULT '' COMMENT '令牌' ,
  `AESKey` varchar(255) NULL  DEFAULT '' COMMENT '密钥' ,
  `account` varchar(255) NOT NULL  DEFAULT '' COMMENT '小程序号' ,
  `description` varchar(500) NOT NULL  DEFAULT '' COMMENT '小程序简介' ,
  `qrcode` varchar(255) NOT NULL  DEFAULT '' COMMENT '二维码' ,
  `menu` text NOT NULL  COMMENT '菜单' ,
  `config` text NOT NULL  COMMENT '其它配置' ,
  `payment` text NOT NULL  COMMENT '支付配置' ,
  PRIMARY KEY (`id`) ,
  KEY `idx_appid`(`appid`)
) ENGINE=MyISAM DEFAULT CHARSET='".iPHP_DB_CHARSET."';
        ");
      $msg.='创建weixin表<iCMS>';
    }
    $fields  = apps_db::fields('#iCMS@__weixin_api_log');
    if(empty($fields['appid'])){
        iDB::query("
ALTER TABLE `#iCMS@__weixin_api_log`
    ADD COLUMN `appid` varchar(255)  NOT NULL DEFAULT '' after `id` ,
    CHANGE `ToUserName` `ToUserName` varchar(255)  NOT NULL DEFAULT '' after `appid` ;
        ");
        $msg.='更新微信API记录表<iCMS>';
    }
    $fields  = apps_db::fields('#iCMS@__weixin_event');
    if(empty($fields['appid'])){
        iDB::query("
ALTER TABLE `#iCMS@__weixin_event`
    CHANGE `pid` `pid` int(10) unsigned   NOT NULL DEFAULT 0 COMMENT '属性' after `id` ,
    ADD COLUMN `appid` varchar(128)  NOT NULL DEFAULT '' COMMENT '公众号APPID' after `pid` ,
    CHANGE `name` `name` varchar(255)  NOT NULL DEFAULT '' COMMENT '事件名称' after `appid` ,
    ADD KEY `idx_appid`(`appid`);
        ");
        $msg.='更新微信事件表<iCMS>';
    }
    menu::cache();
    return $msg;
});

