<?php
/**
 * 关联连接管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: KeylinkController.php 19 2012-11-05 10:09:53Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class KeylinkController extends admin {
	public function __construct() {
		$this->db = Loader::model ( 'keylink_model' );
		parent::__construct ();
	}

	function init() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->order('keylinkid DESC')->listinfo ($page, 20 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'admin/keylink/add' ), 'add', L ( 'add_keylink' ), 500, 120 );
		include $this->admin_tpl ( 'keylink_list' );
	}

	/**
	 * 验证数据有效性
	 */
	public function public_name() {
		$word = isset ( $_GET ['word'] ) && trim ( $_GET ['word'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['word'] ) ) : trim ( $_GET ['word'] )) : exit ( '0' );
		$keylinkid = isset ( $_GET ['keylinkid'] ) && intval ( $_GET ['keylinkid'] ) ? intval ( $_GET ['keylinkid'] ) : '';
		$data = array ();
		if ($keylinkid) {
			$data = $this->db->where( array ('keylinkid' => $keylinkid ))->field( 'word' )->find();
			if (! empty ( $data ) && $data ['word'] == $word) exit ( '1' );
		}
		if ($this->db->where ( array ('word' => $word ))->field( 'keylinkid' )->find()) exit ( '0' );
		else exit ( '1' );
	}

	/**
	 * 关联词添加
	 */
	function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			if (empty ( $_POST ['info'] ['word'] ) || empty ( $_POST ['info'] ['url'] )) return false;
			$this->db->insert ( $_POST ['info'] );
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), U ( 'admin/keylink/add' ), '', 'add' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			include $this->admin_tpl ( 'keylink_add' );
		}
	}

	/**
	 * 关联词修改
	 */
	function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$keylinkid = intval ( $_GET ['keylinkid'] );
			if (empty ( $_POST ['info'] ['word'] ) || empty ( $_POST ['info'] ['url'] )) return false;
			$this->db->where(array ('keylinkid' => $keylinkid ))->update ( $_POST ['info'] );
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), U ( 'admin/keylink/edit' ), '', 'edit' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			$info = $this->db->getby_keylinkid( $_GET ['keylinkid'] );
			if (! $info) showmessage ( L ( 'specified_word_not_exist' ) );
			extract ( $info );
			include $this->admin_tpl ( 'keylink_edit' );
		}
	}

	/**
	 * 关联词删除
	 */
	function delete() {
		if (isset($_POST ['keylinkid']) && is_array ( $_POST ['keylinkid'] )) {
			foreach ( $_POST ['keylinkid'] as $keylinkid_arr ) {
				$this->db->where(array ('keylinkid' => $keylinkid_arr ))->delete (  );
			}
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), U ( 'admin/keylink' ) );
		} else {
			$keylinkid = intval ( $_GET ['keylinkid'] );
			if ($keylinkid < 1) return false;
			$result = $this->db->where(array ('keylinkid' => $keylinkid ))->delete (  );
			$this->public_cache_file (); // 更新缓存
			if ($result) {
				showmessage ( L ( 'operation_success' ), U ( 'admin/keylink' ) );
			} else {
				showmessage ( L ( "operation_failure" ), U ( 'admin/keylink' ) );
			}
		}
	}

	/**
	 * 生成缓存
	 */
	public function public_cache_file() {
		$infos = $this->db->field('word,url')->order('keylinkid ASC')->select();
		$datas = array();
		if($infos && is_array($infos)){
			foreach($infos as $r) {
				$datas[] = array(0=>$r['word'],1=>$r['url']);
			}
		}
		S ( 'common/keylink', $datas );
		return true;
	}
}