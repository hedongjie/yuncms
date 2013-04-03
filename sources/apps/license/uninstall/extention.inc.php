<?php
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'UNINSTALL' ) or exit ( 'Access Denied' );
$type_db = Loader::model ( 'type_model' );
$typeid = $type_db->delete ( array ('application' => 'license' ) );
if (! $typeid) return FALSE;
?>