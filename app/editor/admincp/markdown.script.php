<?php
    $editor_id = 'ED_'.substr(md5(uniqid(true)),8,16);;
?>
<link rel="stylesheet" href="./app/admincp/ui/editor.md/css/editormd.min.css" />
<style>
.editormd-menu>li{padding: 0px;margin-top: 4px;}
.editormd-form input[type=text], .editormd-form input[type=number]{height: 36px;}
.editormd-form input[type=number]{width: 45px !important;}
.editormd-dialog-footer{padding: 0px;}
.editormd-form input[type=text], .editormd-form input[type=number]{color: #999 !important;border:1px solid #ddd;}
.editormd-dialog-container .editormd-btn, .editormd-dialog-container button, .editormd-dialog-container input[type=submit], .editormd-dialog-footer .editormd-btn, .editormd-dialog-footer button, .editormd-dialog-footer input[type=submit], .editormd-form .editormd-btn, .editormd-form button, .editormd-form input[type=submit]{padding: 1px 2px;}
</style>
<script type="text/javascript" charset="utf-8" src="./app/admincp/ui/editor.md/editormd.min.js"></script>
<script type="text/javascript" charset="utf-8" src="./app/admincp/ui/iCMS.editormd.js"></script>

<script>
var <?php echo $editor_id;?> = iCMS.editormd;
<?php echo $editor_id;?>.create("<?php echo $id;?>");

(function($) {
    $(function(){
    })
})(jQuery);
</script>
<div class="clearfix"></div>
<div class="input-prepend">
  <div class="btn-group">
    <button type="button" class="btn" onclick="javascript:<?php echo $editor_id;?>.insPageBreak();"><i class="fa fa-ellipsis-h"></i> 插入分页符</button>
    <button type="button" class="btn" onclick="javascript:<?php echo $editor_id;?>.delPageBreakflag();"><i class="fa fa-ban"></i> 删除分页符</button>
  </div>
</div>
<div class="clearfix mt10"></div>
