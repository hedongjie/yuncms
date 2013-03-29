<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = 1;
include $this->admin_tpl('header', 'admin');
?>
<div class="pad-lr-10">
<form name="myform" action="?app=vote&controller=vote&action=delete" method="post" onsubmit="checkuid();return false;">
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('subjectid[]');"></th>
			<th><?php echo L('title')?></th>
			<th width="40" align="center"><?php echo L('vote_num')?></th>
			<th width="68" align="center"><?php echo L('startdate')?></th>
			<th width="68" align="center"><?php echo L('enddate')?></th>
			<th width='68' align="center"><?php echo L('inputtime')?></th>
			<th width="180" align="center"><?php echo L('operations_manage')?></th>
		</tr>
	</thead>
<tbody>
<?php
if(is_array($infos)){
	foreach($infos as $info){
		?>
	<tr>
		<td align="center"><input type="checkbox"
			name="subjectid[]" value="<?php echo $info['subjectid']?>"></td>
		<td><a href="?app=vote&controller=index&action=show&show_type=1&subjectid=<?php echo $info['subjectid']?>" title="<?php echo L('check_vote')?>" target="_blank"><?php echo $info['subject'];?></a> <font color=red><?php if($info['enabled']==0)echo L('lock'); ?></font></td>
		<td align="center"><font color=blue><?php echo $info['votenumber']?></font> </td>
		<td align="center"><?php echo $info['fromdate'];?></td>
		<td align="center"><?php echo $info['todate'];?></td>
		<td align="center"><?php echo date("Y-m-d",$info['addtime']);?></td>
		<td align="center"><a href='###'
			onclick="statistics(<?php echo $info['subjectid']?>, '<?php echo new_addslashes($info['subject'])?>')"> <?php echo L('statistics')?></a>
		| <a href="###"
			onclick="edit(<?php echo $info['subjectid']?>, '<?php echo new_addslashes($info['subject'])?>')"
			title="<?php echo L('edit')?>"><?php echo L('edit')?></a> | <a href="javascript:call(<?php echo new_addslashes($info['subjectid'])?>);void(0);"><?php echo L('call_js_code')?></a> | <a
			href='?app=vote&controller=vote&action=delete&subjectid=<?php echo new_addslashes($info['subjectid'])?>'
			onClick="return confirm('<?php echo L('vote_confirm_del')?>')"><?php echo L('delete')?></a>
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
<input name="submit" type="submit" class="button"
	value="<?php echo L('remove_all_selected')?>"
	onClick="return confirm('<?php echo L('vote_confirm_del')?>')">&nbsp;&nbsp;</div>
<div id="pages"><?php echo $pages?></div>
</form>
</div>
<script type="text/javascript">
function edit(id, name) {
	window.top.art.dialog.open('?app=vote&controller=vote&action=edit&subjectid='+id,{
		title:'<?php echo L('edit')?> '+name+' ',
		id:'edit',
		width:'700px',
		height:'450px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
	    cancel: function(){}
	});
}
function statistics(id, name) {
	window.top.art.dialog.open('?app=vote&controller=vote&action=statistics&subjectid='+id,{
		title:'<?php echo L('statistics')?> '+name+' ',
		id:'edit',
		width:'700px',
		height:'350px',
		ok: function(iframeWin, topWin){}
	});
}

function call(id) {
	window.top.art.dialog.open('?app=vote&controller=vote&action=public_call&subjectid='+id,{
		title:'<?php echo L('vote')?><?php echo L('linkage_calling_code','','admin');?>',
		id:'call',
		width:'600px',
		height:'470px',
		yesFn: function(iframeWin, topWin){}
	});
}

function checkuid() {
	var ids='';
	$("input[name='subjectid[]']:checked").each(function(i, n){
		ids += $(n).val() + ',';
	});
	if(ids=='') {
		window.top.art.dialog.alert('<?php echo L('before_select_operation')?>');
		return false;
	} else {
		myform.submit();
	}
}

</script>
</body>
</html>
