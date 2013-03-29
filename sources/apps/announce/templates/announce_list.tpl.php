<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
$show_dialog = 1;
include $this->admin_tpl ( 'header', 'admin' );
?>
<div class="pad-lr-10">
	<form name="myform"
		action="?app=announce&controller=admin_announce&action=listorder"
		method="post">
		<div class="table-list">
			<table width="100%" cellspacing="0">
				<thead>
					<tr>
						<th width="35" align="center"><input type="checkbox" value=""
							id="check_box" onclick="selectall('aid[]');"></th>
						<th align="center"><?php echo L('title')?></th>
						<th width="68" align="center"><?php echo L('startdate')?></th>
						<th width='68' align="center"><?php echo L('enddate')?></th>
						<th width='68' align="center"><?php echo L('inputer')?></th>
						<th width="50" align="center"><?php echo L('hits')?></th>
						<th width="120" align="center"><?php echo L('inputtime')?></th>
						<th width="69" align="center"><?php echo L('operations_manage')?></th>
					</tr>
				</thead>
				<tbody>
 <?php
	if (is_array ( $data )) {
		foreach ( $data as $announce ) {
			?>
	<tr>
						<td align="center"><input type="checkbox" name="aid[]"
							value="<?php echo $announce['aid']?>"></td>
						<td><?php echo $announce['title']?></td>
						<td align="center"><?php echo $announce['starttime']?></td>
						<td align="center"><?php echo $announce['endtime']?></td>
						<td align="center"><?php echo $announce['username']?></td>
						<td align="center"><?php echo $announce['hits']?></td>
						<td align="center"><?php echo date('Y-m-d H:i:s', $announce['addtime'])?></td>
						<td align="center">
	<?php if ($_GET['s']==1) {?><a
							href="?app=announce&controller=index&action=show&aid=<?php echo $announce['aid']?>"
							title="<?php echo L('preview')?>" target="_blank"><?php }?><?php echo L('index')?><?php if ($_GET['s']==1) {?></a><?php }?> |
	<a
							href="javascript:edit('<?php echo $announce['aid']?>', '<?php echo safe_replace($announce['title'])?>');void(0);"><?php echo L('edit')?></a>
						</td>
					</tr>
<?php
		}
	}
	?>
</tbody>
			</table>

			<div class="btn">
				<label for="check_box"><?php echo L('selected_all')?>/<?php echo L('cancel')?></label>
        <?php if($_GET['s']==1) {?><input name='submit' type='submit'
					class="button" value='<?php echo L('cancel_all_selected')?>'
					onClick="document.myform.action='?app=announce&controller=admin_announce&action=public_approval&passed=0'"><?php } elseif($_GET['s']==2) {?><input
					name='submit' type='submit' class="button"
					value='<?php echo L('pass_all_selected')?>'
					onClick="document.myform.action='?app=announce&controller=admin_announce&action=public_approval&passed=1'"><?php }?>&nbsp;&nbsp;
		<input name="submit" type="submit" class="button"
					value="<?php echo L('remove_all_selected')?>"
					onClick="document.myform.action='?app=announce&controller=admin_announce&action=delete';return confirm('<?php echo L('affirm_delete')?>')">&nbsp;&nbsp;
			</div>
		</div>
		<div id="pages"><?php echo $this->db->pages;?></div>
	</form>
</div>
</body>
</html>
<script type="text/javascript">
function edit(id, title) {
	window.top.art.dialog.open('?app=announce&controller=admin_announce&action=edit&aid='+id ,{
		title:'<?php echo L('edit_announce')?>--'+title,
		id:'edit',
		width:'850px',
		height:'500px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
		cancel: function(){}
	});
}
</script>