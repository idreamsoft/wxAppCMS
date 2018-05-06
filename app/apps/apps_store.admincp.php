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

class apps_storeAdmincp extends appsAdmincp {
    /**
     * [模板市场]
     * @return [type] [description]
     */
    public function do_template(){
      $title      = '模板';
      $storeArray = apps_store::get_array(array('type'=>'1'));
      $dataArray  = apps_store::remote_all('template');
      include admincp::view("apps.store");
    }
    public function do_template_update(){
      $this->do_store_update('template','模板');
    }
    public function do_template_uninstall(){
      $sid   = (int)$_GET['sid'];
      $store = apps_store::get($sid);
      $dir   = iView::check_dir($store['app']);
      if($dir && $store['app']){
        foreach (iDevice::$config as $key => $value) {
          if($value['tpl']==$store['app']){
            iUI::alert('当前模板【'.$key.'】设备正在使用中,如果删除将出现错误','js:1',10);
          }
        }
        foreach ((array)iDevice::$config['device'] as $key => $value) {
          if($value['tpl']==$store['app']){
            iUI::alert('当前模板【'.$value['name'].'】设备正在使用中,如果删除将出现错误','js:1',10);
          }
        }
        iFS::rmdir($dir);
      }
      $sid && apps_store::del($sid,'sid');
      iUI::success('模板已删除','js:1');
    }
    /**
     * [从模板市场安装模板]
     * @return [type] [description]
     */
    public function do_template_install(){
      $this->do_store_install('template','模板');
    }
    /**
     * [付费安装模板]
     * @return [type] [description]
     */
    public function do_template_premium_install(){
      $this->do_store_premium_install('template');
    }
    /**
     * [应用市场]
     * @return [type] [description]
     */
    public function do_store($name=null){
      $title      = '应用';
      $storeArray = apps_store::get_array(array('type'=>'0'));
      $dataArray  = apps_store::remote_all();
      include admincp::view("apps.store");
    }
    public function do_store_uninstall(){
      $this->do_uninstall();
    }
    public function do_store_update($type='app',$title='应用'){
      $sid   = (int)$_GET['sid'];
      $data  = apps_store::get($sid);
      $store = apps_store::remote_update($data);
      apps_store::check_must($store);
      apps_store::$is_update = true;
      apps_store::setup($store['url'],$store,$data);
    }
    /**
     * [从应用市场安装应用]
     * @return [type] [description]
     */
    public function do_store_install($type='app',$title='应用',$update=false){
      $sid   = (int)$_GET['sid'];
      $store = apps_store::remote_send($sid);
      apps_store::check_must($store);
      $bak = '_bak_'.get_date(0,"YmdHi");
      $force = $_GET['force'];
      $store['force'] && $forceBtn = ' <a href="'.iPHP_REQUEST_URL.'&force=true" target="iPHP_FRAME" class="install-btn btn btn-inverse">备份原有,强制安装</a>';
      if($type=='app'){
          $ag = apps::get($store['app'],'app');
          if($ag){
            if($force){
              iDB::update("apps",array('app'=>$store['app'].$bak),array('app'=>$store['app']));
            }else{
              iUI::alert($store['name'].'['.$store['app'].'] 应用已存在'.$forceBtn,'js:1',1000000);
            }
          }
          if(is_array($store['data']) && $store['data']['tables']){
            foreach ($store['data']['tables'] as $tkey => $table) {
              if(iDB::check_table($table)){
                if($force){
                  iDB::query("RENAME TABLE `".iDB::table($table)."` TO `".iDB::table($table).$bak."`");
                }else{
                  iUI::alert('['.$table.']数据表已经存在!'.$forceBtn,'js:1',1000000);
                }
              }
            }
          }
          $path = iPHP_APP_DIR.'/'.$store['app'];
          if(iFS::checkDir($path)){
            if($force){
              rename($path, $path.$bak);
            }else{
              $ptext = iSecurity::filter_path($path);
              iUI::alert(
                $store['name'].'['.$store['app'].'] <br />应用['.$ptext.']目录已存在,<br />程序无法继续安装'.$forceBtn,
                'js:1',1000000
              );
            }
          }
      }

      if($type=='template'){
        $path = iPHP_TPL_DIR.'/'.$store['app'];
        if(iFS::checkDir($path)){
          if($force){
            rename($path, $path.$bak);
          }else{
            $ptext = iSecurity::filter_path($path);
            iUI::alert(
              $store['name'].'['.$store['app'].'] <br /> 模板['.$ptext.']目录已存在,<br />程序无法继续安装'.$forceBtn,
              'js:1',1000000
            );
          }
        }
      }

      iCache::set('store/'.$sid,$store,3600);
      if($store){
        if($store['premium']){
          apps_store::premium_dialog($sid,$store,$title);
        }else{
          apps_store::setup($store['url'],$store);
        }
      }
    }
    /**
     * [付费安装]
     * @return [type] [description]
     */
    public function do_store_premium_install($type='app'){
      $sid   = (int)$_GET['sid'];
      $key   = 'store/'.$sid;
      $store = iCache::get($key);
      iCache::del($key);
      apps_store::setup($_GET['url'],$store);
    }
    public function do_restore(){
      $transaction_id = $_GET['transaction_id'];
      $sid            = $_GET['sid'];
      $order_id       = $_GET['order_id'];
      $authkey        = $_GET['authkey'];
      empty($transaction_id) && iUI::alert("请输入微信支付订单号");
      $ret = apps_store::remote_send($sid,'restore',compact(array('transaction_id','sid','order_id','authkey')));
      if(!$ret['code']){
        iUI::alert($ret['msg']);
      }
    }
    public function do_pay_notify(){
      apps_store::pay_notify();
    }
}
