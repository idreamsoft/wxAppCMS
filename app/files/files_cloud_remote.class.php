<?php

class files_cloud_remote {
    public $conf;
    public function __construct($conf){
        $this->conf = $conf;
    }
   /**
     * [_upload_file 上传文件接口]
     * @param  [type] $fileRootPath [文件绝对路径]
     * @param  [type] $filePath [文件路径]
     * @return [type]           [description]
     */
    public function _upload_file($fileRootPath,$filePath){
        $key  = 'ufile';
        $json = iHttp::upload($this->conf['api'],
            array($key=>$fileRootPath),
            array(
                'app'       =>'files',
                'action'    =>'remote_save',
                'key'       =>$key,
                'path'      =>$filePath,
                // 'Bucket'    =>$this->conf['Bucket'],
                'AccessKey' =>$this->conf['AccessKey'],
                'SecretKey' =>$this->conf['SecretKey'],
            )
        );
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
        $json = iHttp::post($this->conf['api'],
            array(
                'app'       =>'files',
                'action'    =>'remote_delete',
                'key'       =>$key,
                'path'      =>$filePath,
                'AccessKey' =>$this->conf['AccessKey'],
                'SecretKey' =>$this->conf['SecretKey'],
            )
        );
        $response = json_decode($json,true);
        return json_encode(array(
                'error' => $response['error']?true:false,
                'msg'   => $response
        ));
    }

}
