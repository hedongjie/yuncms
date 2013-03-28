<?php
/**
 * 模版文件管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: FileController.php 252 2012-11-07 14:52:09Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
error_reporting ( E_ERROR );
class FileController extends admin {

	/**
	 * 模板文件夹
	 *
	 * @var string
	 */
	private $filepath;

	/**
	 * 风格名
	 *
	 * @var string
	 */
	private $style;

	// 风格属性
	private $style_info;

	// 是否允许在线编辑模板
	private $tpl_edit;

	/**
	 * 模版后缀
	 *
	 * @var string
	 */
	private $suffix;

	public function __construct() {
		$this->style = isset ( $_GET ['style'] ) && trim ( $_GET ['style'] ) ? str_replace ( array ('..\\','../','./','.\\','/','\\' ), '', trim ( $_GET ['style'] ) ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		if (empty ( $this->style )) showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$this->filepath = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . $this->style . DIRECTORY_SEPARATOR;
		if (file_exists ( $this->filepath . 'config.php' )) {
			$this->style_info = include $this->filepath . 'config.php';
			if (! isset ( $this->style_info ['name'] )) $this->style_info ['name'] = $this->style;
		}
		$this->suffix = C ( 'template', 'ext' );
		$this->tpl_edit = C ( 'template', 'edit' );
		parent::__construct ();
	}

	public function init() {
		$dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? str_replace ( array ('..\\','../','./','.\\' ), '', trim ( $_GET ['dir'] ) ) : '';
		$filepath = $this->filepath . $dir;
		$list = glob ( $filepath . DIRECTORY_SEPARATOR . '*' );
		if (! empty ( $list )) ksort ( $list );
		$local = str_replace ( array (SOURCE_PATH,DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR ), array ('',DIRECTORY_SEPARATOR ), $filepath );
		if (substr ( $local, - 1, 1 ) == '.') {
			$local = substr ( $local, 0, (strlen ( $local ) - 1) );
		}
		$encode_local = str_replace ( array ('/','\\' ), '|', $local );
		$file_explan = isset ( $this->style_info ['file_explan'] ) ? $this->style_info ['file_explan'] : '';
		$show_header = true;
		include $this->admin_tpl ( 'file_list' );
	}

	public function public_ajax_get() {
		$op_tag = Loader::lib ( $_GET ['op'] . ':' . $_GET ['op'] . "_tag" );
		$html = $op_tag->$_GET ['do'] ( $_GET ['html'], $_GET ['value'], $_GET ['id'] );
		echo $html;
	}

	/**
	 * 编辑YUN标签
	 */
	public function edit_yun_tag() {
		if (empty ( $this->tpl_edit )) showmessage ( L ( 'tpl_edit' ) );
		$dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? str_replace ( array ('..\\','../','./','.\\' ), '', urldecode ( trim ( $_GET ['dir'] ) ) ) : showmessage ( L ( 'illegal_operation' ) );
		$file = isset ( $_GET ['file'] ) && trim ( $_GET ['file'] ) ? urldecode ( trim ( $_GET ['file'] ) ) : showmessage ( L ( 'illegal_operation' ) );
		$op = isset ( $_GET ['op'] ) && trim ( $_GET ['op'] ) ? trim ( $_GET ['op'] ) : showmessage ( L ( 'illegal_operation' ) );
		$tag_md5 = isset ( $_GET ['tag_md5'] ) && trim ( $_GET ['tag_md5'] ) ? trim ( $_GET ['tag_md5'] ) : showmessage ( L ( 'illegal_operation' ) );
		$show_header = $show_scroll = $show_validator = true;
		Loader::helper ( 'template:global' );
		$filepath = $this->filepath . $dir . DIRECTORY_SEPARATOR . $file;
		switch ($op) {
			case 'xml' :
			case 'json' :
				if ($_POST ['dosubmit']) {
					$url = isset ( $_POST ['url'] ) && trim ( $_POST ['url'] ) ? trim ( $_POST ['url'] ) : showmessage ( L ( 'data_address' ) . L ( 'empty' ) );
					$cache = isset ( $_POST ['cache'] ) && trim ( $_POST ['cache'] ) ? trim ( $_POST ['cache'] ) : 0;
					$return = isset ( $_POST ['return'] ) && trim ( $_POST ['return'] ) ? trim ( $_POST ['return'] ) : '';
					if (! preg_match ( '/http:\/\//i', $url )) {
						showmessage ( L ( 'data_address_reg_sg' ), HTTP_REFERER );
					}
					$tag_md5_list = tag_md5 ( $filepath );
					$yun_tag = creat_yun_tag ( $op, array ('url' => $url,'cache' => $cache,'return' => $return ) );
					if (in_array ( $tag_md5, $tag_md5_list [0] )) {
						$old_yun_tag = $tag_md5_list [1] [$tag_md5];
					}
					if (replace_yun_tag ( $filepath, $old_yun_tag, $yun_tag, $this->style, $dir )) {
						showmessage ( L ( 'operation_success' ), '', '', 'edit', 'if(!window.top.right){parent.location.reload();}' );
					} else {
						showmessage ( L ( 'failure_the_document_may_not_to_write' ) );
					}
				}
				include $this->admin_tpl ( 'yun_tag_tools_json_xml' );
				break;

			case 'get' :
				if ($_POST ['dosubmit']) {
					$sql = isset ( $_POST ['sql'] ) && trim ( $_POST ['sql'] ) ? trim ( $_POST ['sql'] ) : showmessage ( 'SQL' . L ( 'empty' ) );
					$dbsource = isset ( $_POST ['dbsource'] ) && trim ( $_POST ['dbsource'] ) ? trim ( $_POST ['dbsource'] ) : '';
					$cache = isset ( $_POST ['cache'] ) && intval ( $_POST ['cache'] ) ? intval ( $_POST ['cache'] ) : 0;
					$return = isset ( $_POST ['return'] ) && trim ( $_POST ['return'] ) ? trim ( $_POST ['return'] ) : '';
					$tag_md5_list = tag_md5 ( $filepath );
					$yun_tag = creat_yun_tag ( $op, array ('sql' => $sql,'dbsource' => $dbsource,'cache' => $cache,'return' => $return ) );
					if (in_array ( $tag_md5, $tag_md5_list [0] )) {
						$old_yun_tag = $tag_md5_list [1] [$tag_md5];
					}
					if (replace_yun_tag ( $filepath, $old_yun_tag, $yun_tag, $this->style, $dir )) {
						showmessage ( L ( 'operation_success' ), '', '', 'edit', 'if(!window.top.right){parent.location.reload();}' );
					} else {
						showmessage ( L ( 'failure_the_document_may_not_to_write' ) );
					}
				}
				$dbsource_db = Loader::model ( 'dbsource_model' );
				$r = $dbsource_db->select ( '', 'name' );
				$dbsource_list = array ('' => L ( 'please_select' ) );
				foreach ( $r as $v ) {
					$dbsource_list [$v ['name']] = $v ['name'];
				}
				include $this->admin_tpl ( 'yun_tag_tools_get' );
				break;

			default :
				if (! file_exists ( APPS_PATH . $op . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . $op . '_tag.php' )) {
					showmessage ( L ( 'the_application_will_not_support_the_operation' ) );
				}
				$op_tag = Loader::lib ( $op.':'.$op . "_tag" );
				if (! method_exists ( $op_tag, 'yun_tag' )) {
					showmessage ( L ( 'the_application_will_not_support_the_operation' ) );
				}
				$html = $op_tag->yun_tag ();
				if (isset($_POST ['dosubmit'])) {
					$do = isset ( $_POST ['do'] ) && trim ( $_POST ['do'] ) ? trim ( $_POST ['do'] ) : 0;
					$data = array ('do' => $do );
					if (isset ( $html [$do] ) && is_array ( $html [$do] )) {
						foreach ( $html [$do] as $key => $val ) {
							$val ['validator'] ['reg_msg'] = isset($val ['validator'] ['reg_msg']) ? $val ['validator'] ['reg_msg'] : $val ['name'] . L ( 'inputerror' );
							if ($val ['htmltype'] != 'checkbox') {
								$$key = isset ( $_POST [$key] ) && trim ( $_POST [$key] ) ? trim ( $_POST [$key] ) : '';
							} else {
								$$key = isset ( $_POST [$key] ) && $_POST [$key] ? implode ( ',', $_POST [$key] ) : '';
							}
							if (isset ( $val ['ajax'] ['id'] ) && ! empty ( $val ['ajax'] ['id'] )) {
								$data [$val ['ajax'] ['id']] = isset ( $_POST [$val ['ajax'] ['id']] ) && trim ( $_POST [$val ['ajax'] ['id']] ) ? trim ( $_POST [$val ['ajax'] ['id']] ) : '';
							}
							if (! empty ( $val ['validator'] )) {
								if (isset ( $val ['validator'] ['min'] ) && strlen ( $$key ) < $val ['validator'] ['min']) {
									showmessage ( $val ['name'] . L ( 'should' ) . L ( 'is_greater_than' ) . $val ['validator'] ['min'] . L ( 'lambda' ) );
								}
								if (isset ( $val ['validator'] ['max'] ) && strlen ( $$key ) > $val ['validator'] ['max']) {
									showmessage ( $val ['name'] . L ( 'should' ) . L ( 'less_than' ) . $val ['validator'] ['max'] . L ( 'lambda' ) );
								}
								if (! preg_match ( '/' . $val ['validator'] ['reg'] . '/' . $val ['validator'] ['reg_param'], $$key )) {
									showmessage ( $val ['name'] . $val ['validator'] ['reg_msg'] );
								}
							}
							$data [$key] = $$key;
						}
					}

					$page = isset ( $_POST ['page'] ) && trim ( $_POST ['page'] ) ? trim ( $_POST ['page'] ) : '';
					$num = isset ( $_POST ['num'] ) && intval ( $_POST ['num'] ) ? intval ( $_POST ['num'] ) : 0;
					$return = isset ( $_POST ['return'] ) && trim ( $_POST ['return'] ) ? trim ( $_POST ['return'] ) : '';
					$cache = isset ( $_POST ['cache'] ) && intval ( $_POST ['cache'] ) ? intval ( $_POST ['cache'] ) : 0;
					$data ['page'] = $page;
					$data ['num'] = $num;
					$data ['return'] = $return;
					$data ['cache'] = $cache;

					$tag_md5_list = tag_md5 ( $filepath );
					$yun_tag = creat_yun_tag ( $op, $data );
					if (in_array ( $tag_md5, $tag_md5_list [0] )) {
						$old_yun_tag = $tag_md5_list [1] [$tag_md5];
					}
					if (replace_yun_tag ( $filepath, $old_yun_tag, $yun_tag, $this->style, $dir )) {
						showmessage ( L ( 'operation_success' ), '', '', 'edit', "window.top.art.dialog.tips('" . L ( 'operation_success' ) . "', 2);if(!window.top.right){parent.location.reload();}" );
					} else {
						showmessage ( L ( 'failure_the_document_may_not_to_write' ) );
					}
				}
				include $this->admin_tpl ( 'yun_tag_application' );
				break;
		}
	}

	/**
	 * 可视化编辑模版
	 */
	public function visualization() {
		error_reporting ( E_ERROR );
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? str_replace ( array ('..\\','../','./','.\\' ), '', urldecode ( trim ( $_GET ['dir'] ) ) ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$file = isset ( $_GET ['file'] ) && trim ( $_GET ['file'] ) ? trim ( $_GET ['file'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		ob_start ();
		include template ( $dir, basename ( $file, $this->suffix ), $this->style );
		$html = ob_get_contents ();
		ob_clean ();
		Loader::helper( 'template:global' );
		$html = visualization ( $html, $this->style, $dir, $file );
		echo $html;
	}

	/**
	 * 更新模版名称
	 */
	public function updatefilename() {
		$file_explan = isset ( $_POST ['file_explan'] ) ? $_POST ['file_explan'] : '';
		if (! isset ( $this->style_info ['file_explan'] )) $this->style_info ['file_explan'] = array ();
		$this->style_info ['file_explan'] = array_merge ( $this->style_info ['file_explan'], $file_explan );
		@file_put_contents ( $this->filepath . 'config.php', '<?php return ' . var_export ( $this->style_info, true ) . ';?>' );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 修改模版
	 */
	public function edit_file() {
		if (empty ( $this->tpl_edit )) showmessage ( L ( 'tpl_edit' ) );
		$dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? str_replace ( array ('..\\','../','./','.\\' ), '', urldecode ( trim ( $_GET ['dir'] ) ) ) : '';
		$file = isset ( $_GET ['file'] ) && trim ( $_GET ['file'] ) ? trim ( $_GET ['file'] ) : '';
		if ($file) {
			preg_match ( '/^([a-zA-Z0-9])?([^.|-|_]+)/i', $file, $file_t );
			$file_t = $file_t [0];
			$file_t_v = array ('header' => array ('{$SEO[\'title\']}' => L ( 'seo_title' ),'{$SEO[\'site_title\']}' => L ( 'site_title' ),'{$SEO[\'keyword\']}' => L ( 'seo_keyword' ),'{$SEO[\'description\']}' => L ( 'seo_des' ) ),'category' => array ('{$catid}' => L ( 'cat_id' ),'{$catname}' => L ( 'cat_name' ),'{$url}' => L ( 'cat_url' ),'{$r[catname]}' => L ( 'cat_name' ),'{$r[url]}' => 'URL','{$CATEGORYS}' => L ( 'cats' ) ),'list' => array ('{$catid}' => L ( 'cat_id' ),'{$catname}' => L ( 'cat_name' ),'{$url}' => L ( 'cat_url' ),'{$CATEGORYS}' => L ( 'cats' ) ),'show' => array ('{$title}' => L ( 'title' ),'{$inputtime}' => L ( 'inputtime' ),'{$copyfrom}' => L ( 'comeform' ),'{$content}' => L ( 'content' ),'{$previous_page[url]}' => L ( 'pre_url' ),'{$previous_page[title]}' => L ( 'pre_title' ),'{$next_page[url]}' => L ( 'next_url' ),'{$next_page[title]}' => L ( 'next_title' ) ),'page' => array ('{$CATEGORYS}' => L ( 'cats' ),'{$content}' => L ( 'content' ) ) );
		}
		if (('.' . File::get_suffix ( $file )) != $this->suffix) showmessage ( L ( "can_edit_files" ) );
		$filepath = $this->filepath . $dir . DIRECTORY_SEPARATOR . $file;
		$is_write = 0;
		if (is_writable ( $filepath )) $is_write = 1;
		if (isset ( $_POST ['dosubmit'] )) {
			$code = isset ( $_POST ['code'] ) ? stripslashes ( $_POST ['code'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
			if ($is_write == 1) {
				Loader::helper ( 'template:global' );
				creat_template_bak ( $filepath, $this->style, $dir );
				file_put_contents ( $filepath, htmlspecialchars_decode ( $code ) );
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			} else
				showmessage ( L ( "file_does_not_writable" ), HTTP_REFERER );
		} else {
			if (file_exists ( $filepath )) $data = htmlspecialchars ( file_get_contents ( $filepath ) );
			else showmessage ( L ( 'file_does_not_exists' ) );
		}
		$show_header = true;
		include $this->admin_tpl ( 'file_edit_file' );
	}

	/**
	 * 添加模版
	 */
	public function add_file() {
		if (empty ( $this->tpl_edit )) showmessage ( L ( 'tpl_edit' ) );
		$dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? str_replace ( array ('..\\','../','./','.\\' ), '', urldecode ( trim ( $_GET ['dir'] ) ) ) : '';
		$filepath = $this->filepath . $dir . DIRECTORY_SEPARATOR;
		$is_write = 0;
		if (is_writable ( $filepath )) $is_write = 1;
		if (! $is_write) showmessage ( 'dir_not_writable' );
		if (isset ( $_POST ['dosubmit'] )) {
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( '' );
			if (! preg_match ( '/^[\w]+$/i', $name )) showmessage ( L ( 'name_datatype_error' ), HTTP_REFERER );
			if ($is_write == 1) {
				@file_put_contents ( $filepath . $name . $this->suffix, '' );
				showmessage ( '', '', '', 'add_file' );
			} else
				showmessage ( L ( "dir_not_writable" ), HTTP_REFERER );
		}
		$show_header = $show_validator = true;
		include $this->admin_tpl ( 'file_add_file' );
	}

	/**
	 * 检查模版名称是否存在
	 */
	public function public_name() {
		$dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? str_replace ( array ('..\\','../','./','.\\' ), '', urldecode ( trim ( $_GET ['dir'] ) ) ) : '';
		$name = isset ( $_GET ['name'] ) && trim ( $_GET ['name'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['name'] ) ) : trim ( $_GET ['name'] )) : exit ( '0' );
		$filepath = $this->filepath . $dir . DIRECTORY_SEPARATOR . $name . $this->suffix;
		if (file_exists ( $filepath )) exit ( '0' );
		else exit ( '1' );
	}
}