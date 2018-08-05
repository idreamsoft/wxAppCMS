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
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="group" class="span2" id="group" value="<?php echo $_GET['group'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>角色列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th>ID</th>
              <th>名称</th>
              <th>类型</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){?>
            <tr id="id<?php echo $rs[$i]['gid'] ; ?>">
              <td><?php if($rs[$i]['gid']!='1'){  ?><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['gid'] ; ?>" /><?php } ?></td>
              <td><?php echo $rs[$i]['gid'] ; ?></td>
              <td><?php echo $rs[$i]['name'] ; ?></td>
              <td><?php echo $rs[$i]['type']?"管理组":"会员组" ; ?></td>
              <td>
                <?php if($rs[$i]['type']){  ?>
                <a href="<?php echo __ADMINCP__; ?>=members&gid=<?php echo $rs[$i]['gid'] ; ?>&job=1" class="btn btn-small"><i class="fa fa-bar-chart-o"></i> 统计</a>
                <a href="<?php echo APP_URI; ?>&do=add&tab=mpriv&gid=<?php echo $rs[$i]['gid'] ; ?>" class="btn btn-small"><i class="fa fa-tachometer"></i> 后台权限</a>
                <a href="<?php echo APP_URI; ?>&do=add&tab=cpriv&gid=<?php echo $rs[$i]['gid'] ; ?>" class="btn btn-small"><i class="fa  fa-unlock-alt"></i> 栏目权限</a>
                <?php } ?>
                <a href="<?php echo APP_URI; ?>&do=add&gid=<?php echo $rs[$i]['gid'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                <?php if($rs[$i]['gid']!='1'){  ?>
                <a href="<?php echo APP_FURI; ?>&do=del&gid=<?php echo $rs[$i]['gid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"><i class="fa fa-trash-o"></i> 删除</a>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="6"><div class="pagination pagination-right" style="float:right;"><?php echo iPagination::$pagenav ; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="dels"><i class="fa fa-trash-o"></i> 删除</a></li>
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
<?php admincp::foot();?>
