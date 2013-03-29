<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: StyleController.php 500 2012-12-02 16:08:16Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class StyleController extends admin {
	// 模板文件夹
	private $filepath;

	public function __construct() {
		$this->filepath = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR;
		parent::__construct ();
	}

	public function init() {
		Loader::helper ( 'admin:global' );
		$list = template_list ( 1 );
		$big_menu = big_menu ( '?app=template&controller=style&action=import', 'import', L ( 'import_style' ), 500, 250 );
		include $this->admin_tpl ( 'style_list' );
	}

	/**
	 * 设置默认风格
	 */
	public function set_default() {
		$style = isset ( $_GET ['style'] ) && trim ( $_GET ['style'] ) ? trim ( $_GET ['style'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		Core_Config::modify ( 'template', array ('name' => $style ) ); // 模板配置
		$category_model = Loader::model ( 'category_model' );
		$result = S ( 'common/category_content' ); // 加载栏目缓存
		if (! empty ( $result ) && is_array ( $result )) {
			foreach ( $result as $r ) {
				$setting = string2array($r['setting']);
				$setting['template_list'] = $style;
				$setting = array2string ( $setting );
				$category_model->where(array('catid'=>$r['catid']))->update(array('setting'=>$setting));
			}
		}
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 开启禁用风格
	 */
	public function disable() {
		$style = isset ( $_GET ['style'] ) && trim ( $_GET ['style'] ) ? trim ( $_GET ['style'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$filepath = $this->filepath . $style . DIRECTORY_SEPARATOR . 'config.php';
		if (file_exists ( $filepath )) {
			$arr = include $filepath;
			if (! isset ( $arr ['disable'] )) {
				$arr ['disable'] = 1;
			} else {
				if ($arr ['disable'] == 1) $arr ['disable'] = 0;
				else $arr ['disable'] = 1;
			}
			if (is_writable ( $filepath )) file_put_contents ( $filepath, '<?php return ' . var_export ( $arr, true ) . ';?>' );
			else showmessage ( L ( 'file_does_not_writable' ), HTTP_REFERER );
		} else {
			$arr = array ('name' => $style,'disable' => 1,'dirname' => $style );
			file_put_contents ( $filepath, '<?php return ' . var_export ( $arr, true ) . ';?>' );
		}
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 导出风格
	 */
	public function export() {
		$style = isset ( $_GET ['style'] ) && trim ( $_GET ['style'] ) ? trim ( $_GET ['style'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$filepath = $this->filepath . $style . DIRECTORY_SEPARATOR . 'config.php';
		if (file_exists ( $filepath )) {
			$arr = include $filepath;
			if (CHARSET == 'gbk') $arr = array_iconv ( $arr );
			$data = base64_encode ( json_encode ( $arr ) );
			header ( "Content-type: application/octet-stream" );
			header ( "Content-Disposition: attachment; filename=yun_template_" . $style . '.txt' );
			echo $data;
		} else
			showmessage ( L ( 'file_does_not_exists' ), HTTP_REFERER );
	}

	/**
	 * 导入模版
	 */
	public function import() {
		if (isset ( $_POST ['dosubmit'] )) {
			$type = isset ( $_POST ['type'] ) && trim ( $_POST ['type'] ) ? trim ( $_POST ['type'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
			if ($type == 1) {
				$filename = $_FILES ['file'] ['tmp_name'];
				if (strtolower ( substr ( $_FILES ['file'] ['name'], - 3, 3 ) ) != 'txt') showmessage ( L ( 'only_allowed_to_upload_txt_files' ), HTTP_REFERER );
				$code = json_decode ( base64_decode ( file_get_contents ( $filename ) ), true );
				@unlink ( $filename );
			} elseif ($type == 2) {
				$code = isset ( $_POST ['code'] ) && trim ( $_POST ['code'] ) ? json_decode ( base64_decode ( trim ( $_POST ['code'] ) ), true ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
				if (! isset ( $code ['dirname'] )) showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			}
			if (CHARSET == 'gbk') $code = array_iconv ( $code, 'utf-8', 'gbk' );
			if (! file_exists ( $this->filepath . $code ['dirname'] . DIRECTORY_SEPARATOR . 'config.php' )) {
				if (@is_writable ( $this->filepath . $code ['dirname'] . DIRECTORY_SEPARATOR )) {
					@mkdir ( $this->filepath . $code ['dirname'] . DIRECTORY_SEPARATOR, 0755, true );
					@file_put_contents ( $this->filepath . $code ['dirname'] . DIRECTORY_SEPARATOR . 'config.php', '<?php return ' . var_export ( $code, true ) . ';?>' );
					showmessage ( L ( 'operation_success' ), HTTP_REFERER, '', 'import' );
				} else
					showmessage ( L ( 'template_directory_not_write' ), HTTP_REFERER );
			} else
				showmessage ( L ( 'file_exists' ), HTTP_REFERER );
		} else {
			$show_header = true;
			include $this->admin_tpl ( 'style_import' );
		}
	}

	/**
	 * 更新模版名称
	 */
	public function updatename() {
		$name = isset ( $_POST ['name'] ) ? $_POST ['name'] : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		if (is_array ( $name )) {
			foreach ( $name as $key => $val ) {
				$filepath = $this->filepath . $key . DIRECTORY_SEPARATOR . 'config.php';
				if (file_exists ( $filepath )) {
					$arr = include $filepath;
					$arr ['name'] = $val;
				} else
					$arr = array ('name' => $val,'disable' => 0,'dirname' => $key );
				@file_put_contents ( $filepath, '<?php return ' . var_export ( $arr, true ) . ';?>' );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
	}
}