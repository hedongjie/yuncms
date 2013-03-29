<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-1
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Mongodb.php 19 2012-11-05 10:09:53Z xutongle $
 */
class Mongodb {

	/**
	 * 单例模式实例化对象
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * 数据库连接ID
	 *
	 * @var object
	 */
	protected $db_link;

	/**
	 * mongo实例化对象
	 *
	 * @var object
	 */
	protected $_mongo;

	/**
	 * 构造函数
	 *
	 * @access public
	 * @param array $params
	 *        	数据库连接参数,如主机名,数据库用户名,密码等
	 * @return boolean
	 */
	public function __construct(array $params = null) {
		if (! extension_loaded ( 'mongo' )) throw new Core_Exception ( 'The mongo extension must be loaded!' );
		// 参数分析
		if (empty ( $params ) || ! is_array ( $params )) $params = C ( 'mongodb' ); // 加载数据库配置文件.
		if (empty ( $params )) {
			$params ['dsn'] = 'mongodb://localhost:27017';
			$params ['option'] = array ('connect' => true );
		} else {
			// 分析dsn信息
			if (! $params ['dsn']) $params ['dsn'] = 'mongodb://' . trim ( $params ['host'] ) . ':' . ($params ['port'] ? $params ['port'] : '27017');

			$params ['option'] = (! $params ['option']) ? array ('connect' => true ) : trim ( $params ['option'] );
		}
		// 实例化mongo
		$this->_mongo = new Mongo ( $params ['dsn'], $params ['option'] );
		if ($params ['dbname']) $this->db_link = $this->_mongo->selectDB ( $params ['dbname'] );

		// 用户登录
		if ($params ['username'] && $params ['password']) {
			$result = $this->db_link->authenticate ( $params ['username'], $params ['password'] );
			if (! $result) throw new Core_Exception ( 'Mongo Auth Failed: bad user or password.' );
		}
		return true;
	}

	/**
	 * Select Collection
	 *
	 * @author ColaPHP
	 * @param string $collection
	 * @return MogoCollection
	 */
	public function collection($collection) {
		return $this->db_link->selectCollection ( $collection );
	}

	/**
	 * 查询一条记录
	 *
	 * @access public
	 * @param string $collnections
	 * @param array $query
	 *        	相当于key=value
	 * @param array $filed
	 * @return array
	 */
	public function fetch_row($collnections, $query, $filed = array()) {
		return $this->collection ( $collnections )->findOne ( $query, $filed );
	}

	/**
	 * 查询多条记录
	 *
	 * @access public
	 * @param string $collnections
	 * @param array $query
	 *        	相当于key=value
	 * @param array $filed
	 * @return array
	 */
	public function fetch_all($collnections, $query, $filed = array()) {
		$result = array ();
		$cursor = $this->collection ( $collnections )->find ( $query, $filed );
		while ( $cursor->hasNext () ) {
			$result [] = $cursor->getNext ();
		}
		return $result;
	}

	/**
	 * 插入数据
	 *
	 * @access public
	 * @param string $collnections
	 * @param array $data_array
	 * @return boolean
	 */
	public function insert($collnections, $data_array) {
		return $this->collection ( $collnections )->insert ( $data_array );
	}

	/**
	 * 更改数据
	 *
	 * @access public
	 * @param string $collnections
	 * @param array $query
	 * @param array $update_data
	 * @param array $options
	 * @return boolean
	 */
	public function update($collection, $query, $update_data, $options = array('safe'=>true,'multiple'=>true)) {
		return $this->collection ( $collection )->update ( $query, $update_data, $options );
	}

	/**
	 * 删除数据
	 *
	 * @access public
	 * @param string $collnections
	 * @param array $query
	 * @param array $option
	 * @return unknow
	 */
	public function delete($collection, $query, $option = array("justOne"=>false)) {
		return $this->collection ( $collection )->remove ( $query, $option );
	}

	/**
	 * MongoId
	 *
	 * @author ColaPHP
	 * @param string $id
	 * @return MongoId
	 */
	public static function id($id = null) {
		return new MongoId ( $id );
	}

	/**
	 * MongoTimestamp
	 *
	 * @author ColaPHP
	 * @param int $sec
	 * @param int $inc
	 * @return MongoTimestamp
	 */
	public static function Timestamp($sec = null, $inc = 0) {
		if (! $sec) $sec = time ();
		return new MongoTimestamp ( $sec, $inc );
	}

	/**
	 * GridFS
	 *
	 * @author ColaPHP
	 * @return MongoGridFS
	 */
	public function gridFS($prefix = 'fs') {
		return $this->db_link->getGridFS ( $prefix );
	}

	/**
	 * 析构函数
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		if ($this->_mongo) $this->_mongo->close ();
	}

	/**
	 * 本类单例实例化函数
	 *
	 * @access public
	 * @param array $params
	 *        	数据库连接参数,如数据库服务器名,用户名,密码等
	 * @return object
	 */
	public static function get_instance($params) {
		if (! self::$instance) self::$instance = new self ( $params );
		return self::$instance;
	}
}