<?php
/**
 * 修改模型字段的SQL语句
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * $Id: edit.sql.php 71 2012-11-05 12:51:29Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
$defaultvalue = isset ( $_POST ['setting'] ['defaultvalue'] ) ? $_POST ['setting'] ['defaultvalue'] : '';
$minnumber = isset ( $_POST ['setting'] ['minnumber'] ) ? $_POST ['setting'] ['minnumber'] : 1;
$decimaldigits = isset ( $_POST ['setting'] ['decimaldigits'] ) ? $_POST ['setting'] ['decimaldigits'] : '';

switch ($field_type) {
    case 'varchar' :
        if (! $maxlength)
            $maxlength = 255;
        $maxlength = min ( $maxlength, 255 );
        $fieldtype = isset ( $issystem ) ? 'CHAR' : 'VARCHAR';
        $sql = "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` $fieldtype( $maxlength ) NOT NULL DEFAULT '$defaultvalue'";
        $this->db->execute ( $sql );
        break;

    case 'tinyint' :
        $minnumber = intval ( $minnumber );
        $defaultvalue = intval ( $defaultvalue );
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` TINYINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '$defaultvalue'" );
        break;

    case 'number' :
        $minnumber = intval ( $minnumber );
        $defaultvalue = $decimaldigits == 0 ? intval ( $defaultvalue ) : floatval ( $defaultvalue );
        $sql = "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` " . ($decimaldigits == 0 ? 'INT' : 'FLOAT') . " " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '$defaultvalue'";
        $this->db->execute ( $sql );
        break;

    case 'smallint' :
        $minnumber = intval ( $minnumber );
        $defaultvalue = intval ( $defaultvalue );
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` SMALLINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '$defaultvalue'" );
        break;

    case 'mediumint' :
        $minnumber = intval ( $minnumber );
        $defaultvalue = intval ( $defaultvalue );
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` MEDIUMINT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '$defaultvalue'" );
        break;

    case 'int' :
        $minnumber = intval ( $minnumber );
        $defaultvalue = intval ( $defaultvalue );
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` INT " . ($minnumber >= 0 ? 'UNSIGNED' : '') . " NOT NULL DEFAULT '$defaultvalue'" );
        break;

    case 'mediumtext' :
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` MEDIUMTEXT NOT NULL" );
        break;

    case 'text' :
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` TEXT NOT NULL" );
        break;

    case 'date' :
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` DATE NULL" );
        break;

    case 'datetime' :
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` DATETIME NULL" );
        break;

    case 'timestamp' :
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `$field` TIMESTAMP NOT NULL" );
        break;
    // 特殊自定义字段
    case 'pages' :

        break;
    case 'readpoint' :
        $defaultvalue = intval ( $defaultvalue );
        $this->db->execute ( "ALTER TABLE `$tablename` CHANGE `$oldfield` `readpoint` smallint(5) unsigned NOT NULL default '$defaultvalue'" );
        break;

}