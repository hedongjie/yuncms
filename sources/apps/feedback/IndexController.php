<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
error_reporting ( E_ERROR );
class IndexController {
	private $db, $m_db, $M;
	function __construct() {
		$this->db = Loader::model ( 'model_model' );
		$this->m_db = Loader::model ( 'model_field_model' );
		$this->M = new_htmlspecialchars ( S ( 'common/feedback' ) );
		$mode_info = $this->model_db->get_one ( array ('type' => 4 ), '*' );
		$this->modelid = $mode_info ['modelid'];
	}

	/**
	 * 留言反馈首页
	 */
	public function index() {
		$SEO = seo ( '', L ( 'feedback' ) );
		$userid = cookie_get ( '_userid' );
		if ($this->M ['allowunreg'] == 0 && ! $userid && $_GET ['do'] != 'js') showmessage ( L ( 'please_login_in' ), U ( 'member/passport/login', array ('forward' => urlencode ( HTTP_REFERER ) ) ) );
		if ($this->M ['allowunreg'] == 0 && ! $userid && $_GET ['do'] == 'js') $no_allowed = 1;
		$f_info = $this->db->get_one ( array ('modelid' => $this->modelid ) );
		extract ( $f_info );
		$where = array ();
		if ($userid)
			$where = array ('userid' => $userid );
		else
			$where = array ('ip' => IP );
		$re = $this->m_db->get_one ( $where, 'datetime' );
		$setting = string2array ( $setting );
		if (($setting ['allowmultisubmit'] == 0 && isset ( $re ['datetime'] )) || ((TIME - $re ['datetime']) < $this->M ['interval'] * 60)) {
			$_GET ['act'] ? exit () : showmessage ( L ( 'had_participate' ), U ( 'feedback/index/index' ) );
		}

		require CACHE_MODEL_PATH . 'feedback_form.php';
		$formguide_form = new feedback_form ( $formid, $no_allowed );
		$forminfos_data = $formguide_form->get ();
		$SEO = seo ( L ( 'feedback' ), $name );
		if (isset ( $_GET ['do'] ) && $_GET ['do'] == 'js') {
			if (! function_exists ( 'ob_gzhandler' )) ob_clean ();
			ob_start ();
		}
		$template = ($_GET ['do'] == 'js') ? $js_template : $show_template;
		include template ( 'feedback', $template, $default_style );
		if (isset ( $_GET ['do'] ) && $_GET ['do'] == 'js') {
			$data = ob_get_contents ();
			ob_clean ();
			exit ( format_js ( $data ) );
		}
	}

	/**
	 * 提交
	 */
	public function add() {
			$tablename = 'form_' . $r ['tablename'];
			$this->m_db->change_table ( $tablename );
			$data = array ();
			require CACHE_MODEL_PATH . 'formguide_input.php';
			$formguide_input = new formguide_input ( $formid );
			$data = $formguide_input->get ( $_POST ['info'] );
			$data ['userid'] = ! empty ( $userid ) ? $userid : 0;
			$data ['username'] = cookie_get ( '_username' );
			$data ['datetime'] = TIME;
			$data ['ip'] = IP;
			$dataid = $this->m_db->insert ( $data, true );
			if ($dataid) {
				if ($setting ['sendmail']) {
					$mails = explode ( ',', $setting ['mails'] );
					if (is_array ( $mails )) {
						foreach ( $mails as $m ) {
							sendmail ( $m, L ( 'tips' ), $this->M ['mailmessage'] );
						}
					}
				}
				$this->db->update ( array ('items' => '+=1' ), array ('modelid' => $formid ) );
			}
			showmessage ( L ( 'thanks' ), SITE_URL );

	}
}
?>