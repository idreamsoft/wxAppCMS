<?php
/**
 * 腾讯云万象图片服务 iPHP接口 统一
 */
require dirname(__FILE__) .'/library/Tencentyun/Http.php';
require dirname(__FILE__) .'/library/Tencentyun/Conf.php';
require dirname(__FILE__) .'/library/Tencentyun/Auth.php';
require dirname(__FILE__) .'/library/Tencentyun/ImageV2.php';
require dirname(__FILE__) .'/library/Tencentyun/Video.php';

class files_cloud_TencentYun {
    public $conf;
    public function __construct($conf){
        $this->conf = $conf;
        Conf::$SECRET_ID  = $conf['AccessKey'];
        Conf::$SECRET_KEY = $conf['SecretKey'];
        Conf::$APPID      = $conf['AppId'];
    }
   /**
     * [_upload_file 上传文件接口]
     * @param  [type] $fileRootPath [文件绝对路径]
     * @param  [type] $filePath [文件路径]
     * @return [type]           [description]
     */
    public function _upload_file($fileRootPath,$filePath){
        $response = ImageV2::stat($this->conf['Bucket'], $filePath);//判断文件有没有存在
        $response['code'] && $response = ImageV2::upload($fileRootPath,$this->conf['Bucket'],$filePath);

        return json_encode(array(
                'error' => $response['code'],
                'url'   => $this->conf['domain'].'/'.$filePath,
                'msg'   => $response
        ));
    }
   /**
     * [_delete_file 删除文件接口]
     * @param  [type] $filePath [文件路径]
     * @return [type]           [description]
     */
    public function _delete_file($filePath){
        $response = ImageV2::del($this->conf['Bucket'],$filePath);
        return json_encode(array(
                'error' => $response['code'],
                'msg'   => $response
        ));
    }
}
// require dirname(__FILE__).'/../../iCMS.php';
// $conf = iCMS::$config['cloud']['sdk']['TencentYun'];
// $cloud = new files_cloud_TencentYun($conf);
// $filePath = '2017/04-21/20/0c607dc077954c7f7d6f25deb0f34f6f.jpg';
// $fileRootPath= iFS::fp($filePath,'+iPATH');
// $response = $cloud->_upload_file($fileRootPath,$filePath);
// $response = $cloud->_delete_file($filePath);
// print_r($response);
