<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
$show_dialog = 1;
include $this->admin_tpl ( 'header', 'admin' );
?>

<div class="pad-lr-10">
  <form name="myform" id="myform"
		action="<?php echo U('admin/keylink/delete');?>" method="post"
		onsubmit="checkuid();return false;">
    <div class="table-list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <th width="35" align="center"><input type="checkbox" value=""
							id="check_box" onclick="selectall('keylinkid[]');"></th>
            <th width="30%"><?php echo L('keyword_name')?></th>
            <th><?php echo L('link_url')?></th>
            <th width="120"><?php echo L('operations_manage')?></th>
          </tr>
        </thead>
        <tbody>
          <?php
if (is_array ( $infos )) {
	foreach ( $infos as $info ) {
		?>
          <tr>
            <td align="center"><input type="checkbox" name="keylinkid[]"
							value="<?php echo $info['keylinkid']?>"></td>
            <td width="30%" align="left"><?php echo $info['word']?></td>
            <td align="center"><?php echo $info['url']?></td>
            <td align="center"><a
							href="javascript:edit(<?php echo $info['keylinkid']?>, '<?php echo new_addslashes($info['word'])?>')"><?php echo L('edit')?></a> | <a
							href="<?php echo art_confirm(L('keylink_confirm_del'),'?app=admin&controller=keylink&action=delete&keylinkid='.$info['keylinkid']);?>"><?php echo L('delete')?></a></td>
          </tr>
          <?php
	}
}
?>
        </tbody>
      </table>
      <div class="btn"> <a href="#"
					onClick="javascript:$('input[type=checkbox]').attr('checked', true)"><?php echo L('selected_all')?></a>/<a
					href="#"
					onClick="javascript:$('input[type=checkbox]').attr('checked', false)"><?php echo L('cancel')?></a>
        <input type="submit" name="submit" class="button"
					value="<?php echo L('remove_all_selected')?>"
					onClick="return confirm('<?php echo L('badword_confom_del')?>')" />
      </div>
      <div id="pages"><?php echo $pages?></div>
    </div>
  </form>
</div>
</body></html><script type="text/javascript">
function edit(id, name) {
	window.top.art.dialog.open('?app=admin&controller=keylink&action=edit&keylinkid='+id,{
		title:'<?php echo L('keylink_edit')?> '+name+' ',
		id:'edit',
		width:'450px',
		height:'130px',
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
	$("input[name='keylinkid[]']:checked").each(function(i, n){
		ids += $(n).val() + ',';
	});
	if(ids=='') {
		window.top.art.dialog.alert('<?php echo L('badword_pleasechose')?>');
		return false;
	} else {
		myform.submit();
	}
}
</script>