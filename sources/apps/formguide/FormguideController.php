<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class FormguideController extends admin {

	private $db, $tablename, $m_db, $M;
	public function __construct() {
		parent::__construct ();
		$this->tablename = '';
		$this->M = new_htmlspecialchars ( S ( 'common/formguide' ) );
		$this->db = Loader::model ( 'model_model' );
	}

	// 表单向导列表
	public function init() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$data = $this->db->where(array ('type' => 3 ))->order('modelid DESC')->listinfo ($page );
		$big_menu = big_menu ( U ( 'formguide/formguide/add' ), 'add', L ( 'formguide_add' ), 700, 500 );
		include $this->admin_tpl ( 'formguide_list' );
	}

	/**
	 * 添加表单向导
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			if ($_POST ['setting'] ['starttime']) {
				$_POST ['setting'] ['starttime'] = strtotime ( $_POST ['setting'] ['starttime'] );
			}
			if ($_POST ['setting'] ['endtime']) {
				$_POST ['setting'] ['endtime'] = strtotime ( $_POST ['setting'] ['endtime'] );
			}
			$_POST ['info'] = $this->check_info ( $_POST ['info'] );
			$_POST ['info'] ['setting'] = array2string ( $_POST ['setting'] );
			$_POST ['info'] ['addtime'] = TIME;
			$_POST ['info'] ['js_template'] = $_POST ['info'] ['show_js_template'];
			$_POST ['info'] ['type'] = 3;
			unset ( $_POST ['info'] ['show_js_template'] );
			$this->tablename = $_POST ['info'] ['tablename'];
			$formid = $this->db->insert ( $_POST ['info'], true );
			define ( 'MODEL_PATH', APPS_PATH . 'formguide' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
			$create_sql = file_get_contents ( MODEL_PATH . 'create.sql' );
			$this->m_db = Loader::model ( 'model_field_model' );
			$this->sql_execute ( $create_sql );
			$form_public_field_array = S ( 'model/form_public_field_array' );
			if (is_array ( $form_public_field_array )) {
				foreach ( $form_public_field_array as $k => $v ) {
					$v ['info'] ['modelid'] = $formid;
					$this->m_db->insert ( $v ['info'] );
					$sql = str_replace ( 'formguide_table', $this->m_db->get_prefix () . 'form_' . $_POST ['info'] ['tablename'], $v ['sql'] );
					$this->m_db->execute ( $sql );
				}
			}
			showmessage ( L ( 'add_success' ), U ( 'formguide/formguide_field/init', array ('formid' => $formid ) ), '', 'add' );
		} else {
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			$formid = isset ( $_GET ['formid'] ) ? intval ( $_GET ['formid'] ) : null;
			$show_header = $show_validator = $show_scroll = 1;
			include $this->admin_tpl ( 'formguide_add' );
		}
	}

	/**
	 * 编辑表单向导
	 */
	public function edit() {
		if (! isset ( $_GET ['formid'] ) || empty ( $_GET ['formid'] )) {
			showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		}
		$formid = intval ( $_GET ['formid'] );
		if (isset ( $_POST ['dosubmit'] )) {
			if ($_POST ['setting'] ['starttime']) {
				$_POST ['setting'] ['starttime'] = strtotime ( $_POST ['setting'] ['starttime'] );
			}
			if ($_POST ['setting'] ['endtime']) {
				$_POST ['setting'] ['endtime'] = strtotime ( $_POST ['setting'] ['endtime'] );
			}
			$_POST ['info'] = $this->check_info ( $_POST ['info'], $formid );
			$_POST ['info'] ['setting'] = array2string ( $_POST ['setting'] );
			$_POST ['info'] ['js_template'] = $_POST ['info'] ['show_js_template'];
			unset ( $_POST ['info'] ['show_js_template'] );
			$this->db->where(array ('modelid' => $formid ))->update ( $_POST ['info'] );
			showmessage ( L ( 'update_success' ), U ( 'formguide/formguide/edit', array ('formid' => $formid ) ), '', 'edit' );
		} else {
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			$data = $this->db->getby_modelid ( $formid );
			$data ['setting'] = string2array ( $data ['setting'] );
			$show_header = $show_validator = $show_scroll = 1;
			include $this->admin_tpl ( 'formguide_edit' );
		}
	}

	/**
	 * 表单向导禁用、开启
	 */
	public function disabled() {
		if (! isset ( $_GET ['formid'] ) || empty ( $_GET ['formid'] )) {
			showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		}
		$formid = intval ( $_GET ['formid'] );
		$val = $_GET ['val'] ? intval ( $_GET ['val'] ) : 0;
		$this->db->where(array ('modelid' => $formid ))->update ( array ('disabled' => $val ) );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 预览
	 */
	public function public_preview() {
		if (! isset ( $_GET ['formid'] ) || empty ( $_GET ['formid'] )) {
			showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		}
		$formid = intval ( $_GET ['formid'] );
		$f_info = $this->db->where ( array ('modelid' => $formid ))->field( 'name' )->find();
		define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
		require CACHE_MODEL_PATH . 'formguide_form.php';
		$formguide_form = new formguide_form ( $formid );
		$forminfos_data = $formguide_form->get ();
		$show_header = 1;
		include $this->admin_tpl ( 'formguide_preview' );
	}

	/**
	 * ajax 检测表是重复
	 */
	public function public_checktable() {
		if (isset ( $_GET ['formid'] ) && ! empty ( $_GET ['formid'] )) {
			$formid = intval ( $_GET ['formid'] );
		}
		$r = $this->db->where ( array ('tablename' => trim ( $_GET ['tablename'] ) ))->field( 'tablename, modelid' )->find();
		if (! isset ( $r ['modelid'] )) {
			exit ( '1' );
		} elseif (isset ( $r ['modelid'] ) && ($r ['modelid'] == $formid)) {
			exit ( '1' );
		} else {
			exit ( '0' );
		}
	}

	/**
	 * 判断表单数据合法性
	 *
	 * @param array $data
	 *        	表单数组
	 * @param intval $formid
	 *        	表单id
	 */
	private function check_info($data = array(), $formid = 0) {
		if (empty ( $data ) || $data ['name'] == '') {
			showmessage ( L ( 'input_form_title' ), HTTP_REFERER );
		}
		if ($data ['tablename'] == '') {
			showmessage ( L ( 'please_input_tallename' ), HTTP_REFERER );
		}
		$r = $this->db->where ( array ('tablename' => $data ['tablename'] ))->field( 'tablename, modelid' )->find();
		if (isset ( $r ['modelid'] ) && (($r ['modelid'] != $formid) || ! $formid)) {
			showmessage ( L ( 'tablename_existed' ), HTTP_REFERER );
		}
		return $data;
	}

	/**
	 * 删除表单向导
	 */
	public function delete() {
		if (isset ( $_GET ['formid'] ) && ! empty ( $_GET ['formid'] )) {
			$formid = intval ( $_GET ['formid'] );
			$m_db = Loader::model ( 'model_field_model' );
			$m_db->where(array ('modelid' => $formid ))->delete (  );
			$m_info = $this->db->where ( array ('modelid' => $formid ))->field( 'tablename' )->find();
			$tablename = $m_db->get_prefix () . 'form_' . $m_info ['tablename'];
			$m_db->execute ( "DROP TABLE `$tablename`" );
			$this->db->where ( array ('modelid' => $formid ) )->delete();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} elseif (isset ( $_POST ['formid'] ) && ! empty ( $_POST ['formid'] )) {
			$m_db = Loader::model ( 'model_field_model' );
			$m_db->where(array ('modelid' => $formid ))->delete (  );
			if (is_array ( $_POST ['formid'] )) {
				foreach ( $_POST ['formid'] as $fid ) {
					$m_info = $this->db->where ( array ('modelid' => $fid ))->field( 'tablename' )->find();
					$tablename = $m_db->get_prefix () . 'form_' . $m_info ['tablename'];
					$m_db->execute ( "DROP TABLE `$tablename`" );
					$this->db->where ( array ('modelid' => $fid ) )->delete();
				}
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		}
	}

	/**
	 * 统计
	 */
	public function stat() {
		if (! isset ( $_GET ['formid'] ) || empty ( $_GET ['formid'] )) showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$formid = intval ( $_GET ['formid'] );
		$fields = S ( 'model/formguide_field_' . $formid );
		$f_info = $this->db->where ( array ('modelid' => $formid ))->field('tablename' )->find();
		$tablename = 'form_' . $f_info ['tablename'];
		$m_db = Loader::model ( 'model_field_model' );
		$result = $m_db->where(array ('modelid' => $formid,'formtype' => 'box' ))->field('fieldid, setting')->select ( );
		$m_db->change_table ( $tablename );
		$datas = $m_db->select ();
		$total = count ( $datas );
		include $this->admin_tpl ( 'formguide_stat' );
	}

	/**
	 * 模块配置
	 */
	public function setting() {
		if (isset ( $_POST ['dosubmit'] )) {
			$setting = $_POST ['setting'];
			S ( 'common/formguide', $setting ); // 设置缓存
			Loader::model ( 'application_model' )->set_setting('formguide',$_POST ['setting']); // 将配置信息存入数据表中
			showmessage ( L ( 'setting_updates_successful' ), HTTP_REFERER, '', 'setting' );
		} else {
			@extract ( $this->M );
			include $this->admin_tpl ( 'setting' );
		}
	}

	/**
	 * 执行sql文件，创建数据表等
	 *
	 * @param string $sql
	 *        	sql语句
	 */
	private function sql_execute($sql) {
		$sqls = $this->sql_split ( $sql );

		if (is_array ( $sqls )) {
			foreach ( $sqls as $sql ) {
				if (trim ( $sql ) != '') {
					$this->m_db->execute ( $sql );
				}
			}
		} else {
			$this->m_db->execute ( $sqls );
		}
		return true;
	}

	/**
	 * 处理sql语句，执行替换前缀都功能。
	 *
	 * @param string $sql
	 *        	原始的sql，将一些大众的部分替换成私有的
	 */
	private function sql_split($sql) {
		$database = C ( 'database' );
		$dbcharset = $database ['default'] ['charset'];
		if ($this->m_db->version () > '4.1' && $dbcharset) {
			$sql = preg_replace ( "/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . $dbcharset, $sql );
		}
		$sql = str_replace ( "yuncms_form_table", $this->m_db->get_prefix () . 'form_' . $this->tablename, $sql );
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
		return $ret;
	}
}
?>