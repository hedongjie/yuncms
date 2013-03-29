<?php
/**
 * 队列抽象类
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-14
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Abstract.php 2 2013-01-14 07:14:05Z xutongle $
 */
abstract class Queue_Abstract{

	public function __construct($options = array()) {

	}

	/**
	 * 插入队列
	 *
	 * @param string $name
	 *        	名称
	 * @param string $data
	 *        	数据
	 */
	abstract public function put($name, $data);

	/**
	 * 获取队列
	 *
	 * @param string $name
	 *        	名称
	 */
	abstract public function get($name);

	/**
	 * 查看队列状态
	 *
	 * @param string $name
	 *        	名称
	 */
	abstract public function status($name);

	/**
	 * 重置指定队列
	 *
	 * @param string $name
	 *        	名称
	 */
	abstract public function reset($name);
}