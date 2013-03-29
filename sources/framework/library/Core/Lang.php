<?php
/**
 * 核心语言类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Lang.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Core_Lang {

	protected static $instance = null;

	protected $app_lang = array ();

	public static function &instance() {
		if (null === self::$instance) {
			self::$instance = new self ();
		}
		return self::$instance;
	}

	public function __construct() {
		if (! defined ( 'LANG' )) define ( 'LANG', C ( 'config', 'lang', 'zh-cn' ) );
	}

	/**
	 * 加载语言包
	 */
	public function load($language = 'NO_LANG', $pars = array(), $applications = '') {
		static $LANG = array ();
		if (! $LANG) {
			// 加载框架语言包
			require_once FW_PATH . 'language' . DIRECTORY_SEPARATOR . LANG . '.php';
			require_once SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . LANG . DIRECTORY_SEPARATOR . 'system.php';
			if (defined ( 'IN_ADMIN' )) require_once SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . LANG . DIRECTORY_SEPARATOR . 'admin_menu.php';
			if (defined ( 'APP' ) && file_exists ( SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . LANG . DIRECTORY_SEPARATOR . APP . '.php' )) require SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . LANG . DIRECTORY_SEPARATOR . APP . '.php';
		}
		if (! empty ( $applications )) {
			$applications = explode ( ',', $applications );
			foreach ( $applications as $app ) {
				if (! isset ( $this->app_lang [$app] )) {
					require SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . LANG . DIRECTORY_SEPARATOR . $app . '.php';
					$this->app_lang [$app] = true;
				}
			}
		}
		if (! array_key_exists ( $language, $LANG )) {
			$return = $LANG ['NO_LANG'] . '[' . $language . ']';
			log_message ( 'error', APP . ':' . $return );
			return $return;
		} else {
			$language = $LANG [$language];
			if ($pars) {
				foreach ( $pars as $_k => $_v ) {
					$language = str_replace ( '{' . $_k . '}', $_v, $language );
				}
			}
			return $language;
		}
	}
}