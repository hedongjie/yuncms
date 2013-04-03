<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
error_reporting ( E_ERROR );
class IndexController {
	private $db, $m_db, $M;
	public function __construct() {
		$this->db = Loader::model ( 'model_model' );
		$this->m_db = Loader::model ( 'model_field_model' );
		$this->M = S ( 'common/formguide' );
	}

	/**
	 * 表单向导首页
	 */
	public function init() {
		$SEO = seo ( '', L ( 'formguide_list' ) );
		$page = max ( intval ( $_GET ['page'] ), 1 );
		$datas = $this->db->where ( array ('type' => 3,'disabled' => 0 ) )->field ( 'modelid, name, addtime' )->order ( 'modelid DESC' )->listinfo ();
		include template ( 'formguide', 'index' );
	}

	/**
	 * 表单展示
	 */
	public function show() {
		if (! isset ( $_GET ['formid'] ) || empty ( $_GET ['formid'] )) {
			$_GET ['do'] ? exit () : showmessage ( L ( 'form_no_exist' ), HTTP_REFERER );
		}
		$formid = intval ( $_GET ['formid'] );
		$r = $this->db->where ( array ('modelid' => $formid,'disabled' => 0 ))->field( 'tablename, setting' )->find();
		if (! $r) {
			$_GET ['do'] ? exit () : showmessage ( L ( 'form_no_exist' ), HTTP_REFERER );
		}
		$setting = string2array ( $r ['setting'] );
		if ($setting ['enabletime']) {
			if ($setting ['starttime'] > TIME || ($setting ['endtime'] + 3600 * 24) < TIME) {
				$_GET ['do'] ? exit () : showmessage ( L ( 'form_expired' ), U ( 'formguide/index' ) );
			}
		}
		$userid = cookie ( '_userid' );
		if ($setting ['allowunreg'] == 0 && ! $userid && $_GET ['do'] != 'js') showmessage ( L ( 'please_login_in' ), U ( 'member/passport/login', array ('forward' => urlencode ( HTTP_REFERER ) ) ) );
		if (isset ( $_POST ['dosubmit'] )) {
			$tablename = 'form_' . $r ['tablename'];
			$this->m_db->change_table ( $tablename );
			$data = array ();
			require CACHE_MODEL_PATH . 'formguide_input.php';
			$formguide_input = new formguide_input ( $formid );
			$data = $formguide_input->get ( $_POST ['info'] );
			$data ['userid'] = ! empty ( $userid ) ? $userid : 0;
			$data ['username'] = cookie ( '_username' );
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
				$this->db->where(array ('modelid' => $formid ))->update ( array ('items' => '+=1' ) );
			}
			showmessage ( L ( 'thanks' ), SITE_URL );
		} else {
			if ($setting ['allowunreg'] == 0 && ! $userid && $_GET ['do'] == 'js') {
				$no_allowed = 1;
			}
			$f_info = $this->db->getby_modelid ( $formid );
			extract ( $f_info );
			$tablename = 'form_' . $r ['tablename'];
			$this->m_db->change_table ( $tablename );
			$ip = IP;
			$where = array ();
			if ($userid)
				$where = array ('userid' => $userid );
			else
				$where = array ('ip' => $ip );
			$re = $this->m_db->where($where)->field ( 'datetime' )->find();
			$setting = string2array ( $setting );
			if (($setting ['allowmultisubmit'] == 0 && isset ( $re ['datetime'] )) || ((TIME - $re ['datetime']) < $this->M ['interval'] * 60)) {
				$_GET ['act'] ? exit () : showmessage ( L ( 'had_participate' ), U ( 'formguide/index/index' ) );
			}

			require CACHE_MODEL_PATH . 'formguide_form.php';
			$formguide_form = new formguide_form ( $formid, $no_allowed );
			$forminfos_data = $formguide_form->get ();
			$SEO = seo ( L ( 'formguide' ), $name );
			if (isset ( $_GET ['do'] ) && $_GET ['do'] == 'js') {
				if (! function_exists ( 'ob_gzhandler' )) ob_clean ();
				ob_start ();
			}
			$template = ($_GET ['do'] == 'js') ? $js_template : $show_template;
			include template ( 'formguide', $template, $default_style );
			if (isset ( $_GET ['do'] ) && $_GET ['do'] == 'js') {
				$data = ob_get_contents ();
				ob_clean ();
				exit ( format_js ( $data ) );
			}
		}
	}
}
?>