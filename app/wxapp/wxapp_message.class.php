<?php

class wxapp_message {
    /**
     * [send_template_message description]
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public static function send($param=null){
        // $param = array(
        //     'touser'=>'oHSS05FvcSO1_zZW-twiS4rl0YTQ',
        //     'template_id'=>'aDVFzEL5oTXg6u2pPp49s_XLXLZhwDb7HStaCT9dc-Q',
        //     'page'=>'index/index',
        //     'form_id'=>'wx200853228808172209ec642f1249381879',
        //     'data'=>array(
        //         'keyword1'=> array('value'=>'打赏人'),
        //         'keyword2'=> array('value'=>'打赏金额'),
        //         'keyword3'=> array('value'=>'打赏时间'),
        //         'keyword4'=> array('value'=>'打赏来源'),
        //         'keyword5'=> array('value'=>'留言'),
        //     ),
        //     'emphasis_keyword'=>'keyword2.DATA'
        // );
        $param['appid'] && wxapp::set_app($param['appid']);
        wxapp_api::weixin_init();

        if(empty($param['formid']) && $param['openid']){
            $data = self::get_formid($param['openid'],'openid');
            empty($param['formid']) && $param['formid'] = $data->formid;
            empty($param['appid']) && $param['appid']  = $data->appid;
        }
        if(empty($param['formid']) && $param['userid']){
            $data = self::get_formid($param['userid'],'userid');
            $param['formid'] = $data->formid;
            empty($param['openid']) && $param['openid'] = $data->openid;
            empty($param['appid']) && $param['appid'] = $data->appid;
        }

        if(empty($param['openid'])||empty($param['formid'])){
            if(iPHP_DEBUG){
                if(iPHP_SHELL){
                    empty($param['openid']) && print("openid is missing.");
                    empty($param['formid']) && print("formid is missing.");
                    return false;
                }else{
                    empty($param['openid']) && trigger_error("openid is missing.",E_USER_ERROR);
                    empty($param['formid']) && trigger_error("formid is missing.",E_USER_ERROR);
                }
            }else{
                return false;
            }
        }

// iUtils::LOG($param,'wxapp_message.send.param');

        $url      = weixin::url('message/wxopen/template/send');
        $json     = self::create($param);
        $response = iHttp::send($url,$json);

// iUtils::LOG($response,'wxapp_message.send.response');
//
        if($response){
            $uWhere = array(
                'openid' =>$param['openid'],
                'formid' =>$param['formid']
            );
            if($response->errcode=="0"){//发送成功
                $update = array(
                    'send_time' =>time(),
                    'status'    =>'1'
                );
                self::update($update,$uWhere);
                return true;
            }
            if($response->errcode=="41029"||$response->errcode=="41028"){//已经发送过的
                $update = array(
                    'send_time' =>time(),
                    'status'    =>'2'
                );
                $update && self::update($update,$uWhere);
                unset($param['formid']);
                self::send($param);
            }
            $response->errcode && iUtils::LOG((array)$response,'wxapp_message.send.errot');
            // $response->errcode && trigger_error("errcode:{$response->errcode},errmsg:{$response->errmsg}",E_USER_ERROR);
        }
        return $response;
    }
    public static function create($vars){
        extract($vars,EXTR_PREFIX_ALL,'var');
        foreach ($var_data as $i => $value) {
            $data['keyword'.($i+1)]['value'] =  $value;
        }
        $param = array(
            'touser'           =>$var_openid,
            'template_id'      =>$var_template,
            'page'             =>$var_page,
            'form_id'          =>$var_formid,
            'data'             =>$data,
        );
        $var_big && $param['emphasis_keyword'] = 'keyword'.$var_big.'.DATA';

        return json_encode($param);
    }
    public static function row($where,$field='*'){
        $sql = iSQL::where($where,false);
        $row = iDB::row("
            SELECT {$field} FROM `#iCMS@__wxapp_formids`
            WHERE $sql
            ORDER BY `id` DESC;
        ",ARRAY_A);
        $row['data'] = (array)json_decode($row['data'],true);
        // var_dump(iDB::$last_query);
        return $row;
    }
    public static function all($where,$field='*'){
        $sql = iSQL::where($where,false);
        $rs = iDB::all("
            SELECT {$field} FROM `#iCMS@__wxapp_formids`
            WHERE $sql
        ",ARRAY_A);
        if($rs)foreach ($rs as $key => $value) {
            $value['data'] = (array)json_decode($value['data'],true);
            $rs[$key] = $value;
        }
        return $rs;
    }
    public static function update($data,$where){
        return iDB::update('wxapp_formids',$data,$where);
    }
    public static function get_formid($value,$field='openid'){
        $now = time();
        return iDB::row("
            SELECT `formid`,`openid`,`appid` FROM `#iCMS@__wxapp_formids`
            WHERE `$field` = '$value'
            AND `appid`='".wxapp::$appId."'
            AND `expire_time`>$now
            AND `status`='0'
            ORDER BY `id` ASC;
        ");
    }
    public static function insert($formids=null,$type='0'){
        $fields = array(
            'userid', 'appid', 'openid',
            'formid',
            'create_time', 'expire_time', 'send_time', 'type', 'status'
        );
        $data = compact($fields);

        $data['appid']       = wxapp::$appId;
        $data['userid']      = user::$userid;
        $data['openid']      = user::$openid;
        $data['expire_time'] = time()+86400*7;
        $data['send_time']   = 0;
        $data['type']        = $type;
        $data['status']      = 0;
        iSQL::filter_data($data,$fields);
        if($formids){
            if(is_array($formids)){
                $multi = array();
                foreach ($formids as $key => $fid) {
                    if($fid){
                        $multi[$key] = $data;
                        $multi[$key]['formid'] = $fid;
                        if($fid=='the formId is a mock one'){
                            $multi[$key]['status'] = '9';
                        }
                    }
                }
                if($multi){
                    iDB::insert_multi('wxapp_formids',$multi);
                }
            }else{
                $data['formid'] = $formids;
                if($formid=='the formId is a mock one'){
                    $data['status'] = '9';
                }
                return iDB::insert('wxapp_formids',$data);
            }
        }
    }
}
