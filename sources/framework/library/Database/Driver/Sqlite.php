<?php
/**
 * Sqlite数据库驱动
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-17
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Sqlite.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Database_Driver_Sqlite extends Core_DB {

	/**
	 * 架构函数 读取数据库配置信息
	 *
	 * @param array $config 数据库配置数组
	 */
	public function __construct($config = '') {
		if (! extension_loaded ( 'sqlite' )) {
			throw_exception ( 'suppert does not exist.' . ':sqlite' );
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
		if ($this->config ['pconnect'] == 1) {
			$this->link = sqlite_popen ( $this->config ['database'], $this->config ['mode'] );
		} else {
			$this->link = sqlite_open ( $this->config ['database'], $this->config ['mode'] );
		}
		if (! $this->link) {
			throw_exception ( sqlite_error_string () );
		}
		return $this->link;
	}

	/**
	 * 释放查询结果
	 */
	public function free_result() {
		if (is_resource ( $this->lastqueryid )) {
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
		N ( 'db_query', 1 );
		// 记录开始执行时间
		G ( 'queryStartTime' );
		$this->lastqueryid = sqlite_query ( $this->link, $this->query_str );
		$this->debug ();
		if (false === $this->lastqueryid) {
			$this->error ();
			return false;
		} else {
			$this->num_rows = sqlite_num_rows ( $this->lastqueryid );
			return $this->get_all ( $key );
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::execute()
	 */
	public function execute($str) {
		$this->query_str = $this->parse_prefix ( $str ); // 自动替换表前缀
		N ( 'db_write', 1 );
		// 记录开始执行时间
		G ( 'queryStartTime' );
		$result = sqlite_exec ( $this->link, $this->query_str );
		$this->debug ();
		if (false === $result) {
			$this->error ();
			return false;
		} else {
			$this->num_rows = sqlite_changes ( $this->link );
			$this->lastinsid = sqlite_last_insert_rowid ( $this->link );
			return $this->num_rows;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Core_DB::start_trans()
	 */
	public function start_trans() {
		if (! $this->link) return false;
		// 数据rollback 支持
		if ($this->trans_times == 0) {
			sqlite_query ( $this->link, 'BEGIN TRANSACTION' );
		}
		$this->trans_times ++;
		return;
	}

	/**
	 * (non-PHPdoc)
	 * @see Core_DB::commit()
	 */
	public function commit() {
		if ($this->trans_times > 0) {
			$result = sqlite_query ( $this->link, 'COMMIT TRANSACTION' );
			if (! $result) {
				$this->error ();
				return false;
			}
			$this->trans_times = 0;
		}
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see Core_DB::rollback()
	 */
	public function rollback() {
		if ($this->trans_times > 0) {
			$result = sqlite_query ( $this->link, 'ROLLBACK TRANSACTION' );
			if (! $result) {
				$this->error ();
				return false;
			}
			$this->trans_times = 0;
		}
		return true;
	}


	private function fetch($mode = SQLITE_ASSOC) {
		$result = sqlite_fetch_array ( $this->lastqueryid, $mode );
		if (! $result) {
			$this->free_result ();
		}
		return $result;
	}

	/**
	 * 获得所有的查询数据
	 *
	 * @access private
	 * @return array
	 */
	private function get_all($key = '',$mode = MYSQL_ASSOC) {
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
	 * 取得数据表的字段信息
	 *
	 * @return array
	 */
	public function get_fields($tableName) {
		$result = $this->query ( 'PRAGMA table_info( ' . $tableName . ' )' );
		$info = array ();
		if ($result) {
			foreach ( $result as $key => $val ) {
				$info [$val ['Field']] = array ('name' => $val ['Field'],'type' => $val ['Type'],'notnull' => ( bool ) ($val ['Null'] === ''),				// not
				                                                                                                                               // null
				                                                                                                                               // is
				                                                                                                                               // empty,
				                                                                                                                               // null
				                                                                                                                               // is
				                                                                                                                               // yes
				'default' => $val ['Default'],'primary' => (strtolower ( $val ['Key'] ) == 'pri'),'autoinc' => (strtolower ( $val ['Extra'] ) == 'auto_increment') );
			}
		}
		return $info;
	}

	/**
	 * 取得数据库的表信息
	 *
	 * @return array
	 */
	public function get_tables($dbName = '') {
		$result = $this->query ( "SELECT name FROM sqlite_master WHERE type='table' " . "UNION ALL SELECT name FROM sqlite_temp_master " . "WHERE type='table' ORDER BY name" );
		$info = array ();
		foreach ( $result as $key => $val ) {
			$info [$key] = current ( $val );
		}
		return $info;
	}

	/**
	 * 关闭数据库
	 */
	public function close() {
		if (is_resource ( $this->link )) {
			sqlite_close ( $this->link );
		}
		$this->link = null;
	}

	/**
	 * 数据库错误信息
	 * 并显示当前的SQL语句
	 *
	 * @return string
	 */
	public function error() {
		$this->error = sqlite_error_string ( sqlite_last_error ( $this->link ) );
		if ('' != $this->query_str) {
			$this->error .= "\n [ SQL语句 ] : " . $this->query_str;
		}
		trace ( $this->error, '', 'ERR' );
		return $this->error;
	}

	/**
	 * SQL指令安全过滤
	 *
	 * @param string $str SQL指令
	 * @return string
	 */
	public function escape_string($str) {
		return sqlite_escape_string ( $str );
	}

	/**
	 * limit
	 *
	 * @return string
	 */
	public function parse_limit($limit) {
		$limit_str = '';
		if (! empty ( $limit )) {
			$limit = explode ( ',', $limit );
			if (count ( $limit ) > 1) {
				$limit_str .= ' LIMIT ' . $limit [1] . ' OFFSET ' . $limit [0] . ' ';
			} else {
				$limit_str .= ' LIMIT ' . $limit [0] . ' ';
			}
		}
		return $limit_str;
	}

	public function version() {
		return 'SQLite 2.8.17';
	}
}