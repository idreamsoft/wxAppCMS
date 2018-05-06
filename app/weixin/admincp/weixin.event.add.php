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
    var msgArray = <?php echo $rs['msg']?$rs['msg']:'null'; ?>;

    $("#eventype").bind('chosen:updated change', function(event) {
        eventype_change(this);
    });
    $("#msgtype").bind('chosen:updated change', function(event) {
        msgtype_change(this);
    });
    $("#wx_appid").bind('chosen:updated change', function(event) {
        var appid = $('option:selected', this).val();
        var type = $('option:selected', this).data('type');
        if(type=="3"){
            var opts = $("#msgtype-wxapp").html();
        }else{
            var opts = $("#msgtype-default").html();
        }
        $("#msgtype").html(opts).trigger("chosen:updated");

        $('.media_modal[data-toggle="modal"]').each(function(index, el) {
            var url = $(this).attr('href');
            var pos = url.indexOf('&wx_appid');
            if (pos > 0) {
                url = url.substring(0, pos);
            }
            url += '&wx_appid=' + appid;
            $(this).attr('href', url);
        });
    });

    function eventype_change(a) {
        var $opts = $('option:selected', a);
        var type = $opts.parent().data('type');
        var g_addon = $opts.parent().data('addon') || '事件KEY值';
        var g_help = $opts.parent().data('help') || '事件KEY值，与自定义菜单接口中KEY值对应';
        var addon = $opts.data('addon') || g_addon;
        var help = $opts.data('help') || g_help;
        var is_def = $opts.data('default')
        var is_operator = $opts.data('operator')

        $("#operator_input").hide();
        $("#eventkey_input .add-on").text(addon);
        $("#eventkey_input .help-inline").text(help);

        if (is_def) {
            var select = $opts.val(),
                text = $opts.text();
            $("#name").val(text);
            $("#eventkey").val(select);
        }

        if (is_operator) {
            $("#operator_input").show();
        }
    }

    function msg(msgArray, keyArray, name) {
        $.each(msgArray, function(index, value) {
            if (typeof(value) === 'object') {
                keyArray.push('[' + index + ']');
                msg(value, keyArray, name);
                keyArray = [];
            } else if (typeof(value) === 'string') {
                var key = keyArray.join('') + '[' + index + ']';
                if (name) {
                    var kname = '[name="msg[' + name + ']' + key + '"]';
                } else {
                    var kname = '[name="msg' + key + '"]';
                }
                console.log(kname);
                $(kname).val(value);
            }
        });
        // console.log(keyArray);
    }

    function msgtype_change(a) {
        var select = $('option:selected', a).val();
        var clone = $('#msg-' + select).clone(true).show();
        $('#msg').html(clone);
        if (select == 'news') {
            $('#msg').html('<a name="additem" class="btn btn-inverse">添加一条</a>');
            if (msgArray) {
                $.each(msgArray['Articles'], function(index, value) {
                    articles_item();
                });
                msg(msgArray['Articles'], [], 'Articles');
            } else {
                articles_item();
            }
        } else {
            if (msgArray) {
                msg(msgArray, []);
            }
        }
    }
    $("#msg").on("click", 'a[name="additem"]', function(event) {
        event.preventDefault();
        articles_item();
    });
    $("#msg").on("click", 'a[name="delitem"]', function(event) {
        event.preventDefault();
        $(this).parent().remove();
    });
    $("#iCMS-event").submit(function() {
        var eventype = $("#eventype option:selected").attr("value");
        if (eventype == "0") {
            iCMS.alert("请选择事件类型");
            $("#eventype").focus();
            return false;
        }
        if ($("#name").val() == '') {
            iCMS.alert("请填写事件名称!");
            $("#name").focus();
            return false;
        }
        if ($("#eventkey").val() == '') {
            iCMS.alert("请填写事件KEY值!");
            $("#eventkey").focus();
            return false;
        }
        if (eventype == "keyword") {
            if ($("#operator option:selected").attr("value") == "0") {
                iCMS.alert("请选择关键词匹配模式");
                $("#operator").focus();
                return false;
            }
        }
        if ($("#msgtype option:selected").attr("value") == "0") {
            iCMS.alert("请选择回复消息的类型");
            $("#msgtype").focus();
            return false;
        }
    });
});

