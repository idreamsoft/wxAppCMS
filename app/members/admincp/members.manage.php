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
.job { font-size: 14px; }
.job table { background: #FFF; text-align: center; }
.day td.today { background: #0054E3; color: #FFF; font-weight: bold; }
.job hr { height: 1px; margin: 2px 0px; }
.job .week th { text-align: center; color: rgb(187, 31, 1); text-align: center; }
</style>
<script type="text/javascript">
$(function(){
  <?php if($_GET['orderby']){ ?>
  iCMS.select('orderby',"<?php echo $_GET['orderby'] ; ?>");
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
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend">
          <span class="add-on">排序</span>
          <select name="orderby" id="orderby" class="span2 chosen-select">
            <option value=""></option>
            <optgroup label="降序"><?php echo $orderby_option['DESC'];?></optgroup>
            <optgroup label="升序"><?php echo $orderby_option['ASC'];?></optgroup>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="members" class="span2" id="members" value="<?php echo $_GET['members'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>用户列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th>ID</th>
              <th>账号</th>
              <th>昵称</th>
              <th>用户组</th>
              <th>最后登陆IP</th>
              <th style="width:80px;"><a class="fa fa-clock-o tip-top" title="最后登陆时间"></a></th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){?>
            <tr id="id<?php echo $rs[$i]['uid'] ; ?>">
              <td><?php if($rs[$i]['uid']!="1"){?><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['uid'] ; ?>" /><?php } ; ?></td>
              <td><?php echo $rs[$i]['uid'] ; ?></td>
              <td><a class="tip-top" title="注册时间:<?php if($rs[$i]['regtime']) echo get_date($rs[$i]['regtime'],"Y-m-d") ; ?><hr />累计登陆次数:<?php echo $rs[$i]['logintimes'] ; ?>"><?php echo $rs[$i]['username'] ; ?></a></td>
              <td><?php echo $rs[$i]['nickname'] ; ?></td>
              <td><a href="<?php echo APP_DOURI; ?>&gid=<?php echo $rs[$i]['gid'] ; ?>"><?php echo $this->groupAdmincp->array[$rs[$i]['gid']]['name'] ; ?></a></td>
              <td><?php echo $rs[$i]['lastip'] ; ?></td>
              <td><?php if($rs[$i]['lastlogintime']) echo get_date($rs[$i]['lastlogintime'],"Y-m-d") ; ?></td>
              <td>
                <a href="<?php echo APP_URI; ?>&do=job&id=<?php echo $rs[$i]['uid'] ; ?>" class="btn btn-small"><i class="fa fa-bar-chart-o"></i> 统计</a>
                <a href="<?php echo __ADMINCP__; ?>=article&userid=<?php echo $rs[$i]['uid'] ; ?>" class="btn btn-small"><i class="fa fa-list-alt"></i> 文章</a>
                <a href="<?php echo APP_URI; ?>&do=add&id=<?php echo $rs[$i]['uid'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                <?php if($rs[$i]['uid']!="1"){ ; ?>
                <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $rs[$i]['uid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a>
                <?php } ; ?>
                </td>
            </tr>
            <?php if($_GET['job']){
    		$job->count_post($rs[$i]['uid']);
    		?>
            <tr>
              <td colspan="10"><div class="job">
                  <table class="table table-bordered table-hover span6">
                    <thead>
                      <tr>
                        <th><i class="fa fa-calendar"></i> 工作统计</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>今天(<?php echo date('Y-m-d',$job->today['start']) ;?>)已经发布<?php echo $job->today['count'] ;?>篇文章</td>
                      </tr>
                      <tr>
                        <td>昨天(<?php echo date('Y-m-d',$job->yesterday['start']) ;?>)已经发布<?php echo $job->yesterday['count'] ;?>篇文章</td>
                      </tr>
                      <tr>
                        <td>上月已发布<?php echo $job->pmonth['count'] ;?>篇文章 从 <?php echo date('Y-m-d',$job->pmonth['start']) ;?> 至 <?php echo date('Y-m-d',$job->pmonth['end']) ;?>,平均<?php echo round($job->pmonth['count']/$job->pmonth['t'])?>篇/天</td>
                      </tr>
                      <tr>
                        <td>本月已发布<?php echo $job->month['count'] ;?>篇文章 从 <?php echo date('Y-m-d',$job->month['start']) ;?> 至 <?php echo date('Y-m-d',$job->month['end']) ;?>,平均<?php echo round($job->month['count']/$job->month['t'])?>篇/天</td>
                      </tr>
                      <tr>
                        <td>总计已发布<?php echo $job->total ;?>篇文章</td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="clearfloat"></div>
                </div></td>
            </tr>
            <?php }  ?>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="10"><div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
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
