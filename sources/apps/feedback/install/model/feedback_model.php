<?php
/**
 * 留言反馈数据模型
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-3
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: feedback_model.php 200 2013-03-29 23:15:00Z 85825770@qq.com $
 */
defined('IN_YUNCMS') or exit('No permission resources.');
class feedback_model extends Model {

	public $table_name;

	public function __construct() {
		$this->options = 'default';
		$this->table_name = 'feedback';
		parent::__construct();
	}
}