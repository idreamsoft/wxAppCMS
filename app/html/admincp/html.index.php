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
        <li class="active"><a href="javascript:;"><i class="fa fa-floppy-o"></i> <b>首页</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=category"><i class="fa fa-floppy-o"></i> <b>栏目</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=article"><i class="fa fa-floppy-o"></i> <b>文章</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=createIndex" method="post" class="form-inline" id="iCMS-html" target="iPHP_FRAME">
        <div id="html-add" class="tab-content">
          <div class="input-prepend input-append"> <span class="add-on">主页模板</span>
            <input type="text" name="indexTPL" class="span3" id="indexTPL" value="<?php echo iCMS::$config['template']['index']['tpl'] ; ?>"/>
            <?php echo filesAdmincp::modal_btn('模板','indexTPL');?>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">文 件 名</span>
            <input type="text" name="indexName" class="span3" id="indexName" value="<?php echo iCMS::$config['template']['index']['name'] ; ?>"/>
          </div>
          <span class="help-inline"><?php echo iCMS::$config['router']['ext'] ; ?> 首页文件名,一般为<span class="label label-important">index</span> 不用填写文件后缀名</span> </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 生成</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
