<?php
class feedback_output {

	public $fields;
	public $data;

	public function __construct($formid) {
		$this->formid = $formid;
		$this->fields = S ( 'model/formguide_field_' . $formid );
	}

	public function get($data) {
		$this->data = $data;
		$this->id = $data ['dataid'];
		$info = array ();
		foreach ( $this->fields as $field => $v ) {
			if (! isset ( $data [$field] )) continue;
			$func = $v ['formtype'];
			$value = $data [$field];
			$result = method_exists ( $this, $func ) ? $this->$func ( $field, $data [$field] ) : $data [$field];
			if ($result !== false) $info [$field] = $result;
		}
		return $info;
	}

	public function box($field, $value) {
		extract ( string2array ( $this->fields [$field] ['setting'] ) );
		if ($outputtype) {
			return $value;
		} else {
			$options = explode ( "\n", $this->fields [$field] ['options'] );
			foreach ( $options as $_k ) {
				$v = explode ( "|", $_k );
				$k = trim ( $v [1] );
				$option [$k] = $v [0];
			}
			$string = '';
			switch ($this->fields [$field] ['boxtype']) {
				case 'radio' :
					$string = $option [$value];
					break;

				case 'checkbox' :
					$value_arr = explode ( ',', $value );
					foreach ( $value_arr as $_v ) {
						if ($_v) $string .= $option [$_v] . ' 、';
					}
					break;

				case 'select' :
					$string = $option [$value];
					break;

				case 'multiple' :
					$value_arr = explode ( ',', $value );
					foreach ( $value_arr as $_v ) {
						if ($_v) $string .= $option [$_v] . ' 、';
					}
					break;
			}
			return $string;
		}
	}

	public function datetime($field, $value) {
		$setting = string2array ( $this->fields [$field] ['setting'] );
		extract ( $setting );
		if ($fieldtype == 'date' || $fieldtype == 'datetime') {
			return $value;
		} else {
			$format_txt = $format;
		}
		if (strlen ( $format_txt ) < 6) {
			$isdatetime = 0;
		} else {
			$isdatetime = 1;
		}
		if (! $value) $value = TIME;
		$value = date ( $format_txt, $value );
		return $value;
	}

	public function editor($field, $value) {
		return $value;
	}

	public function images($field, $value) {
		return string2array ( $value );
	}

	public function linkage($field, $value) {
		$setting = string2array ( $this->fields [$field] ['setting'] );
		$datas = S ( 'linkage/' . $setting ['linkageid'] );
		$infos = $datas ['data'];
		if ($setting ['showtype'] == 1) {
			$result = get_linkage ( $value, $setting ['linkageid'], $setting ['space'], 1 );
		} elseif ($setting ['showtype'] == 2) {
			$result = $value;
		} else {
			$result = get_linkage ( $value, $setting ['linkageid'], $setting ['space'], 2 );
		}
		return $result;
	}
}?>