<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    iDB::query("
      UPDATE `#iCMS@__apps`
      SET `config`=REPLACE(`config`,'\"menu\":\"main\"','\"menu\":\"default\"')
    ");
    $msg.='升级[apps]数据<iCMS>';
    $msg.='<hr/>';
    iDB::query("
        UPDATE `#iCMS@__keywords` SET `replace`=REPLACE(`replace`,'&quot;/&gt;','&quot;&gt;')
    ");
    iDB::query("
        UPDATE `#iCMS@__keywords` SET `replace`=REPLACE(`replace`,'\"/>','\">')
    ");
    iDB::query("
        UPDATE `#iCMS@__keywords` SET `replace`=REPLACE(`replace`,'/>','>')
    ");
    $msg.='更新[keywords]数据<iCMS>';
    $msg.='<hr/>';
    $config = addslashes('{"info":"自定义表单管理\/接口","template":["iCMS:forms:create","iCMS:forms:list","$forms"],"version":"v7.0","menu":"default"}');
    iDB::query("
        UPDATE `#iCMS@__apps` SET `config`='".$config."' WHERE `app`='forms'
    ");
    $msg.='更新[自定义表单]菜单<iCMS>';
    $msg.='<hr/>';

    return $msg;
});

