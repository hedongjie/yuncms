<?php
/**
 * MSsql数据库驱动 要求sqlserver2005
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-17
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Mssql.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Database_Driver_Mssql extends Core_DB {

	/**
	 * 架构函数 读取数据库配置信息
	 *
	 * @param array $config 数据库配置数组
	 */
	public function __construct($config = '') {
		if (! extension_loaded ( 'mssql' )) {
			throw_exception ( 'suppert does not exist.' . ':mssql' );
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
		// 处理不带端口号的socket连接情况
		$sepr = IS_WIN ? ',' : ':';
		$host = $this->config ['hostname'] . ($this->config ['port'] ? $sepr . "{$this->config['port']}" : '');
		if ($this->config ['pconnect'] == 1) {
			$this->link = mssql_pconnect ( $host, $this->config ['username'], $this->config ['password'] );
		} else {
			$this->link = mssql_connect ( $host, $this->config ['username'], $this->config ['password'] );
		}
		if (! $this->link) {
			throw_exception ( "Couldn't connect to SQL Server on $host" );
		}
		$this->select_db ( $this->config ['database'] );
		$this->set_prefix ( $this->config ['prefix'] );
		return $this->link;
	}

	/**
	 * Select a MsSQL database
	 */
	public function select_db($database = null) {
		if (! is_null ( $database ) && ! @mssql_select_db ( $database, $this->link )) {
			throw_exception ( "Couldn't open database  " . $database );
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
			mssql_free_result ( $this->lastqueryid );
			$this->lastqueryid = null;
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::query()
	 */
	public function query($str, $key = '') {
		$this->parse_query ( $str );
		if (0 === stripos ( $this->query_str, 'call' )) { // 存储过程查询支持
			$this->close ();
		}
		if (! $this->link) return false;
		N ( 'db_query', 1 );
		G ( 'queryStartTime' );
		$this->lastqueryid = mssql_query ( $this->query_str, $this->link );
		$this->debug ();
		if (false === $this->lastqueryid) {
			$this->error ();
			return false;
		} else {
			$this->num_rows = mssql_num_rows ( $this->lastqueryid );
			$res = $this->get_all ( $key );
			return $res;
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::execute()
	 */
	public function execute($str) {
		$this->parse_query ( $str );
		if (! $this->link) return false;
		N ( 'db_write', 1 );
		G ( 'queryStartTime' );
		$result = mssql_query ( $this->query_str, $this->link );
		$this->debug ();
		if (false === $result) {
			$this->error ();
			return false;
		} else {
			$this->num_rows = mssql_rows_affected ( $this->link );
			$this->lastinsid = $this->mssql_insert_id ();
			return $this->num_rows;
		}
	}

	/**
	 * limit
	 *
	 * @return string
	 */
	public function parse_limit($limit) {
		if (empty ( $limit )) return '';
		$limit = explode ( ',', $limit );
		if (count ( $limit ) > 1)
			$limit_str = '(T1.ROW_NUMBER BETWEEN ' . $limit [0] . ' + 1 AND ' . $limit [0] . ' + ' . $limit [1] . ')';
		else
			$limit_str = '(T1.ROW_NUMBER BETWEEN 1 AND ' . $limit [0] . ")";
		return 'WHERE ' . $limit_str;
	}

	/**
	 * 用于获取最后插入的ID
	 *
	 * @return integer
	 */
	public function mssql_insert_id() {
		$query = "SELECT @@IDENTITY as last_insert_id";
		$result = mssql_query ( $query, $this->link );
		list ( $last_insert_id ) = mssql_fetch_row ( $result );
		mssql_free_result ( $result );
		return $last_insert_id;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::start_trans()
	 */
	public function start_trans() {
		if (! $this->link) return false;
		if ($this->trans_times == 0) { // 数据rollback 支持
			mssql_query ( 'BEGIN TRAN', $this->link );
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
			$result = mssql_query ( 'COMMIT TRAN', $this->link );
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
			$result = mssql_query ( 'ROLLBACK TRAN', $this->link );
			$this->trans_times = 0;
			if (! $result) {
				$this->error ();
				return false;
			}
		}
		return true;
	}

	/**
	 * 获得查询数据
	 *
	 * @param $type 返回结果集类型 MSSQL_ASSOC，MSSQL_NUM 和 MSSQL_BOTH
	 * @return array
	 */
	private function fetch($mode = MSSQL_ASSOC) {
		$result = mssql_fetch_array ( $this->lastqueryid, $mode );
		if (! $result) {
			$this->free_result ();
		}
		return $result;
	}

	/**
	 * 获得所有的查询数据
	 *
	 * @param $key 俺键名排序
	 * @param int $mode 获得结果集的模式MSSQL_ASSOC，MSSQL_NUM 和 MSSQL_BOTH
	 * @return array
	 */
	private function get_all($key = '', $mode = MSSQL_ASSOC) {
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
		$result = $this->query ( "SELECT   column_name,   data_type,   column_default,   is_nullable
				FROM    information_schema.tables AS t
				JOIN    information_schema.columns AS c
				ON  t.table_catalog = c.table_catalog
				AND t.table_schema  = c.table_schema
				AND t.table_name    = c.table_name
				WHERE   t.table_name = '$table'" );
		$info = array ();
		if ($result) {
			foreach ( $result as $key => $val ) {
				$info [$val ['column_name']] = array ('name' => $val ['column_name'],'type' => $val ['data_type'],'notnull' => ( bool ) ($val ['is_nullable'] === ''),				// not
				                                                                                                                                                       // null
				                                                                                                                                                       // is
				                                                                                                                                                       // empty,
				                                                                                                                                                       // null
				                                                                                                                                                       // is
				                                                                                                                                                       // yes
				'default' => $val ['column_default'],'primary' => false,'autoinc' => false );
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
		$result = $this->query ( "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'" );
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
		$this->error = mssql_get_last_message ();
		if ('' != $this->queryStr) {
			$this->error .= "\n [ SQL语句 ] : " . $this->query_str;
		}
		// trace($this->error,'','ERR');
		return $this->error;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::close()
	 */
	public function close() {
		if ($this->link) {
			mssql_close ( $this->link );
		}
		$this->link = null;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::version()
	 */
	public function version() {
		$result = $this->query ( "SELECT SERVERPROPERTY ('edition'), SERVERPROPERTY('productversion'), SERVERPROPERTY ('productlevel')" );
		$return = 'Microsoft SQL Server 2005 ' . $result [0] ['computed'] . ' ' . $result [0] ['computed1'] . ' ' . $result [0] ['computed2'];
		return $return;
	}
}