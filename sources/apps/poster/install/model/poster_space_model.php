<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class poster_space_model extends model {
	function __construct() {
		$this->setting = 'default';
		$this->table_name = 'poster_space';
		parent::__construct ();
	}
}