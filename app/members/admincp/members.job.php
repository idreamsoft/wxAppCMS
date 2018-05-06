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
.job table { background: #FFF; text-align:center; }
.day td.today { background: rgb(123, 168, 7); color:#FFF; font-weight: bold; }
.job hr { height:1px; margin:2px 0px; border-bottom:0px; }
.job .week th { text-align:center; color:rgb(187, 31, 1); text-align:center; }
</style>
<div class="iCMS-container">
  <div class="well iCMS-well iCMS-account-job job">
    <h4><?php echo $rs->nickname ;?> 工作统计</h4>
    <table class="table table-bordered table-hover table-condensed span6">
      <thead>
        <tr>
          <th><i class="icon-calendar"></i> 统计</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>今天<span class="label label-success"><?php echo date('Y-m-d',$job->today['start']) ;?></span>已经发布<span class="label label-success"><?php echo $job->today['count'] ;?></span>篇文章 <span class="label label-important"><?php echo (int)$job->today['count0'] ;?></span>篇文章未审核</td>
        </tr>
        <tr>
          <td>昨天<span class="label label-success"><?php echo date('Y-m-d',$job->yesterday['start']) ;?></span>已经发布<span class="label label-success"><?php echo $job->yesterday['count'] ;?></span>篇文章</td>
        </tr>
        <tr>
          <td>上月已发布<span class="label label-success"><?php echo $job->pmonth['count'] ;?></span>篇文章 从 <?php echo date('Y-m-d',$job->pmonth['start']) ;?> 至 <?php echo date('Y-m-d',$job->pmonth['end']) ;?>,平均<?php echo round($job->pmonth['count']/$job->pmonth['t'])?>篇/天</td>
        </tr>
        <tr>
          <td>本月已发布<span class="label label-success"><?php echo $job->month['count'] ;?></span>篇文章 从 <?php echo date('Y-m-d',$job->month['start']) ;?> 至 <?php echo date('Y-m-d',$job->month['end']) ;?>,平均<?php echo round($job->month['count']/$job->month['t'])?>篇/天</td>
        </tr>
        <tr>
          <td>总计已发布<span class="label label-success"><?php echo $job->total ;?></span>篇文章 其中<span class="label label-important"><?php echo $job->count0post ;?></span>篇文章未审核</td>
        </tr>
      </tbody>
    </table>
    <div class="clearfloat"></div>
    <table class="table table-bordered table-condensed span6">
      <thead>
        <tr>
          <th colspan="7"><i class="icon-calendar"></i> 本月 <span class="label label-success">今天 <?php echo $month['year'];?>年<?php echo $month['month'];?>月<?php echo $month['day'];?>日 <?php echo $month['week'];?></span></th>
        </tr>
        <tr class="week">
          <th>日</th>
          <th>一</th>
          <th>二</th>
          <th>三</th>
          <th>四</th>
          <th>五</th>
          <th>六</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $month['days'];?>
      </tbody>
    </table>
    <table class="table table-bordered table-condensed span6">
      <thead>
        <tr>
          <th colspan="7"><i class="icon-calendar"></i> 上个月 <span class="label label-success"><?php echo $pmonth['year'];?>年<?php echo $pmonth['month'];?>月<?php echo $pmonth['day'];?>日 <?php echo $pmonth['week'];?></span></th>
        </tr>
        <tr class="week">
          <th>日</th>
          <th>一</th>
          <th>二</th>
          <th>三</th>
          <th>四</th>
          <th>五</th>
          <th>六</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $pmonth['days'];?>
      </tbody>
    </table>
    <div class="clearfloat"></div>
  </div>
</div>
<?php admincp::foot();?>
