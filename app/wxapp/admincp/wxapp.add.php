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
    $(function() {
        iCMS.select('cid', "<?php echo $rs['cid'] ; ?>");
    });
    </script>
    <div class="iCMS-container">
        <div class="widget-box" id="<?php echo APP_BOXID;?>">
            <div class="widget-title">
                <span class="icon"> <i class="fa fa-plus-square"></i> </span>
                <h5 class="brs"><?php echo empty($this->id)?'添加':'修改' ; ?>小程序</b></h5>
                <ul class="nav nav-tabs" id="config-tab">
                    <li class="active">
                        <a href="#info" data-toggle="tab">基本配置</a>
                    </li>
                    <li>
                        <a href="#payment" data-toggle="tab">微信支付配置</a>
                    </li>
                    <li>
                        <a href="#apps-custom" data-toggle="tab"><i class="fa fa-wrench"></i> 自定义</a>
                    </li>
                    <li>
                        <a href="#apps-metadata" data-toggle="tab"><i class="fa fa-sitemap"></i> 动态属性</a>
                    </li>
                </ul>
            </div>
            <div class="widget-content nopadding">
                <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-prop" target="iPHP_FRAME">
                    <input name="id" type="hidden" value="<?php echo $this->id ; ?>" />
                    <div id="<?php echo APP_BOXID;?>" class="tab-content">
                        <div id="info" class="tab-pane active">
                            <div class="input-prepend">
                                <span class="add-on">分类</span>
                                <select name="cid" id="cid" class="span4 chosen-select">
                                    <option value="0"> ==== 暂无所属栏目 ==== </option>
                                    <?php echo category::appid($this->appid,'ca')->select($rs['cid'],0,1,true);?>
                                </select>
                            </div>
                            <span class="help-inline">本小程序所属的栏目</span>
                            <div class="clearfloat mb10"></div>
                            <div class="input-prepend">
                                <span class="add-on">APPID</span>
                                <input type="text" name="appid" class="span6" id="wxapp_appid" value="<?php echo $rs['appid'] ; ?>" />
                            </div>
                            <div class="clearfloat mt10"></div>
                            <div class="input-prepend">
                                <span class="add-on">密钥</span>
                                <input type="text" name="appsecret" class="span6" id="wxapp_appsecret" value="<?php echo $rs['appsecret'] ; ?>" />
                            </div>
                            <span class="help-inline">小程序appsecret</span>
                            <div class="clearfloat mt10"></div>
                            <div class="input-prepend">
                                <span class="add-on">名称</span>
                                <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>" />
                            </div>
                            <span class="help-inline">小程序名称</span>
                            <div class="clearfloat mt10"></div>
                            <div class="input-prepend">
                                <span class="add-on">接口域名</span>
                                <input type="text" name="url" class="span6" value="<?php echo $rs['url'] ; ?>" />
                            </div>
                            <span class="help-inline">小程序接口域名,例:<span class="label label-info">https://www.wxappcms.com</span></span>
                            <div class="clearfloat mb10"></div>
                            <div class="input-prepend input-append">
                                <span class="add-on">接口模板</span>
                                <input type="text" name="tpl" class="span5" id="tpl" value="<?php echo $rs['tpl'] ; ?>" />
                                <?php echo filesAdmincp::modal_btn('模板','tpl','dir');?>
                            </div>
                            <span class="help-inline">小程序接口默认模板,请选择接口具体版本号,例如:wxapp/api/v1.1.0</span>
                            <div class="clearfloat mb10"></div>
                            <div class="input-prepend">
                                <span class="add-on">接口版本号</span>
                                <input type="text" name="version" class="span5" id="wxapp_version" value="<?php echo $rs['version'] ; ?>" />
                            </div>
                            <span class="help-inline">当前最新使用版本号.以防止新旧版本交替期间接口混乱.版本号格式:v1.0.0 例:v1.1.2</span>
                            <div class="clearfloat mb10"></div>
                            <div class="input-prepend input-append">
                                <span class="add-on">首页</span>
                                <input type="text" name="index" class="span5" id="index_tpl" value="<?php echo $rs['index']?:'{iTPL}/index.htm' ; ?>" />
                                <?php echo filesAdmincp::modal_btn('模板','index_tpl');?>
                            </div>
                            <span class="help-inline">小程序首页接口默认模板</span>
                            <div class="clearfloat mt10"></div>
                            <div class="input-prepend">
                                <span class="add-on">小程序号</span>
                                <input type="text" name="account" class="span6" id="wxapp_account" value="<?php echo $rs['account'] ; ?>" />
                            </div>
                            <div class="clearfloat mb10"></div>
                            <div class="input-prepend">
                                <span class="add-on">二维码</span>
                                <input type="text" name="qrcode" class="span6" id="qrcode" value="<?php echo $rs['qrcode'] ; ?>" />
                            </div>
                            <span class="help-inline">小程序的二维码链接/小程序码</span>
                            <div class="clearfloat mb10"></div>
                            <div class="input-prepend"><span class="add-on">描述</span>
                                <textarea name="description" id="description" class="span6" style="height: 90px;"><?php echo $rs['description'] ; ?></textarea>
                            </div>
                            <span class="help-inline">小程序的描述</span>
                        </div>
                        <div id="payment" class="tab-pane hide">
                            <div class="hide">
	                            <div class="input-prepend">
	                                <span class="add-on">仿真测试</span>
	                                <div class="switch">
	                                    <input type="checkbox" data-type="switch" name="payment[use_sandbox]" id="wx_use_sandbox" <?php echo $payment['use_sandbox']? 'checked': ''; ?>/>
	                                </div>
	                            </div>
	                            <span class="help-inline">是否使用 微信支付仿真测试系统</span>
                                <div class="clearfloat mt10"></div>
                                <div class="input-prepend">
                                    <span class="add-on">APPID</span>
                                    <input type="text" name="payment[app_id]" class="span6" id="wx_app_id" value="<?php echo $rs['appid'] ; ?>" />
                                </div>
                                <span class="help-inline">APPID</span>
                            </div>
                            <div class="clearfloat mt10"></div>
                            <div class="input-prepend">
                                <span class="add-on">商户号</span>
                                <input type="text" name="payment[mch_id]" class="span6" id="wx_mch_id" value="<?php echo $payment['mch_id'] ; ?>" />
                            </div>
                            <span class="help-inline">商户号</span>
                            <div class="clearfloat mt10"></div>
                            <div class="input-prepend">
                                <span class="add-on">商户支付密钥</span>
                                <input type="text" name="payment[mch_key]" class="span6" id="wx_mch_key" value="<?php echo $payment['mch_key'] ; ?>" />
                            </div>
                            <span class="help-inline">商户支付密钥</span>
                            <div class="clearfloat mt10"></div>
                            <div class="input-prepend">
                                <span class="add-on">跳转页面</span>
                                <input type="text" name="payment[redirect_url]" class="span6" id="wx_redirect_url" value="<?php echo $payment['redirect_url'] ; ?>" />
                            </div>
                            <span class="help-inline">如果是h5支付，可以设置该值，返回到指定页面</span>
                            <div class="clearfloat mt10"></div>
                            <div class="input-prepend input-append">
                                <span class="add-on">商户证书</span>
                                <span class="add-on" style="width:30px;">公钥</span>
                                <input type="text" name="payment[sslcert]" class="span3" id="wx_sslcert" value="<?php echo $payment['sslcert'] ; ?>" />
                                <span class="add-on" style="width:30px;">私钥</span>
                                <input type="text" name="payment[sslkey]" class="span3" id="wx_sslkey" value="<?php echo $payment['sslkey'] ; ?>" />
                            </div>
                            <span class="help-inline">商户证书公钥/私钥文件名 (仅退款、撤销订单时需要)</span>
                            <div class="clearfloat mb10"></div>
                            <div class="input-prepend">
                                <span class="add-on">返回原始数据</span>
                                <div class="switch">
                                    <input type="checkbox" data-type="switch" name="payment[return_raw]" id="wx_return_raw" <?php echo $payment[ 'return_raw']? 'checked': ''; ?>/>
                                </div>
                            </div>
                            <span class="help-inline">在处理回调时，是否直接返回原始数据,默认为处理过的数据</span>
                        </div>
                        <div id="apps-custom" class="tab-pane hide">
                            <?php echo former::layout();?>
                        </div>
                        <div id="apps-metadata" class="tab-pane hide">
                            <?php include admincp::view("apps.meta","apps");?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i>
                            <?php echo empty($this->id)?'添加':'修改' ; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php admincp::foot();?>
