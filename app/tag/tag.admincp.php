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

class tagAdmincp{
    public $appid = null;
    public $callback = array();
    public function __construct() {
        $this->appid = iCMS_APP_TAG;
        $this->id = (int)$_GET['id'];
    }
    public function do_config(){
        configAdmincp::app($this->appid);
    }
    public function do_save_config(){
        configAdmincp::save($this->appid);
    }

    public function do_add(){
        $this->id && $rs = iDB::row("SELECT * FROM `#iCMS@__tag` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        if(empty($rs)){
            $rs['status'] = '1';
        }
        iPHP::callback(array("apps_meta","get"),array($this->appid,$this->id));
        iPHP::callback(array("formerApp","add"),array($this->appid,$rs,true));
        include admincp::view('tag.add');
    }
    public function do_update(){
        if($this->id){
            $data = iSQL::update_args($_GET['_args']);
            $data && iDB::update("tag",$data,array('id'=>$this->id));
            tag::cache($this->id,'id');
            iUI::success('操作成功!','js:1');
        }
    }
    public function do_iCMS(){
    	admincp::$APP_METHOD="domanage";
    	$this->do_manage();
    }
    public function do_manage(){
        $sql  = " where 1=1";
        $cid    = (int)$_GET['cid'];
        $tcid   = (int)$_GET['tcid'];
        $pid    = (int)$_GET['pid'];
        $rootid = (int)$_GET['rootid'];

        $_GET['keywords'] && $sql.=" AND CONCAT(name,seotitle,subtitle,keywords,description) REGEXP '{$_GET['keywords']}'";

        $sql.= category::search_sql($cid);
        $sql.= category::search_sql($tcid,'tcid');
        $_GET['starttime'] && $sql.=" AND `pubdate`>='".str2time($_GET['starttime'].(strpos($_GET['starttime'],' ')!==false?'':" 00:00:00"))."'";
        $_GET['endtime']   && $sql.=" AND `pubdate`<='".str2time($_GET['endtime'].(strpos($_GET['endtime'],' ')!==false?'':" 23:59:59"))."'";
        $_GET['post_starttime'] && $sql.=" AND `postime`>='".str2time($_GET['post_starttime'].(strpos($_GET['post_starttime'],' ')!==false?'':" 00:00:00"))."'";
        $_GET['post_endtime']   && $sql.=" AND `postime`<='".str2time($_GET['post_endtime'].(strpos($_GET['post_endtime'],' ')!==false?'':" 23:59:59"))."'";

        isset($_GET['pic']) && $sql.=" AND `haspic` ='".($_GET['pic']?1:0)."'";
        $_GET['field'] && $sql.=" AND `field` ='".$_GET['field']."'";
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
            $sql     = ",({$map_sql}) map {$sql} AND `id` = map.`iid`";
        }

