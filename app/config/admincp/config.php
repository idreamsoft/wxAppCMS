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

$(function(){
  $(document).on("click",".del_device",function(){
      $(this).parent().parent().parent().remove();
  });
  $(".add_template_device").click(function(){
    var TD  = $("#template_device");
    var length = parseInt($("tr:last",TD).attr('data-key'))+1;
    var tdc = $(".template_device_clone tr").clone(true);
    if(!length) length = 0;
    tdc.attr('data-key', length);

    $('input',tdc).each(function(){
      this.id   = this.id.replace("{key}",length);
      if(this.name) this.name = this.name.replace("{key}",length);
    });

    $('.files_modal',tdc).each(function(index, el) {
      var href = $(this).attr("href").replace("{key}",length);
      $(this).attr("href",href);
    });

    tdc.appendTo(TD);
    return false;
  });
});
function modal_tplfile(el,a){
  if(!el) return;
  if(!a.checked) return;

  var e   = $('#'+el)||$('.'+el);
  var def = $("#template_desktop_tpl").val();
  var val = a.value.replace(def+'/', "{iTPL}/");
  e.val(val);
  return 'off';
}
function modal_tpl_index(el,a){
  if(!el) return;
  if(!a.checked) return;

  var e = $('#'+el)||$('.'+el),
  p = e.parent().parent(),
  pid = p.attr('id'),
  dir = $('#'+pid+'_tpl').val(),
  val = a.value.replace(dir+'/', "{iTPL}/");
  e.val(val);
  return 'off';
}
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-cog"></i> </span>
      <ul class="nav nav-tabs" id="config-tab">
        <li class="active"><a href="#config-base" data-toggle="tab">基本信息</a></li>
        <li><a href="#config-tpl" data-toggle="tab">模板</a></li>
        <li><a href="#config-url" data-toggle="tab">URL</a></li>
        <li><a href="#config-cache" data-toggle="tab">缓存</a></li>
        <li><a href="#config-file" data-toggle="tab">附件</a></li>
        <li><a href="#config-thumb" data-toggle="tab">缩略图</a></li>
        <li><a href="#config-watermark" data-toggle="tab">水印</a></li>
        <li><a href="#config-time" data-toggle="tab">时间</a></li>
        <li><a href="#config-other" data-toggle="tab">其它</a></li>
        <li><a href="#config-patch" data-toggle="tab">更新</a></li>
        <li><a href="#config-grade" data-toggle="tab">高级</a></li>
        <li><a href="#config-mail" data-toggle="tab">邮件</a></li>
        <li><a href="#apps-metadata" data-toggle="tab">动态属性</a></li>
        <?php //apps::config('tabs');?>
      </ul>
    </div>
    <div class="widget-content nopadding iCMS-config">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-config" target="iPHP_FRAME">
        <div id="config" class="tab-content">

          <div id="config-base" class="tab-pane active">
            <div class="input-prepend"> <span class="add-on">网站名称</span>
              <input type="text" name="config[site][name]" class="span6" id="name" value="<?php echo $config['site']['name'] ; ?>"/>
            </div>
            <span class="help-inline">网站名称</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">网站标题</span>
              <input type="text" name="config[site][seotitle]" class="span6" id="seotitle" value="<?php echo $config['site']['seotitle'] ; ?>"/>
            </div>
            <span class="help-inline">网站标题通常是搜索引擎关注的重点，本设置将出现在标题中网站名称的后面，如果有多个关键字，建议用 "|"、","(不含引号) 等符号分隔</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"><span class="add-on">关 键 字</span>
              <textarea name="config[site][keywords]" id="keywords" class="span6" style="height: 90px;"><?php echo $config['site']['keywords'] ; ?></textarea>
            </div>
            <span class="help-inline">网站关键字 用","号隔开</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"><span class="add-on">网站描述</span>
              <textarea name="config[site][description]" id="description" class="span6" style="height: 90px;"><?php echo $config['site']['description'] ; ?></textarea>
            </div>
            <span class="help-inline">将被搜索引擎用来说明您网站的主要内容</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">备 案 号</span>
              <input type="text" name="config[site][icp]" class="span3" id="title" value="<?php echo $config['site']['icp'] ; ?>"/>
            </div>
            <span class="help-inline">页面底部可以显示 ICP 备案信息，如果网站已备案，在此输入您的备案号，如果没有请留空</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">程序提示</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="config[debug][php]" id="debug_php" <?php echo $config['debug']['php']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-inline">程序错误提示!如果网站显示空白或者不完整,可开启此项,方便排除错误.<a onclick="javscript:$('.debug_php_trace,.debug_access_log').toggle();">更多</a></span>
            <div class="<?php echo $config['debug']['php_trace']?'':'hide'; ?> debug_php_trace">
              <div class="clearfloat mb10"></div>
              <div class="input-prepend"> <span class="add-on">程序调试信息</span>
                <div class="switch">
                  <input type="checkbox" data-type="switch" name="config[debug][php_trace]" id="debug_php_trace" <?php echo $config['debug']['php_trace']?'checked':''; ?>/>
                </div>
              </div>
              <span class="help-inline">显示程序调试信息</span>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="<?php echo $config['debug']['access_log']?'':'hide'; ?> debug_access_log">
              <div class="clearfloat mb10"></div>
              <div class="input-prepend"> <span class="add-on">后台访问记录</span>
                <div class="switch" data-off-label="开启" data-on-label="关闭">
                  <input type="checkbox" data-type="switch" name="config[debug][access_log]" id="debug_access_log" <?php echo $config['debug']['access_log']?'checked':''; ?>/>
                </div>
              </div>
              <span class="help-inline">后台访问记录 默认为开启状态</span>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">模板提示</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="config[debug][tpl]" id="debug_tpl" <?php echo $config['debug']['tpl']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-inline">模板错误提示!如果网站显示空白或者不完整,可开启此项,方便排除错误!模板调整时也可开启 <a onclick="javscript:$('.debug_tpl_trace').toggle();">更多</a></span>
            <div class="<?php echo $config['debug']['tpl_trace']?'':'hide'; ?> debug_tpl_trace">
              <div class="clearfloat mb10"></div>
              <div class="input-prepend"> <span class="add-on">模板调试信息</span>
                <div class="switch">
                  <input type="checkbox" data-type="switch" name="config[debug][tpl_trace]" id="debug_tpl_trace" <?php echo $config['debug']['tpl_trace']?'checked':''; ?>/>
                </div>
              </div>
              <span class="help-inline">模板所有数据调试信息</span>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">数据库提示</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="config[debug][db]" id="debug_db" <?php echo $config['debug']['db']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-inline">开启后将显示所有数据库错误信息. <a onclick="javscript:$('.debug_db_trace,.debug_db_explain').toggle();">更多</a></span>
            <div class="<?php echo $config['debug']['db_trace']?'':'hide'; ?> debug_db_trace">
              <div class="clearfloat mb10"></div>
              <div class="input-prepend"> <span class="add-on">SQL跟踪</span>
                <div class="switch">
                  <input type="checkbox" data-type="switch" name="config[debug][db_trace]" id="debug_db_trace" <?php echo $config['debug']['db_trace']?'checked':''; ?>/>
                </div>
              </div>
              <span class="help-inline">开启后将显示所有SQL执行情况</span>
            </div>
            <div class="<?php echo $config['debug']['db_explain']?'':'hide'; ?> debug_db_explain">
              <div class="clearfloat mb10"></div>
              <div class="input-prepend"> <span class="add-on">SQL解释</span>
                <div class="switch">
                  <input type="checkbox" data-type="switch" name="config[debug][db_explain]" id="debug_db_explain" <?php echo $config['debug']['db_explain']?'checked':''; ?>/>
                </div>
              </div>
              <span class="help-inline">开启后将显示 SQL EXPLAIN 信息</span>
            </div>
          </div>
          <div id="config-tpl" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">首页静态跳转</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="config[template][index][mode]" id="index_mode" <?php echo $config['template']['index']['mode']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-inline">只对桌面端有效.首页生成静态后自动跳转.如果出现循环跳转请关闭此项</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">首页REWRITE</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="config[template][index][rewrite]" id="index_rewrite" <?php echo $config['template']['index']['rewrite']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-inline">如果首页不是动态访问模式,且网站首页有分页 请开启此项</span>
            <div class="clearfloat mb10 solid"></div>
            <input type="hidden" name="config[template][index][tpl]" value="<?php echo $config['template']['index']['tpl']?:$config['template']['desktop']['index']; ?>"/>
            <input type="hidden" name="config[template][index][name]" value="<?php echo $config['template']['index']['name']?:'index' ; ?>"/>
            <div id="template_desktop">
              <div class="input-prepend"> <span class="add-on">桌面端域名</span>
                <input type="text" name="config[router][url]" class="span3" value="<?php echo $config['router']['url'] ; ?>"/>
              </div>
              <span class="help-inline">例:<span class="label label-info">https://www.icmsdev.com</span></span>
              <div class="clearfloat mb10"></div>
              <div class="input-prepend input-append"> <span class="add-on">桌面端模板</span>
                <input type="text" name="config[template][desktop][tpl]" class="span3" id="template_desktop_tpl" value="<?php echo $config['template']['desktop']['tpl'] ; ?>"/>
                <?php echo filesAdmincp::modal_btn('模板','template_desktop_tpl','dir');?>
              </div>
              <span class="help-inline">网站桌面端模板默认模板</span>
              <div class="clearfloat mb10"></div>
              <div class="input-prepend input-append"> <span class="add-on">首页模板</span>
                <input type="text" name="config[template][desktop][index]" class="span3" id="template_desktop_index" value="<?php echo $config['template']['desktop']['index']?:'{iTPL}/index.htm' ; ?>"/>
                <?php echo filesAdmincp::modal_btn('模板','template_desktop_index','file','tpl_index');?>
              </div>
              <span class="help-inline">桌面端默认模板</span>
            </div>
            <div class="clearfloat mb10 solid"></div>
            <div id="template_mobile">
              <div class="input-prepend"> <span class="add-on">移动端识别</span>
                <input type="text" name="config[template][mobile][agent]" class="span3" id="template_mobile_agent" value="<?php echo $config['template']['mobile']['agent'] ; ?>"/>
              </div>
              <span class="help-inline">请用<span class="label label-info">,</span>分隔 如不启用自动识别请留空</span>
              <div class="clearfloat mb10"></div>
              <div class="input-prepend"> <span class="add-on">移动端域名</span>
                <input type="text" name="config[template][mobile][domain]" class="span3" id="template_mobile_domain" value="<?php echo $config['template']['mobile']['domain'] ; ?>"/>
              </div>
              <span class="help-inline">例:<span class="label label-info">http://m.icmsdev.com</span></span>
              <div class="clearfloat mb10"></div>
              <div class="input-prepend input-append"> <span class="add-on">移动端模板</span>
                <input type="text" name="config[template][mobile][tpl]" class="span3" id="template_mobile_tpl" value="<?php echo $config['template']['mobile']['tpl'] ; ?>"/>
                <?php echo filesAdmincp::modal_btn('模板','template_mobile_tpl','dir');?>
              </div>
              <span class="help-inline">网站移动端模板默认模板,如果不想让程序自行切换请留空</span>
              <div class="clearfloat mb10"></div>
              <div class="input-prepend input-append"> <span class="add-on">首页模板</span>
                <input type="text" name="config[template][mobile][index]" class="span3" id="template_mobile_index" value="<?php echo $config['template']['mobile']['index']?:'{iTPL}/index.htm'; ?>"/>
                <?php echo filesAdmincp::modal_btn('模板','template_mobile_index','file','tpl_index');?>
              </div>
              <span class="help-inline">移动端首页默认模板</span>
            </div>
            <div class="clearfloat mb10 solid"></div>
            <div class="<?php echo $config['router']['redirect']?'':'hide'; ?> router_redirect">
              <div class="input-prepend"> <span class="add-on">适配跳转</span>
                <div class="switch">
                  <input type="checkbox" data-type="switch" name="config[router][redirect]" id="router_redirect" <?php echo $config['router']['redirect']?'checked':''; ?>/>
                </div>
              </div>
              <span class="help-inline">如果出现循环重定向(跳转)或者已在服务器配置做重定向,请关闭此项.</span>
              <div class="clearfloat mb10 solid"></div>
            </div>
            <table class="table table-hover">
              <thead>
                <tr>
                  <th style="text-align:left;">
                    <span class="label label-important fs16">模板优先级为:设备模板 &gt; 移动端模板 &gt; PC端模板</span>
                    <span class="label label-inverse fs16"><i class="icon-warning-sign icon-white"></i> 设备模板和移动端模板 暂时不支持生成静态模式</span>
                    <a onclick="javscript:$('.router_redirect').toggle();">适配跳转</a>
                  </th>
                </tr>
              </thead>
              <tbody id="template_device">
