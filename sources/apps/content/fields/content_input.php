<?php
class content_input {
    public $modelid;
    public $fields;
    public $data;

    public function __construct($modelid) {
        $this->db = Loader::model ( 'model_field_model' );
        $this->db_pre = $this->db->get_prefix();
        $this->modelid = $modelid;
        $this->fields = S ( 'model/model_field_' . $modelid );
        $this->attachment = new Attachment ( 'content', '0' );
        $this->userid = cookie ( 'userid' ) ? cookie ( 'userid' ) : 0;
        $this->attachment->set_userid ( $this->userid );
        $this->site_config = S ( 'common/common');
    }

    public function get($data, $isimport = 0) {
        $this->data = $data;
        $info = array ();
        foreach ( $data as $field => $value ) {
            // if(!isset($this->fields[$field]) || check_in($_roleid,
            // $this->fields[$field]['unsetroleids']) || check_in($_groupid,
            // $this->fields[$field]['unsetgroupids'])) continue;
            $name = $this->fields [$field] ['name'];
            $minlength = $this->fields [$field] ['minlength'];
            $maxlength = $this->fields [$field] ['maxlength'];
            $pattern = $this->fields [$field] ['pattern'];
            $errortips = $this->fields [$field] ['errortips'];
            if (empty ( $errortips ))
                $errortips = $name . ' ' . L ( 'not_meet_the_conditions' );
            $length = strlen ( $value );

            if ($minlength && $length < $minlength) {
                if ($isimport) {
                    return false;
                } else {
                    showmessage ( $name . ' ' . L ( 'not_less_than' ) . ' ' . $minlength . L ( 'characters' ) );
                }
            }
            if ($maxlength && $length > $maxlength) {
                if ($isimport) {
                    $value = str_cut ( $value, $maxlength, '' );
                } else {
                    showmessage ( $name . ' ' . L ( 'not_more_than' ) . ' ' . $maxlength . L ( 'characters' ) );
                }
            } elseif ($maxlength) {
                $value = str_cut ( $value, $maxlength, '' );
            }
            if ($pattern && $length && ! preg_match ( $pattern, $value ) && ! $isimport)
                showmessage ( $errortips );
            $MODEL = S ( 'common/model' );
            $this->db->table_name = $this->fields [$field] ['issystem'] ? $this->db_pre . $MODEL [$this->modelid] ['tablename'] : $this->db_pre . $MODEL [$this->modelid] ['tablename'] . '_data';
            if ($this->fields [$field] ['isunique'] && $this->db->get_one ( array (
                    $field => $value
            ), $field ) && ROUTE_A != 'edit')
                showmessage ( $name . L ( 'the_value_must_not_repeat' ) );
            $func = $this->fields [$field] ['formtype'];
            if (method_exists ( $this, $func ))
                $value = $this->$func ( $field, $value );
            if ($this->fields [$field] ['issystem']) {
                $info ['system'] [$field] = $value;
            } else {
                $info ['model'] [$field] = $value;
            }
            // 颜色选择为隐藏域 在这里进行取值
            $info ['system'] ['style'] = isset($_POST ['style_color']) ? strip_tags ( $_POST ['style_color'] ) : '';
            if (isset($_POST ['style_font_weight']))
                $info ['system'] ['style'] = $info ['system'] ['style'] . ';' . strip_tags ( $_POST ['style_font_weight'] );
        }
        return $info;
    }

    function areaid($field, $value){
    	$areas = S('common/area');
    	if($value && !isset($areas[$value])) showmessage("所选地区不存在！");
    	return $value;
    }

    function box($field, $value) {
    	if($this->fields[$field]['boxtype'] == 'checkbox') {
    		if(!is_array($value) || empty($value)) return false;
    		array_shift($value);
    		$value = ','.implode(',', $value).',';
    		return $value;
    	} elseif($this->fields[$field]['boxtype'] == 'multiple') {
    		if(is_array($value) && count($value)>0) {
    			$value = ','.implode(',', $value).',';
    			return $value;
    		}
    	} else {
    		return $value;
    	}
    }

    function copyfrom($field, $value) {
    	$field_data = $field.'_data';
    	if(isset($_POST[$field_data])) {
    		$value .= '|'.$_POST[$field_data];
    	}
    	return $value;
    }

    function datetime($field, $value) {
    	$setting = string2array($this->fields[$field]['setting']);
    	if($setting['fieldtype']=='int') {
    		$value = strtotime($value);
    	}
    	return $value;
    }

    function downfile($field, $value) {
    	//取得镜像站点列表
    	$result = '';
    	$server_list = count($_POST[$field.'_servers']) > 0 ? implode(',' ,$_POST[$field.'_servers']) : '';
    	$result = $value.'|'.$server_list;
    	return $result;
    }

    function downfiles($field, $value) {
    	$files = $_POST[$field.'_fileurl'];
    	$files_alt = $_POST[$field.'_filename'];
    	$array = $temp = array();
    	if(!empty($files)) {
    		foreach($files as $key=>$file) {
    			$temp['fileurl'] = $file;
    			$temp['filename'] = $files_alt[$key];
    			$array[$key] = $temp;
    		}
    	}
    	$array = array2string($array);
    	return $array;
    }

    function editor($field, $value) {
    	$setting = string2array($this->fields[$field]['setting']);
    	$enablesaveimage = $setting['enablesaveimage'];
    	if(isset($_POST['spider_img'])) $enablesaveimage = 0;
    	if($enablesaveimage) {
    		$watermark_enable = C('attachment','watermark_enable');
    		$value = $this->attachment->download('content', $value,$watermark_enable);
    	}
    	return $value;
    }
}?>