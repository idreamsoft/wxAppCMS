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
define('iPHP_WAF_CSRF', true);

class spiderAdmincp {

	public function __construct() {
		// spider::loader();
		spider::$cid = $this->cid = (int) $_GET['cid'];
		spider::$rid = $this->rid = (int) $_GET['rid'];
		spider::$pid = $this->pid = (int) $_GET['pid'];
		spider::$sid = $this->sid = (int) $_GET['sid'];
		spider::$title = $this->title = $_GET['title'];
		spider::$url = $this->url = $_GET['url'];
		spider::$work = $this->work = false;
		$this->poid = (int) $_GET['poid'];
	}
	/**
	 * [更新采集结果]
	 * @return [type] [description]
	 */
	public function do_update() {
		if ($this->sid) {
			$data = iSQL::update_args($_GET['_args']);
			$data && iDB::update("spider_url", $data, array('id' => $this->sid));
		}
		iUI::success('操作成功!', 'js:1');
	}
	public function do_batch() {
		$idArray = (array) $_POST['id'];
		$idArray OR iUI::alert("请选择要删除的项目");
		$ids = implode(',', $idArray);
		$batch = $_POST['batch'];
		switch ($batch) {
            case 'poid':
                $poid = $_POST['poid'];
                iDB::query("update `#iCMS@__spider_project` set `poid`='$poid' where `id` IN($ids);");
                iUI::success('操作成功!','js:1');
            break;
            case 'rid':
                $rid = $_POST['rid'];
                iDB::query("update `#iCMS@__spider_project` set `rid`='$rid' where `id` IN($ids);");
                iUI::success('操作成功!','js:1');
            break;
            case 'move':
                $cid = $_POST['cid'];
                iDB::query("update `#iCMS@__spider_project` set `cid`='$cid' where `id` IN($ids);");
                iUI::success('操作成功!','js:1');
            break;
		case 'delurl':
			iDB::query("delete from `#iCMS@__spider_url` where `id` IN($ids);");
			break;
		case 'delpost':
			iDB::query("delete from `#iCMS@__spider_post` where `id` IN($ids);");
			break;
		case 'delproject':
			iDB::query("delete from `#iCMS@__spider_project` where `id` IN($ids);");
			break;
		case 'delrule':
			iDB::query("delete from `#iCMS@__spider_rule` where `id` IN($ids);");
			break;
		default:
			if (strpos($batch, '#') !== false) {
				list($table, $_batch) = explode('#', $batch);
				if (in_array($table, array('url', 'post', 'project', 'rul'))) {
					if (strpos($_batch, ':') !== false) {
						$data = iSQL::update_args($_batch);
						foreach ($idArray AS $id) {
							$data && iDB::update("spider_" . $table, $data, array('id' => $id));
						}
						iUI::success('操作成功!', 'js:1');
					}
				}
			}
			iUI::alert('参数错误!', 'js:1');
		}
		iUI::success('全部删除成功!', 'js:1');
	}
	/**
	 * [删除采集结果]
	 * @return [type] [description]
	 */
	public function do_delspider($dialog=true) {
		$this->sid OR iUI::alert("请选择要删除的项目");
		iDB::query("delete from `#iCMS@__spider_url` where `id` = '$this->sid';");
		$dialog && iUI::success('删除完成', 'js:1');
	}

