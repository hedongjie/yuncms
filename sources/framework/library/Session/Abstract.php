<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-14
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Abstract.php 2 2013-01-14 07:14:05Z xutongle $
 */
abstract class Session_Abstract {

	public function __construct($options = array()) {
	}

	public static function &get_instance($options = array()) {
		$class = 'Session_Driver_' . ucfirst ( $options ['driver'] );
		$return = new $class ( $options );
		return $return;
	}

	public function register() {
		session_set_save_handler ( array (
				$this,
				'open'
		), array (
				$this,
				'close'
		), array (
				$this,
				'read'
		), array (
				$this,
				'write'
		), array (
				$this,
				'destroy'
		), array (
				$this,
				'gc'
		) );
	}
}