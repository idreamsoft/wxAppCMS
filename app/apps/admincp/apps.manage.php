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
<style>
.app_list_desc{font-size: 12px;color: #666;}
.nopadding .tab-content{padding: 0px;}
</style>
<script type="text/javascript">
$(function(){
  $("#<?php echo APP_FORMID;?>").batch();
  $("#local_app").click(function(event) {
      var local_app_wrap = document.getElementById("local_app_wrap");
      iCMS.dialog({
        title: 'iCMS - 安装本地应用',
        content:local_app_wrap
      });
  });
});
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
    <h5>搜索</h5>
    <div class="pull-right">
      <a style="margin: 10px;" class="btn btn-success btn-mini" href="<?php echo APP_FURI; ?>&do=cache" target="iPHP_FRAME"><i class="fa fa-refresh"></i> 更新缓存</a>
    </div>
  </div>
  <div class="widget-content">
      <div class="pull-right">
        <button class="btn btn-primary" type="button" id="local_app"><i class="fa fa-send"></i> 安装本地应用</button>
      </div>
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
      <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
      <div class="input-prepend input-append">
        <span class="add-on">每页</span>
        <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
        <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append">
          <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
    </span>
    <ul class="nav nav-tabs" id="apps-tab">
      <?php foreach (apps::$type_array as $key => $value) {?>
      <li class="apps-type-<?php echo $key;?>"><a href="#apps-type-<?php echo $key;?>" data-toggle="tab"><i class="fa fa-cubes"></i> <?php echo $value;?></a></li>
      <?php }?>
    </ul>
  </div>
  <div class="widget-content nopadding">
    <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
      <div class="tab-content">
        <?php foreach (apps::$type_array as $type_key => $type_value) {?>
        <div id="apps-type-<?php echo $type_key;?>" class="tab-pane apps-type-<?php echo $type_key;?>">
          <table class="table table-bordered table-condensed table-hover">
            <thead>
              <tr>
                <th style="width:40px;">APPID</th>
                <th>标识</th>
                <th class="span3">名称</th>
                <th>数据表</th>
                <th class="span3">模板标签</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ((array)$apps_type_group[$type_key] as $key => $data) {
                $table  = apps::table_item($data['table']);
                $config = json_decode($data['config'],true);
              ?>
              <tr id="id<?php echo $data['id'] ; ?>">
                <td><b><?php echo $data['id'] ; ?></b></td>
                <td>
                  <b><?php echo $data['app'] ; ?></b><br />
                  <span class="label label-inverse"><?php echo $config['version'] ; ?></span>
                </td>
                <td>
                  <?php echo $data['name'] ; ?>
                  <p class="app_list_desc"><?php echo $config['info'] ; ?></p>
                  <?php if($config['iFormer']){ ?>
                    <span class="label label-info">可自定义</span>
                  <?php }?>
                </td>
                <td>
                  <?php if(is_array($table)){ ?>
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <td>表名</td>
                        <td>主键</td>
                        <td>关联</td>
                        <td>名称</td>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      foreach ((array)$table as $tkey => $tval) {
                      ?>
                      <tr>
                        <td><?php echo $tval['name'] ; ?></td>
                        <td><?php echo $tval['primary'] ; ?></td>
                        <td><?php echo $tval['union'] ; ?></td>
                        <td><?php echo $tval['label'] ; ?></td>
                      </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                  <?php }else{
                    echo '<span class="label">无相关表</span>';
                  }
                  ?>
                </td>
                <td>
                  <?php
                  if($config['template']){
                    foreach ((array)$config['template'] as $key => $tpltags) {
                      echo '<a href="https://www.icmsdev.com/docs/'.str_replace(array(':','$'), array('_',''), $tpltags).'" target="_blank" title="点击查看模板标签说明">&lt;!--{'.$tpltags.'}--&gt;</a><br />';
                    }
                  }else{
                    echo '<span class="label">无相关标签</span>';
                  }
                  ?>
                  </td>
                  <td>
                    <?php if($data['type']){?>
                      <?php if($data['apptype']=="2"){?>
                        <a href="<?php echo __ADMINCP__; ?>=<?php echo $data['app'] ; ?>&do=manage&appid=<?php echo $data['id'] ; ?>" class="btn btn-small" target="_blank"><i class="fa fa-dashboard"></i> 内容管理</a>
                        <a href="<?php echo __ADMINCP__; ?>=<?php echo $data['app'] ; ?>&do=add&appid=<?php echo $data['id'] ; ?>" class="btn btn-small" target="_blank"><i class="fa fa-edit"></i> 添加内容</a>
                        <div class="clearfix mt5"></div>
                      <?php }?>
                      <a href="<?php echo APP_URI; ?>&do=add&id=<?php echo $data['id'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                      <a href="<?php echo APP_URI; ?>&do=pack&id=<?php echo $data['id'] ; ?>" class="btn btn-small"><i class="fa fa-download"></i> 打包</a>
                      <?php if($data['apptype']){?>
                        <?php if($data['status']){?>
                          <a href="<?php echo APP_URI; ?>&do=update&_args=status:0&id=<?php echo $data['id'] ; ?>" target="iPHP_FRAME" class="btn btn-small btn-warning" onclick="return confirm('关闭应用不会删除数据，但应用将不可用\n确定要关闭应用?');"><i class="fa fa-close"></i> 关闭</a>
                        <?php }else{?>
                          <a href="<?php echo APP_URI; ?>&do=update&_args=status:1&id=<?php echo $data['id'] ; ?>" target="iPHP_FRAME" class="btn btn-small btn-success"><i class="fa fa-check"></i> 启用</a>
                        <?php }?>
                        <a href="<?php echo APP_FURI; ?>&do=uninstall&id=<?php echo $data['id'] ; ?>" target="iPHP_FRAME" class="del btn btn-small btn-danger" title='永久删除'  onclick="return confirm('卸载应用会清除应用所有数据！\n卸载应用会清除应用所有数据！\n卸载应用会清除应用所有数据！\n确定要卸载?\n确定要卸载?\n确定要卸载?');"/><i class="fa fa-trash-o"></i> 卸载</a>
                      <?php }else{?>
                      <?php }?>
                    <?php }?>
                    </td>
                </tr>
                <?php }  ?>
              </tbody>
              <tr>
                <td colspan="7">
                  <div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
<!--                   <div class="input-prepend input-append mt20">
                    <span class="add-on">全选
                      <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                    </span>
                    <div class="btn-group dropup" id="iCMS-batch">
                      <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a>
                      <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        <li><a data-toggle="batch" data-action="dels"><i class="fa fa-trash-o"></i> 删除</a></li>
                      </ul>
                    </div>
                  </div> -->
                </td>
              </tr>
            </table>
          </div>
          <?php }?>
        </div>
      </form>
    </div>
  </div>
</div>
<div id="local_app_wrap" style="display:none;">
  <form action="<?php echo APP_FURI; ?>&do=local_app" method="post" enctype="multipart/form-data" target="iPHP_FRAME">
    <div class="alert alert-info">
      由于安全限制<br />
      请先把iCMS应用安装包文件(.zip)<br />
      上传到网站根目录下
    </div>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend">
      <span class="add-on">可用安装包</span>
      <select name="zipfile" class="chosen-select span4" data-placeholder="请选择iCMS应用安装包文件(.zip)...">
        <?php foreach(glob(iPATH."iCMS.APP.*-v*.*.*.zip") as $value){ ?>
        <option value="<?php echo $value;?>"><?php echo str_replace(iPATH, '', $value);?></option>
        <?php } ?>
      </select>
    </div>
    <div class="clearfloat mb10"></div>
    <button class="btn btn-primary btn-large btn-block" type="submit"><i class="fa fa-check"></i> 安装</button>
    <div class="clearfloat mb10"></div>
  </form>
</div>
<script>
$("li","#apps-tab").click(function(event) {
  // console.log($(this).attr('class'));
  iCMS.setcookie('apps_tab',$(this).attr('class'));
});
var appstab = iCMS.getcookie('apps_tab');
  // console.log(appstab);
if(appstab){
  $('.'+appstab).addClass('active');
  $('#'+appstab).addClass('active');
}else{
  $("#apps-tab li:eq(0)").addClass('active');
  $(".tab-content div:eq(0)").addClass('active');
}

</script>
<?php admincp::foot();?>
