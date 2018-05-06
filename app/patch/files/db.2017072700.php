<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    $fields  = apps_db::fields('#iCMS@__tag');
    if(empty($fields['field'])){
        iDB::query("
            ALTER TABLE `#iCMS@__tag`
            ADD COLUMN `field` VARCHAR(255) DEFAULT '' NOT NULL AFTER `name`;
        ");
        iDB::query("
            UPDATE `#iCMS@__tag` SET `field` = 'tags' WHERE `field` = '';
        ");
    }
    $msg.='升级[tag]表结构<iCMS>';

    $menu = '[{"id":"tools","children":[{"id":"spider","sort":"-994","caption":"采集管理","href":"spider","icon":"magnet","children":[{"caption":"错误信息","href":"spider&do=error","icon":"info-circle"},{"caption":"-"},{"caption":"采集列表","href":"spider&do=manage","icon":"list-alt"},{"caption":"未发文章","href":"spider&do=inbox","icon":"inbox"},{"caption":"-"},{"caption":"采集方案","href":"spider&do=project","icon":"magnet"},{"caption":"添加方案","href":"spider&do=addproject","icon":"edit"},{"caption":"-"},{"caption":"采集规则","href":"spider&do=rule","icon":"magnet"},{"caption":"添加规则","href":"spider&do=addrule","icon":"edit"},{"caption":"-"},{"caption":"发布模块","href":"spider&do=post","icon":"magnet"},{"caption":"添加发布","href":"spider&do=addpost","icon":"edit"}]},{"caption":"-","sort":"-993"}]}]';

    $table ='{"spider_post":["spider_post","id","","发布"],"spider_project":["spider_project","id","","方案"],"spider_rule":["spider_rule","id","","规则"],"spider_url":["spider_url","id","","采集结果"],"spider_error":["spider_error","id","","错误记录"]}';

    iDB::query("
        UPDATE `#iCMS@__apps`
        SET `menu` = '".addslashes($menu)."',
        `table` = '".addslashes($table)."'
        WHERE `app` = 'spider';
    ");

    $msg.='升级[spider]表数据<iCMS>';

    menu::cache();
    return $msg;
});

