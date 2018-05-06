<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class article {
    public static function fields($id=0){
        $fields  = array('cid', 'scid','ucid','pid',
            'title', 'stitle','keywords', 'tags', 'description','source',
            'author', 'editor', 'userid',
            'haspic','pic','mpic','spic', 'picdata',
            'related', 'pubdate', 'chapter', 'url','clink',
            'hits','hits_today','hits_yday','hits_week','hits_month','favorite','comments', 'good', 'bad',
            'sortnum','weight', 'postype', 'creative','markdown','tpl','status');

        if(empty($id)){ //新增
            $_fields = array('mobile','postime');
            $fields  = array_merge ($fields,$_fields);
        }

        return $fields;
    }

    public static function count_sql($sql=''){
        return "SELECT count(*) FROM `#iCMS@__article` {$sql}";
    }
    public static function chapter_count($aid){
        $count = iDB::value("SELECT count(id) FROM `#iCMS@__article_data` where `aid` = '$aid'");
        iDB::query("UPDATE `#iCMS@__article` SET `chapter`='$count'  WHERE `id` = '$aid'");
    }
    public static function check($value,$id=0,$field='title'){
        $sql = "SELECT `id` FROM `#iCMS@__article` where `{$field}` = '$value'";
        $id && $sql.=" AND `id` !='$id'";
        return iDB::value($sql);
    }

    public static function value($field='id',$id=0){
        if(empty($id)){
            return;
        }
        return iDB::value("SELECT {$field} FROM `#iCMS@__article` WHERE `id`='$id';");
    }
    public static function row($id=0,$field='*',$sql=''){
        return iDB::row("SELECT {$field} FROM `#iCMS@__article` WHERE `id`='$id' {$sql} LIMIT 1;",ARRAY_A);
    }
    public static function data($id=0,$adid=0,$userid=0){
        $userid && $sql = " AND `userid`='$userid'";
        $rs    = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id`='$id' {$sql} LIMIT 1;",ARRAY_A);
        if($rs){
            $aid   = $rs['id'];
            $adsql = "SELECT * FROM `#iCMS@__article_data` WHERE `aid`='$aid'";
            $adid && $adsql.= " AND `id`='{$adid}'";

            if($rs['chapter']){
                $adrs  = iDB::all($adsql,ARRAY_A);
            }else{
                $adrs  = iDB::row($adsql,ARRAY_A);
            }
        }
        return array($rs,$adrs);
    }
    public static function body($id=0){
        $body = iDB::value("SELECT body FROM `#iCMS@__article_data` WHERE aid='$id'");
        return $body;
    }

    public static function batch($data,$ids){
        if(empty($ids)){
            return;
        }
        foreach ( array_keys($data) as $k ){
            $bits[] = "`$k` = '$data[$k]'";
        }
        iDB::query("UPDATE `#iCMS@__article` SET " . implode( ', ', $bits ) . " WHERE `id` IN ($ids)");
    }
    public static function insert($data){
        return iDB::insert('article',$data);
    }
    public static function update($data,$where){
        return iDB::update('article',$data,$where);
    }
// --------------------------------------------------
    public static function data_fields($update=false){
        $fields  = array('subtitle', 'body');
        $update OR $fields  = array_merge ($fields,array('aid'));
        return $fields;
    }
    public static function data_insert($data){
        return iDB::insert('article_data',$data);
    }
    public static function data_update($data,$where){
        return iDB::update('article_data',$data,$where);
    }

    public static function del($id){
        iDB::query("DELETE FROM `#iCMS@__article` WHERE id='$id'");
    }
    public static function del_data($id,$f='aid'){
        iDB::query("DELETE FROM `#iCMS@__article_data` WHERE `$f`='$id'");
    }
}

