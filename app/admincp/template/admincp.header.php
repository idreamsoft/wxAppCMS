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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>iCMS Administrator's Control Panel</title>
<meta name="renderer" content="webkit">
<meta name="force-rendering" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta content="iCMSdev.com" name="Copyright" />
<link rel="stylesheet" href="./app/admincp/ui/bootstrap/2.3.2/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/bootstrap/2.3.2/css/datepicker.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/bootstrap/2.3.2/css/bootstrap-switch.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/font-awesome/4.2.0/css/font-awesome.min.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/artDialog/ui-dialog.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/iCMS.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/jquery/uniform-2.1.2.min.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/jquery/chosen.min.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/admincp.css" type="text/css" />

<script src="./app/admincp/ui/jquery-1.11.0.min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/artDialog/dialog-plus-min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/bootstrap/2.3.2/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/bootstrap/2.3.2/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="./app/admincp/ui/bootstrap/2.3.2/js/bootstrap-switch.min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/iCMS.js" type="text/javascript"></script>
<script src="./app/admincp/ui/jquery/migrate-1.2.1.js" type="text/javascript"></script>
<script src="./app/admincp/ui/jquery/scrollUp-1.1.0.min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/jquery/uniform-2.1.2.min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/jquery/chosen.jquery.min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/admincp.js" type="text/javascript"></script>

<script type="text/javascript">
var CSRF_TOKEN = '<?php echo iPHP_WAF_CSRF_TOKEN;?>';
window.iCMS.init({
  API:'<?php echo iPHP_SELF;?>',
  UI:'./app/admincp/ui',
  URL:'<?php echo iCMS_URL;?>',
  PUBLIC:'<?php echo iCMS_PUBLIC_URL;?>',
  DEFTPL:'<?php echo iCMS::$config['template']['desktop']['tpl'];?>',
  COOKIE:'<?php echo iPHP_COOKIE_PRE;?>',
});
$(function(){
	<?php if($_GET['tab']){?>
	var $itab = $("#<?php echo admincp::$APP_NAME; ?><?php echo ($_GET['do']?'-'.$_GET['do']:''); ?>-tab");
	$("li",$itab).removeClass("active");
	$(".tab-pane").removeClass("active").addClass("hide");
	$("a[href ='#<?php echo admincp::$APP_NAME; ?>-<?php echo $_GET['tab']; ?>']",$itab).parent().addClass("active");
	$("#<?php echo admincp::$APP_NAME; ?>-<?php echo $_GET['tab']; ?>").addClass("active").removeClass("hide");
	<?php }?>
  <?php if($body_class=='sidebar-mini'){?>
    mini_tip();
  <?php }?>
})
</script>
</head>
<body class="<?php echo $body_class; ?>">
<iframe class="hide" id="iPHP_FRAME" name="iPHP_FRAME"></iframe>
<div id="iCMS-MODAL" class="modal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 class="modal-title">iCMS 提示</h3>
  </div>
  <div class="modal-body">
    <p><img src="./app/admincp/ui/img/loading.gif" /></p>
  </div>
</div>
