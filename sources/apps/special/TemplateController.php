<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
Loader::helper ( 'special:global' );
error_reporting ( E_ERROR );
class TemplateController extends admin {
	private $db;
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'special_model' );
	}

	/**
	 * 编辑专题首页模板
	 */
	public function init() {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$specialid = isset ( $_GET ['specialid'] ) && intval ( $_GET ['specialid'] ) ? intval ( $_GET ['specialid'] ) : showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		;
		if (! $specialid) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );

		$info = $this->db->where ( array ('id' => $specialid,'disabled' => '0' ) )->find();
		if (! $info ['id']) showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$id = $specialid;
		if ($info ['css']) $css_param = unserialize ( $info ['css'] );
		if (! $info ['ispage']) {
			$type_db = Loader::model ( 'type_model' );
			$types = $type_db->where(array ('application' => 'special','parentid' => $id ))->order('listorder ASC, typeid ASC')->select ();
		}
		extract ( $info );
		$css = get_css ( $css_param );
		$template = $info ['index_template'] ? $info ['index_template'] : 'index';
		Loader::helper ( 'template:global' );
		ob_start ();
		include template ( 'special', $template );
		$html = ob_get_contents ();
		ob_clean ();
		$html = visualization ( $html, 'default', 'test', 'block.html' );
		include $this->admin_tpl ( 'template_edit' );
	}

	/**
	 * css编辑预览
	 */
	public function preview() {
		define ( 'HTML', true );
		if (! $_GET ['specialid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		$info = $this->db->where ( array ('id' => $_GET ['specialid'],'disabled' => '0' ) )->find();
		if (! $info ['id']) showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$css = get_css ( $_POST ['info'] );
		$template = $info ['index_template'] ? $info ['index_template'] : 'index';
		include template ( 'special', $template );
	}

	/**
	 * css添加
	 */
	public function add() {
		if (! $_GET ['specialid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		$info = $this->db->where ( array ('id' => $_GET ['specialid'],'disabled' => '0' ) )->find();
		if (! $info ['id']) showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$data = serialize ( $_POST ['info'] );
		$this->db->where(array ('id' => $info ['id'] ))->update ( array ('css' => $data ) );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}
}