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

class appsAdmincp{
    public function __construct() {
      $this->appid = iCMS_APP_APPS;
    	$this->id = (int)$_GET['id'];

      iHttp::$CURLOPT_TIMEOUT        = 60;
      iHttp::$CURLOPT_CONNECTTIMEOUT = 10;
    }
    public function do_iCMS(){
      $this->do_manage();
    }
    public function do_add(){
        $this->id && $rs = apps::get($this->id);
        if(empty($rs)){
          $rs['type']   = "2";
          $rs['status'] = "1";
          $rs['create'] = "1";
          if($rs['type']=="2"){
            $rs['apptype'] = "2";
            $rs['config']['iFormer'] = '1';
            $rs['config']['menu']    = 'default';
            $base_fields  = apps_mod::base_fields_array();
            $rs['fields'] = json_decode(get_php_file(iPHP_APP_DIR.'/apps/json/fields.php'),true);
            $rs['menu']   = json_decode(get_php_file(iPHP_APP_DIR.'/apps/json/menu.php'),true);
          }
        }else{
          if($rs['apptype']=="2"){
            $rs['config']['iFormer'] = '1';
          }
        }

        $rs['config']['template'] = apps_mod::template($rs);
        if(empty($rs['config']['iurl'])){
          $rs['config']['iurl'] = apps_mod::iurl($rs);
        }
        if($rs['menu']){
          $rs['menu'] = str_replace(
            array('[{"','},','}]'),
            array("[\n{\"","},\n","}\n]"),
            cnjson_encode($rs['menu'])
          );
        }
        if($rs['router']){
          $rs['router'] = str_replace(
            array('{["','],',']}','\/'),
            array("{\n[\"","],\n","]\n}",'/'),
            stripcslashes($rs['router'])
          );
        }
        include admincp::view("apps.add");
    }

