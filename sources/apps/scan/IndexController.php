<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 * 木马扫描
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 145 2013-03-25 13:09:15Z 85825770@qq.com $
 */
class IndexController extends admin {

	protected $safe = array ('file_type' => 'php|js','code' => '','func' => 'com|system|exec|eval|escapeshell|cmd|passthru|base64_decode|gzuncompress','dir' => '','md5_file' => '' );

	public function __construct() {
		parent::__construct ();
	}

	public function init() {
		$list = glob ( BASE_PATH . '*' );
		if (file_exists ( DATA_PATH . 'scan' )) {
			$md5_file_list = glob ( DATA_PATH . 'scan' . DIRECTORY_SEPARATOR . 'md5_*.php' );
			foreach ( $md5_file_list as $k => $v ) {
				$md5_file_list [$v] = basename ( $v );
				unset ( $md5_file_list [$k] );
			}
		}

		$scan = S ( 'scan/scan_config' );
		if (is_array ( $scan )) {
			$scan = array_merge ( $this->safe, $scan );
		} else {
			$scan = $this->safe;
		}
		$scan ['dir'] = string2array ( $scan ['dir'] );
		include $this->admin_tpl ( 'scan_index' );
	}

	/**
	 * 进行配置文件更新
	 */
	public function public_update_config() {
		$info = isset ( $_POST ['info'] ) ? $_POST ['info'] : showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		$dir = isset($_POST['dir']) ? new_stripslashes($_POST['dir']) : '';
		if (empty ( $dir )) showmessage ( L ( 'please_select_the_content' ), U ( 'scan/index/init' ) );
		$info['dir'] = var_export($dir, true);
		S ( 'scan/scan_config', $info );
		showmessage ( L ( 'configuration_file_save_to_the' ), U ( 'scan/index/public_file_count' ) );
	}

	/**
	 * 对要进行扫描的文件进行统计
	 */
	public function public_file_count() {
		$scan = S ( 'scan/scan_config' );
		Loader::helper ( 'scan:global' );
		set_time_limit ( 120 );
		$scan ['dir'] = string2array ( $scan ['dir'] );
		$scan ['file_type'] = explode ( '|', $scan ['file_type'] );
		$list = array ();
		foreach ( $scan ['dir'] as $v ) {
			if (is_dir ( $v )) {
				foreach ( $scan ['file_type'] as $k ) {
					$list = array_merge ( $list, scan_file_lists ( $v . DIRECTORY_SEPARATOR, 1, $k, 0, 1, 1 ) );
				}
			} else {
				$list = array_merge ( $list, array (str_replace ( BASE_PATH, '', $v ) => md5_file ( $v ) ) );
			}
		}
		S ( 'scan/scan_list', $list );
		showmessage ( L ( 'documents_to_file_the_statistics' ), U ( 'scan/index/public_file_filter' ) );
	}

	/**
	 * 对文件进行筛选
	 */
	public function public_file_filter() {
		$scan_list = S ( 'scan/scan_list' );
		$scan = S ( 'scan/scan_config' );
		if (file_exists ( $scan ['md5_file'] )) {
			$old_md5 = include $scan ['md5_file'];
			foreach ( $scan_list as $k => $v ) {
				if (isset ( $old_md5 [$k] ) && $v == $old_md5 [$k]) unset ( $scan_list [$k] );
			}
		}
		S ( 'scan/scan_list', $scan_list );
		showmessage ( L ( 'file_through_a_feature_the_function_is' ), U ( 'scan/index/public_file_func' ) );
	}

	/**
	 * 进行特征函数过滤
	 */
	public function public_file_func() {
		@set_time_limit ( 600 );
		$file_list = S ( 'scan/scan_list' );
		$scan = S ( 'scan/scan_config' );
		if (isset ( $scan ['func'] ) && ! empty ( $scan ['func'] )) {
			foreach ( $file_list as $key => $val ) {
				$html = file_get_contents ( BASE_PATH . $key );
				if (stristr ( $key, '.php.' ) != false || preg_match_all ( '/[^a-z]?(' . $scan ['func'] . ')\s*\(/i', $html, $state, PREG_SET_ORDER )) $badfiles [$key] ['func'] = $state;
			}
		}
		if (! isset ( $badfiles )) $badfiles = array ();
		S ( 'scan/scan_bad_file', $badfiles );
		showmessage ( L ( 'feature_function_complete_a_code_used_by_filtration' ), U ( 'scan/index/public_file_code' ) );
	}

