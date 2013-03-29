<?php
/**
 * 系统异常基类
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Exception.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Core_Exception  extends Exception{


	/**
	 * 架构函数
	 *
	 * @param string $message 异常信息
	 */
	public function __construct($message, $code = 0) {
		parent::__construct ( $message, $code );
		if (C ( 'log', 'log_threshold' ) != 0) {// 记录 Exception 日志
			log_message ( 'error', 'Severity: ' . $message , TRUE );
		}
	}
}