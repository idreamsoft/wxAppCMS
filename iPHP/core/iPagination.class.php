<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) iiiPHP.com. All rights reserved.
 *
 * @author iPHPDev <master@iiiphp.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.1.0
 */
class iPagination {
    public static $config   = array();
    public static $callback = array();

    public static $pagenav = NULL;
    public static $offset  = NULL;

    public static $total_cache = 'G';
    public static function getTotal($sql,$perpage=10,$nowindex=null) {
        $total_type = $vars['total_cache'] ? 'G' : null;
        $total = self::totalCache($sql,$total_type,iCMS::$config['cache']['page_total']);
        return self::make(array(
            'total_type' => $total_type,
            'total'      => $total,
            'perpage'    => $perpage,
            'nowindex'   => ($nowindex===null?$GLOBALS['page']:$nowindex)
        ));
    }
    //分页数缓存
    public static function totalCache($sql, $type = null,$cachetime=3600) {
        $total = (int) $_GET['total_num'];
        if($type=="G"){
            empty($total) && $total = iDB::value($sql);
        }else{
            $cache_key = 'page_total/'.substr(md5($sql), 8, 16);
            if(empty($total)){
                if (!isset($_GET['page_total_cache'])|| $type === 'nocache'||!$cachetime) {
                    $total = iDB::value($sql);
                    $type === null && iCache::set($cache_key,$total,$cachetime);
                }else{
                    $total = iCache::get($cache_key);
                }
            }
        }
        return (int)$total;
    }
    //动态翻页函数
    public static function pagenav($total, $perpage = 20, $unit = "条记录", $url = '', $target = '') {
        $pageconf = array(
            'url'        => $url,
            'target'     => $target,
            'total'      => $total,
            'perpage'    => $perpage,
            'total_type' => 'G',
            'lang'       => iUI::lang(iPHP_APP . ':page'),
        );
        $pageconf['lang']['format_left'] = '<li>';
        $pageconf['lang']['format_right'] = '</li>';

        $iPages = new iPages($pageconf);
        self::$offset = $iPages->offset;
        self::$pagenav = '<ul>' .
        self::$pagenav.= $iPages->show(3);
        self::$pagenav.= "<li> <span class=\"muted\">{$total}{$unit} {$perpage}{$unit}/页 共{$iPages->totalpage}页</span></li>";
        if ($iPages->totalpage > 200) {
            $url = $iPages->get_url(1);
            self::$pagenav.= "<li> <span class=\"muted\">跳到 <input type=\"text\" id=\"pageselect\" style=\"width:24px;height:12px;margin-bottom: 0px;line-height: 12px;\" /> 页 <input class=\"btn btn-small\" type=\"button\" onClick=\"window.location='{$url}&page='+$('#pageselect').val();\" value=\"跳转\" style=\"height: 22px;line-height: 18px;\"/></span></li>";
        } else {
            self::$pagenav.= "<li> <span class=\"muted\">跳到" . $iPages->select() . "页</span></li>";
        }
        self::$pagenav.= '</ul>';
    }
    //模板翻页函数
    public static function make($conf) {
        empty($conf['lang']) && $conf['lang'] = iUI::lang(iPHP_APP . ':page');
        empty($conf['unit']) && $conf['unit'] = iUI::lang(iPHP_APP . ':page:list');

        $iPages = new iPages($conf);
        // if ($iPages->totalpage > 1) {
            $iPages->nowindex<1 && $iPages->nowindex =1;
            $pagenav = $conf['pagenav'] ? strtoupper($conf['pagenav']) : 'NAV';
            $pnstyle = $conf['pnstyle'] ? $conf['pnstyle'] : 0;
            iView::set_iVARS(array(
                'PAGES' => $iPages,
                'PAGE'  => array(
                    $pagenav  => ($iPages->totalpage > 1)?$iPages->show($pnstyle):'',
                    'COUNT'   => $conf['total'],
                    'TOTAL'   => $iPages->totalpage,
                    'CURRENT' => $iPages->nowindex,
                    'PN'      => $iPages->nowindex,
                    'PREV'    => $iPages->prev_page(),
                    'NEXT'    => $iPages->next_page(),
                    'LAST'    => ($iPages->nowindex>=$iPages->totalpage),
                )
            ));
        // }
        return $iPages;
    }
    //模板静态分页配置
    public static function url($iurl){
        if(isset($GLOBALS['iPage'])) return;

        $iurl = (array)$iurl;
        $GLOBALS['iPage']['url']  = $iurl['pageurl'];
        $GLOBALS['iPage']['config'] = array(
            'enable' =>true,
            'index'  =>$iurl['href'],
            'ext'    =>$iurl['ext']
        );
    }
    //内容分页
    public static function content($content,$page,$total,$count,$mode=null,$chapterArray=null){
        $pageArray = array();
        $pageurl = $content['iurl']['pageurl'];
        if ($total > 1) {
            $_GLOBALS_iPage = $GLOBALS['iPage'];
            $mode && self::url($content['iurl']);
            $pageconf = array(
                'page_name' => 'p',
                'url'       => $pageurl,
                'total'     => $total,
                'perpage'   => 1,
                'nowindex'  => (int) $_GET['p'],
                'lang'      => iUI::lang(iPHP_APP . ':page'),
            );
            if ($content['chapter']) {
                foreach ((array) $chapterArray as $key => $value) {
                    $pageconf['titles'][$key + 1] = $value['subtitle'];
                }
            }
            $iPages = new iPages($pageconf);
            unset($GLOBALS['iPage']);
            $GLOBALS['iPage'] = $_GLOBALS_iPage;
            unset($_GLOBALS_iPage);

            $pageArray['list']  = $iPages->list_page();
            $pageArray['index'] = $iPages->first_page('array');
            $pageArray['prev']  = $iPages->prev_page('array');
            $pageArray['next']  = $iPages->next_page('array');
            $pageArray['endof'] = $iPages->last_page('array');
            $pagenav = $iPages->show(0);
            $pagetext = $iPages->show(10);
        }
        $content_page = array(
            'pn'      => $page,
            'total'   => $total, //总页数
            'count'   => $count, //实际页数
            'current' => $page,
            'nav'     => $pagenav,
            'url'     => iURL::page_num($pageurl,$_GET['p']),
            'pageurl' => $pageurl,
            'text'    => $pagetext,
            'PAGES'   => $iPages,
            'args'    => iSecurity::escapeStr($_GET['pageargs']),
            'first'   => ($page == "1" ? true : false),
            'last'    => ($page == $count ? true : false), //实际最后一页
            'end'     => ($page == $total ? true : false)
        ) + $pageArray;
        unset($pagenav, $pagetext, $iPages, $pageArray);
        return $content_page;
    }
}
