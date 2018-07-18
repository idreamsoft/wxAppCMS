<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
function iCMS_url($vars){
    if(isset($vars['url'])){
        $url = $vars['url'];
        unset($vars['url']);
    }
    $ret = $vars['ret'];
    unset($vars['app'],$vars['as'],$vars['ret']);
    $url = iURL::make($vars,$url);
    if($ret){
        echo $url;
    }
    return $url;
}
