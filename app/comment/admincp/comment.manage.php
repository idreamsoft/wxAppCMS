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
  <?php if($_GET['sub']=="on"){ ?>
  iCMS.checked('#sub');
  <?php } ?>

	$("#<?php echo APP_FORMID;?>").batch();

  $(".view_reply").popover({
    html:true,
    content:function(){
      var a = $(this);
      $.get('<?php echo APP_URI; ?>&do=get_reply',{'id': a.attr('data-id')},
        function(html) {
          update_popover(html,a);
        }
      );
      return '<p><img src="./app/admincp/ui/img/ajax_loader.gif" /></p>';
    }
  });
});
function update_popover(html,a){
  $('.popover-content','.popover').html(html);
  var close = $('<button type="button" class="close">×</button>');
  close.click(function(event) {
    a.popover('toggle');
  });
  $('.popover-title','.popover').append(close);
}
</script>
<style>
.popover{max-width: 600px;}
.popover.right .arrow{top:65px;}
</style>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <div class="input-prepend input-append"> <span class="add-on">栏目</span>
          <select name="cid" id="cid" class="span3 chosen-select">
            <option value="0">所有栏目</option>
            <?php echo category::priv('cs')->select() ; ?>
          </select>
          <span class="add-on">
          <input type="checkbox" name="sub" id="sub"/>
        子栏目 </span> </div>
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
      <h5><?php if($appid){echo apps::get_label($appid);}?>评论列表</h5>
      <span title="总共<?php echo $total;?>条评论" class="badge badge-info tip-left"><?php echo $total;?></span>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <ul class="recent-comments">
          <?php if($rs){
          foreach ($rs as $key => $value) {
            $url       = commentApp::redirect_url($value);
            $user      = user::info($value['userid'],$value['username']);
            $app_label = apps::get_label($value['appid']);
          ?>
          <li id="id-<?php echo $value['id'] ; ?>">
            <div class="user-thumb">
              <a href="<?php echo $user['url'] ; ?>" target="_blank" class="avatar">
              <img width="50" height="50" alt="<?php echo $user['name'] ; ?>" src="<?php echo $user['avatar'] ; ?>">
              </a>
              <div class="claerfix mb10"></div>
              <a href="<?php echo __ADMINCP__; ?>=user&do=update&id=<?php echo $value['userid'] ; ?>&_args=status:2" class="btn btn-inverse btn-mini tip-bottom" title="加入黑名单,禁止用户登陆" target="iPHP_FRAME">黑名单</a>
            </div>
            <div class="comments">
              <input type="checkbox" name="id[]" value="<?php echo $value['id'] ; ?>" />
              <span class="user-info">
                <a href="<?php echo APP_URI; ?>&userid=<?php echo $value['userid'] ; ?>" class="tip" title="查看该用户所有评论"><span class="label label-info"><?php echo $user['name'] ; ?></span></a>
                在<?php echo $app_label; ?>
                <a href="<?php echo APP_URI; ?>&iid=<?php echo $value['iid'] ; ?>" class="tip" title="查看该<?php echo $app_label;?>所有评论"><?php echo $value['title'] ; ?></a>
                <a href="<?php echo $url; ?>" target="_blank">[原文]</a>
                <?php if($value['reply_id']){?>
                  对<a href="<?php echo APP_URI; ?>&userid=<?php echo $value['reply_uid'] ; ?>" class="tip" title="查看该用户所有评论"><span class="label label-success"><?php echo $value['reply_name'] ; ?></span></a>的回帖发表评论
                <?php }else{?>
                  发表评论
                <?php } ?>
                <a href="<?php echo APP_URI; ?>&ip=<?php echo $value['ip'] ; ?>" class="tip" title="查看该IP所有评论"><span class="label label-inverse">IP：<?php echo $value['ip'] ; ?></span></a>
                <?php if(!$value['status']){?>
                 <span class="label label-warning">未审核</span>
                <?php } ?>
              </span>
              <p>
              <?php echo nl2br($value['content']); ?>
              <?php if($value['reply_id']){?>
                <div class="claerfix"></div>
                <a href="javascript:;" class="btn view_reply" data-id="<?php echo $value['reply_id'] ; ?>" title="<?php echo $value['reply_name'] ; ?>的回帖" >点击查看 <?php echo $value['reply_name'] ; ?> 的回帖</a>
              <?php } ?>
              </p>
              <div class="claerfix"></div>
              <span class="label"><?php echo get_date($value['addtime'],'Y-m-d H:i:s');?></span>
              <span class="label label-info"><i class="fa fa-thumbs-o-up"></i> <?php echo $value['up'] ; ?></span>
              <?php if(!$value['status']){?>
              <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $value['id'] ; ?>&_args=status:1" class="btn btn-success btn-mini" target="iPHP_FRAME">通过审核</a>
              <?php } ?>
              <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $value['id'] ; ?>" class="btn btn-danger btn-mini" target="iPHP_FRAME" onclick="return confirm('确定要删除?');">删除</a>
            </div>
            <div class="claerfix mb10"></div>
          </li>
          <?php }} ?>
        </ul>
    </form>
    <table class="table table-bordered table-condensed table-hover">
      <tr>
        <td><div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
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
  </table>
  </div>
</div>
</div>
<?php admincp::foot();?>
