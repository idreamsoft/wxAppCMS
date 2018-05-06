<?php
/**
 * @author [zhainan13]
 * @source https://github.com/zhainan13/iCMS
 */
require dirname(__FILE__) .'/library/UpYun.class.php';

class files_cloud_UpYun extends UpYun {
	public $conf;
	public function __construct($conf){
		$this->conf = $conf;
		parent::__construct($conf['Bucket'], $conf['AccessKey'], $conf['SecretKey']);
	}

   /**
     * [_upload_file 上传文件接口]
     * @param  [type] $fileRootPath [文件绝对路径]
     * @param  [type] $filePath [文件路径]
     * @return [type]           [description]
     */
    public function _upload_file($fileRootPath,$filePath){
    	$filePath = '/'.ltrim($filePath,'/');
		$response = $this->writeFile($filePath, file_get_contents($fileRootPath));
        return json_encode(array(
                'error' => $response['code']==200?false:true,
                'url'   => $this->getFileUrl($filePath),
                'msg'   => $response
        ));
	}
   /**
     * [_delete_file 删除文件接口]
     * @param  [type] $filePath [文件路径]
     * @return [type]           [description]
     */
    public function _delete_file($filePath){
    	$filePath = '/'.ltrim($filePath,'/');
        $response = $this->delete($filePath);
        return json_encode(array(
                'error' => $response['code']==200?false:true,
                'msg'   => $response
        ));
    }

	private function getFileUrl($path){
		if (empty($this->conf['domain'])){
			return "http://" . $this->conf['Bucket'] . ".b0.upaiyun.com/" . ltrim($path, '/');
		}else{
			return "http://" . $this->conf['domain'] .  $path;
		}
	}
}
// require dirname(__FILE__).'/../../iCMS.php';
// $conf = iCMS::$config['cloud']['sdk']['UpYun'];
// $cloud = new files_cloud_UpYun($conf);
// $filePath = '2017/02-08/23/01b71d15d5bc0de1c15e1beb4be128ea.jpg';
// $fileRootPath= iFS::fp($filePath,'+iPATH');
// $response = $cloud->_upload_file($fileRootPath,$filePath);
// $response = $cloud->_delete_file($filePath);
// print_r($response);
