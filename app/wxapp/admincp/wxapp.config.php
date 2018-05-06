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
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title">
      <span class="icon"> <i class="fa fa-cog"></i> </span>
      <h5>配置微信小程序</h5>
      <span class="label right">申请地址:https://mp.weixin.qq.com/</span>
    </div>
    <div class="widget-content nopadding iCMS-config">
      <form action="<?php echo APP_FURI; ?>&do=save_config" method="post" class="form-inline" id="iCMS-config" target="iPHP_FRAME">
        <div id="config" class="tab-content">
          <div id="config-content" class="tab-pane active">
            <div class="input-prepend">
              <span class="add-on">
              appID
              </span>
              <input type="text" name="config[appid]" class="span5" id="wxapp_appid" value="<?php echo $config['appid'] ; ?>"/>
            </div>
            <div class="clearfloat mt10"></div>
            <div class="input-prepend">
              <span class="add-on">
              appsecret
              </span>
              <input type="text" name="config[appsecret]" class="span5" id="wxapp_appsecret" value="<?php echo $config['appsecret'] ; ?>"/>
            </div>
            <div class="clearfloat mt10"></div>
            <div class="input-prepend">
              <span class="add-on">
              名称
              </span>
              <input type="text" name="config[name]" class="span5" id="wxapp_name" value="<?php echo $config['name'] ; ?>"/>
            </div>
            <div class="clearfloat mt10"></div>
            <div class="input-prepend">
              <span class="add-on">
              小程序号
              </span>
              <input type="text" name="config[account]" class="span5" id="wxapp_account" value="<?php echo $config['account'] ; ?>"/>
            </div>
            <div class="clearfloat mt10"></div>
            <div class="input-prepend">
              <span class="add-on">
              二维码
              </span>
              <input type="text" name="config[qrcode]" class="span5" id="wxapp_qrcode" value="<?php echo $config['qrcode'] ; ?>"/>
            </div>
            <span class="help-inline">
            小程序的二维码链接/小程序码
            </span>
            <div class="form-actions">
              <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
