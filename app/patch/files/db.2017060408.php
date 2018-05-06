<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    $fields  = apps_db::fields('#iCMS@__spider_url');
    if(empty($fields['appid'])){
        iDB::query("
            ALTER TABLE `#iCMS@__spider_url`
            ADD COLUMN `appid` INT(10) NOT NULL AFTER `id`;
        ");
    }
    $msg.='升级[spider_url]表<iCMS>';
    return $msg;
});

