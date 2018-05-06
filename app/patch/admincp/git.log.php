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
<div class="widget-box widget-plain">
  <div class="widget-content nopadding">
    <table class="table table-bordered table-condensed table-hover">
      <thead>
        <tr>
          <th><i class="fa fa-arrows-v"></i></th>
          <th>版本</th>
          <th>信息</th>
          <th>作者</th>
          <th>更新时间 </th>
          <th></th>
        </tr>
      </thead>
      <tbody class="log-list">
        <?php
          $uri=$_GET['path']?"&path=".urlencode($_GET['path']):null;

          if($log) foreach ($log AS $k => $value) {
              $commit_id = $value['commit_id'];
        ?>
        <tr id="<?php echo $commit_id; ?>">
          <td><?php echo $k+1; ?></td>
          <td><?php echo substr($commit_id, 0,16) ; ?></td>
          <td class="span7"><?php echo $value['info'][3]; ?></td>
          <td><?php echo $value['info'][1]; ?></td>
          <td><?php echo date('Y-m-d H:i',$value['info'][2]); ?></td>
          <td>
            <!-- <a href="<?php echo APP_FURI; ?>&do=git_log&commit_id=<?php echo $commit_id; ?>" class="gitlog btn btn-small" title="查看这个版本详细信息"><i class="fa fa-eye"></i> 查看</a> -->
            <a href="<?php echo APP_FURI; ?>&do=git_show&commit_id=<?php echo $commit_id; ?>&git=true<?php echo $uri;?>" class="btn btn-info btn-small"
              data-toggle="modal" data-target="#iCMS-MODAL" data-meta="{&quot;width&quot;:&quot;85%&quot;,&quot;height&quot;:&quot;450px&quot;}"
              title="查看<?php echo substr($commit_id, 0,16) ; ?>详细信息"><i class="fa fa-eye"></i> 查看</a>
            <a href="<?php echo APP_FURI; ?>&do=git_download&last_commit_id=<?php echo $commit_id; ?>&release=<?php echo date('Ymd',$value['info'][2]); ?>&git=true<?php echo $uri;?>" class="btn btn-success btn-small" target="_blank" title="更新到这个版本"><i class="fa fa-check"></i> 更新</a>
          </td>
        </tr>
        <?php }?>
        <tr>
          <td></td>
          <td><?php echo substr(GIT_COMMIT, 0,16) ; ?></td>
          <td>
            <?php if($log){ ?>
            您当前使用的版本
            <?php }else{?>
            您当前使用的是最新版本
            <?php }?>
          </td>
          <td><?php echo GIT_AUTHOR; ?></td>
          <td><?php echo date('Y-m-d H:i',GIT_TIME); ?></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<script>
// $(function(){
//   var param    = {}
//   var type_map = {'D':'删除','A':'增加','M':'更改'}
//   $(".gitlog").click(function(event) {
//     event.preventDefault();
//     var $this = $(this);
//     var url = $this.attr('href');
//     $.get(url, function(c) {
//       // $("#git_commit").html(c[0]);
//       var fileList =''
//       $.each(c[1], function(index, val) {
//         fileList+='<tr>'
//                     +'<td scope="row">'+index+'</td>'
//                     +'<td>'
//                       +'<div class="checkbox">'
//                         +'<label>'
//                           +'<input type="checkbox" name="filelist"'
//                           +'value="'+val[0]+'@~@'+val[1]+'" checked />'
//                         +'</label>'
//                       +'</div>'
//                     +'</td>'
//                     +'<td>'+type_map[val[0]]+'</td>'
//                     +'<td><div class="filepath">'+val[2]+'</div></td>'
//                   +'</tr>';
//       });
//       var table = '<table class="table table-hover">'
//                 +'<thead>'
//                   +'<tr>'
//                     +'<th>#</th>'
//                     +'<th>选择</th>'
//                     +'<th>执行</th>'
//                     +'<th></th>'
//                   +'</tr>'
//                 +'</thead>'
//                 +'<tbody>'
//                 +fileList
//                 +'</tbody>'
//               +'</table>';

//       iCMS.dialog({
//           content: $(table)
//       });
//     },'json');
//   });
// });
</script>
<?php admincp::foot(); ?>
