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

class spider_content {
    public static $hash = null;
    /**
     * 抓取资源
     * @param  [string] $html      [抓取结果]
     * @param  [array] $data      [数据项]
     * @param  [array] $rule      [规则]
     * @param  [array] $responses [已经抓取资源]
     * @return [array]           [返回处理结果]
     */
    public static function crawl($html,$data,$rule,$responses) {
        @set_time_limit(0);

        if(trim($data['rule'])===''){
            return '';
        }
        $name = $data['name'];
        if (spider::$dataTest) {
            echo'<b>['.$name.']规则:</b>'.iSecurity::escapeStr($data['rule'])."<br />";
        }
        /**
         * 在数据项里调用之前采集的数据[DATA@name][DATA@name.key]
         */
        if(strpos($data['rule'], '[DATA@')!==false){
            $content = spider_tools::getDATA($responses,$data['rule']);
            if(is_array($content)){
                return $content;
            }else{
                $data['rule'] = $content;
            }
        }
        /**
         * 在数据项里调用之前采集的数据RULE@规则id@url
         */
        if(strpos($data['rule'], 'RULE@')!==false){
            list($_rid,$_urls) = explode('@', str_replace('RULE@', '',$data['rule']));
            empty($_urls) && $_urls = trim($html);
            if (spider::$dataTest) {
                print_r('<b>使用[rid:'.$_rid.']规则抓取</b>:'.$_urls);
                echo "<hr />";
            }
            return spider_urls::crawl('DATA@RULE',false,$_rid,$_urls);
        }
        /**
         * RAND@10,0
         * 返回随机数
         */
        if(strpos($data['rule'], 'RAND@')!==false){
            $random = str_replace('RAND@', '',$data['rule']);
            list($length,$numeric) = explode(',', $random);
            return random($length, empty($numeric)?0:1);
        }
        if(is_array($html)){
            $content = $html;
        }else{
            $contentArray      = array();
            self::$hash        = array();
            $_content          = spider_content::match($html,$data,$rule);
            $cmd5              = md5($_content);
            $contentArray[]    = $_content;
            self::$hash[$cmd5] = spider::$url;
            $data['page'] && self::page_data($html,$data,$rule,$contentArray);
            $content = implode('#--iCMS.PageBreak--#', $contentArray);
            unset($contentArray,$_content);
        }
        unset($html);
        //遍历 例:FOREACH@<p><img src="[KEY@source]" />[KEY@add_intro]</p>
        //
        if(strpos($data['rule'], 'FOREACH@')!==false){
            $data_rule = str_replace('FOREACH@', '', $data['rule']);
            preg_match_all('!.*?\[KEY@([\w-_]+)\].*?!ism', $data_rule,$matchs);
            $variable = array();
            foreach ((array)$content as $key => $value) {
                foreach ((array)$matchs[1] as $i => $k) {
                    if(isset($value[$k])){
                        $variable[$key][$k] = $value[$k];
                    }
                }
            }
            foreach ((array)$matchs[1] as $i => $k) {
                $search[] = '[KEY@'.$k.']';
            }
            $contentArray = array();
            foreach ($variable as $key => $value) {
                $contentArray[] = str_replace($search, $value, $data_rule);
            }
            $content = implode('#--iCMS.PageBreak--#', $contentArray);
            unset($contentArray,$variable);
        }

        if (spider::$dataTest) {
            print_r('<b>['.$name.']匹配结果:</b><div style="max-height:300px;overflow-y: scroll;">'.htmlspecialchars($content).'</div>');
            echo "<hr />";
        }

        if ($data['cleanbefor']) {
            $content = spider_tools::dataClean($data['cleanbefor'], $content);
        }
        if ($data['trim']) {
            if(is_array($content)){
                $content = array_map('trim', $content);
            }else{
                $content = str_replace('&nbsp;','',trim($content));
            }
        }
        if ($data['json_decode']) {
            $content = json_decode($content,true);
            if(is_null($content)){
                return self::msg(
                    'JSON ERROR:'.json_last_error_msg(),
                    'content.json_decode.error',
                    $name,$rule
                );
            }
        }
        if ($data['htmlspecialchars_decode']) {
            $content = htmlspecialchars_decode($content);
        }
        if(!is_array($content)){
            $content = stripslashes($content);
        }

        if ($data['cleanhtml']) {
            $content = preg_replace('/<[\/\!]*?[^<>]*?>/is', '', $content);
        }
        if ($data['format'] && $content) {
            $content = autoformat($content);
        }

        if ($data['img_absolute'] && $content) {
            preg_match_all("/<img.*?src\s*=[\"|'](.*?)[\"|']/is", $content, $img_match);
            if($img_match[1]){
                $_img_array = array_unique($img_match[1]);
                $_img_urls  = array();
                foreach ((array)$_img_array as $_img_key => $_img_src) {
                    $_img_urls[$_img_key] = spider_tools::url_complement($rule['__url__'],$_img_src);
                }
               $content = str_replace($_img_array, $_img_urls, $content);
            }
            unset($img_match,$_img_array,$_img_urls,$_img_src);
        }

        if ($data['capture']) {
            $content && $content = spider_tools::remote($content);
        }
        if ($data['download']) {
            $content && $content = iFS::http($content);
        }

        if ($data['autobreakpage']) {
            $content = spider_tools::autoBreakPage($content);
        }
        if ($data['mergepage']) {
            $content = spider_tools::mergePage($content);
        }
        if ($data['cleanafter']) {
            $content = spider_tools::dataClean($data['cleanafter'], $content);
        }

        if ($data['filter']) {
            $fwd = iPHP::callback(array("filterApp","run"),array(&$content),false);
            if($fwd){
                return self::msg(
                    '中包含【'.$fwd.'】被系统屏蔽的字符!',
                    'content.filter',
                    $name,$rule
                );
            }
        }
        if ($data['empty']) {
            $empty = $content;
            is_array($content) && $empty = implode('', $content);
            $empty = self::real_empty($empty);
            if(empty($empty)){
                return self::msg(
                    '规则设置了不允许为空.当前抓取结果为空!请检查,规则是否正确!',
                    'content.empty',
                    $name,$rule
                );
            }
            unset($empty);
        }

        if (spider::$callback['content'] && is_callable(spider::$callback['content'])) {
            $content = call_user_func_array(spider::$callback['content'],array($content,$data));
        }

        if($data['array']){
            if(strpos($content, '#--iCMS.PageBreak--#')!==false){
                $content = explode('#--iCMS.PageBreak--#', $content);
            }
            return (array)$content;
        }
        if($data['implode'] && is_array($content)){
            $content = implode('', $content);
        }
        return $content;
    }
    public static function page_data($html,$data,$rule,&$contentArray){
        if(empty($rule['page_url'])){
            $rule['page_url'] = $rule['list_url'];
        }
        if (empty(spider::$allHtml)) {
            $page_url_array = array();
            $page_area_rule = trim($rule['page_area_rule']);
            if($page_area_rule){
                if(strpos($page_area_rule, 'DOM::')!==false){
                    iPHP::vendor('phpQuery');
                    $doc      = phpQuery::newDocumentHTML($html,'UTF-8');
                    $pq_dom   = str_replace('DOM::','', $page_area_rule);
                    $pq_array = phpQuery::pq($pq_dom);
                    foreach ($pq_array as $pn => $pq_val) {
                        $href = phpQuery::pq($pq_val)->attr('href');
                        if($href){
                            if($rule['page_url_rule']){
                                if(strpos($rule['page_url_rule'], '<%')!==false){
                                    $page_url_rule = spider_tools::pregTag($rule['page_url_rule']);
                                    if (!preg_match('|' . $page_url_rule . '|is', $href)){
                                        continue;
                                    }
                                }else{
                                    $cleanhref = spider_tools::dataClean($rule['page_url_rule'],$href);
                                    if($cleanhref){
                                        $href = $cleanhref;
                                        unset($cleanhref);
                                    }else{
                                        continue;
                                    }
                                }
                            }
                            $href = str_replace('<%url%>',$href, $rule['page_url']);
                            $page_url_array[$pn] = spider_tools::url_complement($rule['__url__'],$href);
                        }
                    }
                    phpQuery::unloadDocuments($doc->getDocumentID());
                }else{
                    $page_area_rule = spider_tools::pregTag($page_area_rule);
                    if ($page_area_rule) {
                        preg_match('|' . $page_area_rule . '|is', $html, $matches, $PREG_SET_ORDER);
                        $page_area = $matches['content'];
                    } else {
                        $page_area = $html;
                    }
                    if($rule['page_url_rule']){
                        $page_url_rule = spider_tools::pregTag($rule['page_url_rule']);
                        preg_match_all('|' .$page_url_rule. '|is', $page_area, $page_url_matches, PREG_SET_ORDER);
                        foreach ($page_url_matches AS $pn => $row) {
                            $href = str_replace('<%url%>', $row['url'], $rule['page_url']);
                            $page_url_array[$pn] = spider_tools::url_complement($rule['__url__'],$href);
                            gc_collect_cycles();
                        }
                    }
                    unset($page_area);
                }
            }else{ // 逻辑方式
                if($rule['page_url_parse']=='<%url%>'){
                    $page_url = str_replace('<%url%>',$rule['__url__'],$rule['page_url']);
                }else{
                    $page_url_rule = spider_tools::pregTag($rule['page_url_parse']);
                    preg_match('|' . $page_url_rule . '|is', $rule['__url__'], $matches, $PREG_SET_ORDER);
                    $page_url = str_replace('<%url%>', $matches['url'], $rule['page_url']);
                }
                if (stripos($page_url,'<%step%>') !== false){
                    for ($pn = $rule['page_no_start']; $pn <= $rule['page_no_end']; $pn = $pn + $rule['page_no_step']) {
                        $pno = $pn;
                        if($rule['page_no_fill']){
                            $pno = sprintf("%0".$rule['page_no_fill']."s",$pn);
                        }
                        $page_url_array[$pn] = str_replace('<%step%>', $pno, $page_url);
                        gc_collect_cycles();
                    }
                }
            }
            //URL去重清理
            if($page_url_array){
                $page_url_array = array_filter($page_url_array);
                $page_url_array = array_unique($page_url_array);
                $puk = array_search($rule['__url__'],$page_url_array);
                if($puk!==false){
                    unset($page_url_array[$puk]);
                }
            }

            if (spider::$dataTest) {
                echo "<b>内容页网址:</b>".$rule['__url__'] . "<br />";
                echo "<b>分页网址提取规则:</b>".iSecurity::escapeStr($page_url_rule). "<br />";
                echo "<b>分页合成:</b>".$rule['page_url'] . "<br />";
                echo "<hr />";
            }
            if(spider::$dataTest){
                echo "<b>分页列表:</b><pre>";
                print_r($page_url_array);
                echo "</pre><hr />";
            }

            if($data['page']){
                spider::$content_right_code = ($data['dom']?'DOM::':'').$data['rule'];
            }
            $rule['page_url_right'] && spider::$content_right_code = trim($rule['page_url_right']);
            spider::$content_error_code = trim($rule['page_url_error']);
            if(spider::$dataTest){
                echo "<b>有效分页特征码:</b>";
                echo iSecurity::escapeStr(spider::$content_right_code);
                echo "<br />";
                echo "<b>无效分页特征码:</b>";
                echo iSecurity::escapeStr(spider::$content_error_code);
                echo "<hr />";
            }
            $rule['proxy'] && spider::$curl_proxy = $rule['proxy'];
            $rule['data_charset'] && spider::$charset = $rule['data_charset'];
            $pageurl = array();

            foreach ($page_url_array AS $pukey => $purl) {
                //usleep(100);
                $phtml = spider_tools::remote($purl);
                if (empty($phtml)) {
                    break;
                }
                $md5 = md5($phtml);
                if($pageurl[$md5]){
                    if (spider::$dataTest) {
                        echo "<b>{$purl}此分页已采过</b><hr />";
                    }
                    continue;
                }
                $check_content_code = spider_tools::check_content_code($phtml,'error');
                if ($check_content_code === false) {
                    unset($check_content_code,$phtml);
                    if (spider::$dataTest) {
                        echo "<b>找到无效分页特征码,中止其它分页采集</b><hr />";
                    }
                    break;
                }

                $check_content_code = spider_tools::check_content_code($phtml,'right');
                if ($check_content_code === false) {
                    unset($check_content_code,$phtml);
                    if (spider::$dataTest) {
                        echo "<b>未找到有效分页特征码,中止其它分页采集</b><hr />";
                    }
                    break;
                }

                $_content = spider_content::match($phtml,$data,$rule);
                $cmd5     = md5($_content);
                $_purl    = self::$hash[$cmd5];
                if($_purl){
                    if (spider::$dataTest) {
                        echo "<b>发现[{$purl}]正文与[{$_purl}]相同,跳过本页采集</b><hr />";
                    }
                    continue;
                }

                $contentArray[]        = $_content;
                $pageurl[$md5]         = $purl;
                self::$hash[$cmd5]     = $purl;
                spider::$allHtml[$md5] = $phtml;
            }
            gc_collect_cycles();
            unset($check_content_code,$phtml);

            if (spider::$dataTest) {
                echo "<b>最终分页列表:</b><pre>";
                print_r($pageurl);
                echo "</pre><hr />";
            }
        }else{
            foreach ((array)spider::$allHtml as $ahkey => $phtml) {
                $contentArray[] = spider_content::match($phtml,$data,$rule);
            }
        }
    }
    public static function real_empty($text){
        $text = str_replace(array('&nbsp;','　'), '', $text);
        $text = preg_replace(array(
            '/\s*/','/\r*/','/\n*/',
            '@<p[^>]*>\s*<br[^>]*>\s*</p>@',
            '@<(\w+)>\s*<\$1>@',
            '@</*(p|strong|b|span)>@'
        ), '', $text);
        $text = trim($text);
        return $text;
    }
    public static function match($html,$data,$rule){
        $match_hash = array();
        if($data['dom']){
            iPHP::vendor('phpQuery');
            spider::$dataTest && $_GET['pq_debug'] && phpQuery::$debug =1;
            $html = preg_replace(array('/<script.+?<\/script>/is','/<style.+?<\/style>/is'),'',$html);
            $doc  = phpQuery::newDocumentHTML($html,'UTF-8');
            if(strpos($data['rule'], '@')!==false){
                list($content_dom,$content_attr) = explode("@", $data['rule']);
                $content_fun = 'attr';
            }else{
                list($content_dom,$content_fun,$content_attr) = explode("\n", $data['rule']);
            }
            $content_dom  = trim($content_dom);
            $content_fun  = trim($content_fun);
            $content_attr = trim($content_attr);
            $content_fun OR $content_fun = 'html';
            if ($data['multi']) {
                $conArray = array();
                $_content = null;
                foreach ($doc[$content_dom] as $doc_key => $doc_value) {
                    if($content_attr){
                        $_content = phpQuery::pq($doc_value)->$content_fun($content_attr);
                    }else{
                        $_content = phpQuery::pq($doc_value)->$content_fun();
                    }
                    $cmd5 = md5($_content);
                    if($match_hash[$cmd5]){
                        break;
                    }
                    if ($data['trim']) {
                        $_content = trim($_content);
                    }
                    if(empty($_content)){
                        $cmd5 = 'empty('.$doc_key.')';
                    }else{
                        $conArray[$doc_key]  = $_content;
                    }
                    $match_hash[$cmd5] = true;
                }
                if (spider::$dataTest) {
                    echo "<b>多条匹配结果:</b><pre>";
                    print_r($match_hash);
                    echo "</pre><hr />";
                }
                $content = implode('#--iCMS.PageBreak--#', $conArray);
                unset($conArray,$_content,$match_hash);
            }else{
                if($content_attr){
                    $content = $doc[$content_dom]->$content_fun($content_attr);
                }else{
                    $content = $doc[$content_dom]->$content_fun();
                }
            }

            phpQuery::unloadDocuments($doc->getDocumentID());
            unset($doc);
        }else{
            if(trim($data['rule'])=='<%content%>'){
                $content = $html;
            }else{
                $data_rule = spider_tools::pregTag($data['rule']);
                if (preg_match('/(<\w+>|\.\*|\.\+|\\\d|\\\w)/i', $data_rule)) {
                    if ($data['multi']) {
                        preg_match_all('|' . $data_rule . '|is', $html, $matches, PREG_SET_ORDER);
                        $conArray = array();
                        foreach ((array) $matches AS $mkey => $mat) {
                            $cmd5 = md5($mat['content']);
                            if($match_hash[$cmd5]){
                                break;
                            }
                            if ($data['trim']) {
                                $mat['content'] = trim($mat['content']);
                            }
                            if(empty($mat['content'])){
                                $cmd5 = 'empty('.$mkey.')';
                            }else{
                                $conArray[$mkey] = $mat['content'];
                            }
                            $match_hash[$cmd5] = true;
                        }
                        if (spider::$dataTest) {
                            echo "<b>多条匹配结果:</b><pre>";
                            print_r($match_hash);
                            echo "</pre><hr />";
                        }
                        $content = implode('#--iCMS.PageBreak--#', $conArray);
                        unset($conArray,$match_hash);
                    } else {
                        preg_match('|' . $data_rule . '|is', $html, $matches);
                        $content = $matches['content'];
                    }
                } else {
                    $content = $data['rule'];
                }
            }
        }
        return $content;
    }
    public static function msg($msg,$type,$name,$rule){
        $msg = '['.$name.']'.$msg;
        if(spider::$dataTest){
            exit('<h1>'.$msg.'</h1>');
        }
        if(spider::$work){
            echo spider::errorlog($msg,$rule['__url__'],$type);
            echo "\n{$msg}\n";
            return null;
        }else{
            iUI::alert($msg);
        }
    }
}
