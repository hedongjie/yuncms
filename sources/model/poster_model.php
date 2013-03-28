<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class poster_model extends Model {

    public function __construct() {
        $this->setting = 'default';
        $this->table_name = 'poster';
        parent::__construct ();
    }
}