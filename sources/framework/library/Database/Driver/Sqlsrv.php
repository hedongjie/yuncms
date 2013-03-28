<?php
/**
 * Sqlsrv数据库驱动
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-17
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Sqlsrv.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Database_Driver_Sqlsrv extends Core_DB {

	protected $select_sql = 'SELECT T1.* FROM (SELECT thinkphp.*, ROW_NUMBER() OVER (%ORDER%) AS ROW_NUMBER FROM (SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%) AS thinkphp) AS T1 %LIMIT%%COMMENT%';

	public function __construct($config = '') {
		if (! function_exists ( 'sqlsrv_connect' )) {
			throw_exception ( 'suppert does not exist.:sqlsrv' );
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
		$host = $this->config ['hostname'] . ($this->config ['port'] ? ",{$this->config['port']}" : '');
		$connect_info = array ('Database' => $this->config ['database'],'UID' => $this->config ['username'],'PWD' => $this->config ['password'],'CharacterSet' => $this->config ['charset'] );
		$this->link = sqlsrv_connect ( $host, $connect_info );
		if (! $this->link) $this->error ( false );
		return $this->link;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::free_result()
	 */
	public function free_result() {
		if (is_resource ( $this->lastqueryid )) {
			sqlsrv_free_stmt ( $this->lastqueryid );
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
		$this->lastqueryid = sqlsrv_query ( $this->link, $this->query_str, array (), array ("Scrollable" => SQLSRV_CURSOR_KEYSET ) );
		$this->debug ();
		if (false === $this->lastqueryid) {
			$this->error ();
			return false;
		} else {
			$this->num_rows = sqlsrv_num_rows ( $this->lastqueryid );
			return $this->get_all ($key);
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
		$this->lastqueryid = sqlsrv_query ( $this->link, $str );
		$this->debug ();
		if (false === $this->lastqueryid) {
			$this->error ();
			return false;
		} else {
			$this->num_rows = sqlsrv_rows_affected ( $this->lastqueryid );
			$this->lastinsid = $this->mssql_insert_id ();
			return $this->num_rows;
		}
	}

	/**
	 * 用于获取最后插入的ID
	 *
	 * @return integer
	 */
	public function mssql_insert_id() {
		$query = "SELECT @@IDENTITY as last_insert_id";
		$result = sqlsrv_query ( $this->link, $query );
		list ( $last_insert_id ) = sqlsrv_fetch_array ( $result );
		sqlsrv_free_stmt ( $result );
		return $last_insert_id;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::start_trans()
	 */
	public function start_trans() {
		// 数据rollback 支持
		if ($this->trans_times == 0) {
			sqlsrv_begin_transaction ( $this->link );
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
			$result = sqlsrv_commit ( $this->link );
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
			$result = sqlsrv_rollback ( $this->link );
			$this->trans_times = 0;
			if (! $result) {
				$this->error ();
				return false;
			}
		}
		return true;
	}

	private function fetch($mode = SQLSRV_FETCH_ASSOC) {
		$result = sqlsrv_fetch_array ( $this->lastqueryid, $mode );
		if (! $result) {
			$this->free_result ();
		}
		return $result;
	}

	/**
	 * 获得所有的查询数据
	 *
	 * @return array
	 */
	private function get_all($key = '', $mode = SQLSRV_FETCH_ASSOC) {
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
	public function get_fields($tableName) {
		$result = $this->query ( "SELECT   column_name,   data_type,   column_default,   is_nullable
				FROM    information_schema.tables AS t
				JOIN    information_schema.columns AS c
				ON  t.table_catalog = c.table_catalog
				AND t.table_schema  = c.table_schema
				AND t.table_name    = c.table_name
				WHERE   t.table_name = '$tableName'" );
		$info = array ();
		if ($result) {
			foreach ( $result as $key => $val ) {
				$info [$val ['column_name']] = array ('name' => $val ['column_name'],'type' => $val ['data_type'],'notnull' => ( bool ) ($val ['is_nullable'] === ''),'default' => $val ['column_default'],'primary' => false,'autoinc' => false );
			}
		}
		return $info;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::get_tables()
	 */
	public function get_tables($dbName = '') {
		$result = $this->query ( "SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.TABLES
				WHERE TABLE_TYPE = 'BASE TABLE'
				" );
		$info = array ();
		foreach ( $result as $key => $val ) {
			$info [$key] = current ( $val );
		}
		return $info;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::parse_order()
	 */
	protected function parse_order($order) {
		return ! empty ( $order ) ? ' ORDER BY ' . $order : ' ORDER BY rand()';
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::parse_limit()
	 */
	public function parse_limit($limit) {
		if (empty ( $limit )) return '';
		$limit = explode ( ',', $limit );
		if (count ( $limit ) > 1)
			$limitStr = '(T1.ROW_NUMBER BETWEEN ' . $limit [0] . ' + 1 AND ' . $limit [0] . ' + ' . $limit [1] . ')';
		else
			$limitStr = '(T1.ROW_NUMBER BETWEEN 1 AND ' . $limit [0] . ")";
		return 'WHERE ' . $limitStr;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Core_DB::update()
	 */
	public function update($data, $options) {
		$sql = 'UPDATE ' . $this->parse_table ( $options ['table'] ) . $this->parse_set ( $data ) . $this->parse_where ( ! empty ( $options ['where'] ) ? $options ['where'] : '' ) . $this->parse_lock ( isset ( $options ['lock'] ) ? $options ['lock'] : false ) . $this->parse_comment ( ! empty ( $options ['comment'] ) ? $options ['comment'] : '' );
		return $this->execute ( $sql );
	}

	/**
	 * (non-PHPdoc)
	 * @see Core_DB::delete()
	 */
	public function delete($options = array()) {
		$sql = 'DELETE FROM ' . $this->parse_table ( $options ['table'] ) . $this->parse_where ( ! empty ( $options ['where'] ) ? $options ['where'] : '' ) . $this->parse_lock ( isset ( $options ['lock'] ) ? $options ['lock'] : false ) . $this->parse_comment ( ! empty ( $options ['comment'] ) ? $options ['comment'] : '' );
		return $this->execute ( $sql );
	}

	/**
	 * (non-PHPdoc)
	 * @see Core_DB::close()
	 */
	public function close() {
		if ($this->link) {
			sqlsrv_close ( $this->link );
		}
		$this->link = null;
	}

	/**
	 * (non-PHPdoc)
	 * @see Core_DB::error()
	 */
	public function error($result = true) {
		$errors = sqlsrv_errors ();
		$this->error = '';
		foreach ( $errors as $error ) {
			$this->error .= $error ['message'];
		}
		if ('' != $this->query_str) {
			$this->error .= "\n [ SQL语句 ] : " . $this->query_str;
		}
		$result ? trace ( $error ['message'], '', 'ERR' ) : throw_exception ( $this->error );
		return $this->error;
	}

	/**
	 * (non-PHPdoc)
	 * @see Core_DB::version()
	 */
	public function version(){
		return ;
	}
}