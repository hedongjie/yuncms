<?php
/**
 * KVDB抽象类
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Abstract.php 2 2013-01-14 07:14:05Z xutongle $
 */
abstract class KVDB_Abstract {

	public static function &get_instance($driver = 'dba', $handler = 'flatfile') {
		$class = 'KVDB_Driver_' . $driver;
		$c = new $class ( $handler );
		return $c;
	}

	/**
	 * 打开一个到KVdb的连接
	 * @param string $path 路径
	 * @param string $mode 模式
	 */
	abstract public function open($path, $mode = 'n');

	/**
	 * 打开一个到KVdb的长连接
	 * @param string $path 路径
	 * @param string $mode 模式
	 */
	abstract public function popen($path, $mode = 'n');

	/**
	 * 增加key-value
	 * @param string $key Key
	 * @param string $value Value
	 */
	abstract public function add($key, $value);

	/**
	 * 更新key-value
	 * @param string $key Key
	 * @param string $value Value
	 */
	abstract public function set($key, $value);

	/**
	 * 获得key-value
	 * @param string $key Key
	 */
	abstract public function get($key);

	/**
	 * 删除key-value
	 * @param string $key Key
	 */
	abstract public function rm($key);

	/**
	 * Key是否存在
	 * @param string $key Key
	 */
	abstract public function exists($key);

	/**
	 * 关闭到KVDB的连接
	 */
	abstract public function close();
}