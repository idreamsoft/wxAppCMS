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
configAdmincp::head("云存储配置","cloud_config");
$cloud_config_file = filesAdmincp::cloud_config_file();
?>
<?php if($cloud_config_file){ ?>
<div class="input-prepend"> <span class="add-on">使用云存储</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[enable]" id="cloud_enable" <?php echo $config['enable']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">使用云存储后,相关管理请到云存储管理</span>
<div class="clearfloat mb10"></div>
<div class="input-prepend"> <span class="add-on">不保留本地</span>
  <div class="switch">
    <input type="checkbox" data-type="switch" name="config[local]" id="cloud_local" <?php echo $config['local']?'checked':''; ?>/>
  </div>
</div>
<span class="help-inline">默认保留本地资源,当备份用</span>
<div class="clearfloat mb10"></div>
<div class="alert" style="width:360px;">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>注意!</strong> 设置云存储,将会影响文件的上传效率
</div>
<hr />
<?php //include admincp::view("remote.config","files"); ?>
<?php
  foreach ($cloud_config_file as $name =>$path) {
    include admincp::view("cloud_".$name,"files");
    echo '<hr />';
  }
?>
<?php }?>
<?php configAdmincp::foot();?>
