<?php
    $editor_id = 'ED_'.substr(md5(uniqid(true)),8,16);;
?>
<script type="text/javascript">
window.catchRemoteImageEnable = <?php echo iCMS::$config['article']['catch_remote']?'true':'false';?>;
</script>
<script type="text/javascript" charset="utf-8" src="./app/admincp/ui/iCMS.ueditor.js"></script>
<script type="text/javascript" charset="utf-8" src="./app/admincp/ui/ueditor/ueditor.all.min.js"></script>
<script>
var <?php echo $editor_id;?> = iCMS.editor;
(function($) {
    $(function(){
        <?php echo $editor_id;?>.create("<?php echo $id;?>");
    })
})(jQuery);
</script>
<div class="clearfloat"></div>
<div class="input-prepend">
  <div class="btn-group">
    <button type="button" class="btn" onclick="javascript:<?php echo $editor_id;?>.insPageBreak();"><i class="fa fa-ellipsis-h"></i> 插入分页符</button>
    <button type="button" class="btn" onclick="javascript:<?php echo $editor_id;?>.delPageBreakflag();"><i class="fa fa-ban"></i> 删除分页符</button>
    <button type="button" class="btn" onclick="javascript:<?php echo $editor_id;?>.cleanup();"><i class="fa fa-magic"></i> 自动排版</button>
  </div>
</div>
<div class="clearfloat mt10"></div>
