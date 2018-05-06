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

class favorite {
    public static function update_count($id=0,$field='count',$math='+',$count='1'){
        $math=='-' && $sql = " AND `{$field}`>0";
        iDB::query("
            UPDATE `#iCMS@__favorite`
            SET `{$field}` = {$field}{$math}{$count}
            WHERE `id`='{$id}' {$sql}
        ");
    }
}
