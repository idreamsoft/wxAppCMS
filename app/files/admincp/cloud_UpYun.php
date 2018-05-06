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
<div id="UpYun">
  <h3 class="title">又拍云存储</h3>
  <span class="help-inline">申请地址:<a href="https://console.upyun.com/login/" target="_blank">https://console.upyun.com/login/</a></span>
  <span class="help-inline">要求PHP >= 5.5</span>
  <div class="clearfloat"></div>
  <div class="input-prepend">
    <span class="add-on">域名</span>
    <input type="text" name="config[sdk][UpYun][domain]" class="span4" id="cloud_UpYun_domain" value="<?php echo $config['sdk']['UpYun']['domain'] ; ?>"/>
  </div>
  <span class="help-inline">云存储访问域名</span>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">Bucket</span>
    <input type="text" name="config[sdk][UpYun][Bucket]" class="span4" id="cloud_UpYun_Bucket" value="<?php echo $config['sdk']['UpYun']['Bucket'] ; ?>"/>
  </div>
  <span class="help-inline">账号名称</span>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">操作员</span>
    <input type="text" name="config[sdk][UpYun][AccessKey]" class="span4" id="cloud_UpYun_AccessKey" value="<?php echo $config['sdk']['UpYun']['AccessKey'] ; ?>"/>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend">
    <span class="add-on">密码</span>
    <input type="text" name="config[sdk][UpYun][SecretKey]" class="span4" id="cloud_UpYun_SecretKey" value="<?php echo $config['sdk']['UpYun']['SecretKey'] ; ?>"/>
  </div>
  <div class="clearfloat mb10"></div>
</div>