        list($orderby,$orderby_option) = get_orderby(array(
            'id'         =>"ID",
            'hits'       =>"点击",
            'hits_week'  =>"周点击",
            'hits_month' =>"月点击",
            'count'      =>"使用数",
            'good'       =>"顶",
            'postime'    =>"时间",
            'pubdate'    =>"发布时间",
            'comments'   =>"评论数",
        ));
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total		= iPagination::totalCache("SELECT count(*) FROM `#iCMS@__tag` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个标签");
        $limit  = 'LIMIT '.iPagination::$offset.','.$maxperpage;
        if($map_sql||iPagination::$offset){
            if(iPagination::$offset > 1000 && $total > 2000 && iPagination::$offset >= $total/2) {
                $_offset = $total-iPagination::$offset-$maxperpage;
                if($_offset < 0) {
                    $_offset = 0;
                }
                $orderby = "id ASC";
                $limit = 'LIMIT '.$_offset.','.$maxperpage;
            }
            $ids_array = iDB::all("
                SELECT `id` FROM `#iCMS@__tag` {$sql}
                ORDER BY {$orderby} {$limit}
            ");
            if(isset($_offset)){
                $ids_array = array_reverse($ids_array, TRUE);
                $orderby   = "id DESC";
            }
            $ids   = iSQL::values($ids_array);
            $ids   = $ids?$ids:'0';
            $sql   = "WHERE `id` IN({$ids})";
            $limit = '';
        }
        $rs     = iDB::all("SELECT * FROM `#iCMS@__tag` {$sql} ORDER BY {$orderby} {$limit}");
        $_count = count($rs);
        $propArray = propAdmincp::get("pid",null,'array');
    	include admincp::view("tag.manage");
    }
    /**
     * [导入标签]
     * @return [type] [description]
     */
    public function do_import(){
        $_POST['cid'] OR iUI::alert('请选择标签所属栏目！');
        files::$check_data        = false;
        files::$cloud_enable      = false;
        iFS::$config['allow_ext'] = 'txt';
        $F    = iFS::upload('upfile');
        $path = $F['RootPath'];
        if($path){
            $contents = file_get_contents($path);
            $contents = iSecurity::encoding($contents);
            if($contents){
                $fields   = tag::fields();
                $cid      = implode(',', (array)$_POST['cid']);
                $tcid     = implode(',', (array)$_POST['tcid']);
                $pid      = implode(',', (array)$_POST['pid']);
                $variable = explode("\n", $contents);
                $msg = array();
                foreach ($variable as $key => $name) {
                    $name = tag::name($name);
                    if(empty($name)){
                        $msg['empty']++;
                        continue;
                    }
                    $name = addslashes($name);
                    if(iDB::value("SELECT `id` FROM `#iCMS@__tag` where `name` = '$name'")){
                        $msg['has']++;
                        continue;
                    }

                    $tkey    = strtolower(iPinyin::get($name));
                    $userid  = members::$userid;
                    $haspic  = '0';
                    $status  = '1';
                    $pubdate = time();
                    $data    = compact ($fields);
                    $id = iDB::insert('tag',$data);

                    $pid && iMap::init('prop',$this->appid,'pid')->add($pid,$id);
                    iMap::init('category',$this->appid,'cid');
                    iMap::add($cid,$id);
                    $tcid && iMap::add($tcid,$id);
                    $msg['success']++;
                }
            }
            @unlink($path);
            iUI::success('标签导入完成<br />空标签:'.(int)$msg['empty'].'个<br />已经存在标签:'.(int)$msg['has'].'个<br />成功导入标签:'.(int)$msg['success'].'个');
        }
    }
    public function do_save(){
        $id          = (int)$_POST['id'];

        $cid         = (int)$_POST['cid'];
        $_cid        = iSecurity::escapeStr($_POST['_cid']);
        category::check_priv($cid,($tid?'ce':'ca'),'alert');

        $tcid        = (int)$_POST['tcid'];
        $_tcid       = iSecurity::escapeStr($_POST['_tcid']);

        $pid         = implode(',', (array)$_POST['pid']);
        $_pid        = iSecurity::escapeStr($_POST['_pid']);

        $userid      = (int)$_POST['userid'];
        $status      = (int)$_POST['status'];
        $sortnum     = (int)$_POST['sortnum'];
        $weight      = (int)$_POST['weight'];
        $hits        = (int)$_POST['hits'];
        $hits_today  = (int)$_POST['hits_today'];
        $hits_yday   = (int)$_POST['hits_yday'];
        $hits_week   = (int)$_POST['hits_week'];
        $hits_month  = (int)$_POST['hits_month'];
        $favorite    = (int)$_POST['favorite'];
        $comments    = (int)$_POST['comments'];
        $good        = (int)$_POST['good'];
        $bad         = (int)$_POST['bad'];
        $creative    = (int)$_POST['creative'];

        $editor      = iSecurity::escapeStr($_POST['editor']);
        $description = iSecurity::escapeStr($_POST['description']);
        $keywords    = iSecurity::escapeStr($_POST['keywords']);
        $clink       = iSecurity::escapeStr($_POST['clink']);
        $url         = iSecurity::escapeStr($_POST['url']);
        $tpl         = iSecurity::escapeStr($_POST['tpl']);
        $pic         = iSecurity::escapeStr($_POST['pic']);
        $bpic        = iSecurity::escapeStr($_POST['bpic']);
        $mpic        = iSecurity::escapeStr($_POST['mpic']);
        $spic        = iSecurity::escapeStr($_POST['spic']);

        $rootid      = (int)$_POST['rootid'];
        $name        = iSecurity::escapeStr($_POST['name']);
        $subtitle    = iSecurity::escapeStr($_POST['subtitle']);
        $tkey        = iSecurity::escapeStr($_POST['tkey']);
        $seotitle    = iSecurity::escapeStr($_POST['seotitle']);
        $related     = iSecurity::escapeStr($_POST['related']);

        if(empty($name)){
            return iUI::alert('标签名称不能为空！');
        }
        $title = $name;

        $pubdate   = str2time($_POST['pubdate']);
        $postype   = $_POST['postype']?$_POST['postype']:0;
        $userid OR $userid = members::$userid;
        $editor OR $editor = members::$nickname;

		if(empty($id)) {
            $hasNameId = iDB::value("SELECT `id` FROM `#iCMS@__tag` where `name` = '$name'");
            if($hasNameId){
                if(isset($_POST['spider_update'])){
                    $id = $hasNameId;
                }else{
                    return iUI::alert('该标签已经存在!请检查是否重复');
                }
            }
		}

        $category = category::get($cid);
        if(strstr($category->rule['topic'],'{LINK}')!==false && empty($clink)){
            $clink = iPinyin::get($title,self::$config['clink']);
        }

        if($clink && tag::check($clink,$id,'clink')){
            return iUI::alert('该标签自定义链接已经存在!请检查是否重复');
        }

        iFS::$force_ext = "jpg";
        (iFS::checkHttp($pic)  && !isset($_POST['pic_http']))  && $pic  = iFS::http($pic);
        (iFS::checkHttp($bpic) && !isset($_POST['bpic_http'])) && $bpic = iFS::http($bpic);
        (iFS::checkHttp($mpic) && !isset($_POST['mpic_http'])) && $mpic = iFS::http($mpic);
        (iFS::checkHttp($spic) && !isset($_POST['spic_http'])) && $spic = iFS::http($spic);
        $picdata = filesAdmincp::picdata($pic,$bpic,$mpic,$spic);
        $haspic  = empty($pic)?0:1;

        $tkey OR $tkey = strtolower(iPinyin::get($name));

        $fields = tag::fields($id);
        $data   = compact($fields);

		if(empty($id)){
            $this->check_tkey($tkey);
            $data['tkey']     = $tkey;
            $data['postime']  = $pubdate;
            $data['count']    = '0';
            $data['comments'] = '0';
            $id = iDB::insert('tag',$data);

            iMap::init('prop',$this->appid,'pid');
            $pid && iMap::add($pid,$id);

            iMap::init('category',$this->appid,'cid');
            iMap::add($cid,$id);
            $tcid && iMap::add($tcid,$id);

            categoryAdmincp::update_count($cid);
            $tcid && categoryAdmincp::update_count($tcid);

            $msg = '标签添加完成';
		}else{

            $this->check_tkey($tkey,$id);
            $data['tkey'] = $tkey;

            unset($data['count'],$data['comments']);
            iDB::update('tag', $data, array('id'=>$id));

            iMap::init('prop',$this->appid,'pid');
            iMap::diff($pid,$_pid,$id);

            iMap::init('category',$this->appid,'cid');
            iMap::diff($cid,$_cid,$id);
            $tcid && iMap::diff($tcid,$_tcid,$id);

            if($_cid!=$cid) {
                categoryAdmincp::update_count($_cid,'-');
                categoryAdmincp::update_count($cid);
            }
            if($_tcid!=$tcid) {
                categoryAdmincp::update_count($_tcid,'-');
                categoryAdmincp::update_count($tcid);
            }

            $msg = '标签更新完成';
		}
        iPHP::callback(array("apps_meta","save"),array($this->appid,$id));
        iPHP::callback(array("formerApp","save"),array($this->appid,$id));
        iPHP::callback(array("spider","callback"),array($this,$id));

        if($this->callback['return']){
            return $this->callback['return'];
        }

        iUI::success($msg,"url:".APP_URI);
    }
    public function check_tkey(&$tkey,$id=0){
        $sql = "SELECT count(`id`) FROM `#iCMS@__tag` where `tkey` ='$tkey' ";
        $id && $sql.=" AND `id` !='$id'";
        $hasTkey = iDB::value($sql);
        if($hasTkey){
            $count = iDB::value("SELECT count(`id`) FROM `#iCMS@__tag` where `tkey` LIKE '{$tkey}-%'");
            $tkey = $tkey.'-'.($count+1);
        }
    }

