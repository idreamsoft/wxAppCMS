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
        $app         = iSecurity::escapeStr($_POST['app']);
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
            'cid', 'appid', 'appsecret','app',
            'name','account', 'qrcode','description',
            'url','version','tpl','index',
            'config', 'payment'
        );
        $data   = compact ($fields);

        if($id){
            iDB::update('wxapp', $data, array('id'=>$id));
            $msg = "小程序更新完成!";
        }else{
            $id = iDB::insert('wxapp',$data);
            $msg = "小程序添加完成!";
        }
        $data['id'] = $id;

        iPHP::callback(array("apps_meta","save"),array($this->appid,$id));
        iPHP::callback(array("formerApp","save"),array($this->appid,$id));

        // $this->update_config_js($data);
        $this->cache($id);
        iUI::success($msg,'url:'.APP_URI);
    }
    public function do_getconfig(){
        $this->id && $data = wxapp::value($this->id);
        list($dir,$api) = explode('/', $data['tpl']);
        $config = <<<EOT
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
        echo '<h2>请将下面内容复制到的小程序文件夹下的config.js文件里</h2>';
        echo '<pre style="color: #383d41;
    background-color: #e2e3e5;
    border-color: #d6d8db;padding:10px;">';
        echo $config;
        echo '</pre>';
        // $path = iPHP_APP_CACHE.'/'.md5($data['appid']).'.js';
        // iFS::write($path,$config);
        // filesApp::attachment($path,'config.js');
        // iFS::rm($path);
    }
    public function do_update(){
        foreach((array)$_POST['id'] as $tk=>$id){
            iDB::query("
                UPDATE `#iCMS@__wxapp`
                SET `app` = '".$_POST['app'][$tk]."',
                `name` = '".$_POST['name'][$tk]."',
                `value` = '".$_POST['value'][$tk]."'
                WHERE `id` = '$id';
            ");
            $this->cache($id);
        }
        iUI::alert('更新完成');
    }
    public function do_del($id = null,$dialog=true){
        $id===null && $id=$this->id;
        $id OR iUI::alert('请选择要删除的小程序!');
        $this->del($id);
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
                iUI::$break = false;
                foreach($idArray AS $id){
                    $this->cache($id);
                }
                iUI::$break = true;
                iUI::success('小程序缓存全部更新完成!','js:1');
            break;
        }
    }
    public function do_cache(){
        $rs = iDB::all("SELECT `id` FROM `#iCMS@__wxapp`");
        $_count = count($rs);
        for ($i=0; $i < $_count; $i++) {
            $this->cache($rs[$i]['id']);
        }
        iUI::success('小程序缓存全部更新完成!','js:1');
    }
    public function do_user(){
        $sql = "WHERE 1=1";

        if($_GET['keywords']) {
            $sql.=" AND CONCAT(username,nickname) REGEXP '{$_GET['keywords']}'";
        }
        if(isset($_GET['status']) && $_GET['status']!==''){
            $sql.=" AND `status`='{$_GET['status']}'";
        }

        $_GET['wxappid']  && $sql.=" AND `appid`='{$_GET['wxappid']}'";
        $_GET['openid']   && $sql.=" AND `openid`='{$_GET['openid']}'";
        $_GET['client_ip']&& $sql.=" AND `client_ip`='{$_GET['client_ip']}'";
        $_GET['gender']   && $sql.=" AND `gender`='{$_GET['gender']}'";
        $_GET['province'] && $sql.=" AND `province`='{$_GET['province']}'";
        $_GET['city']     && $sql.=" AND `city`='{$_GET['city']}'";
        $_GET['client_ip']&& $sql.=" AND `client_ip`='{$_GET['client_ip']}'";

        list($orderby,$orderby_option) = get_orderby(array(
            'uid'        =>"UID",
        ));

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__wxapp_user` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个用户");
        $limit  = 'LIMIT '.iUI::$offset.','.$maxperpage;
        if($map_sql||iUI::$offset){
            $ids_array = iDB::all("
                SELECT `uid` FROM `#iCMS@__wxapp_user` {$sql}
                ORDER BY {$orderby} {$limit}
            ");
            $ids   = iSQL::values($ids_array,'uid');
            $ids   = $ids?$ids:'0';
            $sql   = "WHERE `uid` IN({$ids})";
            $limit = '';
        }
        $rs     = iDB::all("SELECT * FROM `#iCMS@__wxapp_user` {$sql} ORDER BY {$orderby} {$limit}");
        $_count = count($rs);
        include admincp::view("wxapp.user.manage");
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
    public function del($id){
        $data = wxapp::value($id);
        iDB::query("DELETE FROM `#iCMS@__wxapp` WHERE `id` = '$id'");
        iCache::delete('wxapp/'.$id);
        iCache::delete('wxapp/'.$data['appid']);
    }
    public function cache($id){
        $data = wxapp::value($id);
        iCache::set('wxapp/'.$id,$data,0);
        iCache::set('wxapp/'.$data['appid'],$data,0);
    }

}
