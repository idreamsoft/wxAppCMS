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

class patchAdmincp{

	public function __construct() {
		$this->msg   = "";
		if(isset($_GET['git'])){
			patch::$release = $_GET['release'];
			patch::$zipName = $_GET['zipname'];
			// patch::$test = true;
		}else{
			$this->patch = patch::init(isset($_GET['force'])?true:false);
		}
	}
    /**
     * [升级检查]
     */
    public function do_check(){
		if(empty($this->patch)){
			if($_GET['ajax']){
				iUI::json(array('code'=>0));
			}else{
				iUI::success("您使用的 iCMS 版本,目前是最新版本<hr />当前版本：iCMS ".iCMS_VERSION." [".iCMS_RELEASE."]",0,"5");
			}
		}else{
	    	switch(iCMS::$config['system']['patch']){
	    		case "1"://自动下载,安装时询问
					$this->msg = patch::download($this->patch[1]);
					$json      = array(
						'code' => "1",
						'url'  => __ADMINCP__.'=patch&do=install',
						'msg'  => "发现iCMS最新版本<br /><span class='label label-warning'>iCMS ".$this->patch[0]." [".$this->patch[1]."]</span><br />".$this->patch[3]."<hr />您当前使用的版本<br /><span class='label label-info'>iCMS ".iCMS_VERSION." [".iCMS_RELEASE."]</span><br /><br />新版本已经下载完成!! 是否现在更新?",
		    		);
	    		break;
	    		case "2"://不自动下载更新,有更新时提示
		    		$json	= array(
						'code' => "2",
						'url'  => __ADMINCP__.'=patch&do=download',
						'msg'  => "发现iCMS最新版本<br /><span class='label label-warning'>iCMS ".$this->patch[0]." [".$this->patch[1]."]</span><br />".$this->patch[3]."<hr />您当前使用的版本<br /><span class='label label-info'>iCMS ".iCMS_VERSION." [".iCMS_RELEASE."]</span><br /><br />请马上更新您的iCMS!!!",
		    		);
	    		break;
	    	}
	    	if($_GET['ajax']){
	    		iUI::json($json,true);
	    	}
		    $moreBtn=array(
		            array("text"=>"马上更新","url"=>$json['url']),
		            array("text"=>"以后在说","js" =>'return true'),
		    );
    		iUI::dialog('success:#:check:#:'.$json['msg'],0,30,$moreBtn);
		}
    }
    /**
     * [下载升级包]
     */
    public function do_download(){
		$this->msg = patch::download();//下载文件包
		include admincp::view("patch");
    }
    /**
     * [安装升级包]
     */
    public function do_install(){
        patch::setTime();
        $this->msg  = patch::update();//更新文件
        $is_upgrade = patch::$upgrade;
		include admincp::view("patch");
    }
    public function do_upgrade(){
        $this->msg  = patch::run();//升级
        $is_upgrade = patch::$upgrade;
        include admincp::view("patch");
    }
    //===================git=========
    /**
     * [开发版升级检查]
     */
    public function do_git_check(){
    	$log =  patch::git('log');
    	include admincp::view("git.log");
    }
    /**
     * [下载开发版升级包]
     */
    public function do_git_download(){
    	$zip_url = patch::git('zip',null,'url');
		$release = $_GET['release'];
		$zipName = str_replace(patch::PATCH_URL.'/', '', $zip_url);

		// patch::$release = $release;
		// patch::$zipName = $zipName;
		// $this->do_download();
		iPHP::redirect(APP_URI.'&do=download&release='.$release.'&zipname='.$zipName.'&git=true');
    }
    /**
     * [查看开发版信息]
     */
    public function do_git_show(){
    	$log =  patch::git('show');
        $type_map = array(
          'D'=>'删除',
          'A'=>'增加',
          'M'=>'更改'
        );
    	include admincp::view("git.show");
    }

    public static function check_update() {
        include admincp::view("check_update","patch");
    }
    /**
     * [检查版信息]
     */
    public static function do_version() {
        echo patch::version();
    }
    public static function check_version() {
        include admincp::view("check_version","patch");
    }
}
