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

class weixinAdmincp{
    public function __construct() {
        $this->appid    = iCMS_APP_WEIXIN;
        $this->id       = (int)$_GET['id'];
        $this->wx_appid = addslashes($_GET['wx_appid']);
    }
    public function do_config(){
        $this->config = configAdmincp::get($this->appid,admincp::$APP_NAME);
        configAdmincp::app($this->appid);
    }
    public function do_save_config(){
        $this->config = configAdmincp::get($this->appid,admincp::$APP_NAME);
        $_POST['config'] = array_merge((array)$this->config,(array)$_POST['config']);
        configAdmincp::save($this->appid);
    }
    public function do_add(){
        $this->id && $rs = iDB::row("SELECT * FROM `#iCMS@__weixin` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        if(empty($rs)){

        }else{
            $config  = (array)json_decode($rs['config'],true);
            $payment = (array)json_decode($rs['payment'],true);
        }
        iPHP::callback(array("apps_meta","get"),array($this->appid,$this->id));
        iPHP::callback(array("formerApp","add"),array($this->appid,$rs,true));

        include admincp::view("weixin.add");
    }
    public function do_save(){
        $id          = (int)$_POST['id'];
        $cid         = (int)$_POST['cid'];
        $type        = (int)$_POST['type'];
        $appid       = iSecurity::escapeStr($_POST['appid']);
        $appsecret   = iSecurity::escapeStr($_POST['appsecret']);
        $token       = iSecurity::escapeStr($_POST['token']);
        $AESKey      = iSecurity::escapeStr($_POST['AESKey']);

        $name        = iSecurity::escapeStr($_POST['name']);
        $account     = iSecurity::escapeStr($_POST['account']);
        $qrcode      = iSecurity::escapeStr($_POST['qrcode']);
        $description = iSecurity::escapeStr($_POST['description']);

        $config    = $_POST['config']?addslashes(json_encode($_POST['config'])):'';
        $payment   = $_POST['payment']?addslashes(json_encode($_POST['payment'])):'';

        $appid OR iUI::alert('APPID不能为空!');
        $appsecret OR iUI::alert('appsecret不能为空!');
        $token OR iUI::alert('令牌不能为空!');
        $AESKey OR iUI::alert('密钥不能为空!');
        $name OR iUI::alert('名称不能为空!');

        $fields = array(
            'cid', 'type', 'appid', 'appsecret','token','AESKey',
            'name','account', 'qrcode','description',
            'config', 'payment'
        );
        $data   = compact ($fields);

        if($id){
            iDB::update('weixin', $data, array('id'=>$id));
            $msg = "公众号更新完成!";
        }else{
            iDB::value("
                SELECT `id` FROM `#iCMS@__weixin`
                WHERE `appid` ='$appid'
            ") && iUI::alert('该APPID公众号已经存在');

            iDB::insert('weixin',$data);
            $msg = "公众号添加完成!";
        }

        iPHP::callback(array("apps_meta","save"),array($this->appid,$id));
        iPHP::callback(array("formerApp","save"),array($this->appid,$id));

        iUI::success($msg,'url:'.APP_URI.'&do=manage');
    }
    // public function do_update(){
    //     foreach((array)$_POST['id'] as $tk=>$id){
    //         iDB::query("update `#iCMS@__weixin` set `app` = '".$_POST['app'][$tk]."', `name` = '".$_POST['name'][$tk]."', `value` = '".$_POST['value'][$tk]."' where `id` = '$id';");
    //     }
    //     $this->cache();
    //     iUI::alert('更新完成');
    // }
    public function do_del($id = null,$dialog=true){
        $id===null && $id=$this->id;
        $id OR iUI::alert('请选择要删除的公众号!');
        $this->del($id);
        $dialog && iUI::success("已经删除!",'url:'.APP_URI);
    }
    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要操作的项目");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
        switch($batch){
            case 'dels':
                iUI::$break = false;
                foreach($idArray AS $id){
                    $this->do_del($id,false);
                }
                iUI::$break = true;
                iUI::success('公众号全部删除完成!','js:1');
            break;
            case 'event_dels':
                iUI::$break = false;
                foreach($idArray AS $id){
                    $this->do_event_del($id,false);
                }
                iUI::$break = true;
                iUI::success('公众号全部删除完成!','js:1');
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

        $_GET['cid']  && $sql.=" AND `cid`='".$_GET['cid']."'";
        $_GET['cid']  && $uriArray['cid'] = $_GET['cid'];

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__weixin` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个公众号");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__weixin` {$sql} order by id DESC LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
        include admincp::view("weixin.manage");
    }

    /**
     * [自定义菜单]
     * @return [type] [description]
     */
    public function do_menu_save(){
        $appid = addslashes($_POST['wx_appid']);
        $menu  = $_POST['menuData']?addslashes(json_encode($_POST['menuData'])):'';
        iDB::update("weixin",array('menu'=>$menu),array('appid'=>$appid));
        iUI::success('菜单保存完成,现在可以同步菜单到微信平台');
    }
    /**
     * [同步菜单]
     * @return [type] [description]
     */
    public function do_menu_rsync(){
        $this->weixin_init();
        $response = weixin::setMenu();
        if(empty($response->errcode)){
            iUI::success('同步成功');
        }else{
            iUI::alert('同步出错 <br />errcode:"'.$response->errcode.'" errmsg:"'.$response->errmsg.'"');
        }
    }
    /**
     * [菜单管理]
     * @return [type] [description]
     */
    public function do_menu(){
        $rs = $this->value($this->wx_appid,'menu','appid');
        $menuArray = array();
        $rs['menu'] && $menuArray = json_decode($rs['menu'],true);
        include admincp::view("weixin.menu");
    }

    // /**
    //  * [第三方平台]
    //  * @return [type] [description]
    //  */
    // public function do_component_login(){
    //     $token = iSecurity::escapeStr($_GET['token']);
    //     if($token!=$this->config['token']){
    //         iUI::alert("Token(令牌)出错！请先保存Token(令牌)配置！",'js:window.iCMS_MODAL.destroy();');
    //     }
    //     $url = iCMS_WEIXIN_COMPONENT.'/iCMS/login?'.
    //     'token='.$token.
    //     '&url='.urlencode(iCMS::$config['router']['public']);
    //     iPHP::redirect($url);
    // }
    public function do_media(){
        $from     = $_GET['from'];
        $type     = $_GET['type'];
        $callback = $_GET['callback'];
        $target   = $_GET['target'];

        $this->weixin_init();
        $rs     = weixin::mediaList($type);
        $navbar = false;
        include admincp::view("weixin.media");
    }
    /**
     * [事件管理]
     * @return [type] [description]
     */
    public function do_event(){
        $sql = " WHERE ";
        switch($doType){ //status:[0:草稿][1:正常][2:回收]
            case 'inbox'://草稿
                $sql.="`status` ='0'";
            break;
            case 'trash'://回收站
                $sql.="`status` ='2'";
            break;
            default:
                $sql.=" `status` ='1'";
        }
        $this->wx_appid   && $sql.=" AND `appid`='$this->wx_appid' ";
        $_GET['keywords'] && $sql.=" AND `keyword` REGEXP '{$_GET['keywords']}'";
        $_GET['starttime']&& $sql.=" AND `addtime`>=UNIX_TIMESTAMP('".$_GET['starttime']." 00:00:00')";
        $_GET['endtime']  && $sql.=" AND `addtime`<=UNIX_TIMESTAMP('".$_GET['endtime']." 23:59:59')";

        list($orderby,$orderby_option) = get_orderby();
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__weixin_event` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个事件");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__weixin_event` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        // var_dump(iDB::$last_query);
        $_count = count($rs);

        include admincp::view("weixin.event");
    }
    /**
     * [添加事件]
     * @return [type] [description]
     */
    public function do_event_add(){
        $id = (int)$_GET['id'];
        if($id) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__weixin_event` WHERE `id`='$id' LIMIT 1;",ARRAY_A);
            $this->wx_appid = $_GET['wx_appid'] = $rs['appid'];
        }else{
            $rs['status'] = '1';
            $rs['msgtype'] = 'text';
        }
        include admincp::view("weixin.event.add");
    }
    /**
     * [保存事件]
     * @return [type] [description]
     */
    public function do_event_save(){
        $id       = (int)$_POST['id'];
        $appid    = $_POST['wx_appid'];
        $pid      = $_POST['pid'];
        $eventype = $_POST['eventype'];
        $name     = $_POST['name'];
        $eventkey = $_POST['eventkey'];
        $operator = $_POST['operator'];
        $msgtype  = $_POST['msgtype'];
        $msg      = $_POST['msg'];
        $status   = $_POST['status'];

        $eventype OR iUI::alert("请选择事件类型");
        $name OR iUI::alert("请填写事件名称");
        $eventkey OR iUI::alert("请填写事件KEY值");
        if($eventype=="keyword"){
            $operator OR iUI::alert("请选择关键词匹配模式");
        }
        $msgtype OR iUI::alert("请选择回复消息的类型");
        $msg OR iUI::alert("请填写回复内容");

        $msg     = stripslashes_deep($msg);
        $msg     = addslashes(json_encode($msg));
        $addtime = time();
        $fields  = array('appid','pid', 'name', 'eventype', 'eventkey', 'msgtype', 'operator', 'msg', 'addtime', 'status');
        $data    = compact ($fields);
        if(empty($id)) {
            iDB::value("
              SELECT `id`
              FROM `#iCMS@__weixin_event`
              WHERE `eventkey` ='$eventkey' AND `appid` ='$appid'
            ") && iUI::alert('该事件已经存在!');

            iDB::insert('weixin_event',$data);
            $msg = '添加完成';
        }else{
            iDB::update('weixin_event', $data, array('id'=>$id));
            $msg = '更新完成';
        }
        iUI::success($msg,'url:'.APP_URI.'&do=event&wx_appid='.$appid);
    }
    public function do_event_del($id = null,$dialog=true){
      $id===null && $id=$_GET['id'];
      $id OR iUI::alert('请选择要删除的事件!');
      iDB::query("DELETE FROM `#iCMS@__weixin_event` WHERE `id` = '$id';");
      $dialog && iUI::success("已经删除!",'url:'.APP_URI.'&do=event');
    }
    public function value($val,$field='*',$where='id'){
      return iDB::row("SELECT {$field} FROM `#iCMS@__weixin` where `$where`='{$val}'",ARRAY_A);
    }
    public function del($id=null){
        $rs = $this->value($id,'appid');
        $appid = $rs['appid'];
        iDB::query("DELETE FROM `#iCMS@__weixin_event` WHERE `appid` = '$appid'");
        iDB::query("DELETE FROM `#iCMS@__weixin_api_log` WHERE `appid` = '$appid'");
        iDB::query("DELETE FROM `#iCMS@__weixin` WHERE `id` = '$id'");
    }
    public function option(){
      $rs = iDB::all("SELECT * FROM `#iCMS@__weixin` order by id DESC");
      if($rs)foreach ((array)$rs as $wx) {
          $option.= '<option value="'.$wx['appid'].'" data-type="'.$wx['type'].'">' . $wx['name'] . ' [APPID:'.$wx['appid'].']</option>';
      }
      return $option;
    }