function articles_item() {
    var length = $('#msg .articles_item').length + 1;
    var clone = $('#msg-news .articles_item').clone(true);
    var dkey = $(".articles_item:last", '#msg').attr('data-key');
    if (length > 10) {
        iCMS.alert("图文信息最多只能添加10条");
        return false;
    }
    if (typeof dkey == "undefined") {
        key = 0;
    } else {
        key = parseInt(dkey) + 1;
    }
    var html = clone[0].outerHTML.replace(/\{KEY\}/g, key);
    $('#msg').append(html);
}

function modal_media(e, a) {
    if (!e) return;
    if (!a.checked) return;

    $('#' + e, $('#msg')).val(a.value);
    return 'off';
}

function modal_tplfile(e, a) {
    if (!e) return;
    if (!a.checked) return;

    var val = a.value.replace(iCMS.config.DEFTPL + '/', "{iTPL}/");
    $('#' + e, $('#msg')).val(val);
    return 'off';
}
</script>
<div class="iCMS-container">
    <div class="widget-box">
        <div class="widget-title">
            <span class="icon"> <i class="fa fa-plus-square"></i> </span>
            <h5><?php echo empty($id)?'添加':'修改' ; ?>事件</h5>
        </div>
        <div class="widget-content nopadding">
            <form action="<?php echo APP_FURI; ?>&do=event_save" method="post" class="form-inline" id="iCMS-event" target="iPHP_FRAME">
                <input name="id" type="hidden" value="<?php echo $id ; ?>" />
                <div id="event-add" class="tab-content">
                    <div class="input-prepend input-append">
                        <span class="add-on">公众号</span>
                        <select name="wx_appid" id="wx_appid" class="chosen-select span5">
                            <option></option>
                            <?php echo $this->option(); ?>
                        </select>
                    </div>
                    <script>$(function() { iCMS.select('wx_appid', "<?php echo $rs['appid']?$rs['appid']:$_GET['wx_appid'];?>"); })</script>
                    <span class="help-inline">请选择绑定事件的公众号</span>
                    <div class="clearfloat mb10"></div>
                    <div class="input-prepend">
                        <span class="add-on">事件类型</span>
                        <select name="eventype" id="eventype" class="span5 chosen-select" data-placeholder="请选择事件类型...">
                            <option value='0'></option>
                            <optgroup label="用户消息" data-type="keyword" data-addon="关键词" data-help="用户输入的关键词">
                                <option value='keyword' data-operator="true">关键词</option>
                            </optgroup>
                            <optgroup label="菜单事件" data-type="event">
                                <option value='click'>点击事件</option>
                                <option value='view' data-addon="跳转URL" data-help="设置跳转URL">跳转链接</option>
                                <option value='scancode_push'>扫码推事件</option>
                                <option value='scancode_waitmsg'>扫码带提示</option>
                                <option value='pic_sysphoto'>系统拍照发图</option>
                                <option value='pic_photo_or_album'>拍照或者相册发图</option>
                                <option value='pic_weixin'>微信相册发图器</option>
                                <option value='location_select'>地理位置选择器</option>
                            </optgroup>
                            <optgroup label="系统事件" data-type="system" data-help="此类型事件可不填写">
                                <option value='subscribe' data-default="true">关注</option>
                                <option value='unsubscribe' data-default="true">取消关注</option>
                                <option value='location' data-default="true">上报地理位置</option>
                            </optgroup>
                            <optgroup label="小程序客服会话" data-type="contact">
                                <option value='contact_text' data-operator="true" data-addon="关键词" data-help="用户输入的关键词">文本消息</option>
                                <option value='contact_image'>图片消息</option>
                                <option value='miniprogrampage'>小程序卡片消息</option>
                            </optgroup>
                        </select>
                    </div>
                    <script>$(function() { iCMS.select('eventype', "<?php echo $rs['eventype'];?>"); })</script>
                    <span class="help-inline">如无法使用,请确认是否需要公众号认证</span>
                    <div class="clearfloat mb10"></div>
                    <div class="input-prepend">
                        <span class="add-on">事件名称</span>
                        <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>" />
                    </div>
                    <span class="help-inline">事件中文名称</span>
                    <div class="clearfloat mb10"></div>
                    <div id="eventkey_input">
                        <div class="input-prepend">
                            <span class="add-on">事件KEY值</span>
                            <input type="text" name="eventkey" class="span6" id="eventkey" value="<?php echo $rs['eventkey'] ; ?>" />
                        </div>
                        <span class="help-inline">事件KEY值，与自定义菜单接口中KEY值或回复的关键词对应</span>
                        <div class="clearfloat mb10"></div>
                    </div>
                    <div id="operator_input" class="hide">
                        <div class="input-prepend">
                            <span class="add-on">匹配模式</span>
                            <select name="operator" id="operator" class="span3 chosen-select" data-placeholder="请选择关键词匹配模式...">
                                <option value='0'></option>
                                <option value='eq'>完全匹配</option>
                                <option value='in'>包含关键词</option>
                                <option value='re'>正则</option>
                            </select>
                        </div>
                        <div class="clearfloat mb10"></div>
                        <script>$(function() { iCMS.select('operator', "<?php echo $rs['operator'];?>"); })</script>
                    </div>
                    <div class="input-prepend input-append">
                        <span class="add-on">事件属性</span>
                        <select name="pid" id="pid" class="chosen-select span3">
                            <option></option>
                            <?php echo propAdmincp::get("pid") ; ?>
                        </select>
                        <?php echo propAdmincp::btn_add('添加常用属性');?>
                    </div>
                    <script>$(function() { iCMS.select('pid', "<?php echo $rs['pid'];?>"); })</script>
                    <div class="clearfloat mb10"></div>
                    <div class="input-prepend input-append">
                        <span class="add-on">事件状态</span>
                        <select name="status" id="status" class="chosen-select span3">
                            <option value="1">正常[status='1']</option>
                            <option value="0">草稿[status='0']</option>
                            <?php echo propAdmincp::get("status");?>
                        </select>
                        <?php echo propAdmincp::btn_add('添加状态');?>
                    </div>
                    <script>$(function() { iCMS.select('status', "<?php echo $rs['status'];?>"); })</script>
                    <div class="clearfloat mb10"></div>
                    <div class="input-prepend">
                        <span class="add-on">回复类型</span>
                        <select name="msgtype" id="msgtype" class="span3 chosen-select" data-placeholder="请选择回复消息的类型...">
                            <option value='0'></option>
                            <optgroup label="公众号">
                              <option value='text'>文本消息</option>
                              <option value='image'>图片消息</option>
                              <option value='voice'>语音消息</option>
                              <option value='video'>视频消息</option>
                              <option value='music'>音乐消息</option>
                              <option value='news'>图文消息</option>
                              <option value='tpl'>调用模板</option>
                            </optgroup>
                        </select>
                    </div>
                    <script>$(function() { iCMS.select('msgtype', "<?php echo $rs['msgtype'];?>"); })</script>
                    <div class="clearfloat mb10"></div>
                    <div id="msg"></div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div style="display:none;" id="msgtype-default">
    <option value='0'></option>
    <optgroup label="公众号">
      <option value='text'>文本消息</option>
      <option value='image'>图片消息</option>
      <option value='voice'>语音消息</option>
      <option value='video'>视频消息</option>
      <option value='music'>音乐消息</option>
      <option value='news'>图文消息</option>
      <option value='tpl'>调用模板</option>
    </optgroup>
