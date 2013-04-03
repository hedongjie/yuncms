<?php
/**
 * Leaps 数据库中间层
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: DB.php 2 2013-01-14 07:14:05Z xutongle $
 */
abstract class Core_DB {

	/**
	 * 数据库配置
	 *
	 * @var array
	 */
	public $config = array ();

	/**
	 * 数据库连接资源句柄
	 */
	public $link = null;

	/**
	 * 当前SQL指令
	 *
	 * @var string
	 */
	protected $query_str = '';

	/**
	 * 最近一次查询资源句柄
	 */
	public $lastqueryid = null;

	/**
	 * 最后插入ID
	 *
	 * @var int
	 */
	protected $lastinsid = null;

	/**
	 * 事务指令数
	 */
	protected $trans_times = 0;

	/**
	 * 数据库名称
	 *
	 * @var string
	 */
	protected $database = null;

	/**
	 * 表前缀
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * 数据库表达式
	 *
	 * @var array
	 */
	protected $comparison = array ('eq' => '=','neq' => '<>','gt' => '>','egt' => '>=','lt' => '<','elt' => '<=','notlike' => 'NOT LIKE','like' => 'LIKE','in' => 'IN','notin' => 'NOT IN' );

	/**
	 * 查询表达式
	 *
	 * @var string
	 */
	protected $select_sql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';

	/**
	 * 打开数据库连接,有可能不真实连接数据库
	 *
	 * @param $config 数据库连接参数
	 * @return void
	 */
	public function open() {
		if ($this->config ['autoconnect'] == 1) {
			$this->connect ();
		}
	}

	/**
	 * 获取数据库名称
	 */
	public function get_database() {
		return $this->database;
	}

	/**
	 * 设置表前缀
	 *
	 * @param string $prefix
	 */
	public function set_prefix($prefix) {
		$this->prefix = $prefix;
	}

	/**
	 * 获取表前缀
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * 数据库调试 记录当前SQL
	 */
	protected function debug() {
		// 记录操作结束时间
		if (C ( 'config', 'db_sql_log' )) {
			G ( 'queryEndTime' );
			trace ( $this->query_str . ' [ RunTime:' . G ( 'queryStartTime', 'queryEndTime', 6 ) . 's ]', '', 'SQL' );
		}
	}

	/**
	 * 插入记录
	 *
	 * @param mixed $data 数据
	 * @param array $options 参数表达式
	 * @param boolean $replace 是否replace
	 * @return false | integer
	 */
	public function insert($data, $options = array(), $replace = false) {
		$values = $fields = array ();
		foreach ( $data as $key => $val ) {
			$value = $this->parse_value ( $val );
			if (is_scalar ( $value )) { // 过滤非标量数据
				$values [] = $value;
				$fields [] = $this->parse_key ( $key );
			}
		}
		$sql = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->parse_table ( $options ['table'] ) . ' (' . implode ( ',', $fields ) . ') VALUES (' . implode ( ',', $values ) . ')';
		$sql .= $this->parse_lock ( isset ( $options ['lock'] ) ? $options ['lock'] : false );
		$sql .= $this->parse_comment ( ! empty ( $options ['comment'] ) ? $options ['comment'] : '' );
		return $this->execute ( $sql );
	}

	/**
	 * 更新记录
	 *
	 * @param mixed $data 数据
	 * @param array $options 表达式
	 * @return false | integer
	 */
	public function update($data, $options) {
		$sql = 'UPDATE ' . $this->parse_table ( $options ['table'] ) . $this->parse_set ( $data ) . $this->parse_where ( ! empty ( $options ['where'] ) ? $options ['where'] : '' ) . $this->parse_order ( ! empty ( $options ['order'] ) ? $options ['order'] : '' ) . $this->parse_limit ( ! empty ( $options ['limit'] ) ? $options ['limit'] : '' ) . $this->parse_lock ( isset ( $options ['lock'] ) ? $options ['lock'] : false ) . $this->parse_comment ( ! empty ( $options ['comment'] ) ? $options ['comment'] : '' );
		return $this->execute ( $sql );
	}

