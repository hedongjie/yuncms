<?php
/**
 * Session Xcache驱动
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-14
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Xcache.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Session_Driver_Xcache extends Session_Abstract {

	public function __construct($options = array()) {
		if (! $this->test ()) {
			throw_exception ( "The xcache extension isn't available" );
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
		if (! xcache_isset ( $sess_id )) return;
		return ( string ) xcache_get ( $sess_id );
	}

	public function write($id, $session_data) {
		$sess_id = 'sess_' . $id;
		return xcache_set ( $sess_id, $session_data, ini_get ( "session.gc_maxlifetime" ) );
	}

	public function destroy($id) {
		$sess_id = 'sess_' . $id;
		if (! xcache_isset ( $sess_id )) return true;
		return xcache_unset ( $sess_id );
	}

	public function gc($maxlifetime) {
		return true;
	}

	public function test() {
		return extension_loaded ( 'xcache' );
	}
}