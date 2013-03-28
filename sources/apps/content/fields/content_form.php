<?php
class content_form {

	public $modelid;
	public $fields;
	public $id;
	public $formValidator;

	public function __construct($modelid, $catid = 0, $categorys = array()) {
		$this->modelid = $modelid;
		$this->catid = $catid;
		$this->categorys = $categorys;
		$this->fields = S ( 'model/model_field_' . $modelid );
	}

	public function get($data = array()) {
		$_groupid = cookie ( '_groupid' );
		$this->data = $data;
		if (isset ( $data ['id'] )) $this->id = $data ['id'];
		$info = array ();
		$this->content_url = isset ( $data ['url'] ) ? $data ['url'] : '';
		foreach ( $this->fields as $field => $v ) {
			if (defined ( 'IN_ADMIN' )) {
				if ($v ['iscore'] || check_in ( $_SESSION ['roleid'], $v ['unsetroleids'] )) continue;
			} else {
				if ($v ['iscore'] || ! $v ['isadd'] || check_in ( $_groupid, $v ['unsetgroupids'] )) continue;
			}
			$func = $v ['formtype'];
			$value = isset ( $data [$field] ) ? htmlspecialchars ( $data [$field], ENT_QUOTES ) : '';
			if ($func == 'pages' && isset ( $data ['maxcharperpage'] )) {
				$value = $data ['paginationtype'] . '|' . $data ['maxcharperpage'];
			}
			if (! method_exists ( $this, $func )) continue;
			$form = $this->$func ( $field, $value, $v );
			if ($form !== false) {
				if (defined ( 'IN_ADMIN' )) {
					if ($v ['isbase']) {
						$star = $v ['minlength'] || $v ['pattern'] ? 1 : 0;
						$info ['base'] [$field] = array ('name' => $v ['name'],'tips' => $v ['tips'],'form' => $form,'star' => $star,'isomnipotent' => $v ['isomnipotent'],'formtype' => $v ['formtype'] );
					} else {
						$star = $v ['minlength'] || $v ['pattern'] ? 1 : 0;
						$info ['senior'] [$field] = array ('name' => $v ['name'],'tips' => $v ['tips'],'form' => $form,'star' => $star,'isomnipotent' => $v ['isomnipotent'],'formtype' => $v ['formtype'] );
					}
				} else {
					$star = $v ['minlength'] || $v ['pattern'] ? 1 : 0;
					$info [$field] = array ('name' => $v ['name'],'tips' => $v ['tips'],'form' => $form,'star' => $star,'isomnipotent' => $v ['isomnipotent'],'formtype' => $v ['formtype'] );
				}
			}
		}
		return $info;
	}

	public function readpoint($field, $value = '0' , $fieldinfo) {
		if(empty($value)) $value = 0;
		$paytype = $this->data['paytype'];
		if($paytype) {
			$checked1 = '';
			$checked2 = 'checked';
		} else {
			$checked1 = 'checked';
			$checked2 = '';
		}
		return '<input type="text" name="info['.$field.']" value="'.$value.'" size="5"><input type="radio" name="info[paytype]" value="0" '.$checked1.'> '.L('point').' <input type="radio" name="info[paytype]" value="1" '.$checked2.'>'.L('money');
	}

	public function template($field, $value, $fieldinfo) {
		$default_style = C('template','name');
		return Form::select_template($default_style,'content',$value,'name="info['.$field.']" id="'.$field.'"','show');
	}