	/**
	 * 进行特征代码过滤
	 */
	public function public_file_code() {
		@set_time_limit ( 600 );
		$file_list = S ( 'scan/scan_list' );
		$scan = S ( 'scan/scan_config' );
		$badfiles = S ( 'scan/scan_bad_file' );
		if (isset ( $scan ['code'] ) && ! empty ( $scan ['code'] )) {
			foreach ( $file_list as $key => $val ) {
				$html = file_get_contents ( BASE_PATH . $key );
				if (stristr ( $key, '.php.' ) != false || preg_match_all ( '/[^a-z]?(' . $scan ['code'] . ')/i', $html, $state, PREG_SET_ORDER )) $badfiles [$key] ['code'] = $state;
				if (strtolower ( substr ( $key, - 4 ) ) == '.php' && function_exists ( 'zend_loader_file_encoded' ) && zend_loader_file_encoded ( BASE_PATH . $key )) $badfiles [$key] ['zend'] = 'zend encoded';
			}
		}
		S ( 'scan/scan_bad_file', $badfiles );
		showmessage ( L ( 'scan_completed' ), U ( 'scan/index/scan_report', array ('menuid' => 237 ) ) );
	}

	public function scan_report() {
		$badfiles = S ( 'scan/scan_bad_file' );
		if (empty ( $badfiles )) showmessage ( L ( 'scan_to_find_a_result_please_to_scan' ), U ( 'scan/index/init' ) );
		include $this->admin_tpl ( 'scan_report' );
	}

	/**
	 * 查看特征码文件
	 */
	public function public_view() {
		$url = isset ( $_GET ['url'] ) && trim ( $_GET ['url'] ) ? urldecode ( trim ( $_GET ['url'] ) ) : showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		if (! file_exists ( BASE_PATH . $url )) showmessage ( L ( 'file_not_exists' ) );
		$html = file_get_contents ( BASE_PATH . $url );
		$file_list = S ( 'scan/scan_bad_file' );
		if (isset ( $file_list [$url] ['func'] ) && is_array ( $file_list [$url] ['func'] ) && ! empty ( $file_list [$url] ['func'] )) foreach ( $file_list [$url] ['func'] as $key => $val ) {
			$func [$key] = strtolower ( $val [1] );
		}
		if (isset ( $file_list [$url] ['code'] ) && is_array ( $file_list [$url] ['code'] ) && ! empty ( $file_list [$url] ['code'] )) foreach ( $file_list [$url] ['code'] as $key => $val ) {
			$code [$key] = strtolower ( $val [1] );
		}
		if (isset ( $func )) $func = array_unique ( $func );
		if (isset ( $code )) $code = array_unique ( $code );
		$show_header = true;
		include $this->admin_tpl ( 'public_view' );
	}

	/**
	 * 创建MD5
	 */
	public function md5_creat() {
		set_time_limit ( 120 );
		$pro = isset ( $_GET ['pro'] ) && intval ( $_GET ['pro'] ) ? intval ( $_GET ['pro'] ) : 1;
		Loader::helper ( 'scan:global' );
		switch ($pro) {
			case '1' : // 统计文件
				$msg = L ( 'please_wait' );
				ob_start ();
				include $this->admin_tpl ( 'md5_creat' );
				ob_flush ();
				ob_clean ();
				$list = scan_file_lists ( BASE_PATH, 1, 'php', 0, 1 );
				$list = "<?php\nreturn " . stripcslashes ( var_export ( $list, true ) ) . ";\n?>";
				File::write ( DATA_PATH . 'scan/md5_' . date ( 'Y-m-d' ) . '.php', $list );
				echo '<script type="text/javascript">location.href="?app=scan&controller=index&action=md5_creat&pro=2"</script>';
				break;

			case '2' :
				showmessage ( L ( 'viewreporttrue' ), U ( 'scan/index/init' ) );
				break;
		}
	}
}