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
admincp::head($navbar);
?>
<script type="text/javascript">
function modal_callback(obj){
  var state = window.parent.modal_<?php echo $callback;?>('<?php echo $target; ?>',obj);
  if(state=='off'){
    window.top.iCMS_MODAL.destroy();
  }
}
$(function(){
    $('input[type=file]').uniform();
    $(".checkAll").click(function() {
        var target = $(this).attr('data-target');
        $('[data-click="media_id"]:checkbox', $(target)).each(function() {
            if (this.checked) {
              modal_callback(this);
            }
        });
    });
    $('[data-click]').click(function() {
      modal_callback(this);
    });
});
</script>
<style>
.op { text-align: right !important; padding-right: 28px !important; }
#media-explorer tbody .checker { margin-left: 6px !important; }
#media-explorer .pwd { float:left; padding: 5px; margin: 6px 15px 0 10px; }
#media-explorer .pwd a { color: #fff; }
#media-explorer td { line-height: 2em; }
#upload-box { display:none; }
</style>
<?php if($from!='modal'){?>
<div class="iCMS-container">
<?php } ?>
  <div class="widget-box <?php if($from=='modal'){?> widget-plain<?php } ?>" id="media-explorer">
    <div class="widget-title"> <!-- <span class="icon"><input type="checkbox" class="checkAll" data-target="#media-explorer" /></span> -->
      <h5 class="brs">素材管理</h5>
      <span class="label label-important mt10 ml10">提示:点击选择框选择</span>
      <div class="buttons">
        <!-- <a href="<?php echo APP_FURI; ?>&do=media_upload" title="上传文件" data-toggle="modal" data-meta='{"width":"98%","height":"580px"}' class="btn btn-mini btn-primary" id="upload"> <i class="fa fa-upload"></i> 上传素材</a> -->
      </div>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i><!-- <span class="icon"><input type="checkbox" class="checkAll" data-target="#media-explorer" /></span> --></th>
              <th>文件名</th>
              <th style="width:130px;">最后修改时间</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($rs['items'] as $key => $value) {
            ?>
            <tr id="<?php echo $value['media_id'] ; ?>">
              <td><input type="checkbox" value="<?php echo $value['media_id'] ; ?>" data-click="media_id"/></td>
              <td><?php echo $value['name'] ; ?></td>
              <td><?php echo get_date($value['update_time'],'Y-m-d H:i'); ?></td>
              <td class="op">
<!--                   <a class="btn btn-small" href="<?php echo $href; ?>" data-toggle="modal" title="上传并覆盖文件"><i class="fa fa-upload"></i> 上传</a>
                  <?php if(0){?>
              	  <a class="btn btn-small" href="<?php echo $value['url']; ?>" data-toggle="modal" title="查看文件"><i class="fa fa-eye"></i> 查看</a>
              	  <?php }?>
              	  <a class="btn btn-small" href="<?php echo APP_URI; ?>&do=media_del&media_id=<?php echo $value['media_id'] ; ?>" target="iPHP_FRAME" title="删除文件" onclick="return confirm('确定要删除?');"><i class="fa fa-trash-o"></i> 删除</a>
 -->              </td>
            </tr>
            <?php }  ?>
          </tbody>
        </table>

      </form>
    </div>
  </div>
  <?php if($from!='modal'){?>
</div>
<?php } ?>
<?php admincp::foot();?>
