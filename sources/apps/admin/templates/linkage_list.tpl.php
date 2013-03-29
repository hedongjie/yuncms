<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<div class="pad_10">
	<div class="explain-col">
<?php echo L('linkage_tips');?>
</div>
	<div class="bk10"></div>
		<div class="table-list">
			<table width="100%" cellspacing="0">
				<thead>
					<tr>
						<th width="10%">ID</th>
						<th width="20%" align="left"><?php echo L('linkage_name')?></th>
						<th width="30%" align="left"><?php echo L('linkage_desc')?></th>
						<th width="20%"><?php echo L('linkage_calling_code')?></th>
						<th width="20%"><?php echo L('operations_manage')?></th>
					</tr>
				</thead>
				<tbody>
		<?php
		if (is_array ( $infos )) {
			foreach ( $infos as $info ) {
				?>
		<tr>
						<td width="10%" align="center"><?php echo $info['linkageid']?></td>
						<td width="20%"><?php echo $info['name']?></td>
						<td width="30%"><?php echo $info['description']?></td>
						<td width="20%" class="text-c"><input type="text"
							value="{menu_linkage(<?php echo $info['linkageid']?>,'L_<?php echo $info['linkageid']?>')}"
							style="width: 200px;"></td>
						<td width="20%" class="text-c"><a
							href="?app=admin&controller=linkage&action=public_manage_submenu&keyid=<?php echo $info['linkageid']?>&menuid=<?php echo isset($_GET['menuid']) ? $_GET['menuid'] : ''?>"><?php echo L('linkage_manage_submenu')?></a>
							| <a href="javascript:void(0);"
							onclick="edit('<?php echo $info['linkageid']?>','<?php echo new_addslashes($info['name'])?>')"><?php echo L('edit')?></a>
							| <a
							href="<?php echo art_confirm(L('linkage_is_del'), '?app=admin&controller=linkage&action=delete&linkageid='.$info['linkageid'])?>"><?php echo L('delete')?></a>
							| <a
							href="?app=admin&controller=linkage&action=public_cache&linkageid=<?php echo $info['linkageid']?>"><?php echo L('update_backup')?></a></td>
					</tr>
		<?php
			}
		}
		?>
</tbody>
			</table>
		</div>

</div>
</form>
<script type="text/javascript">
<!--
function edit(id, name) {
	window.top.art.dialog.open('?app=admin&controller=linkage&action=edit&linkageid='+id,{
		title:name,
		id:'edit',
		width:'500px',
		height:'200px',
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
</body>
</html>