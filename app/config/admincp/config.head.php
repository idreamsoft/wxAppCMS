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
    <div class="widget-title"> <span class="icon"> <i class="fa fa-cog"></i> </span>
      <h5><?php echo $title;?></h5>
    </div>
    <div class="widget-content nopadding iCMS-config">
      <form action="<?php echo APP_FURI; ?>&do=save_<?php echo $action;?>" method="post" class="form-inline" id="iCMS-config" target="iPHP_FRAME">
        <div id="config" class="tab-content">
          <div id="config-content" class="tab-pane active">