</div>
<div style="display:none;" id="msgtype-wxapp">
    <option value='0'></option>
    <optgroup label="小程序客服会话">
      <option value='wxapp-text'>文本消息</option>
      <option value='wxapp-image'>图片消息</option>
      <option value='wxapp-link'>图文链接</option>
      <option value='wxapp-miniprogrampage'>小程序卡片</option>
    </optgroup>
</div>
<div style="display:none;" id="msg-wxapp-text">
    <div class="input-prepend">
        <span class="add-on">消息内容</span>
        <textarea name="msg[text][content]" id="msg_text_content" class="span6" style="height: 150px;"></textarea>
    </div>
    <div class="clearfloat mt10"></div>
    <span class="help-inline">
      发送文本消息时，支持添加可跳转小程序的文字链<br />
      <code>文本内容....&lt;a href="http://www.qq.com" data-miniprogram-appid="appid" data-miniprogram-path="pages/index/index"&gt;点击跳小程序&lt;/a&gt;</code>
    </span>
</div>
<div style="display:none;" id="msg-wxapp-image">
    <div class="input-prepend input-append">
        <span class="add-on">MediaId</span>
        <input type="text" name="msg[image][media_id]" class="span6" id="msg_wxapp_image_media_id" value="" />
        <?php echo self::modal_btn('素材','msg_wxapp_image_media_id','image');?>
    </div>
    <span class="help-inline">通过上传多媒体文件，得到的media_id。</span>
