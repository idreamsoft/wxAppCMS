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

    <div class="input-prepend"> <span class="add-on">SMTP 主机</span>
        <input type="text" name="config[mail][host]" class="span3" id="mail_host" value="<?php echo $config['mail']['host']; ?>" />
    </div>
    <span class="help-inline">发送邮件的服务器.例如:smtp.qq.com</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend"> <span class="add-on">安全协议</span>
        <input type="text" name="config[mail][secure]" class="span3" id="mail_secure" value="<?php echo $config['mail']['secure']; ?>" />
    </div>
    <span class="help-inline">发送邮件的服务器使用的安全协议.默认为空.可选项"ssl" 或者 "tls"</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend"> <span class="add-on">SMTP 端口</span>
        <input type="text" name="config[mail][port]" class="span3" id="mail_port" value="<?php echo $config['mail']['port']?:'25'; ?>" />
    </div>
    <span class="help-inline">发送邮件的服务器的端口,默认:25</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend"> <span class="add-on">SMTP 账号</span>
        <input type="text" name="config[mail][username]" class="span3" id="mail_username" value="<?php echo $config['mail']['username']; ?>" />
    </div>
    <span class="help-inline">登陆邮件的服务器的账号</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend"> <span class="add-on">账号密码</span>
        <input type="text" name="config[mail][password]" class="span3" id="mail_password" value="<?php echo $config['mail']['password']; ?>" />
    </div>
    <span class="help-inline">登陆邮件的服务器的账号密码</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend"> <span class="add-on">发送账号</span>
        <input type="text" name="config[mail][setfrom]" class="span3" id="mail_setfrom" value="<?php echo $config['mail']['setfrom']; ?>" />
    </div>
    <span class="help-inline">用于发送邮件的账号</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend"> <span class="add-on">联系Email</span>
        <input type="text" name="config[mail][replyto]" class="span3" id="mail_replyto" value="<?php echo $config['mail']['replyto']; ?>" />
    </div>
    <span class="help-inline">用于邮件中回复Email的账号</span>
    <div class="clearfloat mt10"></div>

