<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class userAdmincp{
    public $groupAdmincp = null;
    public function __construct() {
        $this->appid        = iCMS_APP_USER;
        $this->uid          = (int)$_GET['id'];
        $this->groupAdmincp = new groupAdmincp(0);
    }
    public function do_config(){
        configAdmincp::app($this->appid);
    }
    public function do_save_config(){
        foreach ((array)$_POST['config']['open'] as $key => $value) {
            if($value['appid'] && $value['appkey']){
                $_POST['config']['open'][$key]['enable'] = true;
            }
        }

        configAdmincp::save($this->appid);
    }
    public function do_update(){
        $data = iSQL::update_args($_GET['_args']);
        $data && iDB::update('user',$data,array('uid'=>$this->uid));
        iUI::success('操作成功!','js:1');
    }
    public function do_add(){
        if($this->uid) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__user` WHERE `uid`='$this->uid' LIMIT 1;");
            $rs && $userdata = iDB::row("SELECT * FROM `#iCMS@__user_data` WHERE `uid`='$this->uid' LIMIT 1;");
        }
        iPHP::callback(array("formerApp","add"),array($this->appid,(array)$rs,true));
        iPHP::callback(array("apps_meta","get"),array($this->appid,$this->uid));
        include admincp::view("user.add");
    }
    /**
     * [登陆用户]
     * @return [type] [description]
     */
    public function do_login(){
        if($this->uid) {
            $user = iDB::row("SELECT * FROM `#iCMS@__user` WHERE `uid`='$this->uid' LIMIT 1;",ARRAY_A);
            user::set_cookie($user['username'],$user['password'],$user);
            $url = iURL::router(array('uid:home',$this->uid));
            iPHP::redirect($url);
        }
    }
    public function do_iCMS(){
        $sql = "WHERE 1=1";
        $pid = $_GET['pid'];
        if($_GET['keywords']) {
            $sql.=" AND CONCAT(username,nickname) REGEXP '{$_GET['keywords']}'";
        }

        $_GET['gid'] && $sql.=" AND `gid`='{$_GET['gid']}'";
        if(isset($_GET['status']) && $_GET['status']!==''){
            $sql.=" AND `status`='{$_GET['status']}'";
        }
        $_GET['regip'] && $sql.=" AND `regip`='{$_GET['regip']}'";
        $_GET['loginip'] && $sql.=" AND `lastloginip`='{$_GET['loginip']}'";

        if(isset($_GET['pid']) && $pid!='-1'){
            $uri_array['pid'] = $pid;
            if($_GET['pid']==0){
                $sql.= " AND `pid`=''";
            }else{
                iMap::init('prop',$this->appid,'pid');
                $map_where = iMap::where($pid);
            }
        }

        if($map_where){
            $map_sql = iSQL::select_map($map_where);
            $sql     = ",({$map_sql}) map {$sql} AND `uid` = map.`iid`";
        }
        list($orderby,$orderby_option) = get_orderby(array(
            'uid'        =>"UID",
            'hits'       =>"点击",
            'hits_week'  =>"周点击",
            'hits_month' =>"月点击",
            'fans'       =>"粉丝数",
            'follow'     =>"关注数",
            'article'    =>"文章数",
            'favorite'   =>"收藏数",
            'comments'   =>"评论数",
        ));

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__user` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个用户");
        $limit  = 'LIMIT '.iUI::$offset.','.$maxperpage;
        if($map_sql||iUI::$offset){
            $ids_array = iDB::all("
                SELECT `uid` FROM `#iCMS@__user` {$sql}
                ORDER BY {$orderby} {$limit}
            ");
            $ids   = iSQL::values($ids_array,'uid');
            $ids   = $ids?$ids:'0';
            $sql   = "WHERE `uid` IN({$ids})";
            $limit = '';
        }
        $rs     = iDB::all("SELECT * FROM `#iCMS@__user` {$sql} ORDER BY {$orderby} {$limit}");
        $_count = count($rs);
        $propArray = propAdmincp::get("pid",null,'array');
        include admincp::view("user.manage");
    }
    public function do_save(){
        $uid      = (int)$_POST['uid'];
        $pid      = implode(',', (array)$_POST['pid']);
        $_pid     = iSecurity::escapeStr($_POST['_pid']);
        $user     = iSecurity::escapeStr($_POST['user']);
        $userdata = iSecurity::escapeStr($_POST['userdata']);
        $username = iSecurity::escapeStr($user['username']);
        $nickname = iSecurity::escapeStr($user['nickname']);
        $password = iSecurity::escapeStr($user['password']);
        unset($user['password']);

        $username OR iUI::alert('账号不能为空');
        preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/i",$username) OR iUI::alert('该账号格式不对');
        $nickname OR iUI::alert('昵称不能为空');

        $user['regdate']       = str2time($user['regdate']);
        $user['lastlogintime'] = str2time($user['lastlogintime']);
        $user['pid']           = $pid;

       if(empty($uid)) {
            $password OR iUI::alert('密码不能为空');
            $user['password'] = md5($password);
            iDB::value("SELECT `uid` FROM `#iCMS@__user` where `username` ='$username' LIMIT 1") && iUI::alert('该账号已经存在');
            iDB::value("SELECT `uid` FROM `#iCMS@__user` where `nickname` ='$nickname' LIMIT 1") && iUI::alert('该昵称已经存在');
            $uid = iDB::insert('user',$user);
            iMap::init('prop',iCMS_APP_USER,'pid');
            $pid && iMap::add($pid,$uid);
            $msg = "账号添加完成!";
        }else {
            iDB::value("SELECT `uid` FROM `#iCMS@__user` where `username` ='$username' AND `uid` !='$uid' LIMIT 1") && iUI::alert('该账号已经存在');
            iDB::value("SELECT `uid` FROM `#iCMS@__user` where `nickname` ='$nickname' AND `uid` !='$uid' LIMIT 1") && iUI::alert('该昵称已经存在');
            $password && $user['password'] = md5($password);
            iDB::update('user', $user, array('uid'=>$uid));
            iMap::init('prop',iCMS_APP_USER,'pid');
            iMap::diff($pid,$_pid,$uid);
            if(iDB::value("SELECT `uid` FROM `#iCMS@__user_data` where `uid`='$uid' LIMIT 1")){
                iDB::update('user_data', $userdata, array('uid'=>$uid));
            }else{
                $userdata['uid'] = $uid;
                iDB::insert('user_data',$userdata);
            }
            $msg = "账号修改完成!";
        }
        iPHP::callback(array("apps_meta","save"),array($this->appid,$uid));
        iPHP::callback(array("formerApp","save"),array($this->appid,$uid));
        iUI::success($msg,'url:'.APP_URI);
    }
    public function do_batch(){
    	$idA	= (array)$_POST['id'];
    	$idA OR iUI::alert("请选择要操作的用户");
    	$ids	= implode(',',(array)$_POST['id']);
    	$batch	= $_POST['batch'];
    	switch($batch){
            case 'prop':
                iMap::init('prop',iCMS_APP_USER,'pid');

                $pid = implode(',', (array)$_POST['pid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_pid = iDB::value("SELECT `pid` FROM `#iCMS@__user` where `uid`='$id' LIMIT 1");
                    iDB::update('user',compact('pid'),array('uid'=>$id));
                    iMap::diff($pid,$_pid,$id);
                }
                iUI::success('用户属性设置完成!','js:1');

            break;
    		case 'dels':
                iUI::$break = false;
	    		foreach($idA AS $id){
	    			$this->do_del($id,false);
	    		}
                iUI::$break = true;
				iUI::success('用户全部删除完成!','js:1');
    		break;
		}
	}
    public function do_del($uid = null,$dialog=true){
    	$uid===null && $uid=$this->uid;
		$uid OR iUI::alert('请选择要删除的用户');
        iDB::query("DELETE FROM `#iCMS@__user` WHERE `uid` = '$uid'");
        iDB::query("DELETE FROM `#iCMS@__user_category` WHERE `uid` = '$uid'");
        iDB::query("DELETE FROM `#iCMS@__user_data` WHERE `uid` = '$uid'");
        iDB::query("DELETE FROM `#iCMS@__user_follow` WHERE `uid` = '$uid'");
        iDB::query("DELETE FROM `#iCMS@__user_openid` WHERE `uid` = '$uid'");
        iDB::query("DELETE FROM `#iCMS@__report` WHERE `uid` = '$uid'");
        iDB::query("DELETE FROM `#iCMS@__user` WHERE `uid` = '$uid'");
        if(iDB::check_table('user_cdata')){
            iDB::query("DELETE FROM `#iCMS@__user_cdata` WHERE `user_id` = '$uid'");
        }
        // iMap::del_data($uid,iCMS_APP_USER,'prop');
		$dialog && iUI::success('用户删除完成','js:parent.$("#id'.$uid.'").remove();');
    }
    public static function _count(){
        return iDB::value("SELECT count(*) FROM `#iCMS@__user`");
    }
}
