<?php

class wxapp_user {
    const PLATFORM = '5';

    public static function cookie($openid,$user){
        user::$openid   = $openid;
        user::$userid   = $user['userid'];
        user::$nickname = $user['nickname'];
        $user['uid']    = $user['userid'];
        user::$callback['cookie'] = $user;
        return $user;
    }
    public static function get($uid=0,$openid=null,$appid=null){
        $appid===null && $appid = wxapp::$appId;
        // $openid&& $sql.=" AND `openid`='{$openid}' ";
        // $appid && $sql.=" AND `appid`='{$appid}' ";
        // $uid   && $sql.=" AND `uid`='{$uid}' ";
        // return iDB::row("
        //     SELECT * FROM `#iCMS@__wxapp_user`
        //     WHERE `status`='1' {$sql}
        //     LIMIT 1
        // ");
        $userid = user_openid::uid($openid,self::PLATFORM,$appid);
        if($userid){
            return user::get($userid,false);
        }else{
            return false;
        }
    }
    public static function uid($openid,$appid=null){
        $appid===null && $appid = wxapp::$appId;
        $appid && $sql =" AND `appid`='{$appid}'";
        $uid = iDB::value("
            SELECT `uid` FROM `#iCMS@__wxapp_user`
            WHERE `status`='1' AND `openid`='{$openid}' {$sql}
            LIMIT 1
        ");
        return $uid;
    }
    public static function check($openid,$appid=null){
        $appid===null && $appid = wxapp::$appId;
        // return self::uid($openid,$appid);
        //
        return user_openid::uid($openid,self::PLATFORM,$appid);
    }
    public static function insert($array){
        $fields = array(
            'uid', 'appid', 'username', 'nickname', 'openid',
            'gender', 'province', 'city',
            'client_ip', 'create_time', 'status'
        );
        $data = compact($fields);
        $data = array_merge($data,$array);
        iSQL::filter_data($data,$fields);
        $userid = iDB::insert('wxapp_user', $data);
    }
    public static function login($session=null){
        $appid = wxapp::$appId;
        $session===null && $session = wxapp_api::get_session();
        if($session){
            $openid      = $session->openid;
            $session_key = $session->session_key;

            if(empty($openid)){
                return false;
            }

            $user = self::get(0,$openid,$appid);
            $res = array(
                // 'session_key' =>$session->session_key,
                'openid' =>$openid,
                'userid' =>$user->uid,
            );
            if ($user) {
                // $res['username'] = base64_decode($user->username);
                $res['username'] = $user->username;
                $res['nickname'] = $user->nickname;
                $res['password'] = $user->password;
                $res['status']   = $user->status;
            } else {
                if(extension_loaded('openssl') && function_exists('openssl_decrypt')){
                    $crypt   = new wxapp_crypt($appid, $session_key);
                    $errCode = $crypt->decrypt(stripslashes($_POST['encryptedData']), $_POST['iv'],$rawData);
                    if($errCode>0){
                        return false;
                    }
                }
                empty($rawData) && $rawData = json_decode(stripslashes($_POST['rawData']));

                if(empty($rawData)){
                    return false;
                }

                $rawData->nickName = trim($rawData->nickName);
                empty($rawData->nickName) && $rawData->nickName = substr($openid, 0,8);
                $nickname    = $rawData->nickName;
                $province    = $rawData->province;
                $city        = $rawData->city;
                $gender      = $rawData->gender;
                $status      = 1;
//---------------------------------------会员系统-----------------------------------------------
                $username    = base64_encode($rawData->nickName).'@'.$appid;
                $password    = md5($openid.uniqid());
                $regip       = iPHP::get_ip();
                $regdate     = time();
                $lastloginip = $regip;
                $gid         = 0;
                $pid         = 0;
                $type        = self::PLATFORM;
                $fields      = array(
                    'gid', 'pid', 'username', 'nickname', 'password','gender',
                    'regip','lastloginip', 'regdate','type', 'status',
                );
                $data = compact($fields);
                $uid = iDB::insert('user', $data);
                iDB::insert('user_data', array(
                    'uid'      =>$uid,
                    'province' =>$province,
                    'city'     =>$city,
                ));
                user_openid::save($uid,$openid,self::PLATFORM,$appid);
//------------------------------------------------------------------------------------------
//---------------------------------------小程序 会员系统-----------------------------------------------
                // iDB::query("SET NAMES 'utf8mb4'");

                // $username    = base64_encode($rawData->nickName);
                // $client_ip   = iPHP::get_ip();
                // $create_time = time();
                // $array       = compact(array(
                //     'uid', 'appid', 'username', 'nickname', 'openid',
                //     'gender', 'province', 'city',
                //     'client_ip', 'create_time', 'status'
                // ));
                // self::insert($array);
//------------------------------------------------------------------------------------------
                $res['userid']   = $uid;
                $res['username'] = $username;
                $res['nickname'] = $nickname;
                $res['password'] = $password;
                $res['status']   = $status;

                $avatar = $rawData->avatarUrl;
                if ($avatar) {
                    $avatarData = iHttp::remote($avatar);
                    if ($avatarData) {
                        $avatarpath = iFS::fp(get_user_pic($uid), '+iPATH');
                        iFS::mkdir(dirname($avatarpath));
                        iFS::write($avatarpath, $avatarData);
                    }
                }
            }
            $flag = self::cookie($openid,$res);
            $_POST['FORM_ID'] && wxapp_message::insert($_POST['FORM_ID'],'1');
            return $flag;
        }
    }
}
