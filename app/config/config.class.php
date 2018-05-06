<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class config{
    public static $appid = 0;
    public static $data  = array();

    /**
     * [cache 更新配置]
     * @return [type] [description]
     */
    public static function cache(){
        $config         = self::get();
        $config['apps'] = apps::get_appsid();
        $config['iurl'] = apps::get_iurl();
        $config['router']['config'] = apps::router_cache();
        self::write($config);
    }
    public static function head($title=null,$action="config"){
        include admincp::view("config.head","config");
    }
    public static function foot(){
        include admincp::view("config.foot","config");
    }
    /**
     * [app 其它应用配置接口]
     * @param  integer $appid [应用ID]
     * @param  [sting] $name   [应用名]
     */
    public static  function app($appid=0,$name=null,$ret=false,$suffix="config"){
        $name===null && $name = admincp::$APP_NAME;
        if(empty($appid) && self::$appid){
            $appid = self::$appid;
        }
        empty($appid) && iUI::alert("配置程序出错缺少APPID!");

        $config = self::get($appid,$name);
        if($ret){
            return $config;
        }
        include admincp::view($name.'.'.$suffix);
    }
    /**
     * [save 其它应用配置保存]
     * @param  integer $appid [应用ID]
     * @param  [sting] $app   [应用名]
     */
    public static function save($appid=0,$name=null,$handler=null,$dialog=true){
        $name===null   && $name = admincp::$APP_NAME;
        if(empty($appid) && self::$appid){
            $appid = self::$appid;
        }
        empty($appid) && iUI::alert("配置程序出错缺少APPID!");
        $config = iSecurity::escapeStr($_POST['config']);
        self::set($config,$name,$appid,false);
        $handler && iPHP::callback($handler,array($config));
        self::cache();
        $dialog && iUI::success('配置更新完成','js:1');
    }

    public static function post() {
        $_POST['config'] = array_merge((array)self::$data,(array)$_POST['config']);
    }
    /**
     * [get 获取配置]
     * @param  integer $appid [应用ID]
     * @param  [type]  $name   [description]
     * @return [type]       [description]
     */
    public static function get($appid = NULL, $name = NULL) {
        $appid && self::$appid = $appid;
        if ($name === NULL) {
            $sql = "appid< '999999'";
            $appid === NULL OR $sql = " AND `appid`='$appid'";
            $rs  = iDB::all("SELECT * FROM `#iCMS@__config` WHERE $sql");
            foreach ((array)$rs AS $c) {
                $value = $c['value'];
                // strpos($c['value'], 'a:')===false OR $value = serialize($c['value']);
                $value = (array)json_decode($value,true);
                $config[$c['name']] = $value;
            }
            self::$data = $config;
            return $config;
        } else {
            $value = iDB::value("SELECT `value` FROM `#iCMS@__config` WHERE `appid`='$appid' AND `name` ='$name'");
            // strpos($value, 'a:')===false OR $value = unserialize($value);
            $value = (array)json_decode($value,true);
            self::$data = $value;
            return $value;
        }
    }
    /**
     * [set 更新配置]
     * @param [type]  $v     [description]
     * @param [type]  $n     [description]
     * @param [type]  $appid   [description]
     * @param boolean $cache [description]
     */
    public static function set($value, $name, $appid, $cache = false) {
        $cache && iCache::set('config/' . $name, $value, 0);
        // is_array($value) && $value = addslashes(serialize($value));
        is_array($value) && $value = addslashes(cnjson_encode($value));
        $check  = iDB::value("SELECT `name` FROM `#iCMS@__config` WHERE `appid` ='$appid' AND `name` ='$name'");
        $fields = array('appid','name','value');
        $data   = compact ($fields);
        if($check===null){
            iDB::insert('config',$data);
        }else{
            iDB::update('config', $data, array('appid'=>$appid,'name'=>$name));
        }
    }
    public static function del($name, $appid) {
        if($name &&$appid){
            iDB::query("DELETE FROM `#iCMS@__config` WHERE `appid` ='$appid' AND `name` ='$name'");
        }
    }
    /**
     * [write 配置写入文件]
     * @param  [type] $config [description]
     * @return [type]         [description]
     */
    public static function write($config=null){
        $config===null && $config = self::get();
        $output = "<?php\ndefined('iPHP') OR exit('Access Denied');\nreturn ";
        $output.= var_export($config,true);
        $output.= ';';
        iFS::write(iPHP_APP_CONFIG,$output);
    }
    /**
     * [update 单个配置更新]
     * @param  [type] $k [description]
     * @return [type]    [description]
     */
    public static function update($k,$appid=0){
        self::set(iCMS::$config[$k],$k,$appid);
        self::cache();
    }
    public static function view(){
        include admincp::view('config',null,true);
    }
}
