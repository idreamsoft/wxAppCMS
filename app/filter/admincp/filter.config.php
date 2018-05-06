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
    <div class="widget-title"> <span class="icon"> <i class="fa fa-filter"></i> </span>
    <ul class="nav nav-tabs" id="filter-tab">
      <li class="active"><a href="#tab-disable" data-toggle="tab"><i class="fa fa-strikethrough"></i> 禁用词</a></li>
      <li><a href="#tab-filter" data-toggle="tab"><i class="fa fa-umbrella"></i> 过滤词</a></li>
    </ul>
  </div>
  <div class="widget-content nopadding">
    <form action="<?php echo APP_FURI; ?>&do=save_config" method="post" class="form-inline" id="iCMS-filter" target="iPHP_FRAME">
      <div id="filter" class="tab-content">
        <div id="tab-disable" class="tab-pane active">
          <textarea name="config[disable]" class="span6" style="height: 300px;"><?php echo implode("\n",(array)$config['disable']) ; ?></textarea>
        </div>
        <div id="tab-filter" class="tab-pane hide">
          <textarea name="config[filter]" class="span6" style="height: 300px;"><?php echo implode("\n",(array)$config['filter']) ; ?></textarea>
        </div>
        <span class="help-inline">每行一个<br />
        过滤词格式:过滤词=***</span> </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
