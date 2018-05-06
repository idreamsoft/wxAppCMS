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
          <th>网址</th>
          <th class="span5">错误</th>
          <th>位置</th>
          <th>时间</th>
        </tr>
      </thead>
      <?php foreach ((array)$rs AS $key => $value) {?>
      <tr>
        <td><?php echo $value['id'];?></td>
        <td>
          <?php echo $value['url'];?>(<?php echo $value['ct'];?>)
          <br />
          <a class="btn btn-small" href="<?php echo __ADMINCP__; ?>=spider&do=testdata&url=<?php echo urlencode($value['url']);?>&rid=<?php echo $value['rid'];?>&pid=<?php echo $value['pid'];?>" target="_blank">测试网址</a>
          <a href="<?php echo __ADMINCP__; ?>=spider&do=testrule&rid=<?php echo $value['rid']; ?>" class="btn btn-small" data-toggle="modal" title="测试规则"><i class="fa fa-keyboard-o"></i> 测试规则</a>
          <a href="<?php echo __ADMINCP__; ?>=spider&do=addrule&rid=<?php echo $value['rid']; ?>" class="btn btn-small" target="_blank"><i class="fa fa-edit"></i> 编辑规则</a>
        </td>
        <td><?php echo str_replace(',', '<br />', $value['msg']);?></td>
        <td><?php echo str_replace(',', '<br />', $value['type']);?></td>
        <td><?php echo date("Y-m-d H:i:s",$value['addtime']);?></td>
      </tr>
      <?php }?>
    </table>
  </div>
</div>
