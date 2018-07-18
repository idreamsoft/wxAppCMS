<?php
defined('iPHP') OR exit('Access Denied');
return array (
  'site' => 
  array (
    'name' => 'wxAppCMS',
    'seotitle' => '给我一套程序，我能搅动互联网',
    'keywords' => 'wxAppCMS,wxAppCMS微信小程序管理系统,微信小程序管理系统,小程序管理系统',
    'description' => 'wxAppCMS 是一套采用 PHP 和 MySQL 构建的高效简洁的微信小程序管理系统,为您的微信小程序提供一个完美的开源解决方案',
    'icp' => '',
  ),
  'router' => 
  array (
    'url' => 'https://www.wxappcms.com',
    'redirect' => '0',
    404 => 'https://www.wxappcms.com/public/404.htm',
    'public' => 'https://www.wxappcms.com/public',
    'user' => 'https://www.wxappcms.com/user',
    'dir' => '/',
    'ext' => '.html',
    'speed' => '5',
    'rewrite' => '0',
    'config' => 
    array (
      'api' => 
      array (
        0 => '/api',
        1 => 'api.php',
      ),
      'comment' => 
      array (
        0 => '/comment',
        1 => 'api.php?app=comment',
      ),
      'favorite' => 
      array (
        0 => '/favorite',
        1 => 'api.php?app=favorite',
      ),
      'favorite:id' => 
      array (
        0 => '/favorite/{id}/',
        1 => 'api.php?app=favorite&id={id}',
      ),
      'forms' => 
      array (
        0 => '/forms',
        1 => 'api.php?app=forms',
      ),
      'forms:save' => 
      array (
        0 => '/forms/save',
        1 => 'api.php?app=forms&do=save',
      ),
      'forms:id' => 
      array (
        0 => '/forms/{id}/',
        1 => 'api.php?app=forms&id={id}',
      ),
      'public:seccode' => 
      array (
        0 => '/public/seccode',
        1 => 'api.php?app=public&do=seccode',
      ),
      'public:agreement' => 
      array (
        0 => '/public/agreement',
        1 => 'api.php?app=public&do=agreement',
      ),
      'search' => 
      array (
        0 => '/search',
        1 => 'api.php?app=search',
      ),
      'user' => 
      array (
        0 => '/user',
        1 => 'api.php?app=user',
      ),
      'user:home' => 
      array (
        0 => '/user/home',
        1 => 'api.php?app=user&do=home',
      ),
      'user:publish' => 
      array (
        0 => '/user/publish',
        1 => 'api.php?app=user&do=manage&pg=publish',
      ),
      'user:article' => 
      array (
        0 => '/user/article',
        1 => 'api.php?app=user&do=manage&pg=article',
      ),
      'user:category' => 
      array (
        0 => '/user/category',
        1 => 'api.php?app=user&do=manage&pg=category',
      ),
      'user:comment' => 
      array (
        0 => '/user/comment',
        1 => 'api.php?app=user&do=manage&pg=comment',
      ),
      'user:inbox' => 
      array (
        0 => '/user/inbox',
        1 => 'api.php?app=user&do=manage&pg=inbox',
      ),
      'user:inbox:uid' => 
      array (
        0 => '/user/inbox/{uid}',
        1 => 'api.php?app=user&do=manage&pg=inbox&user={uid}',
      ),
      'user:manage' => 
      array (
        0 => '/user/manage',
        1 => 'api.php?app=user&do=manage',
      ),
      'user:manage:favorite' => 
      array (
        0 => '/user/manage/favorite',
        1 => 'api.php?app=user&do=manage&pg=favorite',
      ),
      'user:manage:fans' => 
      array (
        0 => '/user/manage/fans',
        1 => 'api.php?app=user&do=manage&pg=fans',
      ),
      'user:manage:follow' => 
      array (
        0 => '/user/manage/follow',
        1 => 'api.php?app=user&do=manage&pg=follow',
      ),
      'user:profile' => 
      array (
        0 => '/user/profile',
        1 => 'api.php?app=user&do=profile',
      ),
      'user:profile:base' => 
      array (
        0 => '/user/profile/base',
        1 => 'api.php?app=user&do=profile&pg=base',
      ),
      'user:profile:avatar' => 
      array (
        0 => '/user/profile/avatar',
        1 => 'api.php?app=user&do=profile&pg=avatar',
      ),
      'user:profile:setpassword' => 
      array (
        0 => '/user/profile/setpassword',
        1 => 'api.php?app=user&do=profile&pg=setpassword',
      ),
      'user:profile:bind' => 
      array (
        0 => '/user/profile/bind',
        1 => 'api.php?app=user&do=profile&pg=bind',
      ),
      'user:profile:custom' => 
      array (
        0 => '/user/profile/custom',
        1 => 'api.php?app=user&do=profile&pg=custom',
      ),
      'user:register' => 
      array (
        0 => '/user/register',
        1 => 'api.php?app=user&do=register',
      ),
      'user:logout' => 
      array (
        0 => '/user/logout',
        1 => 'api.php?app=user&do=logout',
      ),
      'user:login' => 
      array (
        0 => '/user/login',
        1 => 'api.php?app=user&do=login',
      ),
      'user:login:qq' => 
      array (
        0 => '/user/login/qq',
        1 => 'api.php?app=user&do=login&sign=qq',
      ),
      'user:login:wb' => 
      array (
        0 => '/user/login/wb',
        1 => 'api.php?app=user&do=login&sign=wb',
      ),
      'user:login:wx' => 
      array (
        0 => '/user/login/wx',
        1 => 'api.php?app=user&do=login&sign=wx',
      ),
      'user:findpwd' => 
      array (
        0 => '/user/findpwd',
        1 => 'api.php?app=user&do=findpwd',
      ),
      'uid:home' => 
      array (
        0 => '/{uid}/',
        1 => 'api.php?app=user&do=home&uid={uid}',
      ),
      'uid:comment' => 
      array (
        0 => '/{uid}/comment/',
        1 => 'api.php?app=user&do=comment&uid={uid}',
      ),
      'uid:share' => 
      array (
        0 => '/{uid}/share/',
        1 => 'api.php?app=user&do=share&uid={uid}',
      ),
      'uid:favorite' => 
      array (
        0 => '/{uid}/favorite/',
        1 => 'api.php?app=user&do=favorite&uid={uid}',
      ),
      'uid:fans' => 
      array (
        0 => '/{uid}/fans/',
        1 => 'api.php?app=user&do=fans&uid={uid}',
      ),
      'uid:follower' => 
      array (
        0 => '/{uid}/follower/',
        1 => 'api.php?app=user&do=follower&uid={uid}',
      ),
      'uid:cid' => 
      array (
        0 => '/{uid}/{cid}/',
        1 => 'api.php?app=user&do=home&uid={uid}&cid={cid}',
      ),
      'uid:favorite:id' => 
      array (
        0 => '/{uid}/favorite/{id}/',
        1 => 'api.php?app=user&do=favorite&uid={uid}&id={id}',
      ),
    ),
  ),
  'cache' => 
  array (
    'engine' => 'file',
    'host' => '',
    'time' => '300',
    'compress' => '1',
    'page_total' => '300',
    'prefix' => 'iCMS',
  ),
  'FS' => 
  array (
    'url' => 'https://www.wxappcms.com/res/',
    'dir' => 'res',
    'dir_format' => 'Y/m-d/H',
    'allow_ext' => 'gif,jpg,rar,swf,jpeg,png,zip',
  ),
  'thumb' => 
  array (
    'size' => '',
  ),
  'watermark' => 
  array (
    'enable' => '1',
    'mode' => '0',
    'pos' => '9',
    'x' => '10',
    'y' => '10',
    'width' => '140',
    'height' => '140',
    'allow_ext' => 'jpg,jpeg,png',
    'img' => 'watermark.png',
    'transparent' => '80',
    'text' => 'iCMS',
    'font' => '',
    'fontsize' => '24',
    'color' => '#000000',
    'mosaics' => 
    array (
      'width' => '150',
      'height' => '90',
      'deep' => '9',
    ),
  ),
  'publish' => 
  array (
  ),
  'debug' => 
  array (
    'php' => '1',
    'php_trace' => '0',
    'access_log' => '0',
    'tpl' => '1',
    'tpl_trace' => '0',
    'db' => '0',
    'db_trace' => '0',
    'db_explain' => '0',
  ),
  'time' => 
  array (
    'zone' => 'Asia/Shanghai',
    'cvtime' => '0',
    'dateformat' => 'Y-m-d H:i:s',
  ),
  'apps' => 
  array (
    'article' => '1',
    'category' => '2',
    'tag' => '3',
    'comment' => '5',
    'prop' => '6',
    'message' => '7',
    'favorite' => '8',
    'user' => '9',
    'admincp' => '10',
    'config' => '11',
    'files' => '12',
    'menu' => '13',
    'group' => '14',
    'members' => '15',
    'editor' => '16',
    'apps' => '17',
    'former' => '18',
    'patch' => '19',
    'content' => '20',
    'index' => '21',
    'public' => '22',
    'cache' => '23',
    'filter' => '24',
    'plugin' => '25',
    'forms' => '26',
    'weixin' => '27',
    'keywords' => '28',
    'links' => '29',
    'search' => '31',
    'database' => '32',
    'html' => '33',
    'spider' => '34',
    'wxapp' => '100',
    'sitehub' => '101',
  ),
  'other' => 
  array (
    'sidebar_enable' => '1',
    'sidebar' => '1',
  ),
  'system' => 
  array (
    'patch' => '1',
  ),
  'sphinx' => 
  array (
    'host' => '127.0.0.1:9312',
    'index' => 'iCMS_article iCMS_article_delta',
  ),
  'open' => 
  array (
  ),
  'template' => 
  array (
    'index' => 
    array (
      'mode' => '0',
      'rewrite' => '0',
      'tpl' => '{iTPL}/index.htm',
      'name' => 'index',
    ),
    'desktop' => 
    array (
      'tpl' => 'www/desktop',
      'index' => '{iTPL}/index.htm',
      'domain' => 'https://www.wxappcms.com',
    ),
    'mobile' => 
    array (
      'agent' => 'WAP,Smartphone,Mobile,UCWEB,Opera Mini,Windows CE,Symbian,SAMSUNG,iPhone,Android,BlackBerry,HTC,Mini,LG,SonyEricsson,J2ME,MOT',
      'domain' => 'https://www.wxappcms.com',
      'tpl' => 'www/mobile',
      'index' => '{iTPL}/index.htm',
    ),
  ),
  'api' => 
  array (
    'baidu' => 
    array (
      'sitemap' => 
      array (
        'site' => '',
        'access_token' => '',
        'sync' => '0',
      ),
    ),
  ),
  'mail' => 
  array (
    'host' => '',
    'secure' => '',
    'port' => '25',
    'username' => '',
    'password' => '',
    'setfrom' => '',
    'replyto' => '',
  ),
  'article' => 
  array (
    'pic_center' => '1',
    'pic_next' => '0',
    'pageno_incr' => '',
    'markdown' => '0',
    'autoformat' => '0',
    'catch_remote' => '0',
    'remote' => '0',
    'autopic' => '1',
    'autodesc' => '1',
    'descLen' => '100',
    'autoPage' => '0',
    'AutoPageLen' => '',
    'repeatitle' => '0',
    'showpic' => '0',
    'filter' => '0',
    'clink' => '-',
  ),
  'category' => 
  array (
    'domain' => NULL,
  ),
  'tag' => 
  array (
    'rule' => '{PHP}',
    'dir' => '/',
    'tpl' => '{iTPL}/tag.htm',
    'tkey' => '-',
    'url' => 'https://test.wxappcms.com',
  ),
  'comment' => 
  array (
    'enable' => '1',
    'examine' => '0',
    'seccode' => '1',
    'plugin' => 
    array (
      'changyan' => 
      array (
        'enable' => '0',
        'appid' => '',
        'appkey' => '',
      ),
    ),
  ),
  'user' => 
  array (
    'register' => 
    array (
      'enable' => '1',
      'seccode' => '1',
      'interval' => '86400',
    ),
    'login' => 
    array (
      'enable' => '1',
      'seccode' => '1',
      'interval' => '3600',
    ),
    'post' => 
    array (
      'seccode' => '1',
      'interval' => '10',
    ),
    'agreement' => '',
    'coverpic' => '/ui/coverpic.jpg',
    'open' => 
    array (
      'WX' => 
      array (
        'appid' => '',
        'appkey' => '',
        'redirect' => '',
      ),
      'QQ' => 
      array (
        'appid' => '',
        'appkey' => '',
        'redirect' => '',
      ),
      'WB' => 
      array (
        'appid' => '',
        'appkey' => '',
        'redirect' => '',
      ),
      'TB' => 
      array (
        'appid' => '',
        'appkey' => '',
        'redirect' => '',
      ),
    ),
  ),
  'cloud' => 
  array (
  ),
  'hooks' => 
  array (
    'article' => 
    array (
      'body' => 
      array (
        0 => 
        array (
          0 => 'keywordsApp',
          1 => 'HOOK_run',
        ),
        1 => 
        array (
          0 => 'plugin_download',
          1 => 'HOOK',
        ),
        2 => 
        array (
          0 => 'plugin_markdown',
          1 => 'HOOK',
        ),
      ),
    ),
  ),
  'weixin' => 
  array (
    'menu' => 
    array (
      0 => 
      array (
        'type' => 'click',
        'name' => '',
        'key' => '',
      ),
      1 => 
      array (
        'type' => 'click',
        'name' => '',
        'key' => '',
      ),
      2 => 
      array (
        'type' => 'click',
        'name' => '',
        'key' => '',
      ),
    ),
    'appid' => '',
    'appsecret' => '',
    'token' => '',
    'name' => '',
    'account' => '',
    'qrcode' => '',
    'subscribe' => '',
    'unsubscribe' => '',
    'AESKey' => '',
  ),
  'keywords' => 
  array (
    'limit' => '-1',
  ),
  'iurl' => 
  array (
    'article' => 
    array (
      'rule' => '2',
      'primary' => 'id',
      'page' => 'p',
    ),
    'category' => 
    array (
      'rule' => '1',
      'primary' => 'cid',
    ),
    'tag' => 
    array (
      'rule' => '3',
      'primary' => 'id',
    ),
    'index' => 
    array (
      'rule' => '0',
      'primary' => '',
    ),
  ),
);