<!-- 字段选择 -->
<div class="iFormer-design">
  <div class="widget-title">
    <span class="icon"> <i class="fa fa-cog"></i> </span>
    <h5 class="brs">字段</h5>
    <ul class="nav nav-tabs" id="fields-tab">
      <li class="active"><a href="#fields-tab-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 简易字段</a></li>
      <li><a href="#fields-tab-func" data-toggle="tab"><i class="fa fa-cog"></i> 功能字段</a></li>
      <li><a href="#fields-tab-addons" data-toggle="tab"><i class="fa fa-cog"></i> 附加字段</a></li>
    </ul>
  </div>
  <div id="fields-tab-content" class="tab-content">
    <div id="fields-tab-base" class="tab-pane active">
      <ul>
        <li i="layout" tag="br" type="br" class="br">
          <span class="fa fa-arrows-h"></span>
          <p style="vertical-align: text-top;">换行符</p>
        </li>
        <li i="field" tag="input" type="hidden" field="VARCHAR" len="255" label="隐藏字段">
          <span class="fb-icon fb-icon-input"></span>
          <p>隐藏字段</p>
        </li>
        <li i="field" tag="input" type="text" field="VARCHAR" len="255" label="单行">
          <span class="fb-icon fb-icon-input"></span>
          <p>单行</p>
        </li>
        <li i="field" tag="input" type="text" field="VARCHAR" len="5120" label="长文本">
          <span class="fb-icon fb-icon-input"></span>
          <p>单行长文本</p>
        </li>
        <li i="field" tag="textarea" type="textarea" field="TEXT" label="多行">
          <span class="fb-icon fb-icon-textarea"></span>
          <p>多行</p>
        </li>
        <li i="field" tag="input" type="text" field="VARCHAR" len="255" label="邮箱">
          <span class="fb-icon fb-icon-mail"></span>
          <p>邮箱</p>
        </li>
        <li i="field" tag="input" type="date" field="INT" len="10" label="日期">
          <span class="fb-icon fb-icon-date"></span>
          <p>日期</p>
        </li>
        <li i="field" tag="input" type="datetime" field="INT" len="10" label="时间">
          <span class="timeIcon fb-icon fb-icon-datetime"></span>
          <p>日期时间</p>
        </li>
        <li i="field" tag="input" type="radio" field="VARCHAR" len="255" label="单选">
          <span class="fb-icon fb-icon-radio"></span>
          <p>单选框</p>
        </li>
        <li i="field" tag="input" type="checkbox" field="VARCHAR" len="255" label="复选">
          <span class="fb-icon fb-icon-checkbox"></span>
          <p>复选框</p>
        </li>
        <li i="field" tag="select" type="select" field="VARCHAR" len="255" label="列表">
          <span class="fb-icon fb-icon-dropdown"></span>
          <p>下拉列表</p>
        </li>
        <li i="field" tag="select" type="multiple" field="VARCHAR" len="255" label="多选">
          <span class="multiselect fb-icon fb-icon-multiselect"></span>
          <p>多选列表</p>
        </li>
        <li i="field" tag="input" type="number" field="TINYINT" len="1" label="数字">
          <span class="fb-icon fb-icon-number"></span>
          <p>数字</p>
        </li>
        <li i="field" tag="input" type="number" field="INT" len="10" label="大数字">
          <span class="fb-icon fb-icon-number"></span>
          <p>大数字</p>
        </li>
        <li i="field" tag="input" type="number" field="BIGINT" len="20" label="超大数字">
          <span class="fb-icon fb-icon-number"></span>
          <p>超大数字</p>
        </li>
        <li i="field" tag="input" type="decimal" field="DECIMAL" len="6,2" label="小数">
          <span class="fb-icon fb-icon-decimal"></span>
          <p>小数</p>
        </li>
        <li i="field" tag="input" type="percentage" field="DECIMAL" len="4,2" label="百分比" label-after="%">
          <span class="fb-icon fb-icon-percentage"></span>
          <p>百分比</p>
        </li>
        <li i="field" tag="input" type="currency" field="INT" len="10" label="货币" label-after="¥">
          <span class="fb-icon fb-icon-currency"></span>
          <p>货币</p>
        </li>
        <li i="field" tag="input" type="text" field="VARCHAR" len="255" label="链接">
          <span class="fb-icon fb-icon-url"></span>
          <p>Url</p>
        </li>
        <!--                         <li i="field" fieldtype="32">
          <span class="lookupIcon fb-icon fb-icon-lookup"></span>
          <p class="lookupConent">查找</p>
        </li>
        <li i="field" fieldtype="14">
          <span class="addnotesIcon fb-icon fb-icon-addnotes"></span>
          <p class="addnotestext">添加备注</p>
        </li>
        <li i="field" fieldtype="99">
          <span class="subformIcon fb-icon fb-icon-subform"></span>
          <p class="subformText">子表单</p>
        </li>
        <li i="field" fieldtype="31">
          <span class="autonumberIcon fb-icon fb-icon-autonumber"></span>
          <p class="lookupConent">自动编号</p>
        </li>
        <li i="field" fieldtype="15">
          <span class="formulaIcon fb-icon fb-icon-formula"></span>
          <p class="formulaText">公式</p>
        </li>
        <li i="field" fieldtype="36">
          <span class="signatureIcon fb-icon fb-icon-signature"></span>
          <p class="lookupConent">签名 </p>
        </li>
        <li i="field" fieldtype="30">
          <span class="usersIcon fb-icon fb-icon-name" style="margin-top:1px;"></span>
          <p class="lookupConent">用户</p>
        </li> -->
        <div class="clearfix"></div>
      </ul>
      <div class="clearfix"></div>
    </div>
    <div id="fields-tab-func" class="tab-pane">
      <ul>
        <li i="field" tag="tpldir" type="tpldir" field="VARCHAR" len="255" label="模板目录">
          <span class="fb-icon fb-icon-template"></span>
          <p>选择框-模板目录</p>
        </li>
        <li i="field" tag="tplfile" type="tplfile" field="VARCHAR" len="255" label="模板文件">
          <span class="fb-icon fb-icon-template"></span>
          <p>选择框-模板文件</p>
        </li>
        <li i="field" tag="category" type="category" field="INT" len="10" label="栏目">
          <span class="multiselect fb-icon fb-icon-multiselect"></span>
          <p>栏目</p>
        </li>
        <li i="field" tag="multi_category" type="multi_category" field="VARCHAR" len="255" label="多选栏目">
          <span class="multiselect fb-icon fb-icon-multiselect"></span>
          <p>栏目(多选)</p>
        </li>
        <li i="field" tag="image" type="image" field="VARCHAR" len="255" label="图片">
          <span class="fb-icon fb-icon-image"></span>
          <p>图片上传</p>
        </li>
        <li i="field" tag="multi_image" type="multi_image" field="TEXT" label="多图">
          <span class="fb-icon fb-icon-image"></span>
          <p>多图上传</p>
        </li>
        <li i="field" tag="file" type="file" field="VARCHAR" len="255" label="上传">
          <span class="fb-icon fb-icon-fileupload"></span>
          <p>上传文件</p>
        </li>
        <li i="field" tag="multi_file" type="multi_file" field="TEXT" label="批量上传">
          <span class="fb-icon fb-icon-fileupload"></span>
          <p>批量上传</p>
        </li>
        <li i="field" tag="prop" type="prop" field="VARCHAR" len="255" label="属性">
          <span class="fb-icon fb-icon-prop"></span>
          <p>属性(单选)</p>
        </li>
        <li i="field" tag="multi_prop" type="multi_prop" field="VARCHAR" len="255" label="多选属性">
          <span class="fb-icon fb-icon-prop"></span>
          <p>属性(多选)</p>
        </li>
        <li i="field" tag="tag" type="tag" field="VARCHAR" len="255" label="标签" ui-class="span6">
          <span class="fb-icon fb-icon-tag"></span>
          <p>标签</p>
        </li>
        <li i="field" tag="username" type="username" field="VARCHAR" len="255" label="用户名">
          <span class="fb-icon fb-icon-username"></span>
          <p>用户名</p>
        </li>
        <li i="field" tag="nickname" type="nickname" field="VARCHAR" len="255" label="用户昵称">
          <span class="fb-icon fb-icon-username"></span>
          <p>用户昵称</p>
        </li>
        <li i="field" tag="userid" type="userid" field="INT" len="10" label="用户ID">
          <span class="fb-icon fb-icon-userid"></span>
          <p>用户ID</p>
        </li>
        <li i="field" tag="ip" type="ip:hidden" field="VARCHAR" len="255" label="IP地址">
          <span class="fb-icon fb-icon-username"></span>
          <p>IP地址</p>
        </li>
        <li i="field" tag="referer" type="referer:hidden" field="VARCHAR" len="255" label="来路">
          <span class="fb-icon fb-icon-username"></span>
          <p>来路</p>
        </li>

        <li i="field" tag="seccode" type="seccode" len="8" label="验证码">
          <span class="fb-icon fb-icon-url"></span>
          <p>验证码</p>
        </li>
        <div class="clearfix"></div>
      </ul>
      <div class="clearfix"></div>
    </div>
    <div id="fields-tab-addons" class="tab-pane">
      <?php ?>
      <ul>
        <li i="field" tag="textarea" type="multitext" field="MEDIUMTEXT" label="大文本">
          <span class="fb-icon fb-icon-textarea"></span>
          <p>大文本</p>
        </li>
        <li i="field" tag="editor" type="editor" field="MEDIUMTEXT" label="编辑器">
          <span class="fb-icon fb-icon-richtext"></span>
          <p>编辑器</p>
        </li>
        <li i="field" tag="markdown" type="markdown" field="MEDIUMTEXT" label="md编辑器">
          <span class="fb-icon fb-icon-richtext"></span>
          <p>markdown</p>
        </li>
        <span class="help-inline">* 此标签下的字段会独立创建cdata表</span>
        <div class="clearfix"></div>
      </ul>
      <div class="clearfix"></div>
    </div>
  </div>
  <div class="clearfix"></div>
</div>
