<?php
/**
 * 获取操作模型
 * @author Tongle Xu <xutongle@gmail.com> 2013-3-28
 * @copyright Copyright (c) 2003-2103 tintsoft.com
 * @license http://www.tintsoft.com
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class get_model extends Model {

	public $table_name = '';

	public function __construct($table = null) {
		$this->setting = 'default';
		if (! is_null ( $table )) $this->table_name = $this->prefix . $table;
		parent::__construct ();
	}

	public function sql_query($sql) {
		if (! empty ( $this->prefix )) $sql = str_replace ( '#dbprefix#', $this->prefix, $sql );
		return parent::query ( $sql );
	}
}