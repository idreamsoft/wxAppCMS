<?php
class apps_db {
    public static function make_field_sql($vars=null,$alter=null,$origin=null){
        is_array($vars) OR $vars = json_decode($vars,true);

        $field   = $vars['field'];  //字段类型
        $label   = $vars['label']; //字段名称
        $name    = $vars['name'];  //字 段 名
        $default = $vars['default']; //默 认 值
        $len     = $vars['len']; //数据长度
        $comment = $vars['comment']?$vars['comment']:$label;
        $unsigned= $vars['unsigned']; //无符号

        empty($name) && $name = iPinyin::get($label);
        $field = strtolower($field);
        switch ($field) {
            case 'varchar':
            case 'multivarchar':
                $data_type = 'VARCHAR';
            break;
            case 'tinyint':
                $len OR $len = '1';
                $data_type = 'TINYINT';
                $default   = (int)$default;
                empty($default) && $default ='0';
            break;
            case 'primary':
            case 'int':
            case 'time':
                $len OR $len = '10';
                $data_type = 'INT';
                $default   = (int)$default;
                empty($default) && $default ='0';
            break;
            case 'bigint':
                $len OR $len = '20';
                $data_type = 'BIGINT';
                $default   = (int)$default;
                empty($default) && $default ='0';
            break;
            case 'radio':
            case 'select':
                $len OR $len = '6';
                $data_type = 'SMALLINT';
                $default   = (int)$default;
                empty($default) && $default ='0';
            break;
            case 'checkbox':
            case 'multiselect':
                $len OR $len = '255';
                $data_type = 'VARCHAR';
            break;
            case 'image':
            case 'file':
                $len OR $len = '255';
                $data_type = 'VARCHAR';
            break;
            case 'multiimage':
            case 'multifile':
                $len OR $len = '10240';
                $data_type = 'VARCHAR';
            break;
            case 'text':
                $data_type = 'TEXT';
                $len = null;
                $data_default   = null;
            break;
            case 'mediumtext':
            case 'editor':
                $data_type = 'MEDIUMTEXT';
                $len = null;
                $data_default   = null;
            break;
            case 'float':
            case 'double':
            case 'decimal':
                $data_type = strtoupper($field);
                $default   = '0.0';
            break;
            default:
                $len OR $len = '255';
                $data_type = 'VARCHAR';
            break;
        }
        $len===null OR $data_len  = '('.$len.')';

        if(in_array($data_type, array('BIGINT','INT','MEDIUMINT','SMALLINT','TINYINT'))){
            $unsigned && $data_len.=' UNSIGNED';
        }

        if($data_default!==null){
            $data_default = " DEFAULT '$default'";
        }
        if($field=='primary'){
            $data_default = 'AUTO_INCREMENT';
        }

        $sql = self::idf_escape($name)." $data_type$data_len NOT NULL $data_default COMMENT '$comment'";

        switch ($alter) {
          case 'ADD':
              $sql = 'ADD COLUMN '.$sql;
            break;
          case 'CHANGE':
              $sql = 'CHANGE '.self::idf_escape($origin).' '.$sql;
            break;
          case 'DROP':
              $sql = 'DROP COLUMN '.self::idf_escape($name);
            break;
        }

        return $sql;
    }
    public static function make_alter_sql($N_fields,$O_fields,$field_origin){
        //新字段
        $N_field_array  = $N_fields;
        //旧的字段
        $O_fields_array = $O_fields;

        // print_r($O_fields_array);
        // print_r($N_field_array);

        $diff = array_diff_values($N_field_array,$O_fields_array);
        $sql_array = array();
        //删除 或者更改过
        if($diff['-'])foreach ($diff['-'] as $key => $value) {
            if(isset($field_origin[$key])){
              //新字段名
              $nfield = $field_origin[$key];
              //新数据json
              $nvalue = $N_field_array[$nfield];
              if($nvalue){
                $sql_array[]= apps_db::make_field_sql($nvalue,'CHANGE',$key);
                //将更改的字段从新增数据里移除
                unset($diff['+'][$nfield]);
              }
            }else{
              //删除字段
              $sql_array[]= apps_db::make_field_sql($value,'DROP');
            }
        }
        //新增
        if($diff['+'])foreach ($diff['+'] as $key => $value) {
            if(!isset($field_origin[$key])){
              $sql_array[]= apps_db::make_field_sql($value,'ADD');
            }
        }
        // print_r($diff);
        // print_r($field_origin);
        // print_r($sql_array);
        // exit;
        return $sql_array;
    }
    public static function alter_table($name,$sql=null){
        if(empty($sql))return;

        $alter_sql = "ALTER TABLE `#iCMS@__{$name}` ";
        if(is_array($sql)){
            $alter_sql.=implode(',', $sql);
        }
        $alter_sql.= ';';
        iDB::query($alter_sql);
    }
    // public static function create_table($name,$fields=null,$base_fields=true,$PRIMARY='id',$index=null,$ret=false){
    //     $fields_sql = array();
    //     $fields_sql[$PRIMARY]= "`{$PRIMARY}` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键 自增ID'";
    //     // $index && $fields_sql['union_addons']= "`iid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '内容ID 关联基础表'";
    //     $base_fields && $fields_sql= array_merge($fields_sql,self::base_fields_sql());
    //     if(is_array($fields))foreach ($fields as $key => $arr) {
    //         $arr && $fields_sql[$arr['name']] = self::make_field_sql($arr);
    //     }
    //     $fields_sql['primary_'.$PRIMARY] = 'PRIMARY KEY (`'.$PRIMARY.'`)';
    //     // $index && $fields_sql['index_union_addons'] = 'KEY `iid` (`iid`)';
    //     $base_fields && $fields_sql = array_merge($fields_sql,apps_mod::base_fields_index());
    //     $sql= "CREATE TABLE `#iCMS@__{$name}` ("
    //         .implode(",\n", $fields_sql).
    //     ') ENGINE=MYISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;';

