<?php
function groupid($field, $value, $fieldinfo) {
	extract ( string2array ( $fieldinfo ['setting'] ) );
	$grouplist = S ( 'member/grouplist' );
	foreach ( $grouplist as $_key => $_value ) {
		$data [$_key] = $_value ['name'];
	}
	return '<input type="hidden" name="info[' . $field . ']" value="1">' . Form::checkbox ( $data, $value, 'name="' . $field . '[]" id="' . $field . '"', '', '120' );
}
?>