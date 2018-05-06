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
?>
<div id="QiNiuYun">
    <h3 class="title">七牛云存储</h3>
    <span class="help-inline">申请地址:<a href="https://portal.qiniu.com/signup?from=iCMS" target="_blank">https://portal.qiniu.com/signup</a></span>
    <div class="clearfloat"></div>
    <div class="input-prepend">
        <span class="add-on">域名</span>
        <input type="text" name="config[sdk][QiNiuYun][domain]" class="span4" id="cloud_QiNiuYun_domain" value="<?php echo $config['sdk']['QiNiuYun']['domain'] ; ?>"/>
    </div>
    <span class="help-inline">云存储访问域名</span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend">
        <span class="add-on">Bucket</span>
        <input type="text" name="config[sdk][QiNiuYun][Bucket]" class="span4" id="cloud_QiNiuYun_Bucket" value="<?php echo $config['sdk']['QiNiuYun']['Bucket'] ; ?>"/>
    </div>
    <span class="help-inline">空间名称</span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend">
        <span class="add-on">AccessKey</span>
        <input type="text" name="config[sdk][QiNiuYun][AccessKey]" class="span4" id="cloud_QiNiuYun_AccessKey" value="<?php echo $config['sdk']['QiNiuYun']['AccessKey'] ; ?>"/>
    </div>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend">
        <span class="add-on">SecretKey</span>
        <input type="text" name="config[sdk][QiNiuYun][SecretKey]" class="span4" id="cloud_QiNiuYun_SecretKey" value="<?php echo $config['sdk']['QiNiuYun']['SecretKey'] ; ?>"/>
    </div>
    <div class="clearfloat mb10"></div>
</div>
