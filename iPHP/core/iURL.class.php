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
class iURL {
    const PAGE_SIGN = '{P}';

    public static $config   = array();
    public static $callback = array();

    protected static $ARRAY = null;

    public static function init($config=array(),$_config=array()){
        self::$config   = array_merge($config,$_config);
        self::$callback = array_merge((array)self::$config['callback'],self::$callback);
    }

    public static function router($key, $var = null) {
        $routerArray = self::$config['config'];
        $routerKey   = $key;
        is_array($key) && $routerKey = $key[0];
        $router = $routerArray[$routerKey];
        $rewrite = iPHP_ROUTER_REWRITE;
        if(self::$callback['router']['rewrite']){
            $rewrite = self::$callback['router']['rewrite'];
        }
        $url = $rewrite?$router[0]:$router[1];

        if ($rewrite && stripos($routerKey, 'uid:') === 0) {
            $url = rtrim(self::$config['user_url'], '/') . $url;
        }

        if (is_array($key)) {
            if (is_array($key[1])) {
                /* 多个{} 例:/{uid}/{cid}/ */
                preg_match_all('/\{(\w+)\}/i', $url, $matches);
                $url = str_replace($matches[0], $key[1], $url);
            } else {
                $url = preg_replace('/\{\w+\}/i', $key[1], $url);
            }
            $key[2] && $url = $key[2] . $url;
        }

        if ($var == '?&') {
            $url .= $rewrite ? '?' : '&';
        }
        if(!$rewrite){
            $url = self::$config['api_url'].'/'.$url;
        }else{
            if(!iFS::checkHttp($url)){
                $url = rtrim(self::$config['url'],'/').$url;
            }
        }
        if(self::$callback['router']['data']){
            call_user_func_array(self::$callback['router']['data'], array(&$url));
        }
        return $url;
    }

