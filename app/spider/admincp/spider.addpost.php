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
  iCMS.select('app',"<?php echo $rs['app'] ; ?>");
  $("#app").on('change', function(evt, params) {
    var fun = $(this).find('option[value="' + params['selected'] + '"]').attr('fun');
    $("#fun").val(fun);
    var tipMap ={
      'forms':'自定义表单 需要填写 form_id',
      'content':'自定义应用 需要填写 appid'
    }
    var tip = tipMap[params['selected']]||'';
    $(".post-tip").addClass('hide').removeClass('hide').html(tip);
  });
});

</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->poid)?'添加':'修改' ; ?>发布模块</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=savepost" method="post" class="form-inline" id="iCMS-spider-post" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->poid ; ?>" />
        <div id="addpost" class="tab-content">
          <div class="input-prepend"><span class="add-on">应用</span>
            <select name="app" id="app" class="chosen-select span3">
              <option value="0"></option>
              <option value="article" fun="do_save"> 文章 </option>
              <option value="tag" fun="do_save"> 标签 </option>
              <option value="article_category" fun="do_save"> 文章栏目 </option>
              <option value="tag_category" fun="do_save"> 标签分类 </option>
              <option value="forms" fun="do_savedata"> 自定义表单 </option>
              <option value="content" fun="do_save"> 自定义应用  </option>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">名称</span>
            <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name']; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">接口</span>
            <input type="text" name="fun" class="span6" id="fun" value="<?php echo $rs['fun']?$rs['fun']:'do_save'; ?>"/>
          </div>
          <span class="help-inline">可使用URL 远程发布</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">发布项</span>
            <textarea name="post" id="post" class="span6" style="height: 90px;"><?php echo $rs['post'] ; ?></textarea>
          </div>
          <span class="help-inline hide post-tip"></span>
          <div class="clearfloat mb10"></div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
