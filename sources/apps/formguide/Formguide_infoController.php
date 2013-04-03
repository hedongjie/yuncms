<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class Formguide_infoController extends admin {

	private $db, $f_db, $tablename;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'model_field_model' );
		$this->f_db = Loader::model ( 'model_model' );
		if (isset ( $_GET ['formid'] ) && ! empty ( $_GET ['formid'] )) {
			$formid = intval ( $_GET ['formid'] );
			$f_info = $this->f_db->where ( array ('modelid' => $formid ) )->field ( 'tablename' )->find();
			$this->tablename = 'form_' . $f_info ['tablename'];
			$this->db->change_table ( $this->tablename );
		}
	}

	/**
	 * 用户提交表单信息列表
	 */
	public function init() {
		if (! isset ( $_GET ['formid'] ) || empty ( $_GET ['formid'] )) {
			showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		}
		$formid = intval ( $_GET ['formid'] );
		if (! $this->tablename) {
			$f_info = $this->f_db->where ( array ('modelid' => $formid ))->field( 'tablename' )->find();
			$this->tablename = 'form_' . $f_info ['tablename'];
			$this->db->change_table ( $this->tablename );
		}
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$datas = $this->db->order('dataid DESC')->listinfo ();
		$big_menu = big_menu ( '?app=formguide&controller=formguide&action=add', 'add', L ( 'formguide_add' ), 700, 500 );
		include $this->admin_tpl ( 'formguide_info_list' );
	}

	/**
	 * 查看
	 */
	public function public_view() {
		if (! $this->tablename || ! isset ( $_GET ['did'] ) || empty ( $_GET ['did'] )) showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$did = intval ( $_GET ['did'] );
		$formid = intval ( $_GET ['formid'] );
		$info = $this->db->getby_dataid ($did  );
		define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
		require CACHE_MODEL_PATH . 'formguide_output.php';
		$formguide_output = new formguide_output ( $formid );
		$forminfos_data = $formguide_output->get ( $info );
		$fields = $formguide_output->fields;
		include $this->admin_tpl ( 'formguide_info_view' );
	}

	/**
	 * 删除
	 */
	public function public_delete() {
		$formid = intval ( $_GET ['formid'] );
		if (isset ( $_GET ['did'] ) && ! empty ( $_GET ['did'] )) {
			$did = intval ( $_GET ['did'] );
			$this->db->where(array ('dataid' => $did ))->delete (  );
			$this->f_db->where(array ('modelid' => $formid ))->update ( array ('items' => '-=1' ) );
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else if (is_array ( $_POST ['did'] ) && ! empty ( $_POST ['did'] )) {
			foreach ( $_POST ['did'] as $did ) {
				$did = intval ( $did );
				$this->db->where( array ('dataid' => $did ))->delete ( );
				$this->f_db->where(array ('modelid' => $formid ))->update ( array ('items' => '-=1' ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		}
	}
}
?>