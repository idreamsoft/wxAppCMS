<?php

class Redis_client{
    const TIMEOUT     = 200;
    protected $host   = '127.0.0.1';
    protected $port   = 6379;
    protected $db     = 0;
    protected $passwd = '';
    protected $schema = array();
    protected $history= array();
    protected $socket;
    protected $compress;
    protected $_have_zlib;
    protected static $instace;

    public static function get_instance($config = array())
    {
        if(empty(self::$instace)){
            self::$instace = new self($config);
        }
        return self::$instace;
    }

    public function __construct($config = array())
    {
        foreach($config as $key => $val){
            $this->$key = $val;
        }
        $this->history[] = $this->db;
        $this->_have_zlib = function_exists("gzcompress");
    }

    public function new_connect($config = array())
    {
        $r = clone $this;
        $r->socket = null;
        foreach($config as $key => $val){
            $r->$key = $val;
        }
        return $r;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function auth($pass)
    {
        $this->write('AUTH', $pass);
        return $this->get_response();
    }

    public function quit()
    {
        $this->write('QUIT');
        return $this->get_response();
    }

    public function ping()
    {
        $this->write('PING');
        return $this->get_response();
    }

    public function do_echo($s)
    {
        $this->write('ECHO', $s);
        return $this->get_response();
    }

    public function exists($name)
    {
        $this->write('EXISTS',$name);
        return (bool)$this->get_response();
    }

    public function delete($name)
    {
        $this->write('DEL', $name);
        return $this->get_response();
    }

    public function type($name)
    {
        $this->write('TYPE', $name);
        return $this->get_response();
    }

    public function keys($pattern)
    {
        $this->write('KEYS', $pattern);
        return  $this->get_response();
    }

    public function randomkey()
    {
        $this->write('RANDOMKEY');
        return $this->get_response();
    }

    public function rename($src, $dst)
    {
        $this->write('RENAME', $src, $dst);
        return $this->get_response();
    }

    public function renamenx($src, $dst)
    {
        $this->write('RENAMENX', $src, $dst);
        return $this->get_response();
    }

    public function dbsize()
    {
        $this->write('DBSIZE');
        return $this->get_response();
    }

    public function expire($name, $time)
    {
        $this->write('EXPIRE', $name, $time);
        return $this->get_response();
    }

    public function ttl($name)
    {
        $this->write('TTL', $name);
        return $this->get_response();
    }

    public function select($name)
    {
        if(!ctype_digit($name) && isset($this->schema[$name])){
            $name = $this->schema[$name];
        }
        $this->history[] = (int)$name;
        $this->write('SELECT', (int)$name);
        return $this->get_response();
    }

    public function back()
    {
        if(end($this->history) != ($name = prev($this->history))){
            return $this->select($name);
        }
        return true;
    }

    public function move($name, $db)
    {
        $this->write('MOVE', $name, $db);
        return $this->get_response();
    }

    public function flushdb($all=false)
    {
        $this->write($all ? 'FLUSHALL' : 'FLUSHDB');
        return $this->get_response();
    }

    public function flushall()
    {
        return $this->flush(true);
    }


    public function add($name, $value, $exp=0){
        $value = is_scalar($value) ? (string) $value : serialize($value);
        if ($this->_have_zlib && $this->compress){
            $value = gzcompress($value, 9);
        }
        $this->write('SET',$name, $value);
        $exp && $this->expire($name, $exp);
        return $this->get_response();
    }
    public function get($name){
        $this->write('GET', $name);
        $response = $this->get_response();
        if ($this->_have_zlib && $this->compress){
            $response = @gzuncompress($response);
        }
        $unresponse = unserialize($response);
        if(is_array($unresponse) && $response ){
            $response = $unresponse;
        }
        return $response;
    }
    public function get_multi($keys){
        $this->write('MGET', $keys);
        $response = $this->get_response();
        $unresponse = array_map('unserialize',$response);
        if(is_array($unresponse) && $response ){
            $response = $unresponse;
        }
        foreach($keys as $i =>$key){
            if ($this->_have_zlib && $this->compress){
                $response[$i] = @gzuncompress($res[$i]);
            }
            $value[$key] = $response[$i];
        }
        return $value;
    }
    /*
      Commands operating on string values
    */
    public function set($name, $value, $preserve=false)
    {
        $value = is_scalar($value) ? (string) $value : serialize($value);
        $this->write(($preserve ? 'SETNX' : 'SET') ,$name, $value);
        return $this->get_response();
    }

    public function Rget($name)
    {
        $this->write('GET', $name);
        $response = $this->get_response();
        if ($this->_have_zlib && $this->compress){
            $response = @gzuncompress($response);
        }
        $unresponse = unserialize($response);
        if(is_array($unresponse) && $response ){
            $response = $unresponse;
        }
        return $response;
    }

    public function getset($name, $value)
    {
        $this->write('GETSET', $name, is_scalar($value) ? (string) $value : serialize($value));
        return $this->get_response();
    }

    public function mget($keys, $format = '')
    {
        if($format != ''){
            foreach($keys as $i =>$key){
                $keys[$i] = sprintf($format, $key);
            }
        }
        $this->write('MGET', $keys);
        $response = $this->get_response();
        $unresponse = array_map('unserialize',$response);
        if(is_array($unresponse) && $response ){
            $response = $unresponse;
        }
        return $response;
    }

    public function incr($name, $amount=1)
    {
        if ($amount == 1)
            $this->write('INCR', $name);
        else
            $this->write('INCRBY', $name, $amount);
        return $this->get_response();
    }

    public function decr($name, $amount=1)
    {
        if ($amount == 1)
            $this->write('DECR', $name);
        else
            $this->write('DECRBY', $name, $amount);
        return $this->get_response();
    }

    public function push($name, $value, $tail=true)
    {
        $this->write(($tail ? 'RPUSH' : 'LPUSH') , $name, $value);
        return $this->get_response();
    }

    public function lpush($name, $value)
    {
        return $this->push($name, $value, false);
    }

    public function rpush($name, $value)
    {
        return $this->push($name, $value, true);
    }

    public function ltrim($name, $start, $end)
    {
        $this->write('LTRIM', $name ,$start, $end);
        return $this->get_response();
    }

    public function lindex($name, $index)
    {
        $this->write('LINDEX', $name, $index);
        return $this->get_response();
    }

    public function pop($name, $tail=true)
    {
        $this->write(($tail ? 'RPOP' : 'LPOP') ,$name);
        return $this->get_response();
    }

    public function lpop($name)
    {
        return $this->pop($name, false);
    }

    public function rpop($name)
    {
        return $this->pop($name, true);
    }

    public function llen($name)
    {
        $this->write('LLEN', $name);
        return $this->get_response();
    }

    public function lmembers($name, $count = 0)
    {
        $end = ($count = $this->llen($name));
        $this->write('LRANGE', $name, 0, $end);
        return $this->get_response();
    }

    public function lrange($name, $start, $end )
    {
        $this->write('LRANGE', $name, $start, $end);
        return $this->get_response();
    }

    public function sort($name, $query=false)
    {
        if($query){
            $this->write('SORT', $name, $query);
        }else{
            $this->write('SORT', $name);
        }
        return $this->get_response();
    }

    public function lset($name, $value, $index)
    {
        $this->write('LSET', $name, $index, $value);
        return $this->get_response();
    }

    public function save($background=false)
    {
        $this->write($background ? 'BGSAVE' : 'SAVE');
        return $this->get_response();
    }

    public function bgsave($background=false)
    {
        return $this->save(true);
    }

    public function lastsave()
    {
        $this->write('LASTSAVE');
        return $this->get_response();
    }

    public function info()
    {
        $this->write('INFO');
        $info = array();
        $data = $this->get_response();
        foreach (explode("\r\n", $data) as $l) {
            if (!$l){
                continue;
            }
            list($k, $v) = explode(':', $l, 2);
            $_v = strpos($v, '.') !== false ? (float)$v : (int)$v;
            $info[$k] = (string)$_v == $v ? $_v : $v;
        }
        return $info;
    }


    protected function connect()
    {
        if ($this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, 2)){

            if(!empty($this->passwd)){
                $this->auth($this->passwd);
            }

            if(!empty($this->db) && count($this->history) == 1){
                $this->select($this->db);
            }

            return;
        }

        trigger_error("Cannot open socket to {$this->host}:{$this->port}.", E_USER_ERROR);
    }

