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
class commentFunc{
	public static $appid = iCMS_APP_COMMENT;
	public static function comment_array($vars){
		$where_sql = " `status`='1'";
		$is_multi = false;
		if(isset($vars['id'])){
			if(is_array($vars['id'])){
				$is_multi = true;
				$where_sql.= iSQL::in($vars['id'],'id',false,false);
			}else{
				$where_sql.= " AND `id`='".(int)$vars['id']."'";
			}
		}
		isset($vars['userid']) && $where_sql.= " AND `userid`='".(int)$vars['userid']."'";
		$rs = iDB::all("SELECT * FROM `#iCMS@__comment` WHERE {$where_sql}",ARRAY_A);
		if($is_multi){
			$_count = count($rs);
	        for ($i=0; $i < $_count; $i++) {
	        	$data[$rs[$i]['id']] = $rs[$i];
	        	$data[$rs[$i]['id']]['user']= user::info($rs[$i]['userid'],$rs[$i]['username'],$vars['facesize']);;
	        }
		}else{
			$data = $rs[0];
			$data['user'] = user::info($data['userid'],$data['username'],$vars['facesize']);
		}
		return $data;
	}
	private static function list_display($vars){
		$vars['do']          = 'list';
		$vars['page_ajax']   = 'comment_page_ajax';
		$vars['total_cahce'] = 'G';
		$tpl = 'list.default';
		isset($vars['_display']) && $vars['display'] = $vars['_display'];
		unset($vars['method'],$vars['_display']);
		$vars['query'] = http_build_query($vars);
		$vars['param'] = array(
			'suid'  => (int)$vars['suid'],
			'iid'   => (int)$vars['iid'],
			'cid'   => (int)$vars['cid'],
			'appid' => (int)$vars['appid'],
			'title' => iSecurity::escapeStr($vars['title']),
		);
		iView::assign('comment_vars',$vars);
		iView::display("iCMS://comment/{$tpl}.htm");
	}
	public static function comment_list($vars){
		if(!iCMS::$config['comment']['enable']){
			return;
		}

		if ($vars['display'] && empty($vars['loop'])) {
			$_vars = iView::app_vars(true);
			$vars  = array_merge($vars,(array)$_vars);
			$vars['iid']   OR iUI::warning('iCMS&#x3a;comment&#x3a;list 标签出错! 缺少参数"iid"或"iid"值为空.');
			$vars['appid'] OR iUI::warning('iCMS&#x3a;comment&#x3a;list 标签出错! 缺少参数"appid"或"appid"值为空.');
			return commentFunc::list_display($vars);
		}

		$where_sql = " `status`='1'";
		if(isset($vars['appid'])){
			$appid    = (int)$vars['appid'];
			$where_sql.= " AND `appid`='$appid'";
		}
	    if(isset($vars['cid!'])){
	    	$ncids    = explode(',',$vars['cid!']);
	        $vars['sub'] && $ncids+=categoryApp::get_cids($ncids,true);
	        $where_sql.= iSQL::in($ncids,'cid','not');
	    }
	    if(isset($vars['cid'])){
	        $cid = explode(',',$vars['cid']);
	        $vars['sub'] && $cid+=categoryApp::get_cids($cid,true);
	        $where_sql.= iSQL::in($cid,'cid');
	    }
	    isset($vars['userid'])&& $where_sql.= " AND `userid`='{$vars['userid']}'";

		$vars['pid'] && $where_sql .=" AND `pid`='".(int)$vars['pid']."'";
		$vars['iid'] && $where_sql .=" AND `iid`='".(int)$vars['iid']."'";
		if($vars['id']){
			$where_sql .=" AND `id`='".(int)$vars['id']."'";
			$vars['page'] = false;
		}

        $vars['id'] && $where_sql .= iSQL::in($vars['id'], 'id');
        $vars['id!'] && $where_sql .= iSQL::in($vars['id!'], 'id', 'not');
		$maxperpage	= isset($vars['row'])?(int)$vars['row']:"10";
		$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;
		$by			= $vars['by']=='ASC'?"ASC":"DESC";
		switch ($vars['orderby']) {
			default: $order_sql = " ORDER BY `id` $by";
		}
		$md5	= md5($where_sql.$order_sql);
		$offset = (int)$vars['offset'];
		if($vars['page']){
			isset($vars['total_cache']) && $_GET['total_cahce'] = true;
			$total  = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__comment` WHERE {$where_sql}",null,iCMS::$config['cache']['page_total']);
			$pgconf = array(
				'total'     => $total,
				'perpage'   => $maxperpage,
				'unit'      => iUI::lang('iCMS:page:comment'),
				'ajax'      => $vars['page_ajax']?'comment_page_ajax':FALSE,
				'nowindex'  => $GLOBALS['page'],
			);
			if($vars['display'] == 'iframe' || $vars['page_ajax']){
				iSecurity::GP('pn','GP',2);
				$pgconf['page_name'] = 'pn';
				$pgconf['nowindex']  = $GLOBALS['pn'];
			}

			isset($vars['total_cache']) && $pgconf['total_type'] = $vars['total_cache'];

			$multi  = iUI::page($pgconf);
			$offset = $multi->offset;
			// if($offset>1000){
				//$where_sql.=" AND `id` >= (SELECT `id` FROM `#iCMS@__comment` WHERE {$where_sql} {$order_sql} LIMIT {$offset},1)";
				//$limit  = "LIMIT {$maxperpage}";
			// }
			iView::assign("comment_list_total",$total);
		}
		$limit = "LIMIT {$offset},{$maxperpage}";
		if($vars['cache']){
			$cache_name = iPHP_DEVICE.'/comment/'.$md5."/".(int)$offset;
			$resource   = iCache::get($cache_name);
		}
		if(empty($resource)){
			$resource = iDB::all("SELECT * FROM `#iCMS@__comment` WHERE {$where_sql} {$order_sql} {$limit}");
	        if($vars['reply']){
	            $ridArray = iSQL::values($resource,'reply_id','array',null);
	            if($ridArray){
	            	$rkey = array_search (0,$ridArray);
	            	unset($ridArray[$rkey]);
	            }
	            $ridArray && $reply_array = self::comment_array(array('id'=>$ridArray));
	        }
			$ln = ($pgconf['nowindex']-1)<0?0:$pgconf['nowindex']-1;

			if($resource)foreach ($resource as $key => $value) {
				$value = commentApp::value($value, $vars);

				if($vars['by']=='ASC'){
					$value['lou'] = $key+$ln*$maxperpage+1;
				}else{
					$value['lou'] = $total-($key+$ln*$maxperpage);
				}
				$value['total'] = $total;

				if($vars['reply'] && $reply_array){
					$value['reply_data'] = $reply_array[$value['reply_id']];
					unset($reply_array[$value['reply_id']]);
				}
				if($vars['page']){
					$value['page']  = array('total'=>$multi->totalpage,'perpage'=>$multi->perpage);
				}
				$resource[$key] = $value;
			}
			$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
		}

		return $resource;
	}
	public static function comment_form($vars){
		if(!iCMS::$config['comment']['enable']){
			return;
		}

		if(!isset($vars['ref'])){
			$_vars = iView::app_vars(true);
			$vars  = array_merge($vars,$_vars);
			unset($vars['ref'],$_vars);
		}

		$vars['iid']   OR iUI::warning('iCMS&#x3a;comment&#x3a;form 标签出错! 缺少参数"iid"或"iid"值为空.');
		$vars['cid']   OR iUI::warning('iCMS&#x3a;comment&#x3a;form 标签出错! 缺少参数"cid"或"cid"值为空.');
		$vars['appid'] OR iUI::warning('iCMS&#x3a;comment&#x3a;form 标签出错! 缺少参数"appid"或"appid"值为空.');
		$vars['title'] OR iUI::warning('iCMS&#x3a;comment&#x3a;form 标签出错! 缺少参数"title"或"title"值为空.');
		switch ($vars['display']) {
			case 'iframe':
				$tpl        = 'form.iframe';
				$vars['do'] = 'form';
				break;
			default:
				isset($vars['_display']) && $vars['display'] = $vars['_display'];
				$vars['param'] = array(
					'suid'  => (int)$vars['suid'],
					'iid'   => (int)$vars['iid'],
					'cid'   => (int)$vars['cid'],
					'appid' => (int)$vars['appid'],
					'title' => iSecurity::escapeStr($vars['title']),
				);
				$tpl = 'form.default';
				break;
		}
		unset($vars['method'],$vars['_display']);
		$vars['query'] = http_build_query($vars);
		iView::assign('comment_vars',$vars);
		iView::display('iCMS://comment/'.$tpl.'.htm');
	}
}
