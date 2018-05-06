<?php
/**
 * @author https://github.com/chuck911/qiniu-php
 */
class QiniuClient
{
	const UP_HOST  = 'http://up.qiniu.com';
	const RS_HOST  = 'http://rs.qbox.me';
	const RSF_HOST = 'http://rsf.qbox.me';

	public $accessKey;
	public $secretKey;

	public function __construct($accessKey,$secretKey)
	{
		$this->accessKey = $accessKey;
		$this->secretKey = $secretKey;
	}

	public function uploadFile($filePath,$bucket,$key=null)
	{
		$data = array();
		$ch   = curl_init();
		if (class_exists('CURLFile',false)) {
		    defined('CURLOPT_SAFE_UPLOAD') && curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
		    $data['file'] = new CURLFile($filePath);
		} else {
	        if (defined('CURLOPT_SAFE_UPLOAD')) {
	            if (version_compare('5.6',PHP_VERSION,'>=')) {
	                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
	            }
	        }
		    $data['file'] = "@$filePath";
		}
		// $data['file'] = "@$filePath";
		$data['token'] = $this->uploadToken(array('scope' => $bucket));
		if($key) $data['key'] = $key;

		curl_setopt($ch, CURLOPT_URL, self::UP_HOST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	public function upload($content,$bucket,$key=null)
	{
		$filePath = tempnam(sys_get_temp_dir(), 'UPLOAD');
		file_put_contents($filePath, $content);
		$result = $this->uploadFile($filePath,$bucket,$key);
		unlink($filePath);
		return $result;
	}

	public function uploadRemote($url,$bucket,$key=null)
	{
		$filePath = tempnam(sys_get_temp_dir(), 'UPLOAD');
		copy($url,$filePath);
		$result = $this->uploadFile($filePath,$bucket,$key);
		unlink($filePath);
		return $result;
	}

	public function stat($bucket,$key)
	{
		$encodedEntryURI = $this->urlsafe_base64_encode("{$bucket}:{$key}");
		$url = "/stat/{$encodedEntryURI}";
		return $this->fileHandle($url);
	}

	public function move($bucket,$key,$bucket2,$key2=false)
	{
		if(!$key2) {
			$key2 = $bucket2;
			$bucket2 = $bucket;
		}
		$encodedEntryURISrc = $this->urlsafe_base64_encode("{$bucket}:{$key}");
		$encodedEntryURIDest = $this->urlsafe_base64_encode("{$bucket2}:{$key2}");
		$url = "/move/{$encodedEntryURISrc}/{$encodedEntryURIDest}";
		return $this->fileHandle($url);
	}

	public function copy($bucket,$key,$bucket2,$key2=false)
	{
		if(!$key2) {
			$key2 = $bucket2;
			$bucket2 = $bucket;
		}
		$encodedEntryURISrc = $this->urlsafe_base64_encode("{$bucket}:{$key}");
		$encodedEntryURIDest = $this->urlsafe_base64_encode("{$bucket2}:{$key2}");
		$url = "/copy/{$encodedEntryURISrc}/{$encodedEntryURIDest}";
		return $this->fileHandle($url);
	}

	public function delete($bucket,$key)
	{
		$encodedEntryURI = $this->urlsafe_base64_encode("{$bucket}:{$key}");
		$url = "/delete/{$encodedEntryURI}";
		return $this->fileHandle($url);
	}

	// $operator = stat|move|copy|delete
	// $client->batch('stat',array('square:test/test5.txt','square:test/test13.png'));
	public function batch($operator,$files)
	{
		$data = '';
		foreach ($files as $file) {
			if(!is_array($file)) {
				$encodedEntryURI = $this->urlsafe_base64_encode($file);
				$data.="op=/{$operator}/{$encodedEntryURI}&";
			}else{
				$encodedEntryURI = $this->urlsafe_base64_encode($file[0]);
				$encodedEntryURIDest = $this->urlsafe_base64_encode($file[1]);
				$data.="op=/{$operator}/{$encodedEntryURI}/{$encodedEntryURIDest}&";
			}
		}
		return $this->fileHandle('/batch',$data);
	}

	public function listFiles($bucket,$limit='',$prefix='',$marker='')
	{
		$params = array_filter(compact('bucket','limit','prefix','marker'));
		$url = self::RSF_HOST.'/list?'.http_build_query($params);
		return $this->fileHandle($url);
	}

	public function fileHandle($url,$data=array())
	{
		if(strpos($url, 'http://')!==0) $url = self::RS_HOST.$url;

		if(is_array($data)) $accessToken = $this->accessToken($url);
		else $accessToken = $this->accessToken($url,$data);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Authorization: QBox '.$accessToken,
	    ));

	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    // If $data is an array, the Content-Type header will be set to multipart/form-data
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $result = curl_exec($ch);
	    $info = curl_getinfo($ch);
		curl_close($ch);

		if($info['http_code']>=300){
	        return json_encode(array(
	                'error' => $info['http_code'],
	                'msg'   => json_decode($result,true)
	        ));
		}
		if($info['content_type']=='application/json'){
			return json_decode($result,true);
		}

		return $result;
	}

	public function uploadToken($flags)
	{
		if(!isset($flags['deadline']))
			$flags['deadline'] = 3600 + time();
		$encodedFlags = $this->urlsafe_base64_encode(json_encode($flags));
		$sign = hash_hmac('sha1', $encodedFlags, $this->secretKey, true);
		$encodedSign = $this->urlsafe_base64_encode($sign);
	    $token = $this->accessKey.':'.$encodedSign. ':' . $encodedFlags;
	    return $token;
	}

	public function accessToken($url,$body=false){
	    $parsed_url = parse_url($url);
	    $path = $parsed_url['path'];
	    $access = $path;
	    if (isset($parsed_url['query'])) {
	        $access .= "?" . $parsed_url['query'];
	    }
	    $access .= "\n";
	    if($body) $access .= $body;
	    $digest = hash_hmac('sha1', $access, $this->secretKey, true);
	    return $this->accessKey.':'.$this->urlsafe_base64_encode($digest);
	}

	public function urlsafe_base64_encode($str){
	    $find = array("+","/");
	    $replace = array("-", "_");
	    return str_replace($find, $replace, base64_encode($str));
	}
}
