<?php
defined('IN_YUNCMS') or exit('No permission resources.');
class license_client_model extends Model {
	public function __construct() {
		$this->options = 'default';
		$this->table_name = 'license_client';
		parent::__construct();
	}
}
?>