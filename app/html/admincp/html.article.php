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
        <li><a href="<?php echo APP_URI; ?>&do=category"><i class="fa fa-floppy-o"></i> <b>栏目</b></a></li>
        <li class="active"><a href="javascript:;"><i class="fa fa-floppy-o"></i> <b>文章</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline" id="iCMS-html" target="iPHP_FRAME">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <input type="hidden" name="do" value="createArticle" />
        <input type="hidden" name="frame" value="iPHP" />
        <div id="html-add" class="tab-content">
          <div class="input-prepend input-append"> <span class="add-on">按栏目</span>
            <select name="cid[]" multiple="multiple" class="span3" size="15">
              <option value="all">所 有 栏 目</option>
              <optgroup label="======================================"></optgroup>
              <?php echo category::appid(iCMS_APP_ARTICLE,'cs')->select();?>
            </select>
          </div>
          <hr>
          <div class="input-prepend input-append"><span class="add-on">按时间</span> <span class="add-on"><i class="fa fa-calendar"></i></span>
            <input type="text" class="ui-datepicker" name="startime" value="<?php echo $_GET['startime'] ; ?>" placeholder="开始时间" />
            <span class="add-on"><i class="fa fa-minus"></i></span>
            <input type="text" class="ui-datepicker" name="endtime" value="<?php echo $_GET['endtime'] ; ?>" placeholder="结束时间" />
            <span class="add-on"><i class="fa fa-calendar"></i></span> </div>
          <hr>
          <div class="input-prepend input-append"><span class="add-on">按文章ID</span> <span class="add-on">起始ID</span>
            <input type="text" name="startid" class="span1" id="startId"/>
            <span class="add-on"><i class="fa fa-arrows-h"></i></span> <span class="add-on">结束ID</span>
            <input type="text" name="endid" class="span1" id="endid"/>
            <span class="add-on"><i class="fa fa-filter"></i></span> </div>
          <hr>
          <div class="input-prepend"> <span class="add-on">生成顺序</span>
            <select name="orderby" id="orderby" class="span4 chosen-select">
              <option value=""></option>
              <optgroup label="降序"><?php echo $orderby_option['DESC'];?></optgroup>
              <optgroup label="升序"><?php echo $orderby_option['ASC'];?></optgroup>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 开始</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
