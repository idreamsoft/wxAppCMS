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
class iSQL {
    public static $check_numeric = false;
    public static function get_rand_ids($table,$where=null,$limit='10',$primary='id'){
        $whereSQL = $where?
            "{$where} AND `{$table}`.`{$primary}` >= rand_id":
            " WHERE `{$table}`.`{$primary}` >= rand_id";
        // $limitNum = rand(2,10);
        // $prelimit = ceil($limit/rand(2,10));
        $randSQL  = "
            SELECT `{$table}`.`{$primary}` FROM `{$table}`
            JOIN (SELECT
                  ROUND(RAND() * (
                      (SELECT MAX(`{$table}`.`{$primary}`) FROM `{$table}`) -
                      (SELECT MIN(`{$table}`.`{$primary}`) FROM `{$table}`)
                    ) + (SELECT MIN(`{$table}`.`{$primary}`) FROM `{$table}`)
                 ) AS rand_id) RAND_DATA
            {$whereSQL}
            LIMIT $limit;
        ";
        $randIdsArray = iDB::all($randSQL);
        // $randIdsArray = null;
        // for ($i=0; $i <=$prelimit; $i++) {
        //     $randIdsArray[$i] = array('id'=>iDB::value($randSQL));
        //     echo iDB::$last_query;
        // }
        return $randIdsArray;
    }
    public static function update_hits($all=true,$hit=1){
        $timer_task = iPHP::timer_task();
        // var_dump($timer_task);
        $pieces = array();
        $all && $pieces[] = '`hits` = hits+'.$hit;
        foreach ($timer_task as $key => $bool) {
            $field = "hits_{$key}";
            if($key=='yday'){
                if($bool==1){
                    $pieces[]="`hits_yday` = hits_today";
                }elseif ($bool>1) {
                    $pieces[]="`hits_yday` = 0";
                }
                continue;
            }
            $pieces[]="`{$field}` = ".($bool?"{$field}+{$hit}":$hit);
        }
        return implode(',', $pieces);
    }
    public static function filter_data(array &$data,$fields=null) {
        if($fields){
            foreach ($data as $key => $value) {
                if(array_search($key, $fields)===FALSE){
                    unset($data[$key]);
                }
            }
        }
    }
    public static function where($where,$and=false) {
        if ( is_array( $where ) ){
            foreach ( $where as $c => $v ){
                if($c[0]=='!'){
                    $c = str_replace('!', '', $c);
                    $wheres[] = "`$c` != '" . addslashes( $v ) . "'";
                }else{
                    $wheres[] = "`$c` = '" . addslashes( $v ) . "'";
                }
            }
            return ($and?' AND ':'').implode(' AND ', $wheres);
        }
    }

    public static function in($vars, $field, $flag = false, $noand = false, $table = '') {
        if (is_bool($vars) || empty($vars)) {
            if(self::$check_numeric){
                if(!is_numeric($vars)){
                    return '';
                }
            }else{
                return '';
            }
        }
        if (!is_array($vars) && strpos($vars,',') !== false){
            $vars = explode(',', $vars);
        }

        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $vk => $vv) {
                        $vas[] = "'" . addslashes($vv) . "'";
                    }
                }else{
                    $vas[] = "'" . addslashes($value) . "'";
                }
            }
            is_array($vas) && $vas  = array_unique($vas);
            $vars = implode(',', $vas);
            $sql  = " IN ({$vars}) ";
            $flag=='not' && $sql  = " NOT IN ({$vars})";
        } else {
            $vars = addslashes($vars);
            $sql = " ='{$vars}' ";
            if($flag){
                $sql = " {$flag}'{$vars}' ";
                $flag=='not' && $sql = "<>'{$vars}' ";
            }
        }
        $table && $table .= '.';
        $sql = "{$table}`{$field}`" . $sql;
        if ($noand) {
            return $sql;
        }
        $sql = ' AND ' . $sql;
        self::$check_numeric = false;
        return $sql;
    }
    public static function explode_var($ids,$delimiter=',') {
      $array = array();
      foreach ((array)$ids as $key => $value) {
        if(strpos($value, $delimiter) !== false){
          $a = explode($delimiter, $value);
          foreach ($a as $k => $v) {
              $array[] = $v;
          }
        }else{
          $array[] = $value;
        }
      }
      return $array;
    }
    public static function multi_var($ids,$only=false) {
        $is_multi = false;
        if(is_array($ids)){
            $is_multi = true;
        }
        if(!is_array($ids) && strpos($ids, ',') !== false){
            $ids = explode(',', $ids);
            $is_multi = true;
        }
        if($only){
            return $ids;
        }
        return array($ids,$is_multi);
    }
    /**
     * 返回数组中指定的一列
     * @param  [type] $rs    [description]
     * @param  string $field [description]
     * @param  string $ret   [description]
     * @param  string $quote [description]
     * @param  [type] $key   [description]
     * @return [type]        [description]
     */
    public static function values($rs, $field = 'id',$ret='string',$quote="'",$key=null) {
        if (empty($rs)) {
            return false;
        }

        $resource = array();
        foreach ((array) $rs AS $rkey =>$_vars) {
            if($key===null){
                $_key = $rkey;
            }else{
                $_key = $_vars[$key];
            }

            if ($field === null) {
                $_vars!=='' && $resource[$_key] = $quote . $_vars . $quote;
            } else {
                if(is_array($field)){
                    foreach ($field as $fk => $fv) {
                        $_vars[$fv]!=='' && $resource[$_key][$fk] = $quote . $_vars[$fv] . $quote;
                    }
                }else{
                    $_vars[$field]!=='' && $resource[$_key] = $quote . $_vars[$field] . $quote;
                }
            }
        }
        unset($rs);
        if ($resource) {
            if($ret=='array'){
                return $resource;
            }else{
                $resource = implode(',', $resource);
                return $resource;
            }
        }
        return false;
    }
    public static function select_map($where, $type = null, $field = 'iid') {
        if (empty($where)) {
            return false;
        }
        $i = 0;
        foreach ($where as $key => $value) {
            $as = ' map';
            $i && $as .= $i;
            $_FROM[] = $key . $as;
            $_WHERE[] = str_replace($key, $as, $value);
            $_FIELD[] = $as . ".`{$field}`";
            $i++;
        }
        $_field = $_FIELD[0];
        $_count = count($_FIELD);
        if ($_count > 1) {
            foreach ($_FIELD as $fkey => $fd) {
                $fkey && array_push($_WHERE, $_field . ' = ' . $fd);
            }
        }
        if ($type == 'join') {
            return array('from' => implode(',', $_FROM), 'where' => implode(' AND ', $_WHERE));
        }
        return 'SELECT ' . $_field . ' AS ' . $field . ' FROM ' . implode(',', $_FROM) . ' WHERE ' . implode(' AND ', $_WHERE);
    }
    public static function update_args($data = '') {
        $array = array();
        $dA = explode(',', $data);
        foreach ((array) $dA as $d) {
            list($f, $v) = explode(':', $d);
            $v == 'now' && $v = time();
            $v = (int) $v;
            $array[$f] = $v;
        }
        return $array;
    }
    public static function pickup_keys(&$resource,$keys,$remove=false) {
        is_array($keys) OR $keys = explode(',', $keys);
        foreach ((array)$resource as $key => $value) {
            foreach ($value as $k => $v) {
                if(in_array($k, $keys)){
                    if($remove)unset($resource[$key][$k]);
                }else{
                    if(!$remove)unset($resource[$key][$k]);
                }
            }
        }
    }
}