</div>
<div style="display:none;" id="msg-wxapp-link">
    <div class="input-prepend">
        <span class="add-on">图文标题</span>
        <input type="text" name="msg[link][title]" class="span6" id="msg_link_title" value="" />
    </div>
    <span class="help-inline">图文链接标题</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
        <span class="add-on">图文描述</span>
        <textarea name="msg[link][description]" id="msg_link_description" class="span6" style="height: 150px;"></textarea>
    </div>
    <span class="help-inline">图文链接描述</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
        <span class="add-on">图文链接</span>
        <input type="text" name="msg[link][url]" class="span6" id="msg_link_url" value="" />
    </div>
    <span class="help-inline">图文链接</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend input-append">
        <span class="add-on">缩略图</span>
        <input type="text" name="msg[link][thumb_url]" class="span6" id="msg_link_thumb_url" value="" />
        <?php filesAdmincp::pic_btn("msg_link_thumb_url");?>
    </div>
    <span class="help-inline">图文链接消息的图片链接，支持 JPG、PNG 格式，较好的效果为大图 640 X 320，小图 80 X 80</span>
</div>
<div style="display:none;" id="msg-wxapp-miniprogrampage">
    <div class="input-prepend">
        <span class="add-on">小程序标题</span>
        <input type="text" name="msg[miniprogrampage][title]" class="span6" id="msg_miniprogrampage_title" value="" />
    </div>
    <span class="help-inline">小程序标题</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
        <span class="add-on">小程序页面</span>
        <input type="text" name="msg[miniprogrampage][pagepath]" class="span6" id="msg_miniprogrampage_pagepath" value="" />
    </div>
    <span class="help-inline">小程序的页面路径，跟app.json对齐，支持参数，比如/pages/index/index?foo=bar</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend input-append">
        <span class="add-on">缩略图</span>
        <input type="text" name="msg[miniprogrampage][thumb_media_id]" class="span6" id="msg_miniprogrampage_thumb_media_id" value="" />
        <?php echo self::modal_btn('素材','msg_miniprogrampage_thumb_media_id','image');?>
    </div>
    <span class="help-inline">小程序消息卡片的封面， image类型的media_id，通过新增素材接口上传图片文件获得，建议大小为520*416</span>
</div>
<div style="display:none;" id="msg-text">
    <div class="input-prepend">
        <span class="add-on">消息内容</span>
        <textarea name="msg[Text]" id="msg_Text" class="span6" style="height: 150px;"></textarea>
    </div>
    <span class="help-inline">回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）</span>
</div>
<div style="display:none;" id="msg-image">
    <div class="input-prepend input-append">
        <span class="add-on">MediaId</span>
        <input type="text" name="msg[Image][MediaId]" class="span6" id="msg_Image_MediaId" value="" />
        <?php echo self::modal_btn('素材','msg_Image_MediaId','image');?>
    </div>
    <span class="help-inline">通过上传多媒体文件，得到的media_id。</span>
</div>
<div style="display:none;" id="msg-voice">
    <div class="input-prepend input-append">
        <span class="add-on">MediaId</span>
        <input type="text" name="msg[Voice][MediaId]" class="span6" id="msg_Voice_MediaId" value="" />
        <?php echo self::modal_btn('素材','msg_Voice_MediaId','voice');?>
    </div>
    <span class="help-inline">通过上传多媒体文件，得到的media_id。</span>
