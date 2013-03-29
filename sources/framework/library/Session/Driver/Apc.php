<?php
/**
 * Session Apc驱动
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-14
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Apc.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Session_Driver_Apc extends Session_Abstract {

	public function __construct($options = array()) {
		if (! $this->test ()) {
			throw_exception ( "The apc extension isn't available" );
		}
		$this->register ();
	}

	public function open($save_path, $session_name) {
		return true;
	}

	public function close() {
		return true;
	}

	public function read($id) {
		$sess_id = 'sess_' . $id;
		return ( string ) apc_fetch ( $sess_id );
	}

	public function write($id, $session_data) {
		$sess_id = 'sess_' . $id;
		return apc_store ( $sess_id, $session_data, ini_get ( "session.gc_maxlifetime" ) );
	}

	public function destroy($id) {
		$sess_id = 'sess_' . $id;
		return apc_delete ( $sess_id );
	}

	public function gc($maxlifetime) {
		return true;
	}

	public function test() {
		return extension_loaded ( 'apc' );
	}
}