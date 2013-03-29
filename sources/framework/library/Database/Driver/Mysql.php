<?php
/**
 * Mysql驱动类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-15
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Mysql.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Database_Driver_Mysql extends Core_DB {

	/**
	 * 架构函数 读取数据库配置信息
	 *
	 * @param array $config 数据库配置数组
	 */
	public function __construct($config = '') {
		if (! extension_loaded ( 'mysql' )) {
			throw_exception ( 'suppert does not exist.' . ':mysql' );
		}
		if (! empty ( $config )) {
			$this->config = $config;
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::connect()
	 */
	public function connect() {
		$host = $this->config ['hostname'] . ($this->config ['port'] ? ":{$this->config['port']}" : '');
		if ($this->config ['pconnect'] == 1) {
			$this->link = mysql_pconnect ( $host, $this->config ['username'], $this->config ['password'], 1 );
		} else {
			$this->link = mysql_connect ( $host, $this->config ['username'], $this->config ['password'], 1 );
		}
		if (! $this->link) {
			throw_exception ( "Couldn't connect to SQL Server on " . $this->config ['hostname'] );
		}
		if ($this->version () > '4.1') {
			// 设置数据库编码
			$charset = isset ( $this->config ['charset'] ) ? $this->config ['charset'] : 'utf8';
			mysql_query ( "SET NAMES '" . $charset . "'", $this->link );
			// 设置连接字符集和校对
			$serverset = "character_set_connection='$charset',character_set_results='$charset',character_set_client=binary";
			mysql_query ( "SET $serverset", $this->link );
			// 设置 sql_model
			if ($this->version () > '5.0.1') {
				mysql_query ( "SET sql_mode=''", $this->link );
			}
		}
		$this->select_db ( $this->config ['database'] );
		$this->set_prefix ( $this->config ['prefix'] );
		return $this->link;
	}

	/**
	 * Select a MySQL database
	 */
	public function select_db($database = null) {
		if (! is_null ( $database ) && ! @mysql_select_db ( $database, $this->link )) {
			throw_exception ( 'Cannot use database ' . $database );
			return false;
		}
		$this->database = $database;
		return true;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::free_result()
	 */
	public function free_result() {
		if (is_resource ( $this->lastqueryid )) {
			mysql_free_result ( $this->lastqueryid );
			$this->lastqueryid = null;
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::query()
	 */
	public function query($str, $key = '') {
		$this->query_str = $this->parse_prefix ( $str ); // 自动替换表前缀
		if (0 === stripos ( $this->query_str, 'call' )) { // 存储过程查询支持
			$this->close ();
		}
		if (! $this->link) return false;
		N ( 'db_query', 1 );
		G ( 'queryStartTime' );
		$this->lastqueryid = mysql_query ( $this->query_str, $this->link );
		$this->debug ();
		if (false === $this->lastqueryid) {
			$this->error ();
			return false;
		} else {
			$this->num_rows = mysql_num_rows ( $this->lastqueryid );
			$result = $this->get_all ( $key );
			return $result;
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::execute()
	 */
	public function execute($str) {
		$this->query_str = $this->parse_prefix ( $str ); // 自动替换表前缀
		if (! $this->link) return false;
		N ( 'db_write', 1 );
		G ( 'queryStartTime' );
		$result = mysql_query ( $this->query_str, $this->link );
		$this->debug ();
		if (false === $result) {
			$this->error ();
			return false;
		} else {
			$this->num_rows = mysql_affected_rows ( $this->link );
			$this->lastinsid = mysql_insert_id ( $this->link );
			return $this->num_rows;
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::start_trans()
	 */
	public function start_trans() {
		if (! $this->link) return false;
		if ($this->trans_times == 0) { // 数据rollback 支持
			mysql_query ( 'START TRANSACTION', $this->link );
		}
		$this->trans_times ++;
		return;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::commit()
	 */
	public function commit() {
		if ($this->trans_times > 0) {
			$result = mysql_query ( 'COMMIT', $this->link );
			$this->trans_times = 0;
			if (! $result) {
				$this->error ();
				return false;
			}
		}
		return true;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::rollback()
	 */
	public function rollback() {
		if ($this->trans_times > 0) {
			$result = mysql_query ( 'ROLLBACK', $this->link );
			$this->trans_times = 0;
			if (! $result) {
				$this->error ();
				return false;
			}
		}
		return true;
	}

	/**
	 * 遍历查询结果集
	 *
	 * @param $mode 返回结果集类型
	 * @return array
	 */
	private function fetch($mode = MYSQL_ASSOC) {
		$result = mysql_fetch_array ( $this->lastqueryid, $mode );
		if (! $result) {
			$this->free_result ();
		}
		return $result;
	}

	/**
	 * 获得所有的查询数据
	 *
	 * @param $key 按键名排序
	 * @param int $fetch_mode 获得结果集的模式MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
	 * @return array
	 */
	private function get_all($key = '', $mode = MYSQL_ASSOC) {
		$result = array ();
		if ($this->num_rows > 0) {
			if (! $key)
				while ( $row = $this->fetch ( $mode ) )
					$result [] = $row;
			else
				while ( $row = $this->fetch ( $mode ) ) {
					if (! isset ( $row [$key] )) continue;
					$result [$row [$key]] = $row;
				}
		}
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::get_fields()
	 */
	public function get_fields($table) {
		$result = $this->query ( 'SHOW COLUMNS FROM ' . $this->parse_key ( $table ) );
		$info = array ();
		if ($result) {
			foreach ( $result as $key => $val ) {
				$info [$val ['Field']] = array ('name' => $val ['Field'],'type' => $val ['Type'],'notnull' => ( bool ) ($val ['Null'] === ''),'default' => $val ['Default'],'primary' => (strtolower ( $val ['Key'] ) == 'pri'),'autoinc' => (strtolower ( $val ['Extra'] ) == 'auto_increment') );
			}
		}
		return $info;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::get_tables()
	 */
	public function get_tables($database = '') {
		if (! empty ( $database )) {
			$sql = 'SHOW TABLES FROM ' . $database;
		} else {
			$sql = 'SHOW TABLES ';
		}
		$result = $this->query ( $sql );
		$info = array ();
		foreach ( $result as $key => $val ) {
			$info [$key] = current ( $val );
		}
		return $info;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::error()
	 */
	public function error() {
		$this->error = mysql_error ( $this->link );
		if ('' != $this->query_str) {
			$this->error .= "\n [ SQL语句 ] : " . $this->query_str;
		}
		trace ( $this->error, '', 'ERR' );
		return $this->error;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::version()
	 */
	public function version() {
		if (! is_resource ( $this->link )) {
			$this->connect ();
		}
		return mysql_get_server_info ( $this->link );
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::escape_string()
	 */
	public function escape_string($str) {
		if ($this->link) {
			return mysql_real_escape_string ( $str, $this->link );
		} else {
			return mysql_escape_string ( $str );
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::parse_key()
	 */
	protected function parse_key(&$key) {
		$key = trim ( $key );
		if (! preg_match ( '/[,\'\"\*\(\)`.\s]/', $key )) {
			$key = '`' . $key . '`';
		}
		return $key;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::close()
	 */
	public function close() {
		if (is_resource ( $this->link )) {
			@mysql_close ( $this->link );
		}
		$this->link = null;
	}
}