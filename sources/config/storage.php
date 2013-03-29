<?php
/**
 * 数据存储配置文件
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-1-9
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: storage.php 2 2013-01-14 07:14:05Z xutongle $
 */
return array (
		'default' => array (
				'driver' => 'Local',
				'path' => '',// 附件保存路径
				'url' => ''
				),
		'kvdb' => array (
				'driver' => 'Kvdb',
				'path' => '',// 附件保存路径
				'url' => ''
		),
		'mongodb' => array (
				'driver' => 'Mongodb',
				'path' => '',// 附件保存路径
				'url' => ''
		)
		);