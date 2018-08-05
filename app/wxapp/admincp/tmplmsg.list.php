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
<div class="widget-box widget-plain">
  <div class="widget-content nopadding">
    <table class="table table-bordered table-condensed table-hover">
      <thead>
        <tr>
          <th><i class="fa fa-arrows-v"></i></th>
          <th>模板ID</th>
          <th>标题</th>
          <th>关键词</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php
          $template_list = array();
          if($response['list']) foreach ($response['list'] AS $k => $value) {
              $template_id = $value['template_id'];
              $template_list[$template_id] = $value;
        ?>
        <tr id="<?php echo $template_id; ?>">
          <td><?php echo $k+1; ?></td>
          <td><?php echo $template_id ; ?></td>
          <td><?php echo $value['title']; ?></td>
          <td><?php echo nl2br($value['content']); ?></td>
          <td>
            <a href="<?php echo APP_FURI; ?>&do=tmplmsg_content&id=<?php echo $this->id ; ?>&template_id=<?php echo $template_id; ?>&wxappid=<?php echo $_GET['wxappid']; ?>" class="btn btn-info btn-small"
              data-toggle="modal" data-target="#iCMS-MODAL" data-meta='{"width":"85%","height":"600px"}'
              title="发送<?php echo $value['title']; ?>"><i class="fa fa-send"></i> 发送</a>
          </td>
        </tr>
        <?php }else{ ?>
        <tr>
          <td colspan="5" class="alert">
            暂无个人模板,请选到微信小程序公从平台添加设置个人模板
          </td>
        </tr>
        <?php }?>
      </tbody>
    </table>
  </div>
</div>
<?php $template_list && iCache::set('wxapp/template_list',$template_list,3600); ?>
<?php admincp::foot(); ?>
