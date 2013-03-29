<?php
/**
 * 模版类
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-26
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: View.php 2 2013-01-14 07:14:05Z xutongle $
 */
class View extends Core_View{
	/**
	 * 当前视图实例
	 *
	 * @var object
	 */
	protected static $instance = null;

	public static function &instance() {
		if (null === self::$instance) {
			self::$instance = new self ();
		}
		return self::$instance;
	}
}