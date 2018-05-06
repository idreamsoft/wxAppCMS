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
//array(
//	'enable'	=> true,false,
//	'engine'	=> memcached,redis,file,
//	'host'		=> 127.0.0.1,/tmp/redis.sock,
//	'port'		=> 11211,
//	'db'		=> 1,
//	'compress'	=> 1-9,
//	'time'		=> 0,
//)
class iCache {
	public static $handle = null;
	protected static $config = null;

	public static function init($config,$reset=null) {
		self::$config = $config;

		$reset===null && $reset = $config['reset'];
		$reset && self::destroy();

		if (isset($GLOBALS['iPHP_CACHE']['handle'])) {
			self::$handle = $GLOBALS['iPHP_CACHE']['handle'];
			return self::$handle;
		}
		self::$config['engine'] OR self::$config['engine'] = 'file';
		self::connect();
		return self::$handle;
	}
	public static function connect() {
		if (self::$handle === null) {
			switch (self::$config['engine']) {
				case 'memcached':
					$_servers = explode("\n", str_replace(array("\r", " "), "", self::$config['host']));
					self::$handle = iPHP::vendor('Memcached_client',array(
						'servers' => $_servers,
						'compress_threshold' => 10240,
						'persistant' => false,
						'debug' => false,
						'compress' => self::$config['compress'],
					),true);
					unset($_servers);
				break;
				case 'redis':
					list($hosts, $db, $passwd) = explode('@', trim(self::$config['host']));
					list($host, $port) = explode(':', $hosts);
					if (strstr($hosts, 'unix:')) {
						$host = $hosts;
						$port = 0;
					}
					$db = (int) str_replace('db:', '', $db);
					$db == '' && $db = 1;

					self::$handle = iPHP::vendor('Redis_client',array(
						'host' => $host,
						'port' => $port,
						'db' => $db,
						'passwd' => $passwd,
						'compress' => self::$config['compress'],
					),true);
				break;
				case 'file':
					require_once iPHP_CORE . '/iFileCache.class.php';
					list($dirs, $level) = explode(':', self::$config['host']);
					$level OR $level = 0;
					self::$handle = new iFileCache(array(
						'dirs' => $dirs,
						'level' => $level,
						'compress' => self::$config['compress'],
					));
				break;
			}
			$GLOBALS['iPHP_CACHE']['handle'] = self::$handle;
		}
	}
	public static function prefix($keys = null, $prefix = null) {
		$prefix===null && $prefix = self::$config['prefix'];
		if ($prefix) {
			if (is_array($keys)) {
				foreach ($keys AS $k) {
					$_keys[] = $prefix . '/' . $k;
				}
				$keys = $_keys;
			} else {
				$keys = $prefix . '/' . $keys;
			}
		}
		return $keys;
	}
	public static function get($keys, $ckey = NULL, $unserialize = true) {
		self::connect();
		$keys = self::prefix($keys);
		$_keys = implode('', (array) $keys);
		if (!isset($GLOBALS['iPHP_CACHE'][$_keys])) {
			$GLOBALS['iPHP_CACHE'][$_keys] = is_array($keys) ?
			self::$handle->get_multi($keys, $unserialize) :
			self::$handle->get($keys, $unserialize);
		}
		return $ckey === NULL ? $GLOBALS['iPHP_CACHE'][$_keys] : $GLOBALS['iPHP_CACHE'][$_keys][$ckey];
	}
	public static function set($keys, $res, $cachetime = "-1") {
		self::connect();
		$keys = self::prefix($keys);
		if (self::$config['engine'] == 'memcached') {
			self::$handle->delete($keys);
		}
		self::$handle->add($keys, $res, ($cachetime != "-1" ? $cachetime : self::$config['time']));
	}
	public static function del($key = '', $time = 0) {
		self::delete($key,$time);
	}
	public static function delete($key = '', $time = 0) {
		$key = self::prefix($key);
		self::connect();
		self::$handle->delete($key, $time);
	}

	public static function file_cache() {
		require_once iPHP_CORE . '/iFileCache.class.php';
		return new iFileCache(array(
			'dirs' => '',
			'level' => 0,
			'compress' => 1,
		));
	}
	public static function redis($host = '127.0.0.1:6379@db:1', $time = '86400') {
		if (self::$config['engine'] != 'redis') {
			iCache::init(array(
				'enable' => true,
				'reset' => true,
				'engine' => 'redis',
				'host' => $host,
				'time' => $time,
			));
		}
	}
	public static function destroy() {
		self::$handle = null;
		$GLOBALS['iPHP_CACHE']['handle'] = null;
	}
}