	/**
	 * 删除记录
	 *
	 * @access public
	 * @param array $options 表达式
	 * @return false | integer
	 */
	public function delete($options = array()) {
		$sql = 'DELETE FROM ' . $this->parse_table ( $options ['table'] ) . $this->parse_where ( ! empty ( $options ['where'] ) ? $options ['where'] : '' ) . $this->parse_order ( ! empty ( $options ['order'] ) ? $options ['order'] : '' ) . $this->parse_limit ( ! empty ( $options ['limit'] ) ? $options ['limit'] : '' ) . $this->parse_lock ( isset ( $options ['lock'] ) ? $options ['lock'] : false ) . $this->parse_comment ( ! empty ( $options ['comment'] ) ? $options ['comment'] : '' );
		return $this->execute ( $sql );
	}

	/**
	 * 执行sql查询
	 *
	 * @param array $options 表达式
	 * @return array
	 */
	public function select($options = array()) {
		$sql = $this->build_select_sql ( $options ); // 生成查询SQL
		$cache = isset ( $options ['cache'] ) ? $options ['cache'] : false;
		if ($cache) { // 查询缓存检测
			$key = is_string ( $cache ['key'] ) ? $cache ['key'] : md5 ( $sql );
			$value = S ( '_sql_result/' . $key );
			if (false !== $value) {
				return $value;
			}
		}
		$result = $this->query ( $sql, isset ( $options ['key'] ) ? $options ['key'] : '' );
		if ($cache && false !== $result) { // 查询缓存写入
			if (is_array ( $cache )) {
				S ( '_sql_result/' . $key, $result, $cache ['expire'], $cache ['setting'] );
			} else {
				S ( '_sql_result/' . $key, $result, $cache );
			}
		}
		return $result;
	}

	/**
	 * 生成查询SQL
	 *
	 * @param array $options 表达式
	 * @return string
	 */
	public function build_select_sql($options = array()) {
		if (C ( 'config', 'db_sql_build_cache' )) { // SQL创建缓存
			$key = md5 ( serialize ( $options ) );
			$value = S ( '_sql/' . $key );
			if (false !== $value) {
				return $value;
			}
		}
		$sql = $this->parse_sql ( $this->select_sql, $options );
		$sql .= $this->parse_lock ( isset ( $options ['lock'] ) ? $options ['lock'] : false );
		if (isset ( $key )) { // 写入SQL创建缓存
			S ( '_sql/' . $key, $sql );
		}
		return $sql;
	}

	/**
	 * 替换SQL语句中表达式
	 *
	 * @param array $options 表达式
	 * @return string
	 */
	public function parse_sql($sql, $options = array()) {
		$sql = str_replace ( array ('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%COMMENT%' ), array ($this->parse_table ( $options ['table'] ),$this->parse_distinct ( isset ( $options ['distinct'] ) ? $options ['distinct'] : false ),
																																								$this->parse_field ( ! empty ( $options ['field'] ) ? $options ['field'] : '*' ),
																																								$this->parse_join ( ! empty ( $options ['join'] ) ? $options ['join'] : '' ),
																																								$this->parse_where ( ! empty ( $options ['where'] ) ? $options ['where'] : '' ),
																																								$this->parse_group ( ! empty ( $options ['group'] ) ? $options ['group'] : '' ),
																																								$this->parse_having ( ! empty ( $options ['having'] ) ? $options ['having'] : '' ),
																																								$this->parse_order ( ! empty ( $options ['order'] ) ? $options ['order'] : '' ),
																																								$this->parse_limit ( ! empty ( $options ['limit'] ) ? $options ['limit'] : '' ),
																																								$this->parse_union ( ! empty ( $options ['union'] ) ? $options ['union'] : '' ),
																																								$this->parse_comment ( ! empty ( $options ['comment'] ) ? $options ['comment'] : '' ) ), $sql );
		return $sql;
	}

	/**
	 * 设置锁机制
	 *
	 * @return string
	 */
	protected function parse_lock($lock = false) {
		if (! $lock) return '';
		if ('ORACLE' == ucfirst ( strtolower ( $this->config ['driver'] ) )) {
			return ' FOR UPDATE NOWAIT ';
		}
		return ' FOR UPDATE ';
	}

