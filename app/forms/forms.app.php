<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2017 iCMSdev.com. All rights reserved.
*
* @author icmsdev <master@icmsdev.com>
* @site https://www.icmsdev.com
* @licence https://www.icmsdev.com/LICENSE.html
*/
class formsApp {
    public $methods = array('iCMS','save');
    public function do_iCMS(){
        $fid = (int) $_GET['id'];
        $this->forms($fid);
    }
    public function API_iCMS(){
        $this->do_iCMS();
    }
    public function ACTION_save(){
        $fid       = (int) $_POST['fid'];
        $signature = $_POST['signature'];

        $vendor = iPHP::vendor('Token');
        $vendor->prefix = 'form_'.$fid.'_';

        list($_fid,$token,$timestamp,$nonce) = explode("#", auth_decode($signature));
        $_signature = $vendor->signature($token);
        if($_fid==$fid && $_signature==$signature){
            $active = true;
            $forms  = forms::get($fid);
            if(empty($forms)||empty($forms['status'])){
                $array = iUI::code(0,array('forms:not_found_fid',$fid),null,'array');
                $active = false;
            }
            if(empty($forms['config']['enable'])){
                $array = iUI::code(0,'forms:!enable',null,'array');
                $active = false;
            }
            if($active){
                $formsAdmincp = new formsAdmincp();
                $formsAdmincp->do_savedata(false);
                $array = iUI::code(1,$forms['config']['success'],null,'array');
                former::$error && $array = former::$error;
            }
            $vendor->signature($token,'DELETE');
        }else{
            $array = iUI::code(0,'forms:error',null,'array');
        }

        if(iHttp::is_ajax()){
            echo json_encode($array);
        }else{
            if ($array['code']){
                iUI::success($array['msg']);
            }else{
                iUI::alert($array['msg']);
            }
        }
    }

    public function forms($fid,$tpl = true){
        $forms = forms::get($fid);

        if(empty($forms)||empty($forms['status'])){
            iPHP::error_404(array('forms:not_found_fid',$fid), 10001);
        }
        $forms = $this->value($forms);

        return appsApp::render($forms,$tpl,'forms');
    }
    public static function value($value,$flag=false){
        $flag && $value = apps::item($value);

        $value['fieldArray']   = former::fields($value['fields']);
        $value['action']       = iURL::router('forms');
        $value['url']          = iURL::router(array('forms:id',$value['id']));
        $value['iurl']         = iDevice::urls(array('href'=>$value['url']));
        $value['iurl']['href'] = $value['url'];
        $value['result']       = iURL::router(array('forms:result',$value['id']));
        $value['link']         = '<a href="'.$value['url'].'" class="forms" target="_blank">'.$value['title'].'</a>';
        $value['pic']          = filesApp::get_pic($value['pic']);
        $value['layout_id']    = "former_".$value['id'];

        return $value;
    }
}
