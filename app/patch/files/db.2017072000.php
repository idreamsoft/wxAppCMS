<?php
@set_time_limit(0);
defined('iPHP') OR require (dirname(__FILE__).'/../../../iCMS.php');

if (!function_exists('cnjson_encode')) {
    function cnjson_encode(){
        $json = json_encode($array);
        $json = preg_replace_callback('/\\\\u([0-9a-f]{4})/i','unicode_convert_encoding',$json);
        return $json;
    }
}
return patch::upgrade(function(){
    $variable = apps::get_array(array('status'=>'1'));
    $msg = '';
    foreach ($variable as $key => $value) {
        if($value['table']){
            $tableArray = array();
            foreach ($value['table'] as $k => $v) {
                $tableArray[$k]= array($v['name'],$v['primary'],$v['union'],$v['label']);
                if(strpos($v['name'], '_data')!==false && $v['primary']=='data_id'){
                    unset($tableArray[$k]);
                    $msg.='开始升级"'.$value['name'].'"自定义应用附加表['.$v['name'].']<iCMS>';
                    iDB::query("
                        ALTER TABLE {$v['table']}
                        CHANGE `data_id` `cdata_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键 自增ID';
                    ");
                    $msg.='更改字段名`data_id` => `cdata_id`<iCMS>';
                    $tbn = str_replace('_data', '_cdata', $v['table']);
                    iDB::query("RENAME TABLE {$v['table']} TO {$tbn};");
                    $msg.='更改表名'.$v['table'].' => '.$tbn.'<iCMS>';
                    $name = str_replace('_data', '_cdata', $v['name']);
                    $tableArray[$name]= array($name,'cdata_id',$v['union'],$v['label']);
                    $tbjson = cnjson_encode($tableArray);
                    iDB::update("apps",array('table'=>addslashes($tbjson)),array('id'=>$value['id']));
                    $msg.='更新应用表数据<iCMS>';
                }
            }
        }
    }
    $rs = iDB::all("SELECT * FROM `#iCMS@__forms`");
    if($rs){
        $_count = count($rs);
        for ($i=0; $i < $_count; $i++) {
            $value = apps::item($rs[$i]);
            if($value['table']){
                $tableArray = array();
                foreach ($value['table'] as $k => $v) {
                    $tableArray[$k]= array($v['name'],$v['primary'],$v['union'],$v['label']);
                    if(strpos($v['name'], '_data')!==false && $v['primary']=='data_id'){
                        unset($tableArray[$k]);
                        $msg.='开始升级"'.$value['name'].'"自定义表单附加表['.$v['name'].']<iCMS>';
                        iDB::query("
                            ALTER TABLE {$v['table']}
                            CHANGE `data_id` `cdata_id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '主键 自增ID';
                        ");
                        $msg.='更改字段名`data_id` => `cdata_id`<iCMS>';
                        $tbn = str_replace('_data', '_cdata', $v['table']);
                        iDB::query("RENAME TABLE {$v['table']} TO {$tbn};");
                        $msg.='更改表名'.$v['table'].' => '.$tbn.'<iCMS>';
                        $name = str_replace('_data', '_cdata', $v['name']);
                        $tableArray[$name]= array($name,'cdata_id',$v['union'],$v['label']);
                        $tbjson = cnjson_encode($tableArray);
                        iDB::update("forms",array('table'=>addslashes($tbjson)),array('id'=>$value['id']));
                        $msg.='更新表单表数据<iCMS>';
                    }
                }
            }
        }
    }

    return $msg;
});

