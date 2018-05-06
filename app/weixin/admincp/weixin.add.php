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
    $("#weixin_token_make").click(function(event) {
        var token = iCMS.random(20);
        $("#weixin_token").val(token);
    });
});
</script>
<div class="iCMS-container">
    <div class="widget-box" id="<?php echo APP_BOXID;?>">
        <div class="widget-title">
            <span class="icon"> <i class="fa fa-plus-square"></i> </span>
            <h5 class="brs"><?php echo empty($this->id)?'添加':'修改' ; ?>公众号</b></h5>
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
            <span class="label right">申请地址:https://mp.weixin.qq.com/</span>
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
                        <span class="help-inline">本公众号所属的栏目</span>
                        <div class="clearfloat mb10"></div>
                        <div class="input-prepend input-append">
                            <span class="add-on">类型</span>
                            <select name="type" id="type" class="chosen-select span4">
                                <option value="1"> 订阅号 </option>
                                <option value="2"> 服务号 </option>
                                <option value="3"> 小程序-客服消息 </option>
                            </select>
                        </div>
                        <script>
                        $(function() { iCMS.select('type', "<?php echo $rs['type'];?>"); })
                        </script>
                        <span class="help-inline">本公众号类型</span>
                        <div class="clearfloat mb10"></div>
                        <div class="input-prepend">
                            <span class="add-on">APPID</span>
                            <input type="text" name="appid" class="span6" id="weixin_appid" value="<?php echo $rs['appid'] ; ?>" />
                        </div>
                        <span class="help-inline">公众号APPID</span>
                        <div class="clearfloat mt10"></div>
                        <div class="input-prepend">
                            <span class="add-on">appsecret</span>
                            <input type="text" name="appsecret" class="span6" id="weixin_appsecret" value="<?php echo $rs['appsecret'] ; ?>" />
                        </div>
                        <span class="help-inline">公众号appsecret</span>
                        <div class="clearfloat mt10"></div>
                        <div class="input-prepend input-append">
                            <span class="add-on">Token(令牌)</span>
                            <input type="text" name="token" class="span6" id="weixin_token" value="<?php echo $rs['token'] ; ?>" />
                            <a class="btn" id="weixin_token_make">
                                生成令牌
                            </a>
                        </div>
                        <div class="clearfloat mt10"></div>
                        <div class="input-prepend">
                            <span class="add-on">密钥</span>
                            <input type="text" name="AESKey" class="span6" id="weixin_AESKey" value="<?php echo $rs['AESKey'] ; ?>" />
                        </div>
                        <span class="help-inline">EncodingAESKey(消息加解密密钥)</span>
                        <div class="clearfloat mt10"></div>
                        <div class="input-prepend">
                            <span class="add-on">名称</span>
                            <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>" />
                        </div>
                        <span class="help-inline">公众号名称</span>
                        <div class="clearfloat mt10"></div>
                        <div class="input-prepend">
                            <span class="add-on">微信号</span>
                            <input type="text" name="account" class="span6" id="weixin_account" value="<?php echo $rs['account'] ; ?>" />
                        </div>
                        <div class="clearfloat mb10"></div>
                        <div class="input-prepend">
                            <span class="add-on">二维码</span>
                            <input type="text" name="qrcode" class="span6" id="qrcode" value="<?php echo $rs['qrcode'] ; ?>" />
                        </div>
                        <span class="help-inline">公众号的二维码链接/公众号码</span>
                        <div class="clearfloat mb10"></div>
                        <div class="input-prepend"><span class="add-on">描述</span>
                            <textarea name="description" id="description" class="span6" style="height: 90px;"><?php echo $rs['description'] ; ?></textarea>
                        </div>
                        <span class="help-inline">公众号的描述</span>
                    </div>
                    <div id="payment" class="tab-pane hide">
                        <div class="input-prepend">
                            <span class="add-on">仿真测试</span>
                            <div class="switch">
                                <input type="checkbox" data-type="switch" name="payment[use_sandbox]" id="wx_use_sandbox" <?php echo $payment[ 'use_sandbox']? 'checked': ''; ?>/>
                            </div>
                        </div>
                        <span class="help-inline">是否使用 微信支付仿真测试系统</span>
                        <div class="hide">
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