	/**
	 * set分析
	 *
	 * @param array $data
	 * @return string
	 */
	protected function parse_set($data) {
		if (is_string ( $data ) && $data != '') {
			$set = $data;
		} elseif (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $key => $val ) {
				switch (substr ( $val, 0, 2 )) {
					case '+=' :
						$v = substr ( $val, 2 );
						if (is_numeric ( $v )) {
							$set [] = $this->parse_key ( $key ) . '=' . $this->parse_key ( $key ) . '+' . $this->parse_value ( $v );
						} else {
							continue;
						}
						break;
					case '-=' :
						$v = substr ( $val, 2 );
						if (is_numeric ( $v )) {
							$set [] = $this->parse_key ( $key ) . '=' . $this->parse_key ( $key ) . '-' . $this->parse_value ( $v );
						} else {
							continue;
						}
						break;
					default :
						$value = $this->parse_value ( $val );
						if (is_scalar ( $value )) 						// 过滤非标量数据
						$set [] = $this->parse_key ( $key ) . '=' . $value;
				}
			}
			$set = implode ( ',', $set );
		}
		return ' SET ' . $set;
	}

	/**
	 * table分析
	 *
	 * @param mixed $table
	 * @return string
	 */
	protected function parse_table($tables) {
		if (is_array ( $tables )) { // 支持别名定义
			$array = array ();
			foreach ( $tables as $table => $alias ) {
				if (! is_numeric ( $table ))
					$array [] = $this->parse_key ( $table ) . ' ' . $this->parse_key ( $alias );
				else
					$array [] = $this->parse_key ( $table );
			}
			$tables = $array;
		} elseif (is_string ( $tables )) {
			$tables = $this->parse_prefix ( $tables ); // 自动替换表前缀
			$tables = explode ( ',', $tables );
			array_walk ( $tables, array (&$this,'parse_key' ) );
		}
		return implode ( ',', $tables );
	}

	/**
	 * 表前缀分析
	 *
	 * @param string $table
	 */
	protected function parse_prefix($table) {
		$table = str_replace ( '#prefix#', $this->get_prefix (), $table ); // 自动替换表前缀
		return $table;
	}

	/**
	 * field分析
	 *
	 * @access protected
	 * @param mixed $fields
	 * @return string
	 */
	protected function parse_field($fields) {
		if (is_string ( $fields ) && strpos ( $fields, ',' )) {
			$fields = explode ( ',', $fields );
		}
		if (is_array ( $fields )) {
			// 完善数组方式传字段名的支持
			// 支持 'field1'=>'field2' 这样的字段别名定义
			$array = array ();
			foreach ( $fields as $key => $field ) {
				if (! is_numeric ( $key ))
					$array [] = $this->parse_key ( $key ) . ' AS ' . $this->parse_key ( $field );
				else
					$array [] = $this->parse_key ( $field );
			}
			$fields_str = implode ( ',', $array );
		} elseif (is_string ( $fields ) && ! empty ( $fields )) {
			$fields_str = $this->parse_key ( $fields );
		} else {
			$fields_str = '*';
		}
		// TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
		return $fields_str;
	}

	/**
	 * join分析
	 *
	 * @param mixed $join
	 * @return string
	 */
	protected function parse_join($join) {
		$join_str = '';
		if (! empty ( $join )) {
			if (is_array ( $join )) {
				foreach ( $join as $key => $_join ) {
					if (false !== stripos ( $_join, 'JOIN' ))
						$join_str .= ' ' . $_join;
					else
						$join_str .= ' LEFT JOIN ' . $_join;
				}
			} else {
				$join_str .= ' LEFT JOIN ' . $join;
			}
		}
		// 将__TABLE_NAME__这样的字符串替换成正规的表名,并且带上前缀和后缀
		$join_str = preg_replace ( "/__([A-Z_-]+)__/esU", $this->get_prefix () . ".strtolower('$1')", $join_str );
		return $join_str;
	}

	/**
	 * where分析
	 *
	 * @param mixed $where
	 * @return string
	 */
	protected function parse_where($where) {
		$where_str = '';
		if (is_string ( $where )) {
			// 直接使用字符串条件
			$where_str = $where;
		} else { // 使用数组表达式
			$operate = isset ( $where ['_logic'] ) ? strtoupper ( $where ['_logic'] ) : '';
			if (in_array ( $operate, array ('AND','OR','XOR' ) )) {
				// 定义逻辑运算规则 例如 OR XOR AND NOT
				$operate = ' ' . $operate . ' ';
				unset ( $where ['_logic'] );
			} else {
				// 默认进行 AND 运算
				$operate = ' AND ';
			}
			foreach ( $where as $key => $val ) {
				$where_str .= '( ';
				if (0 === strpos ( $key, '_' )) {
					// 解析特殊条件表达式
					$where_str .= $this->parse_leaps_where ( $key, $val );
				} else {
					// 查询字段的安全过滤
					if (! preg_match ( '/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/', trim ( $key ) )) {
						throw_exception ( 'Express error:' . $key );
					}
					// 多条件支持
					$multi = is_array ( $val ) && isset ( $val ['_multi'] );
					$key = trim ( $key );
					if (strpos ( $key, '|' )) { // 支持 name|title|nickname 方式定义查询字段
						$array = explode ( '|', $key );
						$str = array ();
						foreach ( $array as $m => $k ) {
							$v = $multi ? $val [$m] : $val;
							$str [] = '(' . $this->parse_where_item ( $this->parse_key ( $k ), $v ) . ')';
						}
						$where_str .= implode ( ' OR ', $str );
					} elseif (strpos ( $key, '&' )) {
						$array = explode ( '&', $key );
						$str = array ();
						foreach ( $array as $m => $k ) {
							$v = $multi ? $val [$m] : $val;
							$str [] = '(' . $this->parse_where_item ( $this->parse_key ( $k ), $v ) . ')';
						}
						$where_str .= implode ( ' AND ', $str );
					} else {
						$where_str .= $this->parse_where_item ( $this->parse_key ( $key ), $val );
					}
				}
				$where_str .= ' )' . $operate;
			}
			$where_str = substr ( $where_str, 0, - strlen ( $operate ) );
		}
		return empty ( $where_str ) ? '' : ' WHERE ' . $where_str;
	}

	protected function parse_where_item($key, $val) {
		$where_str = '';
		if (is_array ( $val )) {
			if (is_string ( $val [0] )) {
				if (preg_match ( '/^(EQ|NEQ|GT|EGT|LT|ELT)$/i', $val [0] )) { // 比较运算
					$where_str .= $key . ' ' . $this->comparison [strtolower ( $val [0] )] . ' ' . $this->parse_value ( $val [1] );
				} elseif (preg_match ( '/^(NOTLIKE|LIKE)$/i', $val [0] )) { // 模糊查找
					if (is_array ( $val [1] )) {
						$likeLogic = isset ( $val [2] ) ? strtoupper ( $val [2] ) : 'OR';
						if (in_array ( $likeLogic, array ('AND','OR','XOR' ) )) {
							$likeStr = $this->comparison [strtolower ( $val [0] )];
							$like = array ();
							foreach ( $val [1] as $item ) {
								$like [] = $key . ' ' . $likeStr . ' ' . $this->parse_value ( $item );
							}
							$where_str .= '(' . implode ( ' ' . $likeLogic . ' ', $like ) . ')';
						}
					} else {
						$where_str .= $key . ' ' . $this->comparison [strtolower ( $val [0] )] . ' ' . $this->parse_value ( $val [1] );
					}
				} elseif ('exp' == strtolower ( $val [0] )) { // 使用表达式
					$where_str .= ' (' . $key . ' ' . $val [1] . ') ';
				} elseif (preg_match ( '/IN/i', $val [0] )) { // IN 运算
					if (isset ( $val [2] ) && 'exp' == $val [2]) {
						$where_str .= $key . ' ' . strtoupper ( $val [0] ) . ' ' . $val [1];
					} else {
						if (is_string ( $val [1] )) {
							$val [1] = explode ( ',', $val [1] );
						}
						$zone = implode ( ',', $this->parse_value ( $val [1] ) );
						$where_str .= $key . ' ' . strtoupper ( $val [0] ) . ' (' . $zone . ')';
					}
				} elseif (preg_match ( '/BETWEEN/i', $val [0] )) { // BETWEEN运算
					$data = is_string ( $val [1] ) ? explode ( ',', $val [1] ) : $val [1];
					$where_str .= ' (' . $key . ' ' . strtoupper ( $val [0] ) . ' ' . $this->parse_value ( $data [0] ) . ' AND ' . $this->parse_value ( $data [1] ) . ' )';
				} else {
					throw_exception ( 'Express error:' . $val [0] );
				}
			} else {
				$count = count ( $val );
				$rule = isset ( $val [$count - 1] ) ? strtoupper ( $val [$count - 1] ) : '';
				if (in_array ( $rule, array ('AND','OR','XOR' ) )) {
					$count = $count - 1;
				} else {
					$rule = 'AND';
				}
				for($i = 0; $i < $count; $i ++) {
					$data = is_array ( $val [$i] ) ? $val [$i] [1] : $val [$i];
					if ('exp' == strtolower ( $val [$i] [0] )) {
						$where_str .= '(' . $key . ' ' . $data . ') ' . $rule . ' ';
					} else {
						$op = is_array ( $val [$i] ) ? $this->comparison [strtolower ( $val [$i] [0] )] : '=';
						$where_str .= '(' . $key . ' ' . $op . ' ' . $this->parse_value ( $data ) . ') ' . $rule . ' ';
					}
				}
				$where_str = substr ( $where_str, 0, - 4 );
			}
		} else {
			$where_str .= $key . ' = ' . $this->parse_value ( $val );
		}
		return $where_str;
	}

	/**
	 * 特殊条件分析
	 *
	 * @param string $key
	 * @param mixed $val
	 * @return string
	 */
	protected function parse_leaps_where($key, $val) {
		$where_str = '';
		switch ($key) {
			case '_string' :
				// 字符串模式查询条件
				$where_str = $val;
				break;
			case '_complex' :
				// 复合查询条件
				$where_str = substr ( $this->parse_where ( $val ), 6 );
				break;
			case '_query' :
				// 字符串模式查询条件
				parse_str ( $val, $where );
				if (isset ( $where ['_logic'] )) {
					$op = ' ' . strtoupper ( $where ['_logic'] ) . ' ';
					unset ( $where ['_logic'] );
				} else {
					$op = ' AND ';
				}
				$array = array ();
				foreach ( $where as $field => $data )
					$array [] = $this->parse_key ( $field ) . ' = ' . $this->parse_kalue ( $data );
				$where_str = implode ( $op, $array );
				break;
		}
		return $where_str;
	}

	/**
	 * group分析
	 *
	 * @param mixed $group
	 * @return string
	 */
	protected function parse_group($group) {
		return ! empty ( $group ) ? ' GROUP BY ' . $group : '';
	}

	/**
	 * having分析
	 *
	 * @param string $having
	 * @return string
	 */
	protected function parse_having($having) {
		return ! empty ( $having ) ? ' HAVING ' . $having : '';
	}

	/**
	 * order分析
	 *
	 * @param mixed $order
	 * @return string
	 */
	protected function parse_order($order) {
		if (is_array ( $order )) {
			$array = array ();
			foreach ( $order as $key => $val ) {
				if (is_numeric ( $key )) {
					$array [] = $this->parse_key ( $val );
				} else {
					$array [] = $this->parse_key ( $key ) . ' ' . $val;
				}
			}
			$order = implode ( ',', $array );
		}
		return ! empty ( $order ) ? ' ORDER BY ' . $order : '';
	}

	/**
	 * limit分析
	 *
	 * @param mixed $lmit
	 * @return string
	 */
	protected function parse_limit($limit) {
		return ! empty ( $limit ) ? ' LIMIT ' . $limit . ' ' : '';
	}

	/**
	 * union分析
	 *
	 * @param mixed $union
	 * @return string
	 */
	protected function parse_union($union) {
		if (empty ( $union )) return '';
		if (isset ( $union ['_all'] )) {
			$str = 'UNION ALL ';
			unset ( $union ['_all'] );
		} else {
			$str = 'UNION ';
		}
		foreach ( $union as $u ) {
			$sql [] = $str . (is_array ( $u ) ? $this->build_select_sql ( $u ) : $u);
		}
		return implode ( ' ', $sql );
	}

	/**
	 * comment分析
	 *
	 * @param string $comment
	 * @return string
	 */
	protected function parse_comment($comment) {
		return ! empty ( $comment ) ? ' /* ' . $comment . ' */' : '';
	}

	/**
	 * distinct分析
	 *
	 * @param mixed $distinct
	 * @return string
	 */
	protected function parse_distinct($distinct) {
		return ! empty ( $distinct ) ? ' DISTINCT ' : '';
	}

	/**
	 * 生成sql语句，如果传入$in_cloumn 生成格式为 IN('a', 'b', 'c')
	 *
	 * @param $data 条件数组或者字符串
	 * @param $front 连接符
	 * @param $in_column 字段名称
	 * @return string
	 */
	public function parse_where1($data, $front = ' AND ', $in_column = false) {
		if (empty ( $data )) return '';
		if ($in_column && is_array ( $data )) {
			$ids = '\'' . implode ( '\',\'', $data ) . '\'';
			$sql = "$in_column IN ($ids)";
			return $sql;
		} else {
			if ($front == '') $front = ' AND ';
			if (is_array ( $data ) && count ( $data ) > 0) {
				$sql = '';
				foreach ( $data as $key => $val ) {
					if (is_numeric ( $key )) {
						$sql .= $front . ' (' . $this->to_sqls ( $val ) . ')';
						continue;
					}
					if (strtoupper ( $key ) == 'OR') {
						$sql .= ' OR (' . $this->o_sqls ( $val, " $key " ) . ')';
						continue;
					}
					if (is_array ( $val )) {
						$in_str = '';
						foreach ( $val as $one ) {
							if (is_numeric ( $one ))
								$in_str .= ',' . $one;
							else
								$in_str .= ',\'' . $one . '\'';
						}
						$sql .= $front . " $key " . 'IN(' . trim ( $in_str, ',' ) . ')';
						continue;
					}
					$sql .= $sql ? " $front `$key` = '$val' " : " `$key` = '$val' ";
				}
				return ' WHERE ' . $sql;
			} else {
				return ' WHERE ' . $data;
			}
		}
	}

	/**
	 * 获取最近一次查询的sql语句
	 *
	 * @return string
	 */
	public function get_lastsql() {
		return $this->query_str;
	}

	/**
	 * 获取最近插入的ID
	 *
	 * @access public
	 * @return string
	 */
	public function get_lastinsid() {
		return $this->lastinsid;
	}

	/**
	 * 获取最近的错误信息
	 *
	 * @access public
	 * @return string
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * 字段名分析
	 *
	 * @param string $key
	 * @return string
	 */
	protected function parse_key(&$key) {
		if ('*' == $key || false !== strpos ( $key, '(' ) || false !== strpos ( $key, '.' ) || false !== strpos ( $key, '`' )) {
			// 不处理包含* 或者 使用了sql方法。
		} else {
			$key = '`' . trim ( $key ) . '`';
		}
		return $key;
	}

	/**
	 * value分析
	 *
	 * @param mixed $value
	 * @return string
	 */
	protected function parse_value($value) {
		if (is_string ( $value )) {
			$value = '\'' . $this->escape_string ( $value ) . '\'';
		} elseif (isset ( $value [0] ) && is_string ( $value [0] ) && strtolower ( $value [0] ) == 'exp') {
			$value = $this->escape_string ( $value [1] );
		} elseif (is_array ( $value )) {
			$value = array_map ( array ($this,'parse_value' ), $value );
		} elseif (is_bool ( $value )) {
			$value = $value ? '1' : '0';
		} elseif (is_null ( $value )) {
			$value = 'null';
		}
		return $value;
	}

	/**
	 * SQL指令安全过滤
	 *
	 * @param $value 数组值
	 * @param $key 数组key
	 * @param $quotation
	 */
	public function escape_string($value) {
		return addslashes($value);
	}

	/**
	 * 真正开启数据库连接
	 *
	 * @return void
	 */
	abstract public function connect();

	/**
	 * 启动事务
	 *
	 * @return void
	 */
	abstract public function start_trans();

	/**
	 * 用于非自动提交状态下面的查询提交
	 *
	 * @return boolen
	 */
	abstract public function commit();

	/**
	 * 事务回滚
	 *
	 * @return boolen
	 */
	abstract public function rollback();

	/**
	 * 取得数据表的字段信息
	 *
	 * @return array
	 */
	abstract public function get_fields($table);

	/**
	 * 取得数据库的表信息
	 *
	 * @return array
	 */
	abstract public function get_tables($database = '');

	/**
	 * 执行查询 返回数据集
	 *
	 * @param string $str sql指令
	 * @param string $key 是否按主键排序
	 * @return mixed
	 */
	abstract public function query($str, $key = '');

	/**
	 * 执行语句
	 *
	 * @param string $str sql指令
	 * @return integer false
	 */
	abstract public function execute($str);

	/**
	 * 释放查询结果
	 */
	abstract public function free_result();

	/**
	 * 返回数据库版本
	 */
	abstract public function version();

	/**
	 * 数据库错误信息并显示当前的SQL语句
	 *
	 * @return string
	 */
	abstract public function error();

	/**
	 * 关闭数据库连接
	 */
	abstract public function close();

	/**
	 * 析构方法
	 */
	public function __destruct() {
		// 释放查询
		if ($this->lastqueryid) {
			$this->free_result ();
		}
		// 关闭连接
		$this->close ();
	}
}