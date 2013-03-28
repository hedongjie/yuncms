<?php
class member_input {
	public $modelid;
	public $fields;
	public $data;

    public function __construct($modelid) {
		$this->db = Loader::model('model_field_model');
		$this->db_pre = $this->db->get_prefix ();
		$this->modelid = $modelid;
		$this->fields = S('member/model_field_'.$modelid);
		$this->attachment = new Attachment('content','0');

    }

	public function get($data) {
		$this->data = $data;
		$model_cache = S('common/member_model');
		$this->db->table_name = $this->db_pre.$model_cache[$this->modelid]['tablename'];
		$info = array();
		$debar_filed = array('catid','title','style','thumb','status','islink','description');
		foreach($data as $field=>$value) {
			if((isset($data['islink']) && $data['islink']==1) && !in_array($field,$debar_filed)) continue;
			$name = $this->fields[$field]['name'];
			$minlength = $this->fields[$field]['minlength'];
			$maxlength = $this->fields[$field]['maxlength'];
			$pattern = $this->fields[$field]['pattern'];
			$errortips = $this->fields[$field]['errortips'];
			if(empty($errortips)) $errortips = "$name 不符合要求！";
			$length = strlen($value);
			if($minlength && $length < $minlength && !$isimport) showmessage("$name 不得少于 $minlength 个字符！");
			if($maxlength && $length > $maxlength && !$isimport) {
				showmessage("$name 不得超过 $maxlength 个字符！");
			} else {
				str_cut($value, $maxlength);
			}
			if($pattern && $length && !preg_match($pattern, $value) && !$isimport) showmessage($errortips);
            if($this->fields[$field]['isunique'] && $this->db->get_one(array($field=>$value),$field) && ROUTE_A != 'edit') showmessage("$name 的值不得重复！");
			$func = $this->fields[$field]['formtype'];
			if(method_exists($this, $func)) $value = $this->$func($field, $value);

			$info[$field] = $value;
		}
		return $info;
	}

	public function box($field, $value) {
		if($this->fields[$field]['boxtype'] == 'checkbox') {
			if(!is_array($value) || empty($value)) return false;
			array_shift($value);
			$value = ','.implode(',', $value).',';
			return $value;
		} elseif($this->fields[$field]['boxtype'] == 'multiple') {
			if(is_array($value) && count($value)>1) {
				$value = ','.implode(',', $value).',';
				return $value;
			}
		} else {
			return $value;
		}
	}

	public function datetime($field, $value) {
		$setting = string2array($this->fields[$field]['setting']);
		if($setting['fieldtype']=='int') {
			$value = strtotime($value);
		}
		return $value;
	}

	public function editor($field, $value) {
		$setting = string2array($this->fields[$field]['setting']);
		$enablesaveimage = $setting['enablesaveimage'];
		$site_setting = string2array($this->site_config['setting']);
		$watermark_enable = intval($site_setting['watermark_enable']);
		$value = $this->attachment->download('content', $value,$watermark_enable);
		return $value;
	}

	public function images($field, $value) {
		//取得图片列表
		$pictures = $_POST[$field.'_url'];
		//取得图片说明
		$pictures_alt = $_POST[$field.'_alt'];
		$array = $temp = array();
		if(!empty($pictures)) {
			foreach($pictures as $key=>$pic) {
				$temp['url'] = $pic;
				$temp['alt'] = $pictures_alt[$key];
				$array[$key] = $temp;
			}
		}
		$array = array2string($array);
		return $array;
	}

	public function textarea($field, $value) {
		if(!$this->fields[$field]['enablehtml']) $value = strip_tags($value);
		return $value;
	}
}?>