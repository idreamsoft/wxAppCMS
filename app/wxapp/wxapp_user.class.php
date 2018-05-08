<?php

class wxapp_user {
    const PLATFORM = '5';

    public static function cookie($openid,$user){
        user::$openid   = $openid;
        user::$userid   = $user['userid'];
        user::$nickname = $user['nickname'];
        $user['uid']    = $user['userid'];
        $user['password'] && $user['password'] = $user['password'];
        user::$callback['cookie'] = $user;
        return $user;
    }
    public static function data($response){
        if($response){
            $openid      = $response->openid;
            $session_key = $response->session_key;

            // $crypt   = new wxapp_crypt(wxapp::$appId, $session_key);
            // $errCode = $crypt->decrypt($_POST['encryptedData'], $_POST['iv'],$data);

            if(empty($openid)){
                return false;
            }
            $userid = user_openid::uid($openid,self::PLATFORM,wxapp::$appId);
            $res = array(
                // 'session_key' =>$response->session_key,
                'openid' =>$openid,
                'userid' =>$userid,
            );
            if ($userid) {
                $user = user::get($userid,false);
                $res['username'] = $user->username;
                $res['nickname'] = $user->nickname;
                $res['password'] = $user->password;
                $res['status']   = $user->status;

                // $res['avatar'] = user::router($userid,"avatar");
            } else {
                $rawData = json_decode(stripslashes($_POST['rawData']),true);

                if(empty($rawData)){
                    return false;
                }
                $rawData['nickName'] = trim($rawData['nickName']);
                empty($rawData['nickName']) && $rawData['nickName'] = substr($openid, 0,8);

                $nickname = $rawData['nickName'];
                $username = $rawData['nickName'].'@'.$openid;
                $province = $rawData['province'];
                $city     = $rawData['city'];
                $gender   = $rawData['gender'];
                $password = md5($openid.uniqid());
                $regip    = iPHP::get_ip();
                $regdate  = time();
                $gid      = 0;
                $pid      = 0;
                $status   = 1;
                $type     = self::PLATFORM;
                $fields = array(
                    'gid', 'pid', 'username', 'nickname', 'password','gender',
                    'regip', 'regdate','type', 'status',
                );
                $data = compact($fields);
                $userid = iDB::insert('user', $data);

                user_openid::save($userid,$openid,self::PLATFORM,wxapp::$appId);

                $res['userid']   = $userid;
                $res['username'] = $username;
                $res['nickname'] = $nickname;
                $res['password'] = $password;
                $res['status']   = $status;

                $avatar = $rawData['avatarUrl'];
                if ($avatar) {
                    $avatarData = iHttp::remote($avatar);
                    if ($avatarData) {
                        $avatarpath = iFS::fp(get_user_pic($userid), '+iPATH');
                        iFS::mkdir(dirname($avatarpath));
                        iFS::write($avatarpath, $avatarData);
                    }
                }
            }
            return self::cookie($openid,$res);
        }
    }
}
