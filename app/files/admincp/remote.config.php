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
<div class="cloud_remote">
    <h3 class="title">远程附件</h3>
    <div class="clearfloat"></div>
    <div class="input-prepend"> <span class="add-on">附件接口</span>
        <input type="text" name="config[sdk][remote][api]" class="span4" id="remote_domain" value="<?php echo $config['sdk']['remote']['api'] ; ?>" />
    </div>
    <span class="help-inline">远程附件接口URL</span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend">
        <span class="add-on">domain</span>
        <input type="text" name="config[sdk][remote][domain]" class="span4" id="cloud_remote_domain" value="<?php echo $config['sdk']['remote']['domain'] ; ?>"/>
    </div>
    <span class="help-inline">空间名称</span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend input-append">
        <span class="add-on">AccessKey</span>
        <input type="text" name="config[sdk][remote][AccessKey]" class="span4" id="cloud_remote_AccessKey" value="<?php echo $config['sdk']['remote']['AccessKey'] ; ?>"/>
        <a class="btn" onclick="$('#cloud_remote_AccessKey').val(iCMS.random(32));"><i class="fa fa-random"></i> 生成随机码</a>
    </div>
    <span class="help-inline">该AccessKey会和接口URL中包含的Token进行比对，从而验证安全性</span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend input-append">
        <span class="add-on">SecretKey</span>
        <input type="text" name="config[sdk][remote][SecretKey]" class="span4" id="cloud_remote_SecretKey" value="<?php echo $config['sdk']['remote']['SecretKey'] ; ?>"/>
        <a class="btn" onclick="$('#cloud_remote_SecretKey').val(iCMS.random(32));"><i class="fa fa-random"></i> 生成随机码</a>
    </div>
    <span class="help-inline">该SecretKey会和接口URL中包含的Token进行比对，从而验证安全性</span>
    <div class="clearfloat mb10"></div>
</div>
<hr />
