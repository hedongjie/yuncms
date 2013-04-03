<?php
defined('IN_YUNCMS') or exit('No permission resources.');
defined('INSTALL') or exit('Access Denied');
$parentid = $admin_menu_db->insert(array('name'=>'digg', 'parentid'=>34, 'application'=>'digg', 'controller'=>'range', 'action'=>'init', 'data'=>'', 'listorder'=>0, 'display'=>'1'), true);
$language = array('digg'=>'顶一下');