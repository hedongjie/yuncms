<?php
defined('IN_YUNCMS') or exit('No permission resources.');
class digg_model extends Model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'digg';
		parent::__construct();
	}
}