    //      if($ret){
    //         return $sql;
    //      }
    //      iDB::query($sql);
    //      return array($name,$PRIMARY);
    // }
    public static function create_table($name,$fields=null,$indexs=null,$query=true){
        $fields_sql = array();
        if(is_array($fields))foreach ($fields as $key => $arr) {
            if($arr){
                $fields_sql[$arr['name']] = self::make_field_sql($arr);
                if($arr['field']=='PRIMARY'){
                    $PRIMARY = $arr['name'];
                }
            }
        }
        $fields_sql['primary_'.$PRIMARY] = 'PRIMARY KEY (`'.$PRIMARY.'`)';
        $indexs && $fields_sql = array_merge($fields_sql,$indexs);
        $sql= "CREATE TABLE `#iCMS@__{$name}` ("
            .implode(",\n", $fields_sql).
        ') ENGINE=MYISAM AUTO_INCREMENT=0 DEFAULT CHARSET='.iPHP_DB_CHARSET.';';

         if($query==='sql'){
            return $sql;
         }
         $query && iDB::query($sql);
         return array($name,$PRIMARY);
    }
    public static function bakuptable($tabledb,$exists=true) {
        foreach ($tabledb as $table) {
            $exists && $creattable .= "DROP TABLE IF EXISTS `$table`;\n";
            $CreatTable = iDB::row("SHOW CREATE TABLE $table", ARRAY_A);
            $CreatTable['Create Table'] = str_replace($CreatTable['Table'], $table, $CreatTable['Create Table']);
            $creattable .= $CreatTable['Create Table'] . ";\n\n";
        }
        return $creattable;
    }
    public static function create_table_sql($json) {
        if($json){
          $tableArray = apps::table_item($json);
          foreach ($tableArray as $key => $value) {
            iDB::check_table($value['table'],false) && $tables[] = $value['table'];
          }
          if($tables){
            $sql = self::bakuptable($tables,false);
            $sql = preg_replace('/\sAUTO_INCREMENT=\d+/is', '', $sql);
            $sql = str_replace('`'.iPHP_DB_PREFIX, '`'.iPHP_DB_PREFIX_TAG, $sql);
            return $sql;
          }
        }
        return false;
    }
    public static function multi_query($sql) {
        $sql      = str_replace("\r", "\n", $sql);
        $resource = array();
        $num      = 0;
        $sql_array = explode(";\n", trim($sql));
        foreach($sql_array as $query) {
            $queries = explode("\n", trim($query));
            foreach($queries as $query) {
                $resource[$num] .= $query[0] == '#' ? '' : $query;
            }
            $num++;
        }
        unset($sql);

        foreach($resource as $key=>$query) {
            $query = trim($query);
            $query = str_replace('`icms_', '`#iCMS@__', $query);
            $query && iDB::query($query);
        }
    }
/**
 * 以下方法移植自adminer
 */

