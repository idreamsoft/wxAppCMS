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
        <input type="hidden" name="do" value="access_log" />
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append"> <span class="add-on">关键词</span>
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
      <h5>网站列表</h5>
    </div>
    <div class="widget-content nopadding">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th style="width:60px;">用户ID</th>
              <th style="width:60px;">用户名</th>
              <th style="width:60px;">应用</th>
              <th>访问链接/时间</th>
              <th style="width:60px;">请求方式</th>
              <th style="width:100px;">IP</th>
              <th>User Agent/来路</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){?>
            <tr>
              <td><?php echo $rs[$i]['id'] ; ?></td>
              <td><a href="<?php echo iPHP_SELF; ?>?do=access_log&uid=<?php echo $rs[$i]['uid'] ; ?>"><?php echo $rs[$i]['uid'] ; ?></a></td>
              <td><?php echo $rs[$i]['username'] ; ?></td>
              <td><a href="<?php echo iPHP_SELF; ?>?do=access_log&sapp=<?php echo $rs[$i]['app'] ; ?>"><?php echo $rs[$i]['app'] ; ?></a></td>
              <td>
                <?php echo $rs[$i]['uri'] ; ?>
                <br />
                <?php echo get_date($rs[$i]['addtime'],'Y-m-d H:i:s'); ?>
              </td>
              <td><?php echo $rs[$i]['method'] ; ?></td>
              <td><a href="<?php echo iPHP_SELF; ?>?do=access_log&ip=<?php echo $rs[$i]['ip'] ; ?>"><?php echo $rs[$i]['ip'] ; ?></a></td>
              <td>
                <?php echo $rs[$i]['useragent'] ; ?>
                <br />
                <?php echo $rs[$i]['referer'] ; ?>
              </td>
            </tr>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="9"><div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div></td>
            </tr>
          </tfoot>
        </table>
        <div class="alert alert-info alert-block ml10 mr10">
          <p>访问记录无删除功能,如要删除请登陆数据库删除相关信息</p>
        </div>
    </div>
  </div>
</div>
<?php admincp::foot();?>
