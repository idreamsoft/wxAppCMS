<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

class favorite {
    public static function data_row($where,$field='*'){
        $sql = iSQL::where($where,false);
        $row = iDB::row("
            SELECT {$field} FROM `#iCMS@__favorite_data`
            WHERE $sql
        ",ARRAY_A);
        return $row;
    }
    public static function data_all($where,$field='*'){
        $sql = iSQL::where($where,false);
        $rs = iDB::all("
            SELECT {$field} FROM `#iCMS@__favorite_data`
            WHERE $sql
        ",ARRAY_A);
        return $rs;
    }
    public static function check($iid,$uid,$appid){
        $id  = iDB::value("
            SELECT `id` FROM `#iCMS@__favorite_data`
            WHERE `uid`='$uid'
            AND `iid`='$iid'
            AND `appid`='$appid'
            LIMIT 1
        ");
        return $id?true:false;
    }
    public static function update_count($fid=0,$uid=0,$field='count',$math='+',$count='1'){
        $math=='-' && $sql = " AND `{$field}`>0";
        $uid && $sql.= " AND `uid`='{$uid}'";
        iDB::query("
            UPDATE `#iCMS@__favorite`
            SET `{$field}` = {$field}{$math}{$count}
            WHERE `id`='{$fid}' {$sql}
        ");
    }
}
