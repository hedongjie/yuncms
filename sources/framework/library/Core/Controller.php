<?php
/**
 * 核心控制器基类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Controller.php 2 2013-01-14 07:14:05Z xutongle $
 */
abstract class Core_Controller {

	/**
	 * 模板变量
	 *
	 * @var tVar
	 * @access protected
	 */
	protected $config = array ();

	/**
	 * 构造方法
	 */
	public function __construct() {
		$this->config = C ( 'config' );
		Core_Filter::remove_xss (); // 后台将不过滤XSS
		$this->app = APP; // 当前的app
		$this->controller = CONTROLLER; // 当前控制器
		$this->action = ACTION; // 当前操作
		if (method_exists ( $this, '_initialize' )) $this->_initialize (); // 控制器初始化
	}

	public static function set_token() {
		$_SESSION ['token'] = md5 ( microtime ( true ) );
	}

	public static function valid_token() {
		$return = $_REQUEST ['token'] === $_SESSION ['token'] ? true : false;
		self::set_token ();
		return $return;
	}

	/**
	 * Ajax方式返回数据到客户端
	 *
	 * @access protected
	 * @param mixed $data 要返回的数据
	 * @param String $type AJAX返回数据格式
	 * @return void
	 */
	protected function ajax_return($data, $type = '') {
		if (empty ( $type )) $type = C ( 'config', 'default_ajax_return' );
		switch (strtoupper ( $type )) {
			case 'JSON' :
				// 返回JSON数据格式到客户端 包含状态信息
				header ( 'Content-Type:application/json; charset=utf-8' );
				exit ( json_encode ( $data ) );
			case 'XML' :
				// 返回xml格式数据
				header ( 'Content-Type:text/xml; charset=utf-8' );
				exit ( Loader::lib ( 'Xml' )->serialize ( $data ) );
			case 'JSONP' :
				// 返回JSON数据格式到客户端 包含状态信息
				header ( 'Content-Type:application/json; charset=utf-8' );
				$handler = isset ( $_GET ['callback'] ) ? $_GET ['callback'] : C ( 'config', 'default_jsonp_callback' );
				exit ( $handler . '(' . json_encode ( $data ) . ');' );
			case 'EVAL' :
				// 返回可执行的js脚本
				header ( 'Content-Type:text/html; charset=utf-8' );
				exit ( $data );
			default :
				// 用于扩展其他返回格式数据
				header ( 'Content-Type:application/json; charset=utf-8' );
				exit ( json_encode ( $data ) );
		}
	}

	/**
	 * 提示信息页面跳转，跳转地址如果传入数组，页面会提示多个地址供用户选择，默认跳转地址为数组的第一个值，时间为5秒。
	 * showmessage('登录成功', array('默认跳转地址'=>'http://www.yuncms.net'));
	 *
	 * @param string $msg 提示信息
	 * @param mixed(string/array) $url_forward 跳转地址
	 * @param int $ms 跳转等待时间
	 */
	public function showmessage($msg, $url_forward = 'goback', $ms = 1250) {
		if ($ms == 301) {
			Loader::session ();
			$_SESSION ['msg'] = $msg;
			Header ( "HTTP/1.1 301 Moved Permanently" );
			Header ( "Location: $url_forward" );
			exit ();
		}
		if (defined ( 'IN_ADMIN' )) {
			include (admin::admin_tpl ( 'showmessage', 'admin' ));
		} else {
			include (template ( 'yuncms', 'message' ));
		}
		if (isset ( $_SESSION ['msg'] )) unset ( $_SESSION ['msg'] );
		exit ();
	}

	/**
	 * 析构方法
	 */
	public function __destruct() {

	}
}