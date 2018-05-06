<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class spider{
    public static $cid      = null;
    public static $rid      = null;
    public static $pid      = null;
    public static $sid      = null;
    public static $poid     = null;
    public static $title    = null;
    public static $url      = null;
    public static $work     = false;
    public static $urlslast = null;
    public static $allHtml  = array();

	public static $dataTest = false;
	public static $ruleTest = false;

	public static $content_right_code = false;
	public static $content_error_code = false;

	public static $referer     = null;
	public static $encoding    = null;
	public static $useragent   = null;
	public static $cookie      = null;
	public static $charset     = null;
	public static $curl_proxy  = false;
    public static $proxy_array = array();
    public static $PROXY_URL = false;
    public static $callback = array();

    public static $spider_url_ids = array();

    public static function rule($id) {
        $rs = iDB::row("SELECT * FROM `#iCMS@__spider_rule` WHERE `id`='$id' LIMIT 1;", ARRAY_A);
        $rs['rule'] && $rs['rule'] = stripslashes_deep(unserialize($rs['rule']));
        $rs['rule']['user_agent'] OR $rs['rule']['user_agent'] = "Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)";
        spider::$useragent = $rs['rule']['user_agent'];
        spider::$encoding  = $rs['rule']['curl']['encoding'];
        spider::$referer   = $rs['rule']['curl']['referer'];
        spider::$cookie    = $rs['rule']['curl']['cookie'];
        spider::$charset   = $rs['rule']['charset'];
        return $rs;
    }

    public static function project($id) {
        return iDB::row("SELECT * FROM `#iCMS@__spider_project` WHERE `id`='$id' LIMIT 1;", ARRAY_A);
    }
    public static function postArgs($id) {
        $postRs = iDB::row("SELECT * FROM `#iCMS@__spider_post` WHERE `id`='$id' LIMIT 1;");
        if ($postRs->post) {
            $postArray = explode("\n", $postRs->post);
            $postArray = array_filter($postArray);
            foreach ($postArray AS $key => $pstr) {
                list($pkey, $pval) = explode("=", $pstr);
                if(strpos($pkey, '[')!==false && strpos($pkey, ']')!==false){
                    preg_match('/(.+)\[(.+)\]/', $pkey,$match);
                    $_POST[$match[1]][$match[2]] = trim($pval);
                }else{
                    $_POST[$pkey] = trim($pval);
                }
            }
            return $postRs;
        }
    }

    public static function checker($work = null,$pid=null,$url=null,$title=null){
        $pid   ===null && $pid = spider::$pid;
        $url   ===null && $url = spider::$url;
        $title ===null && $title = spider::$title;
        $project = spider::project($pid);
        $hash    = md5($url);
        if(($project['checker'] && empty($_GET['indexid'])) || $work=="DATA@RULE"){
            $title = addslashes($title);
            $url   = addslashes($url);
            $project_checker = $project['checker'];
            $work=="DATA@RULE" && $project_checker = '1';
            switch ($project_checker) {
                case '1'://按网址检查
                    $sql   = "`url` = '$url'";
                    $label = $url.PHP_EOL;
                    $msg   = $label.'该网址的文章已经发布过!请检查是否重复';
                break;
                case '2'://按标题检查
                    $sql   = "`title` = '$title'";
                    $label = $title.PHP_EOL;
                    $msg   = $label.'该标题的文章已经发布过!请检查是否重复';
                break;
                case '3'://网址和标题
                    $sql   = "`url` = '$url' AND `title` = '$title'";
                    $label = $title.PHP_EOL.$url;
                    $msg   = $label.'该网址和标题的文章已经发布过!请检查是否重复';
                break;
            }
            switch ($project['self']) {
                case '1':
                    $sql.=" AND `pid`='".$pid."'";
                break;
                case '2':
                    $sql.=" AND `rid`='".spider::$rid."'";
                break;
            }

            $checker = iDB::value("SELECT `id` FROM `#iCMS@__spider_url` where $sql AND `publish` in(1,2)");
            if($checker){
                $work===NULL && iUI::alert($msg, 'js:parent.$("#' . $hash . '").remove();');
                if($work=='shell'){
                    echo "\n\033[35m".$msg."\033[0m\n\n";
                    return false;
                }
                if($work=="WEB@AUTO"){
                    return '-1';
                }
                return false;
            }else{
                return true;
            }
        }
        return true;
    }

    public static function update_spider_url_indexid($suid,$indexid){
        iDB::update('spider_url',array(
            //'publish' => '1',
            'indexid' => $indexid,
            //'pubdate' => time()
        ),array('id'=>$suid));
        self::update_spider_url_ids($indexid);
    }

    public static function update_spider_url_publish($suid){
        iDB::update('spider_url',array(
            'publish' => '1',
            'pubdate' => time()
        ),array('id'=>$suid));
        self::update_spider_url_ids();
    }

    public static function update_spider_url_ids($indexid=0){
        foreach ((array)spider::$spider_url_ids as $key => $suid) {
            if($indexid){
                $data = array(
                    'indexid' => $indexid
                );
            }else{
                $data = array(
                    'pid'     => spider::$pid,
                    'publish' => '1',
                    'status'  => '1',
                    'pubdate' => time()
                );
            }
            iDB::update('spider_url',$data,array('id'=>$suid));
        }
    }
    public static function errorlog($msg,$url=null,$type=0,$a=null) {
        $data = array(
            'work'    => spider::$work,
            'rid'     => (int)spider::$rid,
            'sid'     => (int)spider::$sid,
            'pid'     => (int)spider::$pid,
            'url'     => ($url?$url:spider::$url),
            'msg'     => addslashes($msg),
            'date'    => date("Y-m-d"),
            'addtime' => time(),
            'type'    => $type
        );
        $a && $data = array_merge($data,(array)$a);
        iDB::insert('spider_error',$data);
        return $msg;
    }
    public static function publish($work = null) {
        @set_time_limit(0);
        $_POST = spider_data::crawl();

        spider_tools::listItemCache($_POST['reurl'],'delete');

        foreach ((array)$_POST as $key => $value) {
            if($value===null && $key!='__title__'){
                echo spider::errorlog("publish:$key:null\n",$_POST['reurl'],"publish:$key:null");
                return null;
            }
        }
        if($_POST===false){
            return false;
        }
        if(spider::$work && $work===null) $work = spider::$work;

        // if($work=='shell'){
        //    if(empty($_POST['title'])){
        //         echo spider::errorlog("标题不能为空\n",$_POST['reurl'],'publish.title');
        //         return false;
        //    }
        //    if(empty($_POST['body'])){
        //         echo spider::errorlog("内容不能为空\n",$_POST['reurl'],'publish.body');
        //         return false;
        //    }
        // }
        $checker = spider::checker($work,spider::$pid,$_POST['reurl'],$_POST['title']);
        if($checker!==true){
            return $checker;
        }

        $project = spider::project(spider::$pid);

        if(!isset($_POST['cid'])){
            $_POST['cid'] = $project['cid'];
        }

        $poid = $project['poid'];
        spider::$poid && $poid = spider::$poid;
        $postArgs = spider::postArgs($poid);
        $appid = $_POST['appid']?:$postArgs->app;
        $app = apps::get_app($appid);

        if($_GET['indexid']){
            self::get_data_id((int)$_GET['indexid'],$app);
        }

        $title = addslashes($_POST['title']);
        $url   = addslashes($_POST['reurl']);
        $hash  = md5($url);
        if(empty(spider::$sid)){
            $spider_url = iDB::row("SELECT `id`,`publish`,`indexid` FROM `#iCMS@__spider_url` where `url`='$url'",ARRAY_A);
            if(empty($spider_url)){
                $spider_url_data = array(
                    'appid'   => $app['id'],
                    'cid'     => $project['cid'],
                    'rid'     => spider::$rid,
                    'pid'     => spider::$pid,
                    'title'   => $title,
                    'url'     => $url,
                    'hash'    => $hash,
                    'status'  => '1',
                    'addtime' => time(),
                    'publish' => '0',
                    'indexid' => '0',
                    'pubdate' => ''
                );
                $suid = iDB::insert('spider_url',$spider_url_data);
            }else{
                if($spider_url['indexid']){
                    self::get_data_id($spider_url['indexid'],$app);
                }
                $suid = $spider_url['id'];
            }
        }else{
            $suid = spider::$sid;
        }

        if (spider::$callback['post'] && is_callable(spider::$callback['post'])) {
            $_POST = call_user_func_array(spider::$callback['post'],array($_POST));
            if($_POST['callback']){
                return $_POST;
            }
        }

        iSecurity::_addslashes($_POST);
        $fun    = $postArgs->fun;
        $return = "1001";
        if(iFS::checkHttp($fun)){
            $json = self::postUrl($fun,$_POST);
            $callback = json_decode ($json,true);
            if($callback['code']==$return){
                $indexid = $callback['indexid'];
                self::update_spider_url_indexid($suid,$indexid);
                self::update_spider_url_publish($suid);
            }
        }else{
            $obj = $postArgs->app."Admincp";
            $acp = new $obj;
            $acp->callback['code'] = $return;
            /**
             * 主表 回调 更新关联ID
             */
            $acp->callback['primary'] = array(
                array('spider','update_spider_url_indexid'),
                array('suid'=>$suid)
            );
            /**
             * 数据表 回调 成功发布
             */
            $acp->callback['data'] = array(
                array('spider','update_spider_url_publish'),
                array('suid'=>$suid)
            );

            $callback = $acp->$fun();
            if(!$callback){
                echo spider::errorlog("发布失败\n",$_POST['reurl'],'publish.fail');
                return false;
            }
        }
        if ($callback['code'] == $return && $work===NULL) {
            if (spider::$sid) {
                iUI::success("发布成功!",'js:1');
            } else {
                iUI::success("发布成功!", 'js:parent.$("#' . $hash . '").remove();');
            }
        }
        if (spider::$callback['save'] && is_callable(spider::$callback['save'])) {
            $ret = call_user_func_array(spider::$callback['save'],array($callback,$_POST));
            if($ret['callback']){
                return $ret;
            }
        }
        if($work=="shell"||$work=="WEB@AUTO"){
            $callback['work']=$work;
            return $callback;
        }
    }
    public static function callback($obj,$indexid,$type = null) {
        if ($type === null || $type == 'primary') {
            if ($obj->callback['primary']) {
                $PCB = $obj->callback['primary'];
                $handler = $PCB[0];
                $params = (array) $PCB[1];
                $indexid && $params+= array('indexid' => $indexid);

                $obj->callback['return'] = array(
                    "code" => $obj->callback['code']
                )+$params;
                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                }
            }
        }
        if ($type === null || $type == 'data') {
            if ($obj->callback['data']) {
                $DCB = $obj->callback['data'];
                $handler = $DCB[0];
                $params = (array) $DCB[1];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                }
            }
        }
    }
    public static function get_data_id($indexid,$app) {
        $data_table = apps_mod::get_data_table($app['table']);
        if($data_table){
            $data_id_key = $data_table['primary'];
            $union_key   = $data_table['union'];
            $table_name  = $data_table['name'];
            if($indexid){
                $_POST[$union_key]   = $indexid;
                $_POST[$data_id_key] = iDB::value("SELECT `{$data_id_key}` FROM `#iCMS@__{$table_name}` WHERE `{$union_key}`='{$indexid}'");
            }
        }else{
            if($app['app']=='article' && $indexid){
                $_POST['article_id']  = $indexid;
                $_POST['data_id'] = iDB::value("SELECT `id` FROM `#iCMS@__article_data` WHERE aid='".$indexid."'");
            }
        }
    }
    public static function postUrl($url, $data) {
        is_array($data) && $data = http_build_query($data);
        $options = array(
            CURLOPT_URL                  => $url,
            CURLOPT_REFERER              => $_SERVER['HTTP_REFERER'],
            CURLOPT_USERAGENT            => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_POSTFIELDS           => $data,
            // CURLOPT_HTTPHEADER           => array(
            //     'Content-Type:application/x-www-form-urlencoded',
            //     'Content-Length:'.strlen($data),
            //     'Host: www.icmsdev.com'
            // ),
            CURLOPT_POST                 => 1,
            CURLOPT_TIMEOUT              => 10,
            CURLOPT_CONNECTTIMEOUT       => 10,
            CURLOPT_RETURNTRANSFER       => 1,
            CURLOPT_FAILONERROR          => 1,
            CURLOPT_HEADER               => false,
            CURLOPT_NOBODY               => false,
            CURLOPT_NOSIGNAL             => true,
            // CURLOPT_DNS_USE_GLOBAL_CACHE => true,
            // CURLOPT_DNS_CACHE_TIMEOUT    => 86400,
            CURLOPT_SSL_VERIFYPEER       => false,
            CURLOPT_SSL_VERIFYHOST       => false
        );

        $ch = curl_init();
        curl_setopt_array($ch,$options);
        $responses = curl_exec($ch);
        curl_close ($ch);
        return $responses;
    }
}
