<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

return patch::upgrade(function(){
    $variable = iDB::tables_list();

    foreach ($variable as $key => $value) {
        if(strpos($value['TABLE_NAME'],'_meta')!==false){
            $table  = str_replace(iDB::$config['PREFIX'],'',$value['TABLE_NAME']);
            apps_meta::config($table);
        }
    }
    $msg.='更新[动态属性]配置<iCMS>';

    return $msg;
});

