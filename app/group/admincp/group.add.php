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
<script type="text/javascript">
$(function(){
    iCMS.select('type',"<?php echo $rs->type ; ?>");
});
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-user"></i> </span>
      <h5 class="brs"><?php echo empty($this->gid)?'添加':'修改' ; ?>角色</h5>
      <ul class="nav nav-tabs" id="group-tab">
        <li class="active"><a href="#group-info" data-toggle="tab"><b>基本信息</b></a></li>
        <li><a href="#group-mpriv" data-toggle="tab"><b>后台权限</b></a></li>
        <li><a href="#group-apriv" data-toggle="tab"><b>应用权限</b></a></li>
        <li><a href="#group-cpriv" data-toggle="tab"><b>栏目权限</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-group" target="iPHP_FRAME">
        <input name="gid" type="hidden" value="<?php echo $this->gid; ?>" />
        <div id="group-add" class="tab-content">
          <div id="group-info" class="tab-pane active">
            <div class="input-prepend"> <span class="add-on">角色类型</span>
              <select name="type" id="type" class="chosen-select" data-placeholder="请选择角色类型">
                <option value='0'>会员组[type:0] </option>
                <option value='1'>管理组[type:1] </option>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"> 角 色 名</span>
              <input type="text" name="name" class="span3" id="name" value="<?php echo $rs->name ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
          </div>
          <?php include admincp::view("members.priv","members"); ?>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
