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
$unid = uniqid();
?>
<div class="btn-group">
  <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span> 选择<?php echo $title;?></a>
  <ul class="dropdown-menu">
    <?php if(members::check_priv('files.add')){?>
    <li><a href="<?php echo __ADMINCP__;?>=files&do=add&from=modal&callback=<?php echo $callback;?>" data-toggle="modal" data-meta='{"width":"300px","height":"80px"}' title="本地上传"><i class="fa fa-upload"></i> 本地上传</a></li>
    <?php if($multi){?>
    <li><a href="<?php echo __ADMINCP__;?>=files&do=multi&from=modal&callback=<?php echo $callback;?>" data-toggle="modal" data-meta='{"width":"85%","height":"640px"}' title="多图上传"><i class="fa fa-upload"></i> 多图上传</a></li>
    <?php }?>
    <?php }?>
    <?php if(members::check_priv('files.browse')){?>
    <li><a href="<?php echo __ADMINCP__;?>=files&do=browse&from=modal&click=file&callback=<?php echo $callback;?>" data-toggle="modal" title="从网站选择"><i class="fa fa-search"></i> 从网站选择</a></li>
    <li class="divider"></li>
    <?php }?>
    <?php if(members::check_priv('files.editpic')){?>
    <li><a href="<?php echo __ADMINCP__;?>=files&do=editpic&from=modal&callback=<?php echo $callback;?>" data-toggle="modal" title="使用美图秀秀编辑图片" class="modal_photo_<?php echo $unid;?> tip"><i class="fa fa-edit"></i> 编辑</a></li>
    <li class="divider"></li>
        <?php if($indexid){?>
        <li><a href="<?php echo __ADMINCP__;?>=files&do=editpic&from=modal&indexid=<?php echo $indexid;?>&callback=<?php echo $callback;?>" data-toggle="modal" title="使用加载本篇内容所有图片编辑" class="modal_mphoto_<?php echo $unid;?> tip"><i class="fa fa-edit"></i> 多图编辑</a></li>
        <li class="divider"></li>
        <?php }?>
    <?php }?>
    <li><a href="<?php echo __ADMINCP__;?>=files&do=preview&from=modal&callback=<?php echo $callback;?>" data-toggle="modal" data-check="1" title="预览" class="modal_photo_<?php echo $unid;?>"><i class="fa fa-eye"></i> 预览</a></li>
  </ul>
  <?php if(self::$no_http){ ?>
      <span class="add-on tip brr" title="选中不执行远程文件本地化">
        <input type="checkbox" name="<?php echo $callback;?>_http"/>
        <i class="fa fa-cog"></i>
      </span>
  <?php } ?>
</div>
<script type="text/javascript">
$(function(){
<?php if(self::$no_http && iFS::checkHttp(self::$pic_value)){ ?>
    $('[name="<?php echo $callback;?>_http"]').prop('checked','checked');
<?php } ?>
    window.modal_<?php echo $callback;?> = function(el,a,c){
        // console.log(el,a,c,'11111111111111');
        var e = $("#<?php echo $callback;?>");
        var name = e.get(0).tagName;
        if(name=='TEXTAREA'){
            var data = e.val();
            if(data){
                e.val(data+"\n"+a.value);
            }else{
               e.val(a.value);
            }
        }else{
            e.val(a.value);
        }
        if(c===false){
            return true;
        }
        window.iCMS_MODAL.destroy();
    }
    $(".modal_photo_<?php echo $unid;?>").on("click",function(){
        var  pic = $("#<?php echo $callback;?>").val(),href = $(this).attr("href");
        if(pic){
            $("#modal-iframe").attr("src",href+"&pic="+pic);
        }else{
            var check = $(this).attr("data-check"),title=$(this).attr("title");
            if(check){
                window.iCMS_MODAL.destroy();
                iCMS.alert("暂无图片,您现在不能"+title);
            }
        }
        return false;
    });
});
</script>
