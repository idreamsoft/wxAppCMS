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
	iCMS.select('pid',"<?php echo $rs['pid'] ; ?>");
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->cid)?'添加':'修改' ; ?>分类</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-pushcategory" target="iPHP_FRAME">
        <input name="status" type="hidden" value="<?php echo $rs['status']  ; ?>" />
        <input name="cid" type="hidden" value="<?php echo $rs['cid']  ; ?>" />
        <div id="pushcategory-add" class="tab-content">
          <div class="input-prepend"> <span class="add-on">上级分类</span>
            <?php if(category::check_priv($rootid,'a') && empty($rootid)) {   ?>
            <select name="rootid" class="chosen-select">
              <option value="0">======顶级分类=====</option>
              <?php echo category::priv('ca')->select($rootid,0,1,true);?>
            </select>
            <?php }else {  ?>
            <input name="_rootid_hash" type="hidden" value="<?php echo auth_encode($rootid) ; ?>" />
            <input name="rootid" id="rootid" type="hidden" value="<?php echo $rootid ; ?>" />
            <input readonly="true" value="<?php echo category::get($rootid)->name ; ?>" type="text" class="txt" />
            <?php }  ?>
          </div>
          <span class="help-inline">本分类的上级分类或分类</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend input-append"> <span class="add-on">分类属性</span>
            <select name="pid" id="pid" class="chosen-select">
              <option value="0">普通分类[pid='0']</option>
              <?php echo propAdmincp::get("pid",$rs['pid']) ; ?>
            </select>
            <?php echo propAdmincp::btn_add('添加常用属性');?>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">分类名称</span>
            <input type="text" name="name" class="span4" id="name" value="<?php echo $rs['name'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">分类排序</span>
            <input id="sortnum" class="span1" value="<?php echo $rs['sortnum'] ; ?>" name="sortnum" type="text"/>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
