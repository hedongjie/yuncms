<?php
defined('IN_YUNCMS') or exit('Access Denied');
defined('UNINSTALL') or exit('Access Denied');
$form_db = Loader::model('model_model');
$form_field_db = Loader::model('model_field_model');
$result = $form_db->select(array(), 'tablename');
if (is_array($result)) {
	foreach ($result as $r) {
		$tablename = 'form_'.$r['tablename'];
		$form_field_db->change_table($tablename);
		$form_field_db->query('DROP TABLE IF EXISTS `yuncms_'.$tablename.'`;');
	}
}
?>