    public function disconnect()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->socket  = null;
        $this->history = array($this->db);
    }

    public function __call($method, $params)
    {
        array_unshift($params, strtoupper($method));
        call_user_func_array(array($this, 'write'), $params);
        return $this->get_response();
    }

    protected function write()
    {
        if (!$this->socket){
            $this->connect();
        }

        $args = func_get_args();
        $i = 0;
        $s = "\r\n";
        foreach($args as $a){
            if(is_array($a)){
                foreach($a as $b){
                    $s .= sprintf("$%d\r\n%s\r\n", strlen($b), $b);
                    $i++;
                }
            }else{
                $s .= sprintf("$%d\r\n%s\r\n", strlen($a), $a);
                $i++;
            }
        }
        $s = '*' . $i . $s;
        while ($s) {
            $i = fwrite($this->socket, $s);
            if ($i == 0){
                break;
            }
            $s = substr($s, $i);
        }
    }

    protected function read($len = 1024)
    {
        if ($s = fgets($this->socket)){
            return $s;
        }
        $this->disconnect();
        trigger_error("Cannot read from socket.", E_USER_ERROR);
    }

    protected function get_response()
    {
        $data = trim($this->read());
        $c    = $data[0];
        $data = substr($data, 1);
        switch ($c) {
            case '-':
                trigger_error($data, E_USER_ERROR);
                break;
            case '+':
                return $data;
            case ':':
                $i = strpos($data, '.') !== false ? (int)$data : (float)$data;
                if ((string)$i != $data)
                    trigger_error("Cannot convert data '$c$data' to integer", E_USER_ERROR);
                return $i;
            case '$':
                return $this->get_bulk_reply($c . $data);
            case '*':
                $num = (int)$data;
                if ((string)$num != $data)
                    trigger_error("Cannot convert multi-response header '$data' to integer", E_USER_ERROR);
                $result = array();
                for ($i=0; $i< $num; $i++){
                    $result[] = $this->get_response();
                }
                return $result;
            default:
                trigger_error("Invalid reply type byte: '$c'");
        }
    }

    protected function get_bulk_reply($data=null) {
        if ($data === null)
            $data = trim($this->read());
        if ($data == '$-1')
            return null;
        $c = $data[0];
        $data = substr($data, 1);
        $bulklen = (int)$data;
        if ((string)$bulklen != $data)
            trigger_error("Cannot convert bulk read header '$c$data' to integer", E_USER_ERROR);
        if ($c != '$')
            trigger_error("Unkown response prefix for '$c$data'", E_USER_ERROR);
        $buffer = '';
        while ($bulklen) {
            $data = fread($this->socket, $bulklen);
            $bulklen -= strlen($data);
            $buffer .= $data;
        }
        $crlf = fread($this->socket, 2);
        return $buffer;
    }
}

/*
    $r = new Redis(array(
        'host'     => '192.168.1.63',
        'port'     => 6379,
        'db'       => 3
    ));

    var_dump($r->set("foo","bar"));
    var_dump($r->set("foo2","bar"));
    var_dump($r->incr("foo3"));
    var_dump($r->get("foo3"));
    var_dump($r->get("foo"));
    var_dump($r->do_echo("foo"));
    var_dump($r->ping("foo"));
    var_dump($r->mget(array("foo",'foo2')));
*/


