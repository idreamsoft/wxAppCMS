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
	$("#<?php echo APP_FORMID;?>").batch();
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <input type="hidden" name="do" value="<?php echo admincp::$APP_DO;?>" />
        <input type="hidden" name="rid" value="<?php echo $_GET['rid'];?>" />
        <div class="input-prepend input-append"> <span class="add-on">栏目</span>
          <select name="cid" id="cid" class="span3 chosen-select">
            <option value="0">所有栏目</option>
            <?php echo category::select(); ?>
          </select>
          <span class="add-on">
          <input type="checkbox" name="sub" id="sub"/>
          子栏目 </span>
        </div>
        <div class="input-prepend input-append"><span class="add-on"><i class="fa fa-calendar"></i></span>
          <input type="text" class="ui-datepicker" name="starttime" value="<?php echo $_GET['starttime']; ?>" placeholder="开始时间" />
          <span class="add-on">-</span>
          <input type="text" class="ui-datepicker" name="endtime" value="<?php echo $_GET['endtime']; ?>" placeholder="结束时间" />
          <span class="add-on"><i class="fa fa-calendar"></i></span>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $_GET['perpage'] ? $_GET['perpage'] : 20; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span>
        </div>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords']; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>采集列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th>ID</th>
              <th>内容</th>
              <th>栏目</th>
              <th class="span2">采集/发布时间</th>
              <th>appid</th>
              <th>内容ID</th>
              <th>状态/发布</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $categoryArray = category::multi_get($rs,'cid');
            for($i=0;$i<$_count;$i++){
              $C = (array)$categoryArray[$rs[$i]['cid']];
            ?>
            <tr id="id<?php echo $rs[$i]['id']; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['id']; ?>" /></td>
              <td><?php echo $rs[$i]['id']; ?></td>
              <td><?php echo $rs[$i]['title']; ?><br />
                <?php echo $rs[$i]['url']; ?></td>
              <td>
                <a href="<?php echo APP_URI; ?>&do=manage&cid=<?php echo $rs[$i]['cid']; ?>&<?php echo $uri; ?>"><?php echo $C['name']; ?></a> <br />
                <a href="<?php echo APP_URI; ?>&do=manage&rid=<?php echo $rs[$i]['rid']; ?>&<?php echo $uri; ?>"><?php echo $ruleArray[$rs[$i]['rid']]; ?></a></td>
              <td><?php echo get_date($rs[$i]['addtime'], 'Y-m-d H:i'); ?>
                <br />
                <?php echo $rs[$i]['pubdate'] ? get_date($rs[$i]['pubdate'], 'Y-m-d H:i') : '未发布' ?>
              </td>
              <td><?php echo $rs[$i]['appid']; ?></td>
              <td><?php echo $rs[$i]['indexid']; ?></td>
              <td><?php echo $rs[$i]['status']; ?>/<?php echo $rs[$i]['publish']; ?></td>
              <td>
                <a href="<?php echo __ADMINCP__; ?>=files&indexid=<?php echo $rs[$i]['indexid'] ; ?>&method=database" class="tip-bottom" title="查看内容使用的图片" target="_blank"><i class="fa fa-picture-o"></i></a>
                <a href="<?php echo __ADMINCP__; ?>=spider&do=addproject&pid=<?php echo $rs[$i]['pid'] ; ?>" class="btn btn-small" target="_blank"><i class="fa fa-edit"></i> 编辑方案</a>
                <a href="<?php echo __ADMINCP__; ?>=spider&do=addrule&rid=<?php echo $rs[$i]['rid'] ; ?>" class="btn btn-small" target="_blank"><i class="fa fa-edit"></i> 编辑规则</a>
                <?php if($rs[$i]['indexid']){?>
                <a href="<?php echo __ADMINCP__; ?>=article&do=add&id=<?php echo $rs[$i]['indexid'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑内容</a>
                  <?php if(empty($rs[$i]['publish'])){?>
                  <a href="<?php echo APP_FURI; ?>&do=update&sid=<?php echo $rs[$i]['id']; ?>&_args=publish:1" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-flag"></i> 标识发布</a>
                  <?php }else{?>
                  <a href="<?php echo APP_FURI; ?>&do=publish&sid=<?php echo $rs[$i]['id']; ?>&pid=<?php echo $rs[$i]['pid']; ?>&indexid=<?php echo $rs[$i]['indexid'] ; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-edit"></i> 重新发布</a>
                  <?php }?>
                <?php }else{?>
                <a href="<?php echo APP_FURI; ?>&do=publish&sid=<?php echo $rs[$i]['id']; ?>&pid=<?php echo $rs[$i]['pid']; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-edit"></i> 发布</a>
                <a href="<?php echo APP_FURI; ?>&do=update&sid=<?php echo $rs[$i]['id']; ?>&_args=publish:1" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-flag"></i> 标识发布</a>
                <?php }?>
                <a href="<?php echo APP_URI; ?>&do=testdata&rid=<?php echo $rs[$i]['rid']; ?>&url=<?php echo $rs[$i]['url']; ?>" class="btn btn-small" data-toggle="modal" title="测试内容规则"><i class="fa fa-keyboard-o"></i> 测试</a>
                <a href="<?php echo APP_FURI; ?>&do=delspider&sid=<?php echo $rs[$i]['id']; ?>" target="iPHP_FRAME" class="del btn btn-small btn-danger" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a>
                <?php if($rs[$i]['indexid']){?>
                <a href="<?php echo APP_FURI; ?>&do=delcontent&sid=<?php echo $rs[$i]['id']; ?>&pid=<?php echo $rs[$i]['pid']; ?>&indexid=<?php echo $rs[$i]['indexid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small btn-danger" title='删除采集数据和发布的内容'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除 & 内容</a>
                <?php }?>
              </td>
            </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="9"><div class="pagination pagination-right" style="float:right;"><?php echo iPagination::$pagenav; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="delurl"><i class="fa fa-trash-o"></i> 删除</a></li>
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
<?php admincp::foot(); ?>
