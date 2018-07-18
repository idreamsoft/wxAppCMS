<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
function iCMS_site($vars=array()){
    $site          = iCMS::$config['site'];
    $site['title'] = $site['name'];
    $site['404']   = iPHP_URL_404;
    $site['url']   = iCMS_URL;
    $site['tpl']   = iView::$config['template']['dir'];
    $site['page']  = isset($_GET['p'])?(int)$_GET['p']:(int)$_GET['page'];
    $site['urls']  = array(
        "template" => iCMS_URL.'/template',
        "tpl"      => iCMS_URL.'/template/'.iView::$config['template']['dir'],
        "public"   => iCMS_PUBLIC_URL,
        "user"     => iCMS_USER_URL,
        "res"      => iCMS_FS_URL,
        "ui"       => iCMS_PUBLIC_URL.'/ui',
        "avatar"   => iCMS_FS_URL.'avatar/',
    );
    iDevice::domain($site['urls']);

    if(isset($vars['return'])){
        return $site;
    }
    $key = $vars['key']?:'site';
    iView::assign($key,$site);
}
