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
class iView {
    public static $handle   = NULL;
    public static $app      = null;
    public static $gateway  = null;
    public static $config   = array();
    public static $template = array();

    public static function init($config = array()) {
        self::$config   = $config;
        self::$handle = self::Template();
        self::$handle->assign('_GET', $_GET);
        self::$handle->assign('_POST', $_POST);

        iPHP_TPL_DEBUG && self::$handle->clear_compiled_tpl();
    }
    public static function Template() {
        $tpl = new iTemplateLite();
        $tpl->debugging    = iPHP_TPL_DEBUGGING;
        $tpl->template_dir = iPHP_TPL_DIR;
        $tpl->compile_dir  = iPHP_TPL_CACHE;
        $tpl->reserved_template_varname = iPHP_TPL_VAR;
        $tpl->error_reporting_header    = "<?php defined('iPHP') OR exit('What are you doing?');error_reporting(iPHP_TPL_DEBUG?E_ALL & ~E_NOTICE:0);?>\n";
        $tpl->left_delimiter  = '<!--{';
        $tpl->right_delimiter = '}-->';
        $tpl->register_modifier("date", "get_date");
        $tpl->register_modifier("cut", "csubstr");
        $tpl->register_modifier("htmlcut", "htmlcut");
        $tpl->register_modifier("cnlen", "cstrlen");
        $tpl->register_modifier("html2txt", "html2text");
        $tpl->register_modifier("key2num", "key2num");
        $tpl->register_modifier("unicode", "get_unicode");
        $tpl->register_modifier("random", "random");
        $tpl->register_modifier("fields", "select_fields");
        $tpl->register_modifier("pinyin",array("iPinyin", "get"));
        $tpl->register_modifier("thumb", array("files", "thumb"));
        $tpl->register_block("cache", array(__CLASS__, "block_cache"));
        $tpl->template_callback = array(
            "resource" => array(__CLASS__,"callback_resource"),
            "func"     => array(__CLASS__,"callback_func"),
            "plugin"   => array(__CLASS__,"callback_plugin"),
            // "output"   => array(__CLASS__,"callback_output"),
        );
        return $tpl;
    }
    public static function set_template_dir($dir) {
        self::$handle->template_dir = $dir;
    }
    public static function check_func($app) {
        $path = iPHP_APP_DIR . '/' . $app . '/' . $app . '.func.php';
        return is_file($path);
    }
    // public static function callback_output(&$content,$obj) {
    //     if(!self::$config['callback']['output']){
    //         $content.= publicFunc::public_crontab(true);
    //     }
    // }
    /**
     * iPHP:app:method
     * iPHP:func
     * iPHP:aaaApp:method
     */
    public static function callback_func($args,$tpl) {
        is_array($args['app']) && $args['app'] = $args['app']['app'];
        $keys = $args['app'].($args['method']?'_'.$args['method']:'');
        isset($args['as']) && $keys = $args['as'];
        //模板标签 对应>> 类::静态方法
        //iPHP:app:method >> appFunc::app_method
        if($args['method']){
            $callback = array(
                $args['app'].'Func',
                $args['app'].'_'.$args['method']
            );
            if(strpos($args['app'], 'App')!==false){
                //iPHP:aaaApp:method >> aaaApp::method
                //$aaaApp_method
                $callback = array(
                    $args['app'],
                    $args['method']
                );
            }
            if(strpos($args['app'], 'Class')!==false){
                //iPHP:aaaClass:method >> aaa::method
                ////$aaaClass_method
                $callback = array(
                    substr($args['app'], 0,-5),
                    $args['method']
                );
            }
            //自定义APP模板调用
            //iPHP:content:list app="test" >> contentFunc::content_list
            //iPHP:test:list >> contentFunc::content_list
            if(self::$config['define']){
                $apps = self::$config['define']['apps'];
                $func = self::$config['define']['func'];
                if(!self::check_func($args['app']) && $apps[$args['app']]){
                    // 判断自定义APP app/test/test.func.php 程序是否存在
                    // 程序不存在调用 contentFunc::content_list
                    $callback = array(
                        $func.'Func',
                        $func.'_'.$args['method']
                    );
                }
            }

            if($args['_app']){
                //iPHP:app:method _app="aaa" >> aaaFunc::aaa_method
                $keys     = isset($args['as'])?$args['as']:$args['_app'].'_'.$args['method'];
                $callback = array(
                    $args['_app'].'Func',
                    $args['_app'].'_'.$args['method']
                );
            }
            self::callback_func_my($callback);
            if(!method_exists($callback[0],$callback[1]) && strpos($callback[1], '__')===false){
                iPHP::error_throw("Unable to find method '{$callback[0]}::{$callback[1]}'");
            }
        }else{
            //iPHP:func app="ooxx"
            $func_path = iPHP_TPL_FUN."/".iPHP_APP.".".$args['app'].".php";
            // if($args['_app']){
            //     //判断 iPHP.app.php是否存在 不存用检测,原设置_app
            //     if(!is_file($func_path)){
            //         $args['app'] = $args['_app'];
            //         $func_path = iPHP_TPL_FUN."/".iPHP_APP.".".$args['_app'].".php";
            //     }
            // }
            //iPHP:func >> iPHP_func
            $callback = iPHP_APP.'_' . $args['app'];
            function_exists($callback) OR require_once($func_path);
        }
        if(isset($args['vars'])){
            $vars = $args['vars'];
            unset($args['vars'],$vars['loop'],$vars['page']);
            $args = array_merge($args,$vars);
        }

        if(is_array($callback)){
            // iPHP:app:_method >> app_method::func
            // iPHP:app:_method func='aaa' >> app_method::aaa
            strpos($callback[1], '__')!==false && $callback = array('iView','callback_func_proxy');
            $tpl->assign($keys,call_user_func_array($callback, array($args)));
        }else{
            $tpl->assign($keys,$callback($args));
        }
    }
    public static function callback_func_my(&$callback=null){
        if($callback){
            $my = $callback;
            $my[0] = 'MY_'.$my[0];
            $app  = substr($callback[0],0,-4);
            $file = 'MY_'.$app.'.func';
            $path = iPHP_APP_DIR . '/' . $app . '/' . $file . '.php';
            if(is_file($path)){
                if(method_exists($my[0],$my[1]) && strpos($callback[1], '__')===false){
                    $callback = $my;
                }
            }
        }
    }
    /**
     * iPHP:app:_method >> app_method::func
     * iPHP:app:_method func='aaa' >> app_method::aaa
     */
    public static function callback_func_proxy($vars=null){
        $func = 'func';
        $vars['func'] && $func = $vars['func'];
        $callback = array($vars['app'].$vars['method'],$func);
        if(is_callable($callback)){
            call_user_func_array($callback, array($vars));
        }else{
            iPHP::error_throw("Unable to find method '{$callback[0]}::{$callback[1]}'");
        }
    }
    public static function callback_plugin($name,$tpl) {
        $path = iPHP_TPL_FUN."/tpl.".$name;
        if (is_file($path)) {
            return $path;
        }
        return false;
    }
    public static function block_cache($vars, $content, $tpl) {
        $vars['id'] OR iUI::warning('cache 标签出错! 缺少"id"属性或"id"值为空.');
        $cache_time = isset($vars['time']) ? (int) $vars['time'] : -1;
        $cache_name = self::$config['template']['device'] . '/block_cache/' . $vars['id'];
        $cache = iCache::get($cache_name);
        if (empty($cache)) {
            if ($content === null) {
                return false;
            }
            $cache = $content;
            iCache::set($cache_name, $content, $cache_time);
        }
        if ($vars['assign']) {
            $tpl->assign($vars['assign'], $cache);
            return true;
        }
        return $cache;
    }
    /**
     * 模板路径
     * @param  [type] $tpl [description]
     * @return [type]      [description]
     */
    public static function callback_resource($tpl,$obj){
        $tpl = ltrim($tpl,'/');
        strpos($tpl,'..') && iPHP::error_404("The template path contains'..'");

        if(strpos($tpl, 'file::')!==false){
            list($_dir,$tpl)   = explode('||',str_replace('file::','',$tpl));
            $obj->template_dir = $_dir;
            return $tpl;
        }

        strpos($tpl,'./') !==false && $tpl = str_replace('./',dirname($obj->_file).'/',$tpl);

        $rtpl = self::tpl_exists($tpl,$_tpl);
        $rtpl === false && iPHP::error_404('Unable to find the template file <b>iPHP:://template/' . $_tpl . '</b>', '002', 'TPL');
        return $rtpl;
    }
    public static function tpl_exists($tpl,&$_tpl=null) {
        $flag = iPHP_APP . ':/';
        $_tpl = $tpl;
        if (strpos($tpl, $flag) !== false) {
            // 模板名/$tpl
            if ($_tpl = self::check_tpl($tpl, self::$config['template']['dir'])){
                return $_tpl;
            }
            // testApp/$tpl
            if(self::$app){
                if ($_tpl = self::check_tpl($tpl, self::$app.'App')) {
                    return $_tpl;
                }
            }
            // iPHP/$tpl
            if ($_tpl = self::check_tpl($tpl, iPHP_APP)) {
                return $_tpl;
            }
            // iPHP/设备名/$tpl
            if ($_tpl = self::check_tpl($tpl, iPHP_APP.'/'.self::$config['template']['device'])) {
                return $_tpl;
            }
            // // 其它移动设备$tpl
            // if(iPHP_MOBILE){
            //     // iPHP/mobile/$tpl
            //     if ($_tpl = self::check_tpl($tpl, iPHP_APP.'/mobile')) {
            //         return $_tpl;
            //     }
            // }
            $_tpl = str_replace($flag, self::$config['template']['dir'], $tpl);
            // return self::check_tpl($tpl, self::$config['template']['dir']);
        } elseif (strpos($tpl, '{iTPL}') !== false) {
            $flag = '{iTPL}';
            // testApp/$tpl
            if(self::$app){
                if ($_tpl = self::check_tpl($tpl, self::$app.'App',$flag)) {
                    return $_tpl;
                }
            }
            $_tpl = str_replace($flag, self::$config['template']['dir'], $tpl);
        }

        if (is_file(iPHP_TPL_DIR . "/" . $_tpl)) {
            return $_tpl;
        } else {
            return false;
        }
    }
    public static function check_tpl($tpl, $dir=null,$flag=null) {
        $flag===null && $flag = iPHP_APP.':/';
        $dir && $tpl = str_replace($flag, $dir, $tpl);
        if (is_file(iPHP_TPL_DIR . "/" . $tpl)) {
            return $tpl;
        }
        return false;
    }
    public static function check_dir($name) {
        $dir = iPHP_TPL_DIR . "/" . $name;
        if (is_dir($dir)) {
            return $dir;
        }
        return false;
    }
    public static function app_vars($app_name = true, $out = false) {
        $app_name === true && $app_name = iPHP::$app_name;
        $rs = self::get_vars($app_name);
        return $rs['param'];
    }
    public static function get_vars($key = null) {
        return self::$handle->get_template_vars($key);
    }
    public static function set_iVARS($value = null,$key=null,$append=false) {
        if(is_array($value) && $key===null){
            self::$handle->_iVARS = array_merge(self::$handle->_iVARS,$value);
        }else{
            $vars = &self::$handle->_iVARS[$key];
            if($append){
                if(is_array($value)){
                    $vars = array_merge($vars,$value);
                }else{
                    $vars.= $value;
                }
            }else{
                $vars = $value;
            }
        }
    }

