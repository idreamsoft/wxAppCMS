<button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
<div class="clearfloat"></div>
<div class="fields-fluid">
  <ul id="custom_field_list" class="iFormer-layout">

  </ul>
  <div class="clearfloat mt10"></div>
</div>
<div class="clearfloat mt10"></div>
<div class="alert alert-info alert-block">
  <h5><i class="fa fa-support"></i> 注意事项</h5>
  <p><i class="fa fa-arrows-h"></i> 换行符 双击可删除</p>
  <p><i class="fa fa-arrows-h"></i> 换行符 属于占位符 最终界面上将以10px空白替换.效果请参考文章添加页</p>
  <p>本界面元素只作编辑用,最终界面展现效果请使用预览功能</p>
  <p>基础字段可移动位置,半透明显示,不可编辑,不可删除.</p>
  <p>若需要无基础字段功能的简单应用请使用自定义表单</p>
  <p>预览前请先提交修改</p>
</div>
<?php if($this->id && $_GET['app']=='apps'){?>
<a href="<?php echo __ADMINCP__; ?>=<?php echo $rs['app'] ; ?>&do=add&appid=<?php echo $this->id ; ?>&preview"
    class="btn btn-success" data-toggle="modal" data-target="#iCMS-MODAL" data-meta='{"width":"85%","height":"640px"}'>
    <i class="fa fa-eye"></i> 预览表单
</a>
<?php }?>
<script type="text/javascript" src="./app/admincp/ui/jquery/jquery-ui.min.js"></script>
<link rel="stylesheet" href="./app/former/ui/iFormer.css" type="text/css" />
<script type="text/javascript" src="./app/former/ui/iFormer.js"></script>
<script type="text/javascript">
<?php
if($rs['fields']){
  $field_array = apps_mod::get_field_array($rs['fields'],true);
  foreach ($field_array as $key => $value) {
    $readonly = apps_mod::base_fields_key($value['name']);
    echo "iFormer.render($('div'),".json_encode($value).",null,'".$value['id']."',".($readonly?'true':'false').").appendTo('#custom_field_list');";
  }
  echo "$('#custom_field_list').append('<div class=\"clearfloat\"></div>');";
}
?>

$(function() {
    $(".iFormer-layout").sortable({
        placeholder: "ui-state-highlight",
        cancel: ".clearfloat",
        delay:300,
        sort: function( event, ui ) {
            var target = $(event.target);
            $(".clearfloat",target).remove();
            target.append('<div class="clearfloat"></div>');
        },
        receive: function(event, ui) {
            var helper = ui.helper,
            tag        = helper.attr('tag'),
            aclass     = helper.attr('ui-class'),
            field      = helper.attr('field'),
            type       = helper.attr('type'),
            label      = helper.attr('label'),
            after      = helper.attr('label-after'),
            len        = helper.attr('len'),
            id         = iCMS.random(6, true);
            var html   = iFormer.render(helper,{
                'id': id,
                'label': (label || '表单') + id,
                'label-after':after,
                'field': field,
                'class': aclass,
                'name': id,
                'default': '',
                'type': type,
                'len': len
            });
            helper.replaceWith(html);
        }
    }).disableSelection();

    $("[i='layout'],[i='field']",".iFormer-design").draggable({
        placeholder: "ui-state-highlight",
        connectToSortable: ".iFormer-layout",
        helper: "clone",
        revert: "invalid",
    }).disableSelection();

    $(".iFormer-design").draggable().disableSelection();
});
</script>
<?php include admincp::view("former.design","former");?>
