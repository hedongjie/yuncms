<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class Form {

	/**
	 * 编辑器
	 *
	 * @param int $textareaid
	 * @param int $toolbar
	 * @param string $application 应用名称
	 * @param int $catid 栏目id
	 * @param int $color 编辑器颜色
	 * @param boole $allowupload 是否允许上传
	 * @param boole $allowbrowser 是否允许浏览文件
	 * @param string $alowuploadexts 允许上传类型
	 * @param string $height 编辑器高度
	 * @param string $disabled_page 是否禁用分页和子标题
	 */
	public static function editor($textareaid = 'content', $toolbar = 'basic', $application = '', $catid = '', $color = '', $allowupload = 0, $allowbrowser = 1, $alowuploadexts = '', $height = 200, $disabled_page = 0, $allowuploadnum = '10') {
		$str = '';
		if (! defined ( 'EDITOR_INIT' )) {
			$str .= "<script type=\"text/javascript\">\r\nwindow.UEDITOR_HOME_URL='" . JS_PATH . "ueditor/'\r\n</script>\r\n";
			$str .= '<script type="text/javascript" src="' . JS_PATH . 'ueditor/editor_config.js"></script>
			<script type="text/javascript" src="' . JS_PATH . 'ueditor/editor_all.js"></script>
			<script type="text/javascript" src="' . JS_PATH . 'ueditor/editor_util.js"></script>
			<link rel="stylesheet" href="' . JS_PATH . 'ueditor/themes/default/ueditor.css"/>';
			define ( 'EDITOR_INIT', 1 );
		}
		if ($toolbar == 'basic') {
			$toolbar = defined ( 'IN_ADMIN' ) ? "['Source'," : '[';
			$toolbar .= "'Bold', 'Italic', '|', 'InsertOrderedList', 'InsertUnorderedList', '|', 'Link', 'Unlink' ]";
		} elseif ($toolbar == 'full') {
			if (defined ( 'IN_ADMIN' )) {
				$toolbar = "['Source',";
			} else {
				$toolbar = '[';
			}
			$toolbar .= "'fullscreen',  '|', 'undo', 'redo', '|',
			'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch','autotypeset', '|',
			'blockquote', '|', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist','selectall', 'cleardoc', '|', 'customstyle',
			'paragraph', '|','rowspacingtop', 'rowspacingbottom','lineheight', '|','fontfamily', 'fontsize', '|',
			'directionalityltr', 'directionalityrtl', '|', '', 'indent', '|',
			'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|','touppercase','tolowercase','|',
			'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright',
			'imagecenter', '|', 'insertimage', 'emotion','scrawl', 'insertvideo', 'attachment', 'map', 'gmap', 'insertframe','highlightcode','webapp','pagebreak','subtitle','template','background', '|',
			'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
			'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|',
			'print', 'preview', 'searchreplace','help']";
		} elseif ($toolbar == 'desc') {
			$toolbar = "['Bold', 'Italic', '|', 'InsertOrderedList', 'InsertUnorderedList', '|', 'Link', 'Unlink', '|', 'InsertImage', '|','Source']";
		} else {
			$toolbar = "['fullscreen', 'source', '|', 'undo', 'redo', '|',
			'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch','autotypeset', '|',
			'blockquote', '|', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist','selectall', 'cleardoc', '|', 'customstyle',
			'paragraph', '|','rowspacingtop', 'rowspacingbottom','lineheight', '|','fontfamily', 'fontsize', '|',
			'directionalityltr', 'directionalityrtl', '|', '', 'indent', '|',
			'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|','touppercase','tolowercase','|',
			'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright',
			'imagecenter', '|', 'insertimage', 'emotion','scrawl', 'insertvideo', 'attachment', 'map', 'gmap', 'insertframe','highlightcode','webapp','pagebreak','subtitle','template','background', '|',
			'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
			'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|',
			'print', 'preview', 'searchreplace','help']";
		}

		$opt = array ();
		$opt [] = "toolbars:[" . $toolbar . "]";
		$opt [] = "minFrameHeight:" . $height;
		if ($allowupload) {
			$sess_id = TIME;
			$swf_auth_key = md5 ( C ( 'framework', 'auth_key' ) . $sess_id );
			$userid = cookie ( 'userid' ) ? cookie ( 'userid' ) : 0;
			$groupid = cookie ( '_groupid' ) ? cookie ( '_groupid' ) : 1;
			$isadmin = isset ( $_SESSION ['roleid'] ) ? 1 : 0;
			$opt [] = "SWFUPLOADSESSID:'" . $sess_id . "'";
			$opt [] = "swf_auth_key:'" . $swf_auth_key . "'";
			$opt [] = "isadmin:'" . $isadmin . "'";
			$opt [] = "userid:'" . $userid . "'";
			$opt [] = "groupid:'" . $groupid . "'";
		}
		// 图片上传
		$opt [] = "imageUrl:'" . SITE_URL . "index.php?app=attachment&controller=ueditor&action=upimg&catid=$catid&application=$application'";
		// 涂鸦图片配置区
		$opt [] = "scrawlUrl:'" . SITE_URL . "index.php?app=attachment&controller=ueditor&action=scrawl&catid=$catid&application=$application'";
		// 附件上传
		$opt [] = "fileUrl:'" . SITE_URL . "index.php?app=attachment&controller=ueditor&action=upfile&catid=$catid&application=$application'";
		// 远程图片抓取
		$opt [] = "catcherUrl:'" . SITE_URL . "index.php?app=attachment&controller=ueditor&action=get_remoteimage&catid=$catid&application=$application'";
		$opt [] = "localDomain:['127.0.0.1','localhost','img.baidu.com','" . SITE_HOST . "']";
		// 在线图片管理
		$opt [] = "imageManagerUrl:'" . SITE_URL . "index.php?app=attachment&controller=ueditor&action=manage'";
		// 屏幕截图
		$opt [] = "snapscreenHost:'" . SITE_HOST . "'";
		$opt [] = "snapscreenServerUrl:'" . SITE_URL . "index.php?app=attachment&controller=ueditor&action=upimg&catid=$catid&application=$application'";
		// word转存配置区
		$opt [] = "wordImageUrl:'" . SITE_URL . "index.php?app=attachment&controller=ueditor&action=upimg&catid=$catid&application=$application'";
		// 在线视频搜索
		$opt [] = "getMovieUrl:'" . SITE_URL . "api.php?controller=ueditor&action=get_movie'";
		$str .= "<script type=\"text/javascript\">\r\n";
		$str .= "var editor_" . $textareaid . " = new UE.ui.Editor({" . join ( ",", $opt ) . "});\r\neditor_" . $textareaid . ".render('$textareaid');\r\n";
		$str .= '</script>';
		return $str;
	}

	/**
	 * 图片上传
	 *
	 * @param string $name 表单名称
	 * @param int $id 表单id
	 * @param string $value 表单默认值
	 * @param string $moudle 模块名称
	 * @param int $catid 栏目id
	 * @param int $size 表单大小
	 * @param string $class 表单风格
	 * @param string $ext 表单扩展属性 如果 js事件等
	 * @param string $alowexts 允许图片格式
	 * @param array $thumb_setting
	 * @param int $watermark_setting 0或1
	 */
	public static function images($name, $id = '', $value = '', $moudle = '', $catid = '', $size = 50, $class = '', $ext = '', $alowexts = '', $thumb_setting = array(), $watermark_setting = 0) {
		if (! $id) $id = $name;
		if (! $size) $size = 50;
		if (! empty ( $thumb_setting ) && count ( $thumb_setting ))
			$thumb_ext = $thumb_setting [0] . ',' . $thumb_setting [1];
		else
			$thumb_ext = ',';
		if (! $alowexts) $alowexts = 'jpg|jpeg|gif|bmp|png';
		if (! defined ( 'IMAGES_INIT' )) {
			$str = '<script type="text/javascript" src="' . JS_PATH . 'swfupload/swf2ckeditor.js"></script>';
			define ( 'IMAGES_INIT', 1 );
		}
		$authkey = upload_key ( "1,$alowexts,1,$thumb_ext,$watermark_setting" );
		return $str . "<input type=\"text\" name=\"$name\" id=\"$id\" value=\"$value\" size=\"$size\" class=\"$class\" $ext/>  <input type=\"button\" class=\"button\" onclick=\"javascript:flashupload('{$id}_images', '" . L ( 'attachmentupload' ) . "','{$id}',submit_images,'1,{$alowexts},1,{$thumb_ext},{$watermark_setting}','{$moudle}','{$catid}','{$authkey}')\"/ value=\"" . L ( 'imagesupload' ) . "\">";
	}

	/**
	 * url 规则调用
	 *
	 * @param $application 模块
	 * @param $file 文件名
	 * @param $ishtml 是否为静态规则
	 * @param $id 选中值
	 * @param $str 表单属性
	 * @param $default_option 默认选项
	 */
	public static function urlrule($application, $file, $ishtml, $id, $str = '', $default_option = '') {
		if (! $application) $application = 'content';
		$urlrules = S ( 'common/urlrule_detail' );
		$array = array ();
		foreach ( $urlrules as $roleid => $rules ) {
			if ($rules ['application'] == $application && $rules ['file'] == $file && $rules ['ishtml'] == $ishtml) $array [$roleid] = $rules ['example'];
		}
		return self::select ( $array, $id, $str, $default_option );
	}

	/**
	 * 模板选择
	 *
	 * @param $style 风格
	 * @param $application 应用
	 * @param $id 默认选中值
	 * @param $str 属性
	 * @param $pre 模板前缀
	 */
	public static function select_template($style, $application, $id = '', $str = '', $pre = '') {
		$templatedir = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . $style . DIRECTORY_SEPARATOR . $application . DIRECTORY_SEPARATOR;
		$confing_path = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . $style . DIRECTORY_SEPARATOR . 'config.php';
		$localdir = str_replace ( array ('/','\\' ), '', 'template' ) . '|' . $style . '|' . $application;
		$templates = glob ( $templatedir . $pre . '*.html' );
		if (empty ( $templates )) {
			$style = 'default';
			$templatedir = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . $style . DIRECTORY_SEPARATOR . $application . DIRECTORY_SEPARATOR;
			$confing_path = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . $style . DIRECTORY_SEPARATOR . 'config.php';
			$localdir = str_replace ( array ('/','\\' ), '', 'template' ) . '|' . $style . '|' . $application;
			$templates = glob ( $templatedir . $pre . '*.html' );
		}
		if (empty ( $templates )) return false;
		$files = @array_map ( 'basename', $templates );
		$names = array ();
		if (file_exists ( $confing_path )) {
			$names = include $confing_path;
		}
		$templates = array ();
		if (is_array ( $files )) {
			foreach ( $files as $file ) {
				$key = substr ( $file, 0, - 5 );
				$templates [$key] = isset ( $names ['file_explan'] [$localdir] [$file] ) && ! empty ( $names ['file_explan'] [$localdir] [$file] ) ? $names ['file_explan'] [$localdir] [$file] . '(' . $file . ')' : $file;
			}
		}
		ksort ( $templates );
		return self::select ( $templates, $id, $str, L ( 'please_select' ) );
	}

	/**
	 * 栏目选择
	 *
	 * @param string $file 栏目缓存文件名
	 * @param intval/array $catid 别选中的ID，多选是可以是数组
	 * @param string $str 属性
	 * @param string $default_option 默认选项
	 * @param intval $modelid 按所属模型筛选
	 * @param intval $type 栏目类型
	 * @param intval $onlysub 只可选择子栏目
	 */
	public static function select_category($file = '', $catid = 0, $str = '', $default_option = '', $modelid = 0, $type = -1, $onlysub = 0) {
		$tree = Loader::lib ( 'Tree' );
		$result = S ( 'common/category_content' );
		$string = '<select ' . $str . '>';
		if ($default_option) $string .= "<option value='0'>$default_option</option>";
		foreach ( $result as $r ) {
			if ($type >= 0 && $r ['type'] != $type) continue;
			$r ['selected'] = '';
			if (is_array ( $catid )) {
				$r ['selected'] = in_array ( $r ['catid'], $catid ) ? 'selected' : '';
			} elseif (is_numeric ( $catid )) {
				$r ['selected'] = $catid == $r ['catid'] ? 'selected' : '';
			}
			$r ['html_disabled'] = "0";
			if (! empty ( $onlysub ) && $r ['child'] != 0) {
				$r ['html_disabled'] = "1";
			}
			$categorys [$r ['catid']] = $r;
			if ($modelid && $r ['modelid'] != $modelid) unset ( $categorys [$r ['catid']] );
		}
		$str = "<option value='\$catid' \$selected>\$spacer \$catname</option>;";
		$str2 = "<optgroup label='\$spacer \$catname'></optgroup>";

		$tree->init ( $categorys );
		$string .= $tree->get_tree_category ( 0, $str, $str2 );
		$string .= '</select>';
		return $string;
	}

	/**
	 * 下拉选择框
	 */
	public static function select($array = array(), $id = 0, $str = '', $default_option = '') {
		$string = '<select ' . $str . '>';
		$default_selected = (empty ( $id ) && $default_option) ? 'selected' : '';
		if ($default_option) $string .= "<option value='' $default_selected>$default_option</option>";
		if (! is_array ( $array ) || count ( $array ) == 0) return false;
		$ids = array ();
		if (isset ( $id )) $ids = explode ( ',', $id );
		foreach ( $array as $key => $value ) {
			$selected = in_array ( $key, $ids ) ? 'selected' : '';
			$string .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
		}
		$string .= '</select>';
		return $string;
	}

	/**
	 * 复选框
	 *
	 * @param $array 选项 二维数组
	 * @param $id 默认选中值，多个用 '逗号'分割
	 * @param $str 属性
	 * @param $defaultvalue 是否增加默认值 默认值为 -99
	 * @param $width 宽度
	 */
	public static function checkbox($array = array(), $id = '', $str = '', $defaultvalue = '', $width = 0, $field = '') {
		$string = '';
		$id = trim ( $id );
		if ($id != '') $id = strpos ( $id, ',' ) ? explode ( ',', $id ) : array ($id );
		if ($defaultvalue) $string .= '<input type="hidden" ' . $str . ' value="-99">';
		$i = 1;
		foreach ( $array as $key => $value ) {
			$key = trim ( $key );
			$checked = ($id && in_array ( $key, $id )) ? 'checked' : '';
			if ($width) $string .= '<label class="ib" style="width:' . $width . 'px">';
			$string .= '<input type="checkbox" ' . $str . ' id="' . $field . '_' . $i . '" ' . $checked . ' value="' . htmlspecialchars ( $key ) . '"> ' . htmlspecialchars ( $value );
			if ($width) $string .= '</label>';
			$i ++;
		}
		return $string;
	}

	/**
	 * 单选框
	 *
	 * @param $array 选项 二维数组
	 * @param $id 默认选中值
	 * @param $str 属性
	 */
	public static function radio($array = array(), $id = 0, $str = '', $width = 0, $field = '') {
		$string = '';
		foreach ( $array as $key => $value ) {
			$checked = trim ( $id ) == trim ( $key ) ? 'checked' : '';
			if ($width) $string .= '<label class="ib" style="width:' . $width . 'px">';
			$string .= '<input type="radio" ' . $str . ' id="' . $field . '_' . htmlspecialchars ( $key ) . '" ' . $checked . ' value="' . $key . '"> ' . $value;
			if ($width) $string .= '</label>';
		}
		return $string;
	}

	/**
	 * 验证码
	 *
	 * @param string $id 生成的验证码ID
	 * @param integer $code_len 生成多少位验证码
	 * @param integer $font_size 验证码字体大小
	 * @param integer $width 验证图片的宽
	 * @param integer $height 验证码图片的高
	 * @param string $font 使用什么字体，设置字体的URL
	 * @param string $font_color 字体使用什么颜色
	 * @param string $background 背景使用什么颜色
	 */
	public static function checkcode($id = 'checkcode', $code_len = 4, $width = 150, $height = 38, $background = '') {
		return "<img id='$id' style=\"cursor:pointer;\" onclick='this.src=this.src+\"&\"+Math.random()' src='" . SITE_URL . "api.php?controller=checkcode&code_len=$code_len&width=$width&height=$height&background=" . urlencode ( $background ) . "'>";
	}

	/**
	 * 日期时间控件
	 *
	 * @param $name 控件name，id
	 * @param $value 选中值
	 * @param $isdatetime 是否显示时间
	 * @param $loadjs 是否重复加载js，防止页面程序加载不规则导致的控件无法显示
	 * @param $showweek 是否显示周，使用，true | false
	 */
	public static function date($name, $value = '', $isdatetime = 0, $loadjs = 0, $showweek = 'true') {
		if ($value == '0000-00-00 00:00:00') $value = '';
		$id = preg_match ( "/\[(.*)\]/", $name, $m ) ? $m [1] : $name;
		if ($isdatetime) {
			$size = 21;
			$format = '%Y-%m-%d %H:%M:%S';
			$showsTime = 12;
		} else {
			$size = 10;
			$format = '%Y-%m-%d';
			$showsTime = 'false';
		}
		$str = '';
		if ($loadjs || ! defined ( 'CALENDAR_INIT' )) {
			define ( 'CALENDAR_INIT', 1 );
			$str .= '<link rel="stylesheet" type="text/css" href="' . JS_PATH . 'calendar/css/jscal2.css"/>
		<link rel="stylesheet" type="text/css" href="' . JS_PATH . 'calendar/css/border-radius.css"/>
		<link rel="stylesheet" type="text/css" href="' . JS_PATH . 'calendar/css/win2k/win2k.css"/>
		<script type="text/javascript" src="' . JS_PATH . 'calendar/jscal2.js"></script>
		<script type="text/javascript" src="' . JS_PATH . 'calendar/unicode-letter.js"></script>
		<script type="text/javascript" src="' . JS_PATH . 'calendar/lang/cn.js"></script>';
		}
		$str .= '<input type="text" name="' . $name . '" id="' . $id . '" value="' . $value . '" size="' . $size . '" class="date" readonly>&nbsp;';
		$str .= '<script type="text/javascript">
		Calendar.setup({
		weekNumbers: ' . $showweek . ',
		inputField : "' . $id . '",
		trigger    : "' . $id . '",
		dateFormat: "' . $format . '",
		showTime: ' . $showsTime . ',
		minuteStep: 1,
		onSelect   : function() {this.hide();}
	});
				</script>';
		return $str;
	}
}