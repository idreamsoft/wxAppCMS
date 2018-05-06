<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class publicFunc{
	public static function public_ui($vars=null){
		iView::assign("public",$vars);
		iView::display("iCMS://public.ui.htm");
	}
	public static function public_seccode($vars=null){
		echo publicApp::seccode();
	}
	public static function public_crontab($vars=null){
		$url = iURL::make('app=public&do=crontab','router::api');
		$html = '<img src="'.$url.'" style="display: none;" />';
		if($vars===true){
			return $html;
		}
		echo $html;
	}
	public static function public_qrcode($vars=null){
		if($vars['base64']){
			echo publicApp::qrcode_base64($vars['data']);
		}else{
			echo publicApp::qrcode_url($vars['data']);
		}
	}
}