    public function do_save(){
        @set_time_limit(0);

        $id      = (int)$_POST['_id'];
        $name    = iSecurity::escapeStr($_POST['_name']);
        $title   = iSecurity::escapeStr($_POST['_title']);
        $app     = iSecurity::escapeStr($_POST['_app']);
        $apptype = (int)$_POST['apptype'];
        $type    = (int)$_POST['type'];
        $status  = (int)$_POST['status'];
        // $create  = (int)$_POST['create']?true:false;
        $create  = true;
        if($_POST['menu']){
          $menu = json_decode(stripcslashes($_POST['menu']));
          $menu = addslashes(cnjson_encode($menu));
        }
        if($_POST['router']){
          $router = json_decode(stripcslashes($_POST['router']));
          $router = addslashes(json_encode($router));
        }
        $name OR iUI::alert('应用名称不能为空!');
        empty($app) && $app = iPinyin::get($name);
        empty($title) && $title = $name;

        $table_array  = $_POST['table'];
        if($table_array){
          $table_array  = array_filter($table_array);
          $table  = addslashes(cnjson_encode($table_array));
        }

        $config_array = $_POST['config'];
        if($config_array['template']){
          $config_array['template'] = explode("\n", $config_array['template']);
          $config_array['template'] = array_map('trim', $config_array['template']);
        }
        if($config_array['iurl']){
          $config_array['iurl'] = json_decode(stripcslashes($config_array['iurl']),true);
        }

        $config_array = array_filter($config_array);
        $config = addslashes(cnjson_encode($config_array));

        $fields = '';
        $fieldata = $_POST['fields'];
        if(is_array($fieldata)){
          $field_array = array();
          foreach ($fieldata as $key => $value) {
            $output = array();
            parse_str($value,$output);
            if(isset($output['UI:BR'])){
              $field_array[$key] = 'UI:BR';
            }else{
              preg_match("/[a-zA-Z0-9_\-]/",$output['name']) OR iUI::alert('['.$output['label'].'] 字段名只能由英文字母、数字或_-组成,不支持中文');
              $output['label'] OR iUI::alert('发现自定义字段中空字段名称!');
              $output['comment'] = $output['label'].($output['comment']?':'.$output['comment']:'');
              $fname = $output['name'];
              $fname OR iUI::alert('发现自定义字段中有空字段名!');
              $field_array[$fname] = $value;
              if($output['field']=="MEDIUMTEXT"){
                $addons_fieldata[$key] = $value;
                unset($fieldata[$key]);//从基本表移除
              }
            }
          }
          //字段数据存入数据库
          $fields = addslashes(cnjson_encode($field_array));
        }

        $addtime = time();
        $array   = compact(array('app','name','title','menu','router','table','config','fields','addtime','apptype','type','status'));
        // $array['menu'] = str_replace(array("\r","\n"),'',$array['menu']);

        if(empty($id)) {
            iDB::value("SELECT `id` FROM `#iCMS@__apps` where `app` ='$app'") && iUI::alert('该应用已经存在!');
            // iDB::$print_sql = true;
            if($type=='3'){
              $array['fields'] = '';
              $msg = "应用信息添加完成!";
            }else if($type=='2'){
              if($create){
                iDB::check_table($array['app']) && iUI::alert('['.$array['app'].']数据表已经存在!');
              }
              if($addons_fieldata){
                $addons_name = apps_mod::data_table_name($array['app']);
                if($create){
                  iDB::check_table($addons_name) && iUI::alert('['.$addons_name.']附加表已经存在!');
                }
              }

              //创建基本表
              $tb = apps_db::create_table(
                $array['app'],
                apps_mod::get_field_array($fieldata),//获取字段数组
                apps_mod::base_fields_index(),//索引
                $create
              );
              array_push ($tb,null,$array['name']);
              $table_array = array();
              $table_array[$array['app']]= $tb;//记录基本表名

              //有MEDIUMTEXT类型字段就创建xxx_data附加表
              if($addons_fieldata){
                $union_id = apps_mod::data_union_key($array['app']);//关联基本表id
                $addons_base_fields = apps_mod::data_base_fields($array['app']);//xxx_data附加表的基础字段
                $addons_fieldata = $addons_base_fields+$addons_fieldata;
                $table_array += apps_mod::data_create_table($addons_fieldata,$addons_name,$union_id,$create);
                // //添加到字段数据里
                // $field_array = array_merge($field_array,$addons_base_fields);
                // $array['fields'] = addslashes(cnjson_encode($field_array));
              }
              $table_array+=apps_meta::table_array($app);

              $array['table']  = $table_array;
              $array['config'] = $config_array;

              $config_array['template'] = apps_mod::template($array,'array');
              $config_array['iurl']   = apps_mod::iurl($array);

              $array['table'] = addslashes(cnjson_encode($table_array));
              $array['config'] = addslashes(cnjson_encode($config_array));
              $msg = "应用创建完成!";
            }

            $id = iDB::insert('apps',$array);
            // if(stripos($array['menu'], '{app}') !== false){
            //   $_name = $array['title']?$array['title']:$array['name'];
            //   $menu = str_replace(
            //       array('{appid}','{app}','{name}','{sort}'),
            //       array($id,$array['app'],$_name,$id*1000),
            //       $array['menu']
            //   );
            //   iDB::update('apps', array('menu'=>$menu), array('id'=>$id));
            // }
        }else {
            iDB::value("SELECT `id` FROM `#iCMS@__apps` where `app` ='$app' AND `id` !='$id'") && iUI::alert('该应用已经存在!');
            $_fields     = iDB::value("SELECT `fields` FROM `#iCMS@__apps` where `id` ='$id'");//json
            $_json_field = apps_mod::json_field($_fields);//旧数据
            $json_field  = apps_mod::json_field($fields); //新数据
            /**
             * 找出字段数据中的 MEDIUMTEXT类型字段
             * PS:函数内会unset(json_field[key]) 所以要在 基本表make_alter_sql前执行
             */
            $_addons_json_field = apps_mod::find_MEDIUMTEXT($_json_field);
            $addons_json_field = apps_mod::find_MEDIUMTEXT($json_field);

            // print_r($_addons_json_field);
            // print_r($addons_json_field);

            //基本表 新旧数据计算交差集 origin 为旧字段名
            $sql_array = apps_db::make_alter_sql($json_field,$_json_field,$_POST['origin']);
            $sql_array && apps_db::alter_table($array['app'],$sql_array);

            //MEDIUMTEXT类型字段 新旧数据计算交差集 origin 为旧字段名
            $addons_sql_array = apps_db::make_alter_sql($addons_json_field,$_addons_json_field,$_POST['origin']);

            $addons_name = apps_mod::data_table_name($array['app']);
            //存在附加表数据
            if($addons_fieldata){
              if($addons_sql_array){
                //附加表名
                //检测附加表是否存在
                if($table_array[$addons_name] && iDB::check_table($addons_name)){
                  //表存在执行 alter
                  apps_db::alter_table($addons_name,$addons_sql_array);
                }else{
                  // 不存在 创建
                  if($addons_fieldata){
                    iDB::check_table($addons_name) && iUI::alert('['.$addons_name.']附加表已经存在!');
                    //有MEDIUMTEXT类型字段创建xxx_data附加表
                    $union_id = apps_mod::data_union_key($array['app']);
                    $addons_base_fields = apps_mod::data_base_fields($array['app']);//xxx_data附加表的基础字段
                    $addons_fieldata = $addons_base_fields+$addons_fieldata;
                    $table_array += apps_mod::data_create_table($addons_fieldata,$addons_name,$union_id);
                    $array['table'] = addslashes(cnjson_encode($table_array));
                    // //添加到字段数据里
                    // $field_array = array_merge($field_array,$addons_base_fields);
                    // $array['fields'] = addslashes(cnjson_encode($field_array));
                  }
                }
              }
            }else{
              if($apptype=="2"){ //只删除自定义应用的表
                //不存在附加表数据 直接删除附加表 返回 table的json值 $table_array为引用参数
                apps_mod::drop_table($addons_fieldata,$table_array,$addons_name);
                $array['table'] = addslashes(cnjson_encode($table_array));
              }else{
                if($table_array){
                  $data_tables = next($table_array);
                  $union_id = apps_mod::data_union_key($array['app']);
                  //判断是否自动生成的表
                  if(is_array($data_tables) &&
                    in_array(apps_mod::DATA_PRIMARY_KEY ,$data_tables) &&
                    in_array($union_id ,$data_tables))
                  {
                    apps_mod::drop_table($addons_fieldata,$table_array,$addons_name);
                    $array['table'] = addslashes(cnjson_encode($table_array));
                  }else{
                    apps_db::alter_table($addons_name,$addons_sql_array);
                  }
                }
              }
            }

            iDB::update('apps', $array, array('id'=>$id));
            $msg = "应用编辑完成!";
        }
        apps::cache();
        menu::cache();
        iUI::success($msg,'url:'.APP_URI);
    }

