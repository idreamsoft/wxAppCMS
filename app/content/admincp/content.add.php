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
$preview = isset($_GET['preview']);
admincp::head(!$preview);
?>
<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title">
      <span class="icon"> <i class="fa fa-pencil"></i> </span>
      <?php if($preview){?>
            <h5 class="brs">预览表单</h5>
      <?php }else{ ?>
            <h5 class="brs"><?php echo ($this->id?'修改':'添加'); ?><?php echo $app['title'];?></h5>
      <?php } ?>
      <ul class="nav nav-tabs" id="-add-tab">
        <li class="active"><a href="#-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <li><a href="#-add-publish" data-toggle="tab"><i class="fa fa-rocket"></i> 发布设置</a></li>
        <li><a href="#apps-metadata" data-toggle="tab"><i class="fa fa-sitemap"></i> 动态属性</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <input id="appid" name="appid" type="hidden"  value="<?php echo $this->appid;?>" />
        <input name="REFERER" type="hidden" value="<?php echo iPHP_REFERER ; ?>" />
        <div class="tab-content">
          <div id="-add-base" class="tab-pane active">
            <?php former::$layout['publish'] = true;?>
            <?php echo former::layout();?>
          </div>
          <div id="-add-publish" class="tab-pane">
            <?php echo former::layout_publish();?>
          </div>
          <div id="apps-metadata" class="tab-pane hide">
            <?php include admincp::view("apps.meta","apps");?>
          </div>
        </div>
        <?php if($preview){?>
        <?php }else{ ?>
        <?php }?>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
