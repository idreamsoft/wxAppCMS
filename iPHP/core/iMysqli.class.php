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
define('OBJECT', 'OBJECT');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');

defined('iPHP_DB_PORT') OR define('iPHP_DB_PORT', '3306');

class iDB {
    public static $print_sql = false;
    public static $show_trace = false;
    public static $show_errors = false;
    public static $show_explain = false;
    public static $num_queries = 0;
    public static $trace_info;
    public static $last_query;
    public static $col_info;
    public static $backtrace;
    public static $func_call;
    public static $last_result;
    public static $num_rows;
    public static $insert_id;
    public static $link;
    public static $config = null;
    public static $dbFlag = 'iPHP_DB';

    private static $collate;
    private static $time_start;
    private static $last_error ;
    private static $result;

    public static function config($config=null) {
        empty(self::$config) && self::$config = array(
            'HOST'       => iPHP_DB_HOST,
            'USER'       => iPHP_DB_USER,
            'PASSWORD'   => iPHP_DB_PASSWORD,
            'DB'         => iPHP_DB_NAME,
            'CHARSET'    => iPHP_DB_CHARSET,
            'PORT'       => iPHP_DB_PORT,
            'PREFIX'     => iPHP_DB_PREFIX,
            'PREFIX_TAG' => iPHP_DB_PREFIX_TAG
        );
        $config && self::$config = $config;
    }
    public static function connect($flag=null) {
        extension_loaded('mysqli') OR self::bail('mysqli extension is missing. Please check your PHP configuration');

        self::config();
        if(isset($GLOBALS[self::$dbFlag])){
            self::$link = $GLOBALS[self::$dbFlag];
            if(self::$link){
                if(self::$link->ping())
                    return self::$link;
            }
        }
        self::$link = new mysqli(self::$config['HOST'], self::$config['USER'], self::$config['PASSWORD'],null,self::$config['PORT']);
        if($flag==='link'){
            return self::$link;
        }

        self::$link->connect_errno && self::bail('Connect Error ('.self::$link->connect_errno.') '.self::$link->connect_error);

        $GLOBALS[self::$dbFlag] = self::$link;
        self::pre_set();
        if($flag===null){
            self::select_db();
        }
    }
    public static function pre_set() {
        self::$link->set_charset(self::$config['CHARSET']);
        self::$link->query("SET @@sql_mode =''");
    }
    public static function select_db($var=false) {
        $sel = self::$link->select_db(self::$config['DB']);
        if($var) return $sel;
        $sel OR self::bail('Connect Error ('.self::$link->errno.') '.self::$link->error);
    }
    // ==================================================================
    /** Quote string to use in SQL
    * @param string
    * @return string escaped string enclosed in '
    */
    public static function quote($string) {
        return "'" . self::$link->real_escape_string($string) . "'";
    }
    public static function table($name) {
        self::config();
        return self::$config['PREFIX'].str_replace(self::$config['PREFIX_TAG'],'', trim($name));
    }
    public static function check_table($table,$prefix=true) {
        $prefix && $table = self::table($table);
        $variable = self::tables_list();
        foreach ($variable as $key => $value) {
            $tables_list[$value['TABLE_NAME']] = true;
        }
        $table = strtolower($table);
        if($tables_list[$table]){
            return true;
        }
        return false;
    }
    /** Get tables list
    * @return array array($name => $type)
    */
    public static function tables_list() {
        return iDB::all(iDB::version() >= 5
            ? "SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME"
            : "SHOW TABLES"
        );
    }
    //  Basic Query - see docs for more detail
    public static function query($query,$QT=NULL) {
        if(empty($query)){
            if (self::$show_errors) {
                self::bail("SQL IS EMPTY");
            } else {
                return false;
            }
        }
        if(self::$print_sql){
            echo '<pre>';
            print_r($query);
            echo '</pre>';
            return;
        }
        self::$link OR self::connect();

        // filter the query, if filters are available
        // NOTE: some queries are made before the plugins have been loaded, and thus cannot be filtered with this method
        $query  = str_replace(self::$config['PREFIX_TAG'],self::$config['PREFIX'], trim($query));

        // initialise return
        $return_val = 0;
        self::flush();

        // Log how the function was called
        self::$func_call = __CLASS__.'::query("'.$query.'")';

        // Keep track of the last query for debug..
        self::$last_query = $query;

        // Perform the query via std mysql_query function..
        self::$show_trace && self::timer_start();

        // $query = self::quote($query);
        $result = self::$link->real_query($query);

        if(!$result){
            // If there is an error then take note of it..
            return self::print_error();
        }
        if(strpos($query,'EXPLAIN')===false){
            self::$num_queries++;
            self::$show_trace && self::backtrace($query);
        }

        self::$show_trace && self::timer_start();

	   if($QT=='get') return $result;

        $QH = strtoupper(substr($query,0,strpos($query, ' ')));
        if (in_array($QH,array('INSERT','DELETE','UPDATE','REPLACE','SET','CREATE','DROP','ALTER'))) {
            // Take note of the insert_id
            if (in_array($QH,array("INSERT","REPLACE"))) {
                self::$insert_id = self::$link->insert_id;
            }
            // Return number of rows affected
            $return_val = self::$link->affected_rows;
        } else {
            $store = self::$link->store_result();

            if($QT=="field") {
                self::$col_info = $store->fetch_fields();
            }else {
                $QH=='EXPLAIN' OR self::show_explain();
                $num_rows = 0;
                if($store){
                    while ( $row = $store->fetch_object() ) {
                        self::$last_result[$num_rows] = $row;
                        $num_rows++;
                    }
                    // $store->close();
                    $store->free();
                }
                $store = null;
                // Log number of rows the query returned
                self::$num_rows = $num_rows;

                // Return number of rows selected
                $return_val = $num_rows;
            }
        }
        $result = null;

        return $return_val;
    }
    public static function get($output = OBJECT) {
        $store = self::$link->store_result();
        if ( $output == OBJECT ) {
            return $store->fetch_object(MYSQL_ASSOC);
        }else{
            return $store->fetch_array(MYSQL_ASSOC);
        }
    }
    /**
     * Insert an array of data into a table
     * @param string $table WARNING: not sanitized!
     * @param array $data should not already be SQL-escaped
     * @return mixed results of self::query()
     */
    public static function insert($table, $data,$IGNORE=false) {
        $fields = array_keys($data);
        self::query("INSERT ".($IGNORE?'IGNORE':'')." INTO ".iPHP_DB_PREFIX_TAG."{$table} (`" . implode('`,`',$fields) . "`) VALUES ('".implode("','",$data)."')");
        return self::$insert_id;
    }
    public static function insert_multi($table,$fields,$data) {
        $datasql = array();
        foreach ((array)$data as $key => $d) {
            $datasql[]= "('".implode("','",$d)."')";
        }
        if($datasql){
            return self::query("INSERT INTO ".iPHP_DB_PREFIX_TAG."{$table} (`" . implode('`,`',$fields) . "`) VALUES ".implode(',',$datasql));
        }
    }
    /**
     * Update a row in the table with an array of data
     * @param string $table WARNING: not sanitized!
     * @param array $data should not already be SQL-escaped
     * @param array $where a named array of WHERE column => value relationships.  Multiple member pairs will be joined with ANDs.  WARNING: the column names are not currently sanitized!
     * @return mixed results of self::query()
     */
    public static function update($table, $data, $where) {
        $bits = $wheres = array();
        foreach ( array_keys($data) as $k ){
            $bits[] = "`$k` = '$data[$k]'";
        }
        if ( is_array( $where ) ){
            foreach ( $where as $c => $v )
                $wheres[] = "`$c` = '" . addslashes( $v ) . "'";
        }else{
            return false;
        }
        return self::query("UPDATE ".iPHP_DB_PREFIX_TAG."{$table} SET " . implode( ', ', $bits ) . ' WHERE ' . implode( ' AND ', $wheres ) . ' LIMIT 1;' );
    }

