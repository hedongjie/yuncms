<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class poster_model extends model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'poster';
		parent::__construct ();
	}
}