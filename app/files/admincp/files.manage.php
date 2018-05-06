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
	<?php if($_GET['st']){ ?>
	iCMS.select('st',"<?php echo $_GET['st'] ; ?>");
	<?php } ?>
  <?php if($_GET['orderby']){ ?>
  iCMS.select('orderby',"<?php echo $_GET['orderby'] ; ?>");
  <?php } ?>
  <?php if($_GET['type']){ ?>
  iCMS.select('type',"<?php echo $_GET['type'] ; ?>");
  <?php } ?>
	$("#<?php echo APP_FORMID;?>").batch({
    edit:function(checkbox){
      var pics = new Array();
      $.each(checkbox,function(key, val) {
        //fids[key] = $(val).val();
        var id = "#id"+$(val).val();
        pics[key] = $("a:eq(0)",id).attr("href");
      });
      //console.log(pics);
      $(this).modal({
        href:"<?php echo __ADMINCP__; ?>=files&frame=iPHP&do=editpic&from=modal&pics="+(pics.join(',')),
        width: "85%",height: "640px",overflow:true});
      return 'false';
    }
  });
});
</script>

<div class="iCMS-container">
  <?php if($widget['search']){?>
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="files" />
        <input type="hidden" name="indexid" value="<?php echo $_GET['indexid'] ; ?>" />
        <input type="hidden" name="userid" value="<?php echo $_GET['userid'] ; ?>" />
        <div class="input-prepend input-append"><span class="add-on"><i class="fa fa-calendar"></i></span>
          <input type="text" class="ui-datepicker" name="starttime" value="<?php echo $_GET['starttime'] ; ?>" placeholder="开始时间" />
          <span class="add-on">-</span>
          <input type="text" class="ui-datepicker" name="endtime" value="<?php echo $_GET['endtime'] ; ?>" placeholder="结束时间" />
          <span class="add-on"><i class="fa fa-calendar"></i></span> </div>
        <div class="input-prepend"> <span class="add-on">类型</span>
          <select name="type" id="type" class="span2 chosen-select">
            <option value="-1">所有</option>
            <option value="0">上传</option>
            <option value="1">远程下载</option>
            <option value="3">数据流</option>
          </select>
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
        <div class="input-prepend"> <span class="add-on">查找方式</span>
          <select name="st" id="st" class="span2 chosen-select">
            <option value="filename">文件名</option>
            <option value="indexid">关联ID</option>
            <option value="userid">用户ID</option>
            <option value="ofilename">源文件</option>
            <option value="size">文件大小</option>
            <option value="path">路径</option>
            <option value="ext">后缀名</option>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span5" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span>
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <?php };?>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>文件列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo __ADMINCP__; ?>=files&frame=iPHP&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <?php if($widget['id']){?>
              <th>ID</th>
              <?php }?>
              <?php if($widget['uid']){?>
              <th style="width:30px;">UID</th>
              <?php }?>
              <th>路径</th>
              <th style="width:60px;">大小</th>
              <th style="width:120px;">时间</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){
              $filepath = $rs[$i]['path'].$rs[$i]['filename'].'.'.$rs[$i]['ext'];
              $href     = iFS::fp($filepath,"+http");
            ?>
            <tr id="id<?php echo $rs[$i]['id'] ; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['id'] ; ?>" /></td>
              <?php if($widget['id']){?>
              <td><?php echo $rs[$i]['id'] ; ?></td>
              <?php }?>
              <?php if($widget['uid']){?>
              <td><?php echo $rs[$i]['userid'] ; ?></td>
              <?php }?>
              <td>
                <a href="<?php echo $href; ?>" title="点击查看" target="_blank"><?php echo files::icon($filepath);?></a>
                <a class="tip" title="<?php echo $filepath ; ?><hr />源文件名:<?php echo htmlspecialchars($rs[$i]['ofilename']) ; ?>"><?php echo $rs[$i]['filename'].'.'.$rs[$i]['ext']; ?></a>
              </td>
              <td><?php echo iFS::sizeUnit($rs[$i]['size']);?></td>
              <td><?php echo get_date($rs[$i]['time'],'Y-m-d H:s');?></td>
              <td>
                <?php if(iCMS::$config['cloud']['sdk']){?>
                <div class="btn-group">
                  <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span> 查看</a>
                  <ul class="dropdown-menu">
                    <?php
                    foreach (iCMS::$config['cloud']['sdk'] as $sdk => $value) {
                      if(empty($value['AccessKey'])|| empty($value['SecretKey'])){
                        continue;
                      }
                      $cloud_href = rtrim($value['domain'],'/').'/'.ltrim($filepath,'/');
                      iFS::checkHttp($cloud_href) OR $cloud_href = 'http://'.trim($cloud_href);
                    ?>
                    <li><a href="<?php echo $cloud_href; ?>" data-toggle="modal" title="查看<?php echo $sdk; ?>"><i class="fa fa-eye"></i> <?php echo $sdk; ?></a></li>
                    <?php }?>
                    <li><a href="<?php echo $href; ?>" data-toggle="modal" title="查看"><i class="fa fa-eye"></i> 本地</a></li>
                  </ul>
                </div>
                <?php }else{?>
                <a class="btn btn-small" href="<?php echo $href; ?>" data-toggle="modal" title="查看"><i class="fa fa-eye"></i> 查看</a>
                <?php }?>
                <?php if(members::check_priv('files.editpic')){?>
                <a class="btn btn-small" href="<?php echo __ADMINCP__;?>=files&frame=iPHP&do=editpic&from=modal&pic=<?php echo $filepath ; ?>" data-toggle="modal" title="编辑图片(<?php echo $rs[$i]['filename'].'.'.$rs[$i]['ext']; ?>)"><i class="fa fa-edit"></i> 编辑</a>
                <?php }?>
                <?php if(strstr($rs[$i]['ofilename'],'http://')){?>
                <a href="<?php echo __ADMINCP__; ?>=files&frame=iPHP&do=download&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" title="正常重新下载" target="iPHP_FRAME"><i class="fa fa-download"></i> 下载</a>
                <a href="<?php echo __ADMINCP__; ?>=files&frame=iPHP&do=download&id=<?php echo $rs[$i]['id'] ; ?>&unwatermark=0" class="btn btn-small" title="重新下载 不添加水印" target="iPHP_FRAME"><i class="fa fa-download"></i> 下载2</a>
                <?php }?>
                <?php if(members::check_priv('files.add')){?>
                <a href="<?php echo __ADMINCP__; ?>=files&frame=iPHP&do=add&from=modal&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" data-toggle="modal" data-meta='{"width":"500px","height":"300px"}' title="重新上传"><i class="fa fa-upload"></i> 上传</a>
                <?php }?>
                <?php if(members::check_priv('files.del')){?>
                <a href="<?php echo __ADMINCP__; ?>=files&frame=iPHP&do=del&id=<?php echo $rs[$i]['id'] ; ?>&indexid=<?php echo $rs[$i]['indexid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a>
                <?php }?>
              </td>
            </tr>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="10"><div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="edit" data-dialog="no" title="多选可拼接"><i class="fa fa-edit"></i> 编辑 </a></li>
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
