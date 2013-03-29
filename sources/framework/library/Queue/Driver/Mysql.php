<?php
/**
 * Mysql模拟队列驱动
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-14
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Mysql.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Queue_Driver_Mysql extends Queue_Abstract {

	public $db;

	public function __construct($options) {
		$this->db = Loader::model($options['option']);
	}

	/**
	 * (non-PHPdoc)
	 * @see Queue::put()
	 */
	public function put($name, $data) {
		if(empty($name)) return false;
		$this->db->insert (array('name'=>$name,'content'=>$data));
		return $this->db->insert_id ();
	}

	/**
	 * (non-PHPdoc)
	 * @see Queue::get()
	 */
	public function get($name) {
		if(empty($name)) return false;
		$res = $this->db->get_one(array('name'=>$name),'*','queueid ASC');
		if($res){
			$this->db->delete(array('queueid'=>$res['queueid']));
			return $res['content'];
		}
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Queue::status()
	 */
	public function status($name) {
		return;
	}

	/**
	 * (non-PHPdoc)
	 * @see Queue::reset()
	 */
	public function reset($name) {
		return $this->db->delete ( array('name'=>$name) );
	}

	public function __destruct() {
	}
}