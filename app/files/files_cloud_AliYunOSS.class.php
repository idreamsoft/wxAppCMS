<?php
/**
 * 阿里云oss iPHP接口 统一
 */
// define('ALI_DISPLAY_LOG', 1);
defined('iPHP') OR exit('What are you doing?');

require dirname(__FILE__) .'/library/AliYunOSS.class.php';

class files_cloud_AliYunOSS extends ALIOSS{
    public $conf;
    public function __construct($conf){
        $this->conf = $conf;
        parent::__construct($conf['AccessKey'],$conf['SecretKey'],$conf['domain']);
        $this->set_debug_mode(FALSE);
    }
   /**
     * [_upload_file 上传文件接口]
     * @param  [type] $fileRootPath [文件绝对路径]
     * @param  [type] $filePath [文件路径]
     * @return [type]           [description]
     */
    public function _upload_file($fileRootPath,$filePath){
        $options = array(
            ALIOSS::OSS_HEADERS => array(
                'Cache-control' => 'max-age=864000',
            )
        );
        $response = $this->upload_file_by_file($this->conf['Bucket'],$filePath,$fileRootPath,$options);
        unset($response->header);
        $response->body && $response->body = simplexml_load_string($response->body, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_encode(array(
                'error' => !$response->isOk(),
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
        $response = $this->delete_object($this->conf['Bucket'],$filePath);
        unset($response->header);
        $response->body && $response->body = simplexml_load_string($response->body, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_encode(array(
                'error' => !$response->isOk(),
                'msg'   => $response
        ));
    }
}
// require dirname(__FILE__).'/../../iCMS.php';
// $conf = iCMS::$config['cloud']['sdk']['AliYunOSS'];
// $cloud = new files_cloud_AliYunOSS($conf);
// $filePath = '2017/02-08/23/01b71d15d5bc0de1c15e1beb4be128ea.jpg';
// $fileRootPath= iFS::fp($filePath,'+iPATH');
// $response = $cloud->_upload_file($fileRootPath,$filePath);
// // $response = $cloud->_delete_file($filePath);
// print_r($response);