    public static function clear_tpl($file = null) {
        self::$handle OR self::init();
        self::$handle->clear_compiled_tpl($file);
    }
    public static function value($key, $value) {
        self::$handle->assign($key, $value);
    }
    public static function assign($key, $value) {
        self::$handle->assign($key, $value);
    }
    public static function append($key, $value = null, $merge = false) {
        self::$handle->append($key, $value, $merge);
    }
    public static function clear($key) {
        self::$handle->clear_assign($key);
    }
    public static function display($tpl) {
        self::$handle OR self::init();
        self::$handle->fetch($tpl,true);
    }
    public static function fetch($tpl) {
        self::$handle OR self::init();
        return self::$handle->fetch($tpl);
    }
    public static function render($tpl, $app = 'index') {
        $tpl OR iPHP::error_404('Please set the template file', '001', 'TPL');
        $app && self::$app = $app;
        self::receive_tpl($tpl);
        if (self::$gateway == 'html') {
            return self::$handle->fetch($tpl);
        } else {
            self::$handle->fetch($tpl,true);
            iPHP::debug_info($tpl);
        }
    }
    public static function receive_tpl(&$iTPL,$tpl=null){
        $tpl===null && $tpl = iSecurity::escapeStr($_GET['tpl']);
        if($tpl){
            $tpl.= '.htm';
            $tpl = iSecurity::escapeDir(ltrim($tpl,'/'));
            if(iSecurity::_escapePath($tpl)){
                $tplpath = iPHP_TPL_DIR . '/' .self::$config['template']['dir'].'/'.$tpl;
                if (is_file($tplpath)) {
                    $iTPL = '{iTPL}/'.$tpl;
                }
            }
        }
    }
}
