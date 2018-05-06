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
$type_3_css = " type_3 ";
$rs['type']!='3' && $type_3_css.=' hide ';

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
      iCMS.alert("应用名称不能为空");
      return false;
    }
    var app =$("#app_app").val();
    if(app==''){
      $("#app_app").focus();
      iCMS.alert("应用标识不能为空");
      return false;
    }
  });

  $("#type").change(function(){
    if(this.value=="3"){
      $("#tab-field,#tab-custom").hide();
      $('[name="apptype"]').val('1');
      $('#config_iFormer').val('0');
      $("#menu").data('data', $("#menu").val());
      $("#menu").val('[{"id":"{app}","caption":"{name}","icon":"pencil-square-o","children":[{"caption":"更新栏目缓存","href":"{app}_category&do=cache","icon":"refresh","target":"iPHP_FRAME"},{"caption":"-"},{"caption":"栏目管理","href":"{app}_category","icon":"list-alt"},{"caption":"添加栏目","href":"{app}_category&do=add","icon":"edit"},{"caption":"-"},{"caption":"添加{name}","href":"{app}&do=add","icon":"edit"},{"caption":"{name}管理","href":"{app}&do=manage","icon":"list-alt"},{"caption":"草稿箱","href":"{app}&do=inbox","icon":"inbox"},{"caption":"回收站","href":"{app}&do=trash","icon":"trash-o"}]}]');
      $(".type_3").show();
    }else if(this.value=="2"){
      $("#tab-field,#tab-custom").show();
      $('[name="apptype"]').val('2');
      $('#config_iFormer').val('1');
      $(".type_3").hide();
      var data = $("#menu").data('data');
      if(data){
        $("#menu").val(data);
      }
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
      tr.append('<td class="type_3"><button class="btn btn-small btn-danger del_table" type="button"><i class="fa fa-trash-o"></i> 删除</button></td>');
      $("#table_list").append(tr);
  });
  var doc = $(document);
  doc.on("click",".del_table",function(){
      $(this).parent().parent().remove();
  });
})
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title">
      <span class="icon"> <i class="fa fa-pencil"></i> </span>
      <h5 class="brs"><?php echo empty($this->id)?'创建':'修改' ; ?>应用</h5>
      <ul class="nav nav-tabs" id="apps-add-tab">
        <li class="active"><a href="#apps-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <li><a href="#apps-add-menu" data-toggle="tab"><i class="fa fa-bars"></i> 配置</a></li>
        <?php if($rs['table'])foreach ($rs['table'] as $key => $tval) {?>
        <li><a href="#apps-add-<?php echo $key; ?>-field" data-toggle="tab"><i class="fa fa-database"></i> <?php echo $tval['label']?$tval['label']:$tval['name']; ?>表字段</a></li>
        <?php }?>
        <?php if($rs['config']['iFormer']){?>
          <?php if(!$rs['table']){?>
          <li id="tab-field"><a href="#apps-add-field" data-toggle="tab"><i class="fa fa-cog"></i> 基础字段</a></li>
          <?php }?>
          <li id="tab-custom"><a href="#apps-add-custom" data-toggle="tab"><i class="fa fa-cog"></i> 字段编辑器</a></li>
        <?php }?>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-apps" target="iPHP_FRAME">
        <input name="_id" type="hidden" value="<?php echo $this->id; ?>" />
        <input name="apptype" type="hidden" value="<?php echo $rs['apptype']; ?>" />
        <input id="config_iFormer" name="config[iFormer]" type="hidden" value="<?php echo $rs['config']['iFormer']; ?>" />
        <div id="apps-add" class="tab-content">
          <div id="apps-add-base" class="tab-pane active">
            <div class="input-prepend">
              <span class="add-on">应用名称</span>
              <input type="text" name="_name" class="span3" id="_name" value="<?php echo $rs['name'] ; ?>"/>
            </div>
            <span class="help-inline">应用中文名称</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">应用标识</span>
              <input type="text" name="_app" class="span3" id="_app" value="<?php echo $rs['app'] ; ?>"/>
            </div>
            <span class="help-inline">应用唯一标识</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">应用标题</span>
              <input type="text" name="_title" class="span3" id="_title" value="<?php echo $rs['title'] ; ?>"/>
            </div>
            <span class="help-inline">应用标题.例:应用名称(文章系统),应用标题(文章)</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">应用简介</span>
              <textarea name="config[info]" id="config_info" class="span6" style="height: 150px;"><?php echo $rs['config']['info'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">模板标签</span>
              <textarea name="config[template]" id="config_template" class="span6" style="height: 150px;" readonly><?php echo $rs['config']['template'] ; ?></textarea>
            </div>
            <span class="help-inline">程序自动获取</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">应用版本</span>
              <input type="text" name="config[version]" class="span3" id="config_version" value="<?php echo $rs['config']['version']?$rs['config']['version']:'v1.0.0' ; ?>"/>
            </div>
            <span class="help-inline">版本号</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">应用菜单</span>
              <select name="config[menu]" id="config_menu" class="chosen-select span3" data-placeholder="请选择应用类型...">
                <option value="0">无菜单</option>
                <option value="default">默认配置[default]</option>
                <option value="main">主菜单[main]</option>
                <optgroup label="应用菜单">
                  <?php
                    foreach (menu::$menu_array as $key => $value) {
                      if($value['caption']=='-'||$key==$rs['app']){
                        continue;
                      }
                  ?>
                    <option value="<?php echo $key?>"><?php echo $value['caption']?>菜单[<?php echo $key?>]</option>
                  <?php }?>
                </optgroup>
              </select>
            </div>
            <script>$(function(){iCMS.select('config_menu',"<?php echo $rs['config']['menu']?$rs['config']['menu']:'0'; ?>");})</script>
            <span class="help-inline">应用的菜单,无菜单后台将不显示入口</span>
            <div class="clearfloat mb10"></div>
            <?php if($rs['type']){?>
            <div class="input-prepend">
              <span class="add-on">应用类型</span>
              <select name="type" id="type" class="chosen-select span3" data-placeholder="请选择应用类型...">
                <?php echo apps::get_type_select('0') ; ?>
              </select>
            </div>
            <script>$(function(){iCMS.select('type',"<?php echo $rs['type']; ?>");})</script>
            <?php }else{ ?>
              <input name="type" type="hidden" value="<?php echo $rs['type']; ?>" />
            <?php } ?>
            <span class="help-inline <?php echo $type_3_css;?>">第三方应用类型,仅供用户开发应用添加数据用,此类型不会自动创建相关表,仅添加一条应用数据</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">用户中心</span>
              <div class="switch" data-on-label="启用" data-off-label="禁用">
                <input type="checkbox" data-type="switch" name="usercp" id="usercp" <?php echo $rs['config']['usercp']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-inline">启用后,用户中心将显示此应用并根据字段设计</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">应用状态</span>
              <div class="switch" data-on-label="启用" data-off-label="禁用">
                <input type="checkbox" data-type="switch" name="status" id="status" <?php echo $rs['status']?'checked':''; ?>/>
              </div>
              <span class="help-inline"></span>
            </div>
            <div class="clearfloat mb10"></div>
            <?php //if(empty($this->id)){?>
            <?php if(false){?>
            <div class="input-prepend">
              <span class="add-on">是否同时创建数据表</span>
              <div class="switch" data-on-label="是" data-off-label="否">
                <input type="checkbox" data-type="switch" name="create" id="create" <?php echo $rs['create']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-block">
              如果选择不同时创建数据表,将只保存应用数据而不创建应用表.需要手工建表<br />
              一般用于二次开发添加应用.
            </span>
            <div class="clearfloat mb10"></div>
            <?php }?>

            <h3 class="title" style="width:710px;">
              <span>数据表</span>
              <button type="button" class="btn btn-link add_table_item <?php echo $type_3_css;?>">
                <i class="fa fa-plus-square"></i> 添加
              </button>
            </h3>
            <table class="table table-bordered bordered" style="width:720px;">
              <thead>
                <tr>
                  <th style="width:120px;">表名</th>
                  <th>主键</th>
                  <th>关联</th>
                  <th>名称</th>
                  <th class="<?php echo $type_3_css;?>"></th>
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
                  <td class="<?php echo $type_3_css;?>"><button class="btn btn-small btn-danger del_table" type="button"><i class="fa fa-trash-o"></i> 删除</button></td>
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
          <div id="apps-add-menu" class="tab-pane">
            <div class="alert alert-error alert-block">
              <p>菜单配置属于重要数据,如果不熟悉请勿修改.敬请等待官方推出相关编辑器</p>
            </div>
            <div class="input-prepend">
              <span class="add-on">菜单配置</span>
              <textarea name="menu" id="menu" class="span8" style="height:450px;"><?php echo $rs['menu'];?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">路由配置</span>
              <textarea name="router" id="router" class="span8" style="height:150px;"><?php echo $rs['router'];?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">内容网址</span>
              <textarea name="config[iurl]" id="config_iurl" class="span8" style="height:120px;"><?php echo $rs['config']['iurl']?cnjson_encode($rs['config']['iurl']):'' ; ?></textarea>
            </div>
          </div>
          <!-- 数据表字段 -->
          <?php include admincp::view("apps.table","apps");?>
          <?php if($rs['config']['iFormer']){?>
          <div id="apps-add-field" class="tab-pane">
            <!-- 基础字段 -->
            <?php include admincp::view("apps.base","apps");?>
          </div>
          <div id="apps-add-custom" class="tab-pane">
            <?php include admincp::view("former.build","former");?>
          </div>
          <?php } ?>
          <div class="clearfloat"></div>
          <div class="form-actions">
            <button class="btn btn-primary btn-large" type="submit"><i class="fa fa-check"></i> 提交</button>
            <?php if($rs['apptype']){?>
            <a href="<?php echo APP_FURI; ?>&do=uninstall&id=<?php echo $rs['id'] ; ?>" target="iPHP_FRAME" class="del btn btn-small btn-danger" title='永久删除'  onclick="return confirm('卸载应用会清除应用所有数据！\n卸载应用会清除应用所有数据！\n卸载应用会清除应用所有数据！\n确定要卸载?\n确定要卸载?\n确定要卸载?');"/><i class="fa fa-trash-o"></i> 卸载</a>
            <?php }?>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include admincp::view("former.editor","former");?>
<?php admincp::foot();?>
