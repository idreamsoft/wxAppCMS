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
configAdmincp::head("用户系统设置");
?>
<div class="input-prepend">
    <span class="add-on">用户注册</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[register][enable]" id="user_register_enable" <?php echo @$config['register']['enable']?'checked':''; ?>/>
    </div>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">注册验证码</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[register][seccode]" id="user_register_seccode" <?php echo @$config['register']['seccode']?'checked':''; ?>/>
    </div>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend input-append">
    <span class="add-on">注册间隔</span>
    <input type="text" name="config[register][interval]" class="span1" id="user_register_interval" value="<?php echo @(int)$config['register']['interval'] ; ?>"/>
    <span class="add-on" style="width:24px;">秒</span>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">用户登陆</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[login][enable]" id="user_login_enable" <?php echo @$config['login']['enable']?'checked':''; ?>/>
    </div>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">登陆验证码</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[login][seccode]" id="user_login_seccode" <?php echo @$config['login']['seccode']?'checked':''; ?>/>
    </div>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend input-append">
    <span class="add-on">登陆间隔</span>
    <input type="text" name="config[login][interval]" class="span1" id="user_login_interval" value="<?php echo @(int)$config['login']['interval'] ; ?>"/>
    <span class="add-on" style="width:24px;">秒</span>
</div>
<span class="help-inline">登陆错误5次后,重试间隔</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">发贴验证码</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[post][seccode]" id="user_post_seccode" <?php echo $config['post']['seccode']?'checked':''; ?>/>
    </div>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend input-append">
    <span class="add-on">发贴间隔</span>
    <input type="text" name="config[post][interval]" class="span1" id="user_post_interval" value="<?php echo (int)$config['post']['interval'] ; ?>"/>
    <span class="add-on" style="width:24px;">秒</span>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">来路跟随</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[forward]" id="user_forward" <?php echo $config['forward']?'checked':''; ?>/>
    </div>
</div>
<span class="help-inline">开启后注册、登陆的URL中将保留来路数据</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">注册条款</span>
    <textarea name="config[agreement]" id="user_agreement" class="span6" style="height: 150px;"><?php echo $config['agreement'] ; ?></textarea>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">默认封面</span>
    <input type="text" name="config[coverpic]" class="span4" id="user_coverpic" value="<?php echo $config['coverpic'] ; ?>"/>
</div>
<span class="help-inline">请将图片放在public目录下</span>
<div class="clearfloat mb10"></div>
<hr />
<h3 class="title">微信开放平台</h3>
<span class="help-inline">申请地址: https://open.weixin.qq.com</span>
<div class="clearfloat"></div>
<div class="input-prepend">
    <span class="add-on" style="width:60px;">APPID:</span>
    <input type="text" name="config[open][WX][appid]" class="span3" id="wx_appid" value="<?php echo $config['open']['WX']['appid'] ; ?>"/>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on"style="width:60px;">APPKEY:</span>
    <input type="text" name="config[open][WX][appkey]" class="span3" id="wx_appkey" value="<?php echo $config['open']['WX']['appkey'] ; ?>"/>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on"style="width:60px;">回调域名:</span>
    <input type="text" name="config[open][WX][redirect]" class="span3" id="wx_redirect" value="<?php echo $config['open']['WX']['redirect'] ; ?>"/>
</div>
<span class="help-inline">例:https://www.icmsdev.com</span>
<hr />
<h3 class="title">QQ开放平台</h3>
<span class="help-inline">申请地址:http://connect.qq.com</span>
<div class="clearfloat"></div>
<div class="input-prepend">
    <span class="add-on" style="width:60px;">APPID:</span>
    <input type="text" name="config[open][QQ][appid]" class="span3" id="qq_appid" value="<?php echo $config['open']['QQ']['appid'] ; ?>"/>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on"style="width:60px;">APPKEY:</span>
    <input type="text" name="config[open][QQ][appkey]" class="span3" id="qq_appkey" value="<?php echo $config['open']['QQ']['appkey'] ; ?>"/>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on"style="width:60px;">回调域名:</span>
    <input type="text" name="config[open][QQ][redirect]" class="span3" id="qq_redirect" value="<?php echo $config['open']['QQ']['redirect'] ; ?>"/>
</div>
<span class="help-inline">例:https://www.icmsdev.com</span>
<hr />
<h3 class="title">微博开放平台</h3>
<span class="help-inline">申请地址:http://open.weibo.com/authentication</span>
<div class="clearfloat"></div>
<div class="input-prepend">
    <span class="add-on" style="width:60px;">APPID:</span>
    <input type="text" name="config[open][WB][appid]" class="span3" id="WB_appid" value="<?php echo $config['open']['WB']['appid']; ?>"/>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on"style="width:60px;">APPKEY:</span>
    <input type="text" name="config[open][WB][appkey]" class="span3" id="WB_appkey" value="<?php echo $config['open']['WB']['appkey'] ; ?>"/>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on"style="width:60px;">回调域名:</span>
    <input type="text" name="config[open][WB][redirect]" class="span3" id="WB_redirect" value="<?php echo $config['open']['WB']['redirect'] ; ?>"/>
</div>
<span class="help-inline">例:https://www.icmsdev.com</span>
<hr />
<h3 class="title">淘宝开放平台</h3>
<span class="help-inline">申请地址:http://open.taobao.com</span>
<div class="clearfloat"></div>
<div class="input-prepend">
    <span class="add-on" style="width:60px;">APPID:</span>
    <input type="text" name="config[open][TB][appid]" class="span3" id="TB_appid" value="<?php echo $config['open']['TB']['appid']; ?>"/>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on"style="width:60px;">APPKEY:</span>
    <input type="text" name="config[open][TB][appkey]" class="span3" id="TB_appkey" value="<?php echo $config['open']['TB']['appkey'] ; ?>"/>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on"style="width:60px;">回调域名:</span>
    <input type="text" name="config[open][TB][redirect]" class="span3" id="TB_redirect" value="<?php echo $config['open']['TB']['redirect'] ; ?>"/>
</div>
<span class="help-inline">例:https://www.icmsdev.com</span>
<?php configAdmincp::foot();?>
