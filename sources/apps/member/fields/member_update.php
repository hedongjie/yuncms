<?php
class member_update {
    public $modelid;
    public $fields;
    public $data;

    public function __construct($modelid) {
        $this->db = Loader::model ( 'model_field_model' );
        $this->db_pre = $this->db->get_prefix ();
        $this->modelid = $modelid;
        $this->fields = S ( 'member/model_field_' . $modelid );
    }
}?>