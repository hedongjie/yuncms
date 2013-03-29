<?php
class member_form {
	public $modelid;
	public $fields;
	public $id;
	public $formValidator;

	public function __construct($modelid) {
		$this->modelid = $modelid;
		$this->fields = S ( 'member/model_field_' . $modelid );
	}

	public function get($data = array()) {
		$_roleid = cookie ( '_roleid' );
		$_groupid = cookie ( '_groupid' );
		$this->data = $data;
		if (isset ( $data ['id'] )) $this->id = $data ['id'];
		$info = array ();
		if (is_array ( $this->fields )) {
			foreach ( $this->fields as $field => $v ) {
				if (defined ( 'IN_ADMIN' )) {
					if ($v ['disabled'] || $v ['iscore'] || check_in ( $_roleid, $v ['unsetroleids'] ) || check_in ( $_groupid, $v ['unsetgroupids'] )) continue;
				} else {
					if ($v ['disabled'] || $v ['iscore'] || ! $v ['isadd'] || check_in ( $_roleid, $v ['unsetroleids'] ) || check_in ( $_groupid, $v ['unsetgroupids'] )) continue;
				}
				$func = $v ['formtype'];
				$value = isset ( $data [$field] ) ? htmlspecialchars ( $data [$field], ENT_QUOTES ) : '';
				if ($func == 'pages' && isset ( $data ['maxcharperpage'] )) {
					$value = $data ['paginationtype'] . '|' . $data ['maxcharperpage'];
				}
				if (! method_exists ( $this, $func )) continue;
				$form = $this->$func ( $field, $value, $v );
				if ($form !== false) {
					$star = $v ['minlength'] || $v ['pattern'] ? 1 : 0;
					$info [$field] = array ('name' => $v ['name'],'tips' => $v ['tips'],'form' => $form,'star' => $star,'isbase' => $v ['isbase'],'isomnipotent' => $v ['isomnipotent'],'formtype' => $v ['formtype'] );
				}
			}
		}
		return $info;
	}

	public function box($field, $value, $fieldinfo) {
		$setting = string2array ( $fieldinfo ['setting'] );
		if ($value == '') $value = $this->fields [$field] ['defaultvalue'];
		$options = explode ( "\n", $this->fields [$field] ['options'] );
		foreach ( $options as $_k ) {
			$v = explode ( "|", $_k );
			$k = trim ( $v [1] );
			$option [$k] = $v [0];
		}
		$values = explode ( ',', $value );
		$value = array ();
		foreach ( $values as $_k ) {
			if ($_k != '') $value [] = $_k;
		}
		$value = implode ( ',', $value );

		switch ($this->fields [$field] ['boxtype']) {
			case 'radio' :
				$string = Form::radio ( $option, $value, "name='info[$field]'", $setting ['width'], $field );
				break;

			case 'checkbox' :
				$string = Form::checkbox ( $option, $value, "name='info[$field][]'", 1, $setting ['width'], $field );
				break;

			case 'select' :
				$string = Form::select ( $option, $value, "name='info[$field]' id='$field'" );
				break;

			case 'multiple' :
				$string = Form::select ( $option, $value, "name='info[$field][]' id='$field' size=2 multiple='multiple' style='height:60px;'" );
				break;
		}
		return $string;
	}

	public function datetime($field, $value, $fieldinfo) {
		extract ( string2array ( $fieldinfo ['setting'] ) );
		$isdatetime = 0;
		if ($fieldtype == 'int') {
			if (! $value) $value = SYS_TIME;
			$format_txt = $format == 'm-d' ? 'm-d' : $format;
			$value = date ( $format_txt, $value );
			$isdatetime = strlen ( $format ) > 6 ? 1 : 0;
		} elseif ($fieldtype == 'datetime') {
			$isdatetime = 1;
		}
		return Form::date ( "info[$field]", $value, $isdatetime, 1 );
	}

	public function editor($field, $value, $fieldinfo) {
		// 是否允许用户上传附件 ，后台管理员开启此功能
		extract ( $fieldinfo );
		extract ( string2array ( $setting ) );
		$allowupload = defined ( 'IN_ADMIN' ) ? 1 : 0;
		if (! $value) $value = $defaultvalue;
		if ($minlength || $pattern) $allow_empty = '';
		if ($errortips) $this->formValidator .= '$("#' . $field . '").formValidator({' . $allow_empty . 'onshow:"",onfocus:"' . $errortips . '"}).inputValidator({min:1,onerror:"' . $errortips . '"});';
		return "<div id='{$field}_tip'></div>" . '<textarea name="info[' . $field . ']" id="' . $field . '" boxid="' . $field . '">' . $value . '</textarea>' . Form::editor ( $field, $toolbar, 'member', '', '', $allowupload, 1, '', 300 );
	}

	public function image($field, $value, $fieldinfo) {
		$setting = string2array ( $fieldinfo ['setting'] );
		extract ( $setting );
		if (! defined ( 'IMAGES_INIT' )) {
			$str = '<script type="text/javascript" src="statics/js/swfupload/swf2ckeditor.js"></script>';
			define ( 'IMAGES_INIT', 1 );
		}
		$authkey = upload_key ( "1,$upload_allowext,$isselectimage,$images_width,$images_height" );
		if ($show_type) {
			$preview_img = $value ? $value : IMG_PATH . 'icon/upload-pic.png';
			return $str . "<div class='upload-pic img-wrap'><input type='hidden' name='info[$field]' id='$field' value='$value'>
			<a href='javascript:;' onclick=\"javascript:flashupload('{$field}_images', '" . L ( 'attachment_upload' ) . "','{$field}',thumb_images,'1,$upload_allowext,$isselectimage,$images_width,$images_height','member','','{$authkey}')\">
			<img src='$preview_img' id='{$field}_preview' width='135' height='113' style='cursor:hand' /></a></div>";
		} else {
			return $str . "<input type='text' name='info[$field]' id='$field' value='$value' size='$size' class='input-text' />  <input type='button' class='button' onclick=\"javascript:flashupload('{$field}_images', '" . L ( 'attachment_upload' ) . "','{$field}',submit_images,'1,{$upload_allowext},$isselectimage,$images_width,$images_height','member','','{$authkey}')\"/ value='" . L ( 'image_upload' ) . "'>";
		}
	}

