<?php
/**
 * 七牛云 iPHP接口 统一
 */
require dirname(__FILE__) .'/library/QiniuClient.class.php';

class files_cloud_QiNiuYun extends QiniuClient{
    public $conf;
    public function __construct($conf){
        $this->conf = $conf;
        parent::__construct($conf['AccessKey'],$conf['SecretKey']);
    }
   /**
     * [_upload_file 上传文件接口]
     * @param  [type] $fileRootPath [文件绝对路径]
     * @param  [type] $filePath [文件路径]
     * @return [type]           [description]
     */
    public function _upload_file($fileRootPath,$filePath){
        $json = $this->uploadFile($fileRootPath,$this->conf['Bucket'],$filePath);
        $response = json_decode($json,true);
        return json_encode(array(
                'error' => $response['error']?true:false,
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
        $json = $this->delete($this->conf['Bucket'],$filePath);
        $response = json_decode($json,true);
        return json_encode(array(
                'error' => $response['error']?true:false,
                'msg'   => $response
        ));
    }
}

// require dirname(__FILE__).'/../../iCMS.php';
// $conf = iCMS::$config['cloud']['sdk']['QiNiuYun'];
// $cloud = new files_cloud_QiNiuYun($conf);
// $filePath = '2017/02-08/23/01b71d15d5bc0de1c15e1beb4be128ea.jpg';
// $fileRootPath= iFS::fp($filePath,'+iPATH');
// $response = $cloud->_upload_file($fileRootPath,$filePath);
// $response = $cloud->_delete_file($filePath);
// print_r($response);
