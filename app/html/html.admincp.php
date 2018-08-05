<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class htmlAdmincp{
    public function __construct() {
		iView::$gateway = "html";
		$this->page      = $GLOBALS['page'];
		$this->PG        = $_POST?$_POST:$_GET;
		$this->CP        = iCMS::$config['router']['speed'];
		$this->alltime   = $_GET['alltime']?$_GET['alltime']:0;
    }
    /**
     * [全站静态]
     * @return [type] [description]
     */
    public function do_all(){
    	include admincp::view("html.all");
    }
    /**
     * [首页静态]
     * @return [type] [description]
     */
    public function do_index(){
    	include admincp::view("html.index");
    }
    /**
     * [生成首页静态]
     * @return [type] [description]
     */
    public function do_createIndex(){
        $indexTPL  = iCMS::$config['template']['index']['tpl']	= $this->PG['indexTPL'];
        $indexName = iCMS::$config['template']['index']['name']  = $this->PG['indexName'];
        $ext = iFS::get_ext($indexName);
        if($ext) iFS::allow_files($ext) OR iUI::alert("文件名不合法!");
    	iFS::check_ext($indexName.iCMS::$config['router']['ext']) OR iUI::alert('文件类型不合法!');
		configAdmincp::update('template');
    	$this->CreateIndex($indexTPL,$indexName);
    }

    public function CreateIndex($indexTPL,$indexName,$p=1,$loop=1){

		$_GET['loop']	&& $loop=0;
		$GLOBALS['page']	= $p+$this->page;
		$query['indexTPL']	= $indexTPL;
		$query['indexName']	= $indexName;

		$htm	= iCMS::run('index','iCMS',array(array($indexTPL,$indexName)));
		$fpath	= iURL::page_num($htm[1]['pagepath']);
		$total	= $GLOBALS['iPage']['total'];
		iFS::check_ext($fpath) OR iUI::alert("文件类型不合法,禁止生成!<hr />请更改系统设置->网站URL->文件后缀");
		iFS::mkdir($htm[1]['dir']);
		iFS::write($fpath,$htm[0]);
		$_total = $total?$total:"1";
		$msg    = "共<span class='label label-info'>{$_total}</span>页 已生成<span class='label label-info'>".$GLOBALS['page']."</span>页,";

//		$surplus		= ceil($total-$p);
		if($loop<$this->CP && $GLOBALS['page']<$total) {
			$loop++;
			$p++;
			$this->CreateIndex($indexTPL,$indexName,$p,$loop);
		}
		$looptimes = ($total-$GLOBALS['page'])/$this->CP;
		$use_time  = iPHP::timer_stop();
		$msg.="用时<span class='label label-info'>{$use_time}</span>秒";
		$query["alltime"] = $this->alltime+$use_time;
		$loopurl = $this->loopurl($total,$query);
		if($loopurl){
			$moreBtn = array(
				array("id"=>"btn_stop","text"=>"停止","url"=>APP_URI."&do=index"),
				array("id"=>"btn_next","text"=>"继续","src"=>$loopurl,"next"=>true)
	        );
			$dtime    = 1;
			$all_time = $looptimes*$use_time+$looptimes+1;
			$msg.="<hr />预计全部生成还需要<span class='label label-info'>{$all_time}</span>秒";
        }else{
			$moreBtn = array(
				array("id"=>"btn_next","text"=>"完成","url"=>APP_URI."&do=index")
	        );
			$dtime = 5;
	        $msg.="<hr />已全部生成完成<hr />总共用时<span class='label label-info'>".$query["alltime"]."</span>秒";
        }
		$updateMsg = $this->page?'FRAME':false;
		iUI::dialog($msg,$loopurl?"src:".$loopurl:'',$dtime,$moreBtn,$updateMsg);
    }
    /**
     * [栏目静态]
     * @return [type] [description]
     */
    public function do_category(){
        category::$appid = iCMS_APP_ARTICLE;
    	include admincp::view("html.category");
    }
    /**
     * [生成栏目静态]
     * @return [type] [description]
     */
    public function do_createCategory($cid=0,$p=1,$loop=1){
		$category	= $this->PG['cid'];
		$rootid		= $this->PG['rootid'];
		$k			= (int)$this->PG['k'];
		if($k>0||empty($category)){
			$category = iCache::get('html/category');
		}
		if(empty($category)){
			iUI::alert('请选择需要生成静态的栏目!');
		}
		$category[0]=='all' && $category = category::get_cid();

		$k===0 && iCache::set('html/category',$category,0);

		$_GET['loop'] && $loop=0;
		$GLOBALS['page'] = $p+$this->page;

		$len = count($category)-1;
		$cid = $category[$k];

		$htm = iCMS::run('category','category',$cid,null);
		$htm OR iUI::alert("栏目[cid:$cid] URL规则设置问题! 此栏目不能生成静态");
		$fpath = iURL::page_num($htm[1]['iurl']['pagepath']);
		$total = $GLOBALS['iPage']['total'];
		iFS::check_ext($fpath) OR iUI::alert("文件类型不合法,禁止生成!<hr />请更改栏目->URL规则设置->栏目规则");
		iFS::mkdir($htm[1]['iurl']['dir']);
		iFS::write($fpath,$htm[0]);
		$_total = $total?$total:"1";
		$name   = $htm[1]['name'];
		$msg    = "<span class='label label-success'>{$name}</span>栏目,共<span class='label label-info'>{$_total}</span>页 已生成<span class='label label-info'>".$GLOBALS['page']."</span>页,";
//		$surplus		= ceil($total-$p);
		if($loop<$this->CP && $GLOBALS['page']<$total) {
			$loop++;
			$p++;
			$this->do_createCategory($cid,$p,$loop);
		}
		$looptimes = ($total-$GLOBALS['page'])/$this->CP;
		$use_time  = iPHP::timer_stop();
		$msg.="用时<span class='label label-info'>{$use_time}</span>秒";
		//$query["alltime"] =
		$query["alltime"] = $this->alltime+$use_time;
		$loopurl = $this->loopurl($total,$query);
		// print_r($loopurl);
		// exit;
		if($loopurl){
			$moreBtn = array(
				array("id"=>"btn_stop","text"=>"停止","url"=>APP_URI."&do=category"),
				array("id"=>"btn_next","text"=>"继续","src"=>$loopurl,"next"=>true)
	        );
			$dtime    = 1;
			$all_time = $looptimes*$use_time+$looptimes+1;
			$msg.="<hr /><span class='label label-success'>{$name}</span>栏目,预计全部生成还需要<span class='label label-info'>{$all_time}</span>秒";
        }else{
			$moreBtn = array(
				array("id"=>"btn_next","text"=>"完成","url"=>APP_URI."&do=category")
	        );
			$dtime = 3;
	        $msg.="<hr /><span class='label label-success'>{$name}</span>栏目,已全部生成完成.总共用时<span class='label label-info'>".$query["alltime"]."</span>秒";
        	if($k<$len){
				$query["k"]       = $k+1;
				$query["alltime"] = 0;
				$GLOBALS['page']  = 0;

				$loopurl = $this->loopurl(1,$query);
		        $msg.="<hr />准备开始生成下一个栏目";
				$moreBtn = array(
					array("id"=>"btn_stop","text"=>"停止","url"=>APP_URI."&do=category"),
					array("id"=>"btn_next","text"=>"继续","src"=>$loopurl,"next"=>true)
		        );
				$dtime = 1;
        	}elseif($k==$len){
        		$msg.="<hr />所有栏目生成完成";
        	}
			$k>0 && $updateMsg	= 'FRAME';
        }
        if($k==0){
			$updateMsg = $this->page?'FRAME':false;
		}
		iUI::dialog($msg,$loopurl?"src:".$loopurl:"",$dtime,$moreBtn,$updateMsg);
    }
    /**
     * [文章静态]
     * @return [type] [description]
     */
    public function do_article(){
        category::$appid = iCMS_APP_ARTICLE;
        list($orderby,$orderby_option) = get_orderby(array(
            'id'         =>"ID",
            'hits'       =>"点击",
            'hits_week'  =>"周点击",
            'hits_month' =>"月点击",
            'good'       =>"顶",
            'postime'    =>"时间",
            'pubdate'    =>"发布时间",
            'comments'   =>"评论数",
        ));
    	include admincp::view("html.article");
    }
    /**
     * [生成文章静态]
     * @return [type] [description]
     */
    public function do_createArticle($aid=null){
		$category = $this->PG['cid'];
		$startime = $this->PG['startime'];
		$endtime  = $this->PG['endtime'];
		$startid  = $this->PG['startid'];
		$endid    = $this->PG['endid'];
		$perpage  = (int)$this->PG['perpage'];
		$offset   = (int)$this->PG['offset'];
		$whereSQL = "WHERE `status` ='1'";
    	$aid===null && $aid = $this->PG['aid'];
		if($aid){
			$title	= self::Article($aid);
			iUI::success($title.'<hr />生成静态完成!');
		}
        if($category[0]=='all'){
            category::$appid = iCMS_APP_ARTICLE;
            $category = category::get_cid();
        }

		if($category){
			$cids	= implode(',',(array)$category);
			$whereSQL.= " AND `cid` IN({$cids})";
		}
		$startime&& $whereSQL.=" AND `pubdate`>=UNIX_TIMESTAMP('{$startime} 00:00:00')";
		$endtime && $whereSQL.=" AND `pubdate`<=UNIX_TIMESTAMP('{$endtime} 23:59:59')";
		$startid && $whereSQL.=" AND `id`>='{$startid}'";
		$endid   && $whereSQL.=" AND `id`<='{$endid}'";
		$perpage OR $perpage = $this->CP;

        $_GET['orderby'] = $this->PG['orderby'];
        list($orderby,$orderby_option) = get_orderby(array(
            'id'         =>"ID",
            'hits'       =>"点击",
            'hits_week'  =>"周点击",
            'hits_month' =>"月点击",
            'good'       =>"顶",
            'postime'    =>"时间",
            'pubdate'    =>"发布时间",
            'comments'   =>"评论数",
        ));

		$total     = iPagination::totalCache("SELECT count(*) FROM `#iCMS@__article` {$whereSQL}","G");
		$looptimes = ceil($total/$perpage);
		$offset    = $this->page*$perpage;
		$rs        = iDB::all("SELECT `id` FROM `#iCMS@__article` {$whereSQL} order by {$orderby} LIMIT {$offset},{$perpage}");
		$_count    = count($rs);
		$msg       = "共<span class='label label-info'>{$total}</span>篇文章,将分成<span class='label label-info'>{$looptimes}</span>次完成<hr />开始执行第<span class='label label-info'>".($this->page+1)."</span>次生成,共<span class='label label-info'>{$_count}</span>篇<hr />";
        for($i=0;$i<$_count;$i++){
			self::Article($rs[$i]['id']);
			$msg.= '<span class="label label-success">'.$rs[$i]['id'].' <i class="fa fa-check"></i></span> ';
        }
        $GLOBALS['page']++;
		$use_time	= iPHP::timer_stop();
		$msg.="<hr />用时<span class='label label-info'>{$use_time}</span>秒";
		$query["total_num"]	= $total;
		$query["alltime"]	= $this->alltime+$use_time;
		$loopurl	= $this->loopurl($looptimes,$query);
		if($loopurl){
			$moreBtn	= array(
				array("id"=>"btn_stop","text"=>"停止","url"=>APP_URI."&do=article"),
				array("id"=>"btn_next","text"=>"继续","src"=>$loopurl,"next"=>true)
	        );
	        $dtime		= 1;
			$all_time	= $looptimes*$use_time+$looptimes+1;
			$msg.="<hr />预计全部生成还需要<span class='label label-info'>{$all_time}</span>秒";
        }else{
			$moreBtn	= array(
				array("id"=>"btn_next","text"=>"完成","url"=>APP_URI."&do=article")
	        );
	        $dtime		= 5;
	        $msg.="<hr />已全部生成完成<hr />总共用时<span class='label label-info'>".$query["alltime"]."</span>秒";
        }
		$updateMsg	= $this->page?'FRAME':false;
		iUI::dialog($msg,$loopurl?"src:".$loopurl:'',$dtime,$moreBtn,$updateMsg);
    }
    public function Article($id){
		$app   = iCMS::run('article','article','object');
		$htm   = $app->article($id);
		$htm OR iUI::alert("文章所属栏目URL规则设置问题! 此栏目下的文章不能生成静态,请修改栏目的访问模式和URL规则");
		$total = $htm[1]['page']['total'];
		$title = $htm[1]['title'];
		iFS::check_ext($htm[1]['iurl']['path']) OR iUI::alert("文件类型不合法,禁止生成!<hr />请更改栏目->URL规则设置->内容规则");
		iFS::mkdir($htm[1]['iurl']['dir']);
		iFS::write($htm[1]['iurl']['path'],$htm[0]);
		if($total>=2){
			for($ap=2;$ap<=$total;$ap++){
				$htm   = $app->article($id,$ap);
				$fpath = iURL::page_num($htm[1]['iurl']['pagepath'],$ap);
				iFS::write($fpath,$htm[0]);
			}
		}
		unset($app,$htm);
		return $title;
    }
    public function loopurl($total,$_query){
    	if ($total>0 && $GLOBALS['page']<$total){
    		//$p++;
			$url  = $_SERVER["REQUEST_URI"];
			$urlA = parse_url($url);

		    parse_str($urlA["query"], $query);
		    $query['page']		= $GLOBALS['page'];
		    $query 				= array_merge($query, (array)$_query);
		    $urlA["query"]		= http_build_query($query);
		    $url	= $urlA["path"].'?'.$urlA["query"];
		    return $url;
			//iPHP::redirect($url);
    	}
    }
}
