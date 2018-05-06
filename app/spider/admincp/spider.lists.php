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
<script type="text/javascript">
$(function() {
})
</script>
<style>
.widget-title span.icon { width: 24px; }
</style>
<div class="widget-box widget-plain" id="spider-list">
  <div class="widget-title"> <span class="icon">
    <input type="checkbox" class="checkAll" data-target=".spider-list" />
    </span>
    <h5 class="brs">采集列表</h5>
  </div>
  <div class="widget-content nopadding">
    <form action="<?php echo APP_FURI; ?>&do=mpublish" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
      <table class="table table-bordered table-condensed table-hover">
        <thead>
          <tr>
            <th><i class="fa fa-arrows-v"></i></th>
            <th>标题</th>
            <th>网址</th>
            <th style="width: 200px;">操作</th>
          </tr>
        </thead>
  <?php if($listsArray) foreach ($listsArray AS $furl => $lists) {?>
        <thead>
          <tr>
            <th><input type="checkbox" class="checkAll" data-target="#spider-list-<?php echo md5($furl); ?>" /></th>
            <th colspan="3"><?php echo $furl; ?></th>
          </tr>
        </thead>
        <tbody class="spider-list" id="spider-list-<?php echo md5($furl); ?>">
    <?php
	  	if($lists) foreach ($lists AS $lkey => $value) {
        $_title = $value['title'];
        $_url   = $value['url'];
        if(empty($value)){
            continue;
        }
				$hash = md5($_url);
				if(spider::checker($work,$pid,$_url,$_title)===true){
		?>
          <tr id="<?php echo $hash; ?>">
            <td><input type="checkbox" name="pub[]" value="<?php echo $cid; ?>|<?php echo $pid; ?>|<?php echo $rid; ?>|<?php echo $_url; ?>|<?php echo $_title; ?>|<?php echo $hash; ?>" /></td>
            <td><?php echo $_title; ?></td>
            <td><?php echo $_url; ?></td>
            <td>
              <a href="<?php echo APP_FURI; ?>&do=publish&cid=<?php echo $cid; ?>&pid=<?php echo $pid; ?>&rid=<?php echo $rid; ?>&hash=<?php echo $hash; ?>&url=<?php echo urlencode($_url); ?>&title=<?php echo  urlencode($_title); ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-check"></i> 发布</a>
              <a href="<?php echo APP_URI;  ?>&do=testdata&cid=<?php echo $cid; ?>&pid=<?php echo $pid; ?>&rid=<?php echo $rid; ?>&url=<?php echo urlencode($_url); ?>&title=<?php echo  urlencode($_title); ?>" class="btn btn-small" target="_blank"><i class="fa fa-keyboard-o"></i> 测试</a>
              <a href="<?php echo APP_FURI; ?>&do=markurl&cid=<?php echo $cid; ?>&pid=<?php echo $pid; ?>&rid=<?php echo $rid; ?>&url=<?php echo urlencode($_url); ?>&title=<?php echo  urlencode($_title); ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 移除</a>
            </td>
          </tr>
        <?php }?>
      <?php }?>
  <?php } ?>
        </tbody>
      </table>
      <div class="form-actions mt0">
        <div class="input-prepend input-append mt20"> <span class="add-on">全选
          <input type="checkbox" class="checkAll" data-target=".spider-list" />
          </span>
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 开始采集</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php admincp::foot(); ?>
