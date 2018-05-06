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
admincp::head(false);
?>
<style type="text/css">
.iCMS-container {margin: 0px;background-color: #ECEEEF;}
#onBrowse{text-align: center;padding: 20px;}
.savetodir{margin-bottom: 0px;}
.ui-dialog-footer button{padding: 10px 20px;}
</style>
<script src="./app/admincp/ui/meitu/xiuxiu.js" type="text/javascript"></script>
<div id="onBrowse" class="well" style="display:none;">
  <a class="btn btn-success" href="<?php echo __ADMINCP__; ?>=files&do=picture&from=modal&click=file&callback=brofile" data-toggle="modal" data-meta='{"width":"75%","height":"480px"}' data-zIndex="9999999" title="从网站选择图片"><i class="fa fa-picture-o"></i> 从网站选择</a>
  <hr />
  <a id="local" class="btn btn-primary"><i class="fa fa-upload"></i> 从电脑选择</a>
  <input id="localfile" type="file" multiple="multiple" accept="image/*" class="hide"/>
</div>
<div id="onUpload" class="well" style="display:none;">
  <a class="btn btn-success" href="<?php echo __ADMINCP__; ?>=files&do=picture&from=modal&click=dir&callback=savetodir" data-toggle="modal" data-meta='{"width":"75%","height":"480px"}' data-zIndex="9999999" title="保存到新目录"><i class="fa fa-save"></i> 保存到..</a>
  <span class="span2 uneditable-input savetodir hide"></span>
  <hr />
  <div class="input-prepend input-append">
    <span class="add-on"><input type="radio" class="radio" name='fna' value="co" checked/></span>
    <span class="add-on">覆盖原文件</span>
  </div>
  <div class="input-prepend input-append">
    <span class="add-on"><input type="radio" class="radio" name='fna' value="mv"/></span>
    <span class="add-on">重命名</span>
  </div>
  <div id="newfn" class="hide">
    <div class="clearfloat mb10"></div>
    <div class="input-prepend input-append">
      <span class="add-on">新文件名</span>
      <input type="text" class="span3" id="fname" value="<?php echo md5(uniqid());?>"/>
      <span class="add-on">.jpg</span>
    </div>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend input-append">
    <span class="add-on">是否添加水印</span>
    <div class="switch" data-on-label="是" data-off-label="否">
      <input type="checkbox" data-type="switch" id="watermark"/>
    </div>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend input-append">
    <span class="add-on">保存后关闭对话框</span>
    <div class="switch" data-on-label="保留" data-off-label="关闭">
      <input type="checkbox" data-type="switch" id="close_modal"/>
    </div>
  </div>
</div>
  <div class="iCMS-container">
    <div id="PhotoEditor">
      <?php if(!is_array($src)){ ?>
      <img src="<?php echo $src;?>" alt="预览">
      <?php } ?>
    </div>
</div>
<script type="text/javascript">
var sel_dialog,sel_channel='main',program = {'name':'<?php echo $file_name;?>', 'udir':"<?php echo $file_path;?>",'ext':'<?php echo $file_ext;?>'}
$(function() {
    $("input[name=fna]").click(function() {
        if(this.value=='mv'){
          $("#newfn").show();
        }else{
          $("#newfn").hide();
        }
    })
    $("#local").click(function() {
        $("#localfile").click();
    })
    $("#localfile").change(function() {
        loading();
        var file = document.getElementById('localfile');
        var file = this.files[0];
        if(/image\/\w+/.test(file.type)){
          var regexp    = /^data:image\/.*;base64,/;
          var reader    = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = function() {
            //callback(this.result.replace(regexp, ''))
            var base64    = this.result.replace(regexp, '');
//console.log(sel_channel);
            loadPhoto(base64,true);
            reader.onload = null;
            sel_dialog.destroy();
          };
        }else{
          sel_dialog.destroy();
        }
    })

    xiuxiu.params.wmode = "transparent";
    xiuxiu.setLaunchVars("preventBrowseDefault", 1);
    xiuxiu.setLaunchVars("preventUploadDefault", 1);
    xiuxiu.setLaunchVars("file_name", "<?php echo $file_name;?>");
    <?php if(is_array($src)){ ?>
    xiuxiu.setLaunchVars("nav", "puzzle/puzzleModel");
    //xiuxiu.setLaunchVars("nav", "/puzzleModel");
    //xiuxiu.embedSWF("PhotoEditor",2,"100%","630","lite");
    <?php }else{ ?>
    <?php } ?>
    /*第1个参数是加载编辑器div容器，第2个参数是编辑器类型，第3个参数是div容器宽，第4个参数是div容器高*/
    xiuxiu.embedSWF("PhotoEditor",3,"100%","630");
    xiuxiu.setUploadURL("<?php echo ACP_HOST;?><?php echo __ADMINCP__; ?>=files&do=IO&format=json&id=<?php echo $file_id;?>");//修改为您自己的上传接收图片程序
    xiuxiu.onInit = function (id){
      <?php if($src){ ?>
        <?php if(is_array($src)){ ?>
          xiuxiu.loadPhoto(["<?php echo implode('","',(array)$src);?>"],false,id,{loadImageChannel: "imageThumbsPanel"});
        <?php }else{ ?>
          xiuxiu.loadPhoto('<?php echo $src;?>');
        <?php } ?>
      <?php } ?>
      xiuxiu.setUploadDataFieldName ('upfile');
      xiuxiu.setUploadType(1);
      //xiuxiu.setUploadArgs();
    }
//xiuxiu.loadImages(images, {"loadImageChannel":channel}); //打开图片对话框接口
//xiuxiu.uploadFail();
//xiuxiu.uploadResponse(data);
//xiuxiu.upload();
    xiuxiu.onBrowse = function(channel, multipleSelection, canClose, id){
      var _onBrowse = document.getElementById("onBrowse");
      //browse.style.display = 'black';
      sel_channel = channel;
// console.log(channel, multipleSelection, canClose, id);
      //iCMS.dialog("asdasd");
      sel_dialog = iCMS.dialog({
        title: 'iCMS - 打开图片',
        content:_onBrowse
      });
 //console.log(sel_dialog);

      // sel_dialog  = dialog({
      //   id: 'iPHP-DIALOG',width: 360,height: 150,fixed: true,
      //   title: 'iCMS - 打开图片',
      //   content: browse,
      // }).show();
    }
    xiuxiu.onBeforeUpload = function (data, id){
      var size = data.size;
      if(size ><?php echo $max_size;?>) {
          iCMS.alert("图片不能超过<?php echo $this->upload_max_filesize;?>");
         return false;
      }
      return true;
    }
    xiuxiu.onUpload = function(id) {
      var onUpload = document.getElementById("onUpload")
      iCMS.dialog({
        title:'iCMS - 保存图片',
        content:onUpload,
        okValue:'保存',
        ok:function(){
          var fna = $("input[name=fna]:checked").val();
          program.name = (fna=='mv'?$('#fname').val():'<?php echo $file_name;?>');
          program.watermark = ($('#watermark').prop("checked")?0:1);
          xiuxiu.setUploadArgs(program);
          xiuxiu.upload();
        },
        cancelValue: '取消',
        cancel: function(){
          return true;
        }
      });
    }
    xiuxiu.onUploadResponse = function (data){
      var info = $.parseJSON(data);
      if(info.state=="SUCCESS"){
        var state = window.parent.modal_<?php echo $this->callback;?>('<?php echo $this->target; ?>',info);
        <?php if($this->callback=='icms'){?>
        iCMS.alert("保存完成!",true,function(){
          window.parent.iCMS_MODAL.destroy();
        });
        <?php }?>
        if($('#close_modal').prop("checked")){
          window.parent.iCMS_MODAL.destroy();
        }
        // if(state=='off'){
        //   window.parent.iCMS_MODAL.destroy();
        // }
      }
      //alert("上传响应" + data);  //可以开启调试
    }
    // xiuxiu.onDebug = function (a, b){
    //   console.log(a, b);
    // }
})

function modal_savetodir(el,a){
//console.log(a);
  if(!a.checked) return;
  program.udir = a.value;
  $(".savetodir").text(a.value).removeClass('hide');
  return 'off';
}
function modal_brofile(el,a){
  if(!a.checked) return;
  loading();
  var url = $(a).attr('url');
//console.log(url);
  loadPhoto(url,false);
  sel_dialog.destroy();
  return 'off';
}

function loading(){
  //sel_dialog.content('<div class="iPHP-msg"><img src="./app/admincp/ui/img/ajax_loader.gif" /> <span class="label label-inverse">图片正在努力加载中...请稍候!</span></div>');
}
function loadPhoto(data,base64){
//console.log('sel_channel:',sel_channel);
//console.log('base64:',base64);

  if(sel_channel=='main'){
    xiuxiu.loadPhoto(data,base64);
  }else{
    xiuxiu.loadPhoto(data,base64,'xiuxiuEditor',{loadImageChannel: "imageThumbsPanel"});
  }
}
</script>
<?php admincp::foot(); ?>
