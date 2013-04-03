<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class FeedbackController extends admin {

	private $db, $tablename, $m_db, $M;
	public function __construct() {
		parent::__construct ();
		$this->tablename = '';
		$this->M = new_htmlspecialchars ( S ( 'common/feedback' ) );
		$this->db = Loader::model ( 'feedback_model' );
	}

	/**
	 * 留言列表
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$datas = $this->db->listinfo (array(), '`fid` DESC' );
		include $this->admin_tpl ( 'feedback_list' );
	}

	/**
	 * 预览
	 */
	public function public_preview() {
		if (! isset ( $_GET ['formid'] ) || empty ( $_GET ['formid'] )) {
			showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		}
		$formid = intval ( $_GET ['formid'] );
		$f_info = $this->db->get_one ( array ('modelid' => $formid ), 'name' );
		define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
		require CACHE_MODEL_PATH . 'formguide_form.php';
		$formguide_form = new formguide_form ( $formid );
		$forminfos_data = $formguide_form->get ();
		$show_header = 1;
		include $this->admin_tpl ( 'formguide_preview' );
	}

	/**
	 * 统计
	 */
	public function stat() {
		if (! isset ( $_GET ['formid'] ) || empty ( $_GET ['formid'] )) showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$formid = intval ( $_GET ['formid'] );
		$fields = S ( 'model/formguide_field_' . $formid );
		$f_info = $this->db->get_one ( array ('modelid' => $formid ), 'tablename' );
		$tablename = 'form_' . $f_info ['tablename'];
		$m_db = Loader::model ( 'model_field_model' );
		$result = $m_db->select ( array ('modelid' => $formid,'formtype' => 'box' ), 'fieldid, setting' );
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
			W ( 'common/feedback', $setting ); // 设置缓存
			$m_db = Loader::model ( 'application_model' ); // 调用模块数据模型
			$setting = array2string ( $_POST ['setting'] );
			$m_db->update ( array ('setting' => $setting ), array ('application' => ROUTE_APP ) ); // 将配置信息存入数据表中
			showmessage ( L ( 'setting_updates_successful' ), HTTP_REFERER, '', 'setting' );
		} else {
			@extract ( $this->M );
			include $this->admin_tpl ( 'setting' );
		}
	}
}
?>