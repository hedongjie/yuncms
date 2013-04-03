<?php
defined('IN_YUNCMS') or exit('Access Denied');
defined('UNINSTALL') or exit('Access Denied');
$form_db = Loader::model('model_model');
$mode_info = $form_db->get_one(array('type'=>4));
$modelid = $mode_info['modelid'];
$form_db->delete(array('modelid'=>$modelid));
Loader::model('model_field_model')->delete(array('modelid'=>$modelid));
?>