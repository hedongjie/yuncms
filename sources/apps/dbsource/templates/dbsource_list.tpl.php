<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<div class="pad_10">
<div class="table-list">
    <table width="100%" cellspacing="0">
        <thead>
		<tr>
		<th width="80">Id</th>
		<th><?php echo L('dbsource_name')?></th>
		<th><?php echo L('server_address')?></th>
		<th width="150"><?php echo L('operations_manage')?></th>
		</tr>
        </thead>
        <tbody>
<?php
if(is_array($list)){
	foreach($list as $v){
?>
<tr>
<td width="80" align="center"><?php echo $v['id']?></td>
<td align="center"><?php echo $v['name']?></td>
<td align="center"><?php echo $v['host']?></td>
<td align="center"><a href="javascript:edit(<?php echo $v['id']?>, '<?php echo htmlspecialchars(new_addslashes($v['name']))?>')"><?php echo L('edit')?></a> | <a href="<?php echo art_confirm(htmlspecialchars(new_addslashes(L('confirm', array('message'=>$v['name'])))), '?app=dbsource&controller=dbsource_admin&action=del&id='.$v['id']);?>" ><?php echo L('delete')?></a></td>
</tr>
<?php
	}}
?>
</tbody>
</table>
</div>
</div>
<div id="pages"><?php echo $pages?></div>
<script type="text/javascript">
<!--
function edit(id, name) {
	window.top.art.dialog.open('?app=dbsource&controller=dbsource_admin&action=edit&id='+id,{
		title:'<?php echo L('edit_dbsource')?>《'+name+'》',
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
//-->
</script>
</body>
</html>