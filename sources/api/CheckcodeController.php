<?php
/**
 * 验证码显示Api
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-12
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: CheckcodeController.php 2 2013-01-14 07:14:05Z xutongle $
 */
class CheckcodeController {
	private $checkcode;

	public function __construct() {

	}

	/**
	 * 显示验证码
	 */
	public function init() {
		// 获取验证码配置
		$config = array ();
		if (isset ( $_GET ['height'] ) && intval ( $_GET ['height'] )) {
			$config ['height'] = intval ( $_GET ['height'] );
			if ($config ['height'] <= 0) $config ['height'] = 38;
		}
		if (isset ( $_GET ['width'] ) && intval ( $_GET ['width'] )) {
			$config ['width'] = intval ( $_GET ['width'] );
			if ($config ['width'] <= 0) $config ['width'] = 120;
		}

		if (isset ( $_GET ['code_len'] ) && intval ( $_GET ['code_len'] )) {
			$config ['complexity'] = intval ( $_GET ['code_len'] );
			if ($config ['complexity'] > 8 || $config ['complexity'] < 2) $config ['complexity'] = 4;
		}
		if (isset ( $_GET ['background'] ) && trim ( urldecode ( $_GET ['background'] ) ) && preg_match ( '/(^#[a-z0-9]{6}$)/im', trim ( urldecode ( $_GET ['background'] ) ) )) $config ['background'] = trim ( urldecode ( $_GET ['background'] ) );
		$this->checkcode = new Checkcode ( $config );
		$this->checkcode->render ();
	}

	/**
	 * AJAX验证验证码是否正确
	 */
	public function check() {
		if (checkcode ( $_GET ['code'] )) exit ( '1' );
		exit ( '0' );
	}
}