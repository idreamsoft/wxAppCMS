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
	<?php if($_GET['cid']){  ?>
	iCMS.select('cid',"<?php echo $_GET['cid'] ; ?>");
	<?php } ?>
	$("#<?php echo APP_FORMID;?>").batch();
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <input type="hidden" name="do" value="<?php echo admincp::$APP_DO;?>" />
        <input type="hidden" name="rid" value="<?php echo $_GET['rid'];?>" />
        <div class="input-prepend input-append"> <span class="add-on">栏目</span>
          <select name="cid" id="cid" class="span3 chosen-select">
            <option value="0">所有栏目</option>
            <?php echo category::select(); ?>
          </select>
          <span class="add-on">
          <input type="checkbox" name="sub" id="sub"/>
          子栏目 </span> </div>
        <div class="input-prepend"> <span class="add-on">规则</span>
          <select name="rid" id="rid" class="span3 chosen-select">
            <option value="0">所有规则</option>
            <?php foreach ((array)$ruleArray as $rid => $rname) {
              echo '<option value="'.$rid.'">'.$rname.'</option>';
            }?>
          </select>
        </div>
        <div class="input-prepend input-append">
          <input type="text" name="days" id="days" value="<?php echo $_GET['days'] ? $_GET['days'] : 7; ?>" style="width:36px;"/>
          <span class="add-on">天内</span>
        </div>
        <div class="input-prepend input-append">
          <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $_GET['perpage'] ? $_GET['perpage'] : 100; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span>
        </div>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords']; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>采集错误信息</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
            <thead>
              <tr>
                <td>方案ID</td>
                <td>规则ID</td>
                <td>错误数</td>
                <td>日期</td>
              </tr>
            </thead>
          <?php foreach ((array)$rs as $key => $value) {?>
          <tr>
            <td><?php echo $value['pid'] ; ?>
              <a href="<?php echo __ADMINCP__; ?>=spider&do=testrule&pid=<?php echo $value['pid']; ?>" class="btn btn-small" data-toggle="modal" title="测试方案"><i class="fa fa-keyboard-o"></i> 测试方案</a>
              <a href="<?php echo __ADMINCP__; ?>=spider&do=addproject&pid=<?php echo $value['pid']; ?>" class="btn btn-small" target="_blank"><i class="fa fa-edit"></i>编辑方案</a>
            </td>
            <td>
              <a href="<?php echo __ADMINCP__; ?>=spider&do=error&rid=<?php echo $value['rid']; ?>" class="btn btn-small"><i class="fa fa-eye"></i> <?php echo $ruleArray[$value['rid']]; ?>[<?php echo $value['rid'] ; ?>]</a>
              <a href="<?php echo __ADMINCP__; ?>=spider&do=testrule&rid=<?php echo $value['rid']; ?>" class="btn btn-small" data-toggle="modal" title="测试<?php echo $ruleArray[$value['rid']]; ?>规则"><i class="fa fa-keyboard-o"></i> 测试</a>
              <a href="<?php echo __ADMINCP__; ?>=spider&do=addrule&rid=<?php echo $value['rid']; ?>" class="btn btn-small" target="_blank"><i class="fa fa-edit"></i> 编辑</a>
              <a href="<?php echo __ADMINCP__; ?>=spider&do=project&rid=<?php echo $value['rid']; ?>" class="btn btn-small" target="_blank"><i class="fa fa-list"></i> 所有方案</a>
            </td>
            <td>
              <?php echo $value['ct'] ; ?>
              <a href="<?php echo __ADMINCP__; ?>=spider&do=view_error&pid=<?php echo $value['pid']; ?>" class="btn btn-small" data-toggle="modal" title="查看错误信息"><i class="fa fa-eye"></i> 查看错误信息</a>
            </td>
            <td><?php echo $value['date'] ; ?></td>
            <td>
              <a href="<?php echo __ADMINCP__; ?>=spider&do=del_error&pid=<?php echo $value['pid']; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-close"></i> 删除</a>
            </td>
          </tr>
          <?php } ?>
        </table>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot(); ?>
