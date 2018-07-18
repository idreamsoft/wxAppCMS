/*
MySQL - 5.5.53 : Database - wxappcms
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `icms_access_log` */

CREATE TABLE `icms_access_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL DEFAULT '',
  `app` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `useragent` varchar(512) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `method` varchar(255) NOT NULL DEFAULT '',
  `referer` varchar(255) NOT NULL DEFAULT '',
  `addtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `app` (`app`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_apps` */

CREATE TABLE `icms_apps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用ID appid',
  `app` varchar(100) NOT NULL DEFAULT '' COMMENT '应用标识',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '应用名',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '应用标题',
  `apptype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型 0官方 1本地 2自定义',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '应用类型',
  `table` text NOT NULL COMMENT '应用表',
  `config` text NOT NULL COMMENT '应用配置',
  `fields` text NOT NULL COMMENT '应用自定义字段',
  `menu` text NOT NULL COMMENT '应用菜单',
  `router` text NOT NULL COMMENT '应用路由',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用状态',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`app`)
) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

/*Table structure for table `icms_apps_store` */

CREATE TABLE `icms_apps_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(10) NOT NULL DEFAULT '0',
  `appid` int(10) NOT NULL DEFAULT '0' COMMENT 'appid',
  `app` varchar(255) NOT NULL DEFAULT '' COMMENT 'app',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `version` varchar(255) NOT NULL DEFAULT '' COMMENT '版本',
  `authkey` varchar(255) NOT NULL DEFAULT '',
  `git_sha` varchar(255) NOT NULL DEFAULT '' COMMENT 'git sha',
  `git_time` int(10) NOT NULL DEFAULT '0' COMMENT 'git版本时间',
  `transaction_id` varchar(255) NOT NULL DEFAULT '' COMMENT '订单号',
  `data` text NOT NULL COMMENT '信息',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '安装时间',
  `uptime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'app:0 tpl:1',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_article` */

CREATE TABLE `icms_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目id',
  `scid` varchar(255) NOT NULL DEFAULT '' COMMENT '副栏目',
  `ucid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户分类',
  `pid` varchar(255) NOT NULL DEFAULT '' COMMENT '属性',
  `sortnum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `stitle` varchar(255) NOT NULL DEFAULT '' COMMENT '短标题',
  `clink` varchar(255) NOT NULL DEFAULT '' COMMENT '自定义链接',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '外部链接',
  `source` varchar(255) NOT NULL DEFAULT '' COMMENT '出处',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '作者',
  `editor` varchar(255) NOT NULL DEFAULT '' COMMENT '编辑',
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `haspic` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否有缩略图',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `mpic` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图2',
  `spic` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图3',
  `picdata` varchar(255) NOT NULL DEFAULT '' COMMENT '图片数据',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键词',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `description` varchar(5120) NOT NULL DEFAULT '' COMMENT '摘要',
  `related` text NOT NULL COMMENT '相关',
  `pubdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `postime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提交时间',
  `tpl` varchar(255) NOT NULL DEFAULT '' COMMENT '模板',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总点击数',
  `hits_today` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天点击数',
  `hits_yday` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '昨天点击数',
  `hits_week` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周点击',
  `hits_month` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '月点击',
  `favorite` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数',
  `comments` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `good` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '顶',
  `bad` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '踩',
  `creative` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '文章类型 1原创 0转载',
  `chapter` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '章节',
  `weight` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `markdown` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'markdown标识',
  `mobile` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1手机发布 0 pc',
  `postype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型 0用户 1管理员',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '[[0:草稿],[1:正常],[2:回收],[3:审核],[4:不合格]]',
  PRIMARY KEY (`id`),
  KEY `id` (`status`,`id`),
  KEY `hits` (`status`,`hits`),
  KEY `pubdate` (`status`,`pubdate`),
  KEY `hits_week` (`status`,`hits_week`),
  KEY `hits_month` (`status`,`hits_month`),
  KEY `cid_hits` (`status`,`cid`,`hits`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_article_data` */

CREATE TABLE `icms_article_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(10) unsigned NOT NULL DEFAULT '0',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_article_meta` */

CREATE TABLE `icms_article_meta` (
  `id` int(10) unsigned NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_category` */

CREATE TABLE `icms_category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rootid` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` varchar(255) NOT NULL DEFAULT '',
  `appid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `creator` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `subname` varchar(255) NOT NULL DEFAULT '',
  `sortnum` int(10) unsigned NOT NULL DEFAULT '0',
  `password` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `dir` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `mpic` varchar(255) NOT NULL DEFAULT '',
  `spic` varchar(255) NOT NULL DEFAULT '',
  `mode` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `domain` varchar(255) NOT NULL DEFAULT '',
  `htmlext` varchar(10) NOT NULL DEFAULT '',
  `rule` text NOT NULL,
  `template` text NOT NULL,
  `config` text NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`cid`),
  KEY `dir` (`dir`),
  KEY `s_o_cid` (`status`,`sortnum`,`cid`),
  KEY `t_o_cid` (`appid`,`sortnum`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_category_map` */

CREATE TABLE `icms_category_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'cid',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `field` varchar(255) NOT NULL DEFAULT '' COMMENT '字段',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  PRIMARY KEY (`id`),
  KEY `idx` (`appid`,`node`,`iid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_category_meta` */

CREATE TABLE `icms_category_meta` (
  `id` int(10) unsigned NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_comment` */

CREATE TABLE `icms_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appid` int(10) unsigned NOT NULL DEFAULT '0',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评论内容分类',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评论内容ID',
  `suid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评论内容用户ID',
  `title` varchar(255) NOT NULL DEFAULT '',
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论者ID',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '评论者',
  `content` text NOT NULL,
  `reply_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复 评论ID',
  `reply_uid` int(11) unsigned NOT NULL DEFAULT '0',
  `reply_name` varchar(255) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `up` int(10) unsigned NOT NULL DEFAULT '0',
  `down` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `quote` int(10) unsigned NOT NULL DEFAULT '0',
  `floor` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_iid` (`appid`,`status`,`iid`,`id`),
  KEY `idx_uid` (`status`,`userid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_config` */

CREATE TABLE `icms_config` (
  `appid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`appid`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_favorite` */

CREATE TABLE `icms_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `follow` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关注数',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数',
  `mode` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 公开 0私密',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_favorite_data` */

CREATE TABLE `icms_favorite_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏者ID',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏夹ID',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '内容URL',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '内容标题',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx` (`uid`,`fid`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_favorite_follow` */

CREATE TABLE `icms_favorite_follow` (
  `fid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '收藏夹ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '关注者',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '收藏夹标题',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关注者ID',
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_files` */

CREATE TABLE `icms_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `ofilename` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `intro` varchar(255) NOT NULL DEFAULT '',
  `ext` varchar(10) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`),
  KEY `path` (`path`),
  KEY `ofilename` (`ofilename`),
  KEY `fn_userid` (`filename`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_files_map` */

CREATE TABLE `icms_files_map` (
  `fileid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `appid` int(10) unsigned NOT NULL,
  `indexid` int(10) unsigned NOT NULL,
  `addtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fileid`,`appid`,`indexid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_forms` */

CREATE TABLE `icms_forms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表单ID',
  `app` varchar(255) NOT NULL DEFAULT '' COMMENT '表单标识',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '表单名',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '表单标题',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '表单图片',
  `description` varchar(5120) NOT NULL DEFAULT '' COMMENT '表单简介',
  `tpl` varchar(255) NOT NULL DEFAULT '' COMMENT '表单模板',
  `table` text NOT NULL COMMENT '表单表',
  `config` text NOT NULL COMMENT '表单配置',
  `fields` text NOT NULL COMMENT '表单字段',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '表单类型',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '表单状态',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`app`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_group` */

CREATE TABLE `icms_group` (
  `gid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `sortnum` int(10) unsigned NOT NULL DEFAULT '0',
  `config` mediumtext NOT NULL,
  `type` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_keywords` */

CREATE TABLE `icms_keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `replace` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`keyword`),
  UNIQUE KEY `keyword` (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_links` */

CREATE TABLE `icms_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  `sortnum` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `s_o_id` (`cid`,`sortnum`,`id`),
  KEY `ordernum` (`sortnum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_members` */

CREATE TABLE `icms_members` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `realname` varchar(255) NOT NULL DEFAULT '',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `info` mediumtext NOT NULL,
  `config` mediumtext NOT NULL,
  `regtime` int(10) unsigned DEFAULT '0',
  `lastip` varchar(15) NOT NULL DEFAULT '',
  `lastlogintime` int(10) unsigned NOT NULL DEFAULT '0',
  `logintimes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `post` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`),
  KEY `groupid` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_message` */

CREATE TABLE `icms_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送者ID',
  `friend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收者ID',
  `send_uid` int(10) DEFAULT '0' COMMENT '发送者ID',
  `send_name` varchar(255) NOT NULL DEFAULT '' COMMENT '发送者名称',
  `receiv_uid` int(10) DEFAULT '0' COMMENT '接收者ID',
  `receiv_name` varchar(255) NOT NULL DEFAULT '' COMMENT '接收者名称',
  `content` text NOT NULL COMMENT '内容',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '信息类型',
  `sendtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `readtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '读取时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '信息状态 参考程序注释',
  PRIMARY KEY (`id`),
  KEY `idx` (`status`,`userid`,`friend`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_prop` */

CREATE TABLE `icms_prop` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rootid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL DEFAULT '0',
  `field` varchar(255) NOT NULL DEFAULT '',
  `appid` int(10) unsigned NOT NULL DEFAULT '0',
  `app` varchar(255) NOT NULL DEFAULT '',
  `sortnum` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `val` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`pid`),
  KEY `field` (`field`),
  KEY `cid` (`cid`),
  KEY `type` (`app`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_prop_map` */

CREATE TABLE `icms_prop_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node` varchar(255) NOT NULL DEFAULT '' COMMENT 'pid',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `field` varchar(255) NOT NULL DEFAULT '' COMMENT '字段',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  PRIMARY KEY (`id`),
  KEY `idx` (`appid`,`node`,`iid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_search_log` */

CREATE TABLE `icms_search_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `search` varchar(200) NOT NULL DEFAULT '',
  `times` int(10) unsigned NOT NULL DEFAULT '0',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `search_times` (`search`,`times`),
  KEY `search_id` (`search`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_sph_counter` */

CREATE TABLE `icms_sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_spider_error` */

CREATE TABLE `icms_spider_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(1024) NOT NULL DEFAULT '',
  `msg` varchar(1024) NOT NULL DEFAULT '',
  `work` varchar(255) NOT NULL DEFAULT '',
  `date` varchar(255) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_spider_post` */

CREATE TABLE `icms_spider_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `app` varchar(255) NOT NULL DEFAULT '',
  `post` text NOT NULL,
  `fun` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_spider_project` */

CREATE TABLE `icms_spider_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `urls` text NOT NULL,
  `list_url` varchar(255) NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `poid` int(10) unsigned NOT NULL,
  `sleep` int(10) unsigned NOT NULL,
  `checker` tinyint(1) unsigned NOT NULL,
  `self` tinyint(1) unsigned NOT NULL,
  `auto` tinyint(1) unsigned NOT NULL,
  `lastupdate` int(10) unsigned NOT NULL,
  `psleep` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_spider_rule` */

CREATE TABLE `icms_spider_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `rule` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_spider_url` */

CREATE TABLE `icms_spider_url` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appid` int(10) NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  `indexid` int(10) NOT NULL,
  `hash` char(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `publish` tinyint(1) NOT NULL,
  `addtime` int(10) NOT NULL,
  `pubdate` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `title` (`title`),
  KEY `url` (`url`(255))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_tag` */

CREATE TABLE `icms_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL DEFAULT '0',
  `tcid` varchar(255) NOT NULL DEFAULT '',
  `pid` varchar(255) NOT NULL DEFAULT '',
  `tkey` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `name` varchar(255) NOT NULL DEFAULT '',
  `field` varchar(255) NOT NULL DEFAULT '',
  `rootid` int(10) unsigned NOT NULL DEFAULT '0',
  `seotitle` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `related` varchar(1024) NOT NULL DEFAULT '',
  `editor` varchar(255) NOT NULL COMMENT '编辑或用户名',
  `userid` int(10) unsigned NOT NULL COMMENT '栏目',
  `haspic` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pic` varchar(255) NOT NULL DEFAULT '',
  `bpic` varchar(255) NOT NULL DEFAULT '',
  `mpic` varchar(255) NOT NULL DEFAULT '',
  `spic` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `tpl` varchar(255) NOT NULL DEFAULT '',
  `weight` int(10) NOT NULL DEFAULT '0',
  `clink` varchar(255) NOT NULL DEFAULT '',
  `sortnum` int(10) unsigned NOT NULL DEFAULT '0',
  `pubdate` int(10) unsigned NOT NULL DEFAULT '0',
  `postime` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL COMMENT '总点击数',
  `hits_today` int(10) unsigned NOT NULL COMMENT '当天点击数',
  `hits_yday` int(10) unsigned NOT NULL COMMENT '昨天点击数',
  `hits_week` int(10) unsigned NOT NULL COMMENT '周点击',
  `hits_month` int(10) unsigned NOT NULL COMMENT '月点击',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `favorite` int(10) unsigned NOT NULL COMMENT '收藏数',
  `good` int(10) unsigned NOT NULL COMMENT '顶',
  `bad` int(10) unsigned NOT NULL COMMENT '踩',
  `creative` tinyint(1) unsigned NOT NULL COMMENT '0:转载;1:原创',
  `postype` tinyint(1) unsigned NOT NULL COMMENT '0:用户;1:管理员',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`,`id`),
  KEY `idx_order` (`status`,`sortnum`),
  KEY `name` (`name`),
  KEY `tkey` (`tkey`),
  KEY `idx_count` (`status`,`count`),
  KEY `pid_count` (`pid`,`count`),
  KEY `cid_count` (`cid`,`count`),
  KEY `pid_id` (`pid`,`id`),
  KEY `cid_id` (`cid`,`id`),
  KEY `rootid` (`rootid`),
  KEY `cid_hits` (`status`,`cid`,`hits`),
  KEY `hits` (`status`,`hits`),
  KEY `hits_month` (`status`,`hits_month`),
  KEY `hits_week` (`status`,`hits_week`),
  KEY `id` (`status`,`id`),
  KEY `pubdate` (`status`,`pubdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_tag_map` */

CREATE TABLE `icms_tag_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `field` varchar(255) NOT NULL DEFAULT '' COMMENT '字段',
  `appid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  PRIMARY KEY (`id`),
  KEY `tid_index` (`appid`,`node`,`field`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_tag_meta` */

CREATE TABLE `icms_tag_meta` (
  `id` int(10) unsigned NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_user` */

CREATE TABLE `icms_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `pid` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '属性ID',
  `username` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户名/email',
  `nickname` varchar(128) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` char(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '密码',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `fans` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '粉丝数',
  `follow` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关注数',
  `comments` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `article` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章数',
  `favorite` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数',
  `credit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `regip` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '注册IP',
  `regdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册日期',
  `lastloginip` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '最后登陆IP',
  `lastlogintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总点击数',
  `hits_today` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天点击数',
  `hits_yday` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '昨天点击数',
  `hits_week` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周点击',
  `hits_month` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '月点击',
  `setting` varchar(1024) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '其它设置',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户类型',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '账号状态',
  PRIMARY KEY (`uid`),
  KEY `nickname` (`nickname`),
  KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `icms_user_category` */

CREATE TABLE `icms_user_category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `mode` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 公开 2私密',
  `appid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `uid` (`uid`,`appid`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_user_data` */

CREATE TABLE `icms_user_data` (
  `uid` int(11) unsigned NOT NULL,
  `realname` varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '联系电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '街道地址',
  `province` varchar(255) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `year` varchar(255) NOT NULL DEFAULT '' COMMENT '生日-年',
  `month` varchar(255) NOT NULL DEFAULT '' COMMENT '生日-月',
  `day` varchar(255) NOT NULL DEFAULT '' COMMENT '生日-日',
  `constellation` varchar(255) NOT NULL DEFAULT '' COMMENT '星座',
  `profession` varchar(255) NOT NULL DEFAULT '' COMMENT '职业',
  `personstyle` varchar(255) NOT NULL DEFAULT '' COMMENT '个人标签',
  `slogan` varchar(512) NOT NULL DEFAULT '' COMMENT '自我介绍',
  `unickEdit` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '昵称修改次数',
  `meta` text NOT NULL COMMENT '其它数据',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_user_follow` */

CREATE TABLE `icms_user_follow` (
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关注者ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '关注者',
  `fuid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '被关注者ID',
  `fname` varchar(255) NOT NULL DEFAULT '' COMMENT '被关注者',
  KEY `uid` (`uid`,`fuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_user_openid` */

CREATE TABLE `icms_user_openid` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `openid` varchar(128) NOT NULL DEFAULT '',
  `platform` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:wx,2:qq,3:wb,4:tb',
  `appid` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_upa` (`uid`,`platform`,`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_user_report` */

CREATE TABLE `icms_user_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `appid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '应用ID',
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '举报者',
  `iid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被举报者',
  `reason` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `content` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_weixin` */

CREATE TABLE `icms_weixin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
  `type` tinyint(1) unsigned NOT NULL COMMENT '类型',
  `appid` varchar(255) NOT NULL DEFAULT '' COMMENT 'appID',
  `appsecret` varchar(255) NOT NULL DEFAULT '' COMMENT 'appsecret',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `token` varchar(255) DEFAULT '' COMMENT '令牌',
  `AESKey` varchar(255) DEFAULT '' COMMENT '密钥',
  `account` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序号',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '小程序简介',
  `qrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码',
  `menu` text NOT NULL COMMENT '菜单',
  `config` text NOT NULL COMMENT '其它配置',
  `payment` text NOT NULL COMMENT '支付配置',
  PRIMARY KEY (`id`),
  KEY `idx_appid` (`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_weixin_api_log` */

CREATE TABLE `icms_weixin_api_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `appid` varchar(255) NOT NULL DEFAULT '',
  `ToUserName` varchar(255) NOT NULL DEFAULT '',
  `FromUserName` varchar(255) NOT NULL DEFAULT '',
  `CreateTime` int(11) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `dayline` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_weixin_event` */

CREATE TABLE `icms_weixin_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '属性',
  `appid` varchar(128) NOT NULL DEFAULT '' COMMENT '公众号APPID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '事件名称',
  `eventype` varchar(255) NOT NULL DEFAULT '' COMMENT '事件类型',
  `eventkey` varchar(255) NOT NULL DEFAULT '' COMMENT '事件KEY值/关键词',
  `msgtype` varchar(255) NOT NULL DEFAULT '' COMMENT '回复类型',
  `operator` varchar(10) NOT NULL DEFAULT '' COMMENT '匹配模式',
  `msg` mediumtext NOT NULL COMMENT '消息内容包含格式',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `eventkey` (`eventkey`),
  KEY `idx_appid` (`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_wxapp` */

CREATE TABLE `icms_wxapp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
  `appid` varchar(128) NOT NULL DEFAULT '' COMMENT 'appID',
  `appsecret` varchar(128) NOT NULL DEFAULT '' COMMENT 'appsecret',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '名称',
  `app` varchar(128) DEFAULT NULL,
  `account` varchar(128) NOT NULL DEFAULT '' COMMENT '小程序号',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '小程序简介',
  `qrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '接口地址',
  `version` varchar(32) NOT NULL DEFAULT '' COMMENT '接口版本号',
  `tpl` varchar(128) NOT NULL DEFAULT '' COMMENT '接口模板',
  `index` varchar(128) NOT NULL DEFAULT '' COMMENT '接口首页模板',
  `config` text NOT NULL COMMENT '配置',
  `payment` text NOT NULL COMMENT '支付配置',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `icms_wxapp_formids` */

CREATE TABLE `icms_wxapp_formids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `appid` varchar(128) NOT NULL DEFAULT '' COMMENT '小程序appid',
  `openid` varchar(128) NOT NULL DEFAULT '' COMMENT '用户openid',
  `formid` varchar(128) NOT NULL DEFAULT '' COMMENT 'formid',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  `send_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`,`status`),
  KEY `openid` (`openid`,`status`),
  KEY `appid` (`appid`,`formid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