    public function do_update(){
        if($this->id){
            $args = iSQL::update_args($_GET['_args']);
            $args && iDB::update("apps",$args,array('id'=>$this->id));
            apps::cache();
            iUI::success('操作成功!','js:1');
        }
    }
    public function do_manage(){
      // if($_GET['keywords']) {
		    // $sql=" WHERE `keyword` REGEXP '{$_GET['keywords']}'";
      // }
      list($orderby,$orderby_option) = get_orderby();
      $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:50;
      $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__apps` {$sql}","G");
      iUI::pagenav($total,$maxperpage,"个应用");
      $rs     = iDB::all("SELECT * FROM `#iCMS@__apps` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
      $_count = count($rs);

      //分组
      foreach ($rs as $key => $value) {
        $apps_type_group[$value['type']][$key] = $value;
      }
    	include admincp::view("apps.manage");
    }

    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要操作的应用");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
      	switch($batch){
  		  }

	  }
    public function do_cache(){
      apps::cache();
      iUI::success('更新完成');
    }
    /**
     * [卸载应用]
     * @return [type] [description]
     */
    public function do_uninstall($id = null,$dialog=true){
      $id===null && $id=$this->id;
      $app = apps::get($id);

      if($app['type'] && $app['apptype']){
        apps::uninstall($app);
        apps::cache();
        menu::cache();
        apps_store::del($id);
        $msg = '应用已经删除';
      }else{
        $msg = '应用已被禁止删除';
      }
      empty($app) && apps_store::del($id);

      $dialog && iUI::alert($msg,'js:1');
    }
    /**
     * [本地安装应用]
     * @return [type] [description]
     */
    public function do_local_app(){
      if(strpos($_POST['zipfile'], '..') !== false){
        iUI::alert('What the fuck!!');
      }
      apps_store::$zip_file = trim($_POST['zipfile'],"\0\n\r\t\x0B");
      apps_store::$msg_mode = 'alert';
      apps_store::install_app();
      iUI::success('应用安装完成','js:1');
    }
    /**
     * [打包下载应用]
     * @return [type] [description]
     */
    public function do_pack(){
      $rs = iDB::row("SELECT * FROM `#iCMS@__apps` where `id`='".$this->id."'",ARRAY_A);
      $appdir = iPHP_APP_DIR.'/'.$rs['app'];
      unset($rs['id']);
      $data     = base64_encode(serialize($rs));
      $config   = json_decode($rs['config'],true);
      $filename = 'iCMS.APP.'.$rs['app'].'-'.$config['version'];
      if(iFS::ex($appdir)) { //本地应用
        $remove_path = iPHP_APP_DIR;
      }else{//自定义应用
        $appdir = iPHP_APP_CACHE.'/pack.app/'.$rs['app'];
        $remove_path = iPHP_APP_CACHE.'/pack.app/';
        iFS::mkdir($appdir);
      }
      //应用数据
      $app_data_file = $appdir.'/iCMS.APP.DATA.php';
      put_php_file($app_data_file, $data);

      //数据库结构
      if($rs['table']){
        $app_table_file = $appdir.'/iCMS.APP.TABLE.php';

        put_php_file(
          $app_table_file,
          apps_db::create_table_sql($rs['table'])
        );
      }

      $zipfile = apps::get_zip($filename,$appdir,$remove_path);
      filesApp::attachment($zipfile);
      iFS::rm($zipfile);
      iFS::rm($app_data_file);
      $app_table_file && iFS::rm($app_table_file);

      if($remove_path != iPHP_APP_DIR){
        iFS::rmdir($remove_path);
      }
    }
    /**
     * [钩子管理]
     * @return [type] [description]
     */
    public function do_hooks(){
        configAdmincp::app($this->appid,'hooks');
    }
    /**
     * [保存钩子]
     * @return [type] [description]
     */
    public function do_hooks_save(){
        $hooks = array();
        foreach ((array)$_POST['hooks']['method'] as $key => $method) {
          $h_app   = $_POST['hooks']['app'][$key];
          $h_field = $_POST['hooks']['field'][$key];
          if($method && $h_app && $h_field){
            $hooks[$h_app][$h_field][]= explode("::", $method);
          }
        }
        $_POST['config'] = $hooks;
        configAdmincp::save($this->appid,'hooks');
    }
    public static function _count(){
      return iDB::value("SELECT count(*) FROM `#iCMS@__apps`");
    }
}
