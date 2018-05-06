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
    <div class="widget-title"> <span class="icon"> <i class="fa fa-file"></i> </span>
      <h5 class="brs">全站静态</h5>
      <ul class="nav nav-tabs" id="html-tab">
        <li class="active"><a href="javascript:;"><i class="fa fa-floppy-o"></i> <b>静态</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=index"><i class="fa fa-floppy-o"></i> <b>首页</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=category"><i class="fa fa-floppy-o"></i> <b>栏目</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=article"><i class="fa fa-floppy-o"></i> <b>文章</b></a></li>
      </ul>
    </div>
    <div class="widget-content">
      <p>
        <strong>iCMS</strong> 不是很支持全站静态这种模式，推荐使用伪静态方式<br />
        所说的不支持不是指技术上不支持。<br />
        有人说纯静态对搜索引擎比较友好<br />
        百度站长学院 http://zhanzhang.baidu.com/college/documentinfo?id=193&page=6<br />
        已经说明 URL是动态还是静态，对百度没有影响<br />
        而且生成静态期间对服务器性能有很大的影响<br />
        如果您还坚持要全站静态，那请手动生成 首页 栏目 文章
        <hr />
        <strong>iCMS 推荐方式 </strong><br />
        首页静态<br />
        其它全部使用伪静态方式<br />
        如果您的流量很大，可使用nginx的缓存功能。速度不比纯静态差多少<br />
        如果您的流量很大很大很大很大很大很大很大，那您已经是土豪了，还怕没有解决的办法
      </p>
    </div>
  </div>
</div>
<?php admincp::foot();?>
