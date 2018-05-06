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
configAdmincp::head("文章系统设置");
?>
<div class="input-prepend">
  <span class="add-on">文章图片标题</span>
  <div class="switch" data-on-label="启用" data-off-label="关闭">
    <input type="checkbox" data-type="switch" name="config[img_title]" id="article_img_title" <?php echo $config['img_title']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">启用后 可以自定义文章正文内图片的title和alt,关闭后 系统将直接替换成文章标题</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">文章图片居中</span>
  <div class="switch" data-on-label="启用" data-off-label="关闭">
    <input type="checkbox" data-type="switch" name="config[pic_center]" id="article_pic_center" <?php echo $config['pic_center']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">启用后 文章内的图片会自动居中</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">文章图片链接</span>
  <div class="switch" data-on-label="启用" data-off-label="关闭">
    <input type="checkbox" data-type="switch" name="config[pic_next]" id="article_pic_next" <?php echo $config['pic_next']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">启用后 文章内的图片都会带上下一页的链接和点击图片进入下一页的链接</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">文章分页+N</span>
  <input type="text" name="config[pageno_incr]" class="span3" id="article_pageno_incr" value="<?php echo $config['pageno_incr'] ; ?>"/>
</div>
<span class="help-inline">设置此项后,内容分页数比实际页数+N页,不增加请设置为0</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">编辑器</span>
  <div class="switch" data-on-label="Editor.md" data-off-label="UEditor">
    <input type="checkbox" data-type="switch" name="config[markdown]" id="article_markdown" <?php echo $config['markdown']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">Editor.md为markdown编辑器,默认使用UEditor</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">自动排版</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[autoformat]" id="article_autoformat" <?php echo $config['autoformat']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">开启后发布文章时,程序会自动对内容进行清理无用代码.采集时推荐开启.如果内容格式丢失 请关闭此项</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">编辑器图片</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[catch_remote]" id="article_catch_remote" <?php echo $config['catch_remote']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">开启后发表文章时只要有图片 就会自动下载</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">下载远程图片</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[remote]" id="article_remote" <?php echo $config['remote']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">开启后发表文章时该选项默认为选中状态</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">提取缩略图</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[autopic]" id="article_autopic" <?php echo $config['autopic']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">开启后发表文章时该选项默认为选中状态</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">提取摘要</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[autodesc]" id="article_autodesc" <?php echo $config['autodesc']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">开启后发表文章时程序会自动提取文章部分内容为文章摘要</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">提取摘要字数</span>
  <input type="text" name="config[descLen]" class="span3" id="article_descLen" value="<?php echo $config['descLen'] ; ?>"/>
</div>
<span class="help-inline">设置自动提取内容摘要字数</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">内容自动分页</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[autoPage]" id="article_autoPage" <?php echo $config['autoPage']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">开启后发表文章时程序会分页</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">内容分页字数</span>
  <input type="text" name="config[AutoPageLen]" class="span3" id="article_AutoPageLen" value="<?php echo $config['AutoPageLen'] ; ?>"/>
</div>
<span class="help-inline">设置自动内容分页字数</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">检查标题重复</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[repeatitle]" id="article_repeatitle" <?php echo $config['repeatitle']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">开启后不能发表相同标题的文章</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">列表显示图片</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[showpic]" id="article_showpic" <?php echo $config['showpic']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">开启后文章列表将会显示缩略图</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">后台文章过滤</span>
  <select name="config[filter][]" id="article_filter" class="chosen-select span6" multiple="multiple">
    <option value="title:标题">标题</option>
    <option value="description:简介">简介</option>
    <option value="body:内容">内容</option>
    <option value="tags:标签">标签</option>
    <option value="stitle:短标题">短标题</option>
    <option value="keywords:关键字">关键字</option>
  </select>
</div>
<script>
$(function(){
  iCMS.select('article_filter',"<?php echo implode(',', (array)$config['filter']);?>");
})
</script>
<span class="help-inline">开启台 后台输入的文章相关字段都将经过关键字过滤</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">字符分隔符</span>
  <input type="text" name="config[clink]" class="span3" id="article_clink" value="<?php echo $config['clink'] ; ?>"/>
</div>
<span class="help-inline">文章自定义链接字符分隔符</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
  <span class="add-on">emoji表情转换</span>
  <select name="config[emoji]" id="article_emoji" class="chosen-select span3">
    <option value="">不转换</option>
    <option value="unicode">unicode</option>
    <option value="clean">清除</option>
  </select>
</div>
<script>
$(function(){
  iCMS.select('article_emoji',"<?php echo $config['article_emoji'];?>");
})
</script>
<span class="help-inline">文章内容出现emoji表情,会出现内容被截断的数据,可选相关处理方法</span>
<?php configAdmincp::foot();?>
