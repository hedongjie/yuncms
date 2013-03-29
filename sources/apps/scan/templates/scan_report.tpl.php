<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header', 'admin' );
?>
<div class="pad-lr-10">
	<div class="table-list">
		<table width="100%" cellspacing="0">
			<thead>
				<tr>
					<th align="left"><?php echo L('file_address')?></th>
					<th align="left"><?php echo L('function_of_characteristics')?></th>
					<th align="left"><?php echo L('characteristic_function')?></th>
					<th align="left"><?php echo L('code_number_of_features')?></th>
					<th align="left"><?php echo L('characteristic_key')?></th>
					<th align="left">Zend encoded</th>
					<th align="left"><?php echo L('operation')?></th>
				</tr>
			</thead>
			<tbody>
<?php
foreach ( $badfiles as $k => $v ) {
	?>
    <tr>
					<td align="left"><?php echo $k?></td>
					<td align="left"><?php if(isset($v['func'])){echo count($v['func']);}else{echo '0';}?></td>
					<td align="left"><?php

	if (isset ( $v ['func'] )) {
		foreach ( $v ['func'] as $keys => $vs ) {
			$d [$keys] = strtolower ( $vs [1] );
		}
		$d = array_unique ( $d );
		foreach ( $d as $vs ) {
			echo "<font color='red'>" . $vs . "</font>  ";
		}
	}
	?></td>
					<td align="left"><?php if(isset($v['code'])){echo count($v['code']);}else{echo '0';}?></td>
					<td align="left"><?php

	if (isset ( $v ['code'] )) {
		foreach ( $v ['code'] as $keys => $vs ) {
			$d [$keys] = strtolower ( $vs [1] );
		}
		$d = array_unique ( $d );
		foreach ( $d as $vs ) {
			echo "<font color='red'>" . htmlentities ( $vs ) . "</font>  ";
		}
	}
	?></td>
					<td align="left"><?php if(isset($v['zend'])){echo '<font color=\'red\'>Yes</font>';}else{echo 'No';}?></td>
					<td align="left"><a href="javascript:void(0)"
						onclick="view('<?php echo urlencode($k)?>')"><?php echo L('view')?></a>
						<a href="<?php echo SITE_URL,$k;?>" target="_blank"><?php echo L('access')?></a></td>
				</tr>
<?php
}

?>
</tbody>
		</table>

	</div>
</div>
<script type="text/javascript">
<!--
function view(url) {
	window.top.art.dialog.open('?app=scan&controller=index&action=public_view&url='+url,{
		title:'<?php echo L('view_code')?>',
		id:'edit',
		width:'700px',
		height:'500px'});
}
//-->
</script>
</body>
</html>