</div>
<div style="display:none;" id="msg-video">
    <div class="input-prepend input-append">
        <span class="add-on">MediaId</span>
        <input type="text" name="msg[Video][MediaId]" class="span6" id="msg_Video_MediaId" value="" />
        <?php echo self::modal_btn('素材','msg_Video_MediaId','video');?>
    </div>
    <span class="help-inline">通过上传多媒体文件，得到的media_id。</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
        <span class="add-on">视频标题</span>
        <input type="text" name="msg[Video][Title]" class="span6" id="msg_Video_Title" value="" />
    </div>
    <span class="help-inline">视频消息的标题</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
        <span class="add-on">视频描述</span>
        <textarea name="msg[Video][Description]" id="msg_Video_Description" class="span6" style="height: 150px;"></textarea>
    </div>
    <span class="help-inline">视频消息的描述</span>
</div>
<div style="display:none;" id="msg-music">
    <div class="input-prepend">
        <span class="add-on">音乐标题</span>
        <input type="text" name="msg[Music][Title]" class="span6" id="msg_Music_Title" value="" />
    </div>
    <span class="help-inline">音乐标题</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
        <span class="add-on">音乐描述</span>
        <textarea name="msg[Music][Description]" id="msg_Music_Description" class="span6" style="height: 150px;"></textarea>
    </div>
    <span class="help-inline">音乐描述</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
        <span class="add-on">音乐链接</span>
        <input type="text" name="msg[Music][MusicUrl]" class="span6" id="msg_Music_MusicUrl" value="" />
    </div>
    <span class="help-inline">音乐链接</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend">
        <span class="add-on">高质量</span>
        <input type="text" name="msg[Music][HQMusicUrl]" class="span6" id="msg_Music_HQMusicUrl" value="" />
    </div>
    <span class="help-inline">高质量音乐链接，WIFI环境优先使用该链接播放音乐</span>
    <div class="clearfloat mt10"></div>
    <div class="input-prepend input-append">
        <span class="add-on">缩略图</span>
        <input type="text" name="msg[Music][ThumbMediaId]" class="span6" id="msg_Music_ThumbMediaId" value="" />
        <?php echo self::modal_btn('素材','msg_Music_ThumbMediaId','image');?>
    </div>
    <span class="help-inline">缩略图的媒体id，通过上传多媒体文件，得到的media_id</span>
</div>
<div style="display:none;" id="msg-news">
    <div class="articles_item" data-key="{KEY}">
        <hr />
        <div class="input-prepend">
            <span class="add-on">图文标题</span>
            <input type="text" name="msg[Articles][{KEY}][item][Title]" class="span6" id="msg_Articles_{KEY}_item_Title" value="" />
        </div>
        <span class="help-inline">图文标题</span>
        <div class="clearfloat mt10"></div>
        <div class="input-prepend">
            <span class="add-on">图文描述</span>
            <textarea name="msg[Articles][{KEY}][item][Description]" id="msg_Articles_{KEY}_item_Description" class="span6" style="height: 150px;"></textarea>
        </div>
        <span class="help-inline">图文描述</span>
        <div class="clearfloat mt10"></div>
        <div class="input-prepend">
            <span class="add-on">图片链接</span>
            <input type="text" name="msg[Articles][{KEY}][item][PicUrl]" class="span6" id="msg_Articles_{KEY}_item_PicUrl" value="" />
        </div>
        <span class="help-inline">图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200</span>
        <div class="clearfloat mt10"></div>
        <div class="input-prepend">
            <span class="add-on">图文链接</span>
            <input type="text" name="msg[Articles][{KEY}][item][Url]" class="span6" id="msg_Articles_{KEY}_item_Url" value="" />
        </div>
        <span class="help-inline">点击图文消息跳转链接</span>
        <div class="clearfloat mt10"></div>
        <a name="delitem" class="btn btn-danger">删除</a>
    </div>
</div>
<div style="display:none;" id="msg-tpl">
    <div class="input-prepend input-append"> <span class="add-on">API模板</span>
        <input type="text" name="msg[Tpl]" class="span3" id="msg_Tpl" />
        <?php echo filesAdmincp::modal_btn('模板','msg_Tpl','file','tplfile');?>
    </div>
    <span class="help-inline">可在模板中调用各应用数据</span>
</div>
<?php admincp::foot();?>
