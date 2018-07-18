<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class contentAdmincp{
    public $appid     = null;
    public $app       = null;
    public $callback  = array();
    public $table     = null;
    public $primary   = null;
    public $union_key = null;
    public $config    = null;

    protected $_view_add     = 'content.add';
    protected $_view_manage  = 'content.manage';
    protected $_view_tpl_dir = null;

    public function __construct($data=null,$dir=null) {
        if($data===null){
            $id = iSecurity::getGP('appid');
            $data = apps::get_app($id);
        }else if(!is_array($data)){
            $data = apps::get_app($data);
        }
        $this->app       = $data;
        $this->appid     = $data['id'];
        $_GET['appid'] && $this->appid = (int)$_GET['appid'];

        $table_array        = apps::get_table($this->app);
        content::$app       = $this->app['app'];
        content::$table     = $table_array['table'];
        content::$primary   = $table_array['primary'];
        content::$union_key = apps_mod::data_union_key($this->app['app']);
        unset($table_array);

        $this->id        = (int)$_GET['id'];
        $this->_postype  = '1';
        $this->_status   = '1';
        $this->config    = iCMS::$config[$this->app['app']];
        category::$appid = $this->appid;
        $this->_view_tpl_dir = $dir;
    }
    public function do_config(){
        configAdmincp::app($this->appid);
    }
    public function do_save_config(){
        configAdmincp::save($this->appid);
    }
    public function do_add(){
      $rs = apps_mod::get_data($this->app,$this->id);
      isset($rs['status']) OR $rs['status'] = '1';
      iPHP::callback(array("apps_meta","get"),array($this->appid,$this->id));
      iPHP::callback(array("formerApp","add"),array($this->app,$rs));
      include admincp::view($this->_view_add,$this->_view_tpl_dir);
    }
    public function do_update(){
        $data = iSQL::update_args($_GET['_args']);
        if($data){
            if(isset($data['pid'])){
                iMap::init('prop',$this->appid,'pid');
                $_pid = content::value('pid',$this->id);
                iMap::diff($data['pid'],$_pid,$this->id);
            }
            content::update($data,array('id'=>$this->id));
        }
        iUI::success('操作成功!','js:1');
    }
    public function do_updateorder(){
        foreach((array)$_POST['sortnum'] as $sortnum=>$id){
            content::update(compact('sortnum'),compact('id'));
        }
    }
    public function do_batch(){
        $_POST['id'] OR iUI::alert("请选择要操作的".$this->app['name']);
        $ids    = implode(',',(array)$_POST['id']);
        $batch  = $_POST['batch'];
        switch($batch){
            case 'order':
                foreach((array)$_POST['sortnum'] AS $id=>$sortnum) {
                    content::update(compact('sortnum'),compact('id'));
                }
                iUI::success('排序已更新!','js:1');
            break;
            case 'meta':
                foreach((array)$_POST['id'] AS $id) {
                    iPHP::callback(array("apps_meta","save"),array($this->appid,$id));
                }
                iUI::success('添加完成!','js:1');
            break;
            case 'baiduping':
                foreach((array)$_POST['id'] AS $id) {
                    $msg.= $this->do_baiduping($id,false);
                }
                iUI::success($msg,'js:1');
            break;
            case 'move':
                $_POST['cid'] OR iUI::alert("请选择目标栏目!");
                iMap::init('category',$this->appid,'cid');
                $cid = (int)$_POST['cid'];
                category::check_priv($cid,'ca','alert');
                foreach((array)$_POST['id'] AS $id) {
                    $_cid = content::value('cid',$id);
                    content::update(compact('cid'),compact('id'));
                    if($_cid!=$cid) {
                        iMap::diff($cid,$_cid,$id);
                        categoryAdmincp::update_count($_cid,'-');
                        categoryAdmincp::update_count($cid);
                    }
                }
                iUI::success('成功移动到目标栏目!','js:1');
            break;
            case 'prop':
                iMap::init('prop',$this->appid,'pid');
                $pid = implode(',', (array)$_POST['pid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_pid = content::value('pid',$id);
                    content::update(compact('pid'),compact('id'));
                    iMap::diff($pid,$_pid,$id);
                }
                iUI::success('属性设置完成!','js:1');
            break;
            case 'weight':
                $data = array('weight'=>$_POST['mweight']);
            break;
            case 'keyword':
                if($_POST['pattern']=='replace') {
                    $data = array('keywords'=>iSecurity::escapeStr($_POST['mkeyword']));
                }elseif($_POST['pattern']=='addto') {
                    foreach($_POST['id'] AS $id){
                        $keywords = content::value('keywords',$id);
                        $keywords = $keywords?$keywords.','.iSecurity::escapeStr($_POST['mkeyword']):iSecurity::escapeStr($_POST['mkeyword']);
                        content::update(compact('keywords'),compact('id'));
                    }
                    iUI::success('关键字更改完成!','js:1');
                }
            break;
            case 'tag':
                foreach($_POST['id'] AS $id){
                    $art  = content::row($id,'tags,cid');
                    $mtag = iSecurity::escapeStr($_POST['mtag']);
                    if($_POST['pattern']=='replace') {
                    }elseif($_POST['pattern']=='addto') {
                        $art['tags'] && $mtag = $art['tags'].','.$mtag;
                    }
                    $tags = tag::diff($mtag,$art['tags'],members::$userid,$id,$art['cid']);
                    $tags = addslashes($tags);
                    content::update(compact('tags'),compact('id'));
                }
                iUI::success('标签更改完成!','js:1');
            break;
            case 'dels':
                iUI::$break = false;
                iUI::flush_start();
                $_count = count($_POST['id']);
                foreach((array)$_POST['id'] AS $i=>$id) {
                    $this->do_del($id,false);
                    $updateMsg  = $i?true:false;
                    $timeout    = ($i++)==$_count?'3':false;
                    iUI::dialog($id."删除",'js:parent.$("#id'.$id.'").remove();',$timeout,0,$updateMsg);
                    iUI::flush();
                }
                iUI::$break = true;
                iUI::success('全部删除完成!','js:1',3,0,true);
            break;
            default:
                $data = iSQL::update_args($batch);
        }
        $data && content::batch($data,$ids);
        iUI::success('操作成功!','js:1');
    }
    /**
     * [百度推送 ]
     * @param  [type]  $id     [description]
     * @param  boolean $dialog [description]
     * @return [type]          [description]
     */
    public function do_baiduping($id = null,$dialog=true){
        $id===null && $id=$this->id;
        $id OR iUI::alert('请选择要推送的文章!');
        $rs   = content::row($id);
        $C    = category::get($rs['cid']);
        $iurl = (array)iURL::get($this->app['app'],array($rs,$C));
        $urls = array();
        $urls[] = $iurl['href'];
        if($iurl['mobile']['url']){
            $urls[] = $iurl['mobile']['url'];
        }
        $res = plugin_baidu::ping($urls);
        // if($iurl['mip']['url']){
        //     $mip = plugin_baidu::ping($iurl['mip']['url'],'mip');
        // }
        if($res===true){
            $msg = '推送完成';
            $dialog && iUI::success($msg,'js:1');
        }else{
            $msg = '推送失败！['.$res->message.']';
            $dialog && iUI::alert($msg,'js:1');
        }
        if(!$dialog) return $msg.'<br />';
    }
    public function do_iCMS(){
    	admincp::$APP_METHOD="domanage";
    	$this->do_manage();
    }
    public function do_inbox(){
        $this->do_manage("inbox");
    }
    public function do_trash(){
        $this->_postype = 'all';
        $this->do_manage("trash");
    }
    public function do_user(){
        $this->_postype = 0;
        $this->do_manage();
    }
    public function do_examine(){
        $this->_postype = 0;
        $this->do_manage("examine");
    }
    public function do_off(){
        $this->_postype = 0;
        $this->do_manage("off");
    }
    public function do_manage($stype='normal') {
        $cid = (int)$_GET['cid'];
        $pid = $_GET['pid'];
        //$stype OR $stype = admincp::$APP_DO;
        $stype_map = array(
            'inbox'   =>'0',//草稿
            'normal'  =>'1',//正常
            'trash'   =>'2',//回收站
            'examine' =>'3',//待审核
            'off'     =>'4',//未通过
        );
        $map_where = array();
        //status:[0:草稿][1:正常][2:回收][3:待审核][4:不合格]
        //postype: [0:用户][1:管理员]
        $stype && $this->_status = $stype_map[$stype];
        if(isset($_GET['pt']) && $_GET['pt']!=''){
            $this->_postype = (int)$_GET['pt'];
        }
        if(isset($_GET['status'])){
            $this->_status = (int)$_GET['status'];
        }
        $sql = "WHERE `status`='{$this->_status}'";
        $this->_postype==='all' OR $sql.= " AND `postype`='{$this->_postype}'";

        if(members::check_priv($this->app['app'].".VIEW")){
            $_GET['userid'] && $sql.= iSQL::in($_GET['userid'],'userid');
        }else{
            $sql.= iSQL::in(members::$userid,'userid');
        }

        if(isset($_GET['pid']) && $pid!='-1'){
            $uri_array['pid'] = $pid;
            if(empty($_GET['pid'])){
                $sql.= " AND `pid`=''";
            }else{
                iMap::init('prop',$this->appid,'pid');
                $map_where+=iMap::where($pid);
            }
        }

        $cp_cids = category::check_priv('CIDS','cs');//取得所有有权限的栏目ID

        if($cp_cids) {
            if(is_array($cp_cids)){
                if($cid){
                    array_search($cid,$cp_cids)===false && admincp::permission_msg('栏目[cid:'.$cid.']',$ret);
                }else{
                    $cids = $cp_cids;
                }
            }else{
                $cids = $cid;
            }
            if($_GET['sub'] && $cid){
                $cids = categoryApp::get_cids($cid,true);
                array_push ($cids,$cid);
            }
            if($_GET['scid'] && $cid){
                iMap::init('category',$this->appid,'cid');
                $map_where+= iMap::where($cids);
            }else{
                $sql.= iSQL::in($cids,'cid');
            }
        }else{
            $sql.= iSQL::in('-1','cid');
        }

        if($_GET['keywords']) {
            $kws = $_GET['keywords'];
            switch ($_GET['st']) {
                case "title": $sql.=" AND `title` REGEXP '{$kws}'";break;
                case "id":
                $kws = str_replace(',', "','", $kws);
                $sql.=" AND `id` IN ('{$kws}')";
                break;
            }
        }

        $sql.= category::search_sql($cid);

        isset($_GET['nopic'])&& $sql.=" AND `haspic` ='0'";
        isset($_GET['pic'])&& $sql.=" AND `haspic` ='".($_GET['pic']?1:0)."'";

        $_GET['starttime'] && $sql.=" AND `pubdate`>='".str2time($_GET['starttime'].(strpos($_GET['starttime'],' ')!==false?'':" 00:00:00"))."'";
        $_GET['endtime']   && $sql.=" AND `pubdate`<='".str2time($_GET['endtime'].(strpos($_GET['endtime'],' ')!==false?'':" 23:59:59"))."'";
        $_GET['post_starttime'] && $sql.=" AND `postime`>='".str2time($_GET['post_starttime'].(strpos($_GET['post_starttime'],' ')!==false?'':" 00:00:00"))."'";
        $_GET['post_endtime']   && $sql.=" AND `postime`<='".str2time($_GET['post_endtime'].(strpos($_GET['post_endtime'],' ')!==false?'':" 23:59:59"))."'";



        isset($_GET['userid']) && $uri_array['userid']  = (int)$_GET['userid'];
        isset($_GET['keyword'])&& $uri_array['keyword'] = $_GET['keyword'];
        isset($_GET['tag'])    && $uri_array['tag']     = $_GET['tag'];
        isset($_GET['pt'])     && $uri_array['pt']      = $_GET['pt'];
        isset($_GET['cid'])    && $uri_array['cid']     = $_GET['cid'];
        $uri_array  && $uri = http_build_query($uri_array);

        list($orderby,$orderby_option) = get_orderby(array(
            content::$primary =>"ID",
            'hits'       =>"点击",
            'hits_week'  =>"周点击",
            'hits_month' =>"月点击",
            'good'       =>"顶",
            'postime'    =>"时间",
            'pubdate'    =>"发布时间",
            'comments'   =>"评论数",
        ));

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;

        if($map_where){
            $map_sql = iSQL::select_map($map_where);
            $sql     = ",({$map_sql}) map {$sql} AND `id` = map.`iid`";
        }

        $total = iCMS::page_total_cache("SELECT count(*) FROM `".content::$table."` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"条记录");

        $limit = 'LIMIT '.iUI::$offset.','.$maxperpage;

        if($map_sql||iUI::$offset){
            if(iUI::$offset > 1000 && $total > 2000 && iUI::$offset >= $total/2) {
                $_offset = $total-iUI::$offset-$maxperpage;
                if($_offset < 0) {
                    $_offset = 0;
                }
                $orderby = "id ASC";
                $limit = 'LIMIT '.$_offset.','.$maxperpage;
            }
        // if($map_sql){
            $ids_array = iDB::all("
                SELECT `id` FROM `".content::$table."` {$sql}
                ORDER BY {$orderby} {$limit}
            ");
            if(isset($_offset)){
                $ids_array = array_reverse($ids_array, TRUE);
                $orderby   = "id DESC";
            }

            $ids = iSQL::values($ids_array);
            $ids = $ids?$ids:'0';
            $sql = "WHERE `id` IN({$ids})";
            // }else{
                // $sql = ",(
                    // SELECT `id` AS aid FROM `".content::$table."` {$sql}
                    // ORDER BY {$orderby} {$limit}
                // ) AS art WHERE `id` = art.aid ";
            // }
            $limit = '';
        }
        $rs = iDB::all("SELECT * FROM `".content::$table."` {$sql} ORDER BY {$orderby} {$limit}");
        $_count = count($rs);
        $propArray = propAdmincp::get("pid",null,'array');
        include admincp::view($this->_view_manage,$this->_view_tpl_dir);
    }
    public function do_save(){
        $update = iPHP::callback(array("formerApp","save"),array($this->app));
        iPHP::callback(array("apps_meta","save"),array($this->appid,formerApp::$primary_id));
        iPHP::callback(array("spider","callback"),array($this,formerApp::$primary_id));

        if($this->callback['return']){
            return $this->callback['return'];
        }
        // $REFERER_URL = $_POST['REFERER'];
        // if(empty($REFERER_URL)||strstr($REFERER_URL, '=save')){
        // }
        $REFERER_URL= APP_URI.'&do=manage';
        if($update){
            iUI::success($this->app['name'].'编辑完成!<br />3秒后返回'.$this->app['name'].'列表','url:'.$REFERER_URL);
        }else{
            $moreBtn = array(
                    array("text" =>"查看该".$this->app['name'],"target"=>'_blank',"url"=>$article_url,"close"=>false),
                    array("text" =>"编辑该".$this->app['name'],"url"=>APP_URI."&do=add&id=".formerApp::$primary_id),
                    array("text" =>"继续添加".$this->app['name'],"url"=>APP_URI."&do=add&cid=".$cid),
                    array("text" =>"返回".$this->app['name']."列表","url"=>$REFERER_URL),
                    array("text" =>"查看网站首页","url"=>iCMS_URL,"target"=>'_blank')
            );
            iUI::$dialog['modal'] = true;
            iUI::dialog('success:#:check:#:'.$this->app['name'].'添加完成!<br />10秒后返回'.$this->app['name'].'列表'.$msg,'url:'.$REFERER_URL,10,$moreBtn);
            // iUI::success($this->app['name'].'添加完成!<br />3秒后返回'.$this->app['name'].'列表','url:'.$REFERER_URL);
        }
    }

    public function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
		$id OR iUI::alert("请选择要删除的{$this->app['title']}");

        $tables = $this->app['table'];
        foreach ($tables as $key => $value) {
            $primary_key = $value['primary'];
            $value['union'] && $primary_key = $value['union'];
            iDB::query("DELETE FROM `{$value['table']}` WHERE `{$primary_key}`='$id'");
        }
		$dialog && iUI::success("{$this->app['title']}删除完成",'js:parent.$("#id'.$id.'").remove();');
    }

    // public static function menu($menu){
    //     $path     = iPHP_APP_DIR.'/apps/etc/content.menu.json.php';
    //     $json     = file_get_contents($path);
    //     $json     = str_replace("<?php defined('iPHP') OR exit('What are you doing?');? >\n", '', $json);
    //     $variable = array();
    //     $array    = apps::get_array(array("apptype"=>'2'));
    //     if($array)foreach ($array as $key => $value) {
    //         if($value['config']['menu']){
    //             $sort = 200000+$key;

    //             $json = str_replace(
    //                 array('{appid}','{app}','{name}','{sort}'),
    //                 array($value['id'],$value['app'],$value['name'],$sort), $json);

    //             if($value['config']['menu']!='main'){
    //                 $json = '[{"id": "'.$value['config']['menu'].'","children":[{"caption": "-"},'.$json.']}]';
    //             }else{
    //                 $json = '['.$json.']';
    //             }

    //             $array  = json_decode($json,true);
    //             if($array){
    //                 $array = $menu::mid($array,$sort);
    //                 $variable[] = $array;
    //             }
    //         }
    //     }
    //     return $variable;
    // }
}
