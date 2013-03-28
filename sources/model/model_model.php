<?php
/**
 * 模型表
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class model_model extends Model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'model';
		parent::__construct ();
		$this->charset = $this->db->config ['charset']; // 获取数据库编码
	}

	public function sql_execute($sql) {
		$sqls = $this->sql_split ( $sql );
		if (is_array ( $sqls )) {
			foreach ( $sqls as $sql ) {
				if (trim ( $sql ) != '') $this->db->execute ( $sql );
			}
		} else {
			$this->db->execute ( $sqls );
		}
		return true;
	}


	public function sql_split($sql) {
		if ($this->db->version () > '4.1' && $this->charset) {
			$sql = preg_replace ( "/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . $this->charset, $sql );
		}
		if ($this->get_prefix () != "yuncms_") $sql = str_replace ( "yuncms_", $this->get_prefix (), $sql );
		$sql = str_replace ( "\r", "\n", $sql );
		$ret = array ();
		$num = 0;
		$queriesarray = explode ( ";\n", trim ( $sql ) );
		unset ( $sql );
		foreach ( $queriesarray as $query ) {
			$ret [$num] = '';
			$queries = explode ( "\n", trim ( $query ) );
			$queries = array_filter ( $queries );
			foreach ( $queries as $query ) {
				$str1 = substr ( $query, 0, 1 );
				if ($str1 != '#' && $str1 != '-') $ret [$num] .= $query;
			}
			$num ++;
		}
		return ($ret);
	}

	/**
	 * 删除表
	 */
	public function drop_table($tablename) {
		$tablename = $this->get_prefix () . $tablename;
		$tablearr = $this->get_tables ();
		if (in_array ( $tablename, $tablearr )) {
			return $this->execute ( "DROP TABLE $tablename" );
		} else {
			return false;
		}
	}

	/**
	 * 修改member表会员模型
	 *
	 * @param string $tablename
	 */
	public function change_member_modelid($from_modelid, $to_modelid) {
		Loader::model ( 'member_model' )->where ( array ('modelid' => $from_modelid ) )->update ( array ('modelid' => $to_modelid ) );
	}
}