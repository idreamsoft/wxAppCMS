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

class wxappAdmincp{
    public function __construct() {
        $this->id      = (int)$_GET['id'];
        $this->appid   = iCMS_APP_WXAPP;
    }

    public function do_add(){
        $this->id && $rs = iDB::row("SELECT * FROM `#iCMS@__wxapp` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);;
        if(empty($rs)){
            $rs['url']  = iCMS::$config['router']['url'];
        }else{
            if($_GET['act']=='copy'){
                $this->id = 0;
                $rs['id'] = 0;
            }
            $config  = (array)json_decode($rs['config'],true);
            $payment = (array)json_decode($rs['payment'],true);
        }
        iPHP::callback(array("apps_meta","get"),array($this->appid,$this->id));
        iPHP::callback(array("formerApp","add"),array($this->appid,$rs,true));

        include admincp::view("wxapp.add");
    }
    public function do_save(){
        $id          = (int)$_POST['id'];
        $cid         = (int)$_POST['cid'];
        $appid       = iSecurity::escapeStr($_POST['appid']);
        $appsecret   = iSecurity::escapeStr($_POST['appsecret']);

        $name        = iSecurity::escapeStr($_POST['name']);
        $account     = iSecurity::escapeStr($_POST['account']);
        $qrcode      = iSecurity::escapeStr($_POST['qrcode']);
        $description = iSecurity::escapeStr($_POST['description']);

        $url         = iSecurity::escapeStr($_POST['url']);
        $version     = iSecurity::escapeStr($_POST['version']);
        $tpl         = iSecurity::escapeStr($_POST['tpl']);
        $index       = iSecurity::escapeStr($_POST['index']);

        $config      = addslashes(json_encode($_POST['config']));
        $payment     = addslashes(json_encode($_POST['payment']));

        $appid OR iUI::alert('APPID不能为空!');
        $appsecret OR iUI::alert('密钥不能为空!');
        $url OR iUI::alert('接口域名不能为空!');
        $tpl OR iUI::alert('接口模板不能为空!');
        preg_match('/^v\d+\.\d+\.\d+$/', $version) OR iUI::alert('版本格式不正确');

        $fields = array(
            'cid', 'appid', 'appsecret',
            'name','account', 'qrcode','description',
            'url','version','tpl','index',
            'config', 'payment'
        );
        $data   = compact ($fields);

        if($id){
            iDB::update('wxapp', $data, array('id'=>$id));
            $msg = "小程序更新完成!";
        }else{
            // iDB::value("
            //     SELECT `id` FROM `#iCMS@__wxapp`
            //     WHERE `appid` ='$appid'
            // ") && iUI::alert('该APPID小程序已经存在');
            iDB::insert('wxapp',$data);
            $msg = "小程序添加完成!";
        }
        $data['id'] = $id;

        iPHP::callback(array("apps_meta","save"),array($this->appid,$id));
        iPHP::callback(array("formerApp","save"),array($this->appid,$id));

        // $this->update_config_js($data);
        // $this->cache();
        iUI::success($msg,'url:'.APP_URI);
    }
    public function do_getconfig(){
        $this->id && $data = iDB::row("SELECT * FROM `#iCMS@__wxapp` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);;
        list($dir,$api) = explode('/', $data['tpl']);
        $config = <<<EOT
//配置模板
module.exports = {
    //小程序 wxAppID
    wxAppID: '{wxAppID}',
    //API版本
    VERSION: '{VERSION}',
    //小程序 request 合法域名
    HOST: '{HOST}',
    //小程序名称
    TITLE: '{TITLE}',
}
EOT;
        $config = str_replace(
            array('{wxAppID}','{VERSION}','{HOST}','{TITLE}'),
            array($data['id'],$data['version'],rtrim($data['url'],'/').'/',trim($data['name'])),
            $config
        );
        $path = iPHP_APP_CACHE.'/'.md5($data['appid']).'.js';
        iFS::write($path,$config);
        filesApp::attachment($path,'config.js');
        iFS::rm($path);
    }
    public function do_update(){
        foreach((array)$_POST['id'] as $tk=>$id){
            iDB::query("update `#iCMS@__wxapp` set `app` = '".$_POST['app'][$tk]."', `name` = '".$_POST['name'][$tk]."', `value` = '".$_POST['value'][$tk]."' where `id` = '$id';");
        }
        $this->cache();
        iUI::alert('更新完成');
    }
    public function do_del($id = null,$dialog=true){
        $id===null && $id=$this->id;
        $id OR iUI::alert('请选择要删除的小程序!');
        $this->del($id);
        $this->cache();
        $dialog && iUI::success("已经删除!",'url:'.APP_URI);
    }
    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要操作的小程序");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
        switch($batch){
            case 'dels':
                iUI::$break = false;
                foreach($idArray AS $id){
                    $this->do_del($id,false);
                }
                iUI::$break = true;
                iUI::success('小程序全部删除完成!','js:1');
            break;
            case 'refresh':
                $this->cache();
                iUI::success('小程序缓存全部更新完成!','js:1');
            break;
        }
    }
    public function do_iCMS(){
      $this->do_manage();
    }
    public function do_manage(){
        $sql = " where 1=1";
        $cid = (int)$_GET['cid'];

        if($cid) {
            $cids = $_GET['sub']?categoryApp::get_cids($cid,true):$cid;
            $cids OR $cids = $vars['cid'];
            $sql .= iSQL::in($cids,'cid');
        }

        $_GET['field']&& $sql.=" AND `field`='".$_GET['field']."'";
        $_GET['field']&& $uriArray['field'] = $_GET['field'];

        $_GET['_app'] && $sql.=" AND `app`='".$_GET['_app']."'";
        $_GET['_app'] && $uriArray['_app'] = $_GET['_app'];

        $_GET['appid'] && $sql.=" AND `appid`='".$_GET['appid']."'";
        $_GET['appid'] && $uriArray['appid'] = $_GET['appid'];

        $_GET['cid']  && $sql.=" AND `cid`='".$_GET['cid']."'";
        $_GET['cid']  && $uriArray['cid'] = $_GET['cid'];

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__wxapp` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个小程序");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__wxapp` {$sql} order by id DESC LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
        include admincp::view("wxapp.manage");
    }

}
