<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class propAdmincp{
    public static $app   = null;
    public static $field = null;
    public function __construct() {
        $this->pid = (int)$_GET['pid'];
    }
    public function do_add(){
        $this->pid && $rs = iDB::row("SELECT * FROM `#iCMS@__prop` WHERE `pid`='$this->pid' LIMIT 1;",ARRAY_A);
        if($_GET['act']=="copy"){
            $this->pid = 0;
            $rs['val'] = '';
        }
        if(empty($rs)){
            $_GET['_app'] && $rs['app']  = iSecurity::escapeStr($_GET['_app']);
            $_GET['field']&& $rs['field'] = iSecurity::escapeStr($_GET['field']);
        }
        include admincp::view("prop.add");
    }
    public function do_save(){
        $pid     = (int)$_POST['pid'];
        $cid     = (int)$_POST['cid'];
        $field   = iSecurity::escapeStr($_POST['field']);
        $app     = iSecurity::escapeStr($_POST['app']);
        $val     = iSecurity::escapeStr($_POST['val']);
        $sortnum = (int)$_POST['sortnum'];
        $name    = $_POST['name'];

        $field OR iUI::alert('属性字段不能为空!');
        $name OR iUI::alert('属性名称不能为空!');
        // $app OR iUI::alert('所属应用不能为空!');

        $fields = array('rootid','cid','field','app','sortnum', 'name', 'val');
        $data   = compact ($fields);

		if($pid){
            if ($field=='pid'&& !is_numeric($data['val'])){
                iUI::alert('pid字段的值只能用数字');
            }
            $field=='pid' && $data['val'] = (int)$data['val'];

            iDB::update('prop', $data, array('pid'=>$pid));
			$msg = "属性更新完成!";
		}else{
            $nameArray = explode("\n",$name);
            if(count($nameArray)>1){
                foreach($nameArray AS $nkey=>$_name){
                    $_name  = trim($_name);
                    if(empty($_name)) continue;

                    if(strpos($_name,':')!==false){
                        list($data['name'],$data['val']) = explode(':', $_name);
                        empty($data['val']) && $data['val'] = $nkey+1;
                    }else{
                        $data['name'] = $data['val'] = $_name;
                    }
                    $data['sortnum'] = $nkey;

                    if ($field=='pid'&& !is_numeric($data['val'])){
                        iUI::alert('pid字段的值只能用数字');
                    }
                    $check = iDB::value("
                        SELECT `pid` FROM `#iCMS@__prop`
                        WHERE `app` ='$app'
                        AND `val` ='".$data['val']."'
                        AND `field` ='$field'
                        AND `cid` ='$cid'
                    ");
                    if($check){
                        $msg.= '该['.$data['val'].']属性值已经存在!请另选一个<br />';
                    }else{
                        iDB::insert('prop',$data);
                        $msg.= '['.$data['val'].']新属性添加完成!';
                    }
                }
            }else{

                if(strpos($data['name'],':')!==false){
                    list($data['name'],$data['val']) = explode(':', $data['name']);
                    empty($data['val']) && $data['val'] = 1;
                }else{
                    $data['name'] = $data['val'] = $data['name'];
                }
                if ($field=='pid'&& !is_numeric($data['val'])){
                    iUI::alert('pid字段的值只能用数字');
                }
                iDB::value("
                    SELECT `pid` FROM `#iCMS@__prop`
                    WHERE `app` ='$app'
                    AND `val` ='$val'
                    AND `field` ='$field'
                    AND `cid` ='$cid'
                ") && iUI::alert('该类型属性值已经存在!请另选一个');
                iDB::insert('prop',$data);
                $msg = "新属性添加完成!";
            }
		}
		$this->cache();
        iUI::success($msg,'url:'.APP_URI);
    }
    public function do_update(){
    	foreach((array)$_POST['pid'] as $tk=>$pid){
            iDB::query("update `#iCMS@__prop` set `app` = '".$_POST['app'][$tk]."', `name` = '".$_POST['name'][$tk]."', `value` = '".$_POST['value'][$tk]."' where `pid` = '$pid';");
    	}
    	$this->cache();
    	iUI::alert('更新完成');
    }
    public function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->pid;
    	$id OR iUI::alert('请选择要删除的属性!');
        $this->del($id);
    	$this->cache();
    	$dialog && iUI::success("已经删除!",'url:'.APP_URI);
    }
    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要操作的属性");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iUI::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iUI::$break	= true;
				iUI::success('属性全部删除完成!','js:1');
    		break;
    		case 'refresh':
    			$this->cache();
    			iUI::success('属性缓存全部更新完成!','js:1');
    		break;
		}
	}

    public function do_iCMS(){
        $sql			= " where 1=1";
//        $cid			= (int)$_GET['cid'];
//
//        if($cid) {
//	        $cids	= $_GET['sub']?categoryApp::get_cids($cid,true):$cid;
//	        $cids OR $cids	= $vars['cid'];
//	        $sql.= iSQL::in($cids,'cid');
//        }

        $_GET['field']&& $sql.=" AND `field`='".$_GET['field']."'";
        $_GET['field']&& $uriArray['field'] = $_GET['field'];

        $_GET['_app'] && $sql.=" AND `app`='".$_GET['_app']."'";
        $_GET['_app'] && $uriArray['_app'] = $_GET['_app'];

        $_GET['cid']  && $sql.=" AND `cid`='".$_GET['cid']."'";
        $_GET['cid']  && $uriArray['cid'] = $_GET['cid'];

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total		= iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__prop` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个属性");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__prop` {$sql} order by pid DESC LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include admincp::view("prop.manage");
    }

    public function do_cache(){
        $this->cache();
        iUI::success('缓存更新完成!','js:1');
    }
    public static function cache(){
    	$rs	= iDB::all("SELECT * FROM `#iCMS@__prop`");
    	foreach((array)$rs AS $row) {
            if($row['app']){
                $app_field_id[$row['app'].'/'.$row['field']][$row['pid']] =
                $app_field_val[$row['app']][$row['field']][$row['val']]   = $row;
            }else{
                $app_field_id[$row['field'].'/pid'][$row['pid']]  =
                $app_field_val[$row['field']][$row['val']] = $row;
            }
    	}
        // prop/article/author=>pid
        // prop/author/pid
        foreach((array)$app_field_id AS $key=>$a){
            iCache::set('prop/'.$key,$a,0);
        }
        // prop/article
        // prop/author
    	foreach((array)$app_field_val AS $k=>$a){
    		iCache::set('prop/'.$k,$a,0);
    	}
    }
    public static function btn_add($title,$field=null,$app = null,$text='<i class="fa fa-plus"></i>'){
        $app OR $app = admincp::$APP_NAME;
        $field OR $field = self::$field;
        $text OR $text = $title;
        return '<a class="btn tip-right" href="'.__ADMINCP__.
        '=prop&do=add&_app='.$app.
        '&field='.$field.'" target="_blank" title="'.$title.'">'.$text.'</a>';
    }
    public static function btn_group($field,$target = null, $app = null){
        self::$field = $field;
        $app OR $app = admincp::$APP_NAME;
        $target OR $target = $field;
        $propArray = iCache::get("prop/{$app}/{$field}");
        $div = '<div class="btn-group">';
        $div.= '<a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span> 选择</a>';
        $div.= '<ul class="dropdown-menu">';
        if($propArray)foreach ((array)$propArray as $prop) {
            $div.= '<li><a href="javascript:;" data-toggle="insert"
                    data-target="#' . $target . '"
                    data-value="' . $prop['val'] . '">' . $prop['name'] . '</a>
                    </li>';
        }
        $div.= '<li class="divider"></li>';
        $div.= '<li>';
        $div.= self::btn_add('添加常用属性',$field,$app,null);
        // $div.= '<a class="btn tip-right" href="'.__ADMINCP__.'=prop&do=add&_app='.$app.'&field='.$field.'" target="_blank" title="添加常用属性"><i class="fa fa-plus"></i>常用属性</a>';
        $div.= '</li></ul>';
        $div.= '</div>';
        return $div;
    }
    public static function select($field,$target = null,$class='span3',$title='请选择或填写',$app = null){
        self::$field = $field;
        $app OR $app = admincp::$APP_NAME;
        $target OR $target = $field;
        $propArray = iCache::get("prop/{$app}/{$field}");

        $div = '<select data-toggle="select_insert"
                data-target="#' . $target . '"
                class="chosen-select '.$class.'"
                data-placeholder="'.$title.'">';
        $div.= '<option></option>';
        if($propArray)foreach ((array)$propArray as $prop) {
            $div.= '<option value="'.$prop['val'].'">' . $prop['name'] . '</option>';
        }
        $div.= '</select>';
        return $div;
    }
    public static function app($app) {
        $self = new self;
        $self::$app = $app;
        return $self;
    }
    public static function get($field, $valArray = NULL,/*$default=array(),*/$out = 'option', $url="",$app = "",$isopt=true) {
        self::$field = $field;
        $app OR $app = admincp::$APP_NAME;
        self::$app && $app = self::$app;
        is_array($valArray) OR $valArray  = explode(',', $valArray);
        $opt = array();
        $propArray = iCache::get("prop/{$app}/{$field}");
        // empty($propArray) && $propArray = iCache::get("prop/{$field}");
        if($propArray)foreach ((array)$propArray AS $k => $P) {
            if ($out == 'option') {
                $optText = "<option value='{$P['val']}'";
                array_search($P['val'], $valArray)!==FALSE && $optText.= " selected='selected'";
                $optText.= "title='{$field}={$P['val']}'";
                $optText.= ">{$P['name']}";
                $isopt && $optText.= "[{$field}='{$P['val']}']";
                $optText.= "</option>";
                $opt[]=$optText;
            } elseif ($out == 'array') {
                $opt[$P['val']] = $P['name'];
            } elseif ($out == 'text') {
                // if (array_search($P['val'],$valArray)!==FALSE) {
                if(array_search($P['val'], $valArray)!==FALSE){
                    $flag = '<i class="fa fa-flag"></i> '.$P['name'];
                    $opt[]= ($url?'<a href="'.str_replace('{PID}',$P['val'],$url).'">'.$flag.'</a>':$flag).'<br />';
                }
            }
        }
        if($out == 'array'){
            return $opt;
        }
        // $opt.='</select>';
        return implode('', $opt);
    }
    public static function flag($pids,$data,$url=null) {
        $pidArray = explode(',',$pids);
        foreach ((array)$pidArray as $key => $pid) {
            $name = $data[$pid];
            if($pid!='0'){
                $flag = '<i class="fa fa-flag"></i> '.$name;
                echo ($url?'<a href="'.str_replace('{PID}',$pid,$url).'">'.$flag.'</a>':$flag).'<br />';
            }
        }
    }
    public static function del_app_data($appid=null){
        if($appid){
            iDB::query("DELETE FROM `#iCMS@__prop` WHERE `appid` = '".$appid."'");
            iDB::query("DELETE FROM `#iCMS@__prop_map` WHERE `appid` = '".$appid."';");
        }
    }
    public static function del($pid=null,$appid=null,$iid=null){
        if($pid){
            $appid && $sql = " AND `appid`='{$appid}'";
            $rs = iDB::row("SELECT * FROM `#iCMS@__prop` WHERE `pid`='$pid' {$sql} LIMIT 1;");
            iDB::query("DELETE FROM `#iCMS@__prop` WHERE `pid` = '$pid' {$sql};");

            $iid && $sql.=" AND iid='$iid'";
            iDB::query("DELETE FROM `#iCMS@__prop_map` WHERE `node`='$pid' {$sql} ;");
            iCache::delete('prop/'.$rs->field);
        }
    }
    public static function _count(){
        return iDB::value("SELECT count(*) FROM `#iCMS@__prop`");
    }
}
