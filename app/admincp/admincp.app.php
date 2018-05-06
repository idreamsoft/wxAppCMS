<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class admincpApp{
    public function __construct() {
        menu::$callback['sidebar'] = array(__CLASS__,'__sidebar');
    }
    public static function get_seccode() {
        iSeccode::run();
        exit;
    }
    public static function check_seccode() {
        if ($_POST['captcha'] === iPHP_KEY) {
            return true;
        }

        if ($_POST['username'] && $_POST['password']) {
            $seccode = iSecurity::escapeStr($_POST['captcha']);
            iSeccode::check($seccode, true) OR iUI::code(0, 'iCMS:seccode:error', 'seccode', 'json');
        }
    }
    public static function access_log() {
        $access = array(
            'uid'       => members::$userid,
            'username'  => members::$nickname,
            'app'       => admincp::$APP_NAME,
            'ip'        => iPHP::get_ip(),
            'uri'       => iSecurity::escapeStr($_SERVER['REQUEST_URI']),
            'useragent' => iSecurity::escapeStr($_SERVER['HTTP_USER_AGENT']),
            'method'    => iSecurity::escapeStr($_SERVER['REQUEST_METHOD']),
            'referer'   => iSecurity::escapeStr($_SERVER['HTTP_REFERER']),
            'addtime'   => iSecurity::escapeStr($_SERVER['REQUEST_TIME']),
        );
        iDB::insert("access_log",$access);
    }
    public static function __sidebar($menu){
        $history   = menu::history(null,true);
        $caption   = menu::get_caption();
        foreach ($history as $key => $url) {
            $uri   =  str_replace(__ADMINCP__.'=', '', $url);
            $title = $caption[$uri];
            $title && $nav.= '<li><a href="'.$url.'"><i class="fa fa-link"></i> <span>'.$title.'</span></a></li>';
        }
        return $nav;
    }
    /**
     * [退出登陆]
     * @return [type] [description]
     */
    public function do_logout(){
   	    members::logout();
    	iUI::success('注销成功!','url:'.iPHP_SELF);
    }
    /**
     * [操作记录]
     * @return [type] [description]
     */
    public function do_access_log(){
        $sql = "WHERE 1=1";
        if($_GET['keywords']) {
            $sql.=" AND CONCAT(username,app,uri,useragent,ip,method,referer) REGEXP '{$_GET['keywords']}'";
        }
        $_GET['cid'] && $sql.=" AND `uid` = '{$_GET['uid']}'";
        $_GET['sapp'] && $sql.=" AND `app` = '{$_GET['sapp']}'";
        $_GET['ip'] && $sql.=" AND `ip` = '{$_GET['ip']}'";

        list($orderby,$orderby_option) = get_orderby();
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__access_log` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"条记录");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__access_log` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
        include admincp::view("admincp.access");
    }
    public function do_iCMS(){
        //数据统计
        $rs=iDB::all("SHOW FULL TABLES FROM `".iPHP_DB_NAME."` WHERE table_type = 'BASE TABLE';");
        foreach($rs as $k=>$val) {
            if(strstr(iPHP_DB_PREFIX, $val['Tables_in_'.iPHP_DB_NAME])===false) {
                $iTable[]=strtoupper($val['Tables_in_'.iPHP_DB_NAME]);
            }else {
                $oTable[]=$val['Tables_in_'.iPHP_DB_NAME];
            }
        }
        $content_datasize = 0;
        $tables = iDB::all("SHOW TABLE STATUS");
        $_count	= count($tables);
        for ($i=0;$i<$_count;$i++) {
            $tableName	= strtoupper($tables[$i]['Name']);
            if(in_array($tableName,$iTable)) {
                $datasize += $tables[$i]['Data_length'];
                $indexsize += $tables[$i]['Index_length'];
                if (stristr(strtoupper(iPHP_DB_PREFIX."article,".iPHP_DB_PREFIX."category,".iPHP_DB_PREFIX."comment,".iPHP_DB_PREFIX."article_data"),$tableName)) {
                    $content_datasize += $tables[$i]['Data_length']+$tables[$i]['Index_length'];
                }
            }
        }
    	include admincp::view("admincp.index");
    }
    public function do_count(){
        $counts = array();
        $counts['acc'] = iPHP::callback(array("categoryAdmincp",  "_count"),array(array('appid'=>iCMS_APP_ARTICLE)));
        $counts['tcc'] = iPHP::callback(array("categoryAdmincp",  "_count"),array(array('appid'=>iCMS_APP_TAG)));
        $counts['apc'] = iPHP::callback(array("appsAdmincp",      "_count"));
        $counts['uc']  = iPHP::callback(array("userAdmincp",      "_count"));

        $counts['lc']  = iPHP::callback(array("linksAdmincp",     "_count"));

        $counts['tc']  = iPHP::callback(array("tagAdmincp",       "_count"));
        $counts['cc']  = iPHP::callback(array("commentAdmincp",   "_count"));
        $counts['kc']  = iPHP::callback(array("keywordsAdmincp",  "_count"));
        $counts['pc']  = iPHP::callback(array("propAdmincp",      "_count"));

        $counts['fc']  = iPHP::callback(array("filesAdmincp",     "_count"));
        if($_GET['a']=='article'||$_GET['a']=='all'){
            $_GET['a']=='all' OR $counts = array();
            $counts['ac']  = iPHP::callback(array("articleAdmincp",   "_count"));
            $counts['ac0'] = iPHP::callback(array("articleAdmincp",   "_count"),array(array('status'=>'0')));
            $counts['ac2'] = iPHP::callback(array("articleAdmincp",   "_count"),array(array('status'=>'2')));
        }
        echo json_encode($counts);
    }
    public function do_version(){
        echo json_encode(array(
            'GIT_COMMIT'   => GIT_COMMIT,
            'GIT_AUTHOR'   => GIT_AUTHOR,
            'GIT_EMAIL'    => GIT_EMAIL,
            'GIT_TIME'     => GIT_TIME,
            'iCMS_VERSION' => iCMS_VERSION,
            'iCMS_RELEASE' => iCMS_RELEASE
        ));
    }
    // 检测函数支持
    public function isfun($fun = ''){
        if (!$fun || trim($fun) == '' || preg_match('~[^a-z0-9\_]+~i', $fun, $tmp)) return '错误';
        return iUI::check((false !== function_exists($fun)));
    }
    //检测PHP设置参数
    public function show($varName){
        switch($result = get_cfg_var($varName)){
            case 0:
                return iUI::check(0);
            break;
            case 1:
                return iUI::check(1);
            break;
            default:
                return $result;
            break;
        }
    }

}
