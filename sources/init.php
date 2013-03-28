<?php
/**
 * 系统入口类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: init.php 2 2013-01-14 07:14:05Z xutongle $
 */
define('IN_YUNCMS', true);
defined ( 'BASE_PATH' ) or define ( 'BASE_PATH', dirname ( $_SERVER ['SCRIPT_FILENAME'] ) . DIRECTORY_SEPARATOR );
require_once 'framework/Framework.php';
define ( 'JS_PATH', C ( 'system', 'js_path' ) ); // CDN JS路径
define ( 'CSS_PATH', C ( 'system', 'css_path' ) ); // CDN CSS路径
define ( 'IMG_PATH', C ( 'system', 'img_path' ) ); // CDN IMG路径
define ( 'SKIN_PATH', C ( 'system', 'skin_path', 'statics/skins/' ) );//CDN IMG路径