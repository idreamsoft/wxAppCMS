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
configAdmincp::head("标签系统设置");
?>
<!-- <div class="input-prepend">
  <span class="add-on">标签URL</span>
  <input type="text" name="config[url]" class="span4" id="url" value="<?php echo $config['url'] ; ?>"/>
</div>
<span class="help-inline">标签目录访问URL 可绑定域名</span>
<div class="clearfloat mb10"></div> -->
<div class="input-prepend input-append">
  <span class="add-on">标签URL规则</span>
  <input type="text" name="config[rule]" class="span4" id="rule" value="<?php echo $config['rule'] ; ?>"/>
  <div class="btn-group">
    <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-question-circle"></i> 帮助</a>
    <ul class="dropdown-menu">
      <li><a href="{ID}" data-toggle="insertContent" data-target="#rule"><span class="label label-important">{ID}</span> 标签ID</a></li>
      <li><a href="{TKEY}" data-toggle="insertContent" data-target="#rule"><span class="label label-important">{TKEY}</span> 标签标识</a></li>
      <li><a href="{ZH_CN}" data-toggle="insertContent" data-target="#rule"><span class="label label-important">{ZH_CN}</span> 标签名(中文)</a></li>
      <li><a href="{NAME}" data-toggle="insertContent" data-target="#rule"><span class="label label-important">{NAME}</span> 标签名</a></li>
      <li class="divider"></li>
      <li><a href="{TCID}" data-toggle="insertContent" data-target="#rule"><span class="label label-inverse">{TCID}</span> 分类ID</a></li>
      <li><a href="{TCDIR}" data-toggle="insertContent" data-target="#rule"><span class="label label-inverse">{TCDIR}</span> 分类目录</a></li>
      <li><a href="{CDIR}" data-toggle="insertContent" data-target="#rule"><span class="label label-inverse">{CDIR}</span> 栏目目录</a></li>
      <li class="divider"></li>
      <li><a href="{P}" data-toggle="insertContent" data-target="#rule"><span class="label label-inverse">{P}</span> 分页数</a></li>
      <li><a href="{EXT}" data-toggle="insertContent" data-target="#rule"><span class="label label-inverse">{EXT}</span> 后缀</a></li>
      <li class="divider"></li>
      <li><a href="{PHP}" data-toggle="insertContent" data-target="#rule"><span class="label label-inverse">{PHP}</span> 动态程序</a></li>
    </ul>
  </div>
</div>
<div class="help-inline">伪静态模式时规则一定要包含<span class="label label-important">{ID}</span>或<span class="label label-important">{NAME}</span>或<span class="label label-important">{ZH_CN}</span>或<span class="label label-important">{TKEY}</span></div>
<!-- <div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">标签目录</span>
  <input type="text" name="config[dir]" class="span4" id="dir" value="<?php echo $config['dir'] ; ?>"/>
</div>
<span class="help-inline">存放标签静态页面目录，相对于app目录。可用../表示上级目录</span>
 --><div class="clearfloat mb10"></div>
<div class="input-prepend input-append">
  <span class="add-on">标签模板</span>
  <input type="text" name="config[tpl]" class="span4" id="tpl" value="<?php echo $config['tpl'] ; ?>"/>
  <?php echo filesAdmincp::modal_btn('模板','tpl');?>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend"> <span class="add-on">TKEY分割符</span>
  <input type="text" name="config[tkey]" class="span4" id="tkey" value="<?php echo $config['tkey'] ; ?>"/>
</div>
<span class="help-inline">留空，按紧凑型生成(pinyin)</span>
<div class="mt20"></div>
<div class="alert alert-block">
  此配置为标签的URL默认配置<br />
  标签规则优先级
  标签自定义链接 > 标签分类 > 标签所属栏目 > 标签系统设置
</div>
<?php configAdmincp::foot();?>
