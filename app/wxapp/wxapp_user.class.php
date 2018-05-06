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
            $openid = $response->openid;
            $userid = user::openid($openid, self::PLATFORM);
            $res = array(
                // 'session_key' =>$response->session_key,
                'openid'      =>$openid,
                'userid'      =>$userid,
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

                empty($rawData['nickName']) && $rawData['nickName'] = substr($openid, 0,8);

                $nickname = $rawData['nickName'];
                $username = $rawData['nickName'].'@'.wxapp::$appId;
                $province = $rawData['province'];
                $city     = $rawData['city'];
                $gender   = $rawData['gender'];
                $password = md5($openid);
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

                iDB::insert('user_openid',array(
                    'uid'      => $userid,
                    'openid'   => $openid,
                    'platform' => self::PLATFORM,
                ));

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
