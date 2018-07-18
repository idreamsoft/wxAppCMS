<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class filesAdmincp{
    public static $appid            = null;
    public static $no_http          = false;
    public static $pic_value        = null;
    public static $DELETE_ERROR_PIC = false;

    public function __construct() {
        self::$appid    = apps::id(__CLASS__);
	    $this->from		= iSecurity::escapeStr($_GET['from']);
	    $this->callback	= iSecurity::escapeStr($_GET['callback']);
		$this->click	= iSecurity::escapeStr($_GET['click']);
        $this->target   = iSecurity::escapeStr($_GET['target']);
        $this->format   = iSecurity::escapeStr($_GET['format']);
    	$this->id		= (int)$_GET['id'];
	    $this->callback OR $this->callback	= 'icms';
        $this->upload_max_filesize = get_cfg_var("upload_max_filesize");
    }
    public function _trim(&$value){
        $value = trim($value);
    }
    public function do_cloud_config(){
        configAdmincp::app(self::$appid,'cloud');
    }
    public function do_save_cloud_config(){
        array_walk_recursive($_POST['config'],array(__CLASS__,'_trim'));
        configAdmincp::save(self::$appid,'cloud');
    }
    public static function cloud_config_file(){
        $array = array();
        foreach (glob(iPHP_APP_DIR."/files/admincp/cloud_*.php") as $filename) {
            $sdk = str_replace(array('cloud_','.php'), '', basename($filename));
            $array[$sdk] = $filename;
        }
        return $array;
    }
    /**
     * [单文件上传页面]
     * @return [type] [description]
     */
	public function do_add(){
		$this->id && $rs = iFS::get_filedata('id',$this->id);
        $href = '###';
        if($rs){
            $filepath = $rs->path.$rs->filename.'.'.$rs->ext;
            $href     = iFS::fp($filepath,"+http");
        }

		include admincp::view("files.add");
	}
    /**
     * [批量上传]
     * @return [type] [description]
     */
	public function do_multi(){
		$file_upload_limit	= $_GET['UN']?$_GET['UN']:100;
		$file_queue_limit	= $_GET['QN']?$_GET['QN']:10;
		$file_size_limit	= (int)$this->upload_max_filesize;
        $file_size_limit OR iUI::alert("检测到系统环境脚本上传文件大小限制为{$this->upload_max_filesize},请联系管理员");
        stristr($this->upload_max_filesize,'m') && $file_size_limit    = $file_size_limit*1024;
		include admincp::view("files.multi");
	}

	public function do_iCMS(){
    	$sql='WHERE 1=1 ';
        if($_GET['keywords']) {
            if($_GET['st']=="filename") {
                $sql.=" AND `filename` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="userid") {
                $sql.=" AND `userid` = '{$_GET['keywords']}'";
            }else if($_GET['st']=="ofilename") {
                $sql.=" AND `ofilename` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="size") {
                $sql.=" AND `size` = '{$_GET['keywords']}'";
            }else if($_GET['st']=="path") {
                $sql.=" AND `path` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="ext") {
                $sql.=" AND `ext` = '{$_GET['keywords']}'";
            }
        }

        if($_GET['indexid'] ||($_GET['st']=="indexid" && $_GET['keywords'])){
            $_GET['indexid'] && $indexid = (int)$_GET['indexid'];
            $_GET['keywords'] && $indexid = (int)$_GET['keywords'];
            $_GET['appid'] && $appid = (int)$_GET['appid'];
            $msql = iSQL::in($indexid,'indexid',false,true);
            $appid && $msql.= iSQL::in($appid,'appid',false);

            $msql && $fids_array = iDB::all("SELECT `fileid` FROM ".files::$_MAP_TABLE." WHERE {$msql}");
            $ids = iSQL::values($fids_array,'fileid');
            $ids = $ids ? $ids : '0';
            $sql.= "AND `id` IN({$ids})";
        }
        isset($_GET['type']) && $_GET['type']!='-1'  && $sql.=" AND `type`='".(int)$_GET['type']."'";

        $_GET['starttime'] && $sql.=" AND `time`>='".str2time($_GET['starttime'].(strpos($_GET['starttime'],' ')!==false?'':" 00:00:00"))."'";
        $_GET['endtime']   && $sql.=" AND `time`<='".str2time($_GET['endtime'].(strpos($_GET['endtime'],' ')!==false?'':" 23:59:59"))."'";

        isset($_GET['userid']) 	&& $uri.='&userid='.(int)$_GET['userid'];

        list($orderby,$orderby_option) = get_orderby(array(
            'id'   =>"ID",
            'size' =>"文件大小",
            'ext'  =>"后缀值",
        ));

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:50;
		$total		= iCMS::page_total_cache("SELECT count(*) FROM ".files::$_DATA_TABLE." {$sql}","G");
        iUI::pagenav($total,$maxperpage,"个文件");
        $rs     = iDB::all("SELECT * FROM ".files::$_DATA_TABLE." {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
        $widget = array('search'=>1,'id'=>1,'uid'=>1,'index'=>1);
    	include admincp::view("files.manage");
    }
    /**
     * [流数据上传]
     * @return [type] [description]
     */
    public function do_IO(){
        files::$watermark_enable = $_GET['watermark'];
        $udir      = iSecurity::escapeStr($_GET['udir']);
        $name      = iSecurity::escapeStr($_GET['name']);
        $ext       = iSecurity::escapeStr($_GET['ext']);
        iFS::check_ext($ext,0) OR iUI::json(array('state'=>'ERROR','msg'=>'不允许的文件类型'));
        iFS::$ERROR_TYPE = true;
        $F = iFS::IO($name,$udir,$ext);
        $F ===false && iUI::json(iFS::$ERROR);
        iUI::json(array(
            "value"    => $F["path"],
            "url"      => iFS::fp($F['path'],'+http'),
            "fid"      => $F["fid"],
            "fileType" => $F["ext"],
            "image"    => in_array($F["ext"],files::$IMG_EXT)?1:0,
            "original" => $F["oname"],
            "state"    => ($F['code']?'SUCCESS':$F['state'])
        ));
    }
    /**
     * [上传文件]
     * @return [type] [description]
     */
    public function do_upload(){
        files::$watermark_enable = !isset($_POST['unwatermark']);
        iFS::$ERROR_TYPE = true;
    	if($this->id){
            iFS::$data = files::get('id',$this->id);
            $F = iFS::upload('upfile');
            if($F && $F['size']!=iFS::$data->size){
                files::update_size($this->id,$F['size']);
            }
    	}else{
            $udir = ltrim($_POST['udir'],'/');
            $F    = iFS::upload('upfile',$udir);
    	}
        $array = ($F===false)?iFS::$ERROR:array(
            "value"    => $F["path"],
            "url"      => iFS::fp($F['path'],'+http'),
            "fid"      => $F["fid"],
            "fileType" => $F["ext"],
            "image"    => in_array($F["ext"],files::$IMG_EXT)?1:0,
            "original" => $F["oname"],
            "state"    => ($F['code']?'SUCCESS':$F['state'])
        );
		if($this->format=='json'){
	    	iUI::json($array);
		}else{
			iUI::js_callback($array);
		}
    }
    /**
     * [下载远程图片]
     * @return [type] [description]
     */
    public function do_download(){
        files::$userid   = false;
        $rs            = iFS::get_filedata('id',$this->id);
        $FileRootPath  = iFS::fp($rs->filepath,"+iPATH");
        iFS::check_ext($rs->filepath,true) OR iUI::alert('文件类型不合法!');
        files::$userid = members::$userid;
        $fileresults   = iHttp::remote($rs->ofilename);

    	if($fileresults){
            iFS::$CALLABLE['write'] = array('files_cloud','upload');

    		iFS::mkdir(dirname($FileRootPath));
    		iFS::write($FileRootPath,$fileresults);
            files::$watermark_enable = !isset($_GET['unwatermark']);
    		$_FileSize	= strlen($fileresults);
    		if($_FileSize!=$rs->size){
                files::update_size($this->id,_FileSize);
    		}
    		iUI::success("{$rs->ofilename} <br />重新下载到<br /> {$rs->filepath} <br />完成",'js:1',3);
    	}else{
    		iUI::alert("下载远程文件失败!",'js:1',3);
    	}
    }
    public function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iUI::alert("请选择要删除的文件");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iUI::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id);
	    		}
	    		iUI::$break	= true;
				iUI::success('文件全部删除完成!','js:1');
    		break;
		}
	}
    public function do_del($id = null){
        $id ===null && $id = $this->id;
        $id OR iUI::alert("请选择要删除的文件");
        // $indexid = (int)$_GET['indexid'];
        // $indexid && $result  = files::index_fileid($indexid);

        $result  = files::delete_file($id);
        files::delete_fdb($id);
        if($result){
            $msg = 'success:#:check:#:文件删除完成!';
            $_GET['ajax'] && iUI::json(array('code'=>1,'msg'=>$msg));
        }else{
             $msg = 'warning:#:warning:#:找不到相关文件,文件删除失败!<hr/>文件相关数据已清除';
             $_GET['ajax'] && iUI::json(array('code'=>0,'msg'=>$msg));
        }
        iUI::dialog($msg,'js:parent.$("#id'.$id.'").remove();');
    }
    /**
     * [创建目录]
     * @return [type] [description]
     */
    public function do_mkdir(){
    	$name	= $_POST['name'];
        strstr($name,'.')!==false	&& iUI::json(array('code'=>0,'msg'=>'您输入的目录名称有问题!'));
        strstr($name,'..')!==false	&& iUI::json(array('code'=>0,'msg'=>'您输入的目录名称有问题!'));
    	$pwd	= trim($_POST['pwd'],'/');
    	$dir	= iFS::path_join(iPATH,iCMS::$config['FS']['dir']);
    	$dir	= iFS::path_join($dir,$pwd);
    	$dir	= iFS::path_join($dir,$name);
    	file_exists($dir) && iUI::json(array('code'=>0,'msg'=>'您输入的目录名称已存在,请重新输入!'));
    	if(iFS::mkdir($dir)){
    		iUI::json(array('code'=>1,'msg'=>'创建成功!'));
    	}
		iUI::json(array('code'=>0,'msg'=>'创建失败,请检查目录权限!!'));
    }
    /**
     * [选择模板文件页]
     * @return [type] [description]
     */
    public function do_seltpl(){
    	$this->explorer('template');
    }
    /**
     * [浏览文件]
     * @return [type] [description]
     */
    public function do_browse(){
    	$this->explorer(iCMS::$config['FS']['dir']);
    }
    /**
     * [浏览图片]
     * @return [type] [description]
     */
    public function do_picture(){
    	$this->explorer(iCMS::$config['FS']['dir'],files::$IMG_EXT);
    }
    /**
     * [图片编辑器]
     * @return [type] [description]
     */
    public function do_editpic(){
        $pic = iSecurity::escapeStr($_GET['pic']);
        //$pic OR iUI::alert("请选择图片!");
        if($pic){
            $src       = iFS::fp($pic,'+http')."?".time();
            $srcPath   = iFS::fp($pic,'+iPATH');
            $fsInfo    = iFS::info($pic);
            $file_name = $fsInfo->filename;
            $file_path = $fsInfo->dirname;
            $file_ext  = $fsInfo->extension;
            $file_id   = 0;
            $rs        = iFS::get_filedata('filename',$file_name);
            if($rs){
                $file_path = $rs->path;
                $file_id   = $rs->id;
                $file_ext  = $rs->ext;
            }
        }else{
            $file_name= md5(uniqid());
            $src      = false;
            $file_ext = 'jpg';
        }
        if($_GET['indexid']){
            $indexid = (int)$_GET['indexid'];
            $msql = iSQL::in($indexid,'indexid',false,true);
            $msql && $fids_array = iDB::all("SELECT `fileid` FROM ".files::$_MAP_TABLE." WHERE {$msql}");
            $ids = iSQL::values($fids_array);
            $ids = $ids ? $ids : '0';
            $sql = " `id` IN({$ids})";
            $rs = iDB::all("
                SELECT * FROM ".files::$_DATA_TABLE."
                WHERE {$sql}
                ORDER BY `id` ASC
                LIMIT 100
            ");
            foreach ((array)$rs as $key => $value) {
                $filepath = $value['path'] . $value['filename'] . '.' . $value['ext'];
                $src[] = iFS::fp($filepath,'+http')."?".time();
            }
        }
        if($_GET['pics']){
            $src = explode(',', $_GET['pics']);
            if(count($src)==1){
                $src = $_GET['pics'];
            }
        }
        $max_size  = (int)$this->upload_max_filesize;
        stristr($this->upload_max_filesize,'m') && $max_size = $max_size*1024*1024;
        include admincp::view("files.editpic");
    }
    /**
     * [预览]
     * @return [type] [description]
     */
    public function do_preview(){
        $_GET['pic'] && $src = iFS::fp($_GET['pic'],'+http');
        include admincp::view("files.preview");
    }
    /**
     * [删除目录]
     * @return [type] [description]
     */
    public function do_deldir(){
        $_GET['path'] OR iUI::alert("请选择要删除的目录");
        strpos($_GET['path'], '..') !== false && iUI::alert("目录路径中带有..");

        $hash         = md5($_GET['path']);
        $dirRootPath = iFS::fp($_GET['path'],'+iPATH');

        if(iFS::rmdir($dirRootPath)){
            $msg    = 'success:#:check:#:目录删除完成!';
            $_GET['ajax'] && iUI::json(array('code'=>1,'msg'=>$msg));
        }else{
            $msg    = 'warning:#:warning:#:找不到相关目录,目录删除失败!';
            $_GET['ajax'] && iUI::json(array('code'=>0,'msg'=>$msg));
        }
        iUI::dialog($msg,'js:parent.$("#'.$hash.'").remove();');
    }
    /**
     * [删除文件]
     * @return [type] [description]
     */
    public function do_delfile(){
        $_GET['path'] OR iUI::alert("请选择要删除的文件");
        strpos($_GET['path'], '..') !== false && iUI::alert("文件路径中带有..");

        $hash         = md5($_GET['path']);
        $FileRootPath = iFS::fp($_GET['path'],'+iPATH');
        if(iFS::del($FileRootPath)){
            $msg    = 'success:#:check:#:文件删除完成!';
            $_GET['ajax'] && iUI::json(array('code'=>1,'msg'=>$msg));
        }else{
            $msg    = 'warning:#:warning:#:找不到相关文件,文件删除失败!';
            $_GET['ajax'] && iUI::json(array('code'=>0,'msg'=>$msg));
        }
        iUI::dialog($msg,'js:parent.$("#'.$hash.'").remove();');
    }
    public function explorer($dir=NULL,$type=NULL){
        $res    = iFS::folder($dir,$type);
        $dirRs  = $res['DirArray'];
        $fileRs = $res['FileArray'];
        $pwd    = $res['pwd'];
        $parent = $res['parent'];
        $URI    = $res['URI'];
        $navbar = false;
        include admincp::view("files.explorer");
    }
    public static function _count(){
        return iDB::value("SELECT count(*) FROM `#iCMS@__files`");
    }
    public static function modal_btn($title='',$target='template_index',$click='file',$callback='',$do='seltpl',$from='modal'){
        $href = __ADMINCP__."=files&do={$do}&from={$from}&click={$click}&target={$target}&callback={$callback}";
        $_title=$title.'文件';
        $click=='dir' && $_title=$title.'目录';
        return '<a href="'.$href.'" class="btn files_modal" data-toggle="modal" title="选择'.$_title.'"><i class="fa fa-search"></i> 选择</a>';
    }
    public static function set_opt($pic_value = null, $no_http = true) {
        self::$no_http = $no_http;
        self::$pic_value = $pic_value;
        $self = new self();
        return $self;
    }
    public static function pic_btn($callback, $indexid = 0, $title="图片",$ret=false,$multi=false) {
        $ret && ob_start();
        include admincp::view("files.picbtn","files");
        if ($ret) {
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }
    public static function picdata($pic='',$bpic='',$mpic='',$spic=''){
        if(is_array($pic)){
            is_array($bpic) && $pic+=$bpic;
            return addslashes(json_encode($pic));
        }
        $picdata = array();
        if($pic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($pic,'+iPATH'));
            $picdata['p'] = array('w'=>$width,'h'=>$height);
        }
        if($bpic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($bpic,'+iPATH'));
            $picdata['b'] = array('w'=>$width,'h'=>$height);
        }
        if($mpic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($mpic,'+iPATH'));
            $picdata['m'] = array('w'=>$width,'h'=>$height);
        }
        if($spic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($spic,'+iPATH'));
            $picdata['s'] = array('w'=>$width,'h'=>$height);
        }
        return $picdata?addslashes(json_encode($picdata)):'';
    }
    public static function remotepic($content,$remote = false,$that=null) {
        if (!$remote) return $content;

        iFS::$force_ext = "jpg";
        $content = stripslashes($content);
        $array   = files::preg_img($content,$match);
        $uri     = parse_url(iCMS_FS_URL);
        $fArray  = array();
        $autopic = array();
        foreach ($array as $key => $value) {
            $value = trim($value);
            if (stripos($value,$uri['host']) === false){
                $filepath = iFS::http($value);
                $rootfilpath = iFS::fp($filepath, '+iPATH');
                list($owidth, $oheight, $otype) = @getimagesize($rootfilpath);
                empty($otype) && $otype = iFS::check_image_bin($rootfilpath);

                if($filepath && !iFS::checkHttp($filepath) && $otype){
                    $value = iFS::fp($filepath,'+http');
                }else{
                    if(self::$DELETE_ERROR_PIC){
                        iFS::del($rootfilpath);
                        $array[$key]  = $match[0][$key];
                        $value = '';
                    }
                }
                $fArray[$key] = $value;
            }else{
                unset($array[$key]);
                $rootfilpath = iFS::fp($value, 'http2iPATH');
                list($owidth, $oheight, $otype) = @getimagesize($rootfilpath);
                empty($otype) && $otype = iFS::check_image_bin($rootfilpath);

                if(self::$DELETE_ERROR_PIC && empty($otype)){
                    iFS::del($rootfilpath);
                    $array[$key]  = $match[0][$key];
                    $fArray[$key] = '';
                }
            }
            $remote==="autopic" && $autopic[$key] = $value;
        }
        if($remote==="autopic"){
            return $autopic;
        }
        if($array && $fArray){
            krsort($array);
            krsort($fArray);
            $content = str_replace($array, $fArray, $content);
        }
        return addslashes($content);
    }
}
