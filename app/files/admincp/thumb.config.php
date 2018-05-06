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
<!--<div class="input-prepend"> <span class="add-on">缩略图</span>
<div class="switch">
<input type="checkbox" data-type="switch" name="config[thumb][enable]" id="thumb_enable" <?php echo $config['thumb']['enable']?'checked':''; ?>/>
</div>
</div>
<div class="clearfloat mb10"></div> -->
<div class="input-prepend"> <span class="add-on">缩略图尺寸</span>
<textarea name="config[thumb][size]" id="thumb_size" class="span6" style="height: 90px;"><?php echo $config['thumb']['size'] ; ?></textarea>
</div>
<div class="clearfloat mb10"></div>
<span class="help-inline"><a class="btn btn-small btn-success" href="https://www.icmsdev.com/docs/thumb.html" target="_blank"><i class="fa fa-question-circle"></i> 缩略图配置帮助</a>　每行一个尺寸；格式:300x300．没有在本列表中的缩略图尺寸，都将直接返回原图！防止空间被刷暴</span>
