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
<div id="counter"></div>
<script src="./app/admincp/ui/jquery/jquery.timer.js" type="text/javascript"></script>
<style>
.app_list_desc{font-size: 14px;color: #666;}
.nopadding .tab-content{padding: 0px;}
.store-wrap{}

.store-card { float: left; margin: 0 8px 16px; width: 48.5%; width: calc(50% - 8px); background-color: #fff; border: 1px solid #ddd; box-sizing: border-box }
.store-card:nth-child(odd) { clear: both; margin-left: 0 }
.store-card:nth-child(even) { margin-right: 0 }
@media screen and (min-width:1600px) {
  .store-card { width: 30%; width: calc(33.1% - 8px) }
  .store-card:nth-child(odd) { clear: none; margin-left: 8px }
  .store-card:nth-child(even) { margin-right: 8px }
  .store-card:nth-child(3n+1) { clear: both; margin-left: 0 }
  .store-card:nth-child(3n) { margin-right: 0 }
}
.store-icon { position: absolute; top: 20px; left: 20px; width: 128px; height: 128px; margin: 0 20px 20px 0 }
.store-card h3 { margin: 0 0 12px; font-size: 18px; line-height: 1.3 }
.store-card .desc, .store-card .name { margin-left: 148px; margin-right: 120px }
.store-card .action-links { position: absolute; top: 20px; right: 20px; width: 130px }
.store-card-top { position: relative; padding: 20px 20px 10px; min-height: 135px }
.store-action-buttons { clear: right; float: right; margin-left: 2em; margin-bottom: 1em; text-align: right }
.store-action-buttons li { margin-bottom: 10px }
.store-card-bottom { clear: both; padding: 12px 20px; background-color: #fafafa; border-top: 1px solid #ddd; overflow: hidden }
.store-card-bottom .star-rating { display: inline; color: #ffb900 }
.store-card-update-failed .update-now { font-weight: 600 }
.store-card-update-failed .notice-error { margin: 0; padding-left: 16px; box-shadow: 0 -1px 0 #ddd }
.store-card-update-failed .store-card-bottom { display: none }
.store-card .column-rating { line-height: 23px }
.store-card .column-rating, .store-card .column-updated { margin-bottom: 4px }
.store-card .column-downloaded, .store-card .column-rating { float: left; clear: left; max-width: 180px }
.store-card .column-compatibility, .store-card .column-updated { text-align: right; float: right; clear: right; width: 65%; width: calc(100% - 180px) }
.premium .label { font-weight: normal; padding: 4px 5px; font-size: 14px; margin-bottom: 3px;}
</style>
<script type="text/javascript">
var tipDialog;
$(function(){
  $("#<?php echo APP_FORMID;?>").batch();
  $(".install-btn,.update-btn").click(function(event) {
    tipDialog = iCMS.success("数据下载中...请稍候!",false,10000000);
  });
});

var pay_notify_timer,clear_timer;
function pay_notify (j,d) {
  clear_timer = false;
  pay_notify_timer = $.timer(function(){
    pay_notify_timer.stop();
    $.getJSON("<?php echo APP_URI;?>&do=pay_notify",{authkey:j[0],sid:j[1]},function(o){
          // console.log(o);
          if(o.code=="1" && o.url && o.t){
            iCMS.success("数据下载中...请稍候!",false,10000000);
            $("#iPHP_FRAME").attr("src","<?php echo APP_URI;?>&do=<?php echo admincp::$APP_DO; ?>_premium_install&url="+o.url+'&transaction_id='+o.transaction_id+'&sid='+j[1])
            d.close().remove();
            return;
          }else if(o.code=="0"){
            //等待支付
            if(!clear_timer){
              pay_notify_timer.play();
            }
          }else {
            alert(o.msg);
            window.location.reload();
          }
    });
  });
  pay_notify_timer.set({ time : 1000, autostart : true });
  d.addEventListener('close', function(){
    clear_pay_notify_timer();
  });
}
function clear_pay_notify_timer() {
  tipDialog.close().remove();
  clear_timer = true;
  pay_notify_timer.stop();
}
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
    <h5>搜索</h5>
  </div>
  <div class="widget-content">
    <form action="<?php echo iPHP_SELF ; ?>" method="get" class="form-inline">
      <input type="hidden" name="app" value="<?php echo admincp::$APP_NAME;?>" />
      <div class="input-prepend input-append">
        <span class="add-on">每页</span>
        <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
        <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append">
          <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title">
      <h5 class="brs"><i class="fa fa-bank"></i> <span><?php echo $title;?>市场</span></h5>
      <ul class="nav nav-tabs" id="config-tab">
          <li <?php if(!isset($_GET['premium'])) echo 'class="active"';?>><a href="<?php echo admincp::uri(null,$uriArray); ?>">全部</a></li>
          <li <?php if($_GET['premium']=='0') echo 'class="active"';?>><a href="<?php echo admincp::uri("premium=0",$uriArray); ?>">免费<?php echo $title;?></a></li>
          <li <?php if($_GET['premium']=='1') echo 'class="active"';?>><a href="<?php echo admincp::uri("premium=1",$uriArray); ?>">付费<?php echo $title;?></a></li>
      </ul>
    </div>
    <div class="widget-content store-wrap">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
              <?php
              foreach ((array)$dataArray as $key => $value) {
                $is_update = false;
                $sid       = $value['id'];
                $appconf   = $storeArray[$sid];
                if($appconf){
                  version_compare($value['version'],$appconf['version'],'>')        && $is_update = true;
                  ($appconf['git_time'] && $value['git_time']>$appconf['git_time']) && $is_update = true;
                  ($appconf['git_sha'] && $value['git_sha']!=$appconf['git_sha'])   && $is_update = true;
                }
              ?>
            <div id="store-<?php echo $value['app'];?>" class="store-card">
              <div class="store-card-top">
                  <div class="name column-name">
                        <h3>
                          <a href="<?php echo $value['url'];?>?modal"
                          title="<?php echo $title;?>信息"
                          data-toggle="modal" data-target="#iCMS-MODAL" data-meta='{"width":"700px","height":"640px"}'>
                            <?php echo $value['title'];?>
                            <img src="<?php echo $value['pic']['url'];?>" class="store-icon" alt="">
                          </a>
                        </h3>
                  </div>
                  <div class="action-links">
                      <ul class="store-action-buttons">
                          <li>
                            <?php if($appconf){?>
                            <?php if($is_update){?>
                            <a href="<?php echo APP_FURI; ?>&do=<?php echo admincp::$APP_DO; ?>_update&sid=<?php echo $sid;?>&id=<?php echo $appconf['appid'];?>"
                              target="iPHP_FRAME" class="btn btn-success update-btn">
                              <i class="fa fa-repeat"></i> 现在更新
                            </a>
                            <?php }else{ ?>
                              <a disabled="disabled" href="javascript:;" class="btn btn-default"><i class="fa fa-repeat"></i> 暂无更新</a>
                            <?php } ?>
                            <p class="clearfix mt5"></p>
                            <a href="<?php echo APP_FURI; ?>&do=<?php echo admincp::$APP_DO; ?>_uninstall&sid=<?php echo $sid;?>&id=<?php echo $appconf['appid'];?>"
                              target="iPHP_FRAME" class="btn btn-danger tip-top"
                              <?php if($value['type']){?>
                              title="删除此模板文件夹下的所有文件"
                              onclick="return confirm('确定要删除此模板?');"
                              <?php }else{ ?>
                              title="卸载应用会清除应用所有数据！"
                              onclick="return confirm('卸载应用会清除应用所有数据！\n卸载应用会清除应用所有数据！\n卸载应用会清除应用所有数据！\n确定要卸载?\n确定要卸载?\n确定要卸载?');"
                              <?php } ?>
                            >
                              <i class="fa fa-trash-o"></i> 卸载<?php echo $title;?>
                            </a>
                          <?php }else{ ?>
                          <a href="<?php echo APP_FURI; ?>&do=<?php echo admincp::$APP_DO; ?>_install&sid=<?php echo $sid;?>"
                            target="iPHP_FRAME" class="btn btn-primary install-btn">
                            <i class="fa fa-download"></i>
                            <?php if($value['premium']){?>
                            付费安装
                            <?php }else{ ?>
                            现在安装
                            <?php } ?>
                          </a>
                          <?php } ?>
                          </li>
                          <?php if($value['premium']){?>
                          <li class="premium">
                            <?php if($value['coupon']){?>
                            <span class="label label-inverse"><del>原价：<i class="fa fa-rmb"></i> <?php echo $value['price'];?></del></span>
                            <span class="label label-warning">优惠价：<i class="fa fa-rmb"></i> <?php echo $value['coupon'];?></span>
                            <?php }else{ ?>
                            <span class="label label-success">价格：<i class="fa fa-rmb"></i> <?php echo $value['price'];?></span>
                            <?php } ?>
                            </li>
                          <?php } ?>
                          <li>
                          <a href="<?php echo $value['url'];?>?modal"
                          title="<?php echo $title;?>信息"
                          data-toggle="modal" data-target="#iCMS-MODAL" data-meta='{"width":"700px","height":"640px"}'>
                          更多详情</a>
                          </li>
                      </ul>
                  </div>
                  <div class="desc column-description">
                      <p><?php echo csubstr($value['description'],40,'...');?></p>
                      <p class="authors">
                        <cite>由<a href="<?php echo $value['website']?:'javascript:;';?>" target="_blank"><?php echo $value['author'];?></a>提供</cite>
                        <?php if($appconf && $value['qq']){?>
                        <cite>QQ:<?php echo $value['qq'];?></cite>
                        <?php } ?>
                      </p>
                  </div>
              </div>
              <div class="store-card-bottom">
                  <div class="vers column-rating">
                      <div class="star-rating">
                        <?php for ($i=0; $i < $value['star']; $i++) { ?>
                          <i class="fa fa-star"></i>
                        <?php } ?>
                      </div>
                  </div>
                  <div class="column-updated">
                      <?php if($appconf['git_time']){?>
                      <strong>安装时间：</strong> <?php echo format_date($appconf['git_time'],'Y-m-d H:i');?>
                      <div class="clearfix"></div>
                      <?php } ?>
                      <strong>最近更新：</strong> <?php echo format_date($value['git_time'],'Y-m-d H:i');?>
                  </div>
                  <div class="column-downloaded"><?php echo $value['install'];?>个安装</div>
                  <div class="column-compatibility">
                      <?php $compatible ="该{$title}<strong>兼容</strong>于您当前使用的".iPHP_APP."版本"; ?>
                      <?php if($value['iCMS_VERSION']){?>
                        <?php if(version_compare(substr(iCMS_VERSION,1),$value['iCMS_VERSION'],'>=')){?>
                        <i class="fa fa-check"></i>
                        <span class="compatibility-compatible"><?php echo $compatible;?></span>
                        <?php }else{ ?>
                        <i class="fa fa-times"></i>
                        <span class="compatibility-untested">该<?php echo $title;?><strong>要求</strong>在<?php echo iPHP_APP?> v<?php echo $value['iCMS_VERSION'];?>及以上版本使用</span>
                        <?php } ?>
                      <?php }else{ ?>
                      <i class="fa fa-check"></i>
                      <span class="compatibility-compatible"><?php echo $compatible;?></span>
                      <?php } ?>
                  </div>
                  <?php if($value['iCMS_GIT_TIME']){?>
                  <div class="column-compatibility">
                        <?php if(GIT_TIME<$value['iCMS_GIT_TIME']){?>
                          <span class="compatibility-untested"><i class="fa fa-times"></i><strong>要求</strong>[git:<?php echo get_date($value['iCMS_GIT_TIME'],'Y-m-d H:i');?>]及之后的开发版本</span>
                        <?php } ?>
                  </div>
                  <?php } ?>
              </div>
            </div>
              <?php } ?>
      </form>
      <div class="clearfloat mb10"></div>
    </div>
  </div>
<style>
.demo{font-size: 12px;}
</style>

<?php admincp::foot();?>