    /** Filter length value including enums
    * @param string
    * @return string
    */
    public static function process_length($length) {
        $enum_length = "'(?:''|[^'\\\\]|\\\\.)*'";
        return (preg_match("~^\\s*\\(?\\s*$enum_length(?:\\s*,\\s*$enum_length)*+\\s*\\)?\\s*\$~", $length) && preg_match_all("~$enum_length~", $length, $matches)
            ? "(" . implode(",", $matches[0]) . ")"
            : preg_replace('~^[0-9].*~', '(\0)', preg_replace('~[^-0-9,+()[\]]~', '', $length))
        );
    }

    /** Create SQL string from field type
    * @param array
    * @param string
    * @return string
    */
    public static function process_type($field, $collate = "COLLATE") {
        $unsigned = array("unsigned", "zerofill", "unsigned zerofill");
        return " $field[type]"
            . self::process_length($field["length"])
            . (preg_match('~(^|[^o])int|float|double|decimal~', $field["type"]) && in_array($field["unsigned"], $unsigned) ? " $field[unsigned]" : "")
            . (preg_match('~char|text|enum|set~', $field["type"]) && $field["collation"] ? " $collate " . q($field["collation"]) : "")
        ;
    }

    /** Create SQL string from field
    * @param array basic field information
    * @param array information about field type
    * @return array array("field", "type", "NULL", "DEFAULT", "ON UPDATE", "COMMENT", "AUTO_INCREMENT")
    */
    public static function process_field($field, $type_field) {
        $default = $field["default"];
        return array(
            self::idf_escape(trim($field["field"])),
            self::process_type($type_field),
            ($field["null"] ? " NULL" : " NOT NULL"), // NULL for timestamp
            (isset($default) ? " DEFAULT " . (
                (preg_match('~time~', $field["type"]) && preg_match('~^CURRENT_TIMESTAMP$~i', $default))
                || (iPHP_DB_TYPE == "sqlite" && preg_match('~^CURRENT_(TIME|TIMESTAMP|DATE)$~i', $default))
                || ($field["type"] == "bit" && preg_match("~^([0-9]+|b'[0-1]+')\$~", $default))
                || (iPHP_DB_TYPE == "pgsql" && preg_match("~^[a-z]+\\(('[^']*')+\\)\$~", $default))
                ? $default : q($default)) : ""),
            (preg_match('~timestamp|datetime~', $field["type"]) && $field["on_update"] ? " ON UPDATE $field[on_update]" : ""),
            (self::support("comment") && $field["comment"] != "" ? " COMMENT " . iDB::quo($field["comment"]) : ""),
            ($field["auto_increment"] ? auto_increment() : null),
        );
    }

