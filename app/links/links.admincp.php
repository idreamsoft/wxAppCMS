<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class linksAdmincp{
    public static $appid = null;
    public function __construct() {
        self::$appid = apps::id(__CLASS__);
        $this->id    = (int)$_GET['id'];
    }
    public function do_add(){
        if($this->id) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__links` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        }else{
            $rs['keyword'] = $_GET['keyword'];
            $rs['url']     = $_GET['url'];
        }
        iPHP::callback(array("formerApp","add"),array(self::$appid,$rs,true));
        include admincp::view("links.add");
    }
    public function do_save(){
		$id			= (int)$_POST['id'];
		$cid		= (int)$_POST['cid'];
		$name		= iSecurity::escapeStr($_POST['name']);
		$logo		= iSecurity::escapeStr($_POST['logo']);
		$url		= iSecurity::escapeStr($_POST['url']);
		$desc		= iSecurity::escapeStr($_POST['desc']);
		$sortnum	= (int)$_POST['sortnum'];

        $name 	OR iUI::alert('网站不能为空!');
        $url 	OR iUI::alert('链接不能为空!');
        $fields = array('cid', 'name', 'logo', 'url', 'desc', 'sortnum');
        $data   = compact ($fields);


        if(empty($id)) {
            iDB::value("SELECT `id` FROM `#iCMS@__links` where `name` ='$name'") && iUI::alert('该网站已经存在!');
            $id = iDB::insert('links',$data);
            $msg="网站添加完成!";
        }else {
            iDB::value("SELECT `id` FROM `#iCMS@__links` where `name` ='$name' AND `id` !='$id'") && iUI::alert('该网站已经存在!');
            iDB::update('links', $data, array('id'=>$id));
            $msg="网站编辑完成!";
        }
        iPHP::callback(array("formerApp","save"),array(self::$appid,$id));
        iUI::success($msg,'url:'.APP_URI);
    }

    public function do_iCMS(){
        if($_GET['keywords']) {
			$sql=" WHERE CONCAT(name,url) REGEXP '{$_GET['keywords']}'";
        }
        if($_GET['cid']) {
			$sql=" WHERE `cid` = '{$_GET['cid']}'";
        }

        list($orderby,$orderby_option) = get_orderby();
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total		= iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__links` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个网站");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__links` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include admincp::view("links.manage");
    }
    public function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
		$id OR iUI::alert('请选择要删除的网站!');
		iDB::query("DELETE FROM `#iCMS@__links` WHERE `id` = '$id'");
		$dialog && iUI::success('网站已经删除','js:parent.$("#id'.$id.'").remove();');
    }
    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要操作的网站");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iUI::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iUI::$break	= true;
				iUI::success('网站全部删除完成!','js:1');
    		break;
		}
	}
    public static function _count(){
        return iDB::value("SELECT count(*) FROM `#iCMS@__links`");
    }
}
