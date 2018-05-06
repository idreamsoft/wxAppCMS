<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class content {
    public static $app       = null;
    public static $table     = null;
    public static $primary   = null;
    public static $union_key = null;

    public static function count_sql($sql=''){
        return "SELECT count(*) FROM `".self::$table."` {$sql}";
    }
    public static function check($value,$id=0,$field='title'){
        $sql = "SELECT `".self::$primary."` FROM `".self::$table."` where `{$field}` = '$value'";
        $id && $sql.=" AND `".self::$primary."` !='$id'";
        return iDB::value($sql);
    }

    public static function value($field=null,$id=0){
        if(empty($id)){
            return;
        }
        $field===null && $field = self::$primary;
        return iDB::value("SELECT {$field} FROM `".self::$table."` WHERE `".self::$primary."`='$id';");
    }
    public static function row($id=0,$field='*',$sql=''){
        return iDB::row("SELECT {$field} FROM `".self::$table."` WHERE `".self::$primary."`='$id' {$sql} LIMIT 1;",ARRAY_A);
    }
    public static function data($id=0,$cdid=0,$userid=0){
        $userid && $sql = " AND `userid`='$userid'";
        $rs    = iDB::row("SELECT * FROM `".self::$table."` WHERE `".self::$primary."`='$id' {$sql} LIMIT 1;",ARRAY_A);
        if($rs){
            $id = $rs['id'];
            $data_table = apps_mod::data_table_name(self::$app);
            $cdsql = "SELECT * FROM `".$data_table."` WHERE `".self::$union_key."`='$id'";
            $cdid && $cdsql.= " AND `".apps_mod::DATA_PRIMARY_KEY."`='{$cdid}'";

            if($rs['chapter']){
                $cdrs  = iDB::all($cdsql,ARRAY_A);
            }else{
                $cdrs  = iDB::row($cdsql,ARRAY_A);
            }
        }
        return array($rs,$cdrs);
    }
    public static function body($id=0){
        $data_table = apps_mod::data_table_name(self::$app);
        $body = iDB::value("SELECT * FROM `".$data_table."` WHERE `".self::$union_key."`='$id'");
        return $body;
    }

    public static function batch($data,$ids){
        if(empty($ids)){
            return;
        }
        foreach ( array_keys($data) as $k ){
            $bits[] = "`$k` = '$data[$k]'";
        }
        iDB::query("UPDATE `".self::$table."` SET " . implode( ', ', $bits ) . " WHERE `".self::$primary."` IN ($ids)");
    }
    public static function insert($data){
        return iDB::insert(self::$app,$data);
    }
    public static function update($data,$where){
        return iDB::update(self::$app,$data,$where);
    }
// --------------------------------------------------
    public static function data_fields($update=false){
        $fields  = array('subtitle', 'body');
        $update OR $fields  = array_merge ($fields,array('aid'));
        return $fields;
    }
    public static function data_insert($data){
        $data_table = apps_mod::data_table_name(self::$app);
        return iDB::insert($data_table,$data);
    }
    public static function data_update($data,$where){
        $data_table = apps_mod::data_table_name(self::$app);
        return iDB::update($data_table,$data,$where);
    }

    public static function del($id){
        iDB::query("DELETE FROM `".self::$table."` WHERE `".self::$primary."`='$id'");
    }
    public static function del_cdata($id,$f=null){
        $data_table = apps_mod::data_table_name(self::$app);
        $f===null && $f = self::$union_key;
        iDB::query("DELETE FROM `".$data_table."` WHERE `$f`='$id'");
    }
}

