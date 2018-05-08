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
class iHttp{
    public static $PROXY_URL = null;

    public static $CURL_COUNT             = 3;
    public static $CURL_HTTP_CODE         = null;
    public static $CURL_CONTENT_TYPE      = null;
    public static $CURL_PROXY             = null;
    public static $CURL_PROXY_ARRAY       = array();

    public static $CURLOPT_ENCODING       = '';
    public static $CURLOPT_REFERER        = null;
    public static $CURLOPT_TIMEOUT        = 10; //数据传输的最大允许时间
    public static $CURLOPT_CONNECTTIMEOUT = 3; //连接超时时间
    public static $CURLOPT_USERAGENT      = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.122 Safari/537.36';
    public static $CURLOPT_COOKIEFILE     = null;
    public static $CURLOPT_COOKIEJAR      = null;
    public static $CURLOPT_HTTPHEADER     = null;

    protected static $_count  = 0;

    public static function is_ajax() {
        return (
            $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"||
            $_SERVER["X-Requested-With"] == "XMLHttpRequest"||
            isset($_GET['ajax'])||isset($_POST['ajax'])||
            isset($_GET['is_ajax'])||isset($_POST['is_ajax'])||
            $_GET['format']=='json'||$_POST['format']=='json'
        );
    }
    public static function proxy_test() {
        $options = array(
            CURLOPT_URL => 'http://www.baidu.com',
            CURLOPT_REFERER => 'http://www.baidu.com',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_NOSIGNAL => true,
            // CURLOPT_DNS_USE_GLOBAL_CACHE => true,
            // CURLOPT_DNS_CACHE_TIMEOUT => 86400,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            // CURLOPT_FOLLOWLOCATION => 1,// 使用自动跳转
            // CURLOPT_MAXREDIRS => 7,//查找次数，防止查找太深
        );
        if (empty(self::$CURL_PROXY_ARRAY)) {
            if (empty(self::$CURL_PROXY)) {
                return false;
            }
            self::$CURL_PROXY_ARRAY = explode("\n", self::$CURL_PROXY); // socks5://127.0.0.1:1080@username:password
        }
        if (empty(self::$CURL_PROXY_ARRAY)) {
            return false;
        }
        $rand_keys = array_rand(self::$CURL_PROXY_ARRAY, 1);
        $proxy = self::$CURL_PROXY_ARRAY[$rand_keys];
        $proxy = trim($proxy);
        $options = self::proxy($options, $proxy);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] == 200) {
            return $proxy;
        } else {
            unset(self::$CURL_PROXY_ARRAY[$rand_keys]);
            return self::proxy_test();
        }
    }
    public static function proxy($options = array(), $proxy) {
        if ($proxy) {
            $proxy = trim($proxy);
            $matches = strpos($proxy, 'socks5://');
            if ($matches === false) {
                // $options[CURLOPT_HTTPPROXYTUNNEL] = true;//HTTP代理开关
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP; //使用http代理模式
            } else {
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
            }
            list($url, $auth) = explode('@', $proxy);
            $url = str_replace(array('http://', 'socks5://'), '', $url);
            $options[CURLOPT_PROXY] = $url;
            $auth && $options[CURLOPT_PROXYUSERPWD] = $auth; //代理验证格式  username:password
            $options[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC; //代理认证模式
        }
        return $options;
    }
    public static function get($url) {
        return self::remote($url);
    }
    public static function post($url,$postdata) {
        return self::remote($url,$postdata);
    }
    /**
     * [上传文件]
     * @param  [type] $url      [description]
     * @param  array  $files    [description]
     * @param  [type] $postdata [description]
     * @return [type]           [description]
     */
    public static function upload($url,array $files,$postdata) {
        return self::remote($url,$postdata,$files);
    }
    //获取远程页面的内容
    public static function remote($url, $postdata = null,$files = array()) {
        $url = str_replace(array(' ','&amp;'), array('%20','&'), $url);

        if (function_exists('curl_init')) {
            if (empty($url)) {
                echo 'remote:(' . self::$_count . ')' . $url . "\n";
                echo "url:empty\n";
                return false;
            }
            if (self::$CURLOPT_REFERER === null) {
                $uri = parse_url($url);
                self::$CURLOPT_REFERER = $uri['scheme'] . '://' . $uri['host'];
            }
            $options = array(
                CURLOPT_URL                     => $url,
                CURLOPT_REFERER                 => self::$CURLOPT_REFERER,
                CURLOPT_USERAGENT               => self::$CURLOPT_USERAGENT,
                CURLOPT_ENCODING                => self::$CURLOPT_ENCODING,
                CURLOPT_TIMEOUT                 => self::$CURLOPT_TIMEOUT, //数据传输的最大允许时间
                CURLOPT_CONNECTTIMEOUT          => self::$CURLOPT_CONNECTTIMEOUT, //连接超时时间
                CURLOPT_RETURNTRANSFER          => 1,
                CURLOPT_FAILONERROR             => 0,
                CURLOPT_HEADER                  => 0,
                CURLOPT_NOSIGNAL                => true,
                // CURLOPT_DNS_USE_GLOBAL_CACHE => true,
                // CURLOPT_DNS_CACHE_TIMEOUT    => 86400,
                CURLOPT_SSL_VERIFYPEER          => false,
                CURLOPT_SSL_VERIFYHOST          => false,
                // CURLOPT_FOLLOWLOCATION       => 1,// 使用自动跳转
                // CURLOPT_MAXREDIRS            => 7,//查找次数，防止查找太深
            );
            if (self::$PROXY_URL) {
                $options[CURLOPT_URL] = self::$PROXY_URL.urlencode($url);
            }
            $options[CURLOPT_POST] = 0;

            if (is_array($files)) {
                if (class_exists('CURLFile',false)) {
                    defined('CURLOPT_SAFE_UPLOAD') && $options[CURLOPT_SAFE_UPLOAD] = 1;
                    foreach ($files as $key => $value) {
                        $postdata[$key] = new CURLFile($value);
                    }
                } else {
                    if (defined('CURLOPT_SAFE_UPLOAD')) {
                        if (version_compare('5.6',PHP_VERSION,'>=')) {
                            $options[CURLOPT_SAFE_UPLOAD] = 0;
                        }
                    }
                    foreach ($files as $key => $value) {
                        $postdata[$key] = "@$value";
                    }
                }
            }
            if ($postdata!==null) {
                $options[CURLOPT_POST] = 1;
                $options[CURLOPT_POSTFIELDS] = $postdata;
            }

            if (self::$CURLOPT_COOKIEFILE) {
                $options[CURLOPT_COOKIEFILE] = self::$CURLOPT_COOKIEFILE;
            }

            if (self::$CURLOPT_COOKIEJAR) {
                $options[CURLOPT_COOKIEJAR] = self::$CURLOPT_COOKIEJAR;
            }
            if (self::$CURLOPT_HTTPHEADER) {
                $options[CURLOPT_HTTPHEADER] = self::$CURLOPT_HTTPHEADER;
            }

            if (self::$CURL_PROXY) {
                $proxy = self::proxy_test();
                $proxy && $options = self::proxy($options, $proxy);
            }
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
                $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
            }
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $responses = curl_exec($ch);
            $info  = curl_getinfo($ch);
            $errno = curl_errno($ch);
            if (self::$CURL_HTTP_CODE !== null) {
                if (self::$CURL_HTTP_CODE == $info['http_code']) {
                    return $responses;
                }
            }

            if ($info['http_code'] == 404 || $info['http_code'] == 500) {
                curl_close($ch);
                echo $url . "\n";
                echo "http_code:" . $info['http_code'] . "\n";
                unset($responses, $info);
                return false;
            }
            if (($info['http_code'] == 301 || $info['http_code'] == 302) && self::$_count < self::$CURL_COUNT) {
                $newurl = $info['redirect_url'];
                if (empty($newurl)) {
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    $header = curl_exec($ch);
                    preg_match('|Location: (.*)|i', $header, $matches);
                    $newurl = ltrim($matches[1], '/');
                    if (empty($newurl)) {
                        return false;
                    }

                    if (!strstr($newurl, 'http://')) {
                        $host = $uri['scheme'] . '://' . $uri['host'];
                        $newurl = $host . '/' . $newurl;
                    }
                }
                $newurl = trim($newurl);
                curl_close($ch);
                unset($responses, $info);
                self::$_count++;
                return self::remote($newurl, $postdata,$filepath);
            }

            if (self::$CURL_CONTENT_TYPE !== null && $info['content_type']) {
                if (stripos($info['content_type'], self::$CURL_CONTENT_TYPE) === false) {
                    curl_close($ch);
                    echo $url . "\n";
                    echo "content_type:" . $info['content_type'] . "\n";
                    unset($responses, $info);
                    return false;
                }
            }

            if ($errno > 0 || empty($responses) || empty($info['http_code'])) {
                if (self::$_count < self::$CURL_COUNT) {
                    self::$_count++;
                    curl_close($ch);
                    unset($responses, $info);
                    return self::remote($url, $postdata,$filepath);
                } else {
                    $curl_error = curl_error($ch);
                    curl_close($ch);
                    unset($responses, $info);
                    echo $url . " remote:".self::$_count."\n";
                    echo "cURL Error ($errno): $curl_error\n";
                    return false;
                }
            }
            curl_close($ch);
        } elseif (ini_get('allow_url_fopen') && ($handle = fopen($url, 'rb'))) {
            if (function_exists('stream_get_contents')) {
                $responses = stream_get_contents($handle);
            } else {
                while (!feof($handle) && connection_status() == 0) {
                    $responses .= fread($handle, 8192);
                }
            }
            fclose($handle);
        } else {
            $responses = file_get_contents(urlencode($url));
        }
        return $responses;
    }
    public static function send($url, $POSTFIELDS=null,$ret=false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

        if($POSTFIELDS){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
        }

        $response = curl_exec ($ch);
        // self::$debug && var_dump($response);
        curl_close ($ch);

        if($ret){
            return $response;
        }
        if(empty($response)){
            return '0000000';
        }
        return json_decode($response);
    }
}
