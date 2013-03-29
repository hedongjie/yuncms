<?php
/**
 * 历史版本
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Template_bakController.php 49 2012-11-05 12:45:37Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class Template_bakController extends admin {
	private $db, $style, $dir, $filename, $filepath, $fileid;
	public function __construct() {
		parent::__construct ();
		$this->style = isset ( $_GET ['style'] ) && trim ( $_GET ['style'] ) ? str_replace ( array ('..\\','../','./','.\\','/','\\' ), '', trim ( $_GET ['style'] ) ) : showmessage ( L ( 'illegal_operation' ) );
		$this->dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? trim ( urldecode ( $_GET ['dir'] ) ) : showmessage ( L ( 'illegal_operation' ) );
		$this->dir = safe_replace ( $this->dir );
		$this->filename = isset ( $_GET ['filename'] ) && trim ( $_GET ['filename'] ) ? trim ( $_GET ['filename'] ) : showmessage ( L ( 'illegal_operation' ) );
		if (empty ( $this->style ) || empty ( $this->dir ) || empty ( $this->filename )) showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$this->filepath = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . $this->style . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . $this->filename;
		$this->fileid = $this->style . '_' . $this->dir . '_' . $this->filename;
		$this->db = Loader::model ( 'template_bak_model' );
	}
	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$list = $this->db->where(array ('fileid' => $this->fileid ))->order('creat_at desc')->listinfo ($page, 20 );
		if (! $list) showmessage ( L ( 'not_exist_versioning' ), 'blank' );
		$pages = $this->db->pages;
		$show_header = true;
		include $this->admin_tpl ( 'template_bak_list' );
	}
	public function restore() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		if ($data = $this->db->getby_id ( $id )) {
			if (! is_writable ( $this->filepath )) showmessage ( L ( "file_does_not_writable" ), HTTP_REFERER );
			if (@file_put_contents ( $this->filepath, $data ['template'] ))
				showmessage ( L ( 'operation_success' ), HTTP_REFERER, '', 'history' );
			else
				showmessage ( L ( 'operation_success' ), HTTP_REFERER, '', 'history' );
		} else
			showmessage ( L ( 'notfound' ), HTTP_REFERER );
	}
	public function del() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		if ($data = $this->db->getby_id ( $id )) {
			$this->db->where ( array ('id' => $id ) )->delete();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else
			showmessage ( L ( 'notfound' ), HTTP_REFERER );
	}
}