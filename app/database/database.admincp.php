<?php
/**
 * iCMS - i Content Management System
 * Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
 *
 * @author icmsdev <master@icmsdev.com>
 * @site https://www.icmsdev.com
 * @licence https://www.icmsdev.com/LICENSE.html
 */
class databaseAdmincp {
	public function __construct() {
		$this->bakdir = $_GET['dir'];
		iFS::check($this->bakdir);
	}
	/**
	 * [数据库恢复页]
	 * @return [type] [description]
	 */
	public function do_recover() {
		$res = iFS::folder('cache/backup', array('sql'));
		$dirRs = $res['DirArray'];
		$fileRs = $res['FileArray'];
		$pwd = $res['pwd'];
		$parent = $res['parent'];
		$URI = $res['URI'];
		include admincp::view("database.recover");
	}
	/**
	 * [修复数据库]
	 * @return [type] [description]
	 */
	public function do_repair() {
		$this->do_backup();
	}
	/**
	 * [数据库备份页]
	 * @return [type] [description]
	 */
	public function do_backup() {
		$rs = iDB::all("SHOW TABLE STATUS FROM `" . iPHP_DB_NAME . "` WHERE ENGINE IS NOT NULL;");
		//print_r($rs);
		$_count = count($rs);
//		for($i=0;$i<$_count;$i++){
		//			if (preg_match ("/^".preg_quote(iPHP_DB_PREFIX,'/')."/i" ,$rs[$i]['Name'])){
		//				$iTable[] = $rs[$i];
		//			}else{
		//				$oTable[] = $rs[$i];
		//			}
		//		}
		include admincp::view("database.backup");
	}
	/**
	 * [数据替换页]
	 * @return [type] [description]
	 */
	public function do_replace() {
		include admincp::view("database.replace");
	}
	public function do_batch() {
		list($idArray,$ids,$batch) = iUI::get_batch_args("请选择要操作的表");
		switch ($batch) {
		case 'backup':
			$this->do_savebackup();
			break;
		case 'optimize':
			$this->optimize($tables);
			break;
		case 'repair':
			$this->repair($tables);
			break;
		}
	}
	/**
	 * [数据备份]
	 * @return [type] [description]
	 */
	public function do_savebackup() {
		iDB::query("SET SQL_QUOTE_SHOW_CREATE = 0");

		$tableA = $_POST['table'];
		$step = (int) $_GET['step'];
		$tablesel = $_GET['tablesel'];
		$random = $_GET['random'];
		$bdir = $_GET['bdir'];
		$this->sizelimit = isset($_POST['sizelimit']) ? (int) $_POST['sizelimit'] : (int) $_GET['sizelimit'];
		$this->tableid = (int) $_GET['tableid'];
		$this->start = (int) $_GET['start'];
		$this->rows = $_GET['rows'];

		!$tableA && !$tablesel && iUI::alert('没有选择操作对象');
		!$tableA && $tableA = explode("|", $tablesel);
		!$step && $this->sizelimit /= 2;
		iFS::check($bdir);

		$bakupdata = $this->bakupdata($tableA, $this->start);
		$bakTag = "# iCMS Backup SQL File\n# Version:iCMS " . iCMS_VERSION . "\n# Time: " . get_date(0, "Y-m-d H:i:s") . "\n# iCMS: https://www.icmsdev.com\n# --------------------------------------------------------\n\n\n";
		if (!$step) {
			!$tableA && iUI::alert('没有选择操作对象');
			$tablesel = implode("|", $tableA);
			$step = 1;
			$random = random(10);
			$this->start = 0;
			$bakuptable = $this->bakuptable($tableA);
			$bdir = get_date(0, "Y-m-d-His") . '~' . random(10);
		}
		$updateMsg = ($step == 1 ? false : 'FRAME');
		$f_num = ceil($step / 2);
		$filename = 'iCMS_' . $random . '_' . $f_num . '.sql';
		$step++;
		$writedata = $bakuptable ? $bakuptable . $bakupdata : $bakupdata;

		$t_name = $tableA[$this->tableid - 1];
		$backupdir = iPHP_APP_CACHE . '/backup/' . $bdir . '/';
		$sqlpath = $backupdir . $filename;
		iFS::mkdir($backupdir);
		trim($writedata) && iFS::write($sqlpath, $bakTag . $writedata, true, 'ab');
		if ($this->stop == 1) {
			$loopurl = APP_FURI . "&do=savebackup&start={$this->startfrom}&tableid={$this->tableid}&sizelimit={$this->sizelimit}&step={$step}&random={$random}&tablesel={$tablesel}&rows={$this->rows}&bdir={$bdir}";
			$moreBtn = array(
				array("id" => "btn_stop", "text" => "停止", "url" => APP_URI . "&do=backup"),
				array("id" => "btn_next", "text" => "继续", "src" => $loopurl, "next" => true),
			);
			$dtime = 1;
			$msg = "正在备份数据库表<span class='label label-success'>{$t_name}</span>共<span class='label label-info'>{$this->rows}</span>条记录<hr />已经备份至<span class='label label-info'>{$this->startfrom}</span>条记录,已生成<span class='label label-info'>{$f_num}</span>个备份文件，<hr />程序将自动备份余下部分";
		} else {
			$msg = "success:#:check:#:已全部备份完成,备份文件保存在backup目录下!";
			$moreBtn = array(
				array("id" => "btn_next", "text" => "完成", "url" => APP_URI . "&do=backup"),
			);
			$dtime = 5;
		}
		iUI::dialog($msg, $loopurl ? "src:" . $loopurl : 'js:1', $dtime, $moreBtn, $updateMsg);
	}
	public function do_del() {
		$this->bakdir OR iUI::alert('请选择要删除的备份卷');
		$backupdir = iPHP_APP_CACHE . '/backup/' . $this->bakdir;
		if (iFS::rmdir($backupdir)) {
			iUI::success('备份文件已删除!', 'js:parent.$("#' . md5($this->bakdir) . '").remove();');
		}
	}
	/**
	 * [下载备份]
	 * @return [type] [description]
	 */
	public function do_download() {
		$this->bakdir OR iUI::alert('请选择要下载的备份卷');
		iPHP::vendor('PclZip'); //加载zip操作类
		$zipname = $this->bakdir . ".zip"; //压缩包的名称
		$zipFile = iPHP_APP_CACHE . '/backup/' . $zipname; //压缩包所在路径
		$zip = new PclZip($zipFile);
		$backupdir = iPHP_APP_CACHE . '/backup/' . $this->bakdir;
		$fileArray = glob($backupdir . '/iCMS_*.sql');
		$filelist = implode(',', $fileArray);
		$v_list = $zip->create($filelist, PCLZIP_OPT_REMOVE_PATH, iPHP_APP_CACHE . '/backup/'); //将文件进行压缩
		$v_list == 0 && iPHP::error_throw("压缩出错 : " . $zip->errorInfo(true)); //如果有误，提示错误信息。
		ob_end_clean();
		header("Content-Type: application/force-download");
		header("Content-Transfer-Encoding: binary");
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . $zipname);
		header('Content-Length: ' . filesize($zipFile));
		readfile($zipFile);
		flush();
		ob_flush();
	}
	/**
	 * [备份恢复]
	 * @return [type] [description]
	 */
	public function do_recovery() {
		iUI::alert('请使用其它mysql管理软件恢复');
		$this->bakdir OR iUI::alert('请选择要恢复的备份卷');
		$backupdir = iPHP_APP_CACHE . '/backup/' . $this->bakdir;
		$step = (int) $_GET['step'];
		$count = (int) $_GET['count'];
		if ($count == 0) {
			$fileArray = glob($backupdir . '/iCMS_*.sql');
			$count = count($fileArray);
		}
		$step OR $step = 1;
		$this->bakindata($step);
		$i = $step;
		$step++;
		if ($count > 1 && $step <= $count) {
			$loopurl = APP_FURI . "&do=renew&step={$step}&count={$count}&dir={$this->bakdir}";
			$moreBtn = array(
				array("id" => "btn_stop", "text" => "停止", "url" => APP_URI . "&do=recover"),
				array("id" => "btn_next", "text" => "继续", "src" => $loopurl, "next" => true),
			);
			$dtime = 1;
			$msg = "正在导入第<span class='label label-success'>{$i}</span>卷备份文件，<hr />程序将自动导入余下备份文件...";
		} else {
			$msg = "success:#:check:#:导入成功!";
			$moreBtn = array(
				array("id" => "btn_next", "text" => "完成", "url" => APP_URI . "&do=recover"),
			);
			$dtime = 5;
		}
		$updateMsg = ($i == 1 ? false : 'FRAME');
		iUI::dialog($msg, $loopurl ? "src:" . $loopurl : 'js:1', $dtime, $moreBtn, $updateMsg);
	}
	/**
	 * [执行查询]
	 * @return [type] [description]
	 */
	public function do_query() {
		$field = $_POST["field"];
		$pattern = $_POST["pattern"];
		$replacement = $_POST["replacement"];
		$where = $_POST["where"];
		$pattern OR iUI::alert("查找项不能为空~!");
		if ($field == "body") {
			$rows_affected = iDB::query("UPDATE `#iCMS@__article_data` SET `body` = REPLACE(`body`, '$pattern', '$replacement') {$where}");
		} else {
			if ($field == "tkd") {
				$rows_affected = iDB::query("UPDATE `#iCMS@__article` SET `title` = REPLACE(`title`, '$pattern', '$replacement'),
		    	`keywords` = REPLACE(`keywords`, '$pattern', '$replacement'),
		    	`description` = REPLACE(`description`, '$pattern', '$replacement'){$where}");
			} else {
				$rows_affected = iDB::query("UPDATE `#iCMS@__article` SET `$field` = REPLACE(`$field`, '$pattern', '$replacement'){$where}");
			}
		}
		iUI::success($rows_affected . "条记录被替换<hr />操作完成!!");
	}
	/**
	 * [优化分表]
	 * @return [type] [description]
	 */
	public function do_sharding() {
		admincp::head();
		print("暂无");
		admincp::foot();
	}
	public static function bakuptable($tabledb,$exists=true) {
		foreach ($tabledb as $table) {
			$exists && $creattable .= "DROP TABLE IF EXISTS `$table`;\n";
			$CreatTable = iDB::row("SHOW CREATE TABLE $table", ARRAY_A);
			$CreatTable['Create Table'] = str_replace($CreatTable['Table'], $table, $CreatTable['Create Table']);
			$creattable .= $CreatTable['Create Table'] . ";\n\n";
			//$creattable=str_replace(iPHP_DB_PREFIX,'iCMS_',$creattable);
		}
		return $creattable;
	}
	public function bakupdata($tabledb, $start = 0) {
		//global $iCMS,$sizelimit,$tableid,$startfrom,$stop,$rows;
		$this->tableid = $this->tableid ? $this->tableid - 1 : 0;
		$this->stop = 0;
		$t_count = count($tabledb);

		for ($i = $this->tableid; $i < $t_count; $i++) {
			$ts = iDB::row("SHOW TABLE STATUS LIKE '$tabledb[$i]'");
			$this->rows = $ts->Rows;

			$limit = "LIMIT $start,100000";
			if (version_compare(PHP_VERSION, '5.5', '>=')) {
				$result = mysqli_query(iDB::$link, "SELECT * FROM $tabledb[$i] $limit");
				$fnum = iDB::$link->field_count;
				if (!$result) {
					continue;
				}
				while ($datadb = $result->fetch_row()) {
					$start++;
					//$table        = str_replace(iPHP_DB_PREFIX,'iCMS_',$tabledb[$i]);
					$table = $tabledb[$i];
					$bakupdata .= "INSERT INTO $table VALUES(" . "'" . addslashes($datadb[0]) . "'";
					$tempdb = '';
					for ($j = 1; $j < $fnum; $j++) {
						$tempdb .= ",'" . addslashes($datadb[$j]) . "'";
					}
					$bakupdata .= $tempdb . ");\n";
					if ($this->sizelimit && strlen($bakupdata) > $this->sizelimit * 1000) {
						break;
					}
				}
				$result->close();
			} else {
				$query = mysql_query("SELECT * FROM $tabledb[$i] $limit");
				$fnum = mysql_num_fields($query);
				while ($datadb = mysql_fetch_row($query)) {
					$start++;
					//$table		= str_replace(iPHP_DB_PREFIX,'iCMS_',$tabledb[$i]);
					$table = $tabledb[$i];
					$bakupdata .= "INSERT INTO $table VALUES(" . "'" . addslashes($datadb[0]) . "'";
					$tempdb = '';
					for ($j = 1; $j < $fnum; $j++) {
						$tempdb .= ",'" . addslashes($datadb[$j]) . "'";
					}
					$bakupdata .= $tempdb . ");\n";
					if ($this->sizelimit && strlen($bakupdata) > $this->sizelimit * 1000) {
						break;
					}
				}
				mysql_free_result($query);
			}
			if ($start >= $this->rows) {
				$start = 0;
				$this->rows = 0;
			}

			$bakupdata .= "\n";
			if ($this->sizelimit && strlen($bakupdata) > $this->sizelimit * 1000) {
				$start == 0 && $i++;
				$this->stop = 1;
				break;
			}
			$start = 0;
		}
		if ($this->stop == 1) {
			$i++;
			$this->tableid = $i;
			$this->startfrom = $start;
			$start = 0;
		}
		return $bakupdata;
	}
	public function repair($tables) {
		$tableA = (array) $_POST['table'];
		$rs = iDB::all("REPAIR TABLE $tables");
		$_count = count($rs);
		for ($i = 0; $i < $_count; $i++) {
			$msg .= '表：' . substr(strrchr($rs[$i]['Table'], '.'), 1) . ' 操作：' . $rs[$i]['Op'] . ' 状态：' . $rs[$i]['Msg_text'] . '<hr />';
		}
		iUI::success($msg . "修复表完成");
	}
	public function optimize($tables) {
		$tableA = (array) $_POST['table'];
		$rs = iDB::all("OPTIMIZE TABLE $tables");
		$_count = count($rs);
		for ($i = 0; $i < $_count; $i++) {
			$msg .= '表：' . substr(strrchr($rs[$i]['Table'], '.'), 1) . ' 操作：' . $rs[$i]['Op'] . ' 状态：' . $rs[$i]['Msg_text'] . '<hr />';
		}
		iUI::success($msg . "优化表完成");
	}

	public function bakindata($num) {
		$backupdir = iPHP_APP_CACHE . '/backup/' . $this->bakdir;
		$fileList = glob($backupdir . '/iCMS_*_' . $num . '.sql');
		$fileArray = file($fileList[0]);
		$sql = '';
		$num = 0;
		foreach ($fileArray as $key => $line) {
			$line = trim($line);
			if (!$line || $line[0] == '#') {
				continue;
			}

			if (preg_match("/\;$/", $line)) {
				$sql .= $line;
				if (preg_match("/^CREATE/", $sql)) {
					$extra = substr(strrchr($sql, ')'), 1);
					$tabtype = substr(strchr($extra, '='), 1);
					$tabtype = substr($tabtype, 0, strpos($tabtype, strpos($tabtype, ' ') ? ' ' : ';'));
					$sql = str_replace($extra, '', $sql);
					$extra = iPHP_DB_CHARSET ? " ENGINE=$tabtype DEFAULT CHARSET=" . iPHP_DB_CHARSET . ";" : "ENGINE=$tabtype;";
					$sql .= $extra;
				} elseif (preg_match("/^INSERT/", $sql)) {
					$sql = 'REPLACE ' . substr($sql, 6);
				}
				iDB::query($sql);
				$sql = '';
			} else {
				$sql .= $line;
			}
		}
	}

}
