<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<div class="pad_10">
<div class="table-list">
    <table width="100%" cellspacing="0" >
        <thead>
			<tr>
				<th width="5%">ID</th>
				<th width="20%" align="left"><?php echo L('workflow_name');?></th>
				<th width="20%"><?php echo L('steps');?></th>
				<th width="10%"><?php echo L('workflow_diagram');?></th>
				<th width="*"><?php echo L('description');?></th>
				<th width="30%"><?php echo L('operations_manage');?></th>
			</tr>
        </thead>
    <tbody>


<?php
$steps[1] = L('steps_1');
$steps[2] = L('steps_2');
$steps[3] = L('steps_3');
$steps[4] = L('steps_4');
foreach($datas as $r) {
?>
<tr>
<td align="center"><?php echo $r['workflowid']?></td>
<td ><?php echo $r['workname']?></td>
<td align="center"><?php echo $steps[$r['steps']]?></td>
<td align="center"><a href="javascript:view('<?php echo $r['workflowid']?>','<?php echo $r['workname']?>')"><?php echo L('onclick_view');?></a></td>
<td ><?php echo $r['description']?></td>
<td align="center"><a href="javascript:edit('<?php echo $r['workflowid']?>','<?php echo $r['workname']?>')"><?php echo L('edit');?></a> | <a href="javascript:;" onclick="data_delete(this,'<?php echo $r['workflowid']?>','<?php echo L('confirm',array('message'=>$r['workname']));?>')"><?php echo L('delete')?></a> </td>
</tr>
<?php } ?>
	</tbody>
    </table>

 </div>
</div>
<div id="pages"><?php echo isset($pages) ? $pages:'';?></div>
</div>
<script type="text/javascript">
<!--
window.top.$('#display_center_id').css('display','none');
function edit(id, name) {
	window.top.art.dialog.open('?app=content&controller=workflow&action=edit&workflowid='+id,{
		title:'<?php echo L('edit_workflow');?>《'+name+'》',
		id:'edit',
		width:'680px',
		height:'500px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
		cancel: function(){}
	});
}
function view(id, name) {
	window.top.art.dialog.open('?app=content&controller=workflow&action=view&workflowid='+id,{
		title:'<?php echo L('workflow_diagram');?>《'+name+'》',
		id:'edit',
		width:'580px',
		height:'300px',
		ok: function(iframeWin, topWin){

		}
	});
}
function data_delete(obj,id,name){
	window.top.art.dialog.confirm(name,
		function(topWin){
			$.get('?app=content&controller=workflow&action=delete&workflowid='+id,function(data){
			if(data) {
				$(obj).parent().parent().fadeOut("slow");
			}
		})
	},
	function(){});
};
//-->
</script>
</body>
</html>