    /**
     * Get one variable from the database
     * @param string $query (can be null as well, for caching, see codex)
     * @param int $x = 0 row num to return
     * @param int $y = 0 col num to return
     * @return mixed results
     */
    public static function val($table, $field, $where) {
        $fields = $wheres = array();
        if ( is_array( $field ) ){
            foreach ( $field as $c => $f )
                $fields[] = "`$f`";
        }else{
            return false;
        }

        if ( is_array( $where ) ){
            foreach ( $where as $c => $v ){
                if(strpos($c,'!')===false){
                    $wheres[] = "$c = '" . addslashes( $v ) . "'";
                }else{
                    $c = str_replace('!', '', $c);
                    $wheres[] = "$c != '" . addslashes( $v ) . "'";
                }
            }
        }else{
            return false;
        }
        return self::value("SELECT ".implode( ', ', $fields )." FROM ".iPHP_DB_PREFIX_TAG."{$table} WHERE " . implode( ' AND ', $wheres ) . ' LIMIT 1;' );
    }
    public static function value($query=null, $x = 0, $y = 0) {
        self::$func_call = __CLASS__."::value(\"$query\",$x,$y)";
        $query && self::query($query);
        // Extract var out of cached results based x,y vals
        if ( !empty( self::$last_result[$y] ) ) {
            $values = array_values(get_object_vars(self::$last_result[$y]));
        }
        // If there is a value return it else return null
        return (isset($values[$x]) && $values[$x]!=='') ? $values[$x] : null;
    }