    public function check_spider_data(&$data,$old,$key,$value){
        if($old[$key]){
            if($value){
                $data[$key] = $value;
            }else{
                unset($data[$key]);
            }
        }
    }

    public function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
        iMap::del_data($id,$this->appid,'category');
        iMap::del_data($id,$this->appid,'prop');
    	tag::del($id,'id');
    	$dialog && iUI::success("标签删除成功",'js:parent.$("#id'.$id.'").remove();');
    }
    public function do_batch(){
        list($idArray,$ids,$batch) = iUI::get_batch_args("请选择要操作的标签");
    	switch($batch){
            case 'keywords':
                $rs = iDB::all("SELECT * FROM `#iCMS@__tag` where `id` IN ($ids)");
                $categoryArray  = category::multi_get($rs,'cid');
                $tcategoryArray = category::multi_get($rs,'tcid',$this->appid);
                foreach($rs AS $tag){
                    $C          = (array)$categoryArray[$tag['cid']];
                    $TC         = (array)$tcategoryArray[$tag['tcid']];
                    $tag['url'] = iURL::get('tag',array($tag,$C,$TC))->href;
                    iPHP::callback(array('keywordsAdmincp','insert'),array($tag['name'],$tag['url']));
                }
                iUI::success('内链添加完成!','js:1');
            break;
    		case 'dels':
				iUI::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iUI::$break	= true;
				iUI::success('标签全部删除完成!','js:1');
    		break;
    		case 'move':
		        $_POST['cid'] OR iUI::alert("请选择目标栏目!");
                iMap::init('category',$this->appid,'cid');
		        $cid = (int)$_POST['cid'];
		        foreach($idArray AS $id) {
                    $_cid = iDB::value("SELECT `cid` FROM `#iCMS@__tag` where `id` ='$id'");
                    iDB::update("tag",compact('cid'),compact('id'));
		            if($_cid!=$cid) {
                        iMap::diff($cid,$_cid,$id);
                        categoryAdmincp::update_count($_cid,'-');
                        categoryAdmincp::update_count($cid);
		            }
		        }
		        iUI::success('成功移动到目标栏目!','js:1');
    		break;
    		case 'mvtcid':
		        $_POST['tcid'] OR iUI::alert("请选择目标分类!");
                iMap::init('category',$this->appid,'cid');
		        $tcid = (int)$_POST['tcid'];
		        foreach($idArray AS $id) {
                    $_tcid = iDB::value("SELECT `tcid` FROM `#iCMS@__tag` where `id` ='$id'");
                    iDB::update("tag",compact('tcid'),compact('id'));
		            if($_tcid!=$tcid) {
                        iMap::diff($tcid,$_tcid,$id);
                        categoryAdmincp::update_count($_tcid,'-');
                        categoryAdmincp::update_count($tcid);
		            }
		        }
		        iUI::success('成功移动到目标分类!','js:1');
    		break;
    		case 'prop':
                iMap::init('prop',$this->appid,'pid');
                $pid = implode(',', (array)$_POST['pid']);
                foreach($idArray AS $id) {
                    $_pid = iDB::value("SELECT pid FROM `#iCMS@__tag` WHERE `id`='$id'");;
                    iDB::update("tag",compact('pid'),compact('id'));
                    iMap::diff($pid,$_pid,$id);
                }
                iUI::success('属性设置完成!','js:1');
    		break;
    		case 'weight':
		        $weight	=(int)$_POST['mweight'];
		        $sql	="`weight` = '$weight'";
    		break;
            case 'tpl':
                $tpl = iSecurity::escapeStr($_POST['mtpl']);
                $sql = "`tpl` = '$tpl'";
            break;
    		case 'keyword':
    			if($_POST['pattern']=='replace') {
    				$sql	="`keywords` = '".iSecurity::escapeStr($_POST['mkeyword'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($idArray AS $id){
                        $keywords = iDB::value("SELECT keywords FROM `#iCMS@__tag` WHERE `id`='$id'");
                        $sql      ="`keywords` = '".($keywords?$keywords.','.iSecurity::escapeStr($_POST['mkeyword']):iSecurity::escapeStr($_POST['mkeyword']))."'";
				        iDB::query("UPDATE `#iCMS@__tag` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iUI::success('关键字更改完成!','js:1');
    			}
    		break;
    		case 'tag':
    			if($_POST['pattern']=='replace') {
    				$sql	="`related` = '".iSecurity::escapeStr($_POST['mtag'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($idArray AS $id){
		        		$keywords	= iDB::value("SELECT related FROM `#iCMS@__tag` WHERE `id`='$id'");
		        		$sql		="`related` = '".($keywords?$keywords.','.iSecurity::escapeStr($_POST['mtag']):iSecurity::escapeStr($_POST['mtag']))."'";
				        iDB::query("UPDATE `#iCMS@__tag` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iUI::success('相关标签更改完成!','js:1');
    			}
    		break;
    		default:
                if(strpos($batch, ':')){
                    $data = iSQL::update_args($batch);
                    foreach($idArray AS $id) {
                        $data && iDB::update("tag",$data,array('id'=>$id));
                    }
                    iUI::success('操作成功!','js:1');
                }else{
                    iUI::alert('请选择要操作项!','js:1');
                }

		}
        $sql && iDB::query("UPDATE `#iCMS@__tag` SET {$sql} WHERE `id` IN ($ids)");
		iUI::success('操作成功!','js:1');
	}
    public function do_api_extract(){
        $title   = html2text($_POST['title']);
        $content = html2text($_POST['content']);
        $words   = self::api_extract($title,$content);
        echo $words;
    }
    public static function api_extract($title=null,$content=null){
        $array    = compact('title','content');
        $response = apps_store::api_post('tag.extract',$array);
        return $response;
    }
    public static function _count(){
        return iDB::value("SELECT count(*) FROM `#iCMS@__tag`");
    }
}
