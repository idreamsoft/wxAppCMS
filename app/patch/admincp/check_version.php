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
<script type="text/javascript">
$(function(){
    window.setTimeout(function(){
        $.getJSON('<?php echo __ADMINCP__;?>=patch&do=version&t=<?php echo time(); ?>',
            function(o){
            $('#iCMS_RELEASE').text(o.release);
            $('#iCMS_GIT').text(o.git);
            }
        );
    },1000);
});
</script>
