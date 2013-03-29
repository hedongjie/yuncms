<?php
/**
 * 后台操作日志表
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class admin_log_model extends Model {

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'admin_log';
		parent::__construct ();
	}
}