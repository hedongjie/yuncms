<?php
/**
 * 缓存抽象类
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-26
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Cache.php 2 2013-01-14 07:14:05Z xutongle $
 */
abstract class Cache {
	/**
	 * 缓存配置信息
	 *
	 * @var string
	 */
	private $_options = '';

	/**
	 * 缓存过期时间
	 *
	 * @var int
	 */
	private $expire = '';

	/**
	 * 标志存储时间
	 *
	 * @var string
	 */
	const STORETIME = 'store';

	/**
	 * 标志存储数据
	 *
	 * @var string
	 */
	const DATA = 'data';

	/**
	 * 配置文件中标志过期时间名称定义(也包含缓存元数据中过期时间的定义)
	 *
	 * @var string
	 */
	const EXPIRE = 'expires';

	public static function &get_instance($name = 'default') {
		static $instances = array ();
		if (! isset ( $instances [$name] ) || ! is_object ( $instances [$name] )) {
			$options = C ( 'cache', $name );
			$class = 'Cache_Driver_' . $options ['driver'];
			$instances [$name] = new $class ( $options );
			$instances [$name]->set_config ( $options );
		}
		return $instances [$name];
	}

	/**
	 * 执行设置操作
	 *
	 * @param string $key 缓存数据的唯一key
	 * @param string $value 缓存数据值，该值是一个含有有效数据的序列化的字符串
	 * @param int $expires 缓存数据保存的有效时间，单位为秒，默认时间为0即永不过期
	 * @return boolean
	 * @throws leaps_exception 缓存失败的时候抛出异常
	 */
	protected abstract function set_value($key, $value, $expires = 0);

	/**
	 * 执行获取操作
	 *
	 * @param string $key 缓存数据的唯一key
	 * @return string 缓存的数据
	 * @throws leaps_exception 缓存数据获取失败抛出异常
	 */
	protected abstract function get_value($key);

	/**
	 * 需要实现的删除操作
	 *
	 * @param string $key 需要删除的缓存数据的key
	 * @return boolean
	 */
	protected abstract function delete_value($key);

	/**
	 * 清楚缓存，过期及所有缓存
	 *
	 * @return boolean
	 */
	public abstract function clear();

	/**
	 * 设置缓存
	 * 如果key不存在，添加缓存；否则，将会替换已有key的缓存。
	 *
	 * @param string $key 保存缓存数据的键。
	 * @param string $value 保存缓存数据。
	 * @param int $expires 缓存数据的过期时间,0表示永不过期
	 * @return boolean
	 * @throws leaps_exception 缓存失败时抛出异常
	 */
	public function set($key, $value, $expires = 0) {
		try {
			$data = $this->build_data ( $value, $expires );
			N ( 'cache_write', 1 );
			return $this->set_value ( $key, serialize ( $data ), $data [self::EXPIRE] );
		} catch ( Exception $e ) {
			throw_exception ( 'Setting cache failed.' . $e->getMessage () );
		}
	}

	/**
	 * 根据缓存key获取指定缓存
	 *
	 * @param string $key 获取缓存数据的标识
	 * @param string $key 获取缓存数据的应用
	 * @return mixed 返回被缓存的数据
	 * @throws cache_exception 获取失败时抛出异常
	 */
	public function get($key) {
		try {
			N ( 'cache_read', 1 );
			return $this->format_data ( $key, $this->get_value ( $key ) );
		} catch ( Exception $e ) {
			throw_exception ( 'Getting cache data failed. (' . $e->getMessage () . ')' );
		}
	}

	/**
	 * 根据缓存key获取指定缓存信息
	 *
	 * @param string $key 获取缓存数据的标识
	 * @return mixed 返回被缓存的数据信息
	 */
	public function info($key) {
		try {
			return unserialize ( $this->get_value ( $key ) );
		} catch ( Exception $e ) {
			throw_exception ( 'Getting cache info failed. (' . $e->getMessage () . ')' );
		}
	}

	/**
	 * 删除缓存数据
	 *
	 * @param string $key 获取缓存数据的标识
	 * @param string $key 获取缓存数据的应用
	 * @return boolean
	 * @throws cache_exception 删除失败时抛出异常
	 */
	public function delete($key) {
		try {
			return $this->delete_value ( $key );
		} catch ( Exception $e ) {
			throw_exception ( 'Delete cache data failed. (' . $e->getMessage () . ')' );
		}
	}

	/**
	 * 构造保存的数据
	 *
	 * @param string $value 保存缓存数据。
	 * @param string $type 缓存类型
	 * @param int $expires 缓存数据的过期时间,0表示永不过期
	 */
	protected function build_data($value, $expires = 0) {
		if (! is_array ( $value ) && empty ( $value )) $value = array ();
		$data = array (self::DATA => $value,self::EXPIRE => $expires ? $expires : $this->get_expire (),self::STORETIME => TIME );
		return $data;
	}

	/**
	 * 格式化输出
	 *
	 * 将从缓存对象中获得的缓存源数据进行格式化输出.该源数据是一个格式良好的数组的序列化字符串,需要反序列化获得源数组.
	 * 如果没有数据,则返回false
	 * 如果含有数据,则返回该数据
	 *
	 * @param string $key 缓存的key值
	 * @param string $value 缓存的数据的序列化值
	 * @return mixed 返回保存的真实数据,如果没有数值则返回false
	 */
	protected function format_data($key, $value) {
		if (! $value) return false;
		$data = unserialize ( $value );
		return $this->has_changed ( $key, $data ) ? false : $data [self::DATA];
	}

	/**
	 * 判断数据是否已经被更新
	 *
	 * 如果缓存中有数据,则检查缓存依赖是否已经变更,如果变更则删除缓存,并且返回true.
	 * 如果没有更新则返回false.
	 *
	 * @param string $key 缓存的key
	 * @param array $data 缓存中的数据
	 * @return boolean true表示缓存已变更,false表示缓存未变改
	 */
	protected function has_changed($key, array $data) {
		if ($data [self::EXPIRE]) {
			$_over_time = $data [self::EXPIRE] + $data [self::STORETIME];
			if ($_over_time >= TIME) return false;
		} else
			return false;
		$this->delete ( $key );
		return true;
	}

	/**
	 * 设置配置信息
	 *
	 * @param array $config 缓存配置信息
	 */
	protected function set_config($options = array()) {
		$this->set_expire ( $options ['expire'] );
	}

	/**
	 * 设置缓存过期时间
	 *
	 * 单位为秒,默认为0永不过期
	 *
	 * @param int $expire 缓存过期时间,单位为秒,默认为0永不过期
	 */
	public function set_expire($expire) {
		$this->expire = intval ( $expire );
	}

	/**
	 * 返回过期时间设置
	 *
	 * 单位为秒，默认值为0永不过期
	 *
	 * @return int $expire 缓存过期时间，默认为0永不过期，单位为秒
	 */
	public function get_expire() {
		return $this->expire;
	}
}