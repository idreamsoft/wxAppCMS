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
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-cloud"></i> </span>
      <h5 class="brs">数据库</h5>
      <ul class="nav nav-tabs iMenu-tabs">
        <?php echo menu::app_memu(admincp::$APP_NAME); ?>
      </ul>
      <script>$(".iMenu-tabs").find('a[href="<?php echo menu::$url; ?>"]').parent().addClass('active');</script>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=query" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <div class="tab-content">
          <div class="alert alert-info mb10">批量替换属直接对操作数据库，存在一定危险性，请慎用!!!</div>
          <div class="input-prepend"> <span class="add-on">字段</span>
            <select name="field" id="field" class="chosen-select">
              <option value="title">标题</option>
              <option value="clink">自定义链接</option>
              <option value="comments">评论数</option>
              <option value="pic">缩略图</option>
              <option value="cid">栏目</option>
              <option value="tkd">标题/关键字/简介</option>
              <option value="body">内容</option>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend" style="width:100%;"><span class="add-on">查找</span>
            <textarea name="pattern" id="pattern" class="span6" style="height: 150px;"></textarea>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend" style="width:100%;"><span class="add-on">替换</span>
            <textarea name="replacement" id="replacement" class="span6" style="height: 150px;"></textarea>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend" style="width:100%;"><span class="add-on">条件</span>
            <textarea name="where" id="where" class="span6" style="height: 150px;"></textarea>
          </div>
    	  <span class="help-inline">只支持SQL语句</span>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 执 行</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
