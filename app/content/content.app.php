<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class contentApp extends appsApp {
    public $appid   = null;
    public $app     = null; //应用名
    public $tables  = null;
    public $table   = null;

    public function __construct($data) {
        $this->data    = $data;
        $this->appid   = $data['id'];
        $this->app     = $data['app'];
        $this->tables  = apps::get_table($data,false);
        $this->table   = reset($this->tables);
        $this->primary = $this->table['primary'];
        $this->id      = (int)$_GET[$this->primary];
        parent::__construct('content',$this->primary,$this->table['table']);
        unset($data);
    }

    public function content($fvar, $page = 1,$field='id',$tpl = true) {
        $rs = $this->get_data($fvar,$field);
        if ($rs === false) return false;
        $id = $rs[$this->primary];

        $vars = array(
            'tag'  => true,
            'user' => true,
        );
        $rs+= $this->data($id);
        $rs = $this->value($rs,$cdata,$vars,$page,$tpl);
        if ($rs === false) {
            return false;
        }
        $rs+=(array)apps_meta::data($this->app,$id);
        $this->hooked($rs);

        if ($tpl) {
            $app = apps::get_app_lite($this->data);
            //自定义应用模板信息
            $app['type']=="2" && iPHP::callback(array("contentFunc","interfaced"),array($app));
            $content = $rs; unset($content['category']);
            iView::assign('APP', $app);
            iView::assign('content', $content);
            unset($content);
        }

        return self::render($rs,$tpl,$this->app);
    }

    public function value($rs, $vars = array(),$page = 1, $tpl = false) {
        $category = array();
        $process = $this->process($tpl,$category,$rs);
        if ($process === false) return false;

        if($category['mode'] && stripos($rs['url'], '.php?')===false){
            iURL::page_url($rs['iurl']);
        }

        $vars['tag'] && tagApp::get_array($rs,$category['name'],'tags');

        apps_common::init($rs,$this->app,$vars,$this->primary);
        apps_common::link();
        apps_common::text2link();
        apps_common::user();
        apps_common::comment();
        apps_common::pic();
        apps_common::hits();
        apps_common::param();

        if($this->data['fields']){
            $fields = former::fields($this->data['fields']);
            foreach ((array)$fields as $key => $field) {
                formerApp::vars($field,$key,$rs,$vars,$category,$this->app);
            }
        }
        return $rs;
    }
    public function data($ids=0){
        if(empty($ids)) return array();

        $dtn = apps_mod::data_table_name($this->app);
        $cdata_table = $this->tables[$dtn];
        if(empty($cdata_table)){
            return array();
        }
        $union_key   = $cdata_table['union'];
        $table_name  = $cdata_table['name'];

        list($ids,$is_multi)  = iSQL::multi_var($ids);
        $sql  = iSQL::in($ids,$union_key,false,true);
        $data = array();
        $rs   = iDB::all("SELECT * FROM `#iCMS@__{$table_name}` where {$sql}");
        if($rs){
            $_count = count($rs);
            for ($i=0; $i < $_count; $i++) {
                $data[$rs[$i][$union_key]]= $rs[$i];
            }
            $is_multi OR $data = $data[$ids];
        }
        if(empty($data)){
            return array();
        }
        return $data;
    }
    /**
     * [iPHP::run回调]
     * @param  [type] $app [description]
     * @return [type]      [description]
     */
    public static function run($app){
        $data = apps::get_app($app);
        if($data){
            iPHP::$app_path = iPHP_APP_DIR . '/content';
            iPHP::$app_file = iPHP::$app_path . '/content.app.php';
            iPHP::$app      = new contentApp($data);
        }else{
            iPHP::error_404('Unable to find custom application <b>' . $app . '.app.php</b>', '0003');
        }
    }
}