    /**
     * Get one row from the database
     * @param string $query
     * @param string $output ARRAY_A | ARRAY_N | OBJECT
     * @param int $y row num to return
     * @return mixed results
     */
    public static function row($query = null, $output = OBJECT, $y = 0) {
        self::$func_call = __CLASS__."::row(\"$query\",$output,$y)";
        $query && self::query($query);

        if ( !isset(self::$last_result[$y]) )
            return null;

        if ( $output == OBJECT ) {
            return self::$last_result[$y] ? self::$last_result[$y] : null;
        } elseif ( $output == ARRAY_A ) {
            return self::$last_result[$y] ? get_object_vars(self::$last_result[$y]) : null;
        } elseif ( $output == ARRAY_N ) {
            return self::$last_result[$y] ? array_values(get_object_vars(self::$last_result[$y])) : null;
        } else {
            self::print_error(__CLASS__."::row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N");
        }
    }

    /**
     * Return an entire result set from the database
     * @param string $query (can also be null to pull from the cache)
     * @param string $output ARRAY_A | ARRAY_N | OBJECT
     * @return mixed results
     */
    public static function all($query = null, $output = ARRAY_A) {
        self::$func_call = __CLASS__."::all(\"$query\", $output)";

        $query && self::query($query);

        // Send back array of objects. Each row is an object
        if ( $output == OBJECT ) {
            return self::$last_result;
        } elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
            if ( self::$last_result ) {
                $i = 0;
                foreach( (array) self::$last_result as $row ) {
                    if ( $output == ARRAY_N ) {
                        // ...integer-keyed row arrays
                        $new_array[$i] = array_values( get_object_vars( $row ) );
                    } else {
                        // ...column name-keyed row arrays
                        $new_array[$i] = get_object_vars( $row );
                    }
                    ++$i;
                }
                return $new_array;
            } else {
                return array();
            }
        }
    }

    /**
     * Gets one column from the database
     * @param string $query (can be null as well, for caching, see codex)
     * @param int $x col num to return
     * @return array results
     */
    public static function col($query = null , $x = 0) {
        $query && self::query($query);
        $new_array = array();
        // Extract the column values
        for ( $i=0; $i < count(self::$last_result); $i++ ) {
            $new_array[$i] = self::value(null, $x, $i);
        }
        return $new_array;
    }

    /**
     * Grabs column metadata from the last query
     * @param string $info_type one of name, table, def, max_length, not_null, primary_key, multiple_key, unique_key, numeric, blob, type, unsigned, zerofill
     * @param int $col_offset 0: col name. 1: which table the col's in. 2: col's max length. 3: if the col is numeric. 4: col's type
     * @return mixed results
     */
    public static function col_info($query = null ,$info_type = 'name', $col_offset = -1) {
        $query && self::query($query,"field");
        if ( self::$col_info ) {
            if ( $col_offset == -1 ) {
                $i = 0;
                foreach(self::$col_info as $col ) {
                    $new_array[$i] = $col->{$info_type};
                    $i++;
                }
                return $new_array;
            } else {
                return self::$col_info[$col_offset]->{$info_type};
            }
        }
    }
    public static function version() {

        self::$link OR self::connect();
        // Make sure the server has MySQL 4.0
        $mysql_version = preg_replace('|[^0-9\.]|', '', self::$link->server_info);

        if ( version_compare($mysql_version, '4.0.0', '<') ){
            self::bail('mysql version error,iPHP requires MySQL 4.0.0 or higher');
        }else{
            return $mysql_version;
        }
    }

    // ==================================================================
    //  Kill cached query results

    public static function flush() {
        self::$last_result  = array();
        self::$col_info     = null;
        self::$last_query   = null;
    }
    /**
     * Starts the timer, for debugging purposes
     */
    public static function timer_start() {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        self::$time_start = $mtime[1] + $mtime[0];
        return true;
    }

    /**
     * Stops the debugging timer
     * @return int total time spent on the query, in milliseconds
     */
    public static function timer_stop($restart=false) {
        $mtime      = microtime();
        $mtime      = explode(' ', $mtime);
        $time_end   = $mtime[1] + $mtime[0];
        $time_total = $time_end - self::$time_start;
        $restart && self::$time_start = $time_end;
        return round($time_total, 5);
    }
    // ==================================================================
    public static function show_explain(){
        if(!self::$show_explain) return;
        $query = self::$last_query;
        $explain = self::row('EXPLAIN EXTENDED '.$query);
        $explain && $explain->query = $query;
        if(self::$show_explain=='print'){
            echo "<pre>".
            var_dump($explain);
            echo "</pre>";
        }else{
            echo "<!--\n";
            print_r($explain);
            echo "-->\n";
        }
    }
    // public static function show_errors(){
    //     if(!self::$show_errors) return false;
    //     self::bail('<strong>iDB SQL error:</strong>'.self::$last_query);
    // }
    //
    //  Print SQL/DB error.

    public static function print_error($error = '') {
        if(!self::$show_errors) return;

        self::$last_error = self::$link->error;
        $error OR $error  = self::$last_error;

        $error = htmlspecialchars($error, ENT_QUOTES);
        $query = htmlspecialchars(self::$last_query, ENT_QUOTES);
        // Is error output turned on or not..
        if ($error) {
            self::bail("<strong>iDB error:</strong> [$error]<br /><code>$query</code>");
        } else {
            return false;
        }
    }
    public static function backtrace($query){
        $trace = '';
        $backtrace = debug_backtrace();
        // $backtrace = array_slice($backtrace,1,2);
        foreach ($backtrace as $i => $l) {
            $trace .= "\n[$i] in function <b>{$l['class']}{$l['type']}{$l['function']}</b>";
            $l['file'] = str_replace('\\', '/', $l['file']);
            $l['file'] = iSecurity::filter_path($l['file']);
            $l['file'] && $trace .= " in <b>{$l['file']}</b>";
            $l['line'] && $trace .= " on line <b>{$l['line']}</b>";
        }
        self::$trace_info[] = array('sql'=>$query, 'exec_time'=>self::timer_stop(true),'backtrace'=>$trace);
        unset($trace,$backtrace);
    }
    /**
     * Wraps fatal errors in a nice header and footer and dies.
     * @param string $message
     */
    public static function bail($message=null){ // Just wraps errors in a nice header and footer
        if(!self::$show_errors) return;
        empty($message) && $message = 'mysql Error ('.self::$link->errno.') '.self::$link->error;
        trigger_error($message,E_USER_ERROR);
    }
}
