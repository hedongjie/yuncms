<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 应用操作API
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: application_api.php 406 2012-11-16 02:46:22Z xutongle $
 */
class application_api {
	private $db, $m_db, $installdir, $uninstaldir, $application, $isall;
	public $error_msg = '';
	public function __construct() {
		$this->db = Loader::model ( 'application_model' );
	}

	/**
	 * 模块安装
	 *
	 * @param string $application
	 */
	public function install($application = '') {
		defined ( 'INSTALL' ) or define ( 'INSTALL', true );
		if ($application) $this->application = $application;
		$this->installdir = APPS_PATH . $this->application . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR;
		$this->check ();
		$models = @require ($this->installdir . 'model.php');
		if (! is_array ( $models ) || empty ( $models )) $models = array ('application' );
		if (! in_array ( 'application', $models )) array_unshift ( $models, 'application' );
		if (is_array ( $models ) && ! empty ( $models )) {
			foreach ( $models as $m ) {
				$sql = file_get_contents ( $this->installdir . $m . '.sql' );
				$this->sql_execute ( $sql );
				$this->m_db = Loader::model ( $m . '_model' );
			}
		}
		if (file_exists ( $this->installdir . 'extention.inc.php' )) {
			$admin_menu_db = Loader::model ( 'admin_menu_model' );
			@include ($this->installdir . 'extention.inc.php');
			if (! defined ( 'INSTALL_APPLICATION' )) {
				$file = SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . 'admin_menu.php';
				if (file_exists ( $file )) {
					$content = file_get_contents ( $file );
					$content = substr ( $content, 0, - 2 );
					$data = '';
					foreach ( $language as $key => $l ) {
						if (L ( $key, '', 'admin_menu' ) == L ( 'NO_LANG' ) . '[' . $key . ']') $data .= "\$LANG['" . $key . "'] = '" . $l . "';\r\n";
					}
					$data = $content . $data . "?>";
					file_put_contents ( $file, $data );
				} else {
					foreach ( $language as $key => $l ) {
						if (L ( $key, '', 'admin_menu' ) == L ( 'NO_LANG' ) . '[' . $key . ']') $data .= "\$LANG['" . $key . "'] = '" . $l . "';\r\n";
					}
					$data = "<?" . "php\r\n\$data?>";
					file_put_contents ( $file, $data );
				}
			}
		}

		if (! defined ( 'INSTALL_APPLICATION' )) {
			if (file_exists ( $this->installdir . 'languages' . DIRECTORY_SEPARATOR )) {
				Folder::copy ( $this->installdir . 'languages' . DIRECTORY_SEPARATOR, SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR );
			}
			if (file_exists ( $this->installdir . 'template' . DIRECTORY_SEPARATOR )) {
				Folder::copy ( $this->installdir . 'template' . DIRECTORY_SEPARATOR, SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR );
				if (file_exists ( $this->installdir . 'template' . DIRECTORY_SEPARATOR . 'name.inc.php' )) {
					$keyid = 'template|' . C ( 'template', 'name' ) . '|' . $this->application;
					$file_explan [$keyid] = include $this->installdir . 'templates' . DIRECTORY_SEPARATOR . 'name.inc.php';
					$viewpath = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
					if (file_exists ( $viewpath . 'config.php' )) {
						$style_info = include $viewpath . 'config.php';
						$style_info ['file_explan'] = array_merge ( $style_info ['file_explan'], $file_explan );
						@file_put_contents ( $viewpath . 'config.php', '<?php return ' . var_export ( $style_info, true ) . ';?>' );
					}
					unlink ( SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR . $this->application . DIRECTORY_SEPARATOR . 'name.inc.php' );
				}
			}
		}
		return true;
	}

