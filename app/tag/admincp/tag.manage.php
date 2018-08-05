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
<style>
.modal{z-index: 999999 !important;}
</style>
<script type="text/javascript">
var upordurl="<?php echo APP_URI; ?>&do=updateorder";
$(function(){
	<?php if(isset($_GET['pid']) && $_GET['pid']!='-1'){  ?>
	iCMS.select('pid',"<?php echo (int)$_GET['pid'] ; ?>");
	<?php } if($_GET['cid']){  ?>
	iCMS.select('cid',"<?php echo $_GET['cid'] ; ?>");
	<?php } if($_GET['tcid']){  ?>
	iCMS.select('tcid',"<?php echo $_GET['tcid'] ; ?>");
	<?php } if($_GET['orderby']){ ?>
	iCMS.select('orderby',"<?php echo $_GET['orderby'] ; ?>");
	<?php } if($_GET['sub']=="on"){ ?>
	iCMS.checked('#sub');
	<?php } if($_GET['tfsub']=="on"){ ?>
  iCMS.checked('#tfsub');
	<?php } ?>
	$("#<?php echo APP_FORMID;?>").batch({
		mvtcid: function(){
			var select	= $("#tcid").clone().show()
				.removeClass("chosen-select")
				.attr("id",iCMS.random(3));
			$("option:first",select).remove();
			return select;
		}
	});
  $("#import").click(function(event) {
      var import_wrap = document.getElementById("import_wrap");
      iCMS.dialog({
        title: 'iCMS - 批理导入标签',
        content:import_wrap
      });
  });
  $("#local").click(function() {
      $("#localfile").click();
  });
  $("#localfile").change(function() {
      $("#import_wrap form").submit();
      $(this).val('');
  });
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
      <div class="pull-right">
        <button class="btn btn-success" type="button" id="import"><i class="fa fa-send"></i> 批理导入标签</button>
      </div>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <input type="hidden" name="uid" value="<?php echo $_GET['uid'] ; ?>" />
        <div class="input-prepend"> <span class="add-on">标签属性</span>
          <select name="pid" id="pid" class="span2 chosen-select">
            <option value="-1">所有标签</option>
            <?php echo $pid_select = propAdmincp::get("pid") ; ?>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">栏目</span>
          <select name="cid" id="cid" class="span3 chosen-select">
            <option value="0">所有栏目</option>
            <?php echo $cid_select = category::priv('cs')->select() ; ?>
          </select>
          <span class="add-on">
          <input type="checkbox" name="sub" id="sub"/>
          子栏目 </span> </div>
        <div class="input-prepend input-append"> <span class="add-on">分类</span>
          <select name="tcid" id="tcid" class="chosen-select">
            <option value="0">所有分类</option>
            <?php echo $tcid_select = category::appid($this->appid,'cs')->select() ;?>
          </select>
          <span class="add-on">
          <input type="checkbox" name="tfsub" id="tfsub"/>
          子分类 </span> </div>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend input-append"><span class="add-on"><i class="fa fa-calendar"></i> 发布时间</span>
          <input type="text" class="ui-datepicker" name="starttime" value="<?php echo $_GET['starttime'] ; ?>" placeholder="开始时间" />
          <span class="add-on">-</span>
          <input type="text" class="ui-datepicker" name="endtime" value="<?php echo $_GET['endtime'] ; ?>" placeholder="结束时间" />
          <span class="add-on"><i class="fa fa-calendar"></i></span>
        </div>
        <div class="input-prepend input-append"><span class="add-on"><i class="fa fa-calendar"></i> 添加时间</span>
          <input type="text" class="ui-datepicker" name="post_starttime" value="<?php echo $_GET['post_starttime'] ; ?>" placeholder="开始时间" />
          <span class="add-on">-</span>
          <input type="text" class="ui-datepicker" name="post_endtime" value="<?php echo $_GET['post_endtime'] ; ?>" placeholder="结束时间" />
          <span class="add-on"><i class="fa fa-calendar"></i></span>
        </div>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend">
          <span class="add-on">排序</span>
          <select name="orderby" id="orderby" class="span2 chosen-select">
            <option value=""></option>
            <optgroup label="降序"><?php echo $orderby_option['DESC'];?></optgroup>
            <optgroup label="升序"><?php echo $orderby_option['ASC'];?></optgroup>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append"> <span class="add-on">无缩略图
          <input type="checkbox" name="nopic" id="nopic"/>
          </span> <span class="add-on">缩略图
          <input type="checkbox" name="haspic" id="haspic"/>
          </span> </div>
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
      <h5>标签列表</h5>
    </div>
    <div class="widget-content nopadding">
    <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
      <table class="table table-bordered table-condensed table-hover">
        <thead>
          <tr>
            <th><i class="fa fa-arrows-v"></i></th>
            <th>ID</th>
            <th>排序</th>
            <th>标签</th>
            <th>来源字段</th>
            <th>栏目</th>
            <th>分类</th>
            <th>属性</th>
            <th style="width:48px;">统计</th>
            <th class="span2"><a class="fa fa-clock-o tip-top" title="更新时间/创建时间"></a></th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // category::unset_appid();
          $categoryArray  = category::appid(null)->multi_get($rs,'cid');
          $tcategoryArray = category::multi_get($rs,'tcid',$this->appid);

          for($i=0;$i<$_count;$i++){
              $C             = (array)$categoryArray[$rs[$i]['cid']];
              $TC            = (array)$tcategoryArray[$rs[$i]['tcid']];
              $iurl          = iURL::get('tag',array($rs[$i],$C,$TC));
              $rs[$i]['url'] = $iurl->href;
    	   ?>
          <tr id="id<?php echo $rs[$i]['id'] ; ?>">
            <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['id'] ; ?>" /></td>
            <td><?php echo $rs[$i]['id'] ; ?></td>
            <td class="sortnum"><input type="text" name="sortnum[<?php echo $rs[$i]['id'] ; ?>]" value="<?php echo $rs[$i]['sortnum'] ; ?>" tid="<?php echo $rs[$i]['id'] ; ?>"/></td>
            <td><?php if($rs[$i]['haspic'])echo '<img src="./app/admincp/ui/img/image.gif" align="absmiddle">';?>
              <a href="<?php echo $rs[$i]['url'] ; ?>" class="aTitle" target="_blank"><?php echo $rs[$i]['name'] ; ?></a>
          </div>

        <?php if($rs[$i]['haspic']){ ?>
        <a href="<?php echo APP_URI; ?>&do=preview&id=<?php echo $rs[$i]['id'] ; ?>" data-toggle="modal" title="预览"><img src="<?php echo iFS::fp($rs[$i]['pic']); ?>" style="height:120px;"/></a>
        <?php } ?>
          </td>
            <td><a href="<?php echo APP_DOURI; ?>&field=<?php echo $rs[$i]['field'] ; ?><?php echo $uri ; ?>"><?php echo $rs[$i]['field'] ; ?></a></td>
          <td>
            <a href="<?php echo APP_DOURI; ?>&cid=<?php echo $rs[$i]['cid'] ; ?><?php echo $uri ; ?>"><?php echo $C['name'] ; ?></a>
            <a href="<?php echo __ADMINCP__; ?>=article_category&do=add&cid=<?php echo $rs[$i]['cid']; ?>" target="_blank"><i class="fa fa-edit"></i></a>
          </td>
          <td>
            <?php if($rs[$i]['tcid']){ ?>
            <a href="<?php echo APP_DOURI; ?>&tcid=<?php echo $rs[$i]['tcid'] ; ?><?php echo $uri ; ?>"><?php echo $TC['name'] ; ?></a>
            <a href="<?php echo __ADMINCP__; ?>=tag_category&do=add&cid=<?php echo $rs[$i]['tcid']; ?>" target="_blank"><i class="fa fa-edit"></i></a>
            <?php } ?>
          </td>
          <td><?php $rs[$i]['pid'] && propAdmincp::flag($rs[$i]['pid'],$propArray,APP_DOURI.'&pid={PID}&'.$uri);?></td>
          <td>
                <a class="tip" href="javascript:;" title="
                总点击:<?php echo $rs[$i]['hits'] ; ?><br />
                今日点击:<?php echo $rs[$i]['hits_today'] ; ?><br />
                昨日点击:<?php echo $rs[$i]['hits_yday'] ; ?><br />
                周点击:<?php echo $rs[$i]['hits_week'] ; ?><br />
                收藏:<?php echo $rs[$i]['favorite'] ; ?><br />
                评论:<?php echo $rs[$i]['comments'] ; ?><br />
                赞:<?php echo $rs[$i]['good'] ; ?><br />
                使用数:<?php echo $rs[$i]['count'] ; ?><br />
                ">
                  <?php echo $rs[$i]['hits']; ?>/<?php echo $rs[$i]['count']; ?>
                </a>
        </td>
          <td><?php echo get_date($rs[$i]['pubdate'],'Y-m-d H:i');?><br /><?php echo get_date($rs[$i]['postime'],'Y-m-d H:i');?></td>
          <td>
          	<?php if($rs[$i]['status']=="1"){ ?>
            <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&_args=status:0" class="btn btn-small btn-danger tip" target="iPHP_FRAME" title="当前状态:启用,点击可禁用此标签"><i class="fa fa-power-off"></i> 禁用</a>
            <a href="<?php echo __ADMINCP__; ?>=keywords&do=add&keyword=<?php echo $rs[$i]['name'] ; ?>&url=<?php echo $rs[$i]['url'] ; ?>" class="btn btn-small"><i class="fa fa-paperclip"></i> 内链</a>
            <!-- <a href="<?php echo APP_FURI; ?>&do=cache&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-refresh"></i> 更新缓存</a> -->
            <?php } ?>
            <?php if($rs[$i]['status']=="0"){ ?>
            <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&_args=status:1" class="btn btn-small btn-success tip " target="iPHP_FRAME" title="当前状态:禁用,点击可启用此标签"><i class="fa fa-play-circle"></i> 启用</a>
            <?php } ?>
             <a href="<?php echo APP_URI; ?>&do=add&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a> <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $rs[$i]['id'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 永久删除</a></td>
        </tr>
        <?php }  ?>
          </tbody>

        <tfoot>
          <tr>
            <td colspan="11"><div class="pagination pagination-right" style="float:right;"><?php echo iPagination::$pagenav ; ?></div>
              <div class="input-prepend input-append mt20"> <span class="add-on">全选
                <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                </span>
                <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a data-toggle="batch" data-action="status:1"><i class="fa fa-play-circle"></i> 启用</a></li>
                    <li><a data-toggle="batch" data-action="status:0"><i class="fa fa-power-off"></i> 禁用</a></li>
                    <li class="divider"></li>
                    <li><a data-toggle="batch" data-action="move"><i class="fa fa-fighter-jet"></i> 移动栏目</a></li>
                    <li><a data-toggle="batch" data-action="mvtcid"><i class="fa fa-fighter-jet"></i> 移动分类</a></li>
                    <li><a data-toggle="batch" data-action="prop"><i class="fa fa-puzzle-piece"></i> 设置属性</a></li>
                    <li><a data-toggle="batch" data-action="top"><i class="fa fa-cog"></i> 设置权重</a></li>
                    <li><a data-toggle="batch" data-action="keyword"><i class="fa fa-star"></i> 设置关键字</a></li>
                    <li><a data-toggle="batch" data-action="tag"><i class="fa fa-tags"></i> 设置相关标签</a></li>
                    <li><a data-toggle="batch" data-action="tpl"><i class="fa fa-tags"></i> 设置模板</a></li>
                    <li class="divider"></li>
                     <li><a data-toggle="batch" data-action="keywords"><i class="fa fa-paperclip"></i> 设置为内链</a></li>
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
<div class="iCMS-batch">
  <div id="tplBatch">
    <div class="input-prepend input-append"> <span class="add-on">标签模板</span>
      <input type="text" name="mtpl" class="span2" id="mtpl" value=""/>
      <?php echo filesAdmincp::modal_btn('模板','mtpl');?>
    </div>
  </div>
</div>
<div id="import_wrap" style="display:none;">
  <form action="<?php echo APP_FURI; ?>&do=import" method="post" enctype="multipart/form-data" target="iPHP_FRAME">
    <div class="input-prepend"> <span class="add-on">栏目</span>
      <select name="cid" class="span3 chosen-select" multiple="multiple" data-placeholder="请选择栏目(可多选)...">
        <option value="0">请选择标签所属栏目</option>
        <?php echo $cid_select; ?>
      </select>
    </div>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend"> <span class="add-on">分类</span>
      <select name="tcid" class="span3 chosen-select" multiple="multiple" data-placeholder="请选择分类(可多选)...">
        <option value="0">默认分类</option>
        <?php echo $tcid_select; ?>
      </select>
    </div>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend"> <span class="add-on">属性</span>
      <select name="pid" class="span3 chosen-select" multiple="multiple" data-placeholder="请选择属性(可多选)...">
        <option value="0">默认属性</option>
        <?php echo $pid_select ; ?>
      </select>
    </div>
    <div class="clearfloat mb10"></div>
    <div class="alert alert-info">
      只允许导入TXT文件
      <br />
      每行一个标签
      <br />
      请把文件编码转换成UTF-8
    </div>
    <div class="clearfloat mb10"></div>
    <a id="local" class="btn btn-primary btn-large btn-block"><i class="fa fa-upload"></i> 请选择要导入的标签</a>
    <input id="localfile" name="upfile" type="file" class="hide"/>
    <div class="clearfloat mb10"></div>
  </form>
</div>
<?php admincp::foot();?>