    public static function rule($matches) {
    	$b	= $matches[1];
        if(strpos($b,'RAND')!==false){
            list($_b,$len,$is_num) = explode(',', $b);
            empty($len) && $len = 8;
            return random($len,$is_num);
        }
        $_time = 0;
        if(strpos($b,'AUTHID,')!==false||strpos($b,'AUTHCID,')!==false){
            list($b,$_time) = explode(',', $b);
        }
    	list($a,$c,$tc) = self::$ARRAY;
        switch($b) {
            case 'ID':		$e = $a['id'];break;
            case '0xID':	$e = sprintf("%08s",$a['id']);break;
            case '0x3ID':	$e = substr(sprintf("%08s",$a['id']), 0, 4);break;
            case '0x3,2ID': $e = substr(sprintf("%08s",$a['id']), 4, 2);break;
            case 'AUTHID':  $e = urlencode(auth_encode($a['id'],$_time));break;
            case 'MD5':     $e = substr(md5($c['id']),8,16);break;
            case 'TMD5':    $e = substr(md5(time().uniqid()),8,16);break;

            case 'CID':     $e = $c['cid'];break;
            case '0xCID':   $e = sprintf("%08s",$c['cid']);break;
            case 'AUTHCID': $e = urlencode(auth_encode($a['cid'],$_time));break;
            case 'CDIR':    $e = $c['dir'];break;
            case 'CDIRS':   $e = $c['dirs'];break;

            case 'TIME':	$e = $a['pubdate'];break;
            case 'YY':		$e = get_date($a['pubdate'],'y');break;
            case 'YYYY':	$e = get_date($a['pubdate'],'Y');break;
            case 'M':		$e = get_date($a['pubdate'],'n');break;
            case 'MM':		$e = get_date($a['pubdate'],'m');break;
            case 'D':		$e = get_date($a['pubdate'],'j');break;
            case 'DD':		$e = get_date($a['pubdate'],'d');break;

            case 'NAME':    $e = urlencode(iSecurity::escapeStr($a['name']));break;
            case 'TITLE':   $e = urlencode(iSecurity::escapeStr($a['title']));break;
            case 'ZH_CN':	$e = ($a['name']?$a['name']:$a['title']);break;
            case 'TKEY':    $e = $a['tkey'];break;
            case 'LINK':    $e = $a['clink'];break;

            case 'TCID':	$e = $tc['tcid'];break;
            case 'TCDIR':	$e = $tc['dir'];break;

            case 'EXT':		$e = $c['htmlext']?$c['htmlext']:self::$config['ext'];break;
            case 'P':       $e = self::PAGE_SIGN;break;
            default:
                $key = strtolower($b);
                $a[$key] && $e = $a[$key];
        }
        return $e;
    }
    public static function rule_data($C,$key) {
        if(empty($C['mode'])||$C['password']){
            return '{PHP}';
        }else{
            is_object($C['rule']) && $C['rule'] = (array)$C['rule'];
            is_array($C['rule'])  OR $C['rule'] = json_decode($C['rule'],true);

            return $C['rule'][$key];
        }
    }
    public static function get($uri,$a=array(),$type=null) {
        $i          = new stdClass();
        $default    = array();
        $category   = array();
        $array      = (array)$a;
        $app_conf   = self::$config['iurl'][$uri];
        $type === null && $type = $app_conf['rule'];

        switch($type) {
            case '0':
                $i->href = $array['url'];
                $url     = $array['rule'];
            break;
            case '1'://分类
                $category = $array;
                $i->href  = $category['url'];
                $url      = self::rule_data($category,'index');
                $purl     = self::rule_data($category,'list');
            break;
            case '2'://内容
                $array    = (array)$a[0];
                $category = (array)$a[1];
                $i->href  = $array['url'];
                $url      = self::rule_data($category,$uri);
            break;
            case '3'://标签
                $array     = (array)$a[0];
                $category  = (array)$a[1];
                $_category = (array)$a[2];
                $i->href   = $array['url'];
                $category && $url = self::rule_data($category,$uri);

                if($_category['rule'][$uri]){
                    $url = self::rule_data($_category,$uri);
                }
            break;
            case '4'://自定义
                $array    = (array)$a[0];
                $category = (array)$a[1];
                $i->href  = $array['url'];
                $url      = self::rule_data($category,$uri);
                $href     = 'index.php?app='.$uri;
            break;
            default:
                $url  = '{PHP}';
                $href = 'index.php?app='.$uri;
            break;
        }

        $default  = self::$config[$uri];
        if($default){
            $router_dir = $default['dir'];
            $router_url = $default['url'];
            empty($url) && $url = $default['rule'];
        }
        empty($router_url) && $router_url = self::$config['url'];
        empty($router_dir) && $router_dir = self::$config['dir'];
        //[xxxxx]类自定链接优先
        if($array['clink']){
            preg_match('/\[(.+)\]/', $array['clink'], $match);
            isset($match[1]) && $url = $match[1];
        }
        if(self::$callback['url']['rule']){
            $url = self::$callback['url']['rule'];
        }
        if($url=='{PHP}'){
            $primary = $app_conf['primary'];
            empty($href) && $href = $uri.'.php';
            if($primary){
                $href.= (strpos($href,'?')===false)?'?':'&';
                $href.= $primary.'='.$array[$primary];
            }
            if($app_conf['page']){
                $i->pageurl = $href.((strpos($href,'?')===false)?'?':'&');
                $i->pageurl.= $app_conf['page'].'='.self::PAGE_SIGN;
                iFS::checkHttp($i->pageurl) OR $i->pageurl = rtrim($router_url,'/').'/'.$i->pageurl;
            }
            iFS::checkHttp($href) OR $href = rtrim($router_url,'/').'/'.$href;
            $i->href = $href;
        }else if(strpos($url,'{PHP}')===false) {
        	self::$ARRAY = array($array,$category,$_category);
            $i = self::build($url,$router_dir,$router_url,$category['htmlext']);
            self::page_sign($i);

            if($purl){
                $ii = self::build($purl,$router_dir,$router_url,$category['htmlext']);
                $i->pageurl  = $ii->href;
                $i->pagepath = $ii->path;
                unset($ii);
            }else{
                $pfile = $i->file;
                if(strpos($pfile,self::PAGE_SIGN)===false) {
                    $pfile = $i->name.'_'.self::PAGE_SIGN.$i->ext;
                }
                $i->pageurl  = $i->hdir.'/'.$pfile ;
                $i->pagepath = $i->dir.'/'.$pfile;
            }
            // call_user_func_array(self::$callback, array($uri,$i,self::$ARRAY,$app_conf));
        }
        if($category['cid'] && self::$callback['domain']){
            $i = call_user_func_array(self::$callback['domain'], array($i,$category['cid'],$router_url));
        }
        if(self::$callback['device']){
            $d = call_user_func_array(self::$callback['device'], array($i));
            $i = (object)array_merge((array)$i,$d);
        }
        if(self::$callback['url']['data']){
            call_user_func_array(self::$callback['url']['data'], array(&$i));
        }
        $i->url = $i->href;
        return $i;
    }

