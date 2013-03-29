<?php
/**
 * 友情连接模型
 * @author		YUNCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, NewsTeng, Inc.
 * @license	http://www.yuncms.net/about/license
 * @link		http://www.yuncms.net
 * $Id: link_model.php 462 2012-11-22 00:52:07Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class link_model extends Model {
	public $table_name = '';
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'link';
		parent::__construct ();
	}
	public function hits($linkid) {
		return $this->where ( array ('linkid' => $linkid ) )->update ( "`hits`=hits+1" );
	}
}