<?php
defined('IN_YUNCMS') or exit('No permission resources.');
class digg_log_model extends Model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'digg_log';
		parent::__construct();
	}
}