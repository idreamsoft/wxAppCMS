<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

class apps_common {
    public static $primary ='id';
    public static $data    = array();
    public static $vars    = array();
    public static $name    = null;

    public static function init(&$data,$name,$vars,$primary='id') {
        self::$data    = &$data;
        self::$name    = $name;
        self::$vars    = $vars;
        self::$primary = $primary;
    }
    public static function link($title=null) {
        $title===null && $title = self::$data['title'];
        self::$data['link']  = '<a href="'.self::$data['url'].'" class="'.self::$name.'_link" target="_blank">'.$title.'</a>';
    }
    public static function text2link() {
        self::$data['source'] = text2link(self::$data['source']);
        self::$data['author'] = text2link(self::$data['author']);
    }

    public static function comment() {
        self::$data['comment'] = array(
            'url' => iURL::router('api')."?app=".self::$name."&do=comment&appid=".self::$data['appid']."&iid=".self::$data[self::$primary]."&cid=".self::$data['cid'],
            'count' => self::$data['comments'],
        );
    }
    public static function pic(){
        $picArray = array();
        isset(self::$data['picdata']) && $picArray = filesApp::get_picdata(self::$data['picdata']);

        if(isset(self::$data['pic'])){
            self::$data['pic']  = filesApp::get_pic(
                self::$data['pic'],
                $picArray['p'],
                filesApp::get_twh(
                    self::$vars['ptw'],
                    self::$vars['pth']
                )
            );
        }
        $sizeMap = array('b','m','s');
        foreach ($sizeMap as $key => $size) {
            $k = $size.'pic';
            if(isset(self::$data[$k])){
                self::$data[$k] = filesApp::get_pic(
                    self::$data[$k],
                    $picArray[$size],
                    filesApp::get_twh(
                        self::$vars[$size.'tw'],
                        self::$vars[$size.'th']
                    )
                );
            }
        }
        unset(self::$data['picdata'],$picArray);
    }
    public static function user() {
        if (self::$vars['user']) {
            $author = self::$data['author'];
            if (self::$data['postype']) {
                $author = self::$data['editor'];
            }
            self::$data['user'] = user::info(self::$data['userid'], $author);
        }
    }
    public static function hits() {
        self::$data['hits']   = array(
            'script' => iURL::router('api').'?app='.self::$name.'&do=hits&cid=' . self::$data['cid'] . '&id=' . self::$data[self::$primary],
            'count'  => self::$data['hits'],
            'today'  => self::$data['hits_today'],
            'yday'   => self::$data['hits_yday'],
            'week'   => self::$data['hits_week'],
            'month'  => self::$data['hits_month'],
        );
    }
    public static function param() {
        self::$data['param'] = array(
            "appid" => self::$data['appid'],
            "iid"   => self::$data['id'],
            "cid"   => self::$data['cid'],
            "suid"  => self::$data['userid'],
            "title" => self::$data['title'],
            "url"   => self::$data['url'],
        );
    }

    public static function data($ids=0,$table,$primary='id',$fields="*"){
        if(empty($ids)) return array();
        list($ids,$is_multi)  = iSQL::multi_var($ids);
        $fields OR $fields = "*";
        if($fields != "*"){
            $fields_a = explode(',', $fields);
            $fArray   = array();
            $has_pri  = false;
            foreach ($fields_a as $key => $f) {
                $f = trim($f,'`');
                $primary==$f && $has_pri = true;
                $fArray[] = '`'.$f.'`';
            }
            $has_pri OR $fArray[] = '`'.$primary.'`';
            $fields = implode(', ', $fArray);
        }
        $sql  = iSQL::in($ids,$primary,false,true);
        $data = array();
        $rs   = iDB::all("SELECT {$fields} FROM `#iCMS@__{$table}_data` where {$sql}");
        if($rs){
            $_count = count($rs);
            for ($i=0; $i < $_count; $i++) {
                $data[$rs[$i][$primary]]= $rs[$i];
            }
            $is_multi OR $data = $data[$ids];
        }
        if(empty($data)){
            return;
        }
        return $data;
    }
}