    /** Count tables in all databases
    * @param array
    * @return array array($db => $tables)
    */
    public static function count_tables($databases) {
        $return = array();
        foreach ($databases as $db) {
            $return[$db] = count(iDB::all("SHOW TABLES IN " . self::idf_escape($db)));
        }
        return $return;
    }
    /** Get table status
    * @param string
    * @param bool return only "Name", "Engine" and "Comment" fields
    * @return array array($name => array("Name" => , "Engine" => , "Comment" => , "Oid" => , "Rows" => , "Collation" => , "Auto_increment" => , "Data_length" => , "Index_length" => , "Data_free" => )) or only inner array with $name
    */
    public static function table_status($name = "", $fast = false) {
        $return = array();
        foreach (iDB::all($fast && iDB::version() >= 5
            ? "SELECT TABLE_NAME AS Name, Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() " . ($name != "" ? "AND TABLE_NAME = " . iDB::quote($name) : "ORDER BY Name")
            : "SHOW TABLE STATUS" . ($name != "" ? " LIKE " . iDB::quote(addcslashes($name, "%_\\")) : "")
        ) as $row) {
            if ($row["Engine"] == "InnoDB") {
                // ignore internal comment, unnecessary since MySQL 5.1.21
                $row["Comment"] = preg_replace('~(?:(.+); )?InnoDB free: .*~', '\\1', $row["Comment"]);
            }
            if (!isset($row["Engine"])) {
                $row["Comment"] = "";
            }
            if ($name != "") {
                return $row;
            }
            $return[$row["Name"]] = $row;
        }
        return $return;
    }
    public static function fields($table) {
        $return = array();
        $rs = iDB::all("SHOW FULL FIELDS FROM " . self::table($table));
        foreach ( $rs as $row) {
            preg_match('~^([^( ]+)(?:\\((.+)\\))?( unsigned)?( zerofill)?$~', $row["Type"], $match);
            $return[$row["Field"]] = array(
                "field"          => $row["Field"],
                "full_type"      => $row["Type"],
                "type"           => $match[1],
                "length"         => $match[2],
                "unsigned"       => ltrim($match[3] . $match[4]),
                "default"        => ($row["Default"] != "" || preg_match("~char|set~", $match[1]) ? $row["Default"] : null),
                "null"           => ($row["Null"] == "YES"),
                "auto_increment" => ($row["Extra"] == "auto_increment"),
                "on_update"      => (preg_match('~^on update (.+)~i', $row["Extra"], $match) ? $match[1] : ""), //! available since MySQL 5.1.23
                "collation"      => $row["Collation"],
                "privileges"     => array_flip(preg_split('~, *~', $row["Privileges"])),
                "comment"        => ($row["Comment"]?$row["Comment"]:strtoupper($row["Field"])),
                // "callback"       => (preg_match('~\[F:(.+)\]~i', $row["Comment"], $match) ? $match[1] : ""),
                "primary"        => ($row["Key"] == "PRI"),
            );
        }
        return $return;
    }
    /** Get table indexes
    * @param string
    * @param string Min_DB to use
    * @return array array($key_name => array("type" => , "columns" => array(), "lengths" => array(), "descs" => array()))
    */
    public static function indexes($table, $connection2 = null) {
        $return = array();
        $index = iDB::all("SHOW INDEX FROM " . self::table($table));
        foreach ((array)$index as $row) {
            $return[$row["Key_name"]]["type"] = ($row["Key_name"] == "PRIMARY" ? "PRIMARY" : ($row["Index_type"] == "FULLTEXT" ? "FULLTEXT" : ($row["Non_unique"] ? "INDEX" : "UNIQUE")));
            $return[$row["Key_name"]]["columns"][] = $row["Column_name"];
            $return[$row["Key_name"]]["lengths"][] = $row["Sub_part"];
            $return[$row["Key_name"]]["descs"][] = null;
        }
        return $return;
    }
    /** Get sorted grouped list of collations
    * @return array
    */
    public static function collations() {
        $return = array();
        foreach (iDB::all("SHOW COLLATION") as $row) {
            if ($row["Default"]) {
                $return[$row["Charset"]][-1] = $row["Collation"];
            } else {
                $return[$row["Charset"]][] = $row["Collation"];
            }
        }
        ksort($return);
        foreach ($return as $key => $val) {
            asort($return[$key]);
        }
        return $return;
    }
    /** Find out if database is information_schema
    * @param string
    * @return bool
    */
    public static function information_schema($db) {
        $version = iDB::version();
        return ($version >= 5 && $db == "information_schema")
            || ($version >= 5.5 && $db == "performance_schema");
    }
    public static function partitioning($value=''){
        $partitioning = "";
        if ($partition_by[$row["partition_by"]]) {
            $partitions = array();
            if ($row["partition_by"] == 'RANGE' || $row["partition_by"] == 'LIST') {
                foreach (array_filter($row["partition_names"]) as $key => $val) {
                    $value = $row["partition_values"][$key];
                    $partitions[] = "\n  PARTITION " . idf_escape($val) . " VALUES " . ($row["partition_by"] == 'RANGE' ? "LESS THAN" : "IN") . ($value != "" ? " ($value)" : " MAXVALUE"); //! SQL injection
                }
            }
            $partitioning .= "\nPARTITION BY $row[partition_by]($row[partition])" . ($partitions // $row["partition"] can be expression, not only column
                ? " (" . implode(",", $partitions) . "\n)"
                : ($row["partitions"] ? " PARTITIONS " . (+$row["partitions"]) : "")
            );
        } elseif (self::support("partitioning") && preg_match("~partitioned~", $table_status["Create_options"])) {
            $partitioning .= "\nREMOVE PARTITIONING";
        }
    }
    /** Generate modifier for auto increment column
    * @return string
    */
    function auto_increment() {
        $auto_increment_index = " PRIMARY KEY";
        // don't overwrite primary key by auto_increment
        if ($_GET["create"] != "" && $_POST["auto_increment_col"]) {
            foreach (self::indexes($_GET["create"]) as $index) {
                if (in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"], $index["columns"], true)) {
                    $auto_increment_index = "";
                    break;
                }
                if ($index["type"] == "PRIMARY") {
                    $auto_increment_index = " UNIQUE";
                }
            }
        }
        return " AUTO_INCREMENT$auto_increment_index";
    }
    /** Run commands to create or alter table
    * @param string "" to create
    * @param string new name
    * @param array of array($orig, $process_field, $after)
    * @param array of strings
    * @param string
    * @param string
    * @param string
    * @param string number
    * @param string
    * @return bool
    */
    public static function alter_table2($table, $name, $fields, /*$foreign,*/ $comment, $auto_increment, $engine='MyISAM', $collation='utf8_general_ci',$partitioning='') {
        $alter = array();
        foreach ($fields as $field) {
            $alter[] = ($field[1]
                ? ($table != "" ? ($field[0] != "" ? "CHANGE " . self::idf_escape($field[0]) : "ADD") : " ") . " " . implode($field[1]) . ($table != "" ? $field[2] : "")
                : "DROP " . self::idf_escape($field[0])
            );
        }
        // $alter = array_merge($alter, $foreign);
        $status = ($comment !== null ? " COMMENT=" . iDB::quote($comment) : "")
            . ($engine ? " ENGINE=" . iDB::quote($engine) : "")
            . ($collation ? " COLLATE " . iDB::quote($collation) : "")
            . ($auto_increment != "" ? " AUTO_INCREMENT=$auto_increment" : "")
        ;
        if ($table == "") {
            return iDB::query("CREATE TABLE " . self::table($name) . " (\n" . implode(",\n", $alter) . "\n)$status$partitioning");
        }
        if ($table != $name) {
            $alter[] = "RENAME TO " . self::table($name);
        }
        if ($status) {
            $alter[] = ltrim($status);
        }
        return ($alter || $partitioning ? iDB::query("ALTER TABLE " . self::table($table) . "\n" . implode(",\n", $alter) . $partitioning) : true);
    }
    /** Run commands to alter indexes
    * @param string escaped table name
    * @param array of array("index type", "name", array("column definition", ...)) or array("index type", "name", "DROP")
    * @return bool
    */
    public static function alter_indexes($table, $alter) {
        foreach ($alter as $key => $val) {
            $alter[$key] = ($val[2] == "DROP"
                ? "\nDROP INDEX " . self::idf_escape($val[1])
                : "\nADD $val[0] " . ($val[0] == "PRIMARY" ? "KEY " : "") . ($val[1] != "" ? idf_escape($val[1]) . " " : "") . "(" . implode(", ", $val[2]) . ")"
            );
        }
        return iDB::query("ALTER TABLE " . self::table($table) . implode(",", $alter));
    }
    /** Run commands to truncate tables
    * @param array
    * @return bool
    */
    public static function truncate_tables($tables) {
        return iDB::query("TRUNCATE TABLE ". $tables);
    }
    /** Drop tables
    * @param array
    * @return bool
    */
    public static function drop_tables($tables) {
        return iDB::query("DROP TABLE " . implode(", ", array_map(array(self,'table'), $tables)));
    }
    /** Move tables to other schema
    * @param array
    * @param array
    * @param string
    * @return bool
    */
    public static function move_tables($tables, $target) {
        $rename = array();
        foreach ($tables as $table) { // views will report SQL error
            $rename[] = self::table($table) . " TO " . self::idf_escape($target) . "." . self::table($table);
        }
        return iDB::query("RENAME TABLE " . implode(", ", $rename));
        //! move triggers
    }
    /** Copy tables to other schema
    * @param array
    * @param array
    * @param string
    * @return bool
    */
    public static function copy_tables($tables) {
        iDB::query("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
        foreach ($tables as $table) {
            $name = self::table("copy_$table");
            if (!iDB::query("\nDROP TABLE IF EXISTS $name")
                || !iDB::query("CREATE TABLE $name LIKE " . self::table($table))
                || !iDB::query("INSERT INTO $name SELECT * FROM " . self::table($table))
            ) {
                return false;
            }
        }
        return true;
    }
    /** Get SQL command to create table
    * @param string
    * @param bool
    * @return string
    */
    public static function create_sql($table, $auto_increment) {
        $return = iDB::all("SHOW CREATE TABLE " . self::table($table), 1);
        if (!$auto_increment) {
            $return = preg_replace('~ AUTO_INCREMENT=\\d+~', '', $return); //! skip comments
        }
        return $return;
    }
    /** Get server variables
    * @return array ($name => $value)
    */
    public static function show_variables() {
        return iDB::all("SHOW VARIABLES");
    }
    /** Get process list
    * @return array ($row)
    */
    public static function process_list() {
        return iDB::all("SHOW FULL PROCESSLIST");
    }
    public static function kill_process($val) {
        return iDB::query("KILL " . int($val));
    }
    /** Get status variables
    * @return array ($name => $value)
    */
    public static function show_status() {
        return iDB::all("SHOW STATUS");
    }
    /** Escape database identifier
    * @param string
    * @return string
    */
    public static function idf_escape($idf) {
        return "`" . str_replace("`", "``", $idf) . "`";
    }
    public static function idf_unescape($idf) {
        $last = substr($idf, -1);
        return str_replace($last . $last, $last, substr($idf, 1, -1));
    }
    /** Get escaped table name
    * @param string
    * @return string
    */
    public static function table($idf) {
        return self::idf_escape($idf);
    }
    /** Check whether a feature is supported
    * @param string "comment", "copy", "database", "drop_col", "dump", "event", "kill", "materializedview", "partitioning", "privileges", "procedure", "processlist", "routine", "scheme", "sequence", "status", "table", "trigger", "type", "variables", "view", "view_trigger"
    * @return bool
    */
    function support($feature) {
        $version = iDB::version();
        return !preg_match("~scheme|sequence|type|view_trigger" . ($version < 5.1 ? "|event|partitioning" . ($version < 5 ? "|routine|trigger|view" : "") : "") . "~", $feature);
    }
    public static function vars() {
        $types = array(); ///< @var array ($type => $maximum_unsigned_length, ...)
        $structured_types = array(); ///< @var array ($description => array($type, ...), ...)
        foreach (array(
            'Numbers' => array("tinyint" => 3, "smallint" => 5, "mediumint" => 8, "int" => 10, "bigint" => 20, "decimal" => 66, "float" => 12, "double" => 21),
            'Date and time' => array("date" => 10, "datetime" => 19, "timestamp" => 19, "time" => 10, "year" => 4),
            'Strings' => array("char" => 255, "varchar" => 65535, "tinytext" => 255, "text" => 65535, "mediumtext" => 16777215, "longtext" => 4294967295),
            'Lists' => array("enum" => 65535, "set" => 64),
            'Binary' => array("bit" => 20, "binary" => 255, "varbinary" => 65535, "tinyblob" => 255, "blob" => 65535, "mediumblob" => 16777215, "longblob" => 4294967295),
            'Geometry' => array("geometry" => 0, "point" => 0, "linestring" => 0, "polygon" => 0, "multipoint" => 0, "multilinestring" => 0, "multipolygon" => 0, "geometrycollection" => 0),
        ) as $key => $val) {
            $types += $val;
            $structured_types[$key] = array_keys($val);
        }
        $unsigned = array("unsigned", "zerofill", "unsigned zerofill"); ///< @var array number variants
        $operators = array("=", "<", ">", "<=", ">=", "!=", "LIKE", "LIKE %%", "REGEXP", "IN", "IS NULL", "NOT LIKE", "NOT REGEXP", "NOT IN", "IS NOT NULL", "SQL"); ///< @var array operators used in select
        $functions = array("char_length", "date", "from_unixtime", "lower", "round", "sec_to_time", "time_to_sec", "upper"); ///< @var array functions used in select
        $grouping = array("avg", "count", "count distinct", "group_concat", "max", "min", "sum"); ///< @var array grouping functions used in select
        $edit_functions = array( ///< @var array of array("$type|$type2" => "$function/$function2") functions used in editing, [0] - edit and insert, [1] - edit only
            array(
                "char" => "md5/sha1/password/encrypt/uuid", //! JavaScript for disabling maxlength
                "binary" => "md5/sha1",
                "date|time" => "now",
            ), array(
                "(^|[^o])int|float|double|decimal" => "+/-", // not point
                "date" => "+ interval/- interval",
                "time" => "addtime/subtime",
                "char|text" => "concat",
            )
        );
        var_dump($structured_types,$edit_functions);
    }
}
