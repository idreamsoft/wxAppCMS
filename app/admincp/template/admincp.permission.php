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
<title>iCMS Permission Denied!</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="iCMSdev.com" name="Copyright" />
<link rel="stylesheet" href="./app/admincp/ui/bootstrap/2.3.2/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/bootstrap/2.3.2/css/bootstrap-responsive.min.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/iCMS.css" type="text/css" />
<script type="text/javascript" src="./app/admincp/ui/jquery-1.11.0.min.js"></script>
<style>
body { background-color:#f8f8f8;}
.iCMS-permission { margin: 240px auto 0; width:720px; }
</style>
</head>
<body>
<div class="container">
  <div class="iCMS-permission">
    <div class="alert">
      <h4>Warning!</h4> 您没有<?php echo "[$p]";?>相关权限!
    </div>
  </div>
</div>
</body>
</html>