	public function images($field, $value, $fieldinfo) {
		extract ( $fieldinfo );
		$list_str = '';
		if ($value) {
			$value = string2array ( html_entity_decode ( $value, ENT_QUOTES ) );
			if (is_array ( $value )) {
				foreach ( $value as $_k => $_v ) {
					$list_str .= "<li id='image{$_k}' style='padding:1px'><input type='text' name='{$field}_url[]' value='{$_v[url]}' style='width:310px;' ondblclick='image_priview(this.value);' class='input-text'> <input type='text' name='{$field}_alt[]' value='{$_v[alt]}' style='width:160px;' class='input-text'> <a href=\"javascript:remove_div('image{$_k}')\">" . L ( 'remove' ) . "</a></li>";
				}
			}
		} else {
			// $list_str .= "<input type='hidden' name='{$field}_url[]'
			// value='0'>";
			$list_str .= "<center><div class='onShow' id='nameTip'>" . L ( 'max_upload_num' ) . " <font color='red'>{$upload_number}</font> " . L ( 'zhang' ) . "</div></center>";
		}
		$string = '<input name="info[' . $field . ']" type="hidden" value="1">
		<fieldset class="blue pad-10">
		<legend>' . L ( 'picutre_list' ) . '</legend>';
		$string .= $list_str;
		$string .= '<ul id="' . $field . '" class="picList"></ul>
		</fieldset>
		<div class="bk10"></div>
		';
		if (! defined ( 'IMAGES_INIT' )) {
			$str = '<script type="text/javascript" src="statics/js/swfupload/swf2ckeditor.js"></script>';
			define ( 'IMAGES_INIT', 1 );
		} else {
			$str = '';
		}
		$authkey = upload_key ( "$upload_number,$upload_allowext,$isselectimage" );
		$string .= $str . "<div class='picBut cu'><a herf='javascript:void(0);' onclick=\"javascript:flashupload('{$field}_images', '" . L ( 'attachment_upload' ) . "','{$field}',change_images,'{$upload_number},{$upload_allowext},{$isselectimage}','member','','{$authkey}')\"/> " . L ( 'select_picture' ) . " </a></div>";
		return $string;
	}

	public function linkage($field, $value, $fieldinfo) {
		$setting = string2array($fieldinfo['setting']);
		$linkageid = $setting['linkageid'];
		return menu_linkage($linkageid,$field,$value);
	}

	public function number($field, $value, $fieldinfo) {
		extract($fieldinfo);
		if(!$value) $value = $defaultvalue;
		$errortips = $this->fields[$field]['errortips'];
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"'.$errortips.'",onfocus:"'.$errortips.'"}).inputValidator({min:'.$minnumber.',max:'.$maxnumber.',onerror:"'.$errortips.'"}).regexValidator({regexp:"num",datatype:"enum",onerror:"'.$errortips.'"});';
		return "<input type='text' name='info[$field]' id='$field' value='$value' size='$size' class='input-text' {$formattribute} {$css}>";
	}

	public function omnipotent($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$formtext = str_replace('{FIELD_VALUE}',$value,$formtext);
		$formtext = str_replace('{MODELID}',$this->modelid,$formtext);
		$id  = $this->id ? $this->id : 0;
		$formtext = str_replace('{ID}',$id,$formtext);
		$errortips = $this->fields[$field]['errortips'];
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"'.$errortips.'",onfocus:"'.$errortips.'"}).inputValidator({min:1,onerror:"'.$errortips.'"});';
		return $formtext;
	}

	public function text($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$setting = string2array($setting);
		$size = $setting['size'];
		if(!$value) $value = $defaultvalue;
		$type = $ispassword ? 'password' : 'text';
		$errortips = $this->fields[$field]['errortips'];
		$regexp = $pattern ? '.regexValidator({regexp:"'.substr($pattern,1,-1).'",onerror:"'.$errortips.'"})' : '';
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"'.$errortips.'",onfocus:"'.$errortips.'"}).inputValidator({min:'.$minlength.',max:'.$maxlength.',onerror:"'.$errortips.'"})'.$regexp.';';
		return '<input type="text" name="info['.$field.']" id="'.$field.'" size="'.$size.'" value="'.$value.'" class="input-text" '.$formattribute.' '.$css.'>';
	}

	public function textarea($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$setting = string2array($setting);
		extract($setting);
		if(!$value) $value = $defaultvalue;
		$allow_empty = 'empty:true,';
		if($minlength || $pattern) $allow_empty = '';
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({'.$allow_empty.'onshow:"'.$errortips.'",onfocus:"'.$errortips.'"}).inputValidator({min:1,onerror:"'.$errortips.'"});';
		$value = empty($value) ? $setting['defaultvalue'] : $value;
		return "<textarea name='info[{$field}]' id='$field' style='width:{$width}%;height:{$height}px;' $formattribute $css>{$value}</textarea>";
	}
}?>