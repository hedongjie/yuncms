<?php
class feedback_update {

	public $modelid;
	public $fields;
	public $data;

	public function __construct($modelid, $id) {
		$this->modelid = $modelid;
		$this->fields = S ( 'model/model_field_' . $modelid );
		$this->id = $id;
	}

	public function update($data) {
		$info = array ();
		$this->data = $data;
		foreach ( $data as $field => $value ) {
			if (! isset ( $this->fields [$field] )) continue;
			$func = $this->fields [$field] ['formtype'];
			$info [$field] = method_exists ( $this, $func ) ? $this->$func ( $field, $value ) : $value;
		}
	}
}
?>