<?php defined('IN_ADMIN') or exit('No permission resources.');?>
<?php include $this->admin_tpl('header', 'admin');?>
<div class="pad-lr-10">
<div class="table-list">

<div class="bk10"></div>
<form name="myform" id="myform" action="?app=member&controller=member_modelfield&action=sort" method="post" onsubmit="check();return false;">
    <table width="100%" cellspacing="0" >
        <thead>
            <tr>
			 <th width="70"><?php echo L('listorder')?></th>
            <th width="90"><?php echo L('fieldname')?></th>
			<th width="100"><?php echo L('cnames');?></th>
			<th width="100"><?php echo L('type');?></th>
            <th width="50"><?php echo L('must_input');?></th>
            <th width="50"><?php echo L('search');?></th>
            <th width="50"><?php echo L('listorder');?></th>
			<th width="50"><?php echo L('disabled');?></th>
			<th ><?php echo L('operations_manage');?></th>
            </tr>
        </thead>
    <tbody class="td-line">
	<?php
	foreach($datas as $r) {
	?>
    <tr>
		<td align='center' width='70'>
			<input name='listorders[<?php echo $r['fieldid']?>]' type='text' size='3' value='<?php echo $r['listorder']?>' class='input-text-c'>
		</td>
		<td width='90'><?php echo $r['field']?></td>
		<td width="100"><?php echo $r['name']?></td>
		<td width="100" align='center'><?php echo $r['formtype']?></td>
		<td width="50" align='center'>
			<?php echo $r['isbase'] ? L('icon_unlock') : L('icon_locked')?></td>
		<td width="50" align='center'>
			<?php echo $r['issearch'] ? L('icon_unlock') : L('icon_locked')?></td>
		<td width="50" align='center'>
			<?php echo $r['isorder'] ? L('icon_unlock') : L('icon_locked')?></td>
		<td width="50" align='center'>
			<?php echo $r['disabled'] ? L('icon_unlock') : L('icon_locked')?></td>
		<td align='center'>
			<a href="javascript:edit(<?php echo $r['modelid']?>, <?php echo $r['fieldid']?>, '<?php echo $r['name']?>')"><?php echo L('modify')?></a> |
			<?php if(!$r['disabled']) {?>
			<a href="?app=member&controller=member_modelfield&action=disable&disabled=1&fieldid=<?php echo $r['fieldid']?>&menuid=<?php echo $_GET['menuid']?>"><?php echo L('disable')?></a>
			<?php } else {?>
			<a href="?app=member&controller=member_modelfield&action=disable&disabled=0&fieldid=<?php echo $r['fieldid']?>&menuid=<?php echo $_GET['menuid']?>"><?php echo L('enable')?></a>
			<?php }?> |
			<a href="javascript:confirmurl('?app=member&controller=member_modelfield&action=delete&fieldid=<?php echo $r['fieldid']?>&menuid=<?php echo $_GET['menuid']?>','<?php echo L('sure_delete')?>')"><?php echo L('delete')?></a>
		</td>
	</tr>
	<?php } ?>
    </tbody>
    </table>

<div class="btn"><input type="submit" class="button" name="dosubmit" value="<?php echo L('sort')?>"/>
</div>
<div id="pages"><?php if(isset($pages)){echo $pages;}?></div>
</div>
</div>
</form>
<div id="PC__contentHeight" style="display:none">160</div>
<script language="JavaScript">
<!--
function edit(modelid, fieldid, name) {
	window.top.art.dialog.open('?app=member&controller=member_modelfield&action=edit&modelid='+modelid+'&fieldid='+fieldid,{
		title:'<?php echo L('edit').L('field')?>《'+name+'》',
		id:'edit',
		width:'700px',
		height:'500px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
	    cancel: function(){}
	});
}
function check() {
	if(myform.action == '?app=member&controller=member_model&action=delete') {
		var ids='';
		$("input[name='modelid[]']:checked").each(function(i, n){
			ids += $(n).val() + ',';
		});
		if(ids=='') {
			window.top.art.dialog.alert('<?php echo L('plsease_select').L('member_model')?>');
			return false;
		}
	}
	myform.submit();
}
//-->
</script>
</body>
</html>