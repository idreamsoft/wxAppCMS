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
<style type="text/css">
#field-default .add-on { width: 70px;text-align: right; }
.iCMS_dialog .ui-dialog-content .chosen-container{position: relative;}
.add_table_item{vertical-align: top;margin-top: 5px;}
</style>
<script type="text/javascript">
$(function(){
  $("#iCMS-apps").submit(function(){
    var name =$("#app_name").val();
    if(name==''){
      $("#app_name").focus();
      iCMS.alert("表单名称不能为空");
      return false;
    }
    var app =$("#app_app").val();
    if(app==''){
      $("#app_app").focus();
      iCMS.alert("表单标识不能为空");
      return false;
    }
  });
  $(".add_table_item").click(function(){
    // var clone = $("#table_item").clone();
    // console.log(clone);
      var key = $("#table_list").find('tr').size();
      var tr = $("<tr>");
      for (var i = 0; i < 4; i++) {
          var td = $("<td>");
          td.html('<input type="text" name="table['+key+']['+i+']" class="span2" id="table_'+key+'_'+i+'" value=""/>');
          tr.append(td);
      };
      $("#table_list").append(tr);
  });
})
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title">
      <span class="icon"> <i class="fa fa-pencil"></i> </span>
      <h5 class="brs"><?php echo empty($this->id)?'创建':'修改' ; ?>表单</h5>
      <ul class="nav nav-tabs" id="apps-add-tab">
        <li class="active"><a href="#apps-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <?php if($rs['table'])foreach ($rs['table'] as $key => $tval) {?>
        <li><a href="#apps-add-<?php echo $key; ?>-field" data-toggle="tab"><i class="fa fa-database"></i> <?php echo $tval['label']?$tval['label']:$tval['name']; ?>表字段</a></li>
        <?php }?>
        <?php if(!$rs['table']){?>
        <li><a href="#apps-add-field" data-toggle="tab"><i class="fa fa-cog"></i> 基础字段</a></li>
        <?php }?>
        <li><a href="#apps-add-custom" data-toggle="tab"><i class="fa fa-cog"></i> 字段编辑器</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-apps" target="iPHP_FRAME">
        <input name="_id" type="hidden" value="<?php echo $this->id; ?>" />
        <div id="apps-add" class="tab-content">
          <div id="apps-add-base" class="tab-pane active">
            <div class="input-prepend">
              <span class="add-on">表单名称</span>
              <input type="text" name="_name" class="span3" id="_name" value="<?php echo $rs['name'] ; ?>"/>
            </div>
            <span class="help-inline">表单中文名称</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">表单标识</span>
              <input type="text" name="_app" class="span3" id="_app" value="<?php echo $rs['app'] ; ?>"/>
            </div>
            <span class="help-inline">表单唯一标识</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">表单标题</span>
              <input type="text" name="_title" class="span3" id="_title" value="<?php echo $rs['title'] ; ?>"/>
            </div>
            <span class="help-inline">表单标题</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">表单图片</span>
              <input type="text" name="_pic" class="span6" id="_pic" value="<?php echo $rs['pic'] ; ?>"/>
              <?php filesAdmincp::pic_btn("_pic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">表单简介</span>
              <textarea name="_description" id="_description" class="span6" style="height: 150px;"><?php echo $rs['description'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">表单模板</span>
              <input type="text" name="_tpl" class="span3" id="_tpl" value="<?php echo $rs['tpl'] ; ?>"/>
              <?php echo filesAdmincp::modal_btn('模板','_tpl');?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">完成提示</span>
              <input type="text" name="config[success]" class="span3" id="config_success" value="<?php echo $rs['config']['success']?$rs['config']['success']:'提交成功！' ; ?>"/>
            </div>
            <span class="help-inline">表单提交完成提示语</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">用户提交</span>
              <div class="switch" data-on-label="启用" data-off-label="禁用">
                <input type="checkbox" data-type="switch" name="config[enable]" id="config_enable" <?php echo $rs['config']['enable']?'checked':''; ?>/>
              </div>
              <span class="help-inline"></span>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">表单状态</span>
              <div class="switch" data-on-label="启用" data-off-label="禁用">
                <input type="checkbox" data-type="switch" name="status" id="status" <?php echo $rs['status']?'checked':''; ?>/>
              </div>
              <span class="help-inline"></span>
            </div>
            <div class="clearfloat mb10"></div>
            <?php if(empty($this->id)){?>
            <div class="input-prepend">
              <span class="add-on">是否同时创建数据表</span>
              <div class="switch" data-on-label="是" data-off-label="否">
                <input type="checkbox" data-type="switch" name="create" id="create" <?php echo $rs['create']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-block">
              如果选择不同时创建数据表,将只保存表单数据而不创建数据表.需要手工建表<br />
              一般用于数据表已经存在,只需要简单的查/增/改数据功能
            </span>
            <div class="clearfloat mb10"></div>
            <?php }?>
            <h3 class="title" style="width:620px;">
              <span>数据表</span>
              <button type="button" class="btn btn-link add_table_item">
                <i class="fa fa-plus-square"></i> 添加
              </button>
            </h3>
            <table class="table table-bordered bordered" style="width:600px;">
              <thead>
                <tr>
                  <th style="width:120px;">表名</th>
                  <th>主键</th>
                  <th>关联</th>
                  <th>名称</th>
                </tr>
              </thead>
              <tbody id="table_list">
              <?php if($rs['table']){?>
                <?php foreach ((array)$rs['table'] as $tkey => $tval) {?>
                <tr>
                  <td><input type="text" name="table[<?php echo $tkey; ?>][0]" class="span2" id="table_<?php echo $tkey; ?>_0" value="<?php echo $tval['name'] ; ?>"/></td>
                  <td><input type="text" name="table[<?php echo $tkey; ?>][1]" class="span2" id="table_<?php echo $tkey; ?>_1" value="<?php echo $tval['primary'] ; ?>"/></td>
                  <td><input type="text" name="table[<?php echo $tkey; ?>][2]" class="span2" id="table_<?php echo $tkey; ?>_2" value="<?php echo $tval['union'] ; ?>"/></td>
                  <td><input type="text" name="table[<?php echo $tkey; ?>][3]" class="span2" id="table_<?php echo $tkey; ?>_3" value="<?php echo $tval['label'] ; ?>"/></td>
                </tr>
                <?php } ?>
              <?php }else{ ?>
                <input name="table" type="hidden" value="<?php echo $rs['table']; ?>" />
              <?php } ?>
              </tbody>
            </table>
            <span class="help-inline">非二次开发,请勿修改表名</span>
            <div class="clearfloat mb10"></div>
          </div>
          <!-- 数据表字段 -->
          <?php include admincp::view("apps.table","apps");?>
          <div id="apps-add-field" class="tab-pane">
            <!-- 基础字段 -->
            <?php include admincp::view("apps.base","apps");?>
          </div>
          <div id="apps-add-custom" class="tab-pane">
            <?php include admincp::view("former.build","former");?>
          </div>
          <div class="clearfloat"></div>
          <div class="form-actions">
            <button class="btn btn-primary btn-large" type="submit"><i class="fa fa-check"></i> 提交</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="hide">
  <div id="table_item">
      <td><input type="text" name="table[~KEY~][0]" class="span2" id="table_~KEY~_0" value=""/></td>
      <td><input type="text" name="table[~KEY~][1]" class="span2" id="table_~KEY~_1" value=""/></td>
      <td><input type="text" name="table[~KEY~][2]" class="span2" id="table_~KEY~_2" value=""/></td>
      <td><input type="text" name="table[~KEY~][3]" class="span2" id="table_~KEY~_3" value=""/></td>
  </div>
</div>
<?php include admincp::view("former.editor","former");?>
<?php admincp::foot();?>
