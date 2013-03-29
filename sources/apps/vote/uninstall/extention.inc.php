<?php
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'UNINSTALL' ) or exit ( 'Access Denied' );
$type_db = Loader::model ( 'type_model' );
$typeid = $type_db->delete ( array (
        'application' => 'vote'
) );
if (! $typeid)
    return FALSE;
?>