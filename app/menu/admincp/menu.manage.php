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
<link rel="stylesheet" href="./app/admincp/ui/jquery/treeview-0.1.0.css" type="text/css" />
<script type="text/javascript" src="./app/admincp/ui/template-3.0.js"></script>
<script type="text/javascript" src="./app/admincp/ui/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="./app/admincp/ui/jquery/treeview-0.1.0.js"></script>
<script type="text/javascript" src="./app/admincp/ui/jquery/treeview-0.1.0.async.js"></script>
<script id="tree_li" type="text/html">
{{if caption=='-'}}
<span class="operation">
    <a href="<?php echo APP_FURI;?>&do=del&id={{id}}" class="btn btn-danger btn-small" onClick="return confirm('确定要删除此菜单?');" target="iPHP_FRAME">
      <i class="fa fa-trash-o"></i> 删除
    </a>
</span>
<div class="separator">
    <span class="sortnum" style="display:none;">
        <input type="text" data-id="{{id}}" name="sort[{{id}}]" value="{{sort}}"/>
    </span>
</div>
{{else}}
<div class="row-fluid">
    <span class="sortnum" style="display:none;">
        <input type="text" data-id="{{id}}" name="sort[{{id}}]" value="{{sort}}"/>
    </span>
    <span class="name">{{caption}}</span>
    <span class="operation">
        <a href="<?php echo APP_URI;?>&do=copy&id={{id}}" title="复制本菜单设置"  class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-copy"></i> 复制</a>
        <a href="<?php echo APP_URI;?>&do=add&rootid={{id}}" class="btn btn-info btn-small"><i class="fa fa-plus-square"></i> 子菜单</a>
        <a href="<?php echo APP_FURI;?>&do=addseparator&rootid={{id}}" class="btn btn-success btn-small" target="iPHP_FRAME"><i class="fa fa-minus-square"></i> 分隔符</a>
        <a href="<?php echo APP_URI;?>&do=add&id={{id}}" title="编辑菜单设置"  class="btn btn-primary btn-small"><i class="fa fa-edit"></i> 编辑</a>
        <a href="<?php echo APP_FURI;?>&do=del&id={{id}}" class="btn btn-danger btn-small" onClick="return confirm('确定要删除此菜单?');" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 删除</a>
    </span>
</div>
{{/if}}
</script>
<script type="text/javascript">
var upordurl="<?php echo APP_URI; ?>&do=updateorder";
$(function(){
    $("#tree").treeview({
      tpl:'tree_li',
      url:'<?php echo APP_URI; ?>&do=ajaxtree&expanded=1&appname=<?php echo $_GET['appname'];?>',
      collapsed: false,
      sortable: true,
      animated: "medium",
      control:"#treecontrol",
    });
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-list"></i> </span>
      <h5>后台菜单</h5>
      <div id="treecontrol"> <a style="display:none;"></a> <a style="display:none;"></a> <a class="btn btn-mini btn-info" href="javascript:;">展开/收缩</a></div>
      <a style="margin: 10px;" class="btn btn-mini" href="<?php echo __ADMINCP__; ?>=cache&do=menu" target="iPHP_FRAME"><i class="fa fa-refresh"></i> 更新缓存</a>
    </div>
    <div class="widget-content nopadding">
      <div id="menu-list" class="tab-content">
        <div id="menu-tree" class="row-fluid menu-treeview">
          <ul id="tree">
            <p id="tree-loading"><img src="./app/admincp/ui/img/ajax_loader.gif" />
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php admincp::foot();?>