	public function do_delcontent() {
		$indexid = $_GET['indexid'];
		$indexid OR iUI::alert("请选择要删除的项目");

		$project  = spider::project($this->pid);
		$postArgs = spider::postArgs($project['poid']);
		$app      = apps::get_app($postArgs->app);
		$obj = $postArgs->app."Admincp";
		$acp = new $obj;
		if(method_exists($acp, 'do_del')){
			$acp->do_del($indexid,false);
			$this->do_delspider(false);
			iUI::success('删除完成');
		}else{
			iUI::success($obj.' 中没找到 do_del 方法', 'js:1');
		}
	}
	public function do_iCMS($doType = null) {
		$this->do_manage();
	}
	/**
	 * [采集错误结果管理]
	 * @return [type] [description]
	 */
	public function do_error() {
		$sql = " WHERE 1=1";
		$_GET['pid'] && $sql .= " AND `pid` ='" . (int) $_GET['pid'] . "'";
		$_GET['rid'] && $sql .= " AND `rid` ='" . (int) $_GET['rid'] . "'";
		$days = $_GET['days'] ? $_GET['days'] : "7";
		$days && $sql.=" AND `addtime`>".strtotime('-'.$days.' day');
		$ruleArray = $this->rule_opt(0, 'array');
		// $postArray = $this->post_opt(0, 'array');
		// list($orderby,$orderby_option) = get_orderby();
		$maxperpage = $_GET['perpage'] > 0 ? (int) $_GET['perpage'] : 100;
		// $total = iCMS::page_total_cache( "SELECT count(*) FROM `#iCMS@__spider_error` {$sql}", "G");
		// iUI::pagenav($total, $maxperpage, "个网页");
		// $rs = iDB::all("SELECT * FROM `#iCMS@__spider_error` {$sql} order by {$orderby} LIMIT " . iUI::$offset . " , {$maxperpage}");
		$rs = iDB::all("
		    SELECT
		      `pid`,`rid`,COUNT(id) AS ct,`date`
		    FROM
		      `#iCMS@__spider_error`
		    {$sql}
		    GROUP BY pid DESC
		    ORDER BY ct DESC, `date` DESC
		    LIMIT {$maxperpage}
		");
		$_count = count($rs);
		include admincp::view("spider.error");
	}
    public function do_view_error(){
        $date = $_GET['date'];
        $date && $sql.=" AND `date`='$date'";

		$days = $_GET['days'] ? $_GET['days'] : "7";
		$days && $sql.=" AND `addtime`>".strtotime('-'.$days.' day');

		$rs = iDB::all("
		    SELECT *,
		    	COUNT(id) AS ct,
		    	group_concat(`msg`) as `msg`,
		    	group_concat(`type`) as `type`
		    FROM
		      `#iCMS@__spider_error`
			where pid='$this->pid' {$sql}
			GROUP by url
			ORDER BY id DESC
		");

        include admincp::view("spider.error.view");
    }
	/**
	 * [删除错误信息]
	 * @return [type] [description]
	 */
	public function do_del_error() {
		$this->pid OR iUI::alert("请选择要删除的项目");
		iDB::query("delete from `#iCMS@__spider_error` where `pid` = '$this->pid';");
		iUI::success('删除完成', 'js:1');
	}
	/**
	 * [采集结果管理]
	 * @return [type] [description]
	 */
	public function do_manage($doType = null) {
		$sql = " WHERE 1=1";
		$_GET['keywords'] && $sql .= "  AND `title` REGEXP '{$_GET['keywords']}'";
		$doType == "inbox" && $sql .= " AND `publish` ='0'";
		$_GET['pid'] && $sql .= " AND `pid` ='" . (int) $_GET['pid'] . "'";
		$_GET['rid'] && $sql .= " AND `rid` ='" . (int) $_GET['rid'] . "'";
		$_GET['starttime'] && $sql .= " AND `addtime`>=UNIX_TIMESTAMP('" . $_GET['starttime'] . " 00:00:00')";
		$_GET['endtime'] && $sql .= " AND `addtime`<=UNIX_TIMESTAMP('" . $_GET['endtime'] . " 23:59:59')";

		$sql .= category::search_sql($this->cid);

		$ruleArray = $this->rule_opt(0, 'array');
		$postArray = $this->post_opt(0, 'array');
		list($orderby,$orderby_option) = get_orderby();
		$maxperpage = $_GET['perpage'] > 0 ? (int) $_GET['perpage'] : 20;
		$total = iCMS::page_total_cache( "SELECT count(*) FROM `#iCMS@__spider_url` {$sql}", "G");
		iUI::pagenav($total, $maxperpage, "个网页");
		$rs = iDB::all("SELECT * FROM `#iCMS@__spider_url` {$sql} order by {$orderby} LIMIT " . iUI::$offset . " , {$maxperpage}");
		$_count = count($rs);
		include admincp::view("spider.manage");
	}

	public function do_inbox() {
		$this->do_manage("inbox");
	}
	/**
	 * [测试采集方案]
	 * @return [type] [description]
	 */
	public function do_testdata() {
		spider::$dataTest = true;
		spider_data::crawl();
	}
	/**
	 * [测试采集规则]
	 * @return [type] [description]
	 */
	public function do_testrule() {
		spider::$ruleTest = true;
		spider_urls::crawl('WEB@AUTO');
	}
	/**
	 * [手动采集页]
	 * @return [type] [description]
	 */
	public function do_listpub() {
		$responses = spider_urls::crawl('WEB@MANUAL');
		extract($responses);
		include admincp::view("spider.lists");
	}
	/**
	 * [采集结果移除标记]
	 * @return [type] [description]
	 */
	public function do_markurl() {
		$hash = md5($this->url);
		$title = iSecurity::escapeStr($_GET['title']);
		iDB::insert('spider_url', array(
			'cid' => $this->cid,
			'rid' => $this->rid,
			'pid' => $this->pid,
			'title' => addslashes($title),
			'url' => $this->url,
			'hash' => $hash,
			'status' => '2',
			'addtime' => time(),
			'publish' => '2',
			'indexid' => '0',
			'pubdate' => '0',
		));
		iUI::success("移除成功!", 'js:parent.$("#' . $hash . '").remove();');
	}
	/**
	 * [删除采集数据]
	 * @return [type] [description]
	 */
    public function do_dropdata() {
        $this->pid OR iUI::alert("请选择要删除的项目");
		$rs      = iDB::all("SELECT `indexid`,`appid`,`pid` FROM `#iCMS@__spider_url` where `pid` = '$this->pid'");
		$project = spider::project($this->pid);
		$post    = spider::postArgs($project['poid']);
        $_count  = count($rs);
        for ($i=0; $i <$_count ; $i++) {
			$class = $post->app.'Admincp';
			$delete = 'do_del';
        	if(@class_exists($class) && @method_exists ($class,'do_del')){
        		if($post->app=='content'){
        			$obj = new $class($rs[$i]['appid']);
        		}elseif($post->app=='forms'){
        			$obj = new $class();
        			$delete = 'do_delete';
        		}else{
        			$obj = new $class;
        		}
        		iPHP::callback(array($obj,$delete),array($rs[$i]['indexid'],false));
        	}else{
        		$msg = "未找到内容删除方法,请手动删除内容";
        	}
        }
        $msg && iUI::alert($msg);
        iDB::query("DELETE FROM `#iCMS@__spider_url` where `pid` = '$this->pid';");
        iUI::success('所有采集数据删除完成');
    }
	/**
	 * [删除采集结果数据]
	 * @return [type] [description]
	 */
	public function do_dropurl() {
		$this->pid OR iUI::alert("请选择要删除的项目");

		$type = $_GET['type'];
		if ($type == "0") {
			$sql = " AND `publish`='0'";
		}
		iDB::query("delete from `#iCMS@__spider_url` where `pid` = '$this->pid'{$sql};");
		iUI::success('数据清除完成');
	}
	/**
	 * [自动采集页]
	 * @return [type] [description]
	 */
	public function do_start() {
		$a = spider_urls::crawl('WEB@AUTO');
		$this->do_mpublish($a);
	}
	/**
	 * [批量发布]
	 * @return [type] [description]
	 */
	public function do_mpublish($pubArray = array()) {
		@set_time_limit(0);
		iUI::$break = false;
		if ($_POST['pub']) {
			foreach ((array) $_POST['pub'] as $i => $a) {
				list($cid, $pid, $rid, $url, $title) = explode('|', $a);
				$pubArray[] = array('sid' => 0, 'url' => $url, 'title' => $title, 'cid' => $cid, 'rid' => $rid, 'pid' => $pid);
			}
		}
		if (empty($pubArray)) {
			iUI::$break = true;
			iUI::alert('暂无最新内容', 0, 30);
		}
		$_count = count($pubArray);
		@header('Cache-Control: no-cache');
		iUI::flush_start();
		foreach ((array) $pubArray as $i => $a) {
			spider::$sid = $a['sid'];
			spider::$cid = $a['cid'];
			spider::$pid = $a['pid'];
			spider::$rid = $a['rid'];
			spider::$url = $a['url'];
			spider::$title = $a['title'];
			$rs = $this->multipublish($_count,$i+1);
			$updateMsg = $i ? true : false;
			$timeout = ($i++) == $_count ? '3' : false;
			iUI::dialog($rs['msg'], 'js:' . $rs['js'], $timeout, 0, $updateMsg);
			iUI::flush();
		}
		iDB::update('spider_project', array('lastupdate' => time()), array('id' => $this->pid));
		iUI::dialog('success:#:check:#:采集完成!', 0, 3, 0, true);
	}
	public function multipublish($count,$i) {
		$a = array();
		$code = spider::publish('WEB@AUTO');
		if (is_array($code)) {
			$label = '内容ID:'.$code['indexid'].' <span class="label label-success">发布成功!</span>';
		} else {
			$code == "-1" && $label = '<span class="label label-warning">该URL的文章已经发布过!请检查是否重复</span>';
		}
		$a['msg'].= "总共{$count}条,当前第{$i}条,剩余".($count-$i)."条<hr />";
		$a['msg'].= '标题:' . spider::$title . '<br />URL:' . spider::$url . '<br />' . $label . '<hr />';
		$a['js'] = 'parent.$("#' . md5(spider::$url) . '").remove();';
		return $a;
	}
	/**
	 * [发布]
	 * @return [type] [description]
	 */
	public function do_publish($work = null) {
		return spider::publish($work);
	}

	public function spider_url($work = NULL, $pid = NULL, $_rid = NULL, $_urls = NULL, $callback = NULL) {
		return spider_urls::crawl($work, $pid, $_rid, $_urls, $callback);
	}

	public function spider_content() {
		return spider_data::crawl();
	}
	/**
	 * [采集规则管理]
	 * @return [type] [description]
	 */
	public function do_rule() {
		if ($_GET['keywords']) {
			$sql = " WHERE CONCAT(name,rule) REGEXP '{$_GET['keywords']}'";
		}
		list($orderby,$orderby_option) = get_orderby();
		$maxperpage = $_GET['perpage'] > 0 ? (int) $_GET['perpage'] : 20;
		$total = iCMS::page_total_cache( "SELECT count(*) FROM `#iCMS@__spider_rule` {$sql}", "G");
		iUI::pagenav($total, $maxperpage, "个规则");
		$rs = iDB::all("SELECT * FROM `#iCMS@__spider_rule` {$sql} order by {$orderby} LIMIT " . iUI::$offset . " , {$maxperpage}");
		$_count = count($rs);
		include admincp::view("spider.rule");
	}
	/**
	 * [导出采集规则]
	 * @return [type] [description]
	 */
	public function do_exportrule() {
		$rs = iDB::row("select `name`, `rule` from `#iCMS@__spider_rule` where id = '$this->rid'");
		$data = array('name' => addslashes($rs->name), 'rule' => addslashes($rs->rule));
		$data = base64_encode(serialize($data));
		Header("Content-type: application/octet-stream");
		Header("Content-Disposition: attachment; filename=spider.rule." . $rs->name . '.txt');
        echo $data;
    }

	/**
	 * [导入采集规则]
	 * @return [type] [description]
	 */
	public function do_import_rule() {
        files::$check_data        = false;
        files::$cloud_enable      = false;
        iFS::$config['allow_ext'] = 'txt';
		$F = iFS::upload('upfile');
		$path = $F['RootPath'];
		if ($path) {
			$data = file_get_contents($path);
			if ($data) {
				$data = base64_decode($data);
				$data = unserialize($data);
				iDB::insert("spider_rule", $data);
			}
			@unlink($path);
			iUI::success('规则导入完成', 'js:1');
		}
	}
	/**
	 * [复制采集规则]
	 * @return [type] [description]
	 */
	public function do_copyrule() {
		iDB::query("insert into `#iCMS@__spider_rule` (`name`, `rule`) select `name`, `rule` from `#iCMS@__spider_rule` where id = '$this->rid'");
		$rid = iDB::$insert_id;
		iUI::success('复制完成,编辑此规则', 'url:' . APP_URI . '&do=addrule&rid=' . $rid);
	}
	/**
	 * [删除采集规则]
	 * @return [type] [description]
	 */
	public function do_delrule() {
		$this->rid OR iUI::alert("请选择要删除的项目");
		iDB::query("delete from `#iCMS@__spider_rule` where `id` = '$this->rid';");
		iUI::success('删除完成', 'js:1');
	}
	/**
	 * [添加采集规则]
	 * @return [type] [description]
	 */
	public function do_addrule() {
		$rs = array();
		$this->rid && $rs = spider::rule($this->rid);
		$rs['rule'] && $rule = $rs['rule'];
		if (empty($rule['data'])) {
			$rule['data'] = array(
				array('name' => 'title', 'trim' => true, 'empty' => true),
				array('name' => 'body', 'trim' => true, 'empty' => true, 'format' => true, 'page' => true, 'multi' => true),
			);
		}
		$rule['sort'] OR $rule['sort'] = 1;
		$rule['mode'] OR $rule['mode'] = 1;
		$rule['page_no_start'] OR $rule['page_no_start'] = 1;
		$rule['page_no_end'] OR $rule['page_no_end'] = 5;
		$rule['page_no_step'] OR $rule['page_no_step'] = 1;

		include admincp::view("spider.addrule");
	}
	/**
	 * [保存采集规则]
	 * @return [type] [description]
	 */
	public function do_saverule() {
		$id = (int) $_POST['id'];
		$name = iSecurity::escapeStr($_POST['name']);
		$rule = $_POST['rule'];

		empty($name) && iUI::alert('规则名称不能为空！');
		//empty($rule['list_area_rule']) 	&& iUI::alert('列表区域规则不能为空！');
		if ($rule['mode'] != '2') {
			empty($rule['list_url_rule']) && iUI::alert('列表链接规则不能为空！');
		}

		$rule = addslashes(serialize($rule));
		$fields = array('name', 'rule');
		$data = compact($fields);
		if ($id) {
			iDB::update('spider_rule', $data, array('id' => $id));
			iUI::success('保存成功');
		} else {
            $id = iDB::insert('spider_rule',$data);
			iUI::success('保存成功!', 'url:' . APP_URI . "&do=addrule&rid=" . $id);
		}
	}

	public function rule_opt($id = 0, $output = null) {
		$rs = iDB::all("SELECT * FROM `#iCMS@__spider_rule` order by id desc");
		foreach ((array) $rs AS $rule) {
			$rArray[$rule['id']] = $rule['name'];
			$opt .= "<option value='{$rule['id']}'" . ($id == $rule['id'] ? " selected='selected'" : '') . ">{$rule['name']}[id='{$rule['id']}'] </option>";
		}
		if ($output == 'array') {
			return $rArray;
		}
		return $opt;
	}
	/**
	 * [发布模块管理]
	 * @return [type] [description]
	 */
	public function do_post() {
		if ($_GET['keywords']) {
			$sql = " WHERE CONCAT(name,app,post) REGEXP '{$_GET['keywords']}'";
		}
		list($orderby,$orderby_option) = get_orderby();
		$maxperpage = $_GET['perpage'] > 0 ? (int) $_GET['perpage'] : 20;
		$total = iCMS::page_total_cache( "SELECT count(*) FROM `#iCMS@__spider_post` {$sql}", "G");
		iUI::pagenav($total, $maxperpage, "个模块");
		$rs = iDB::all("SELECT * FROM `#iCMS@__spider_post` {$sql} order by {$orderby} LIMIT " . iUI::$offset . " , {$maxperpage}");
		$_count = count($rs);
		include admincp::view("spider.post");
	}
	/**
	 * [复制发布模块]
	 * @return [type] [description]
	 */
	public function do_copypost() {
		iDB::query("INSERT INTO `#iCMS@__spider_post` (`name`, `app`, `post`, `fun`)
 SELECT `name`, `app`, `post`, `fun` FROM `#iCMS@__spider_post` WHERE id = '$this->poid'");
		$poid = iDB::$insert_id;
		iUI::success('复制完成,编辑此规则', 'url:' . APP_URI . '&do=addpost&poid=' . $poid);
	}
	/**
	 * [删除发布模块]
	 * @return [type] [description]
	 */
	public function do_delpost() {
		$this->poid OR iUI::alert("请选择要删除的项目");
		iDB::query("delete from `#iCMS@__spider_post` where `id` = '$this->poid';");
		iUI::success('删除完成', 'js:1');
	}
	/**
	 * [添加发布模块]
	 * @return [type] [description]
	 */
	public function do_addpost() {
		$this->poid && $rs = iDB::row("SELECT * FROM `#iCMS@__spider_post` WHERE `id`='$this->poid' LIMIT 1;", ARRAY_A);
		include admincp::view("spider.addpost");
	}
	/**
	 * [保存发布模块]
	 * @return [type] [description]
	 */
	public function do_savepost() {
		$id = (int) $_POST['id'];
		$name = trim($_POST['name']);
		$app = iSecurity::escapeStr($_POST['app']);
		$post = trim($_POST['post']);
		$fun = trim($_POST['fun']);

		$fields = array('name', 'app', 'fun', 'post');
		$data = compact($fields);
		if ($id) {
			iDB::update('spider_post', $data, array('id' => $id));
		} else {
			iDB::insert('spider_post', $data);
		}
		iUI::success('保存成功', 'url:' . APP_URI . '&do=post');
	}

	public function post_opt($id = 0, $output = null) {
		$rs = iDB::all("SELECT * FROM `#iCMS@__spider_post`");
		foreach ((array) $rs AS $post) {
			$pArray[$post['id']] = $post['name'];
			$opt .= "<option value='{$post['id']}'" . ($id == $post['id'] ? " selected='selected'" : '') . ">{$post['name']}:{$post['app']}[id='{$post['id']}'] </option>";
		}
		if ($output == 'array') {
			return $pArray;
		}
		return $opt;
	}
	/**
	 * [复制采集方案]
	 * @return [type] [description]
	 */
	public function do_copyproject() {
		iDB::query("INSERT INTO `#iCMS@__spider_project` (`name`, `urls`, `cid`, `rid`, `poid`, `sleep`,`checker`,`self`,`auto`, `psleep`) select `name`, `urls`, `cid`, `rid`, `poid`, `sleep`,`checker`,`self`,`auto`,`psleep` from `#iCMS@__spider_project` where id = '$this->pid'");
		$pid = iDB::$insert_id;
		iUI::success('复制完成,编辑此方案', 'url:' . APP_URI . '&do=addproject&pid=' . $pid . '&copy=1');
	}
	/**
	 * [采集方案管理]
	 * @return [type] [description]
	 */
	public function do_project() {

		$sql = "where 1=1";
		if ($_GET['keywords']) {
			$sql .= " and `name` REGEXP '{$_GET['keywords']}'";
		}
		$sql .= category::search_sql($this->cid);

		if ($_GET['rid']) {
			$sql .= " AND `rid` ='" . (int) $_GET['rid'] . "'";
		}
		if (isset($_GET['auto'])) {
			$sql .= " AND `auto` ='".$_GET['auto']."'";
		}
		if ($_GET['poid']) {
			$sql .= " AND `poid` ='" . (int) $_GET['poid'] . "'";
		}
        $_GET['starttime'] && $sql.=" AND `lastupdate`>='".str2time($_GET['starttime']." 00:00:00")."'";
        $_GET['endtime']   && $sql.=" AND `lastupdate`<='".str2time($_GET['endtime']." 23:59:59")."'";
		$ruleArray = $this->rule_opt(0, 'array');
		$postArray = $this->post_opt(0, 'array');
		list($orderby,$orderby_option) = get_orderby();
		$maxperpage = $_GET['perpage'] > 0 ? (int) $_GET['perpage'] : 20;
		$total = iCMS::page_total_cache( "SELECT count(*) FROM `#iCMS@__spider_project` {$sql}", "G");
		iUI::pagenav($total, $maxperpage, "个方案");
		$rs = iDB::all("SELECT * FROM `#iCMS@__spider_project` {$sql} order by {$orderby} LIMIT " . iUI::$offset . " , {$maxperpage}");
		$_count = count($rs);
		include admincp::view("spider.project");
	}
	/**
	 * [删除采集方案]
	 * @return [type] [description]
	 */
	public function do_delproject() {
		$this->pid OR iUI::alert("请选择要删除的项目");
		iDB::query("delete from `#iCMS@__spider_project` where `id` = '$this->pid';");
		iUI::success('删除完成');
	}
	/**
	 * [添加采集方案]
	 * @return [type] [description]
	 */
	public function do_addproject() {
		$rs = array();
		$this->pid && $rs = spider::project($this->pid);
		$cid = empty($rs['cid']) ? $this->cid : $rs['cid'];

		$cata_option = category::select($cid);
		$rule_option = $this->rule_opt($rs['rid']);
		$post_option = $this->post_opt($rs['poid']);

		//$rs['sleep'] OR $rs['sleep'] = 30;
		include admincp::view("spider.addproject");
	}
	/**
	 * [保存采集方案]
	 * @return [type] [description]
	 */
	public function do_saveproject() {
		$id = (int) $_POST['id'];
		$name = iSecurity::escapeStr($_POST['name']);
		$urls = iSecurity::escapeStr($_POST['urls']);
		$list_url = $_POST['list_url'];
		$cid = iSecurity::escapeStr($_POST['cid']);
		$rid = iSecurity::escapeStr($_POST['rid']);
		$poid = iSecurity::escapeStr($_POST['poid']);
		$poid = iSecurity::escapeStr($_POST['poid']);
		$checker = iSecurity::escapeStr($_POST['checker']);
		$self = (int) $_POST['self'];
		$sleep = (int) $_POST['sleep'];
		$auto = iSecurity::escapeStr($_POST['auto']);
		$psleep = (int) $_POST['psleep'];
		$lastupdate = $_POST['lastupdate'] ? str2time($_POST['lastupdate']) : '';
		empty($name) && iUI::alert('名称不能为空！');
		empty($cid) && iUI::alert('请选择绑定的栏目');
		empty($rid) && iUI::alert('请选择采集规则');
		//empty($poid)	&& iUI::alert('请选择发布规则');
		$fields = array('name', 'urls', 'list_url', 'cid', 'rid', 'poid', 'checker', 'self', 'sleep', 'auto', 'lastupdate', 'psleep');
		$data = compact($fields);
		if ($id) {
			iDB::update('spider_project', $data, array('id' => $id));
		} else {
			iDB::insert('spider_project', $data);
		}
		iUI::success('完成', 'url:' . APP_URI . '&do=project');
	}
	/**
	 * [导入采集方案]
	 * @return [type] [description]
	 */
    public function do_import_project(){
        files::$check_data        = false;
        files::$cloud_enable      = false;
        iFS::$config['allow_ext'] = 'txt';
        $F    = iFS::upload('upfile');
        $path = $F['RootPath'];
        if($path){
            $data = file_get_contents($path);
            if($data){
                $data = base64_decode($data);
                $data = unserialize($data);
                foreach ((array)$data as $key => $value) {
                    iDB::insert("spider_project",$value);
                }
            }
            @unlink($path);
            iUI::success('方案导入完成,请重新设置规则','js:1');
        }
    }
	/**
	 * [导出采集方案]
	 * @return [type] [description]
	 */
    public function do_exportproject(){
        $data = iDB::all("
        	SELECT `name`, `urls`, `list_url`,
        	`cid`, `rid`, `poid`, `sleep`,
        	`checker`, `self`, `auto`,
        	`lastupdate`, `psleep`
        	FROM `#iCMS@__spider_project`
        	WHERE rid = '$this->rid'
        ");
        $data = base64_encode(serialize($data));
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=spider.rule.".$this->rid.'.project.txt');
		echo $data;
	}
	/**
	 * [测试代理 [NOPRIV]]
	 * @return [type] [description]
	 */
	public function do_proxy_test() {
		$a = spider_tools::proxy_test();
		var_dump($a);
	}

}
