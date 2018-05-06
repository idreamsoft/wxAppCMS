<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    $fields  = iDB::col("SHOW COLUMNS FROM `#iCMS@__keywords`");
    if($fields){
        if(!array_search ('replace',$fields) && array_search ('url',$fields)){
            iDB::query("RENAME TABLE `#iCMS@__keywords` TO `#iCMS@__keywords_bak_".date("Ymd")."`");
            iDB::query("
                CREATE TABLE `#iCMS@__keywords` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `keyword` varchar(255) NOT NULL DEFAULT '',
                  `replace` varchar(255) NOT NULL DEFAULT '',
                  PRIMARY KEY (`id`,`keyword`),
                  UNIQUE KEY `keyword` (`keyword`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8
            ");
            $msg.='升级[内链]数据表<iCMS>';
        }
    }

    iDB::update('apps',array(
        'table' => addslashes('{"files":["files","id","","\u6587\u4ef6"],"files_map":["files_map","fileid","fileid","\u6587\u4ef6\u6620\u5c04"]}')
    ),array('id'=>'12'));

    $msg.='升级[文件系统]数据表信息<iCMS>';

    iDB::update('apps',array(
        'menu' => addslashes('[{"id":"tools","children":[{"id":"files","sort":"-998","caption":"\u6587\u4ef6\u7ba1\u7406","icon":"folder","children":[{"caption":"\u4e91\u5b58\u50a8\u914d\u7f6e","href":"files&do=cloud_config","icon":"cog"},{"caption":"-"},{"caption":"\u6587\u4ef6\u7ba1\u7406","href":"files","icon":"folder"},{"caption":"\u4e0a\u4f20\u6587\u4ef6","href":"files&do=multi&from=modal","icon":"upload","data-toggle":"modal","data-meta":{"width":"85%","height":"640px"}}]},{"caption":"-","sort":"-997"}]}]')
    ),array('id'=>'12'));

    $msg.='升级[文件系统]菜单信息<iCMS>';

    iDB::update('apps',array(
        'menu' => addslashes('[{"id":"system","children":[{"id":"apps","caption":"\u5e94\u7528\u7ba1\u7406","icon":"code","sort":"0","children":[{"caption":"\u5e94\u7528\u7ba1\u7406","href":"apps","icon":"code"},{"caption":"\u6dfb\u52a0\u5e94\u7528","href":"apps&do=add","icon":"pencil-square-o"},{"caption":"-"},{"caption":"\u94a9\u5b50\u7ba1\u7406","href":"apps&do=hooks","icon":"plug"},{"caption":"-"},{"caption":"\u5e94\u7528\u5e02\u573a","href":"apps&do=store","icon":"bank"},{"caption":"-"},{"caption":"\u6a21\u677f\u5e02\u573a","href":"apps&do=template","icon":"bank"}]}]}]')
    ),array('id'=>'17'));

    $msg.='升级[应用管理]菜单信息<iCMS>';

    return $msg;
});

