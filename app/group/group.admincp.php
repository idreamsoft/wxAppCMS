<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class groupAdmincp{
    public $gid   = NULL;
    public $group = NULL;
    public $array = NULL;
    public $type  = NULL;

    public function __construct($type=null) {
    	$this->gid	= (int)$_GET['gid'];
    	if($type!==null){
    		$this->type = $type;
    		$sql=" and `type`='$type'";
    	}
		$rs		= iDB::all("SELECT * FROM `#iCMS@__group` where 1=1{$sql} ORDER BY `sortnum` , `gid` ASC");

        array_unshift($rs,
            array('gid'=>'0','type'=>$this->type,'name'=>'路人甲'),
            array('gid'=>'65535','type'=>'0','name'=>'管理员克隆')
        );
        $_count = count($rs);
		for ($i=0;$i<$_count;$i++){
			$this->array[$rs[$i]['gid']] = $rs[$i];
			$this->group[$rs[$i]['type']][$rs[$i]['gid']] = $rs[$i];
		}
    }
    public function do_iCMS(){
        $this->do_manage();
    }
    public function do_add(){
        $this->gid && $rs = iDB::row("SELECT * FROM `#iCMS@__group` WHERE `gid`='$this->gid' LIMIT 1;");
        if($rs){
            $rs->config = json_decode($rs->config,true);
        }
        include admincp::view("group.add");
    }
    public function do_manage(){
        $rs     = iDB::all("SELECT * FROM `#iCMS@__group` ORDER BY `type` , `gid` ASC");
        $_count = count($rs);
        include admincp::view("group.manage");
    }
    public function do_del($gid = null,$dialog=true){
    	$gid===null && $gid=$this->gid;
		$gid OR iUI::alert('请选择要删除的用户组');
		$gid=="1" && iUI::alert('不能删除超级管理员组');
		iDB::query("DELETE FROM `#iCMS@__group` WHERE `gid` = '$gid'");
		$dialog && iUI::success('用户组删除完成','js:parent.$("#id'.$gid.'").remove();');
    }
    public function do_batch(){
    	list($idArray,$ids,$batch) = iUI::get_batch_args("请选择要删除的用户组");
    	switch($batch){
    		case 'dels':
				iUI::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iUI::$break	= true;
				iUI::success('全部删除完成!','js:1');
    		break;
		}
	}
	public function do_save(){
		$gid    = intval($_POST['gid']);
		$type   = intval($_POST['type']);
		$name   = iSecurity::escapeStr($_POST['name']);

        $config = (array)$_POST['config'];
        $config = addslashes(json_encode($config));

		$name OR iUI::alert('角色名不能为空');
		$fields = array('name', 'sortnum', 'config', 'type');
		$data   = compact ($fields);
		if($gid){
            iDB::update('group', $data, array('gid'=>$gid));
			$msg = "角色修改完成!";
		}else{
			iDB::insert('group',$data);
			$msg = "角色添加完成!";
		}
		iUI::success($msg,'url:'.APP_URI);
	}
    public function select($type=null,$currentid=NULL){
        $type===null && $type = $this->type;
        if($this->group[$type])foreach($this->group[$type] AS $G){
            $selected=($currentid==$G['gid'])?" selected='selected'":'';
            $option.="<option value='{$G['gid']}'{$selected}>".$G['name']."[GID:{$G['gid']}] </option>";
        }
        return $option;
    }
}
