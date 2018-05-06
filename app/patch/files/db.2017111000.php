<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    $fields  = apps_db::fields('#iCMS@__user');
    if(empty($fields['favorite'])){
        iDB::query("
            ALTER TABLE `#iCMS@__user`
            CHANGE `share` `favorite` INT(10) UNSIGNED DEFAULT 0 NOT NULL COMMENT '收藏数';
        ");
    }
    $msg.='升级[user]表结构<iCMS>';

    return $msg;
});

