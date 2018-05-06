<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) iiiPHP.com. All rights reserved.
 *
 * @author iPHPDev <master@iiiphp.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.1.0
 */
class iMap {
	public static $table    = 'prop';
	public static $field    = null;
	public static $appid    = '1';
	public static $where    = array();
	public static $stack    = array();
	public static $distinct = false;

	public static function init($table = 'prop',$appid='1',$field = null){
		self::$table = iPHP_DB_PREFIX_TAG.$table.'_map';
		self::$field = $field;
		self::$appid = $appid;
		++self::$stack[$table];
		return new self();
	}
	public static function del($nodes,$iid="0") {
		$_array   = explode(',',$nodes);
		$_count   = count($_array);
		$varArray = array();
	    for($i=0;$i<$_count;$i++) {
	    	$_node = $_array[$i];
			iDB::query("
				DELETE FROM `".self::$table."`
				WHERE `node`='$_node'
				AND `iid`='$iid'
				AND `field`='".self::$field."'
				AND `appid`='".self::$appid."'
			");
	    }
	}
    public static function del_data($iid=null,$appid=null,$table=null,$field='iid'){
        if($iid && $appid && $table){
            iDB::query("
                DELETE FROM `".iPHP_DB_PREFIX_TAG.$table."_map`
                WHERE `$field`='$iid'
                AND `appid`='$appid'
            ");
        }
    }
	public static function add($nodes,$iid="0") {
		$_array   = explode(',',$nodes);
		$_count   = count($_array);
		$varArray = array();
	    for($i=0;$i<$_count;$i++) {
	        $varArray[$i] = self::insert($_array[$i],$iid,$field);
	    }
	    return json_encode($varArray);
	}
	public static function insert($node,$iid="0") {
		$has = iDB::value("
		SELECT `id` FROM `".self::$table."`
			WHERE `node`='$node'
			AND `iid`='$iid'
			AND `field`='".self::$field."'
			AND `appid`='".self::$appid."'
			LIMIT 1
		");
	    if(!$has) {
	        iDB::query("
	        	INSERT INTO `".self::$table."`
	        	(`node`,`iid`,`field`, `appid`) VALUES
	        	('$node','$iid','".self::$field."','".self::$appid."')
	        ");
	    }
	    //return array($vars,$tid,$cid,$tcid);
	}
	public static function diff($Nnodes,$Onodes,$iid="0") {
		$N         = explode(',', $Nnodes);
		$O         = explode(',', $Onodes);
		$diff      = array_diff_values($N,$O);
		$varsArray = array();
	    foreach((array)$N AS $i=>$_node) {//新增
            $varsArray[$i] = self::insert($_node,$iid);
		}
	    foreach((array)$diff['-'] AS $_node) {//减少
	        iDB::query("
	        	DELETE FROM `".self::$table."`
	        	WHERE `node`='$_node'
	        	AND `iid`='$iid'
	        	AND `field`='".self::$field."'
	        	AND `appid`='".self::$appid."'
	        ");
	   }
	   return json_encode($varsArray);
	}
	public static function ids($nodes=0){
		if(empty($nodes)) return false;

		$sql = self::sql($nodes);
		$all = iDB::all($ids.'Limit 10000');
		return iSQL::values($all,'iid');
	}
	public static function where($nodes=0){
		if(empty($nodes)) return false;

		if(!is_array($nodes) && strstr($nodes, ',')){
			$nodes = explode(',', $nodes);
		}
		self::$where[self::$table]['field'][] = self::$field;
		self::$where[self::$table]['node'][]  = $nodes;

		$field = array_unique(self::$where[self::$table]['field']);
		$nodes = array_unique(self::$where[self::$table]['node']);

		$where_sql = iSQL::in(self::$appid,'appid',false,true,self::$table);
		$where_sql.= iSQL::in($nodes,'node',false,false,self::$table);
		$where_sql.= iSQL::in($field,'field',false,false,self::$table);
		return array(self::$table=>$where_sql);
	}

	public static function sql($nodes=0){
		if(empty($nodes)) return false;

		if(!is_array($nodes) && strstr($nodes, ',')){
			$nodes = explode(',', $nodes);
		}
		$where_sql = iSQL::in(self::$appid,'appid',false,true);
		$where_sql.= iSQL::in($nodes,'node');
		$where_sql.= iSQL::in(self::$field,'field');
		return "SELECT `iid` FROM ".self::$table." WHERE {$where_sql}";
	}

	public static function exists($nodes=0,$iid=''){
		if(empty($nodes)) return false;

		$sql = self::sql($nodes)." AND iid =".$iid;
		return ' AND exists ('.$sql.')';
	}
	public static function distinct($table,$f='id'){
		if(self::$distinct){
			return self::distinct_sql($table,$f);
		}
		if(is_array(self::$stack))foreach (self::$stack as $key => $value) {
			if($value>1){
				return self::distinct_sql($table,$f);
			}
		}
		return null;
	}
	public static function distinct_sql($table,$f='id'){
		self::reset();
		return ' DISTINCT `'.$table.'`.`'.$f.'` AS _'.$f.', ';
	}
	public static function reset(){
		self::$where    = array();
		self::$stack    = array();
		self::$distinct = false;
	}
	public static function multi($nodes=0,$iid=''){

	}
}
