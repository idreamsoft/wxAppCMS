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
<div class="input-prepend">
    <span class="add-on">水印</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[watermark][enable]" id="watermark_enable" <?php echo $config[ 'watermark'][ 'enable']? 'checked': ''; ?>/>
    </div>
</div>
<span class="help-inline">将在上传的图片附件中加上您在下面设置的图片或文字水印</span>
<div class="clearfloat mb10"></div>
<hr />
<div class="input-prepend">
    <span class="add-on">水印模式</span>
    <div class="switch" data-on-label="马赛克" data-off-label="水印">
        <input type="checkbox" data-type="switch" name="config[watermark][mode]" id="watermark_mode" <?php echo $config[ 'watermark'][ 'mode']? 'checked': ''; ?>/>
    </div>
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">水印位置</span>
    <select name="config[watermark][pos]" id="watermark_pos" class="span3 chosen-select">
        <option value="0">随机位置</option>
        <option value="1">顶部居左</option>
        <option value="2">顶部居中</option>
        <option value="3">顶部居右</option>
        <option value="4">中部居左</option>
        <option value="5">中部居中</option>
        <option value="6">中部居右</option>
        <option value="7">底部居左</option>
        <option value="8">底部居中</option>
        <option value="9">底部居右</option>
        <option value="-1">自定义</option>
    </select>
</div>
<script>
$(function() { iCMS.select('watermark_pos', "<?php echo (int)$config['watermark']['pos'] ; ?>"); });
</script>
<div class="clearfloat mb10"></div>
<div class="input-prepend input-append">
    <span class="add-on">位置偏移</span><span class="add-on" style="width:24px;">X</span>
    <input type="text" name="config[watermark][x]" class="span1" id="watermark_x" value="<?php echo $config['watermark']['x'] ; ?>" />
    <span class="add-on" style="width:24px;">Y</span>
    <input type="text" name="config[watermark][y]" class="span1" id="watermark_y" value="<?php echo $config['watermark']['y'] ; ?>" />
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend input-append">
    <span class="add-on">图片尺寸</span><span class="add-on" style="width:24px;">宽度</span>
    <input type="text" name="config[watermark][width]" class="span1" id="watermark_width" value="<?php echo $config['watermark']['width'] ; ?>" />
    <span class="add-on" style="width:24px;">高度</span>
    <input type="text" name="config[watermark][height]" class="span1" id="watermark_height" value="<?php echo $config['watermark']['height'] ; ?>" />
</div>
<span class="help-inline">单位:像素(px) 只对超过程序设置的大小的附件图片才加上水印图片或文字(设置为0不限制)</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">图片类型</span>
    <input type="text" name="config[watermark][allow_ext]" class="span3" id="watermark_allow_ext" value="<?php echo $config['watermark']['allow_ext'] ; ?>" />
</div>
<span class="help-inline">需要添加水印的图片类型(jpg,jpeg,png)  注:当前版本gif动画添加水印将失效</span>
<div class="clearfloat mb10"></div>
<hr />
<div class="input-prepend">
    <span class="add-on">水印图片文件</span>
    <input type="text" name="config[watermark][img]" class="span3" id="watermark_img" value="<?php echo $config['watermark']['img'] ; ?>" />
</div>
<span class="help-inline">水印图片存放路径：/cache/conf/iCMS/watermark.png， 如果水印图片不存在，则使用文字水印</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">水印透明度</span>
    <input type="text" name="config[watermark][transparent]" class="span3" id="watermark_transparent" value="<?php echo $config['watermark']['transparent'] ; ?>" />
</div>
<hr />
<div class="input-prepend">
    <span class="add-on">水印文字</span>
    <input type="text" name="config[watermark][text]" class="span3" id="watermark_text" value="<?php echo $config['watermark']['text'] ; ?>" />
</div>
<span class="help-inline">如果设置为中文,字体文件必需要支持中文字体 ,存放路径：/cache/conf/iCMS/</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">文字字体</span>
    <input type="text" name="config[watermark][font]" class="span3" id="watermark_font" value="<?php echo $config['watermark']['font'] ; ?>" />
</div>
<span class="help-inline">字体文件</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">水印文字字体大小</span>
    <input type="text" name="config[watermark][fontsize]" class="span3" id="watermark_fontsize" value="<?php echo $config['watermark']['fontsize'] ; ?>" />
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">水印文字颜色</span>
    <input type="text" name="config[watermark][color]" class="span3" id="watermark_color" value="<?php echo $config['watermark']['color'] ; ?>" />
</div>
<span class="help-inline">例#000000 长度必须7位</span>
<hr />
<div class="clearfloat mb10"></div>
<div class="input-prepend input-append">
    <span class="add-on">马赛克尺寸</span>
    <span class="add-on" style="width:30px;">宽度</span>
    <input type="text" name="config[watermark][mosaics][width]" class="span1" id="watermark_mosaics_width" value="<?php echo $config['watermark']['mosaics']['width']?:150 ; ?>" />
    <span class="add-on" style="width:30px;">高度</span>
    <input type="text" name="config[watermark][mosaics][height]" class="span1" id="watermark_mosaics_height" value="<?php echo $config['watermark']['mosaics']['height']?:90 ; ?>" />
</div>
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">马赛克深度</span>
    <input type="text" name="config[watermark][mosaics][deep]" class="span3" id="watermark_mosaics_deep" value="<?php echo $config['watermark']['mosaics']['deep']?:9 ; ?>" />
</div>
<!--
<div class="clearfloat mb10"></div>
<div class="input-prepend">
    <span class="add-on">缩略图水印</span>
    <div class="switch">
        <input type="checkbox" data-type="switch" name="config[watermark][thumb]" id="watermark_thumb" <?php echo $config['watermark']['thumb']?'checked':''; ?>/>
    </div>
</div>
<span class="help-inline">开启时缩略图也会打上水印</span>
-->
