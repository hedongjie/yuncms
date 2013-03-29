<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
$show_dialog = 1;
include $this->admin_tpl ( 'header', 'admin' );
?>
<div class="pad-lr-10">
	<form name="myform" id="myform"
		action="<?php echo U('admin/player/delete');?>" method="post"
		onsubmit="checkuid();return false;">
		<div class="table-list">
			<table width="100%" cellspacing="0">
				<thead>
					<tr>
						<th width="35" align="center"><input type="checkbox" value=""
							id="check_box" onclick="selectall('playerid[]');"></th>
						<th width="30%">ID</th>
						<th width="30%"><?php echo L('player_name');?></th>
						<th><?php echo L('status');?></th>
						<th width="120"><?php echo L('operations_manage')?></th>
					</tr>
				</thead>
				<tbody>
 <?php
if (is_array ( $infos )) {
    foreach ( $infos as $info ) {
        ?>
    <tr>
						<td align="center"><input type="checkbox" name="playerid[]"
							value="<?php echo $info['playerid']?>"></td>

						<td align="center"><?php echo $info['playerid']?></td>

						<td width="30%" align="center"><?php echo $info['subject']?> </td>
						<td align="center"><?php echo $info['disabled']?'禁用中':'启用中';?></td>
						<td align="center"><a
						href="javascript:edit('<?php echo $info ['playerid']?>')"><?php echo L('edit');?></a> <a
							href="javascript:confirmurl('?app=admin&controller=player&action=delete&playerid=<?php echo $info['playerid']?>', '<?php echo L('confirm', array('message' => L('selected')))?>')"><?php echo L('delete')?></a>
							<a
							href="javascript:confirmurl('?app=admin&controller=player&action=changestatus&playerid=<?php echo $info['playerid']?>&status=<?php echo $info['disabled'];?>', '确定改变播放器使用状态?')"><?php echo $info['disabled']?'启用':'禁用';?></a>
						</td>
					</tr>
<?php
    }
}
?></tbody>
			</table>
			<div class="btn">
				<a href="#"
					onClick="javascript:$('input[type=checkbox]').attr('checked', true)"><?php echo L('selected_all')?></a>/<a
					href="#"
					onClick="javascript:$('input[type=checkbox]').attr('checked', false)"><?php echo L('cancel')?></a>

				<input type="submit" name="submit" class="button"
					value="<?php echo L('remove_all_selected')?>"
					onClick="return confirm('<?php echo L('confirm', array('message' => L('selected')))?>')" />

			</div>
			<div id="pages"><?php echo $pages?></div>

		</div>

	</form>
</div>
<script type="text/javascript">
<!--
function edit(id) {
	window.top.art.dialog.open('?app=admin&controller=player&action=edit&playerid='+id,{
		title:'<?php echo L('edit_player');?>《'+id+'》',
		id:'edit',
		width:'550px',
		height:'400px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
		cancel: function(){}
	});
}
function checkuid() {
	var ids='';
	$("input[name='playerid[]']:checked").each(function(i, n){
		ids += $(n).val() + ',';
	});
	if(ids=='') {
		window.top.art.dialog.tips('<?php echo L('please_secect_player');?>', 1);
		return false;
	} else {
		myform.submit();
	}
}
//-->
</script>
</body>
</html>
