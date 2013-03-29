<?php
/**
 *
 * @author		YUNCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, NewsTeng, Inc.
 * @license	http://www.yuncms.net/about/license
 * @link		http://www.yuncms.net
 * $Id: delete.sql.php 71 2012-11-05 12:51:29Z xutongle $
 */
defined('IN_YUNCMS') or exit('No permission resources.');
$this->db->execute("ALTER TABLE `$tablename` DROP `$field`");