<?php
/**
 * 网站入口
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: index.php 2 2013-01-14 07:14:05Z xutongle $
 */
if (! function_exists ( 'saeAutoLoader' )) {
	define ( 'SAE_MYSQL_HOST_M', 'localhost' );
	define ( 'SAE_MYSQL_PORT', 3306 );
	define ( 'SAE_MYSQL_DB', 'yuncms' );
	define ( 'SAE_MYSQL_USER', 'root' );
	define ( 'SAE_MYSQL_PASS', '123456' );
}
require_once 'sources/init.php';
Core::run ();