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
	$("#<?php echo APP_FORMID;?>").batch();
  $("#import_rule").click(function(event) {
      var import_rule_wrap = document.getElementById("import_rule_wrap");
      iCMS.dialog({
        title: 'iCMS - 导入规则',
        content:import_rule_wrap
      });
  });
  $("#local").click(function() {
      $("#localfile").click();
  });
  $("#localfile").change(function() {
      $("#import_rule_wrap form").submit();
      $(this).val('');
  });
});
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <input type="hidden" name="do" value="<?php echo admincp::$APP_DO;?>" />
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
        <div class="pull-right">
          <button class="btn btn-success" type="button" id="import_rule"><i class="fa fa-send"></i> 导入规则</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>规则列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th>ID</th>
              <th class="span6">名称</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){?>
            <tr id="id<?php echo $rs[$i]['id'] ; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['id'] ; ?>" /></td>
              <td><?php echo $rs[$i]['id'] ; ?></td>
              <td><?php echo $rs[$i]['name'] ; ?></td>
              <td>
                <a href="<?php echo APP_URI; ?>&do=manage&rid=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small btn-success" target="_blank"><i class="fa fa-list-alt"></i> 已采集</a>
                <a href="<?php echo APP_URI; ?>&do=project&rid=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small btn-info" target="_blank"><i class="fa fa-magnet"></i> 方案</a>
                <a href="<?php echo APP_URI; ?>&do=error&rid=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small btn-danger" target="_blank"><i class="fa fa-info-circle"></i> 错误信息</a>
                <a href="<?php echo APP_FURI; ?>&do=exportrule&rid=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-download"></i> 导出</a>
                <a href="<?php echo APP_FURI; ?>&do=exportproject&rid=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-download"></i> 导出方案</a>
                <a href="<?php echo APP_FURI; ?>&do=copyrule&rid=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-clipboard"></i> 复制</a>
                <a href="<?php echo APP_URI; ?>&do=testrule&rid=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small btn-inverse" data-toggle="modal" title="测试规则"><i class="fa fa-keyboard-o"></i> 测试</a>
                <a href="<?php echo APP_URI; ?>&do=addrule&rid=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" target="_blank"><i class="fa fa-edit"></i> 编辑</a>
                <a href="<?php echo APP_FURI; ?>&do=delrule&rid=<?php echo $rs[$i]['id'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a></td>
            </tr>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"><div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="delrule"><i class="fa fa-trash-o"></i> 删除</a></li>
                    </ul>
                  </div>
                </div></td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </div>
</div>
<div id="import_rule_wrap" style="display:none;">
  <form action="<?php echo APP_FURI; ?>&do=import_rule" method="post" enctype="multipart/form-data" target="iPHP_FRAME">
    <div class="alert alert-info">
      只允许导入TXT文件
    </div>
    <div class="clearfloat mb10"></div>
    <a id="local" class="btn btn-primary btn-large btn-block"><i class="fa fa-upload"></i> 请选择要导入的规则</a>
    <input id="localfile" name="upfile" type="file" class="hide"/>
    <div class="clearfloat mb10"></div>
  </form>
</div>
<?php admincp::foot();?>
