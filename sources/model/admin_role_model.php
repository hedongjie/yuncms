<?php
/**
 * 管理员角色模型
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class admin_role_model extends Model {

	public $table_name = '';

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'admin_role';
		parent::__construct ();
	}
}