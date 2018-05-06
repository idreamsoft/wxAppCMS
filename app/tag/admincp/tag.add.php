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
  iCMS.select('cid',"<?php echo $rs['cid'] ; ?>");
  iCMS.select('tcid',"<?php echo $rs['tcid'] ; ?>");
  iCMS.select('pid',"<?php echo $rs['pid']?$rs['pid']:0 ; ?>");
  iCMS.select('status',"<?php echo $rs['status'] ; ?>");
  $("#<?php echo APP_FORMID;?>").submit(function(){
      // if($("#cid option:selected").val()=="0"){
      //  iCMS.alert("请选择所属栏目");
      //  $("#cid").focus();
      //  return false;
      // }
      if($("#name").val()==''){
      iCMS.alert("标签名称不能为空!");
      $("#name").focus();
      return false;
    }
  });
});
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title">
      <span class="icon"> <i class="fa fa-pencil"></i> </span>
      <h5 class="brs"><?php echo ($id?'添加':'修改'); ?>标签</h5>
      <ul class="nav nav-tabs" id="tag-add-tab">
        <li class="active"><a href="#tag-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <li><a href="#add-publish" data-toggle="tab"><i class="fa fa-rocket"></i> 发布设置</a></li>
        <li><a href="#former-layout" data-toggle="tab"><i class="fa fa-wrench"></i> 自定义</a></li>
        <li><a href="#apps-metadata" data-toggle="tab"><i class="fa fa-sitemap"></i> 动态属性</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->id ; ?>" />
        <input name="userid" type="hidden" value="<?php echo $rs['userid'] ; ?>" />
        <input name="_cid" type="hidden" value="<?php echo $rs['cid'] ; ?>" />
        <input name="_tcid" type="hidden" value="<?php echo $rs['tcid'] ; ?>" />
        <input name="_pid" type="hidden" value="<?php echo $rs['pid'] ; ?>" />
        <div id="tags-add" class="tab-content">
          <div id="tag-add-base" class="tab-pane active">
            <div class="input-prepend">
              <span class="add-on">标签分类</span>
              <select name="tcid" id="tcid" class="chosen-select span6" data-placeholder="请选择标签分类...">
                <option value="0"> ==== 默认分类 ==== </option>
                <?php echo category::appid($this->appid,'ca')->select($rs['tcid'],0,1,true);?>
              </select>
            </div>
            <span class="help-inline">本标签所属的标签分类</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">所属栏目</span>
              <select name="cid" id="cid" class="chosen-select span6" data-placeholder="请选择栏目...">
                <option> ==== 无所属栏目 ==== </option>
                <?php echo category::appid(0,'ca')->select($rs['cid'],0,1,true);?>
              </select>
            </div>
            <span class="help-inline">本标签所属的栏目</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">标签属性</span>
              <select name="pid[]" id="pid" class="chosen-select span6" multiple="multiple" data-placeholder="请选择标签属性(可多选)...">
                <option value="0">普通标签[pid='0']</option>
                <?php echo propAdmincp::get("pid") ; ?>
              </select>
              <?php echo propAdmincp::btn_add('添加常用属性');?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">父标签ID</span>
              <input type="text" name="rootid" class="span6" id="rootid" value="<?php echo $rs['rootid'] ; ?>"/>
            </div>
            <span class="help-inline">本标签所属的标签的ID,请自行填写ID</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">标签名称</span>
              <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">唯一标识</span>
              <input type="text" name="tkey" class="span6" id="tkey" value="<?php echo $rs['tkey'] ; ?>"/>
            </div>
            <span class="help-inline">用于伪静态或者静态生成 唯一性<br />
            留空则系统按名称拼音生成</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">SEO 标题</span>
              <input type="text" name="seotitle" class="span6" id="seotitle" value="<?php echo $rs['seotitle'] ; ?>" />
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">副 标 题</span>
              <input type="text" name="subtitle" class="span6" id="subtitle" value="<?php echo $rs['subtitle'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">关 键 字</span>
              <input type="text" name="keywords" class="span6" id="keywords" value="<?php echo $rs['keywords'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">缩 略 图</span>
              <input type="text" name="pic" class="span6" id="pic" value="<?php echo $rs['pic'] ; ?>"/>
              <?php filesAdmincp::set_opt($rs['pic'])->pic_btn("pic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">大图</span>
              <input type="text" name="bpic" class="span6" id="bpic" value="<?php echo $rs['bpic'] ; ?>"/>
              <?php filesAdmincp::set_opt($rs['bpic'])->pic_btn("bpic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">中图</span>
              <input type="text" name="mpic" class="span6" id="mpic" value="<?php echo $rs['mpic'] ; ?>"/>
              <?php filesAdmincp::set_opt($rs['mpic'])->pic_btn("mpic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">小图</span>
              <input type="text" name="spic" class="span6" id="spic" value="<?php echo $rs['spic'] ; ?>"/>
              <?php filesAdmincp::set_opt($rs['spic'])->pic_btn("spic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">描述</span>
              <textarea name="description" id="description" class="span6" style="height: 150px;width:600;"><?php echo $rs['description'] ; ?></textarea>
            </div>
            <span class="help-inline"></span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">相关标签</span>
              <input type="text" name="related" class="span6" id="related" value="<?php echo $rs['related'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">状 态</span>
              <select name="status" id="status" class="chosen-select span3">
                <option value="0"> 草稿 [status='0']</option>
                <option value="1"> 正常 [status='1']</option>
                <option value="2"> 回收站 [status='2']</option>
                <option value="3"> 待审核 [status='3']</option>
                <option value="4"> 未通过 [status='4']</option>
                <?php echo propAdmincp::get("status") ; ?>
              </select>
              <?php echo propAdmincp::btn_add('添加状态');?>
            </div>
          </div>
          <div id="add-publish" class="tab-pane hide">
            <div class="input-prepend">
              <span class="add-on">发布时间</span>
              <input id="pubdate" class="<?php echo $readonly?'':'ui-datepicker'; ?>" value="<?php echo $rs['pubdate']?$rs['pubdate']:get_date(0,'Y-m-d H:i:s') ; ?>"  name="pubdate" type="text" style="width:230px" <?php echo $readonly ; ?>/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">排序</span>
              <input id="sortnum" class="span2" value="<?php echo $rs['sortnum']?$rs['sortnum']:time() ; ?>" name="sortnum" type="text"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">权重</span>
              <input id="weight" class="span2" value="<?php echo $rs['weight']?$rs['weight']:time(); ?>" name="weight" type="text"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">总点击数</span>
              <input type="text" name="hits" class="span1" id="hits" value="<?php echo $rs['hits']?$rs['hits']:'0'; ?>"/>
              <span class="add-on">当天点击数</span>
              <input type="text" name="hits_today" class="span1" id="hits_today" value="<?php echo $rs['hits_today']?$rs['hits_today']:'0'; ?>"/>
              <span class="add-on">昨天点击数</span>
              <input type="text" name="hits_yday" class="span1" id="hits_yday" value="<?php echo $rs['hits_yday']?$rs['hits_yday']:'0'; ?>"/>
              <span class="add-on">周点击</span>
              <input type="text" name="hits_week" class="span1" id="hits_week" value="<?php echo $rs['hits_week']?$rs['hits_week']:'0'; ?>"/>
              <span class="add-on">月点击</span>
              <input type="text" name="hits_month" class="span1" id="hits_month" value="<?php echo $rs['hits_month']?$rs['hits_month']:'0'; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">收藏数</span>
              <input type="text" name="favorite" class="span1" id="favorite" value="<?php echo $rs['favorite']?$rs['favorite']:'0'; ?>"/>
              <span class="add-on">评论数</span>
              <input type="text" name="comments" class="span1" id="comments" value="<?php echo $rs['comments']?$rs['comments']:'0'; ?>"/>
              <span class="add-on">点赞数</span>
              <input type="text" name="good" class="span1" id="good" value="<?php echo $rs['good']?$rs['good']:'0'; ?>"/>
              <span class="add-on">点踩数</span>
              <input type="text" name="bad" class="span1" id="bad" value="<?php echo $rs['bad']?$rs['bad']:'0'; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">模板</span>
              <input type="text" name="tpl" class="span6" id="tpl" value="<?php echo $rs['tpl'] ; ?>"/>
              <?php echo filesAdmincp::modal_btn('模板','tpl');?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">自定链接</span>
              <input type="text" name="clink" class="span6" id="clink" value="<?php echo $rs['clink'] ; ?>"/>
              <span class="help-inline">只能由英文字母、数字或_-组成(不支持中文),留空则自动以标题拼音填充</span>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on">外部链接</span>
              <input type="text" name="url" class="span6 tip" title="注意:文章设置外部链接后编辑器里的内容是不会被保存的哦!" id="url" value="<?php echo $rs['url'] ; ?>"/>
            </div>
            <span class="help-inline">不填写请留空!</span>
            <div class="clearfloat mb10"></div>
          </div>
          <div id="former-layout" class="tab-pane hide">
            <?php echo former::layout();?>
          </div>
          <div id="apps-metadata" class="tab-pane hide">
            <?php include admincp::view("apps.meta","apps");?>
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
