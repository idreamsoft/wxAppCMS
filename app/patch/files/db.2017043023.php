<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){

    if(iDB::check_table('tags')){
        iDB::query("RENAME TABLE `#iCMS@__tags` TO `#iCMS@__tag`; ");
    }
    if(iDB::check_table('tags_map')){
        iDB::query("RENAME TABLE `#iCMS@__tags_map` TO `#iCMS@__tag_map`;");
    }
    $msg.='更改标签表名<iCMS>';

    iDB::query("
        UPDATE `#iCMS@__apps`
        SET `table` = '{\"article\":[\"article\",\"id\",\"\",\"文章\"],\"article_data\":[\"article_data\",\"id\",\"aid\",\"正文\"],\"article_meta\":[\"article_meta\",\"id\",\"\",\"动态属性\"]}'
        WHERE `app` = 'article';
    ");
    iDB::query("
        UPDATE `#iCMS@__apps`
        SET `table` = '{\"category\":[\"category\",\"cid\",\"\",\"分类\"],\"category_map\":[\"category_map\",\"id\",\"node\",\"分类映射\"],\"category_meta\":[\"category_meta\",\"id\",\"\",\"动态属性\"]}'
        WHERE `app` = 'category';
    ");
    iDB::query("
        UPDATE `#iCMS@__apps`
        SET `table` = '{\"tag\":[\"tag\",\"id\",\"\",\"标签\"],\"tag_map\":[\"tag_map\",\"id\",\"node\",\"标签映射\"],\"tag_meta\":[\"tag_meta\",\"id\",\"\",\"动态属性\"]}'
        WHERE `app` = 'tag';
    ");
    apps_meta::$CREATE_TABLE = true;
    apps_meta::create('article_meta');
    apps_meta::create('category_meta');
    apps_meta::create('tag_meta');
    apps::cache();
    menu::cache();
    $msg.='升级表数据<iCMS>';
    return $msg;
});

