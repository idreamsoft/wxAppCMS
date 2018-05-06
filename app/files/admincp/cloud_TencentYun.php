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
<div id="TencentYun">
  <h3 class="title">腾讯云万象图片</h3>
  <span class="help-inline">申请地址:<a href="http://www.qcloud.com/product/ci.html?from=iCMS" target="_blank">http://www.qcloud.com/product/ci.html</a></span>
  <div class="clearfloat"></div>
  <div class="input-prepend">
    <span class="add-on">域名</span>
    <input type="text" name="config[sdk][TencentYun][domain]" class="span4" id="cloud_TencentYun_domain" value="<?php echo $config['sdk']['TencentYun']['domain'] ; ?>"/>
  </div>
  <span class="help-inline">云存储访问域名</span>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">APPID</span>
    <input type="text" name="config[sdk][TencentYun][AppId]" class="span4" id="cloud_TencentYun_AppId" value="<?php echo $config['sdk']['TencentYun']['AppId'] ; ?>"/>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">Bucket</span>
    <input type="text" name="config[sdk][TencentYun][Bucket]" class="span4" id="cloud_TencentYun_Bucket" value="<?php echo $config['sdk']['TencentYun']['Bucket'] ; ?>"/>
  </div>
  <span class="help-inline">空间名称</span>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">AccessKey</span>
    <input type="text" name="config[sdk][TencentYun][AccessKey]" class="span4" id="cloud_TencentYun_AccessKey" value="<?php echo $config['sdk']['TencentYun']['AccessKey'] ; ?>"/>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">SecretKey</span>
    <input type="text" name="config[sdk][TencentYun][SecretKey]" class="span4" id="cloud_TencentYun_SecretKey" value="<?php echo $config['sdk']['TencentYun']['SecretKey'] ; ?>"/>
  </div>
  <div class="clearfloat mb10"></div>
</div>
