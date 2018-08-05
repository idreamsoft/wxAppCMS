<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
define('iPHP_WAF_CSRF', true);

class editorAdmincp{
    public function __construct() {
		iFS::$ERROR_TYPE   = 'json';
    }
    public function do_config(){
        $upload_max_filesize = get_cfg_var('upload_max_filesize');
        $MaxSize = get_bytes($upload_max_filesize);
        empty($MaxSize) && $MaxSize = 2097152;
    	$config_json ='
/* 前后端通信相关的配置,注释只允许使用多行方式 */
{
    /* 上传图片配置项 */
    "imageActionName": "uploadimage", /* 执行上传图片的action名称 */
    "imageFieldName": "upfile", /* 提交的图片表单名称 */
    "imageMaxSize": '.$MaxSize.', /* 上传大小限制，单位B */
    "imageAllowFiles": [".'.implode('", ".', files::$IMG_EXT).'"], /* 上传图片格式显示 */
    "imageCompressEnable": true, /* 是否压缩图片,默认是true */
    "imageCompressBorder": 1600, /* 图片压缩最长边限制 */
    "imageInsertAlign": "none", /* 插入的图片浮动方式 */
    "imageUrlPrefix": "", /* 图片访问路径前缀 */
    "imagePathFormat": "",

    /* 涂鸦图片上传配置项 */
    "scrawlActionName": "uploadscrawl", /* 执行上传涂鸦的action名称 */
    "scrawlFieldName": "upfile", /* 提交的图片表单名称 */
    "scrawlPathFormat": "",
    "scrawlMaxSize": '.$MaxSize.', /* 上传大小限制，单位B */
    "scrawlUrlPrefix": "", /* 图片访问路径前缀 */
    "scrawlInsertAlign": "none",

    /* 截图工具上传 */
    "snapscreenActionName": "uploadimage", /* 执行上传截图的action名称 */
    "snapscreenPathFormat": "",
    "snapscreenUrlPrefix": "", /* 图片访问路径前缀 */
    "snapscreenInsertAlign": "none", /* 插入的图片浮动方式 */

    /* 抓取远程图片配置 */
    "catcherLocalDomain": ["127.0.0.1", "localhost"],
    "catcherActionName": "catchimage", /* 执行抓取远程图片的action名称 */
    "catcherFieldName": "source", /* 提交的图片列表表单名称 */
    "catcherPathFormat": "",
    "catcherUrlPrefix": "", /* 图片访问路径前缀 */
    "catcherMaxSize": '.$MaxSize.', /* 上传大小限制，单位B */
    "catcherAllowFiles": [".'.implode('", ".', files::$IMG_EXT).'"], /* 抓取图片格式显示 */

    /* 上传视频配置 */
    "videoActionName": "uploadvideo", /* 执行上传视频的action名称 */
    "videoFieldName": "upfile", /* 提交的视频表单名称 */
    "videoPathFormat": "",
    "videoUrlPrefix": "", /* 视频访问路径前缀 */
    "videoMaxSize": '.$MaxSize.', /* 上传大小限制，单位B */
    "videoAllowFiles": [
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"], /* 上传视频格式显示 */

    /* 上传文件配置 */
    "fileActionName": "uploadfile", /* controller里,执行上传视频的action名称 */
    "fileFieldName": "upfile", /* 提交的文件表单名称 */
    "filePathFormat": "",
    "fileUrlPrefix": "", /* 文件访问路径前缀 */
    "fileMaxSize": '.$MaxSize.', /* 上传大小限制，单位B */
    "fileAllowFiles": [
        ".'.implode('", ".', files::$IMG_EXT).'",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ], /* 上传文件格式显示 */

    /* 列出指定目录下的图片 */
    "imageManagerActionName": "imageManager", /* 执行图片管理的action名称 */
    "imageManagerListPath": "", /* 指定要列出图片的目录 */
    "imageManagerListSize": 20, /* 每次列出文件数量 */
    "imageManagerUrlPrefix": "", /* 图片访问路径前缀 */
    "imageManagerInsertAlign": "none", /* 插入的图片浮动方式 */
    "imageManagerAllowFiles": [".'.implode('", ".', files::$IMG_EXT).'"], /* 列出的文件类型 */

    /* 列出指定目录下的文件 */
    "fileManagerActionName": "fileManager", /* 执行文件管理的action名称 */
    "fileManagerListPath": "", /* 指定要列出文件的目录 */
    "fileManagerUrlPrefix": "", /* 文件访问路径前缀 */
    "fileManagerListSize": 20, /* 每次列出文件数量 */
    "fileManagerAllowFiles": [
        ".'.implode('", ".', files::$IMG_EXT).'",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ] /* 列出的文件类型 */
}
    	';
        $result = preg_replace("/\/\*[\s\S]+?\*\//", "", $config_json, true);

        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
    /**
     * [编辑器图片管理]
     * @return [type] [description]
     */
    public function do_imageManager(){
		$res = iFS::folder(iCMS::$config['FS']['dir'],files::$IMG_EXT);
		$res['public'] = iCMS_PUBLIC_URL;
		iUI::json($res);
    }
    /**
     * [编辑器文件管理]
     * @return [type] [description]
     */
    public function do_fileManager(){
        $res = iFS::folder(iCMS::$config['FS']['dir']);
        $res['public'] = iCMS_PUBLIC_URL;
        iUI::json($res);
    }
    /**
     * [编辑器抓取远程图片]
     * @return [type] [description]
     */
    public function do_catchimage(){
    	$url_array = (array)$_POST['source'];
		/* 抓取远程图片 */
        $list = array();
        $uri  = parse_url(iCMS_FS_URL);
		foreach ($url_array as $_k => $imgurl) {
            if (stripos($imgurl,$uri['host']) !== false){
				unset($_array[$_k]);
			}

            $F = iFS::http($imgurl,'array');
            if($F===false){
                $a = iFS::$ERROR;
            }else{
                $F['path'] && $url = iFS::fp($F['path'],'+http');
                $a = array(
                    "state"    => 'SUCCESS',
                    "url"      => $url,
                    "size"     => $F["size"],
                    "title"    => iSecurity::escapeStr($info["title"]),
                    "original" => iSecurity::escapeStr($F["oname"]),
                    "source"   => iSecurity::escapeStr($imgurl)
                );
            };
		    array_push($list,$a);
		}
		/* 返回抓取数据 */
		iUI::json(array(
			'code'  => count($list) ? '1':'0',
			'state' => count($list) ? 'SUCCESS':'ERROR',
			'list'  => $list
		));
    }
    /**
     * [编辑器上传图片]
     * @return [type] [description]
     */
    public function do_uploadimage(){
        $F = iFS::upload('upfile');
        $F===false && exit(iFS::$ERROR);
    	$F['path'] && $url = iFS::fp($F['path'],'+http');
		iUI::json(array(
			'title'    => iSecurity::escapeStr($_POST['pictitle']),
			'original' => $F['oname'],
			'url'      => $url,
			'code'     => $F['code'],
			'state'    => 'SUCCESS'
		));
    }
    /**
     * [markdown上传图片]
     * @return [type] [description]
     */
    public function do_md_uploadimage(){
        $F = iFS::upload('editormd-image-file');
        $F===false && iUI::json(array(
            'message'  => iFS::$ERROR,
            'success'  => '0'
        ));
        $F['path'] && $url = iFS::fp($F['path'],'+http');
        iUI::json(array(
            'url'      => $url,
            // 'message'  => '上传成功',
            'success'  => 1
        ));
    }
    /**
     * [编辑器上传文件]
     * @return [type] [description]
     */
    public function do_uploadfile(){
        $F = iFS::upload('upfile');
        $F===false && exit(iFS::$ERROR);
		$F['path'] && $url	= iFS::fp($F['path'],'+http');
    	iUI::json(array(
            "url"      =>$url,
            "path"     =>$F["path"],
            "fid"      =>$F["fid"],
            "ext"      =>$F["ext"],
            "original" =>$F["oname"],
            "state"    =>'SUCCESS'
		));
    }
    /**
     * [编辑器上传视频]
     * @return [type] [description]
     */
    public function do_uploadvideo(){
        $F = iFS::upload('upfile');
        $F===false && exit(iFS::$ERROR);
        $F['path'] && $url  = iFS::fp($F['path'],'+http');
        iUI::json(array(
            "url"      =>$url,
            "fileType" =>$F["ext"],
            "original" =>$F["oname"],
            "state"    =>'SUCCESS'
        ));
    }
    /**
     * [编辑器上传涂鸦]
     * @return [type] [description]
     */
    public function do_uploadscrawl(){
		if ($_GET[ "action" ] == "tmpImg") { // 背景上传
            iFS::$ERROR_TYPE  = false;
            $F = iFS::upload('upfile','scrawl/tmp');
            $F===false && exit();
			$F['path'] && $url	= iFS::fp($F['path'],'+http');
			echo "<script>parent.ue_callback('" .$url. "','SUCCESS')</script>";
		} else {
            iFS::$ERROR_TYPE  = true;
            $F = iFS::base64ToFile($_POST['upfile'],'scrawl/'.get_date(0,'Y/md'));
            $F===false && exit(iFS::$ERROR);
			$F['path'] && $url	= iFS::fp($F['path'],'+http');
			$tmp 	= iFS::get_dir()."scrawl/tmp/";
			iFS::rmdir($tmp);
	    	iUI::json(array(
				"url"   =>$url,
				"state" =>'SUCCESS'
			));
		}
    }
    public static function ueditor_script($id,$config=array()){
        $app = $config['app']['app'];
        empty($app) && $app = 'content';
        ob_start();
        include admincp::view("ueditor.script","editor");
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
    public static function markdown_script($id,$config=array()){
        ob_start();
        include admincp::view("markdown.script","editor");
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

}
