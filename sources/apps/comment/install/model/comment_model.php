<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: comment_model.php 200 2013-03-29 23:15:00Z 85825770@qq.com $
 */
class comment_model extends Model {

	public $table_name;
	public $old_table_name;

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = $this->old_table_name = 'comment';
		parent::__construct ();
	}
}