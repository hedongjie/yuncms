<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<form name="myform"
	action="?app=admin&controller=position&action=listorder" method="post">
	<div class="pad_10">
		<div class="table-list">
			<table width="100%" cellspacing="0">
				<thead>
					<tr>
						<th width="10%" align="left"><?php echo L('listorder');?></th>
						<th width="5%" align="left">ID</th>
						<th width="15%"><?php echo L('posid_name');?></th>
						<th width="15%"><?php echo L('posid_catid');?></th>
						<th width="15%"><?php echo L('posid_modelid');?></th>
						<th width="20%"><?php echo L('posid_operation');?></th>
					</tr>
				</thead>
				<tbody>
 <?php
	if (is_array ( $infos )) {
		foreach ( $infos as $info ) {
			?>
	<tr>
						<td width="10%"><input
							name='listorders[<?php echo $info['posid']?>]' type='text'
							size='2' value='<?php echo $info['listorder']?>'
							class="input-text-c"></td>
						<td width="5%" align="left"><?php echo $info['posid']?></td>
						<td width="15%" align="center"><?php echo $info['name']?></td>
						<td width="15%" align="center"><?php echo $info['catid'] ? $category[$info['catid']]['catname'] : L('posid_all')?></td>
						<td width="15%" align="center"><?php echo $info['modelid'] ? $model[$info['modelid']]['name'] : L('posid_all')?></td>
						<td width="20%" align="center"><a
							href="?app=admin&controller=position&action=public_item&posid=<?php echo $info['posid']?>&menuid=<?php echo $_GET['menuid']?>"><?php echo L('posid_item_manage')?></a>
							| <a
							href="javascript:edit(<?php echo $info['posid']?>, '<?php echo new_addslashes($info['name'])?>')"><?php echo L('edit')?></a> |
	<?php if($_SESSION['roleid'] != 1) {?>
	<font color="#ccc"><?php echo L('delete')?></font>
	<?php } else {?>
	<a
							href="<?php echo art_confirm(L('posid_del_cofirm'), '?app=admin&controller=position&action=delete&posid='.$info['posid']);?>"><?php echo L('delete')?></a>
	<?php } ?>
	</td>
					</tr>
<?php
		}
	}
	?>
    </tbody>
			</table>

			<div class="btn">
				<input type="submit" class="button" name="dosubmit"
					value="<?php echo L('listorder')?>" />
			</div>
		</div>

		<div id="pages"> <?php echo $pages?></div>
	</div>
	</div>
</form>
</body>
</html> <script type="text/javascript">
<!--
	window.top.$('#display_center_id').css('display','none');
	function edit(id, name) {
	window.top.art.dialog.open('?app=admin&controller=position&action=edit&posid='+id ,{
		title:'<?php echo L('edit')?>--'+name,
		id:'edit',
		width:'500px',
		height:'360px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
		cancel: function(){}
	});
}
//-->
</script>