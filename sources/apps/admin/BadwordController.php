<?php
/**
 * 敏感词管理
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class BadwordController extends admin {
	public function __construct() {
		$admin_username = cookie ( 'admin_username' );
		$userid = $_SESSION ['userid'];
		$this->db = Loader::model ( 'badword_model' );
		parent::__construct ();
	}

	/**
	 * 敏感词管理
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $pages = '';
		$infos = $this->db->order ( 'badid DESC' )->listinfo ( $page, 13 );
		$pages = $this->db->pages;
		$level = array (1 => L ( 'general' ),2 => L ( 'danger' ) );
		$big_menu = big_menu ( U ( 'admin/badword/add' ), 'add', L ( 'badword_add' ), 450, 180 );
		include $this->admin_tpl ( 'badword_list' );
	}

	/**
	 * 敏感词添加
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['info'] ['lastusetime'] = TIME;
			$_POST ['info'] ['replaceword'] = str_replace ( "　", "", trim ( $_POST ['replaceword'] ) );
			$_POST ['info'] ['badword'] = str_replace ( "　", "", trim ( $_POST ['badword'] ) );
			if (empty ( $_POST ['info'] ['badword'] )) showmessage ( L ( 'enter_word' ), U ( 'admin/badword/add' ) );
			$this->db->insert ( $_POST ['info'] );
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), '', '', 'add' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			include $this->admin_tpl ( 'badword_add' );
		}
	}

	/**
	 * 检查敏感词是否存在
	 */
	public function public_name() {
		$badword = isset ( $_GET ['badword'] ) && trim ( $_GET ['badword'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['badword'] ) ) : trim ( $_GET ['badword'] )) : exit ( '0' );
		$badid = isset ( $_GET ['badid'] ) && intval ( $_GET ['badid'] ) ? intval ( $_GET ['badid'] ) : '';
		$data = array ();
		if ($badid) {
			$data = $this->db->field ( 'badword' )->where ( array ('badid' => $badid ) )->find ();
			if (! empty ( $data ) && $data ['badword'] == $badword) exit ( '1' );
		}
		if ($this->db->field ( 'badid' )->where ( array ('badword' => $badword ) )->find ())
			exit ( '0' );
		else
			exit ( '1' );
	}

	/**
	 * 敏感词修改
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$badid = intval ( $_GET ['badid'] );
			$_POST ['info'] ['replaceword'] = str_replace ( "　", "", trim ( $_POST ['replaceword'] ) );
			$_POST ['info'] ['badword'] = str_replace ( "　", "", trim ( $_POST ['badword'] ) );
			$this->db->where ( array ('badid' => $badid ) )->update ( $_POST ['info'] );
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), '', '', 'edit' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			$info = array ();
			$info = $this->db->where ( array ('badid' => $_GET ['badid'] ) )->find ();
			if (! $info) showmessage ( L ( 'keywords_no_exist' ) );
			extract ( $info );
			include $this->admin_tpl ( 'badword_edit' );
		}
	}

	/**
	 * 关键词删除 包含批量删除 单个删除
	 */
	public function delete() {
		if (isset ( $_POST ['badid'] ) && is_array ( $_POST ['badid'] )) {
			foreach ( $_POST ['badid'] as $badid_arr ) {
				$this->db->where ( array ('badid' => $badid_arr ) )->delete ();
			}
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), U ( 'admin/badword/init' ) );
		} else {
			$badid = intval ( $_GET ['badid'] );
			if ($badid < 1) return false;
			$result = $this->db->where ( array ('badid' => $badid ) )->delete ();
			if ($result) {
				$this->public_cache_file (); // 更新缓存
				showmessage ( L ( 'operation_success' ), U ( 'admin/badword/init' ) );
			} else {
				showmessage ( L ( "operation_failure" ), U ( 'admin/badword/init' ) );
			}
		}
	}

	/**
	 * 导出敏感词为文本 一行一条记录
	 */
	public function export() {
		$result = $str = '';
		$result = $this->db->order ( 'badid DESC' )->select ();
		if (! is_array ( $result ) || empty ( $result )) showmessage ( L ( 'badword_no' ), U ( 'admin/badword/init' ) );
		foreach ( $result as $s ) {
			$str .= $s ['badword'] . ',' . $s ['replaceword'] . ',' . $s ['level'] . "\n";
		}
		$filename = L ( 'export' );
		header ( 'Content-Type: text/x-sql' );
		header ( 'Expires: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		$is_ie = 'IE';
		if ($is_ie == 'IE') {
			header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header ( 'Pragma: public' );
		} else {
			header ( 'Pragma: no-cache' );
			header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
		}
		echo $str;
		exit ();
	}

	/**
	 * 从文本中导入敏感词, 一行一条记录
	 */
	public function import() {
		if (isset ( $_POST ['dosubmit'] )) {
			$arr = $s = $str = $level_arr = '';
			$s = trim ( $_POST ['info'] );
			if (empty ( $s )) showmessage ( L ( 'not_information' ), U ( 'admin/badword/import' ) );
			$arr = explode ( "\n", $s );
			if (! is_array ( $arr ) || empty ( $arr )) return false;
			foreach ( $arr as $s ) {
				$level_arr = array ("1","2" );
				$str = explode ( ",", $s );
				$sql_str = array ();
				$sql_str ['badword'] = $str [0];
				$sql_str ['replaceword'] = $str [1];
				$sql_str ['level'] = $str [2];
				$sql_str ['lastusetime'] = TIME;
				if (! in_array ( $sql_str ['level'], $level_arr )) $sql_str ['level'] = '1';
				if (empty ( $sql_str ['badword'] ))
					continue;
				else {
					$check_badword = $this->db->where ( array ('badword' => $sql_str ['badword'] ) )->find ();
					if ($check_badword) continue;
					$this->db->insert ( $sql_str );
				}
				unset ( $sql_str, $check_badword );
			}
			showmessage ( L ( 'operation_success' ), U ( 'admin/badword/init' ) );
		} else {
			include $this->admin_tpl ( 'badword_import' );
		}
	}

	/**
	 * 生成缓存
	 */
	public function public_cache_file() {
		$infos = $this->db->field ( 'badid,badword,replaceword,level' )->order ( 'badid ASC' )->select ();
		S ( 'common/badword', $infos );
		return true;
	}
}