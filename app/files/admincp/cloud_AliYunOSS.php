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
<div id="AliYunOSS">
  <h3 class="title">阿里云OSS</h3>
  <span class="help-inline">申请地址:<a href="https://www.aliyun.com/product/oss?spm=iCMS" target="_blank">https://www.aliyun.com/product/oss</a></span>
  <div class="clearfloat"></div>
  <div class="input-prepend">
    <span class="add-on">域名</span>
    <input type="text" name="config[sdk][AliYunOSS][domain]" class="span4" id="cloud_AliYunOSS_domain" value="<?php echo $config['sdk']['AliYunOSS']['domain'] ; ?>"/>
  </div>
  <span class="help-inline">OSS外网域名</span>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">Bucket</span>
    <input type="text" name="config[sdk][AliYunOSS][Bucket]" class="span4" id="cloud_AliYunOSS_Bucket" value="<?php echo $config['sdk']['AliYunOSS']['Bucket'] ; ?>"/>
  </div>
  <span class="help-inline">空间名称</span>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">AccessKey</span>
    <input type="text" name="config[sdk][AliYunOSS][AccessKey]" class="span4" id="cloud_AliYunOSS_AccessKey" value="<?php echo $config['sdk']['AliYunOSS']['AccessKey'] ; ?>"/>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">SecretKey</span>
    <input type="text" name="config[sdk][AliYunOSS][SecretKey]" class="span4" id="cloud_AliYunOSS_SecretKey" value="<?php echo $config['sdk']['AliYunOSS']['SecretKey'] ; ?>"/>
  </div>
  <div class="clearfloat mb10"></div>
</div>
