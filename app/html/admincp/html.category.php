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
admincp::head();
?>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-file"></i> </span>
      <h5 class="brs">生成静态</h5>
      <ul class="nav nav-tabs" id="html-tab">
        <li><a href="<?php echo APP_URI; ?>&do=index"><i class="fa fa-floppy-o"></i> <b>首页</b></a></li>
        <li class="active"><a href="javascript:;"><i class="fa fa-floppy-o"></i> <b>栏目</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=article"><i class="fa fa-floppy-o"></i> <b>文章</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=createCategory" method="post" class="form-inline" id="iCMS-html" target="iPHP_FRAME">
        <div id="html-add" class="tab-content">
          <div class="input-prepend input-append"> <span class="add-on">选择栏目</span>
            <select name="cid[]" multiple="multiple" class="span3" size="15">
              <option value="all">所 有 栏 目</option>
              <optgroup label="======================================"></optgroup>
              <?php echo category::priv('cs')->select(0,0,1,false,array("mode"=>'1'));?>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <?php /*
        <div class="input-prepend"> <span class="add-on">生成页数</span>
          <input type="text" name="cpn" class="span3" id="cpn" value="10000"/>
        </div>
    	<span class="help-inline"></span>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend input-append"> <span class="add-on">间隔时间</span>
          <input type="text" name="time" class="span3" id="time" value="1"/><span class="add-on">秒</span>
        </div>
    	<span class="help-inline"></span> */ ?>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 开始</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
