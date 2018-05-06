<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class filterApp{
    public function __construct() {
        $this->appid = iCMS_APP_FILTER;
    }
    /**
     * [查找禁用词,返回true或false]
     * @param [string] $content [参数]
     * @return [string]         [返回禁用词]
     */
    public static function HOOK_disable_FALSE($content){
        $disable = iCache::get('filter/disable');  //disable禁止
        //禁止关键词
        $subject = implode('', (array)$content);
        $pattern = '/(~|`|!|@|\#|\$|%|\^|&|\*|\(|\)|\-|=|_|\+|\{|\}|\[|\]|;|:|"|\'|<|>|\?|\/|,|\.|\s|\n|。|，|、|；|：|？|！|…|-|·|ˉ|ˇ|¨|‘|“|”|々|～|‖|∶|＂|＇|｀|｜|〃|〔|〕|〈|〉|《|》|「|」|『|』|．|〖|〗|【|】|（|）|［|］|｛|｝|°|′|″|＄|￡|￥|‰|％|℃|¤|￠|○|§|№|☆|★|○|●|◎|◇|◆|□|■|△|▲|※|→|←|↑|↓|〓|＃|＆|＠|＾|＿|＼|№|)*/i';
        $subject = preg_replace($pattern, '', $subject);
        foreach ((array)$disable AS $val) {
            $val = trim($val);
            if(strpos($val,'::')!==false){
                list($tag,$start,$end) = explode('::',$val);
                if($tag=='NUM'){
                    $subject = cnum($subject);
                    if (preg_match('/\d{'.$start.','.$end.'}/i', $subject)) {
                        return $val;
                    }
                }
            }else{
                if ($val && preg_match("/".preg_quote($val, '/')."/i", $subject)) {
                    return $val;
                }
            }
        }
    }
    /**
     * [关键词替换过滤]
     * @param [sting] $content [参数]
     * @return [string]        [返回替换过的内容]
     */
    public static function HOOK_filter($content){
        $filter  = iCache::get('filter/array');    //filter过滤
        if($filter){
            //过滤关键词
            foreach ((array)$filter AS $k =>$val) {
                $val = trim($val);
                if($val){
                    $exp = explode("=", $val);
                    empty($exp[1]) && $exp[1] = '***';
                    $search[$k]  = '/'.preg_quote($exp[0], '/').'/i';
                    $replace[$k] = $exp[1];
                }

            }
            $search && $content = preg_replace($search,$replace,$content);
        }
        return $content;
    }

    /**
     * [run 先判断后过滤]
     * @param  [array] &$content [引用内容]
     * @return [sting]           [返回内容]
     */
    public static function run(&$content){
        if($result = self::HOOK_disable_FALSE($content)){
            return $result;
        }

        $content = self::HOOK_filter($content);
    }
}
