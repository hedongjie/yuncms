<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );

$this->db->execute ( "ALTER TABLE `$tablename` DROP `$field`" );
?>