<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) iiiPHP.com. All rights reserved.
 *
 * @author iPHPDev <master@iiiphp.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.1.0
 */
class iFileCache {
	protected $_cache_sock;
	protected $_have_zlib;
	protected $_compress_enable;
	protected $_dirs;
	protected $_file;

	public function __construct($args=array('dirs'=> '','level'=>'0','compress'=>'9')){
		$this->_dirs            = rtrim(iPHP_APP_CACHE.'/'.$args['dirs'],'/').'/';
		$this->_dir_level       = empty($args['level']) ? -1 : floor(32/$args['level']);
		$this->_compress_enable = $args['compress'];
		$this->_have_zlib       = function_exists("gzcompress");
		$this->_cache_sock      = array();
	}
    public function ping (){
        return true;
    }
	public function add ($key, $val, $exp = 0){
		$this->_file = $this->get_file($key,'add');
		$value       = array(
			"Time"    =>time(),
			"Expires" =>$exp,
			"Data"    =>$val,
		);
		$data = serialize($value);
		$this->_cache_sock='<?php exit;?>';
		if ($this->_have_zlib && $this->_compress_enable){
			$this->_cache_sock.=gzcompress($data, 9);
		}else{
			$this->_cache_sock.=$data;
		}
		return $this->write($this->_file,$this->_cache_sock);
	}
	public function get ($key){
		$this->_file = $this->get_file($key,'get');
		if(!file_exists($this->_file)) return NULL;
		$D     = file_get_contents($this->_file);
		$D     = str_replace('<?php exit;?>','',$D);
		$value = unserialize(($this->_have_zlib && $this->_compress_enable)?@gzuncompress($D):$D);
		if($value['Expires']==0){
			return $value['Data'];
		}else{
			$_time = time();
			return ($_time-$value['Time']<$value['Expires'])?$value['Data']:false;
		}
	}
	public function get_multi ($keys){
		foreach ($keys as $key){
			$value[$key]=$this->get ($key);
		}
		return $value;
	}
	public function replace ($key, $value, $exp=0){}
	public function delete ($key='', $time = 0){
		$this->_file = $this->get_file($key,'get');
		return $this->del($this->_file);
	}
   	public function get_file($key,$method){
		$key     = str_replace(':','/',$key);
		$dirPath = $this->_dirs.(strpos($key,'/')!==false?dirname($key):'');
   		if($this->_dir_level!=-1){
			$md5_array  = $this->str_split(md5($key),$this->_dir_level);
			$md5_array && $dirPath   .= '/'.implode('/',$md5_array).'/';
		}
		if (!file_exists($dirPath) && $method=='add'){
			$this->mkdir($dirPath);
		}
		$strrchr = strrchr($key,'/');
		$strrchr!==false && $key=$strrchr;
		return $dirPath.$key.'.php';
   	}
    public function clear_all($prefix=null){
        $dir = $this->_dirs.$prefix;
        $filesList = $this->file_list($dir);
        foreach ($filesList as $key => $file) {
            if(is_file($file)){
                $ckey = $prefix.str_replace(array($dir,'.php'), '', $file);
                $data = $this->get($ckey);
                $data===false && $this->del($file);
            }
        }
        return $this;
    }
    private function file_list($dir,$pattern='*'){
        $lists = array();
        $dir   = trim($dir, '/');
        foreach(glob($dir.'/'.$pattern) as $value){
            $lists[] = $value;
            if(is_dir($value)){
              $_lists = $this->file_list($value,$pattern);
              $lists  = array_merge($lists,$_lists);
            }
        }
        return (array)$lists;
    }
    private function check($fn) {
        strpos($fn,'..')!==false && trigger_error('What are you doing?',E_USER_ERROR);
    }
    private function del($fn,$check=1) {
        $check && $this->check($fn);
        @chmod ($fn, 0777);
        return @unlink($fn);
    }
    private function write($fn,$data,$check=1,$method="wb+",$iflock=1,$chmod=0) {
        $check && $this->check($fn);
        // @touch($fn);
        $handle = fopen($fn,$method);
        $iflock && flock($handle,LOCK_EX);
        fwrite($handle,$data);
        // $method=="rb+" && ftruncate($handle,strlen($data));
        fclose($handle);
        $chmod && @chmod($fn,0644);
    }
    private function escapeDir($dir) {
        $dir = str_replace(array("'",'#','=','`','$','%','&',';'), '', $dir);
        return rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $dir), '/');
    }
    private function mkdir($d) {
    	$d = $this->escapeDir($d) ;
        $d = str_replace( '//', '/', $d );
        if ( file_exists($d) )
            return @is_dir($d);

        // Attempting to create the directory may clutter up our display.
        if ( @mkdir($d) ) {
            $stat = @stat(dirname($d));
            $dir_perms = $stat['mode'] & 0007777;  // Get the permission bits.
            @chmod($d, $dir_perms );
            return true;
        } elseif (is_dir(dirname($d))) {
            return false;
        }

        // If the above failed, attempt to create the parent node, then try again.
        if ( ( $d != '/' ) && ( $this->mkdir(dirname($d))))
            return $this->mkdir( $d );

        return false;
    }
	private function str_split($str,$level = 1) {
		if ($level < 1) return false;

        if ($level == 1) {
            $ret = array(substr($str,0,3),substr($str,3));
        } elseif ($level == 2) {
            $ret = array(substr($str,0,3),substr( $str,3,6),substr ($str,6));
        } else {
			$strlen = strlen($str);
			$ret    = array();
			for ($i = 0; $i < $strlen; $i += $level) {
				$ret[] = substr($str,$i,$level);
			}
        }

		return $ret;
	}
}


//$c = new iFileCache(array(
//				'dirs'=>"cache_dir_1",
//				'level'=>"1",
//		));
//$c->add("test",array(1,2,3,4,5,6),1000);
//$c->add("asd",array(1,2,3,4,5,6),10);
//$c->add("123123",array(1,2,3,4,5,6),1);
//$rs[]=$c->get("test");
//$rs[]=$c->get("asd");
//$rs[]=$c->get("123123");
//$rs2=$c->get_multi(array("test","asd","123123"));
//var_dump($rs);
//var_dump($rs2);
