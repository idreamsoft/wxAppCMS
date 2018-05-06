<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class keywordsAdmincp{
    public function __construct() {
        $this->appid = iCMS_APP_KEYWORDS;
    	$this->id	= (int)$_GET['id'];
    }
    public function do_config(){
        configAdmincp::app($this->appid);
    }
    public function do_save_config(){
        configAdmincp::save($this->appid);
    }
    public function do_add(){
        if($this->id) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__keywords` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        }else{
            $rs['keyword'] = $_GET['keyword'];
            $rs['replace'] = $_GET['replace'];
        }
        $_GET['url'] && $rs['replace'] =  self::get_replace($rs['keyword'],$_GET['url']);

        include admincp::view("keywords.add");
    }
    public function do_save(){
        $id      = (int)$_POST['id'];
        $keyword = iSecurity::escapeStr($_POST['keyword']);
        $replace = iSecurity::escapeStr($_POST['replace']);

        $keyword OR iUI::alert('关键词不能为空!');
        $replace OR iUI::alert('替换词不能为空!');
        $fields = array('keyword', 'replace');
        $data   = compact($fields);

        if(empty($id)) {
            iDB::value("SELECT `id` FROM `#iCMS@__keywords` where `keyword` ='$keyword'") && iUI::alert('该关键词已经存在!');
            iDB::insert('keywords',$data);
            $msg="关键词添加完成!";
        }else {
            iDB::value("SELECT `id` FROM `#iCMS@__keywords` where `keyword` ='$keyword' AND `id` !='$id'") && iUI::alert('该关键词已经存在!');
            iDB::update('keywords', $data, array('id'=>$id));
            $msg="关键词编辑完成!";
        }
        $this->cache();
        iUI::success($msg,'url:'.APP_URI);
    }
    public static function insert($name,$url=null){
        if(is_array($name) && empty($url)){
            $data = $name;
        }else{
            $data = array();
            $data['keyword'] = $name;
            $data['replace'] = self::get_replace($name,$url);
            array_map('addslashes', $data);
        }
        if($data){
            if(!iDB::value("SELECT `id` FROM `#iCMS@__keywords` where `keyword` ='".$data['keyword']."'")){
                return iDB::insert('keywords',$data);
            }
        }
        return false;
    }
    public function do_iCMS(){
        if($_GET['keywords']) {
			$sql=" WHERE `keyword` REGEXP '{$_GET['keywords']}'";
        }
        list($orderby,$orderby_option) = get_orderby();

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
		$total		= iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__keywords` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个关键词");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__keywords` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include admincp::view("keywords.manage");
    }
    public function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
		$id OR iUI::alert('请选择要删除的关键词!');
		iDB::query("DELETE FROM `#iCMS@__keywords` WHERE `id` = '$id'");
		$this->cache();
		$dialog && iUI::success('关键词已经删除','js:parent.$("#id'.$id.'").remove();');
    }
    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要操作的关键词");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iUI::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iUI::$break	= true;
				iUI::success('关键词全部删除完成!','js:1');
    		break;
		}
	}
    public function do_cache($dialog=true){
        $this->cache();
        $dialog && iUI::success('更新完成');
    }
    public static function cache(){
        $rs    = iDB::all("SELECT * FROM `#iCMS@__keywords` ORDER BY CHAR_LENGTH(`keyword`) DESC");
        $array = array();
        foreach((array)$rs AS $i=>$val) {
            $array[] = array($val['keyword'],htmlspecialchars_decode($val['replace']));
        }
        iCache::set(keywordsApp::CACHE_KEY,$array,0);
    }
    public static function get_replace($name,$url){
        return htmlspecialchars('<a href="'.$url.'" target="_blank" class="keywords">'.$name.'</a>');
    }
    public static function _count(){
        return iDB::value("SELECT count(*) FROM `#iCMS@__keywords`");
    }
}
