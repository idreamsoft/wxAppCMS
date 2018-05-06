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
configAdmincp::head("评论系统配置");
?>
<div class="input-prepend">
    <span class="add-on">评论</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[enable]" id="comment_enable" <?php echo $config['enable']?'checked':''; ?>/>
    </div>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">审核评论</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[examine]" id="comment_examine" <?php echo $config['examine']?'checked':''; ?>/>
    </div>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">验证码</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[seccode]" id="comment_seccode" <?php echo $config['seccode']?'checked':''; ?>/>
    </div>
</div>
<span class="help-inline">开启后发表评论需要验证码</span>

<?php configAdmincp::foot();?>
