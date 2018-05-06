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
	$("#<?php echo APP_FORMID;?>").batch();
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
      <div class="pull-right">
        <a style="margin: 10px;" class="btn btn-success btn-mini" href="<?php echo APP_FURI; ?>&do=cache" target="iPHP_FRAME"><i class="fa fa-refresh"></i> 更新缓存</a>
      </div>
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
      <h5>属性列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th style="width:20px;">ID</th>
              <th style="width:30px;">排序</th>
              <th>值</th>
              <th>名称</th>
              <th>字段</th>
              <th>应用</th>
              <th>栏目</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
        <?php
        $categoryArray = category::multi_get($rs,'cid');
        for($i=0;$i<$_count;$i++){
          $C = (array)$categoryArray[$rs[$i]['cid']];
        ?>
            <tr id="id<?php echo $rs[$i]['pid'] ; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['pid'] ; ?>" /></td>
              <td><?php echo $rs[$i]['pid'] ; ?></td>
              <td class="sortnum"><input type="text" name="sortnum[<?php echo $rs[$i]['pid'] ; ?>]" value="<?php echo $rs[$i]['sortnum'] ; ?>" tid="<?php echo $rs[$i]['pid'] ; ?>"/></td>
              <td><?php echo $rs[$i]['val'] ; ?></td>
              <td><?php echo $rs[$i]['name'] ; ?></td>
              <td><a href="<?php echo admincp::uri(array("field"=>$rs[$i]['field']),$uriArray); ?>"><?php echo $rs[$i]['field'] ; ?></a></td>
              <td><a href="<?php echo admincp::uri(array("_app"=>$rs[$i]['app']),$uriArray); ?>"><?php echo $rs[$i]['app'] ; ?></a></td>
              <td><a href="<?php echo admincp::uri(array("cid"=>$rs[$i]['cid']),$uriArray); ?>"><?php echo $C['name'] ; ?></a></td>
              <td><?php if($rs[$i]['status']=="1"){ ?>
                <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['pid'] ; ?>&_args=status:0" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-power-off"></i> 禁用</a>
                <?php } ?>
                <?php if($rs[$i]['status']=="0"){ ?>
                <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['pid'] ; ?>&_args=status:1" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-play-circle"></i> 启用</a>
                <?php } ?>
                <a href="<?php echo APP_URI; ?>&do=add&pid=<?php echo $rs[$i]['pid'] ; ?>&act=copy" class="btn btn-small"><i class="fa fa-copy "></i> 复制</a>
                <a href="<?php echo APP_URI; ?>&do=add&pid=<?php echo $rs[$i]['pid'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                <a href="<?php echo APP_FURI; ?>&do=del&pid=<?php echo $rs[$i]['pid'] ; ?>" target="iPHP_FRAME" class="del btn btn-danger btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a></td>
            </tr>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="9"><div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="refresh"><i class="fa fa-refresh"></i> 更新缓存</a></li>
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
