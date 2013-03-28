<?php
/**
 * HttpSQS队列驱动器
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-14
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: HttpSQS.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Queue_Driver_HttpSQS extends Queue_Abstract {

	public $server;
	public $port;
	public $auth;
	public $charset;

	public function __construct($options) {
		$this->url = 'http://' . $options ['server'] . ':' . $options ['port'] . '/';
		$this->auth = $options ['auth'];
		$this->charset = CHARSET;
		$this->http = new HttpClient ();
		return true;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Queue::put()
	 */
	public function put($name, $data) {
		$result = $this->http->get ( $this->url . '?auth=' . $this->auth . '&charset=' . $this->charset . '&name=' . $name . '&opt=put&data=' . urlencode ( $data ) );
		if ($result == "HTTPSQS_PUT_OK") {
			return true;
		} else if ($result == "HTTPSQS_PUT_END") {
			return $result;
		}
		return false;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Queue::get()
	 */
	public function get($name) {
		$result = $this->http->get ( $this->url . '?auth=' . $this->auth . '&charset=' . $this->charset . '&name=' . $name . '&opt=get' );
		if ($result == "HTTPSQS_ERROR" || $result == false) return false;
		return $result;
	}

	public function view($name, $pos) {
		$result = $this->http->get ( $this->url . '?auth=' . $this->auth . '&charset=' . $this->charset . '&name=' . $name . '&opt=view&pos=' . $pos );
		if ($result == "HTTPSQS_ERROR" || $result == false) return false;
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Queue::reset()
	 */
	public function reset($name) {
		$result = $this->http->get ( $this->url . '?auth=' . $this->auth . '&charset=' . $this->charset . '&name=' . $name . '&opt=reset' );
		if ($result == "HTTPSQS_RESET_OK") return true;
		return false;
	}

	/**
	 * 更改指定队列的最大队列数量
	 *
	 * @param string $name 队列名称
	 * @param int $num 最大队列数量
	 */
	public function maxqueue($name, $num) {
		$result = $this->http->get ( $this->url . '?auth=' . $this->auth . '&charset=' . $this->charset . '&name=' . $name . '&opt=maxqueue&num=' . $num );
		if ($result == "HTTPSQS_RESET_OK") return true;
		return false;
	}

	/**
	 * 获取队列状态
	 *
	 * @param string $name 队列名称
	 */
	public function status($name) {
		$result = $this->http->get ( $this->url . '?auth=' . $this->auth . '&charset=' . $this->charset . '&name=' . $name . '&opt=status' );
		if ($result == "HTTPSQS_ERROR" || $result == false) return false;
		return $result;
	}

	/**
	 * 获取队列状态JSON方式
	 *
	 * @param string $name 队列名称
	 */
	public function status_json($name) {
		$result = $this->http->get ( $this->url . '?auth=' . $this->auth . '&charset=' . $this->charset . '&name=' . $name . '&opt=status_json' );
		if ($result == "HTTPSQS_ERROR" || $result == false) return false;
		return json_decode ( $result );
	}

	/**
	 * 修改定时刷新内存缓冲区内容到磁盘的间隔时间
	 *
	 * @param int $num 间隔时间
	 */
	public function synctime($num) {
		$result = $this->http->get ( $this->url . '?auth=' . $this->auth . '&charset=' . $this->charset . '&name=httpsqs_synctime&opt=synctime&num=' . $num );
		if ($result == "HTTPSQS_SYNCTIME_OK") return true;
		return false;
	}
}