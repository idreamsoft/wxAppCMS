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

class spider_data {

    public static function crawl($_pid = NULL,$_rid = NULL,$_url = NULL,$_title = NULL) {
        @set_time_limit(0);
        $sid = spider::$sid;
        if ($sid) {
            $sRs   = iDB::row("SELECT * FROM `#iCMS@__spider_url` WHERE `id`='$sid' LIMIT 1;");
            $title = $sRs->title;
            $cid   = $sRs->cid;
            $pid   = $sRs->pid;
            $url   = $sRs->url;
            $rid   = $sRs->rid;
       } else {
            $rid   = spider::$rid;
            $pid   = spider::$pid;
            $title = spider::$title;
            $url   = spider::$url;

            $_rid   === NULL OR $rid = $_rid;
            $_pid   === NULL OR $pid = $_pid;
            $_title === NULL OR $title = $_title;
            $_url   === NULL OR $url = $_url;
        }

        if($pid){
            $project        = spider::project($pid);
            $prule_list_url = $project['list_url'];
        }

        $ruleA           = spider::rule($rid);
        $rule            = $ruleA['rule'];
        $dataArray       = $rule['data'];

        if($prule_list_url){
            $rule['list_url']   = $prule_list_url;
        }

        if (spider::$dataTest) {
            echo "<b>抓取规则信息</b><pre style='max-height:300px;overflow-y: scroll;'>";
            print_r(iSecurity::escapeStr($ruleA));
            print_r(iSecurity::escapeStr($project));
            echo "</pre><hr />";
        }

        $rule['proxy'] && spider::$curl_proxy = $rule['proxy'];
        $rule['data_charset'] && spider::$charset = $rule['data_charset'];

        $responses = array();
        $html      = spider_tools::remote($url);
        if(empty($html)){
            $msg = '错误:001..采集 ' . $url . '文件内容为空!请检查采集规则';
            $msg.= var_export(spider_tools::$curl_info,true);
            if(spider::$work=='shell'){
                echo spider::errorlog("{$msg}\n",$url,'data.empty',array('pid'=>$pid,'sid'=>$sid,'rid'=>$rid));
                return false;
            }else{
                iUI::alert($msg);
            }
        }

//      $http   = spider::check_content_code($html);
//
//      if($http['match']==false){
//          return false;
//      }
//      $content        = $http['content'];
        spider::$allHtml        = array();
        $rule['__url__']        = spider::$url;
        $responses['reurl']     = spider::$url;
        $responses['__title__'] = $title;
        foreach ((array)$dataArray AS $key => $data) {

            $content_html = $html;
            $dname = $data['name'];
            /**
             * [UNSET:name]
             * 注销[name]
             * @var string
             */
            if (strpos($dname,'UNSET:')!== false){
                $_dname = str_replace('UNSET:', '', $dname);
                unset($responses[$_dname]);
                continue;
            }
            /**
             * [DATA:name]
             * 把之前[name]处理完的数据当作原始数据
             * 如果之前有数据会叠加
             * 用于数据多次处理
             * @var string
             */
            if (strpos($dname,'DATA:')!== false){
                $_dname = str_replace('DATA:', '', $dname);
                $content_html = $responses[$_dname];
                unset($responses[$dname]);
            }
            /**
             * [PRE:name]
             * 把PRE:name采集到的数据 当做原始数据
             * 一般用于下载内容
             * @var string
             */
            $pre_dname = 'PRE:'.$dname;
            if(isset($responses[$pre_dname])){
                $content_html = $responses[$pre_dname];
                unset($responses[$pre_dname]);
            }
            /**
             * [EMPTY:name]
             * 如果[name]之前抓取结果数据为空使用这个数据项替换
             * @var string
             */
            if (strpos($dname,'EMPTY:')!== false){
                $_dname = str_replace('EMPTY:', '', $dname);
                if(empty($responses[$_dname])){
                    $dname = $_dname;
                }else{
                    //有值不执行抓取
                    continue;
                }
            }
            $content = spider_content::crawl($content_html,$data,$rule,$responses);
            if($content === null){
                $responses[$key] = null;
                continue;
            }
            unset($content_html);

            if (strpos($dname,'ARRAY:')!== false){
                $dname = str_replace('ARRAY:', '', $dname);
                $cArray = array();

                foreach ((array)$content as $k => $value) {
                    foreach ((array)$value as $key => $val) {
                        $cArray[$key][$k]=$val;
                    }
                }
                if($cArray){
                    $content = $cArray;
                    unset($cArray);
                }
            }

            /**
             * [name.xxx]
             * 采集内容做为数组
             */
            if (strpos($dname,'.')!== false){
                $f_key = substr($dname,0,stripos($dname, "."));
                $s_key = substr(strrchr($dname, "."), 1);
                // $responses = self::create_multi_array($dname,$content);
                if(isset($responses[$f_key][$s_key])){
                    if(is_array($responses[$f_key][$s_key])){
                        $responses[$f_key][$s_key] = array_merge((array)$responses[$f_key][$s_key],(array)$content);
                    }else{
                        $responses[$f_key][$s_key].= $content;
                    }
                }else{
                    $responses[$f_key][$s_key] = $content;
                }
            }else{
                /**
                 * 多个name 内容合并
                 */
                if(isset($responses[$dname])){
                    if(is_array($responses[$dname])){
                        $responses[$dname] = array_merge((array)$responses[$dname],(array)$content);
                    }else{
                        $responses[$dname].= $content;
                    }
                }else{
                    $responses[$dname] = $content;
                }
            }
            /**
             * 对匹配多条的数据去重过滤
             */
            if(!is_array($responses[$dname]) && $data['multi']){
                if(strpos($responses[$dname], ',')!==false){
                    $_dnameArray = explode(',', $responses[$dname]);
                    $dnameArray  = array();
                    foreach ((array)$_dnameArray as $key => $value) {
                        $value = trim($value);
                        $value && $dnameArray[]=$value;
                    }
                    $dnameArray = array_filter($dnameArray);
                    $dnameArray = array_unique($dnameArray);
                    $responses[$dname] = implode(',', $dnameArray);
                    unset($dnameArray,$_dnameArray);
                }
            }

            gc_collect_cycles();
        }
        foreach ($responses as $key => $value) {
            if(strpos($key, ':')!==false){
                unset($responses[$key]);
            }
        }
        if(isset($responses['title']) && empty($responses['title'])){
            $responses['title'] = $responses['__title__'];
        }
        spider::$allHtml = array();
        unset($html);

        gc_collect_cycles();

        if (spider::$dataTest) {
            echo "<b>最终采集结果:</b>";
            echo "<pre style='width:99%;word-wrap: break-word;white-space: pre-wrap;'>";
            print_r(iSecurity::escapeStr($responses));
            echo '<hr />';
            echo '使用内存:'.iFS::sizeUnit(memory_get_usage()).' 执行时间:'.iPHP::timer_stop().'s';
            echo "</pre>";
        }

        self::set_watermark_config($rule);

        if (spider::$callback['data'] && is_callable(spider::$callback['data'])) {
            $responses = call_user_func_array(spider::$callback['data'],array($responses));
        }

        return $responses;
    }
    public static function set_watermark_config($rule){
        iHttp::$CURLOPT_ENCODING        = '';
        iHttp::$CURLOPT_REFERER         = '';
        files::$watermark_config['pos'] = iCMS::$config['watermark']['pos'];
        files::$watermark_config['x']   = iCMS::$config['watermark']['x'];
        files::$watermark_config['y']   = iCMS::$config['watermark']['y'];
        files::$watermark_config['img'] = iCMS::$config['watermark']['img'];
        files::$watermark_enable        = iCMS::$config['watermark']['enable'];

        $rule['fs']['encoding'] && iHttp::$CURLOPT_ENCODING = $rule['fs']['encoding'];
        $rule['fs']['referer']  && iHttp::$CURLOPT_REFERER  = $rule['fs']['referer'];
        if($rule['watermark_mode']){
            files::$watermark_config['pos'] = $rule['watermark']['pos'];
            files::$watermark_config['x']   = $rule['watermark']['x'];
            files::$watermark_config['y']   = $rule['watermark']['y'];
            $rule['watermark']['img'] && files::$watermark_config['img'] = $rule['watermark']['img'];
        }
        if($rule['watermark_mode']=="2"){
            files::$watermark_enable = false;
        }
    }
    public static function create_multi_array($string,$value=null){
        $a_array = explode('.', $string);
        krsort ( $a_array );
        $count = count($a_array);
        $a = $value;
        foreach ($a_array as $k => $v) {
            $a = array($v=>$a);
            if(count($a)>1){
                array_shift($a);
            }
        }
        return $a;
    }
}
