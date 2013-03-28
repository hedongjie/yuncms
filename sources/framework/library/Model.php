<?php
/**
 * 系统模型基类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-26
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Model.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Model {

	/**
	 * 调用数据库的配置项
	 *
	 * @var string/array 可以是数组或字符串
	 */
	protected $setting = 'default';

	/**
	 * 数据表名
	 *
	 * @var string
	 */
	protected $table_name = null;

	/**
	 * 数据连接句柄
	 */
	protected $db = null;

	/**
	 * 是否自动检测数据表字段信息
	 */
	protected $auto_check_fields = true;

	/**
	 * 表前缀
	 *
	 * @var string
	 */
	protected $prefix = null;

	/**
	 * 字段信息
	 *
	 * @var array
	 */
	protected $fields = array ();

	/**
	 * 默认主键
	 *
	 * @var string
	 */
	protected $pk = 'id';

	/**
	 * 参数数组
	 */
	protected $options = array ();

	/**
	 * 链操作方法列表
	 *
	 * @var array
	 */
	protected $methods = array ('table','order','alias','having','group','lock','distinct','auto','filter','validate' );
	public function __construct($table = null) {
		// 模型初始化
		$this->_initialize ();
		$this->db = Loader::db ( $this->setting );
		$this->database = $this->db->get_database (); // 获取数据库名
		$this->prefix = $this->db->get_prefix (); // 获取表前缀
		if (is_null ( $table ))
			$this->table_name = $this->prefix . $this->table_name;
		else
			$this->table_name = $this->prefix . $table;
			// 字段检查
		if ($this->auto_check_fields && $this->table_name != $this->prefix) $this->_check_table_info ();
	}

	/**
	 * 自动检测数据表信息
	 *
	 * @return void
	 */
	protected function _check_table_info() {
		// 只在第一次执行记录
		if (empty ( $this->fields )) { // 如果数据表字段没有定义则自动获取
			if (C ( 'config', 'db_fields_cache' )) {
				$fields = S ( '_fields/' . strtolower ( $this->database . '.' . $this->table_name ) );
				if ($fields) {
					$version = C ( 'config', 'db_fields_version' );
					if (empty ( $version ) || (isset ( $fields ['_version'] ) && $fields ['_version'] == $version)) {
						$this->fields = $fields;
						return;
					}
				}
			}
			// 每次都会读取数据表信息
			$this->flush ();
		}
	}

	/**
	 * 获取字段信息并缓存
	 *
	 * @return void
	 */
	public function flush() {
		$fields = $this->db->get_fields ( $this->table_name );
		if (! $fields) { // 无法获取字段信息
			return false;
		}
		$this->fields = array_keys ( $fields );
		$this->fields ['_autoinc'] = false;
		foreach ( $fields as $key => $val ) {
			// 记录字段类型
			$type [$key] = $val ['type'];
			if ($val ['primary']) {
				$this->fields ['_pk'] = $key;
				if ($val ['autoinc']) $this->fields ['_autoinc'] = true;
			}
		}
		// 记录字段类型信息
		$this->fields ['_type'] = $type;
		if (C ( 'config', 'db_fields_version' )) $this->fields ['_version'] = C ( 'config', 'db_fields_version' );
		if (C ( 'config', 'db_fields_cache' )) {
			// 永久缓存数据表信息
			S ( '_fields/' . strtolower ( $this->database . '.' . $this->table_name ), $this->fields );
		}
	}

	/**
	 * 获取表前缀
	 */
	final public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * 直接执行sql查询
	 *
	 * @param $sql 查询sql语句
	 * @return array/boolean 查询到结果
	 */
	final public function query($sql) {
		return $this->db->query ( $sql );
	}

	/**
	 * 执行一条sql语句 同时返回影响行数
	 *
	 * @param string $sql sql语句
	 * @return int
	 */
	final public function execute($sql) {
		return $this->db->execute ( $sql );
	}

	/**
	 * 执行添加记录操作
	 *
	 * @param $data 要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param array $options 表达式
	 * @param $replace 是否采用 replace into的方式添加数据
	 * @return boolean 返回新建ID号
	 */
	final public function insert($data, $return_insert_id = false, $replace = false) {
		if (! is_array ( $data ) || $this->table_name == '' || count ( $data ) == 0) return false;
		// 验证完成生成数据对象
		if ($this->auto_check_fields) { // 开启字段检测 则过滤非法字段数据
			$fields = $this->get_fields ();
			foreach ( $data as $key => $val ) {
				if (! in_array ( $key, $fields )) {
					unset ( $data [$key] );
				} elseif (MAGIC_QUOTES_GPC && is_string ( $val )) {
					$data [$key] = stripslashes ( $val );
				}
			}
		}
		// 分析表达式
		$options = $this->_parse_options ();
		$result = $this->db->insert ( $data, $options, $replace );
		return $return_insert_id ? $this->insert_id () : true;
	}

	/**
	 * 执行删除记录操作
	 *
	 * @param $options 表达式
	 * @return boolean
	 */
	final public function delete($options = array()) {
		if (is_numeric ( $options ) || is_string ( $options )) {
			// 根据主键删除记录
			$pk = $this->get_pk ();
			if (strpos ( $options, ',' )) {
				$where [$pk] = array ('IN',$options );
			} else {
				$where [$pk] = $options;
			}
			$options = array ();
			$options ['where'] = $where;
		}
		// 分析表达式
		$options = $this->_parse_options ( $options );
		return $this->db->delete ( $options );
	}

	/**
	 * 执行更新记录操作
	 *
	 * @param $data 要更新的数据内容，参数可以为数组也可以为字符串，建议数组。 为数组时数组key为字段值，数组值为数据取值
	 *        为字符串时[例：`name`='yuncms',`hits`=`hits`+1]。
	 *        为数组时[例: array('name'=>'yuncms','password'=>'123456')]
	 *        数组的另一种使用array('name'=>'+=1', 'base'=>'-=1');程序会自动解析为`name` =
	 *        `name` + 1, `base` = `base` - 1
	 * @param $where 更新数据时的条件,可为数组或字符串
	 * @return boolean
	 */
	final public function update($data, $options = array()) {
		// 分析表达式
		$options = $this->_parse_options ( $options );
		return $this->db->update ( $data, $options );
	}

	/**
	 * 执行sql查询
	 * $options 参数
	 *
	 * @return array
	 */
	final public function select($options = array()) {
		if (is_string ( $options ) || is_numeric ( $options )) {
			// 根据主键查询
			$pk = $this->get_pk ();
			if (strpos ( $options, ',' )) {
				$where [$pk] = array ('IN',$options );
			} else {
				$where [$pk] = $options;
			}
			$options = array ();
			$options ['where'] = $where;
		} elseif (false === $options) { // 用于子查询 不查询只返回SQL
			$options = array ();
			// 分析表达式
			$options = $this->_parse_options ( $options );
			return '( ' . $this->db->build_select_sql ( $options ) . ' )';
		}
		// 分析表达式
		$options = $this->_parse_options ( $options );
		return $this->db->select ( $options );
	}

	/**
	 * 查询数据
	 *
	 * @param $options 表达式参数
	 * @return array/null
	 */
	final public function find($options = array()) {
		if (is_numeric ( $options ) || is_string ( $options )) {
			$where [$this->get_pk ()] = $options;
			$options = array ();
			$options ['where'] = $where;
		}
		// 总是查找一条记录
		$options ['limit'] = 1;
		// 分析表达式
		$options = $this->_parse_options ( $options );

		$result_set = $this->db->select ( $options );
		if (false === $result_set) {
			return false;
		}
		if (empty ( $result_set )) { // 查询结果为空
			return null;
		}
		return $result_set [0];
	}

	/**
	 * 获取数据表字段信息
	 *
	 * @return array
	 */
	public function get_fields($table = '') {
		if (! empty ( $table )) { // 动态指定表名
			$fields = $this->db->get_fields ( $this->prefix . $table );
			return $fields ? array_keys ( $fields ) : false;
		}
		if ($this->fields) {
			$fields = $this->fields;
			unset ( $fields ['_autoinc'], $fields ['_pk'], $fields ['_type'], $fields ['_version'] );
			return $fields;
		}
		return false;
	}
	final public function get_tables($table = '') {
		return $this->db->get_tables ( $table );
	}

	/**
	 * 检查表是否存在
	 *
	 * @param $table 表名
	 * @return boolean
	 */
	final public function table_exists($table) {
		$tables = $this->get_tables ();
		$return = in_array ( $this->prefix . $table, $tables );
		return $return ? true : false;
	}

	/**
	 * 启动事务
	 *
	 * @return void
	 */
	public function starttrans() {
		$this->commit ();
		$this->db->starttrans ();
		return;
	}

	/**
	 * 提交事务
	 *
	 * @return boolean
	 */
	public function commit() {
		return $this->db->commit ();
	}

	/**
	 * 事务回滚
	 *
	 * @return boolean
	 */
	public function rollback() {
		return $this->db->rollback ();
	}

	/**
	 * 获取最后一次添加记录的主键号
	 *
	 * @return int
	 */
	final public function insert_id() {
		return $this->db->get_lastinsid ();
	}

	/**
	 * 获取最后一次执行的 SQL语句
	 *
	 * @return string
	 */
	final public function get_lastsql() {
		return $this->db->get_lastsql ();
	}

	/**
	 * 获取主键名称
	 *
	 * @access public
	 * @return string
	 */
	public function get_pk() {
		return isset ( $this->fields ['_pk'] ) ? $this->fields ['_pk'] : $this->pk;
	}

	/**
	 * 返回数据库版本号
	 */
	final public function version() {
		return $this->db->version ();
	}

	/**
	 * 获取一条记录的某个字段值
	 *
	 * @param string $field 字段名
	 * @param string $spea 字段数据间隔符号 NULL返回数组
	 * @return mixed
	 */
	public function get_field($field, $sepa = null) {
		$options ['field'] = $field;
		$options = $this->_parse_options ( $options );
		if (isset ( $options ['key'] )) unset ( $options ['key'] );
		$field = trim ( $field );
		if (strpos ( $field, ',' )) { // 多字段
			if (! isset ( $options ['limit'] )) {
				$options ['limit'] = is_numeric ( $sepa ) ? $sepa : '';
			}
			$resultSet = $this->db->select ( $options );
			if (! empty ( $resultSet )) {
				$_field = explode ( ',', $field );
				$field = array_keys ( $resultSet [0] );
				$key = array_shift ( $field );
				$key2 = array_shift ( $field );
				$cols = array ();
				$count = count ( $_field );
				foreach ( $resultSet as $result ) {
					$name = $result [$key];
					if (2 == $count) {
						$cols [$name] = $result [$key2];
					} else {
						$cols [$name] = is_string ( $sepa ) ? implode ( $sepa, $result ) : $result;
					}
				}
				return $cols;
			}
		} else { // 查找一条记录
		         // 返回数据个数
			if (true !== $sepa) { // 当sepa指定为true的时候 返回所有数据
				$options ['limit'] = is_numeric ( $sepa ) ? $sepa : 1;
			}
			$result = $this->db->select ( $options );
			if (! empty ( $result )) {
				if (true !== $sepa && 1 == $options ['limit']) return reset ( $result [0] );
				foreach ( $result as $val ) {
					$array [] = $val [$field];
				}
				return $array;
			}
		}
		return null;
	}
	public function get_table() {
		return $this->table_name;
	}

	/**
	 * 分析表达式
	 *
	 * @param array $options 表达式参数
	 * @return array
	 */
	protected function _parse_options($options = array()) {
		if (is_array ( $options )) $options = array_merge ( $this->options, $options );
		// 查询过后清空sql表达式组装 避免影响下次查询
		$this->options = array ();
		if (! isset ( $options ['table'] )) {
			// 自动获取表名
			$options ['table'] = $this->get_table ();
		}
		if (! empty ( $options ['alias'] )) { // 数据表别名
			$options ['table'] .= ' ' . $options ['alias'];
		}
		return $options;
	}
	// -------------------------------------------------------------------------------------------------
	// 一下为链式语法相关实现
	/**
	 * 查询SQL组装 join
	 *
	 * @param mixed $join
	 * @return Model
	 */
	public function join($join) {
		if (is_array ( $join )) {
			$this->options ['join'] = $join;
		} elseif (! empty ( $join )) {
			$this->options ['join'] [] = $join;
		}
		return $this;
	}

	/**
	 * 查询SQL组装 union
	 *
	 * @param mixed $union
	 * @param boolean $all
	 * @return Model
	 */
	public function union($union, $all = false) {
		if (empty ( $union )) return $this;
		if ($all) {
			$this->options ['union'] ['_all'] = true;
		}
		if (is_object ( $union )) {
			$union = get_object_vars ( $union );
		}
		// 转换union表达式
		if (is_string ( $union )) {
			$options = $union;
		} elseif (is_array ( $union )) {
			if (isset ( $union [0] )) {
				$this->options ['union'] = array_merge ( $this->options ['union'], $union );
				return $this;
			} else {
				$options = $union;
			}
		} else {
			throw_exception ( 'Data type is invalid.' );
		}
		$this->options ['union'] [] = $options;
		return $this;
	}

	/**
	 * 指定查询条件 支持安全过滤
	 *
	 * @param mixed $where 条件表达式
	 * @param mixed $parse 预处理参数
	 * @return Model
	 */
	public function where($where, $parse = null) {
		if (! $where) return $this;
		if (! is_null ( $parse ) && is_string ( $where )) {
			if (! is_array ( $parse )) {
				$parse = func_get_args ();
				array_shift ( $parse );
			}
			$parse = array_map ( array ($this->db,'escape_string' ), $parse );
			$where = vsprintf ( $where, $parse );
		} elseif (is_object ( $where )) {
			$where = get_object_vars ( $where );
		}
		if (is_string ( $where )) {
			$map = array ();
			$map ['_string'] = $where;
			$where = $map;
		}
		if (isset ( $this->options ['where'] )) {
			$this->options ['where'] = array_merge ( $this->options ['where'], $where );
		} else {
			$this->options ['where'] = $where;
		}
		return $this;
	}

	/**
	 * 指定查询数量
	 *
	 * @param mixed $offset 起始位置
	 * @param mixed $length 查询数量
	 * @return Model
	 */
	public function limit($offset, $length = null) {
		$this->options ['limit'] = is_null ( $length ) ? $offset : $offset . ',' . $length;
		return $this;
	}

	/**
	 * 查询注释
	 *
	 * @param string $comment 注释
	 * @return Model
	 */
	public function comment($comment) {
		$this->options ['comment'] = $comment;
		return $this;
	}

	/**
	 * 查询返回按键名排序
	 *
	 * @param string $key 键名
	 * @return Model
	 */
	public function key($key = '') {
		if (! empty ( $key )) $this->options ['key'] = $key;
		return $this;
	}

	/**
	 * 查询缓存
	 *
	 * @param mixed $key 缓存Key
	 * @param integer $expire 有效期
	 * @param string $setting 加载的设置
	 * @return Model
	 */
	public function cache($key = true, $expire = null, $setting = '') {
		if (empty ( $expire )) $expire = C ( 'config', 'db_cache_expire' );
		if (empty ( $setting )) $setting = C ( 'config', 'db_cache_setting' );
		$this->options ['cache'] = array ('key' => $key,'expire' => $expire,'setting' => $setting );
		return $this;
	}

	/**
	 * 利用__call方法实现一些特殊的Model方法
	 *
	 * @param string $method 方法名称
	 * @param array $args 调用参数
	 * @return mixed
	 */
	public function __call($method, $args) {
		if (in_array ( strtolower ( $method ), $this->methods, true )) {
			// 连贯操作的实现
			$this->options [strtolower ( $method )] = $args [0];
			return $this;
		} elseif (in_array ( strtolower ( $method ), array ('count','sum','min','max','avg' ), true )) {
			// 统计查询的实现
			$field = isset ( $args [0] ) ? $args [0] : '*';
			return $this->get_field ( strtoupper ( $method ) . '(' . $field . ') AS leaps_' . $method );
		} elseif (strtolower ( substr ( $method, 0, 6 ) ) == 'getby_') {
			// 根据某个字段获取记录
			$field = parse_name ( substr ( $method, 6 ) );
			$where [$field] = $args [0];
			return $this->where ( $where )->find ();
		} else {
			throw_exception ( __CLASS__ . ':' . $method . ' Method noe exist.' );
			return;
		}
	}

	// 回调方法 初始化模型
	protected function _initialize() {
	}

	/**
	 * 指定分页
	 *
	 * @param mixed $page 页数
	 * @param mixed $list_rows 每页数量
	 * @return Model
	 */
	public function page($page, $list_rows = null) {
		$this->options ['page'] = is_null ( $list_rows ) ? $page : $page . ',' . $list_rows;
		return $this;
	}

	/**
	 * 指定查询字段 支持字段排除
	 *
	 * @param mixed $field
	 * @param boolean $except 是否排除
	 * @return Model
	 */
	public function field($field, $except = false) {
		if (true === $field) { // 获取全部字段
			$fields = $this->get_fields ();
			$field = $fields ? $fields : '*';
		} elseif ($except) { // 字段排除
			if (is_string ( $field )) {
				$field = explode ( ',', $field );
			}
			$fields = $this->get_fields ();
			$field = $fields ? array_diff ( $fields, $field ) : $field;
		}
		$this->options ['field'] = $field;
		return $this;
	}

	/**
	 * 获取查询条件字符串
	 * @param string $where 查询条件
	 */
	public function get_where($where = ''){
		if(empty($where)) $where = $this->options ['where'];
		return $this->db->parse_where($where);
	}

	/**
	 * 查询多条数据并分页
	 *
	 * @param $where 条件
	 * @param $order 排序
	 * @param $page 页码
	 * @param $pagesize 每页数量
	 * @param $key 返回数组按键名排序
	 * @return array
	 */
	final public function listinfo($page = 1, $pagesize = 20, $key = '', $setpages = 10, $urlrule = '', $array = array()) {
		// ps:复制下数组 统计完总数后options会被清空，此处复制下放下面分页用
		$options = $this->options;
		// 获取总数
		$this->number = $this->count ();
		$page = max ( intval ( $page ), 1 );
		$offset = $pagesize * ($page - 1);
		$this->pages = Page::pages ( $this->number, $page, $pagesize, $urlrule, $array, $setpages );
		$array = array ();
		if ($this->number > 0) {
			return $this->limit ( $offset, $pagesize )->select ( $options );
		} else {
			return array ();
		}
	}
}