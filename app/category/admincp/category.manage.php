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
iPHP::set_cookie(admincp::$APP_NAME.'_tabs',admincp::$APP_DO);
admincp::head();
?>
<?php if(admincp::$APP_DO=='tree'){ ?>
<link rel="stylesheet" href="./app/admincp/ui/jquery/treeview-0.1.0.css" type="text/css" />
<script type="text/javascript" src="./app/admincp/ui/template-3.0.js"></script>
<script type="text/javascript" src="./app/admincp/ui/jquery/jquery-ui.min.js"></script>
<script type="text/javascript" src="./app/admincp/ui/jquery/treeview-0.1.0.js"></script>
<script type="text/javascript" src="./app/admincp/ui/jquery/treeview-0.1.0.async.js"></script>
<script id="tree_li" type="text/html">
<div class="row-fluid status{{status}}">
    <span class="sortnum">
        <input type="text" cid="{{cid}}" name="sortnum[{{cid}}]" value="{{sortnum}}" style="width:32px;"/>
    </span>
    <span class="name">
        <input class="span2" {{if rootid=="0"}}style="font-weight:bold"{{/if}} type="text" name="name[{{cid}}]" value="{{name}}"/>
        {{if status=="0"}}
        <i class="fa fa-eye-slash" title="隐藏<?php echo $this->category_name;?>"></i>
        {{/if}}
        <span class="label label-success">cid:<a href="{{href}}" target="_blank">{{cid}}</a></span>
        {{if url}}
        <span class="label label-warning">∞</span>
        {{/if}}
        {{if pid}}
        <span class="label label-inverse">pid:{{pid}}</span>
        {{/if}}
        <?php if($this->appid===null){?>
          <span class="label label-inverse">appid:{{appid}}</span>
        <?php }?>
        {{if mode && domain}}
        <span class="label label-important">绑定域名</span>
        {{/if}}
        <span class="label label-info">{{count}}条记录</span>
        {{if creator}}
        <span class="label">创建者:{{creator}}</span>
        {{/if}}
    </span>
    <span class="operation">
        {{if CP_ADD}}
        <a href="<?php echo $this->category_uri;?>&do=add&rootid={{cid}}" class="btn btn-small"><i class="fa fa-plus-square"></i> 子<?php echo $this->category_name;?></a>
        {{/if}}
        <a href="{{href}}" target="_blank" class="btn btn-small"><i class="fa fa-link"></i> 访问</a>
        <a href="<?php echo __ADMINCP__;?>=<?php echo $this->_app;?>&do=add&<?php echo $this->_app_cid;?>={{cid}}" class="btn btn-small"><i class="fa fa-edit"></i> 添加<?php echo $this->_app_name;?></a>
        <a href="<?php echo __ADMINCP__;?>=<?php echo $this->_app;?>&<?php echo $this->_app_cid;?>={{cid}}&sub=on" class="btn btn-small"><i class="fa fa-list-alt"></i> <?php echo $this->_app_name;?>管理</a>
        {{if CP_EDIT}}
        <a href="<?php echo $this->category_uri;?>&do=copy&cid={{cid}}" target="iPHP_FRAME"  class="btn btn-small"><i class="fa fa-clipboard"></i> 克隆</a>
        <a href="<?php echo $this->category_uri;?>&do=add&cid={{cid}}" title="编辑<?php echo $this->category_name;?>设置"  class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
        {{/if}}
        {{if CP_DEL}}
        <a href="<?php echo $this->category_furi;?>&do=del&cid={{cid}}" class="btn btn-small" onClick="return confirm(\'确定要删除此<?php echo $this->category_name;?>?\');" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 删除</a>
        {{/if}}
    </span>
</div>
</script>
<script type="text/javascript">
var upordurl="<?php echo $this->category_uri; ?>&do=updateorder";
$(function(){
    $("#tree").treeview({
      tpl:'tree_li',
      url:'<?php echo $this->category_uri; ?>&do=ajaxtree&expanded=<?php echo admincp::$APP_DO=='all'?'1':'0';?>',
      collapsed: false,
      sortable: true,
      animated: "medium",
      control:"#treecontrol",
    });
});
</script>
<?php } ?>
<?php if(admincp::$APP_DO=='list'){ ?>
<script type="text/javascript">
$(function(){
<?php if($_GET['st']){ ?>
iCMS.select('st',"<?php echo $_GET['st'] ; ?>");
<?php } ?>
<?php if($_GET['orderby']){ ?>
iCMS.select('orderby',"<?php echo $_GET['orderby'] ; ?>");
<?php } ?>
<?php if(isset($_GET['rootid']) &&$_GET['rootid']!='-1') {  ?>
iCMS.select('rootid',"<?php echo $_GET['rootid'] ; ?>");
<?php } ?>
  $("#<?php echo APP_FORMID;?>").batch({
    move:function(){
      return $("#mergeBatch").clone(true);
    }
  });
});
</script>
<?php } ?>
<div class="iCMS-container">
  <?php if(admincp::$APP_DO=='list'){ ?>
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
      <div class="pull-right">
        <a style="margin: 10px;" class="btn btn-success btn-mini" href="<?php echo $this->category_furi; ?>&do=cache" target="iPHP_FRAME"><i class="fa fa-refresh"></i> 更新缓存</a>
      </div>
    </div>
    <div class="widget-content">
      <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
        <input type="hidden" name="appid" value="<?php echo $this->appid;?>" />
        <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
        <input type="hidden" name="do" value="<?php echo admincp::$APP_DO;?>" />
        <div class="input-prepend"> <span class="add-on">父<?php echo $this->category_name;?></span>
          <select name="rootid" id="rootid" class="chosen-select" style="width: 230px;">
            <option value="-1">所有<?php echo $this->category_name;?></option>
            <option value="0">=====顶级<?php echo $this->category_name;?>=====</option>
            <?php echo $category_select = category::priv('s')->select(0,0,1,true) ; ?>
          </select></div>
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend">
          <span class="add-on">排序</span>
          <select name="orderby" id="orderby" class="span2 chosen-select">
            <option value=""></option>
            <optgroup label="降序"><?php echo $orderby_option['DESC'];?></optgroup>
            <optgroup label="升序"><?php echo $orderby_option['ASC'];?></optgroup>
          </select>
        </div>
        <div class="input-prepend"> <span class="add-on">查找方式</span>
          <select name="st" id="st" class="chosen-select" style="width:120px;">
            <option value="name"><?php echo $this->category_name;?>名</option>
            <option value="cid">CID</option>
            <option value="tkd">标题/关键字/简介</option>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <?php } ?>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title">
      <span class="icon" style="padding: 8px 12px 5px 12px;">
        <a href="<?php echo $this->category_uri; ?>&do=add" title="添加<?php echo $this->category_name;?>"><i class="fa fa-plus-square"></i></a>
      </span>
      <?php if(admincp::$APP_NAME=='category'){?>
      <span class="icon"><?php echo $apps['name'];?></span>
      <?php } ?>
      <ul class="nav nav-tabs" id="category-tab">
        <li<?php if(admincp::$APP_DO=='tree'){ ?> class="active" <?php } ?>><a href="<?php echo $this->category_uri; ?>&do=tree"><i class="fa fa-tasks"></i> 树模式</a></li>
        <li<?php if(admincp::$APP_DO=='list'){ ?> class="active" <?php } ?>><a href="<?php echo $this->category_uri; ?>&do=list"><i class="fa fa-list"></i> 列表模式</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <?php if(admincp::$APP_DO=='tree'){ ?>
      <form action="<?php echo $this->category_furi; ?>&do=update" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <div id="category-list" class="tab-content">
          <div id="category-tree" class="row-fluid category-treeview">
            <ul id="tree"><p id="tree-loading"><img src="./app/admincp/ui/img/ajax_loader.gif" /></p></ul>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
          <a class="btn btn-inverse" href="<?php echo $this->category_furi; ?>&do=cache" target="iPHP_FRAME"><i class="fa fa-refresh"></i> 更新缓存</a>
          <div id="treecontrol">
            <a href="javascript:;" class="btn btn-info"><i class="fa fa-angle-double-up"></i> 全部折叠</a>
            <a href="javascript:;" class="btn btn-info"><i class="fa fa-angle-double-down"></i> 全部展开</a>
          </div>
          <a class="btn btn-success" href="https://www.icmsdev.com/docs/app-rewrite.html" target="_blank"><i class="fa fa-question-circle"></i> 伪静态规则</a>
        </div>
      </form>
      <?php } ?>
      <?php if(admincp::$APP_DO=='list'){ ?>
      <form action="<?php echo $this->category_furi; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th style="width:10px;"><i class="fa fa-arrows-v"></i></th>
              <th style="width:24px;">CID</th>
              <th><?php echo $this->category_name;?></th>
              <th>目录</th>
              <th>父<?php echo $this->category_name;?></th>
              <?php if(admincp::$APP_NAME=='category'){?><th style="width:40px;">APPID</th><?php } ?>
              <th style="width:40px;">记录数</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $rootidArray = iSQL::values($rs,'rootid','array',null);
              $rootidArray && $root_data = (array) category::get($rootidArray);
              for($i=0;$i<$_count;$i++){
                $root = $root_data[$rs[$i]['rootid']];
            ?>
            <tr id="<?php echo $rs[$i]['cid'] ; ?>" class="status<?php echo $rs[$i]['status'] ; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['cid'] ; ?>" /></td>
              <td><a href="<?php echo iURL::get('category',$rs[$i])->href;?>" target="_blank"><?php echo $rs[$i]['cid'] ; ?></a></td>
              <td><input <?php if($rs[$i]['rootid']=="0"){ ?> style="font-weight:bold"<?php } ?> class="span2" type="text" name="name[<?php echo $rs[$i]['cid'] ; ?>]" value="<?php echo $rs[$i]['name'] ; ?>">
                <?php if(!$rs[$i]['status']){ ?>
                <i class="fa fa-eye-slash" title="隐藏<?php echo $this->category_name;?>"></i>
                <?php } ?>
                <?php if($rs[$i]['pid']){
                  propAdmincp::flag($rs[$i]['pid'],$propArray,APP_DOURI.'&pid={PID}&'.$uri);
                } ?>
              </td>
              <td><input type="text" class="span3" name="dir[<?php echo $rs[$i]['cid'] ; ?>]" value="<?php echo $rs[$i]['dir'] ; ?>" /></td>
              <td><a href="<?php echo APP_DOURI; ?>&rootid=<?php echo $rs[$i]['rootid'] ; ?>"><?php echo  $root?$root->name:'顶级'.$this->category_name ; ?></a></td>
              <?php if(admincp::$APP_NAME=='category'){?>
              <td><a href="<?php echo APP_DOURI; ?>&appid=<?php echo $rs[$i]['appid'] ; ?>"><?php echo $rs[$i]['appid'] ; ?></a></td>
              <?php } ?>
              <td><?php echo $rs[$i]['count'] ; ?></td>
              <td>
                <?php if(category::check_priv($rs[$i]['cid'],'ca') ){?>
                <a href="<?php echo __ADMINCP__;?>=<?php echo $this->_app;?>&do=add&<?php echo $this->_app_cid;?>=<?php echo $rs[$i]['cid'] ;?>" class="btn btn-small"><i class="fa fa-edit"></i> 添加<?php echo $this->_app_name;?></a>
                <?php } ?>
                <?php if(category::check_priv($rs[$i]['cid'],'cs') ){?>
                <a href="<?php echo __ADMINCP__;?>=<?php echo $this->_app;?>&<?php echo $this->_app_cid;?>=<?php echo $rs[$i]['cid'] ;?>&sub=on" class="btn btn-small"><i class="fa fa-list-alt"></i> <?php echo $this->_app_name;?>管理</a>
                <?php } ?>
                <?php if(category::check_priv($rs[$i]['cid'],'a') ){?>
                <a href="<?php echo $this->category_uri; ?>&do=add&rootid=<?php echo $rs[$i]['cid'] ; ?>" class="btn btn-small"><i class="fa fa-plus-square"></i> 子<?php echo $this->category_name;?></a>
                <?php } ?>
                <?php if(category::check_priv($rs[$i]['cid'],'e') ){?>
                <a href="<?php echo $this->category_uri; ?>&do=copy&cid=<?php echo $rs[$i]['cid'] ; ?>" target="iPHP_FRAME" class="btn btn-small"><i class="fa fa-clipboard"></i> 克隆</a>
                <a href="<?php echo $this->category_uri; ?>&do=add&cid=<?php echo $rs[$i]['cid'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a>
                <?php } ?>
                <?php if(category::check_priv($rs[$i]['cid'],'d') ){?>
                <a href="<?php echo $this->category_furi; ?>&do=del&cid=<?php echo $rs[$i]['cid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a></td>
                <?php } ?>
            </tr>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="8"><div class="pagination pagination-right" style="float:right;"><?php echo iUI::$pagenav ; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="merge"><i class="fa fa-random"></i> 合并<?php echo $this->category_name;?></a></li>
                      <li><a data-toggle="batch" data-action="move"><i class="fa fa-fighter-jet"></i> 移动<?php echo $this->category_name;?></a></li>
                      <li><a data-toggle="batch" data-action="recount"><i class="fa fa-refresh"></i> 更新记录数</a></li>
                      <li><a data-toggle="batch" data-action="mkdir"><i class="fa fa-gavel"></i> 重建目录</a></li>
                      <li><a data-toggle="batch" data-action="dir"><i class="fa fa-gavel"></i> 更改目录</a></li>
                      <li><a data-toggle="batch" data-action="status"><i class="fa fa-square"></i> <?php echo $this->category_name;?>状态</a></li>
                      <?php echo $this->batchbtn();?>
                      <li class="divider"></li>
                      <li><a data-toggle="batch" data-action="update"><i class="fa fa-check"></i> 更新</a></li>
                      <li class="divider"></li>
                      <li><a data-toggle="batch" data-action="dels"><i class="fa fa-trash-o"></i> 删除</a></li>
                    </ul>
                  </div>
                </div></td>
            </tr>
          </tfoot>
        </table>
      </form>
      <div class='iCMS-batch'>
        <div id="modeBatch">
          <div class="input-prepend"> <span class="add-on">访问模式</span>
            <select name="mode">
              <option value="0">动态</option>
              <option value="1">静态</option>
              <option value="2">伪静态</option>
            </select>
          </div>
        </div>
        <div id="dirBatch">
          <div class="input-prepend input-append"><span class="add-on">目录</span>
            <input type="text" class="span2" name="mdir"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend input-append"><span class="add-on">前追加
            <input type="radio" name="pattern" value="addtobefore"/>
            </span><span class="add-on">后追加
            <input type="radio" name="pattern" value="addtoafter"/>
            </span>
            <span class="add-on">替换
            <input type="radio" name="pattern" value="replace" checked/>
            </span></div>
        </div>
        <div id="mergeBatch">
          <div class="input-prepend"> <span class="add-on">请选择目标<?php echo $this->category_name;?></span>
            <select name="tocid" class="span3">
              <option value="0">===顶级<?php echo $this->category_name;?>===</option>
              <?php echo $category_select;?>
            </select>
          </div>
        </div>
        <div id="statusBatch">
          <div class="switch" data-on-label="显示" data-off-label="隐藏">
            <input type="checkbox" data-type="switch" name="status" id="status"/>
          </div>
        </div>
        <div id="ruleBatch" style="width: 560px;height:260px;text-align: left;">
          <?php include admincp::view('category.rule',$this->_view_tpl_dir);?>
          <div class="clearfloat mb10"></div>
        </div>
        <div id="templateBatch" style="height:180px;">
          <?php include admincp::view('category.template',$this->_view_tpl_dir);?>
          <div class="clearfloat mb10"></div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php admincp::foot();?>
