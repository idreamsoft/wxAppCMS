<?php
// namespace Tencentyun;

class Conf
{
    const PKG_VERSION = '2.0.1';

    const API_IMAGE_END_POINT = 'http://web.image.myqcloud.com/photos/v1/';

    const API_IMAGE_END_POINT_V2 = 'http://web.image.myqcloud.com/photos/v2/';

	const API_VIDEO_END_POINT = 'http://web.video.myqcloud.com/videos/v1/';

    // 以下部分请您根据在qcloud申请到的项目id和对应的secret id和secret key进行修改

    public static $APPID      = '10005051';
    public static $SECRET_ID  = 'AKIDmBZMo4W9B1IzEkh2902hzyD4zunAmYX2';
    public static $SECRET_KEY = 'cRJ0lhD9mcx1y8IOGeOZMbMpcDGv53m5';

    // 以上部分请您根据在qcloud申请到的项目id和对应的secret id和secret key进行修改

    public static function getUA() {
        return 'QcloudPHP/'.self::PKG_VERSION.' ('.php_uname().')';
    }
}


//end of script
