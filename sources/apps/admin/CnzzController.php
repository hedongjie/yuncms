<?php
/**
 * CNZZ站长统计
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class CnzzController extends admin {

	private $cnzz;

	public function __construct() {
		parent::__construct ();
		$this->cnzz = S ( 'common/cnzz' );
	}

	public function init() {
		if (empty ( $this->cnzz )) {
			showmessage ( L ( 'reg_msg' ) );
		} else {
			$config = & $this->cnzz;
			header ( 'location:http://wss.cnzz.com/user/companion/site_login.php?site_id=' . $config ['username'] . '&password=' . $config ['password'] );
		}
	}

	public function public_regcnzz() {
		if (empty ( $this->cnzz )) {
			$key = md5 ( SITE_HOST . 'AfdF45Ge' );
			if ($data = @file_get_contents ( 'http://wss.cnzz.com/user/companion/site.php?domain=' . SITE_HOST . '&key=' . $key )) {
				if (substr ( $data, 0, 1 ) == '-') {
					showmessage ( L ( 'application_fails' ) );
				} else {
					$data = explode ( '@', $data );
					$data ['username'] = $data [0];
					$data ['password'] = $data [1];
					unset ( $data [0], $data [1] );
					S ( 'common/cnzz', $data );
					showmessage ( L ( 'success' ), U ( 'admin/cnzz/init' ) );
				}
			} else {
				showmessage ( L ( 'donot_connect_server' ) );
			}
		} else {
			showmessage ( L ( 'has_been_registered' ) );
		}
	}
}