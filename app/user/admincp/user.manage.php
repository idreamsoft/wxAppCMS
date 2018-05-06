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
  <?php if(isset($_GET['status'])){  ?>
  iCMS.select('status',"<?php echo $_GET['status'] ; ?>");
  <?php } ?>
  <?php if($_GET['orderby']){ ?>
  iCMS.select('orderby',"<?php echo $_GET['orderby'] ; ?>");
  <?php } ?>
  <?php if(isset($_GET['pid']) && $_GET['pid']!='-1'){  ?>
  iCMS.select('pid',"<?php echo (int)$_GET['pid'] ; ?>");
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
        <div class="input-prepend"> <span class="add-on">账号状态</span>
          <select name="status" id="status" class="span2 chosen-select">
            <option value="">无</option>
            <option value="0">禁用</option>
            <option value="1">正常</option>
            <option value="2">黑名单</option>
            <option value="3">登陆封禁</option>
            <?php echo propAdmincp::get("status") ; ?>
          </select>
        </div>
        <div class="input-prepend"> <span class="add-on">注册IP</span>
          <input type="text" name="regip" id="regip" class="span2" value="<?php echo $_GET['regip'] ; ?>"/>
        </div>
        <div class="input-prepend"> <span class="add-on">最后登陆IP</span>
          <input type="text" name="loginip" id="loginip" class="span2" value="<?php echo $_GET['loginip'] ; ?>"/>
        </div>
        <div class="clearfix mt10"></div>
        <div class="input-prepend"> <span class="add-on">用户属性</span>
          <select name="pid" id="pid" class="span2 chosen-select">
            <option value="-1">所有用户</option>
            <option value="0">普通用户[pid='0']</option>
            <?php echo propAdmincp::get("pid") ; ?>
          </select>
        </div>
        <div class="input-prepend">
          <span class="add-on">排序</span>
          <select name="orderby" id="orderby" class="span2 chosen-select">
            <option value=""></option>
            <optgroup label="降序"><?php echo $orderby_option['DESC'];?></optgroup>
            <optgroup label="升序"><?php echo $orderby_option['ASC'];?></optgroup>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
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
              <th style="width:130px;"><a class="fa fa-clock-o tip-top" title="注册时间/最后登陆时间"></a></th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){
               $url = iURL::router(array('uid:home',$rs[$i]['uid']));
            ?>
            <tr id="id<?php echo $rs[$i]['uid'] ; ?>">
              <td><?php if($rs[$i]['uid']!="1"){ ; ?><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['uid'] ; ?>" /><?php } ; ?></td>
              <td><a href="<?php echo $url; ?>" target="_blank"><?php echo $rs[$i]['uid'] ; ?></a></td>
              <td><a class="tip-top" title="
                粉丝:<?php echo $rs[$i]['fans'] ; ?><br />
                关注:<?php echo $rs[$i]['follow'] ; ?><br />
                评论:<?php echo $rs[$i]['comments'] ; ?><br />
                文章:<?php echo $rs[$i]['article'] ; ?><br />
                点击:<?php echo $rs[$i]['hits'] ; ?><br />
                周点击:<?php echo $rs[$i]['hits_week'] ; ?><br />
                月点击:<?php echo $rs[$i]['hits_month'] ; ?><br />
                "><?php echo $rs[$i]['username'] ; ?></a></td>
              <td><?php echo $rs[$i]['nickname'] ; ?>
                <?php if($rs[$i]['status']=="2"){
                  echo '<span class="label label-inverse">黑名单</span>';
                }else if($rs[$i]['status']=="0"){
                  echo '<span class="label">禁止</span>';
                } ?>
              </td>
              <td>
                <a href="<?php echo APP_URI; ?>&gid=<?php echo $rs[$i]['gid'] ; ?>"><?php echo $this->groupAdmincp->array[$rs[$i]['gid']]['name'] ; ?></a>
                <br />
                <?php $rs[$i]['pid'] && propAdmincp::flag($rs[$i]['pid'],$propArray,APP_DOURI.'&pid={PID}&'.$uri);?>
              </td>
              <td><?php echo $rs[$i]['lastloginip'] ; ?></td>
              <td>
                <?php if($rs[$i]['regdate']) echo get_date($rs[$i]['regdate'],"Y-m-d H:i:s") ; ?><br />
                <?php if($rs[$i]['lastlogintime']) echo get_date($rs[$i]['lastlogintime'],"Y-m-d") ; ?></td>
              <td>
                <?php if(members::is_superadmin()){?>
                <a href="<?php echo APP_URI; ?>&do=login&id=<?php echo $rs[$i]['uid'] ; ?>" class="btn btn-small" target="_blank">登陆</a>
                 <?php } ?>
                <a href="<?php echo __ADMINCP__; ?>=article&do=user&userid=<?php echo $rs[$i]['uid'] ; ?>&pt=0" class="btn btn-small"><i class="fa fa-list-alt"></i> 文章</a>
                <a href="<?php echo APP_URI; ?>&do=add&id=<?php echo $rs[$i]['uid'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $rs[$i]['uid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a>
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
                      <li><a data-toggle="batch" data-action="prop"><i class="fa fa-puzzle-piece"></i> 设置用户属性</a></li>
                      <li class="divider"></li>
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