    public static function build($url,$_dir,$_url,$_ext) {
        if(strpos($url,'{')!==false){
            $url = preg_replace_callback("/\{(.*?)\}/",array(__CLASS__,'rule'),$url);
        }

        $i = new stdClass();
        $i->href = $url;
        if(strpos($_dir,'..')===false) {
            $i->href = $_dir.$url;
        }
        $i->href = ltrim(iFS::path($i->href),'/');
        $i->path = rtrim(iFS::path(iPATH.$_dir.$url),'/') ;

        if(iFS::checkHttp($i->href)===false){
            $i->href = rtrim($_url,'/').'/'.$i->href;
        }
        $pathA = pathinfo($i->path);
        $i->hdir = pathinfo($i->href,PATHINFO_DIRNAME);
        $i->dir  = $pathA['dirname'];
        $i->file = $pathA['basename'];
        $i->name = $pathA['filename'];
        $i->ext  = '.'.$pathA['extension'];
        $i->name OR $i->name = $i->file;

        if(empty($i->file)||substr($url,-1)=='/'||empty($pathA['extension'])) {
            $i->name = 'index';
            $i->ext  = self::$config['ext'];
            $_ext && $i->ext = $_ext;
            $i->file = $i->name.$i->ext;
            $i->path = $i->path.'/'.$i->file;
            $i->dir  = dirname($i->path);
            $i->hdir = dirname($i->href.'/'.$i->file);
        }

        return $i;
    }
    public static function page_sign(&$i) {
        // $i->pfile = $i->file;
        // if(strpos($i->file,self::PAGE_SIGN)===false) {
        //     $i->pfile = $i->name.'_'.self::PAGE_SIGN.$i->ext;
        // }
        // $i->pageurl  = $i->hdir.'/'.$i->pfile ;
        // $i->pagepath = $i->dir.'/'.$i->pfile;
        $i->href = str_replace(self::PAGE_SIGN,1,$i->href);
        $i->path = str_replace(self::PAGE_SIGN,1,$i->path);
        $i->file = str_replace(self::PAGE_SIGN,1,$i->file);
        $i->name = str_replace(self::PAGE_SIGN,1,$i->name);
    }
    public static function page_num($path, $page = false) {
        $page === false && $page = $GLOBALS['page'];
        if ($page < 2) {
            return str_replace(array('_'.self::PAGE_SIGN, '&p='.self::PAGE_SIGN), '', $path);
        }
        return str_replace(self::PAGE_SIGN, $page, $path);
    }
    public static function page_url($iurl){
        return iPagination::url($iurl);
    }
    public static function make($QS=null,$url=null) {
        $url OR $url = $_SERVER["REQUEST_URI"];
        if(strpos($url,'router::')!==false) {
            $rkey = substr($url, 8);
            $url  = iURL::router($rkey);
        }
        $parse  = parse_url($url);
        parse_str($parse['query'], $query);

        $output = (array)$QS;
        is_array($QS) OR parse_str($QS, $output);
        foreach ($output as $key => $value) {
            //这个null是字符
            if($value==='null'||$value===null){
                unset($output[$key]);
                unset($query[$key]);
            }
        }
        $query = array_merge((array)$query,(array)$output);
        $parse['query'] = http_build_query($query);

        $PAGE_SIGN = urlencode(self::PAGE_SIGN);
        if(strpos($parse['query'],$PAGE_SIGN)!==false) {
            $parse['query'] = str_replace($PAGE_SIGN,self::PAGE_SIGN, $parse['query']);
        }
        // if(strpos($parse['path'],'.php')===false) {
        //     $path = '';
        //     foreach ($query as $key => $value) {
        //         $path.= $key.'-'.$value;
        //     }
        //     $parse['path'].= $path.self::$config['ext'];
        // }
        $nurl = self::glue($parse);
        return $nurl?$nurl:$url;
    }
    public static function glue($parsed) {
        if (!is_array($parsed)) return false;

        $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
        $uri.= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
        $parsed['host']    && $uri.= $parsed['host'];
        $parsed['port']    && $uri.= ':'.$parsed['port'];
        $parsed['path']    && $uri.= $parsed['path'];
        $parsed['query']   && $uri.= '?'.$parsed['query'];
        $parsed['fragment']&& $uri.= '#'.$parsed['fragment'];
        return $uri;
    }
}
