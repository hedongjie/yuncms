<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 内容审核表
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-12
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: content_check_model.php 874 2012-06-12 09:38:26Z
 *          85825770@qq.com $
 */
class content_check_model extends Model {

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'content_check';
		parent::__construct ();
	}
}