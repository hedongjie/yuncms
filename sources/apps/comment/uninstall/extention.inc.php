<?php
defined('IN_YUNCMS') or exit('Access Denied');
defined('UNINSTALL') or exit('Access Denied');
$comment_table_db = Loader::model('comment_table_model');
$tablelist = $comment_table_db->select('', 'tableid');
foreach($tablelist as $k=>$v) {
	$comment_table_db->query("DROP TABLE IF EXISTS `".$comment_table_db->get_prefix()."comment_data_".$v['tableid']."`;");
}
?>