	public function text($field, $value, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);
        $size = $setting['size'];
        if(!$value) $value = $defaultvalue;
        $type = $ispassword ? 'password' : 'text';
        $errortips = $this->fields[$field]['errortips'];
        if($errortips || $minlength) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"",onfocus:"'.$errortips.'"}).inputValidator({min:1,onerror:"'.$errortips.'"});';
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
        $value = empty($value) ? $setting[defaultvalue] : $value;
        $str = "<textarea name='info[{$field}]' id='$field' style='width:{$width}%;height:{$height}px;' $formattribute $css";
        if($maxlength) $str .= " onkeyup=\"strlen_verify(this, '{$field}_len', {$maxlength})\"";
        $str .= ">{$value}</textarea>";
        if($maxlength) $str .= L('can_enter').'<B><span id="'.$field.'_len">'.$maxlength.'</span></B> '.L('characters');
        return $str;
    }

	public function title($field, $value, $fieldinfo) {
		extract($fieldinfo);
		$style_arr = explode(';',$this->data['style']);
		$style_color = $style_arr[0];
		$style_font_weight = $style_arr[1] ? $style_arr[1] : '';
		$style = 'color:'.$this->data['style'];
		if(!$value) $value = $defaultvalue;
		$errortips = $this->fields[$field]['errortips'];
		$errortips_max = L('title_is_empty');
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"",onfocus:"'.$errortips.'"}).inputValidator({min:'.$minlength.',max:'.$maxlength.',onerror:"'.$errortips_max.'"});';
		$str = '<input type="text" style="width:400px;'.($style_color ? 'color:'.$style_color.';' : '').($style_font_weight ? 'font-weight:'.$style_font_weight.';' : '').'" name="info['.$field.']" id="'.$field.'" value="'.$value.'" style="'.$style.'" class="measure-input " onBlur="$.post(\'api.php?controller=keyword&action=get&number=3&sid=\'+Math.random()*5, {data:$(\'#title\').val()}, function(data){if(data && $(\'#keywords\').val()==\'\') $(\'#keywords\').val(data); })" onkeyup="strlen_verify(this, \'title_len\', '.$maxlength.');"/><input type="hidden" name="style_color" id="style_color" value="'.$style_color.'">
		<input type="hidden" name="style_font_weight" id="style_font_weight" value="'.$style_font_weight.'">';
		if(defined('IN_ADMIN')) $str .= '<input type="button" class="button" id="check_title_alt" value="'.L('check_title','','content').'" onclick="$.get(\'?app=content&controller=content&action=public_check_title&catid='.$this->catid.'&sid=\'+Math.random()*5, {data:$(\'#title\').val()}, function(data){if(data==\'1\') {$(\'#check_title_alt\').val(\''.L('title_repeat').'\');$(\'#check_title_alt\').css(\'background-color\',\'#FFCC66\');} else if(data==\'0\') {$(\'#check_title_alt\').val(\''.L('title_not_repeat').'\');$(\'#check_title_alt\').css(\'background-color\',\'#F8FFE1\')}})" style="width:73px;"/><img src="'.IMG_PATH.'icon/colour.png" width="15" height="16" onclick="colorpicker(\''.$field.'_colorpanel\',\'set_title_color\');" style="cursor:hand"/>
		<img src="'.IMG_PATH.'icon/bold.png" width="10" height="10" onclick="input_font_bold()" style="cursor:hand"/> <span id="'.$field.'_colorpanel" style="position:absolute;" class="colorpanel"></span>';
		$str .= L('can_enter').'<B><span id="title_len">'.$maxlength.'</span></B> '.L('characters');
		return $str;
	}

	public function typeid($field, $value, $fieldinfo) {
		extract ( $fieldinfo );
		$setting = string2array ( $setting );
		if (! $value) $value = $setting ['defaultvalue'];
		if ($errortips) {
			$errortips = $this->fields [$field] ['errortips'];
			$this->formValidator .= '$("#' . $field . '").formValidator({onshow:"",onfocus:"' . $errortips . '"}).inputValidator({min:1,onerror:"' . $errortips . '"});';
		}
		$usable_type = $this->categorys [$this->catid] ['usable_type'];
		$usable_array = array ();
		if ($usable_type) $usable_array = explode ( ',', $usable_type );

		$type_data = S ( 'common/type_content' );
		foreach ( $type_data as $_key => $_value ) {
			if (in_array ( $_key, $usable_array )) $data [$_key] = $_value ['name'];
		}
		return Form::select ( $data, $value, 'name="info[' . $field . ']" id="' . $field . '" ' . $formattribute . ' ' . $css, L ( 'copyfrom_tips' ) );
	}

	public function video($field, $value, $fieldinfo) {
		extract ( $fieldinfo );
		$textheight = $textheight ? $textheight : 100;
		$list_str = '';
		if ($value && ! empty ( $value )) {
			$value = string2array ( html_entity_decode ( $value, ENT_QUOTES ) );
			if (is_array ( $value )) {
				foreach ( $value as $_k => $_v ) {
					$list_str .= "<li id='image{$_k}' style='padding:1px'><input type='text' name='{$field}_url[]' value='{$_v[url]}' style='width:310px;' ondblclick='image_priview(this.value);' class='input-text'> <input type='text' name='{$field}_alt[]' value='{$_v[alt]}' style='width:160px;' class='input-text'> <a href=\"javascript:remove_div('image{$_k}')\">" . L ( 'remove_out', '', 'content' ) . "</a></li>";
				}
			}
		} else {
			$list_str .= "<center><div class='onShow' id='nameTip'>" . L ( 'upload_pic_max', '', 'content' ) . " <font color='red'>{$upload_number}</font> " . L ( 'tips_pics', '', 'content' ) . "</div></center>";
		}
		$list_str .= "<textarea style='width:98%;height:" . $textheight . "px;' name='" . $field . "'></textarea>";
		$string = '<input name="info[' . $field . ']" type="hidden" value="1">
		<fieldset class="blue pad-10">
		<legend>' . $field . '列表</legend>';
		$string .= $list_str;
		$string .= '<ul id="' . $field . '" class="picList"></ul>
		</fieldset>
		<div class="bk10"></div>
		';
		if (! defined ( 'IMAGES_INIT' )) {
			$str = '<script type="text/javascript" src="statics/js/swfupload/swf2ckeditor.js"></script>';
			define ( 'IMAGES_INIT', 1 );
		}
		$authkey = upload_key ( "$upload_number,$upload_allowext,$isselectimage" );
		$string .= $str . "<div class='picBut cu'><a herf='javascript:void(0);' onclick=\"javascript:flashupload('{$field}_images', '" . L ( 'attachment_upload' ) . "','{$field}',change_images,'{$upload_number},{$upload_allowext},{$isselectimage}','content','$this->catid','{$authkey}')\"/> 选择" . $field . " </a></div>";
		// add player
		$playerlists = array ('0' => '请选择默认播放器' );
		$playerlist = S ( 'common/player' );
		foreach ( ( array ) $playerlist as $k => $v ) {
			$playerlists [$v ['playerid']] = $v ['subject'];
		}
		$string .= Form::select ( $playerlists, $_v ['p'] ? $_v ['p'] : $defaultplayer, 'name="' . $field . '_defaultplayer"' );
		return $string;
	}
}?>