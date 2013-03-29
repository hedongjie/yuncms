<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = 1;
include $this->admin_tpl('header', 'admin');
?>
<div class="pad-lr-10">
<form name="myform" id="myform" action="?app=link&controller=link&action=check_register" method="post" onsubmit="checkuid();return false;">
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('linkid[]');"></th>
 			<th><?php echo L('link_name')?></th>
 			<th width="20%" align="center"><?php echo L('url')?></th>
			<th width="12%" align="center"><?php echo L('logo')?></th>
			<th width="20%" align="center"><?php echo L('operations_manage')?></th>
		</tr>
	</thead>
<tbody>
<?php
if(is_array($infos)){
	foreach($infos as $info){
		?>
	<tr>
		<td align="center" width="35"><input type="checkbox"
			name="linkid[]" value="<?php echo $info['linkid']?>"></td>
 		<td><a href="<?php echo $info['url'];?>" title="<?php echo L('go_website')?>" target="_blank"><?php echo $info['name']?></a></td>
		<th width="20%" align="center"><a href="<?php echo $info['url']?>" target="_blank"><?php echo $info['url']?></a></th>
		<td align="center" width="12%"><?php if($info['linktype']==1){?><img src="<?php echo $info['logo'];?>" width=83 height=31><?php }?></td>
		 <td align="center" width="20%">
		 <a href="###" onclick="edit(<?php echo $info['linkid']?>, '<?php echo new_addslashes($info['name'])?>')" title="<?php echo L('edit')?>"><?php echo L('edit')?></a> |
		<a href='<?php echo art_confirm(L('confirm', array('message' => new_addslashes($info['name']))),'?app=link&controller=link&action=delete&linkid='.$info['linkid'])?>'><?php echo L('delete')?></a>
		</td>
	</tr>
	<?php
	}
}
?>
</tbody>
</table>
<div class="btn"><a href="#"
	onClick="javascript:$('input[type=checkbox]').attr('checked', true)"><?php echo L('selected_all')?></a>/<a
	href="#"
	onClick="javascript:$('input[type=checkbox]').attr('checked', false)"><?php echo L('cancel')?></a>
<input name="dosubmit" type="submit" class="button"
	value="<?php echo L('pass_check')?>"
	onClick="return confirm('<?php echo L('pass_or_not')?>')">&nbsp;&nbsp;<input type="submit" class="button" name="dosubmit" onclick="document.myform.action='?app=link&controller=link&action=delete'" value="<?php echo L('delete')?>"/> </div>
<div id="pages"><?php echo $this->pages?></div>
</form>
</div>
</body>
</html>
<script type="text/javascript">
function edit(id, name) {
	window.top.art.dialog.open('?app=link&controller=link&action=edit&linkid='+id,{
		title:'<?php echo L('edit')?> '+name+' ',
		id:'edit',
		width:'700px',
		height:'450px',
		yesFn: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
	    noFn: function(){}
	});
}
function checkuid() {
	var ids='';
	$("input[name='linkid[]']:checked").each(function(i, n){
		ids += $(n).val() + ',';
	});
	if(ids=='') {
		window.top.art.dialog.alert("<?php echo L('before_select_operations')?>");
		return false;
	} else {
		myform.submit();
	}
}
</script>