<?php
function template_device_td($key,$device=array()){
  $td_key = "device_{$key}";
?>
  <td>
    <div class="input-prepend input-append"> <span class="add-on">设备名称</span>
      <input type="text" name="config[template][device][<?php echo $key;?>][name]" class="span3" id="<?php echo $td_key;?>_name" value="<?php echo $device['name'];?>"/>
      <a class="btn del_device"><i class="fa fa-trash-o"></i> 删除</a>
    </div>
    <span class="help-inline"></span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend"> <span class="add-on">设备识别符</span>
      <input type="text" name="config[template][device][<?php echo $key;?>][ua]" class="span3" id="<?php echo $td_key;?>_ua" value="<?php echo $device['ua'];?>"/>
    </div>
    <span class="help-inline">设备唯一识别符,识别设备的User agent,如果多个请用<span class="label label-info">,</span>分隔.</span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend"> <span class="add-on">访问域名</span>
      <input type="text" name="config[template][device][<?php echo $key;?>][domain]" class="span3" id="<?php echo $td_key;?>_domain" value="<?php echo $device['domain'];?>"/>
    </div>
    <span class="help-inline"></span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend input-append"> <span class="add-on">设备模板</span>
      <input type="text" name="config[template][device][<?php echo $key;?>][tpl]" class="span3" id="<?php echo $td_key;?>_tpl" value="<?php echo $device['tpl'];?>"/>
      <?php echo filesAdmincp::modal_btn('模板',"<?php echo $td_key;?>_tpl",'dir');?>
    </div>
    <span class="help-inline">识别到的设备会使用这个模板设置</span>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend input-append"> <span class="add-on">首页模板</span>
      <input type="text" name="config[template][device][<?php echo $key;?>][index]" class="span3" id="<?php echo $td_key;?>_index" value="<?php echo $device['index']?:'{iTPL}/index.htm';?>"/>
      <?php echo filesAdmincp::modal_btn('模板',"<?php echo $td_key;?>_index",'file','tpl_index');?>
    </div>
    <span class="help-inline">设备的首页模板</span>
  </td>
<?php }?>
                <?php foreach ((array)$config['template']['device'] as $key => $device) {?>
                <?php echo '<tr data-key="'.$key.'">';?>
                <?php echo template_device_td($key,$device);?>
                <?php echo '</tr>';?>
                <?php }?>
              </tbody>
              <tfoot>
              <tr>
                <td colspan="2"><a href="#template_device" class="btn add_template_device btn-success"/><i class="fa fa-tablet"></i> 增加设备模板</a></td>
              </tr>
              </tfoot>
            </table>
          </div>
          <div id="config-url" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">404页面</span>
              <input type="text" name="config[router][404]" class="span4" id="router_404" value="<?php echo $config['router']['404'] ; ?>"/>
            </div>
            <span class="help-inline">404时跳转到的页面</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">公共资源URL</span>
              <input type="text" name="config[router][public]" class="span4" id="router_public" value="<?php echo $config['router']['public'] ; ?>"/>
            </div>
            <span class="help-inline">公共资源访问URL</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">用户URL</span>
              <input type="text" name="config[router][user]" class="span4" id="router_user" value="<?php echo $config['router']['user'] ; ?>"/>
            </div>
            <span class="help-inline">用户URL</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">目录</span>
              <input type="text" name="config[router][dir]" class="span4" id="router_dir" value="<?php echo $config['router']['dir'] ; ?>"/>
            </div>
            <span class="help-inline">网页目录，相对于admincp目录。可用../表示上级目录</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">网页后缀</span>
              <input type="text" name="config[router][ext]" class="span4" id="router_ext" value="<?php echo $config['router']['ext'] ; ?>"/>
            </div>
            <span class="help-inline">{EXT} 推荐使用.html</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">生成速度</span>
              <input type="text" name="config[router][speed]" class="span4" id="router_speed" value="<?php echo $config['router']['speed'] ; ?>"/>
            </div>
            <span class="help-inline">一次性生成多少静态页，可根据服务器IO性能调整</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">REWRITE</span>
              <div class="switch">
                <input type="checkbox" data-type="switch" name="config[router][rewrite]" id="router_rewrite" <?php echo $config['router']['rewrite']?'checked':''; ?>/>
              </div>
            </div>
            <a class="btn btn-small btn-success" href="https://www.icmsdev.com/docs/rewrite.html" target="_blank"><i class="fa fa-question-circle"></i> 查看帮助</a>
            <span class="help-inline">此选项只对应用的路由配置起作用</span>
          </div>
          <div id="config-cache" class="tab-pane hide">
            <?php include admincp::view("cache.config","cache");?>
          </div>
          <div id="config-file" class="tab-pane hide">
            <?php include admincp::view("files.config","files");?>
          </div>
          <div id="config-thumb" class="tab-pane hide">
            <?php include admincp::view("thumb.config","files");?>
          </div>
          <div id="config-watermark" class="tab-pane hide">
            <?php include admincp::view("watermark.config","files");?>
          </div>
          <div id="config-time" class="tab-pane hide">
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">服务器时区</span>
              <select name="config[time][zone]" id="time_zone" class="span4 chosen-select">
                <option value="Pacific/Kwajalein">(标准时-12：00) 日界线西 </option>
                <option value="Pacific/Samoa">(标准时-11：00) 中途岛、萨摩亚群岛 </option>
                <option value="Pacific/Honolulu">(标准时-10：00) 夏威夷 </option>
                <option value="America/Juneau">(标准时-9：00) 阿拉斯加 </option>
                <option value="America/Los_Angeles">(标准时-8：00) 太平洋时间(美国和加拿大) </option>
                <option value="America/Denver">(标准时-7：00) 山地时间(美国和加拿大) </option>
                <option value="America/Mexico_City">(标准时-6：00) 中部时间(美国和加拿大)、墨西哥城 </option>
                <option value="America/New_York">(标准时-5：00) 东部时间(美国和加拿大)、波哥大 </option>
                <option value="America/Caracas">(标准时-4：00) 大西洋时间(加拿大)、加拉加斯 </option>
                <option value="America/St_Johns">(标准时-3：30) 纽芬兰 </option>
                <option value="America/Argentina/Buenos_Aires">(标准时-3：00) 巴西、布宜诺斯艾利斯、乔治敦 </option>
                <option value="Atlantic/Azores">(标准时-2：00) 中大西洋 </option>
                <option value="Atlantic/Azores">(标准时-1：00) 亚速尔群岛、佛得角群岛 </option>
                <option value="Europe/London">(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡 </option>
                <option value="Europe/Paris">(标准时+1：00) 中欧时间、安哥拉、利比亚 </option>
                <option value="Europe/Helsinki">(标准时+2：00) 东欧时间、开罗，雅典 </option>
                <option value="Europe/Moscow">(标准时+3：00) 巴格达、科威特、莫斯科 </option>
                <option value="Asia/Tehran">(标准时+3：30) 德黑兰 </option>
                <option value="Asia/Baku">(标准时+4：00) 阿布扎比、马斯喀特、巴库 </option>
                <option value="Asia/Kabul">(标准时+4：30) 喀布尔 </option>
                <option value="Asia/Karachi">(标准时+5：00) 叶卡捷琳堡、伊斯兰堡、卡拉奇 </option>
                <option value="Asia/Calcutta">(标准时+5：30) 孟买、加尔各答、新德里 </option>
                <option value="Asia/Colombo">(标准时+6：00) 阿拉木图、 达卡、新亚伯利亚 </option>
                <option value="Asia/Bangkok">(标准时+7：00) 曼谷、河内、雅加达 </option>
                <option value="Asia/Shanghai">(北京时间) 北京、重庆、香港、新加坡 </option>
                <option value="Asia/Tokyo">(标准时+9：00) 东京、汉城、大阪、雅库茨克 </option>
                <option value="Australia/Darwin">(标准时+9：30) 阿德莱德、达尔文 </option>
                <option value="Pacific/Guam">(标准时+10：00) 悉尼、关岛 </option>
                <option value="Asia/Magadan">(标准时+11：00) 马加丹、索罗门群岛 </option>
                <option value="Asia/Kamchatka">(标准时+12：00) 奥克兰、惠灵顿、堪察加半岛 </option>
              </select>
            </div>
            <script>$(function(){iCMS.select('time_zone',"<?php echo $config['time']['zone'] ; ?>");});</script>
            <span class="help-inline">服务器所在时区</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">服务器时间校正</span>
              <input type="text" name="config[time][cvtime]" class="span3" id="time_cvtime" value="<?php echo $config['time']['cvtime'] ; ?>"/>
            </div>
            <span class="help-inline">单位:分钟</span>
            <div class="clearfloat"></div>
            <span class="help-inline">此功能用于校正服务器操作系统时间设置错误的问题
            当确认程序默认时区设置正确后，程序显示时间仍有错误，请使用此功能校正</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">默认时间格式</span>
              <input type="text" name="config[time][dateformat]" class="span3" id="time_dateformat" value="<?php echo $config['time']['dateformat'] ; ?>"/>
              <div class="btn-group" to="#FS_dir_format"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-question-circle"></i> 帮助</a>
                <ul class="dropdown-menu">
                  <li><a href="#Y"><span class="label label-inverse">Y</span> 4位数年份</a></li>
                  <li><a href="#y"><span class="label label-inverse">y</span> 2位数年份</a></li>
                  <li><a href="#m"><span class="label label-inverse">m</span> 月份01-12</a></li>
                  <li><a href="#n"><span class="label label-inverse">n</span> 月份1-12</a></li>
                  <li><a href="#d"><span class="label label-inverse">n</span> 日期01-31</a></li>
                  <li><a href="#j"><span class="label label-inverse">j</span> 日期1-31</a></li>
                </ul>
              </div>
            </div>
          </div>
          <div id="config-other" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">侧边栏</span>
              <div class="switch" data-on-label="启用" data-off-label="关闭">
                <input type="checkbox" data-type="switch" name="config[other][sidebar_enable]" id="other_sidebar_enable" <?php echo $config['other']['sidebar_enable']?'checked':''; ?>/>
              </div>
              <div class="switch" data-on-label="打开" data-off-label="最小化">
                <input type="checkbox" data-type="switch" name="config[other][sidebar]" id="other_sidebar" <?php echo $config['other']['sidebar']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-inline">后台侧边栏默认开启,启用后可选择打开或者最小化</span>
            <hr />
            <h3 class="title">百度站长平台 主动推送(实时)</h3>
            <span class="help-inline">申请地址:http://zhanzhang.baidu.com/ (需要权限)</span>
            <div class="clearfloat"></div>
            <div class="input-prepend"> <span class="add-on">站点</span>
              <input type="text" name="config[api][baidu][sitemap][site]" class="span3" id="baidu_sitemap_site" value="<?php echo $config['api']['baidu']['sitemap']['site'] ; ?>"/>
            </div>
            <span class="help-inline">在站长平台验证的站点，比如www.example.com</span>
            <div class="clearfloat mt10"></div>
            <div class="input-prepend"> <span class="add-on">准入密钥</span>
              <input type="text" name="config[api][baidu][sitemap][access_token]" class="span3" id="baidu_sitemap_access_token" value="<?php echo $config['api']['baidu']['sitemap']['access_token'] ; ?>"/>
            </div>
            <span class="help-inline">在站长平台申请的推送用的准入密钥</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">同步推送</span>
              <div class="switch" data-on-label="启用" data-off-label="关闭">
                <input type="checkbox" data-type="switch" name="config[api][baidu][sitemap][sync]" id="baidu_sitemap_sync" <?php echo $config['api']['baidu']['sitemap']['sync']?'checked':''; ?>/>
              </div>
            </div>
            <span class="help-inline">启用文章发布时同步推送 如果发布文章无法正常返回 建议关闭</span>
          </div>
          <div id="config-patch" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">系统更新</span>
              <select name="config[system][patch]" id="system_patch" class="span3 chosen-select">
                <option value="1">自动下载,安装时询问(推荐)</option>
                <option value="2">不自动下载更新,有更新时提示</option>
                <option value="0">关闭自动更新</option>
              </select>
            </div>
            <script>$(function(){iCMS.select('system_patch',"<?php echo (int)$config['system']['patch'] ; ?>");});</script>
          </div>
          <div id="config-grade" class="tab-pane hide">
            <?php include admincp::view("config.grade","config");?>
          </div>
          <div id="config-mail" class="tab-pane hide">
            <?php include admincp::view("config.email","config");?>
          </div>
          <div id="apps-metadata" class="tab-pane hide">
            <?php include admincp::view("apps.meta","apps");?>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary btn-large" type="submit"><i class="fa fa-check"></i> 保 存</button>
        </div>
      </form>
    </div>
  </div>
</div>
<table class="hide template_device_clone">
  <tr>
    <?php echo template_device_td('{key}');?>
  </tr>
</table>
<?php admincp::foot();?>
