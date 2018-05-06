<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/

class tag {
    public static $appid      = '1';
    public static $field      = 'tags';
    public static $remove     = true;
    public static $add_status = '1';

    public static function fields($id=0){
        $fields  = array(
            'cid','tcid','pid',
            'title','name','tkey','field','rootid','seotitle','subtitle','keywords','description','related',
            'editor', 'userid',
            'haspic','pic','bpic','mpic','spic',
            'pubdate', 'url','clink',
            'hits','hits_today','hits_yday','hits_week','hits_month','favorite','comments', 'good', 'bad',
            'sortnum','weight', 'postype', 'creative','tpl','status'
        );

        if(empty($id)){ //新增
            $_fields = array('postime');
            $fields  = array_merge ($fields,$_fields);
        }

        return $fields;
    }
    public static function check($value,$id=0,$field='title'){
        $sql = "SELECT `id` FROM `#iCMS@__tag` where `{$field}` = '$value'";
        $id && $sql.=" AND `id` !='$id'";
        return iDB::value($sql);
    }

    public static function value($field='id',$id=0){
        if(empty($id)){
            return;
        }
        return iDB::value("SELECT {$field} FROM `#iCMS@__tag` WHERE `id`='$id';");
    }
    public static function get($ids=0,$field='id',$callback=null){
        if(empty($ids)) return array();

        list($ids,$is_multi)  = iSQL::multi_var($ids);

        $sql = iSQL::in($ids,$field,false,true);
        $data = array();
        $rs = iDB::all("SELECT * FROM `#iCMS@__tag` where {$sql} AND `status`='1'");
        if($rs){
            $_count = count($rs);
            for ($i=0; $i < $_count; $i++) {
                if(is_callable($callback)){
                    $rs[$i] = iPHP::callback($callback,array($rs[$i]));
                }
                $data[$rs[$i]['id']]= $rs[$i];
            }
            $is_multi OR $data = $data[$ids];
        }
        if(empty($data)){
            return;
        }
        return $data;
    }

	public static function cache($value=0,$field='id'){}

    public static function name($name){
        $name = trim($name);
        $name = trim($name,"\0\n\r\t\x0B");
        $name = preg_replace('/<[\/\!]*?[^<>]*?>/is','',$name);
        $name = htmlspecialchars_decode($name);
        return $name;
    }
    public static function field($field){
        $self = new self();
        $self::$field = $field;
        return $self;
    }
	public static function add($tags,$userid="0",$iid="0",$cid='0',$tcid='0') {
		$a        = explode(',',$tags);
		$c        = count($a);
		$tagArray = array();
	    for($i=0;$i<$c;$i++) {
	        $tagArray[$i] = self::update($a[$i],$userid,$iid,$cid,$tcid);
	    }
	    return implode(',', (array)$tagArray);
	}
	public static function update($name,$userid="0",$iid="0",$cid='0',$tcid='0') {
	    if(empty($name)) return;
        $name = self::name($name);
	    $tid = iDB::value("SELECT `id` FROM `#iCMS@__tag` WHERE `name`='$name'");
	    if($tid) {
            $mapid = iDB::value("
                SELECT `id` FROM `#iCMS@__tag_map`
                WHERE `iid`='$iid'
                AND `node`='$tid'
                AND `appid`='".self::$appid."'
                AND `field`='".self::$field."'
            ");

            empty($mapid) && iDB::query("
                UPDATE `#iCMS@__tag`
                SET `count`=count+1,
                    `pubdate`='".time()."'
                WHERE `id`='$tid'
            ");
	    }else {
			$tkey   = iPinyin::get($name,iCMS::$config['tag']['tkey']);
            $fields = self::fields();
			$data   = compact($fields);
            $data['title']   = $name;
            $data['pid']     = '0';
            $data['count']   = '1';
            $data['weight']  = '0';
            $data['sortnum'] = '0';
            $data['pubdate'] = time();
            $data['postime'] = $data['pubdate'];
            $data['status']  = self::$add_status;
            $data['field']   = self::$field;

			$tid = iDB::insert('tag',$data);
	    }
        iMap::init('tag',self::$appid,self::$field);
        iMap::add($tid,$iid);
	    return $name;
	}
	public static function diff($Ntags,$Otags,$userid="0",$iid="0",$cid='0',$tcid='0') {
		$N        = explode(',', $Ntags);
		$O        = explode(',', $Otags);
		$diff     = array_diff_values($N,$O);
		$tagArray = array();
	    foreach((array)$N AS $i=>$tag) {//新增
            $tagArray[$i] = self::update($tag,$userid,$iid,$cid,$tcid);
		}
        iMap::init('tag',self::$appid,self::$field);

	    foreach((array)$diff['-'] AS $tag) {//减少
	        $ot	= iDB::row("
                SELECT `id`,`count`
                FROM `#iCMS@__tag`
                WHERE `name`='$tag' LIMIT 1;
            ");

	        if($ot->count<=1) {
	            iDB::query("DELETE FROM `#iCMS@__tag` WHERE `name`='$tag'");
	        }else {
	            iDB::query("
                    UPDATE `#iCMS@__tag`
                    SET  `count`=count-1,`pubdate`='".time()."'
                    WHERE `name`='$tag' and `count`>0
                ");
	        }
            iMap::diff('',$ot->id,$iid);
	   }
	   return implode(',', (array)$tagArray);
	}
	public static function del($tags,$field='name',$iid=0){
	    $tag_array	= explode(",",$tags);
	    $iid && $sql="AND `iid`='$iid'";
	    foreach($tag_array AS $k=>$v) {
	    	$tag	= iDB::row("SELECT * FROM `#iCMS@__tag` WHERE `$field`='$v' LIMIT 1;");
	    	$tRS	= iDB::all("
                SELECT `iid` FROM `#iCMS@__tag_map`
                WHERE `node`='$tag->id'
                AND `appid`='".self::$appid."'
                AND `field`='".self::$field."' {$sql}
            ");
            $ids = iSQL::values($tRS,'iid');
            if($ids){
                $app = apps::get_table(self::$appid);
                if($app['table']){
                    iDB::query("
                        UPDATE `".$app['table']."` SET
                        `".self::$field."`= REPLACE(".self::$field.", '$tag->name,',''),
                        `".self::$field."`= REPLACE(".self::$field.", ',$tag->name','')
                        WHERE id IN($ids)
                    ");
                }
            }
            self::$remove && iDB::query("DELETE FROM `#iCMS@__tag`  WHERE `$field`='$v'");
            iDB::query("
                DELETE FROM
                `#iCMS@__tag_map`
                WHERE `node`='$tag->id'
                AND `appid`='".self::$appid."'
                AND `field`='".self::$field."' {$sql}
            ");
	    }
	}
    public function merge($tocid,$cid){
        iDB::query("UPDATE `#iCMS@__tag` SET `cid` ='$tocid' WHERE `cid` ='$cid'");
    }
}
