<?php
/**
 * Session File驱动
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-14
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: File.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Session_Driver_File extends Session_Abstract {

	public function __construct($options = array()) {
		$path = $options ['session_n'] > 0 ? $options ['session_n'] . ';"' . $options ['session_path'] . '"' : $options ['session_path'];
		ini_set ( 'session.save_handler', 'files' );
		session_save_path ( $path );
	}
}