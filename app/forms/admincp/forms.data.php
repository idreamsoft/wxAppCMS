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
  iCMS.select('sfield',"<?php echo $_GET['sfield']; ?>");
  iCMS.select('pattern',"<?php echo $_GET['pattern']; ?>");
  $("#<?php echo APP_FORMID;?>").batch();
});
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title">
      <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5 class="brs">搜索</h5>
      <div class="input-prepend" style="margin-top: 3px; margin-left: 5px;">
        <span class="add-on">表单</span>
        <select name="sssfid" id="sssfid" class="chosen-select span4"
        onchange="window.location.href='<?php echo APP_DOURI; ?>&fid='+this.value"
        data-placeholder="== 请选择表单 ==">
          <?php echo $this->select();?>
        </select>
        <script>
        $(function(){
         iCMS.select('sssfid',"<?php echo (int)$_GET['fid'] ; ?>");
        })
        </script>
      </div>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <input type="hidden" name="do" value="data" />
        <input type="hidden" name="fid" value="<?php echo $this->fid;?>" />
        <div class="input-prepend input-append">
          <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span>
        </div>
        <div class="input-prepend"> <span class="add-on">查找字段</span>
          <select name="sfield" id="sfield" class="span3 chosen-select">
            <option value="">所有字段</option>
            <?php foreach ((array)$fields as $fi => $field) {?>
            <option value="<?php echo $field['id']; ?>"><?php echo $field['label']; ?>[<?php echo $field['field']; ?>]</option>
            <?php } ?>
          </select>
        </div>
        <div class="input-prepend">
          <span class="add-on">查找方式</span>
          <select name="pattern" id="pattern" class="chosen-select" style="width:120px;">
            <option></option>
            <option value="=">等于</option>
            <option value="!=">不等于</option>
            <option value=">">大于</option>
            <option value="<">小于</option>
            <option value="like">like</option>
          </select>
        </div>
        <div class="input-prepend input-append">
          <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
        <span class="help-inline">默认只查找 varchar 类型字段</span>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title">
      <span class="icon">
        <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <input type="hidden" name="fid" value="<?php echo $this->fid;?>" />
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th class="span1"><?php echo strtoupper($primary); ?></th>
              <th>表单内容</th>
            </tr>
          </thead>
          <tbody>
          <?php
            foreach ((array)$rs as $key => $value) {
              $id = $value[$primary];
              if($b[$id] && is_array($b[$id])){
                $value+=$b[$id];
              }
          ?>
            <tr id="id<?php echo $id; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $id ; ?>" /></td>
              <td><?php echo $id ; ?></td>
              <td>
                <table class="table table-bordered">
                  <tbody>
                    <?php foreach ($fields as $fi => $field) {?>
                    <tr>
                      <td class="span3"><?php echo $field['label'] ; ?></td>
                      <td>
                        <?php
                          $vars = former::field_output($value[$field['id']],$field);
                          // is_array($vars) && $vars = implode(',', $vars);
                          print_r($vars);
                        ?>
                      </td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
                <div class="clearfloat mb5"></div>
                <a href="<?php echo APP_URI; ?>&do=submit&fid=<?php echo $this->fid ; ?>&id=<?php echo $id ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                <a href="<?php echo APP_FURI; ?>&do=delete&fid=<?php echo $this->fid ; ?>&id=<?php echo $id ; ?>" target="iPHP_FRAME" class="del btn btn-small btn-danger" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a>
              </td>
            </tr>
            <?php }  ?>
          </tbody>
          <tr>
            <td colspan="<?php echo count($list_fields)+3;?>">
              <div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
              <div class="input-prepend input-append mt20">
                <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                </span>
                <div class="btn-group dropup" id="iCMS-batch">
                  <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a>
                  <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                    <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu">
                    <li class="divider"></li>
                    <li><a data-toggle="batch" data-action="data-dels"><i class="fa fa-trash-o"></i> 删除</a></li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php admincp::foot();?>
