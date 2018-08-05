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
admincp::head(false);
?>
<div class="alert mb10">
  <strong>警告!</strong>
  发送模板信息请遵循微信的发送规则，如果烂用群发功能。<br />将有可能导致接口被封禁，严重者可能被封禁账号。
  <br />具体规则请查看,微信公众平台<a target="_blank" href="https://developers.weixin.qq.com/miniprogram/dev/api/notice.html#%E5%8F%91%E9%80%81%E6%A8%A1%E6%9D%BF%E6%B6%88%E6%81%AF">下发信息规则</a>
</div>
<div class="widget-box widget-plain">
  <div class="widget-content nopadding">
    <table class="table table-bordered table-condensed table-hover">
      <thead>
        <tr>
          <th>内容示例</th>
          <th>发送内容</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="span4">
            <?php echo nl2br($template['example']); ?>
          </td>
          <td>
            <form action="<?php echo APP_FURI; ?>&do=tmplmsg_send" method="post" class="form-inline" target="iPHP_FRAME">
              <input name="id" type="hidden" value="<?php echo $this->id ; ?>" />
              <input name="template_id" type="hidden" value="<?php echo $tid; ?>" />
              <div class="input-prepend">
                  <span class="add-on">发送对象</span>
                  <select name="touser" id="touser" class="span6 chosen-select">
                      <option value="0"> === 请选择 ==== </option>
                      <option value="all"> 所有用户(不推荐)</option>
                      <option value="userid"> 指定用户 </option>
                  </select>
              </div>
              <div class="clearfloat mb10"></div>
              <div class="touserid hide">
                <div class="input-prepend input-append">
                    <span class="add-on">用户ID</span>
                    <input type="text" name="userid" class="span4" />
                    <a target="_blank" href="<?php echo __ADMINCP__; ?>=user&wxappid=<?php echo $_GET['wxappid'] ; ?>" class="btn"><i class="fa fa-users "></i></a>
                </div>
                <span class="help-inline">请填写该小程序下的用户ID,多个请用,分隔</span>
                <div class="clearfloat mb10"></div>
              </div>
            <?php
            if($content) foreach ($content AS $k => $value) {
              list($t,$kdt) = explode('{{', $value);
              $kd = str_replace(array('keyword','.DATA}}'), '', $kdt);
            ?>
              <div class="input-prepend input-append">
                  <span class="add-on"><?php echo $t; ?></span>
                  <input type="text" name="content[<?php echo $k; ?>]" class="span4" />
                  <span class="add-on"><input type="checkbox" name="big" value="<?php echo $kd; ?>"/>放大</span>
              </div>
              <div class="clearfloat mb10"></div>
            <?php }?>
              <div class="input-prepend">
                  <span class="add-on">跳转页面</span>
                  <input type="text" name="page" class="span4" />
              </div>
              <span class="help-inline">点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例 pages/index/index?foo=bar）。该字段不填则模板无跳转。</span>
              <div class="form-actions">
                  <button class="btn btn-primary" type="submit"><i class="fa fa-send"></i>
                      开始发送
                  </button>
              </div>
            </form>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script>
$(function() {

  $("#touser").change(function(event) {
    if(this.value=='userid'){
      $(".touserid").show();
    }else{
      $(".touserid").hide();
      $("#userid").val('');
    }
  });

  $("input:checkbox").click(function(event) {
    var checked = $(this).prop("checked");
    $('input:checkbox').each(function() {
        this.checked = false;
        $.uniform.update($(this));
    });
    this.checked = checked;
    $.uniform.update($(this));
  });
});
</script>
<?php admincp::foot(); ?>
