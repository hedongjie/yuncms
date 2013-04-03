<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 评论数据表
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: comment_data_model.php 875 2012-06-13 02:19:05Z 85825770@qq.com
 *          $
 */
class comment_data_model extends Model {

	public $table_name;

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = '';
		parent::__construct ();
	}

	/**
	 * 设置评论数据表
	 *
	 * @param integer $id
	 *        	数据表ID
	 */
	public function table_name($id) {
		$this->table_name = $this->prefix . 'comment_data_' . $id;
		return $this->table_name;
	}
}
