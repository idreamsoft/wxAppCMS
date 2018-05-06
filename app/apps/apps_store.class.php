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

class apps_store {
    const STORE_URL = "https://store.icmsdev.com/v2";
    public static $zip_name  = null;
    public static $zip_file  = null;
    public static $is_git    = false;
    public static $is_update = false;
    public static $test      = false;
    public static $success   = false;
    public static $msg_mode  = null;
    public static $app_id    = null;
    public static $uptime    = 0;
    public static $authcode  = null;

    public static function download($url=null,$name,$zip_name=null) {
        self::$success = false;
        strpos($url, '/git/')!==false && self::$is_git = true;
        $cache_dir = iPATH . 'cache/iCMS/store/';
        iFS::mkdir($cache_dir);
        if(empty($zip_name)){
            $zip_name = basename($url);
            $zip_name = strstr($zip_name, '?', true);
        }
        self::$zip_file = $cache_dir . $zip_name; //临时文件

        $msg = self::msg('正在下载 [' . $name . '] 安装包',true);
        if (iFS::ex(self::$zip_file) && (filemtime(self::$zip_file)-time()<3600)) {
            $msg.= self::msg('安装包已存在',true);
            self::$success = true;
            return $msg;
        }
        $data = self::remote($url);
        if($data[0] == '{'){
            $array = json_decode($data,true);
            if($array){
                $msg.= self::msg($array['msg'],false);
            }else{
                $msg.= self::msg('下载出错误',false);
            }
        }else if ($data) {
            iFS::write(self::$zip_file, $data); //下载更新包
            $msg.= self::msg('安装包下载完成',true);
            self::$success = true;
        }else{
            $msg.= self::msg('下载出错误',false);
        }
        return $msg;
    }
    public static function install_template($dir=null) {
        self::$success = false;
        $archive_files = self::setup_zip();
        $msg = null;
        if ($archive_files) {
            $setup_msg = self::setup_template_file($archive_files,$dir);
            if(is_array($setup_msg)){
                $msg.= $setup_msg[0];
            }else{
                return self::msg($msg.$setup_msg);
            }
        }
        self::$test OR iFS::rm(self::$zip_file);
        $msg.= self::msg('模板安装完成',true);
        self::$success = true;
        return $msg;
    }

