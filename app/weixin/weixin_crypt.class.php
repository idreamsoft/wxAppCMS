<?php
/**
 * 1.第三方回复加密消息给公众平台；
 * 2.第三方收到公众平台发送的消息，验证消息的安全性，并对消息进行解密。
 */

class weixin_crypt{
    /**
     * @param $token string 公众平台上，开发者设置的token
     * @param $aeskey string 公众平台上，开发者设置的EncodingAESKey
     * @param $appId string 公众平台的appId
     */
    public static $token;
    public static $aeskey;
    public static $appId;
    public static $timeStamp;
    public static $nonce;


    /**
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param $replyMsg string 公众平台待回复用户的消息，xml格式的字符串
     * @param &$encryptMsg string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
     *                      当return返回0时有效
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public static function encrypt($replyMsg, &$encryptMsg){
        Prpcrypt::aeskey(self::$aeskey);
        //加密
        $array = Prpcrypt::encrypt($replyMsg, self::$appId);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }

        if (self::$timeStamp == null) {
            self::$timeStamp = time();
        }
        $encrypt = $array[1];

        //生成安全签名
        $array = self::getSHA1(self::$token, self::$timeStamp, self::$nonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];

        //生成发送的xml
        $encryptMsg = self::generate($encrypt, $signature, self::$timeStamp, self::$nonce);
        return ErrorCode::$OK;
    }


    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param $msgSignature string 签名串，对应URL参数的msg_signature
     * @param $input string 密文，对应POST请求的数据
     * @param &$msg string 解密后的原文，当return返回0时有效
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public static function decrypt($msgSignature, &$msg, $input=null){
        if (strlen(self::$aeskey) != 43) {
            return ErrorCode::$IllegalAesKey;
        }
        $input===null && $input = file_get_contents("php://input");
        //提取密文
        $array = self::extract($input);
        $ret = $array[0];

        if ($ret != 0) {
            return $ret;
        }

        if (self::$timeStamp == null) {
            self::$timeStamp = time();
        }

        $encrypt = $array[1];
        $touser_name = $array[2];

        //验证安全签名
        $array = self::getSHA1(self::$token, self::$timeStamp, self::$nonce, $encrypt);
        $ret = $array[0];

        if ($ret != 0) {
            return $ret;
        }

        $signature = $array[1];
        if ($signature != $msgSignature) {
            return ErrorCode::$ValidateSignatureError;
        }

        Prpcrypt::aeskey(self::$aeskey);
        $result = Prpcrypt::decrypt($encrypt, self::$appId);
        if ($result[0] != 0) {
            return $result[0];
        }
        $msg = $result[1];

        return ErrorCode::$OK;
    }
    /**
     * 用SHA1算法生成安全签名
     * @param string $token 票据
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $encrypt 密文消息
     */
    public static function getSHA1($token, $timestamp, $nonce, $encrypt_msg){
        //排序
        try {
            $array = array($encrypt_msg, $token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode($array);
            return array(ErrorCode::$OK, sha1($str));
        } catch (Exception $e) {
            //print $e . "\n";
            return array(ErrorCode::$ComputeSignatureError, null);
        }
    }
    /**
     * 提取出xml数据包中的加密消息
     * @param string $xmltext 待提取的xml字符串
     * @return string 提取出的加密消息字符串
     */
    public static function extract($input){
        try {
            $array = iUtils::INPUT($input);
            $encrypt = $array['Encrypt'];
            $tousername = $array['ToUserName'];
            return array(0, $encrypt, $tousername);
        } catch (Exception $e) {
            //print $e . "\n";
            return array(ErrorCode::$ParseXmlError, null, null);
        }
    }

    /**
     * 生成xml消息
     * @param string $encrypt 加密后的消息密文
     * @param string $signature 安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     */
    public static function generate($encrypt, $signature, $timestamp, $nonce){
        $format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }
}
/**
 * error code 说明.
 * <ul>
 *    <li>-40001: 签名验证错误</li>
 *    <li>-40002: xml解析失败</li>
 *    <li>-40003: sha加密生成签名失败</li>
 *    <li>-40004: encodingAesKey 非法</li>
 *    <li>-40005: appid 校验错误</li>
 *    <li>-40006: aes 加密失败</li>
 *    <li>-40007: aes 解密失败</li>
 *    <li>-40008: 解密后得到的buffer非法</li>
 *    <li>-40009: base64加密失败</li>
 *    <li>-40010: base64解密失败</li>
 *    <li>-40011: 生成xml失败</li>
 * </ul>
 */
class ErrorCode{
    public static $OK = 0;
    public static $ValidateSignatureError = -40001;
    public static $ParseXmlError          = -40002;
    public static $ComputeSignatureError  = -40003;
    public static $IllegalAesKey          = -40004;
    public static $ValidateAppidError     = -40005;
    public static $EncryptAESError        = -40006;
    public static $DecryptAESError        = -40007;
    public static $IllegalBuffer          = -40008;
    public static $EncodeBase64Error      = -40009;
    public static $DecodeBase64Error      = -40010;
    public static $GenReturnXmlError      = -40011;
}
/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder{
    public static $block_size = 32;
    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    public static function encode($text){
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ($text_length % PKCS7Encoder::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = PKCS7Encoder::$block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    public static function decode($text){
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

}

/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt{
    public static $key;

    public static function aeskey($k){
        self::$key = base64_decode($k . "=");
    }

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public static function encrypt($text, $appid){
        try {
            //获得16位随机字符串，填充到明文之前
            $random = self::getRandomStr();
            $text = $random . pack("N", strlen($text)) . $text . $appid;
            //使用自定义的填充方式对明文进行补位填充
            $text = PKCS7Encoder::encode($text);
            $iv = substr(self::$key, 0, 16);
            if(function_exists('openssl_encrypt')){
                $encrypted = openssl_encrypt($text, 'AES-256-CBC', substr(self::$key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
            }else{
                // 网络字节序
                // $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
                $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
                mcrypt_generic_init($module, self::$key, $iv);
                //加密
                $encrypted = mcrypt_generic($module, $text);
                mcrypt_generic_deinit($module);
                mcrypt_module_close($module);
            }

            //print(base64_encode($encrypted));
            //使用BASE64对加密后的字符串进行编码
            return array(ErrorCode::$OK, base64_encode($encrypted));
        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$EncryptAESError, null);
        }
    }

    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    public static function decrypt($encrypted, $appid) {
        try {
            //使用BASE64对需要解密的字符串进行解码
            $iv = substr(self::$key, 0, 16);
            if(function_exists('openssl_decrypt')){
                $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', substr(self::$key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
            }else{
                $ciphertext_dec = base64_decode($encrypted);
                $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
                mcrypt_generic_init($module, self::$key, $iv);

                //解密
                $decrypted = mdecrypt_generic($module, $ciphertext_dec);
                mcrypt_generic_deinit($module);
                mcrypt_module_close($module);
            }
        } catch (Exception $e) {
            return array(ErrorCode::$DecryptAESError, null);
        }


        try {
            //去除补位字符
            $result = PKCS7Encoder::decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$IllegalBuffer, null);
        }
        if ($from_appid != $appid)
            return array(ErrorCode::$ValidateAppidError, null);
        return array(0, $xml_content);

    }


    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    public static function getRandomStr(){
        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

}


// // 第三方发送消息给公众平台
// $encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
// $token = "pamtest";
// $timeStamp = "1409304348";
// $nonce = "xxxxxx";
// $appId = "wxb11529c136998cb6";
// $text = "<xml><ToUserName><![CDATA[oia2Tj我是中文jewbmiOUlr6X-1crbLOvLw]]></ToUserName><FromUserName><![CDATA[gh_7f083739789a]]></FromUserName><CreateTime>1407743423</CreateTime><MsgType><![CDATA[video]]></MsgType><Video><MediaId><![CDATA[eYJ1MbwPRJtOvIEabaxHs7TX2D-HV71s79GUxqdUkjm6Gs2Ed1KF3ulAOA9H1xG0]]></MediaId><Title><![CDATA[testCallBackReplyVideo]]></Title><Description><![CDATA[testCallBackReplyVideo]]></Description></Video></xml>";

// weixin_crypt::$token     = $token;
// weixin_crypt::$aeskey    = $encodingAesKey;
// weixin_crypt::$appId     = $appId;
// weixin_crypt::$timeStamp = $timeStamp;
// weixin_crypt::$nonce     = $nonce;

// // $pc = new weixin_crypt($token, $encodingAesKey, $appId);
// $encryptMsg = '';
// $errCode = weixin_crypt::encrypt($text, $encryptMsg);
// if ($errCode == 0) {
//     print("加密后: " . $encryptMsg . "\n");
// } else {
//     print($errCode . "\n");
// }

// $xml_tree = new DOMDocument();
// $xml_tree->loadXML($encryptMsg);
// $array_e = $xml_tree->getElementsByTagName('Encrypt');
// $array_s = $xml_tree->getElementsByTagName('MsgSignature');
// $encrypt = $array_e->item(0)->nodeValue;
// $msg_sign = $array_s->item(0)->nodeValue;

// $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
// $from_xml = sprintf($format, $encrypt);

// // 第三方收到公众号平台发送的消息
// $msg = '';
// $errCode = weixin_crypt::decrypt($msg_sign, $from_xml, $msg);
// if ($errCode == 0) {
//     print("解密后: " . $msg . "\n");
// } else {
//     print($errCode . "\n");
// }