    public function menu_get_type($type,$out='value'){
      $type_map = array(
        'click'              =>array('key','点击事件'),
        'view'               =>array('url','跳转URL'),
        'miniprogram'        =>array('url','小程序'),
        'scancode_push'      =>array('key','扫码推事件'),
        'scancode_waitmsg'   =>array('key','扫码带提示'),
        'pic_sysphoto'       =>array('key','系统拍照发图'),
        'pic_photo_or_album' =>array('key','拍照或者相册发图'),
        'pic_weixin'         =>array('key','微信相册发图器'),
        'location_select'    =>array('key','地理位置选择器'),
        'media_id'           =>array('media_id','素材(第三方)'),
        'view_limited'       =>array('media_id','图文(第三方)')
      );

      if($out=='value'){
        empty($type) && $type='click';
        return $type_map[$type][0];
      }
      if($out=='opt'){
        $option = '';
        foreach ($type_map as $key => $value) {
          $seltext = '';
          if($type==$key){
            $seltext =' selected="selected"';
          }
          $option.='<option value="'.$key.'"'.$seltext.'>'.$value[1].'</option>';
        }
        return $option;
      }
    }
    public function menu_button_li($key='~KEY~',$i='~i~',$a=array()){
      $keyname = $this->menu_get_type($a['type']);
      $html = '<li>'.
        '<div class="input-prepend input-append">'.
          '<span class="add-on">类型</span>'.
          '<select name="menuData['.$key.'][sub_button]['.$i.'][type]">'.
            $this->menu_get_type($a['type'],'opt').
          '</select>'.
          '<span class="add-on">名称</span>'.
          '<input type="text" class="span2" name="menuData['.$key.'][sub_button]['.$i.'][name]" value="'.$a['name'].'">'.
          '<span class="button_key">'.
            '<span class="add-on">'.strtoupper($keyname).'</span>'.
            '<input type="text" name="menuData['.$key.'][sub_button]['.$i.']['.$keyname.']" value="'.$a[$keyname].'">';
      if($a['appid']){
        $html.= '<span class="add-on">APPID</span>'.
                '<input type="text" name="menuData['.$key.'][sub_button]['.$i.'][appid]" value="'.$a['appid'].'">';
      }
      if($a['pagepath']){
        $html.= '<span class="add-on pagepath">PAGEPATH</span>'.
                '<input type="text" name="menuData['.$key.'][sub_button]['.$i.'][pagepath]" value="'.$a['pagepath'].'">';
      }

      $html.= '</span>'.
          '<a href="javascript:void(0);" class="btn wx_del_sub_button"><i class="fa fa-del"></i>删除</a>'.
        '</div>'.
      '</li>';
      return $html;
    }
    public static function modal_btn($title='',$target='MediaId',$type='news',$callback='media',$do='media',$from='modal'){
        $href   = APP_URI."&do={$do}&type={$type}&from={$from}&target={$target}&callback={$callback}";
        $_GET['wx_appid'] && $href.='&wx_appid='.$_GET['wx_appid'];

        $_title = $title.'文件';
        return '<a href="'.$href.'" class="btn media_modal" data-toggle="modal" title="选择'.$_title.'"><i class="fa fa-search"></i> 选择</a>';
    }
    // public function do_save(){
    //     iUI::success('更新完成');
    // }
    // public function cache(){
    // }
    public function weixin_init(){
        $config = $this->value($this->wx_appid,'*','appid');
        $config OR iUI::alert('获取不到公众号配置');
        weixin::process_config($config,false);
        weixin::init($config);
    }
}