    public static function install_app($app=null) {
        self::$success = false;

        $archive_files = self::setup_zip();
        $msg = null;
        //安装应用数据
        $setup_msg = self::setup_app_data($archive_files,$app);

        if($setup_msg===true){
            $msg.= self::msg('应用数据安装完成',true);
        }else{
            if($setup_msg===false){
            }else{
                if(!self::$is_update){
                    return self::msg($setup_msg.'安装出错',false);
                }
            }
        }
        //创建应用表
        if(self::setup_app_table($archive_files,$app)){
            $msg.= self::msg('应用表创建完成',true);
        }

        if (count($archive_files)>0) {
            $setup_msg = self::setup_app_file($archive_files,$app);
            if(is_array($setup_msg)){
                $msg.= $setup_msg[0];
            }else{
                return self::msg($msg.$setup_msg);
            }
        }
        self::$test OR iFS::rm(self::$zip_file);

        if(self::$is_update){
            $msg.= self::setup_update($app);
        }else{
            $msg.= self::setup_install($app);
        }
        apps::cache() && $msg.= self::msg('更新应用缓存',true);
        menu::cache() && $msg.= self::msg('更新菜单缓存',true);
        $msg.= self::msg('应用安装完成',true);
        self::$success = true;
        return $msg;
    }
    public static function setup_template_file(&$archive_files,$dir){
        $msg = self::msg('正在对安装包进行解压缩',true);
        $msg.= self::msg('解压完成',true);
        $msg.= self::msg('开始测试目录权限',true);

        if (!iFS::checkDir(iPATH)) {
            return self::msg(iPATH.'根目录无写权限',false);
        }

        if (!iFS::checkDir(iPHP_TPL_DIR)) {
            return self::msg(iPHP_TPL_DIR . '目录无写权限',false);
        }

        $ROOTPATH = self::rootpath('TPL');
        self::$is_git OR $ROOTPATH.= '/'.$dir;

        $continue = self::extract_test($archive_files,$ROOTPATH,$msg);

        if (!$continue) {
            $msg.= self::msg('权限测试无法完成',false);
            $msg.= self::msg('请设置好上面提示的文件写权限',false);
            $msg.= self::msg('然后重新安装',false);
            return $msg;
        }
        $msg.= self::msg('权限测试通过',true);

        self::$test OR iFS::mkdir($ROOTPATH);
        $bakdir = self::create_bakdir($dir,$msg);

        $msg.= self::msg('开始安装模板',true);
        $msg.= self::extract($archive_files,$ROOTPATH,$bakdir);

        return array($msg,true);
    }
    public static function setup_app_data(&$archive_files,$app){
        foreach ($archive_files AS $key => $file) {
            $filename = basename($file['filename']);
            if($filename=="iCMS.APP.DATA.php"){
                unset($archive_files[$key]);

                $content = get_php_content($file['content']);
                $content = base64_decode($content);
                $array   = unserialize($content);

                $check_app = iDB::value("
                    SELECT `id` FROM `#iCMS@__apps`
                    WHERE `app` ='".$array['app']."'
                ");
                if($check_app){
                    $_msg = self::msg('检测应用是否存在',false);
                    return self::msg($_msg.'该应用已经存在',false);
                }

                if($array['table']){
                    $tableArray = apps::table_item($array['table']);
                    foreach ($tableArray AS $value) {
                      if(iDB::check_table($value['table'],false)){
                        $_msg = self::msg('检测应用表是否存在',false);
                        return self::msg($_msg.'['.$value['table'].']数据表已经存在');
                      }
                    }
                }

                $array['addtime'] = time();
                $array = array_map('addslashes', $array);
                self::$test OR self::$app_id = iDB::insert("apps",$array);
                return true;
            }
        }
        return false;
    }
    public static function setup_app_table(&$archive_files){
        foreach ($archive_files AS $key => $file) {
            $filename = basename($file['filename']);
            if($filename=="iCMS.APP.TABLE.php"){
                unset($archive_files[$key]);

                $content = get_php_content($file['content']);
                if(strpos($content, 'IF NOT EXISTS')===false){
                    $content = str_replace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `', $content);
                    $content = str_replace('create table `', 'CREATE TABLE IF NOT EXISTS `', $content);
                }
                if(!self::$test){
                    $content && apps_db::multi_query($content);
                }
                return true;
            }
        }
        return false;
    }
    public static function setup_app_file(&$archive_files,$app){
        $msg = self::msg('正在对安装包进行解压缩',true);
        $msg.= self::msg('解压完成',true);
        $msg.= self::msg('开始测试目录权限',true);

        if (!iFS::checkDir(iPATH)) {
            return self::msg(iPATH.'根目录无写权限',false);
        }

        if (!iFS::checkDir(iPHP_APP_DIR)) {
            return self::msg(iPHP_APP_DIR . '目录无写权限',false);
        }

        if (!iFS::checkDir(iPHP_TPL_DIR)) {
            return self::msg(iPHP_TPL_DIR . '模板无写权限',false);
        }

        $ROOTPATH = self::rootpath();
        //测试目录文件是否写
        $continue = self::extract_test($archive_files,$ROOTPATH,$msg);
        if (!$continue) {
            $msg.= self::msg('权限测试无法完成',false);
            $msg.= self::msg('请设置好上面提示的文件写权限',false);
            $msg.= self::msg('然后重新安装',false);
            return $msg;
        }
        $msg.= self::msg('权限测试通过',true);
        $bakdir = self::create_bakdir($app,$msg);
        $msg.= self::msg('开始安装应用',true);
        $msg.= self::extract($archive_files,$ROOTPATH,$bakdir);
        return array($msg,true);
    }
    public static function setup_update($app){
        $ROOTPATH = iPHP_APP_DIR.'/'.$app.'/';
        foreach (glob($ROOTPATH."iCMS.APP.UPDATE.*.php") as $filename) {
            $d    = str_replace(array($ROOTPATH,'iCMS.APP.UPDATE.','.php'), '', $filename);
            $time = strtotime($d.'00');
            if($time>self::$uptime){
                $files[$d] = $filename;
            }
        }

        if($files){
            ksort($files);
            foreach ($files as $key => $file) {
                $name = $app.'_'.str_replace(array('.php','.'), array('','_'), basename($file));
                $msg.= self::setup_exec($file,$name,'升级');
            }
        }
        return $msg;
    }
    public static function setup_zip() {
        $zip_file = self::$zip_file;
        if(!file_exists($zip_file)){
            return self::msg("安装包不存在",false);
        }

        iPHP::vendor('PclZip');
        $zip = new PclZip($zip_file);
        if (false == ($archive_files = $zip->extract(PCLZIP_OPT_EXTRACT_AS_STRING))) {
            iFS::rm($zip_file);
            return self::msg("ZIP包错误",false);
        }

        if (0 == count($archive_files)) {
            iFS::rm($zip_file);
            return self::msg("空的ZIP文件",false);
        }
        return $archive_files;
    }
    public static function setup_install($app){
        $path = iPHP_APP_DIR.'/'.$app.'/iCMS.APP.INSTALL.php';
        if(is_file($path)){
            return self::setup_exec($path,$app,'安装');
        }
    }
    public static function setup_exec($file,$name,$title='升级') {
        $func = require_once $file;
        if(is_callable($func)){
            $msg = self::msg('执行['.$name.']'.$title.'程序',true);
            try {
                self::$test OR $msg .= $func();
                $msg.= self::msg($title.'顺利完成!',true);
                $msg.= self::msg('删除'.$title.'程序!',true);
            } catch ( Exception $e ) {
                $msg.= self::msg('['.$name.']'.$title.'出错',false);
            }
        }else{
            $msg= self::msg('['.$name.']'.$title.'出错',false);
            $msg.= self::msg('找不到'.$title.'程序',false);
        }
        iFS::del($file);
        return $msg;
    }
    public static function setup_func($func) {
        return $func;
    }
    public static function rootpath($d='APP'){
        $d=='APP' && $dir = iPHP_APP_DIR;
        $d=='TPL' && $dir = iPHP_TPL_DIR;

        $ROOTPATH = self::$is_git?iPATH:$dir;
        return rtrim($ROOTPATH,DIRECTORY_SEPARATOR);
    }
    public static function check_must($store){
      if(empty($store)){
        iUI::alert('请求出错','js:1',10);
      }
      if(empty($store['code'])){
          iUI::alert($store['msg'],'js:1',10);
      }
    }
    public static function msg($text,$s=0){
        $text = iSecurity::filter_path($text);
        if(self::$msg_mode=='alert'){
            $s OR iUI::alert($text);
        }else{
            return str_pad($text,80,'.').iUI::check($s).'<br />';
        }
    }
    public static function create_bakdir($a,&$msg){
        $bakdir = iPATH.'.backup/'.$a.'_'.date("Ymd");
        iFS::mkdir($bakdir) && $msg.= self::msg('备份目录创建完成',true);
        return $bakdir;
    }
    public static function extract_test($archive_files,$ROOTPATH,&$msg){
        $continue = true;
        if($archive_files)foreach ($archive_files as $file) {
            $folder = $file['folder'] ? $file['filename'] : dirname($file['filename']);
            $dp = $ROOTPATH .'/'.trim($folder,'/').'/';
            if (!iFS::checkdir($dp) && iFS::ex($dp)) {
                $continue = false;
                $msg .= self::msg($dp . '目录无写权限',false);
            }
            if (empty($file['folder'])) {
                $fp = $ROOTPATH .'/'. $file['filename'];
                if (file_exists($fp) && !@is_writable($fp)) {
                    $continue = false;
                    $msg.= self::msg($fp . '文件无写权限',false);
                }
            }
        }
        $msg = iSecurity::filter_path($msg);
        return array($msg,$continue);
    }
    public static function extract($archive_files,$ROOTPATH,$bakdir=null){
        foreach ($archive_files as $file) {
            $folder = $file['folder'] ? $file['filename'] : dirname($file['filename']);
            $dp = $ROOTPATH .'/'.trim($folder,'/').'/';

            if (!iFS::ex($dp)) {
                self::$test OR iFS::mkdir($dp);
                $msg.= self::msg('创建文件夹 [' . $dp . ']',true);
            }
            if (!$file['folder']) {
                $fp = $ROOTPATH .'/'. $file['filename'];
                if($bakdir){
                    $bfp = $bakdir . '/' . $file['filename'];
                    iFS::backup($fp,$bfp) && $msg.= self::msg('备份 [' . $fp . '] 文件 到 [' . $bfp . ']',true);
                }
                self::$test OR iFS::write($fp, $file['content']);
                $msg.= self::msg('安装文件 [' . $fp . ']',true);
            }
        }
        $msg = iSecurity::filter_path($msg);
        return $msg;
    }
    public static function get($vars=0,$field='sid'){
        if(empty($vars)) return array();
        if($vars=='all'){
            $sql      = '1=1';
            $is_multi = true;
        }else{
            list($vars,$is_multi)  = iSQL::multi_var($vars);
            $sql  = iSQL::in($vars,$field,false,true);
        }
        $data = array();
        $rs   = iDB::all("SELECT * FROM `#iCMS@__apps_store` where {$sql}");
        if($rs){
            $_count = count($rs);
            for ($i=0; $i < $_count; $i++) {
                $data[$rs[$i][$field]]= $rs[$i];
            }
            $is_multi OR $data = $data[$vars];
        }
        if(empty($data)){
            return;
        }
        return $data;
    }
    public static function get_array($vars,$field="*",$orderby=''){
        $sql = iSQL::where($vars,false);
        $sql.= 'order by '.($orderby?$orderby:'id ASC');
        $rs  = iDB::all("SELECT {$field} FROM `#iCMS@__apps_store` where {$sql}");
        $_count = count($rs);
        for ($i=0; $i < $_count; $i++) {
            $data[$rs[$i]['sid']] = $rs[$i];
        }
        return $data;
    }
    public static function remote($url){
        iHttp::$CURLOPT_TIMEOUT        = 60;
        iHttp::$CURLOPT_CONNECTTIMEOUT = 10;
        iHttp::$CURLOPT_REFERER        = $_SERVER['HTTP_REFERER'];
        iHttp::$CURLOPT_USERAGENT      = $_SERVER['HTTP_USER_AGENT'];
        iHttp::$CURLOPT_HTTPHEADER     = array('AUTHORIZATION: '.self::$authcode);

        $queryArray  = array(
            'iPHP_APP'     => 'wxAppCMS',
            'iCMS_VERSION' => iCMS_VERSION,
            'iCMS_RELEASE' => iCMS_RELEASE,
            'iCMS_HASH'    => iCMS_HASH,
            'GIT_COMMIT'   => GIT_COMMIT,
            'GIT_TIME'     => GIT_TIME,
            'iCMS_HOST'    => $_SERVER['HTTP_HOST'],
        );
        $url  = iURL::make($queryArray,$url);
        $data = iHttp::remote($url);
        return $data;
    }
    public static function remote_update($store) {
        $url = self::STORE_URL.'/git/update?sid='.$store['sid']
            ."&version=".$store['version']
            .'&git_sha=' .$store['git_sha']
            .'&git_time=' .$store['git_time']
            .'&authkey='.$store['authkey']
            .'&transaction_id='.$store['transaction_id'];

        self::$authcode = $store['data'];
        $json  = self::remote($url);
        $array = json_decode($json,true);
        return $array;
    }
    public static function remote_send($sid,$do='get',$add=null){
        $time  = time();
        $host  = $_SERVER['HTTP_HOST'];
        $key   = md5(iPHP_KEY.$host.$time);
        $query = compact(array('sid','key','host','time'));
        $add && $query = array_merge($query,$add);
        $url   = self::STORE_URL.'/'.$do.'/'.$sid.'?'.http_build_query($query);
        $json  = self::remote($url);
        $array = json_decode($json,true);
        return $array;
    }
    public static function remote_all($name='app'){
        $data = array();
        $url  = self::STORE_URL.'/all/'.$name;
        isset($_GET['premium']) && $url.='?premium='.$_GET['premium'];
        $json = self::remote($url);
        $json && $data = json_decode($json,true);
        return $data;
    }
    public static function pay_notify(){
        $query = array(
            'authkey' => $_GET['authkey'],
            'sid'     => $_GET['sid']
        );
        $url = self::STORE_URL.'/payment/notify?'.http_build_query($query);
        echo self::remote($url);
    }
    public static function premium_dialog($sid,$array,$title){
        iUI::$break                = false;
        iUI::$dialog['quickClose'] = false;
        iUI::$dialog['modal']      = true;
        iUI::$dialog['ok']         = true;
        iUI::$dialog['cancel']     = true;
        iUI::$dialog['ok:js']      = 'window.parent.clear_pay_notify_timer();';
        iUI::$dialog['cancel:js']  = 'window.parent.clear_pay_notify_timer();';
        iUI::dialog($array['dialog_html'],'js:1',1000000);

        echo '<script type="text/javascript">
        var j = '.json_encode(array($array['authkey'],$sid)).';
        window.parent.pay_notify(j,d);
        </script>';
    }
    public static function setup($url,$store,$local=null){
        @set_time_limit(0);
        iUI::close_dialog();
        $sid = 0;
        if(self::$is_update && $local){
            $sid = $local['sid'];
            apps_store::$authcode = $local['data'];
            $query = array('sid'=>$local['sid'],'authkey'=>$local['authkey']);
            $store['premium'] && $query['transaction_id'] = $local['transaction_id'];
            $query['store_update'] = 1;
        }else{
            apps_store::$authcode = $store['authcode'];
            $query = array('sid'=>$store['id'],'authkey'=>$store['authkey']);
            $store['premium'] && $query['transaction_id'] = $_GET['transaction_id'];
        }
        $zipname = md5($store['authkey'].$local['authcode']).'.zip';
        $zipurl  = apps_store::STORE_URL.$url.'?'.http_build_query($query);
        $msg     = self::download($zipurl,$store['name'],$zipname);
        self::$success OR iUI::dialog($msg,'js:1',1000000);

        $data = array_merge($query,array(
          'app'      => $store['app'],
          'name'     => $store['name'],
          'git_time' => $store['git_time'],
          'git_sha'  => $store['git_sha'],
          'version'  => $store['version'],
          'type'     => $store['type'],
          'authkey'  => $store['authkey'],
          'data'     => $store['authcode']
        ));

        switch ($store['type']) {
            case '0':
                $msg.= self::install_app($store['app']);
                if(self::$success){
                    self::$app_id && $data['appid'] = self::$app_id;
                    self::save($data,$sid);
                    iUI::dialog(
                        '<div style="overflow-y: auto;max-height: 500px;">'.$msg.'</div>',
                        (
                            self::$is_update?
                            'js:1'://更新
                            'url:'.__ADMINCP__."=apps&do=add&id=".self::$app_id
                        ),30
                    );
                    return true;
                }
            break;
            case '1':
                $msg.= self::install_template($store['app']);
                if(self::$success){
                    $data['appid'] = 0;
                    self::save($data,$sid);
                    iUI::dialog(
                      '<div style="overflow-y: auto;max-height: 500px;">'.$msg.'</div>',
                      'js:1',1000000
                    );
                    return true;
                }
            break;
        }
        iUI::dialog('<div style="overflow-y: auto;max-height: 500px;">'.$msg.'</div>','js:1',1000000);
    }

    public static function del($id=null,$field='appid',$send=true){
        if(isset($_GET['sid']) && $field!='sid'){
            $id    = (int)$_GET['sid'];
            $field = 'sid';
        }
        iDB::query("DELETE FROM `#iCMS@__apps_store` WHERE `$field` = '$id'");
        $send && self::remote_send($id,'del');
    }
    public static function save($array,$sid=null){
        $fields = array('sid', 'appid', 'app', 'name', 'version',
            'authkey', 'git_sha', 'git_time',
            'transaction_id', 'data',
            'addtime', 'uptime', 'type', 'status'
        );
        $data = compact($fields);
        $data = array_merge($data,$array);
        iSQL::filter_data($data,$fields);

        if(self::$is_update){
            $data['uptime'] = time();
            unset($data['appid'],$data['authkey'],$data['data']);//不更新
            iDB::update('apps_store',$data,array('sid'=>$sid));
        }else{
            $data['addtime'] = time();
            iDB::insert('apps_store',$data);
        }
    }

}
