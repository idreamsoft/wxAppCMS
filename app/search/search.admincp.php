<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class searchAdmincp{
    public function __construct() {
    	$this->id	= (int)$_GET['id'];
    }

    public function do_iCMS(){
        if($_GET['keywords']) {
			$sql =" WHERE `search` like '%{$_GET['keywords']}%'";
        }

        list($orderby,$orderby_option) = get_orderby(array(
            'id'    =>"ID",
            'times' =>"搜索次数",
        ));
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__search_log` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"条记录");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__search_log` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include admincp::view("search.manage");
    }
    public function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
		$id OR iUI::alert('请选择要删除的记录!');
		iDB::query("DELETE FROM `#iCMS@__search_log` WHERE `id` = '$id'");
		$dialog && iUI::success('记录已经删除','js:parent.$("#id'.$id.'").remove();');
    }
    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要操作的记录");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iUI::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iUI::$break	= true;
				iUI::success('记录全部删除完成!','js:1');
    		break;
		}
	}
}
