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

class spider_urls {
    public static $urls = null;

    public static function crawl($work = NULL,$pid = NULL,$_rid = NULL,$_urls=null,$callback=null) {
        @set_time_limit(0);
        $pid === NULL && $pid = spider::$pid;

        if ($pid) {
            $project = spider::project($pid);
            $cid = $project['cid'];
            $rid = $project['rid'];
            $prule_list_url = $project['list_url'];
            $lastupdate     = $project['lastupdate'];
        } else {
            $cid = spider::$cid;
            $rid = spider::$rid;
        }

        if($_rid !== NULL) $rid = $_rid;

        if($work=='shell'){
            $lastupdate = $project['lastupdate'];
            if($project['psleep']){
                if(time()-$lastupdate<$project['psleep']){
                    echo '采集方案['.$pid."]:".format_date($lastupdate)."刚采集过了,请".($project['psleep']/3600)."小时后在继续采集\n";
                    return;
                }
            }
            echo "\033[32m开始采集方案[".$pid."] 采集规则[".$rid."]\033[0m\n";
        }
        $ruleA = spider::rule($rid);
        $rule = $ruleA['rule'];
        $urls = $rule['list_urls'];
        $project['urls'] && $urls = $project['urls'];
        spider_urls::$urls && $urls = spider_urls::$urls;
        $_urls && $urls = $_urls;

        $urlsArray  = explode("\n", $urls);
        $urlsArray  = array_filter($urlsArray);
        $_urlsArray = $urlsArray;
        $urlsList   = array();
        if($work=='shell'){
            // echo "$urls\n";
            print_r($urlsArray);
        }

        foreach ($_urlsArray AS $_key => $_url) {
            $_url = trim($_url);
            if(empty($_url)){
                continue;
            }

            $_url      = htmlspecialchars_decode($_url);
            $_urlsList = array();
            /**
             * RULE@rid@url
             * url使用[rid]规则采集并返回列表结果
             */
            if(strpos($_url, 'RULE@')!==false){
                list($___s,$_rid,$_urls) = explode('@', $_url);
                if (spider::$ruleTest) {
                    print_r('<b>使用[rid:'.$_rid.']规则抓取列表</b>:'.$_urls);
                    echo "<hr />";
                }
                $_urlsList = spider_urls::crawl($work,false,$_rid,$_urls,'CALLBACK@URL');
                $urlsList  = array_merge($urlsList,$_urlsList);
                unset($urlsArray[$_key]);
            }else{
                preg_match('|.*<(.*)>.*|is',$_url, $_matches);

                if($_matches){
                    if(strpos($_matches[1], 'DATE:')!==false){
                        list($type,$format) = explode(':',$_matches[1]);
                        $urlsArray[$_key] = str_replace('<'.$_matches[1].'>', date($format),trim($_matches[0]));
                    }else{
                        list($format,$begin,$num,$step,$zeroize,$reverse) = explode(',',$_matches[1]);
                        $url = str_replace($_matches[1], '*',trim($_matches[0]));
                        $_urlsList = spider_tools::mkurls($url,$format,$begin,$num,$step,$zeroize,$reverse);
                        unset($urlsArray[$_key]);
                        $urlsList = array_merge($urlsList,$_urlsList);
                    }
                }
            }
        }
        $urlsList && $urlsArray = array_merge($urlsArray,$urlsList);
        unset($_urlsArray,$_key,$_url,$_matches,$_urlsList,$urlsList);
        $urlsArray = array_filter($urlsArray);
        $urlsArray = array_unique($urlsArray);

        // spider::$useragent = $rule['user_agent'];
        // spider::$encoding  = $rule['curl']['encoding'];
        // spider::$referer   = $rule['curl']['referer'];
        // spider::$charset   = $rule['charset'];

        if(empty($urlsArray)){
            if($work=='shell'){
                echo spider::errorlog("采集列表为空!请填写!\n",$url,'urls.empty',array('pid'=>$pid,'sid'=>$sid,'rid'=>$rid));
                return false;
            }
            iUI::alert('采集列表为空!请填写!', 'js:parent.window.iCMS_MODAL.destroy();');
        }

//      if(spider::$ruleTest){
//          echo "<pre>";
//          print_r(iSecurity::escapeStr($project));
//          print_r(iSecurity::escapeStr($rule));
//          echo "</pre>";
//          echo "<hr />";
//      }
        if($rule['mode']=="2"){
            iPHP::vendor('phpQuery');
            spider::$ruleTest && $_GET['pq_debug'] && phpQuery::$debug =1;
        }

        $pubArray         = array();
        $pubCount         = array();
        $pubAllCount      = array();
        spider::$curl_proxy = $rule['proxy'];
        spider::$urlslast   = null;
        if (spider::$ruleTest) {
            echo '<b>列表总:</b>'.count($urlsArray) . "条<br />";
            echo '<pre>';
            print_r($urlsArray);
            echo '</pre>';
            $urlsArray = array(reset($urlsArray));
            echo '<b>测试第一条</b><br />';
        }
        foreach ($urlsArray AS $key => $url) {
            $url = trim($url);
            spider::$urlslast = $url;
            if($work=='shell'){
                echo '开始采集列表:'.$url."\n";
            }
            if (spider::$ruleTest) {
                echo '<b>抓取列表:</b>'.$url . "<br />";
            }
            $html = spider_tools::remote($url);
            if(empty($html)){
                $msg = "采集列表内容为空!\n";
                $msg.= var_export(spider_tools::$curl_info,true);
                echo spider::errorlog($msg,$url,'url.empty',array('pid'=>$pid,'sid'=>$sid,'rid'=>$rid));
                continue;
            }
            $rule['list_urls_format'] && $html = spider_tools::dataClean($rule['list_urls_format'], $html);

            if($rule['mode']=="2"){
                $doc       = phpQuery::newDocumentHTML($html,'UTF-8');
                $list_area = $doc[trim($rule['list_area_rule'])];
                // if(strpos($rule['list_area_format'], 'DOM::')!==false){
                //     $list_area = spider_tools::dataClean($rule['list_area_format'], $list_area);
                // }

                if($rule['list_area_format']){
                    $list_area_format = trim($rule['list_area_format']);
                    if(strpos($list_area_format, 'ARRAY::')!==false){
                        $list_area_format = str_replace('ARRAY::', '', $list_area_format);
                        $lists = array();
                        foreach ($list_area as $la_key => $la) {
                            $lists[] = phpQuery::pq($list_area_format,$la);
                        }
                    }else{
                        $lists = phpQuery::pq($list_area_format,$list_area);
                    }
                }else{
                    $lists = $list_area;
                }

                // $lists = $list_area;
                //echo 'list:getDocumentID:'.$lists->getDocumentID()."\n";
            }elseif($rule['mode']=="3"){
                $list_area = json_decode($html,true);
                if (spider::$ruleTest && is_null($list_area)) {
                    echo '<b>JSON ERROR:'.json_last_error_msg().'</b>';
                    echo "<hr />";
                }

                if($rule['list_area_rule']){
                    $list_area_rule = explode('->', $rule['list_area_rule']);
                    $level = 0;
                    $lists = spider_tools::array_filter_key($list_area,$list_area_rule,$level);
                }else{
                    $lists = $list_area;
                }
            }else{
                $list_area_rule = spider_tools::pregTag($rule['list_area_rule']);
                if ($list_area_rule && $rule['list_area_rule']!='<%content%>') {
                    preg_match('|' . $list_area_rule . '|is', $html, $matches);
                    $list_area = $matches['content'];
                } else {
                    $list_area = $html;
                }

                $html = null;
                unset($html);

                if (spider::$ruleTest) {
                    echo iSecurity::escapeStr($rule['list_area_rule']);
                    echo "<hr />";
                }
                if ($rule['list_area_format']) {
                    $list_area = spider_tools::dataClean($rule['list_area_format'], $list_area);
                }

                preg_match_all('|' . spider_tools::pregTag($rule['list_url_rule']) . '|is', $list_area, $lists, PREG_SET_ORDER);

                if ($rule['sort'] == "1") {
                    //arsort($lists);
                } elseif ($rule['sort'] == "2") {
                    asort($lists);
                } elseif ($rule['sort'] == "3") {
                    shuffle($lists);
                }
            }

            if (spider::$ruleTest) {
                echo '<b>列表区域规则:</b>'.iSecurity::escapeStr($rule['list_area_rule']);
                echo "<hr />";
                echo '<b>列表区域抓取结果:</b><div style="max-height:300px;overflow-y: scroll;">';
                if(is_array($list_area)){
                    echo "<pre>";
                    var_export($list_area);
                    echo "</pre>";
                }else{
                    echo iSecurity::escapeStr($list_area);
                }
                echo '</div>';
                echo "<hr />";
                echo '<b>列表链接规则:</b>'.iSecurity::escapeStr($rule['list_url_rule']);
                echo "<hr />";
                echo '<b>网址合成规则:</b>'.iSecurity::escapeStr($rule['list_url']);
                echo "<hr />";
            }
            $list_area = null;
            unset($list_area);

            if($prule_list_url){
                $rule['list_url']   = $prule_list_url;
            }

            $urlsData = self::lists_item_data($lists,$rule,$url);

            if (spider::$callback['urls'] && is_callable(spider::$callback['urls'])) {
                $urlsData = call_user_func_array(spider::$callback['urls'],array($urlsData,$url));
                $urlsData['work'] && $work = $urlsData['work'];
            }
            //PID@xx 返回URL列表
            if($callback=='CALLBACK@URL'){
                $cbListUrl = array();
                foreach ($urlsData AS $lkey => $value) {
                    if($value['url']===false){
                        continue;
                    }
                    // if(spider::checker($work)===true){
                        $cbListUrl[] = $value['url'];
                    // }
                }
                return $cbListUrl;
            }

            if($work=="WEB@MANUAL"){
                $listsArray[$url] = $urlsData;
            }
            if($work=="shell"){
                $pubCount[$url]['count'] = count($lists);
                $pubAllCount['count']+=$pubCount[$url]['count'];
                echo "开始采集:".$url." 列表 ".$pubCount[$url]['count']."条记录\n";
                if(empty($pubCount[$url]['count'])){
                    echo spider::errorlog("采集列表记录为0!\n",$url,'url.zero',array('pid'=>$pid,'sid'=>$sid,'rid'=>$rid));
                }
                foreach ($urlsData AS $lkey => $value) {
                    spider::$title = $value['title'];
                    spider::$url   = $value['url'];

                    if(spider::$url===false){
                        continue;
                    }
                    $hash  = md5(spider::$url);
                    echo "\033[32m开始采集...\033[0m\n";
                    echo "\033[36mtitle:\033[0m".spider::$title."\n";
                    echo "\033[36murl:\033[0m".spider::$url."\n";
                    spider::$rid = $rid;
                    $checker = spider::checker($work,$pid,spider::$url,spider::$title);
                    if($checker===true){
                        $wait = 3;
                        $wait_start = time();
                        $callback  = spider::publish("shell");
                        if ($callback['code'] == "1001") {
                            $pubCount[$url]['success']++;
                            $pubAllCount['success']++;
                            $wait+= time()-$wait_start;
                            echo "\033[32m采集完成并发布成功".str_repeat('.',$wait)."√\033[0m\n";
                            if($project['sleep']){
                                if($rule['mode']!="2"){
                                    unset($lists[$lkey]);
                                }
                                gc_collect_cycles();

                                $usleep = $project['sleep']*1000;
                                echo "\033[31m暂停".($project['sleep']/1000)."秒后继续\033[0m\n\n";
                                usleep($usleep); //1000000 = 1s
                            }else{
                                //sleep(1);
                            }
                        }else{
                            $pubCount[$url]['error']++;
                            $pubAllCount['error']++;
                            echo "error\n\n";
                            continue;
                        }
                    }
                    $pubCount[$url]['published']++;
                    $pubAllCount['published']++;
                }
                if($rule['mode']=="2"){
                    phpQuery::unloadDocuments($doc->getDocumentID());
                }else{
                    unset($lists);
                }
            }
            if($work=="WEB@AUTO"||$work=='DATA@RULE'){
                spider::$spider_url_ids = array();
                foreach ($urlsData AS $lkey => $value) {
                    spider::$title = $value['title'];
                    spider::$url   = $value['url'];

                    if(spider::$url===false){
                        continue;
                    }
                    $hash  = md5(spider::$url);
                    if (spider::$ruleTest) {
                        echo '<b>列表抓取结果:</b>'.$lkey.'<br />';
                        echo spider::$title . ' (<a href="' . APP_URI . '&do=testdata'.
                            '&url=' . urlencode(spider::$url) .
                            '&rid=' . $rid .
                            '&pid=' . $pid .
                            '&title=' . urlencode(spider::$title) .
                            '" target="_blank">测试内容规则</a>) <br />';
                        echo spider::$url . "<br />";
                        echo $hash . "<br />";
                        unset($value['title'],$value['url']);
                        if($value){
                            echo '<b>其它采集结果:</b>';
                            echo '<pre>';
                            var_dump(array_map('htmlspecialchars', $value));
                            echo '</pre>';
                        }
                        echo "<hr />";
                    } else {
                        if(spider::checker($work,$pid,spider::$url,spider::$title)===true||spider::$dataTest){
                            $suData = array(
                                'sid'   => 0,
                                'url'   => spider::$url,'title' => spider::$title,
                                'cid'   => $cid,'rid' => $rid,'pid' => $pid,
                                'hash'  => $hash
                            );
                            switch ($work) {
                                case 'DATA@RULE':
                                    $contentArray[$lkey] = spider_data::crawl($pid,$rid,spider::$url,spider::$title);
                                    // $contentArray[$lkey] = spider_urls::crawl($work,$_pid);
                                    unset($suData['sid']);
                                    $suData['title'] = addslashes($suData['title']);
                                    $suData+= array(
                                        'addtime' => time(),
                                        'status'  => '2','publish' => '2',
                                        'indexid' => '0','pubdate' => '0'
                                    );
                                    spider::$dataTest OR $suid = iDB::insert('spider_url',$suData);
                                    spider::$spider_url_ids[$lkey] = $suid;
                                break;
                                case 'WEB@AUTO':
                                    $pubArray[] = $suData;
                                break;
                            }
                        }
                    }
                }
            }
        }
        $lists = null;
        unset($lists);
        gc_collect_cycles();

        switch ($work) {
            case 'WEB@AUTO':
                return $pubArray;
            break;
            case 'DATA@RULE':
                return $contentArray;
            break;
            case 'WEB@MANUAL':
                return array(
                    'cid'        => $cid,
                    'rid'        => $rid,
                    'pid'        => $pid,
                    'sid'        => $sid,
                    'work'       => $work,
                    'rule'       => $rule,
                    'listsArray' => $listsArray
                );
            break;
            case "shell":
                echo "采集数据统结果:\n";
                print_r($pubCount);
                print_r($pubAllCount);
                echo "全部采集完成....\n";
                iDB::update('spider_project',array('lastupdate'=>time()),array('id'=>$pid));
            break;
        }
    }
    public static function lists_item_data($lists,$rule,$url){
        $array = array();
        if($lists)foreach ($lists AS $lkey => $row) {
            $cache = array();
            $data = spider_tools::listItemData($row,$rule,$url);
            if($data)foreach ($data as $key => $value) {
                if(is_numeric($key)|| strpos($key, 'var_')!==false){
                    unset($data[$key]);
                }
                if(strpos($key, 'var_')===false && $key!='title' && $key!='url'){
                    $cache[$key] = $value;
                }
            }
            $data['url'] && $cache && spider_tools::listItemCache($data['url'],$cache);
            $data && $array[$lkey] = $data;
        }
        return $array;
    }

}
