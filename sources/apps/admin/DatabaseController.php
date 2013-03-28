<?php
/**
 * 数据库管理
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
@set_time_limit ( 0 );
Loader::lib ( 'admin:admin', false );
class DatabaseController extends admin {

	private $db;

	function __construct() {
		parent::__construct ();
		$this->userid = $_SESSION ['userid'];
	}

	/**
	 * 数据库导出
	 */
	public function export() {
		$database = C ( 'database' );
		if (isset ( $_POST ['dosubmit'] ) || isset ( $_GET ['dosubmit'] )) {
			if (isset ( $_GET ['pdo_select'] ) && isset ( $_POST ['pdo_select'] )) showmessage ( L ( 'select_pdo' ) );
			$tables = isset ( $_POST ['tables'] ) ? $_POST ['tables'] : (isset ( $_GET ['tables'] ) ? $_GET ['tables'] : '');
			$sqlcharset = isset ( $_POST ['sqlcharset'] ) ? $_POST ['sqlcharset'] : (isset ( $_GET ['sqlcharset'] ) ? $_GET ['sqlcharset'] : '');
			$sqlcompat = isset ( $_POST ['sqlcompat'] ) ? $_POST ['sqlcompat'] : (isset ( $_GET ['sqlcompat'] ) ? $_GET ['sqlcompat'] : '');
			$sizelimit = isset ( $_POST ['sizelimit'] ) ? $_POST ['sizelimit'] : (isset ( $_GET ['sizelimit'] ) ? $_GET ['sizelimit'] : '');
			$fileid = isset ( $_POST ['fileid'] ) ? $_POST ['fileid'] : (isset ( $_GET ['fileid'] ) ? trim ( $_GET ['fileid'] ) : '');
			$random = isset ( $_POST ['random'] ) ? $_POST ['random'] : (isset ( $_GET ['random'] ) ? trim ( $_GET ['random'] ) : '');
			$tableid = isset ( $_POST ['tableid'] ) ? $_POST ['tableid'] : (isset ( $_GET ['tableid'] ) ? trim ( $_GET ['tableid'] ) : '');
			$startfrom = isset ( $_POST ['startfrom'] ) ? $_POST ['startfrom'] : (isset ( $_GET ['startfrom'] ) ? trim ( $_GET ['startfrom'] ) : '');
			$tabletype = isset ( $_POST ['tabletype'] ) ? $_POST ['tabletype'] : (isset ( $_GET ['tabletype'] ) ? trim ( $_GET ['tabletype'] ) : '');
			$this->pdo_name = isset ( $_POST ['pdo_select'] ) ? $_POST ['pdo_select'] : (isset ( $_GET ['pdo_select'] ) ? trim ( $_GET ['pdo_select'] ) : '');
			$this->db = Loader::db ( $this->pdo_name );
			$this->export_database ( $tables, $sqlcompat, $sqlcharset, $sizelimit, '', $fileid, $random, $tableid, $startfrom, $tabletype );
		} else {
			foreach ( $database as $name => $value ) {
				$pdos [$name] = $value ['database'] . '[' . $value ['hostname'] . ']';
			}
			if (isset ( $_GET ['pdoname'] )) {
				//S ( 'common/bakup_table' ,'');
				$pdo_name = trim ( $_GET ['pdoname'] );
				$r = array ();
				$db = Loader::db ( $pdo_name );
				$r = $db->query ( "SHOW TABLE STATUS FROM `" . $database [$pdo_name] ['database'] . "`" );
				$infos = $this->status ( $r, $database [$pdo_name] ['prefix'] );
			}
			include $this->admin_tpl ( 'database_export' );
		}
	}

	/**
	 * 数据库导入
	 */
	public function import() {
		$database = C ( 'database' );
		if (isset ( $_GET ['dosubmit'] )) {
			$admin_founders = explode ( ',', C ( 'system', 'admin_founders' ) );
			if (! in_array ( $this->userid, $admin_founders )) {
				showmessage ( L ( 'only_fonder_operation' ) );
			}
			$this->pdo_name = $_GET ['pdoname'];
			$pre = trim ( $_GET ['pre'] );
			$this->fileid = isset ( $_GET ['fileid'] ) ? trim ( $_GET ['fileid'] ) : '';
			$this->db = Loader::db ( $this->pdo_name );
			$this->db_charset = $this->db->config ['charset'];
			$this->db_prefix = $this->db->get_prefix ();
			$this->import_database ( $pre );
		} else {
			$pdos = $others = array ();
			foreach ( $database as $name => $value ) {
				$pdos [$name] = $value ['database'] . '[' . $value ['hostname'] . ']';
			}
			$pdoname = isset ( $_GET ['pdoname'] ) ? $_GET ['pdoname'] : key ( $pdos );
			$sqlfiles = glob ( DATA_PATH . 'bakup/' . $pdoname . '/*.sql' );
			if (is_array ( $sqlfiles )) {
				asort ( $sqlfiles );
				$prepre = '';
				$info = $infos = $other = $others = array ();
				foreach ( $sqlfiles as $id => $sqlfile ) {
					if (preg_match ( "/(yuncmstables_[0-9]{8}_[0-9a-z]{4}_)([0-9]+)\.sql/i", basename ( $sqlfile ), $num )) {
						$info ['filename'] = basename ( $sqlfile );
						$info ['filesize'] = round ( filesize ( $sqlfile ) / (1024 * 1024), 2 );
						$info ['maketime'] = date ( 'Y-m-d H:i:s', filemtime ( $sqlfile ) );
						$info ['pre'] = $num [1];
						$info ['number'] = $num [2];
						if (! $id) $prebgcolor = '#CFEFFF';
						if ($info ['pre'] == $prepre) {
							$info ['bgcolor'] = $prebgcolor;
						} else {
							$info ['bgcolor'] = $prebgcolor == '#CFEFFF' ? '#F1F3F5' : '#CFEFFF';
						}
						$prebgcolor = $info ['bgcolor'];
						$prepre = $info ['pre'];
						$infos [] = $info;
					} else {
						$other ['filename'] = basename ( $sqlfile );
						$other ['filesize'] = round ( filesize ( $sqlfile ) / (1024 * 1024), 2 );
						$other ['maketime'] = date ( 'Y-m-d H:i:s', filemtime ( $sqlfile ) );
						$others [] = $other;
					}
				}
			}
			$show_validator = true;
			include $this->admin_tpl ( 'database_import' );
		}
	}

	/**
	 * 备份文件下载
	 */
	public function public_down() {
		$admin_founders = explode ( ',', C ( 'system', 'admin_founders' ) );
		if (! in_array ( $this->userid, $admin_founders )) {
			showmessage ( L ( 'only_fonder_operation' ) );
		}
		$datadir = $_GET ['pdoname'];
		$filename = $_GET ['filename'];
		$fileext = File::get_suffix ( $filename );
		if ($fileext != 'sql') {
			showmessage ( L ( 'only_sql_down' ) );
		}
		File::down ( DATA_PATH . 'bakup' . DIRECTORY_SEPARATOR . $datadir . DIRECTORY_SEPARATOR . $filename );
	}

	/**
	 * 备份文件删除
	 */
	public function delete() {
		$filenames = $_POST ['filenames'];
		$pdo_name = $_GET ['pdoname'];
		$bakfile_path = DATA_PATH . 'bakup' . DIRECTORY_SEPARATOR . $pdo_name . DIRECTORY_SEPARATOR;
		if ($filenames) {
			if (is_array ( $filenames )) {
				foreach ( $filenames as $filename ) {
					if (File::get_suffix ( $filename ) == 'sql') {
						@unlink ( $bakfile_path . $filename );
					}
				}
				showmessage ( L ( 'operation_success' ), U ( 'admin/database/import', array ('pdoname' => $pdo_name ) ) );
			} else {
				if (File::get_suffix ( $filenames ) == 'sql') {
					@unlink ( $bakfile_path . $filename );
					showmessage ( L ( 'operation_success' ), U ( 'admin/database/import', array ('pdoname' => $pdo_name ) ) );
				}
			}
		} else {
			showmessage ( L ( 'select_delfile' ) );
		}
	}

	/**
	 * 数据库导出方法
	 *
	 * @param unknown_type $tables 数据表数据组
	 * @param unknown_type $sqlcompat 数据库兼容类型
	 * @param unknown_type $sqlcharset 数据库字符
	 * @param unknown_type $sizelimit 卷大小
	 * @param unknown_type $action 操作
	 * @param unknown_type $fileid 卷标
	 * @param unknown_type $random 随机字段
	 * @param unknown_type $tableid
	 * @param unknown_type $startfrom
	 * @param unknown_type $tabletype 备份数据库类型 （非yuncms数据与yuncms数据）
	 */
	private function export_database($tables, $sqlcompat, $sqlcharset, $sizelimit, $action, $fileid, $random, $tableid, $startfrom, $tabletype) {
		$dumpcharset = $sqlcharset ? $sqlcharset : str_replace ( '-', '', CHARSET );
		$fileid = ($fileid != '') ? $fileid : 1;
		if ($fileid == 1 && $tables) {
			if (! isset ( $tables ) || ! is_array ( $tables )) showmessage ( L ( 'select_tbl' ) );
			$random = mt_rand ( 1000, 9999 );
			S ( 'common/bakup_table', $tables );
		} else {
			if (! $tables = S ( 'common/bakup_table' )) showmessage ( L ( 'select_tbl' ) );
		}
		if ($this->db->version () > '4.1') {
			if ($sqlcharset) {
				$this->db->query ( "SET NAMES '" . $sqlcharset . "';\n\n" );
			}
			if ($sqlcompat == 'MYSQL40') {
				$this->db->query ( "SET SQL_MODE='MYSQL40'" );
			} elseif ($sqlcompat == 'MYSQL41') {
				$this->db->query ( "SET SQL_MODE=''" );
			}
		}

		$tabledump = '';

		$tableid = ($tableid != '') ? $tableid - 1 : 0;
		$startfrom = ($startfrom != '') ? intval ( $startfrom ) : 0;
		for($i = $tableid; $i < count ( $tables ) && strlen ( $tabledump ) < $sizelimit * 1000; $i ++) {
			global $startrow;
			$offset = 100;
			if (! $startfrom) {
				if ($tables [$i] != $this->db->get_prefix () . 'session') {
					$tabledump .= "DROP TABLE IF EXISTS `$tables[$i]`;\n";
				}
				$create = $this->db->query ( "SHOW CREATE TABLE `$tables[$i]` " );
				$tabledump .= $create[0] ['Create Table'] . ";\n\n";
				if ($sqlcompat == 'MYSQL41' && $this->db->version () < '4.1') {
					$tabledump = preg_replace ( "/TYPE\=([a-zA-Z0-9]+)/", "ENGINE=\\1 DEFAULT CHARSET=" . $dumpcharset, $tabledump );
				}
				if ($this->db->version () > '4.1' && $sqlcharset) {
					$tabledump = preg_replace ( "/(DEFAULT)*\s*CHARSET=[a-zA-Z0-9]+/", "DEFAULT CHARSET=" . $sqlcharset, $tabledump );
				}
				if ($tables [$i] == $this->db->get_prefix () . 'session') {
					$tabledump = str_replace ( "CREATE TABLE `" . $this->db->get_prefix () . "session`", "CREATE TABLE IF NOT EXISTS `" . $this->db->get_prefix () . "session`", $tabledump );
				}
			}

			$numrows = $offset;
			while ( strlen ( $tabledump ) < $sizelimit * 1000 && $numrows == $offset ) {
				if ($tables [$i] == $this->db->get_prefix () . 'session') break;
				$sql = "SELECT * FROM `$tables[$i]` LIMIT $startfrom, $offset";
				//获取字段
				$fields_name = $this->db->get_fields ( $tables [$i] );
				//字段总数
				$numfields = count($fields_name);
				//返回结果集中行的数目
				$numrows = $this->db->num_rows;

				$rows = $this->db->query ( $sql );
				$name = array_keys ( $fields_name );
				$r = array ();
				foreach ( $rows as $row ) {
					$r [] = $row;
					$comma = "";
					$tabledump .= "INSERT INTO `$tables[$i]` VALUES(";
					for($j = 0; $j < $numfields; $j ++) {
						$tabledump .= $comma . "'" . mysql_escape_string ( $row [$name [$j]] ) . "'";
						$comma = ",";
					}
					$tabledump .= ");\n";
				}
				$startfrom += $offset;

			}
			$tabledump .= "\n";
			$startrow = $startfrom;
			$startfrom = 0;
		}
		if (trim ( $tabledump )) {
			$tabledump = "# YUNCMS bakfile\n# version:YUNCMS ".C('version','version')."\n# time:" . date ( 'Y-m-d H:i:s' ) . "\n# type:YUNCMS\n# TINTSOFT:http://www.tintsoft.com\n# --------------------------------------------------------\n\n\n" . $tabledump;
			$tableid = $i;
			$filename = $tabletype . '_' . date ( 'Ymd' ) . '_' . $random . '_' . $fileid . '.sql';
			$altid = $fileid;
			$fileid ++;
			$bakfile_path = DATA_PATH . 'bakup' . DIRECTORY_SEPARATOR . $this->pdo_name;
			if (! Folder::create ( $bakfile_path )) {
				showmessage ( L ( 'dir_not_be_created' ) );
			}
			$bakfile = $bakfile_path . DIRECTORY_SEPARATOR . $filename;
			if (! is_writable ( DATA_PATH . 'bakup' )) showmessage ( L ( 'dir_not_be_created' ) );
			file_put_contents ( $bakfile, $tabledump );
			@chmod ( $bakfile, 0777 );
			if (defined ( 'EXECUTION_SQL' )) $filename = L ( 'bundling' ) . $altid . '#';
			showmessage ( L ( 'bakup_file' ) . " $filename " . L ( 'bakup_write_succ' ), U ( 'admin/database/export', array ('sizelimit' => $sizelimit,'sqlcompat' => $sqlcompat,'sqlcharset' => $sqlcharset,'tableid' => $tableid,'fileid' => $fileid,'startfrom' => $startrow,'random' => $random,
					'dosubmit' => '1','tabletype' => $tabletype,'pdo_select' => $this->pdo_name ) ) );
		} else {
			$bakfile_path = DATA_PATH . 'bakup' . DIRECTORY_SEPARATOR . $this->pdo_name . DIRECTORY_SEPARATOR;
			file_put_contents ( $bakfile_path . 'index.html', '' );
			S ( 'common/bakup_table','' );
			showmessage ( L ( 'bakup_succ' ), U ( 'admin/database/import', array ('pdoname' => $this->pdo_name,'menuid'=>62 ) ) );
		}
	}

	/**
	 * 数据库恢复
	 *
	 * @param unknown_type $filename
	 */
	private function import_database($filename) {
		if ($filename && File::get_suffix ( $filename ) == 'sql') {
			$filepath = DATA_PATH . 'bakup' . DIRECTORY_SEPARATOR . $this->pdo_name . DIRECTORY_SEPARATOR . $filename;
			if (! file_exists ( $filepath )) showmessage ( L ( 'database_sorry' ) . " $filepath " . L ( 'database_not_exist' ) );
			$sql = file_get_contents ( $filepath );
			self::sql_execute ( $sql );
			showmessage ( "$filename " . L ( 'data_have_load_to_database' ) );
		} else {
			$fileid = $this->fileid ? $this->fileid : 1;
			$pre = $filename;
			$filename = $filename . $fileid . '.sql';
			$filepath = DATA_PATH . 'bakup' . DIRECTORY_SEPARATOR . $this->pdo_name . DIRECTORY_SEPARATOR . $filename;
			if (file_exists ( $filepath )) {
				$sql = File::read ( $filepath );
				self::sql_execute ( $sql );
				$fileid ++;
				showmessage ( L ( 'bakup_data_file' ) . " $filename " . L ( 'load_success' ), U ( 'admin/database/import', array ('pdoname' => $this->pdo_name,'pre' => $pre,'fileid' => $fileid,'dosubmit' => '1' ) ) );
			} else {
				showmessage ( L ( 'data_recover_succ' ), U ( 'admin/database/import' ) );
			}
		}
	}

	/**
	 * 数据库修复、优化
	 */
	public function public_repair() {
		$tables = isset ( $_POST ['tables'] ) ? $_POST ['tables'] : trim ( $_GET ['tables'] );
		$operation = trim ( $_GET ['operation'] );
		$pdo_name = trim ( $_GET ['pdo_name'] );
		$this->db = Loader::db ( $pdo_name );
		$tables = is_array ( $tables ) ? implode ( ',', $tables ) : $tables;
		if ($tables && in_array ( $operation, array ('repair','optimize' ) )) {
			$this->db->query ( "$operation TABLE $tables" );
			showmessage ( L ( 'operation_success' ), '?app=admin&controller=database&action=export&pdoname=' . $pdo_name );
		} elseif ($tables && $operation == 'showcreat') {
			$structure = $this->db->query ( "SHOW CREATE TABLE $tables" );
			$structure = $structure[0] ['Create Table'];
			$show_header = true;
			include $this->admin_tpl ( 'database_structure' );
		} else {
			showmessage ( L ( 'select_tbl' ), '?app=admin&controller=database&action=export&pdoname=' . $pdo_name );
		}
	}

	/**
	 * 执行SQL
	 *
	 * @param unknown_type $sql
	 */
	private function sql_execute($sql) {
		$sqls = $this->sql_split ( $sql );
		if (is_array ( $sqls )) {
			foreach ( $sqls as $sql ) {
				if (trim ( $sql ) != '') {
					$this->db->execute ( $sql );
				}
			}
		} else {
			$this->db->execute ( $sqls );
		}
		return true;
	}

	private function sql_split($sql) {
		if ($this->db->version () > '4.1' && $this->db_charset) {
			$sql = preg_replace ( "/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . $this->db_charset, $sql );
		}
		if ($this->db->get_prefix () != "yuncms_") $sql = str_replace ( "`yuncms_", '`' . $this->db->get_prefix (), $sql );
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
	 * 获取数据表
	 *
	 * @param array 数据表数组
	 * @param string 表前缀
	 */
	private function status($tables, $tablepre) {
		$yuncms = array ();
		$other = array ();
		foreach ( $tables as $table ) {
			$name = $table ['Name'];
			$row = array ('name' => $name,'rows' => $table ['Rows'],'size' => $table ['Data_length'] + $table ['Index_length'],'engine' => $table ['Engine'],'data_free' => $table ['Data_free'],'collation' => $table ['Collation'] );
			if (strpos ( $name, $tablepre ) === 0) {
				$yuncms [] = $row;
			} else {
				$other [] = $row;
			}
		}
		return array ('yuncmstables' => $yuncms,'othertables' => $other );
	}
}