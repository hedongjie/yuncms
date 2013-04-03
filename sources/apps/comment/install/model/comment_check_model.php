<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 评论审核表
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: comment_check_model.php 875 2012-06-13 02:19:05Z
 *          85825770@qq.com $
 */
class comment_check_model extends Model {

	public $table_name;
	public $old_table_name;

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = $this->old_table_name = 'comment_check';
		parent::__construct ();
	}
}