<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
defined('iPHP') OR exit('What are you doing?');

class forms{
    public static function short_app($app){
        if(strpos($app, 'forms_') !== false) {
            $app = substr($app,6);
        }
        return $app;
    }
    public static function base_fields_index(){
        return array(
            // 'index_id' =>'KEY `id` (`status`,`id`)',
        );
    }
    public static function base_fields_json(){
      return '{
        "id": "id=id&label=内容id&comment=主键%20自增ID&field=PRIMARY&name=id&default=&type=PRIMARY&len=10&class=span2"
      }';
        // "2": "UI:BR",
        // "title": "id=title&label=标题&field=VARCHAR&name=title&type=text&default=&len=255&class=span6&validate%5B%5D=empty",
        // "5": "UI:BR",
        // "pubdate": "id=pubdate&label=发布时间&field=INT&name=pubdate&default=&type=datetime&len=10&class=span3",
        // "postime": "id=postime&label=提交时间&field=INT&name=postime&default=&type=datetime:hidden&len=10&class=span3",
        // "8": "UI:BR",
        // "status": "id=status&label=状态&comment=0:草稿;1:正常;2:回收;3:审核;4:不合格&option=草稿=0;正常=1;回收=2;审核=3;不合格=4;&field=TINYINT&name=status&default=1&type=select&len=1&class=chosen-select span3"
    }
    public static function base_fields_array(){
      $sql = implode(",\n", self::base_fields_sql());
      preg_match_all("@`(.+)`\s(.+)\sDEFAULT\s'(.*?)'\sCOMMENT\s'(.+)'@", $sql, $matches);
      return $matches;
    }
    public static function base_fields_sql(){
        return array(
            // 'title'   =>"`title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '标题'",
            // 'pubdate' =>"`pubdate` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间'",
            // 'postime' =>"`postime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '提交时间'",
            // 'status'  =>"`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态 0:草稿;1:正常;2:回收;3:审核;4:不合格'",
        );
    }
    public static function get($vars=0,$field='id'){
        if(empty($vars)) return array();
        if($vars=='all'){
            $sql      = '1=1';
            $is_multi = true;
        }else{
            list($vars,$is_multi)  = iSQL::multi_var($vars);
            $sql  = iSQL::in($vars,$field,false,true);
        }
        $data = array();
        $rs   = iDB::all("SELECT * FROM `#iCMS@__forms` where {$sql}");
        if($rs){
            $_count = count($rs);
            for ($i=0; $i < $_count; $i++) {
                $data[$rs[$i][$field]]= apps::item($rs[$i]);
            }
            $is_multi OR $data = $data[$vars];
        }
        if(empty($data)){
            return;
        }
        return $data;
    }
    public static function delete($app){
        is_array($app) OR $app = self::get($app);
        if($app){
            //删除表
            self::drop_table($app['table']);
            //删除数据
            self::del_data($app['id']);
        }

    }

    public static function del_data($id){
        $id && iDB::query("DELETE FROM `#iCMS@__forms` WHERE `id` = '{$id}'; ");
    }
    public static function drop_table($table){
        if($table)foreach ((array)$table as $key => $value) {
            $value['table'] && iDB::query("DROP TABLE IF EXISTS `".$value['table']."`");
        }
    }
    public static function get_data($app,$id) {
        $data  = array();
        if(empty($id) ){
            return $data;
        }

        $table = $app['table'];
        foreach ($table as $key => $value) {
            $primary_key = $value['primary'];
            $value['union'] && $primary_key = $value['union'];
            $data+= (array)iDB::row("SELECT * FROM `{$value['table']}` WHERE `{$primary_key}`='$id' LIMIT 1;",ARRAY_A);
        }
        return $data;
    }
}