	/**
	 * 检查安装目录
	 *
	 * @param string $application
	 */
	public function check($application = '') {
		defined ( 'INSTALL' ) or define ( 'INSTALL', true );
		if ($application) $this->application = $application;
		if (! $this->application) {
			$this->error_msg = L ( 'no_application' );
			return false;
		}
		if (! defined ( 'INSTALL_APPLICATION' )) {
			if (Folder::create ( SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . 'test_create_dir' )) {
				sleep ( 1 );
				Folder::delete ( SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . 'test_create_dir' );
			} else {
				$this->error_msg = L ( 'lang_dir_no_write' );
				return false;
			}
		}
		$r = $this->db->where ( array ('application' => $this->application ) )->find ();
		if ($r) {
			$this->error_msg = L ( 'this_application_installed' );
			return false;
		}
		if (! $this->installdir) $this->installdir = APPS_PATH . $this->application . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR;
		if (! is_dir ( $this->installdir )) {
			$this->error_msg = L ( 'install_dir_no_exist' );
			return false;
		}
		if (! file_exists ( $this->installdir . 'application.sql' )) {
			$this->error_msg = L ( 'application_sql_no_exist' );
			return false;
		}
		$models = @require ($this->installdir . 'model.php');
		if (is_array ( $models ) && ! empty ( $models )) {
			foreach ( $models as $app ) {
				if (! file_exists ( SOURCE_PATH . 'model' . DIRECTORY_SEPARATOR . $app . '_model.php' )) {
					copy ( $this->installdir . 'model' . DIRECTORY_SEPARATOR . $app . '_model.php', SOURCE_PATH . 'model' . DIRECTORY_SEPARATOR . $app . '_model.php' );
				}
				if (! file_exists ( $this->installdir . $app . '.sql' )) {
					$this->error_msg = $app . L ( 'sql_no_exist' );
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 模块卸载
	 *
	 * @param string $application
	 */
	public function uninstall($application) {
		define ( 'UNINSTALL', true );
		if (! $application) {
			$this->error_msg = L ( 'illegal_parameters' );
			return false;
		}
		$this->application = $application;
		$this->uninstalldir = APPS_PATH . $this->application . DIRECTORY_SEPARATOR . 'uninstall' . DIRECTORY_SEPARATOR;
		if (! is_dir ( $this->uninstalldir )) {
			$this->error_msg = L ( 'uninstall_dir_no_exist' );
			return false;
		}
		if (file_exists ( $this->uninstalldir . 'model.php' )) {
			$models = @require ($this->uninstalldir . 'model.php');
			if (is_array ( $models ) && ! empty ( $models )) {
				foreach ( $models as $m ) {
					if (! file_exists ( $this->uninstalldir . $m . '.sql' )) {
						$this->error_msg = $this->application . DIRECTORY_SEPARATOR . 'uninstall' . DIRECTORY_SEPARATOR . $m . L ( 'sql_no_exist' );
						return false;
					}
				}
			}
		}
		if (file_exists ( $this->uninstalldir . 'extention.inc.php' )) @include ($this->uninstalldir . 'extention.inc.php');

		if (is_array ( $models ) && ! empty ( $models )) {
			foreach ( $models as $m ) {
				$this->m_db = Loader::model ( $m . '_model' );
				$sql = file_get_contents ( $this->uninstalldir . $m . '.sql' );
				$this->sql_execute ( $sql );
				// @unlink ( SOURCE_PATH . 'model' . DIRECTORY_SEPARATOR . $m .
				// '_model.php' );
			}
		}
		/*
        if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application . '.php' )) {
            @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application . '.php' );
        }
        if (is_dir ( SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR . $this->application )) {
            Folder::delete ( SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR . $this->application );
        }
        $viewpath = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
        if (file_exists ( $viewpath . 'config.php' )) {
            $keyid = 'template|' . C ( 'template', 'name' ) . '|' . $this->application;
            $style_info = include $viewpath . 'config.php';
            unset ( $style_info ['file_explan'] [$keyid] );
            @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php/*
		 * if (file_exists ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR . C
		 * ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' )) { @unlink ( SOURCE_PATH . 'language' . DIRECTORY_SEPARATOR
		 * . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . $this->application .
		 * '.php' ); } if (is_dir ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application )) { Folder::delete ( SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR
		 * . $this->application ); } $viewpath = SOURCE_PATH . 'template' .
		 * DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR;
		 * if (file_exists ( $viewpath . 'config.php' )) { $keyid = 'template|'
		 * . C ( 'template', 'name' ) . '|' . $this->application; $style_info =
		 * include $viewpath . 'config.php'; unset ( $style_info ['file_explan']
		 * [$keyid] ); @file_put_contents ( $viewpath . 'config.php', '<?php
		 * return ' . var_export ( $style_info, true ) . ';?>' ); }
		 */








		$admin_menu_db = Loader::model ( 'admin_menu_model' );
		$admin_menu_db->where(array ('application' => $this->application ))->delete (  );
		$this->db->where(array ('application' => $this->application ))->delete (  );
		return true;
	}

	/**
	 * 执行mysql.sql文件，创建数据表等
	 *
	 * @param string $sql
	 *        	sql语句
	 */
	private function sql_execute($sql) {
		file_put_contents ( '2.txt', $sql, FILE_APPEND );
		$sqls = $this->sql_split ( $sql );
		if (is_array ( $sqls )) {
			foreach ( $sqls as $sql ) {
				if (trim ( $sql ) != '') $this->db->execute ( $sql );
			}
		} else {
			$this->db->execute ( $sqls );
		}
		return true;
	}

	/**
	 * 处理sql语句，执行替换前缀都功能。
	 *
	 * @param string $sql
	 *        	原始的sql，将一些大众的部分替换成私有的
	 */
	private function sql_split($sql) {
		$dbcharset = C ( 'database', 'default' );
		if ($this->db->version () > '4.1' && $dbcharset) $sql = preg_replace ( "/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . $dbcharset, $sql );
		if ($this->db->get_prefix () != "yuncms_") $sql = str_replace ( "yuncms_", $this->db->get_prefix (), $sql );
		$sql = str_replace ( array ("\r",'2010-05-18' ), array ("\n",date ( 'Y-m-d' ) ), $sql );
		$ret = array ();
		$num = 0;
		$queriesarray = explode ( ";\n", trim ( $sql ) );
		unset ( $sql );
		foreach ( $queriesarray as $query ) {
			$ret [$num] = '';
			$queries = explode ( "\n", trim ( $query ) );
			$queries = array_filter ( $queries );
			foreach ( $queries as $query ) {
				$str1 = substr ( $query, 0, 1 );
				if ($str1 != '#' && $str1 != '-') $ret [$num] .= $query;
			}
			$num ++;
		}
		return $ret;
	}
}