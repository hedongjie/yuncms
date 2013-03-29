<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<div class="pad-lr-10">
<div class="table-list">
    <table width="100%" cellspacing="0" >
        <thead>
            <tr>
			 <th width="100">Modelid</th>
            <th width="100"><?php echo L('model_name');?></th>
			<th width="100"><?php echo L('tablename');?></th>
            <th ><?php echo L('description');?></th>
            <th width="100"><?php echo L('status');?></th>
            <th width="100"><?php echo L('items');?></th>
			<th width="230"><?php echo L('operations_manage');?></th>
            </tr>
        </thead>
    <tbody>
	<?php foreach($datas as $r) {$tablename = $r['name'];?>
    <tr>
		<td align='center'><?php echo $r['modelid']?></td>
		<td align='center'><?php echo $tablename?></td>
		<td align='center'><?php echo $r['tablename']?></td>
		<td align='center'>&nbsp;<?php echo $r['description']?></td>
		<td align='center'><?php echo $r['disabled'] ? L('icon_locked') : L('icon_unlock')?></td>
		<td align='center'><?php echo $r['items']?></td>
		<td align='center'><a href="<?php echo U('content/model_field/init',array('modelid'=>$r['modelid']));?>"><?php echo L('field_manage');?></a> |
		<a href="javascript:edit('<?php echo $r['modelid']?>','<?php echo addslashes($tablename);?>')"><?php echo L('edit');?></a> |
		<a href="<?php echo U('content/model/disabled',array('modelid'=>$r['modelid']));?>"><?php echo $r['disabled'] ? L('field_enabled') : L('field_disabled');?></a> |
		<a href="javascript:;" onclick="model_delete(this,'<?php echo $r['modelid']?>','<?php echo L('confirm_delete_model',array('message'=>addslashes($tablename)));?>',<?php echo $r['items']?>)"><?php echo L('delete')?></a> |
		<a href="<?php echo U('content/model/export',array('modelid'=>$r['modelid']));?>"><?php echo L('export');?></a></td>
	</tr>
	<?php } ?>
    </tbody>
    </table>
   <div id="pages"><?php echo $pages;?>
  </div>
</div>
<script type="text/javascript">
<!--
window.top.$('#display_center_id').css('display','none');
function edit(id, name) {
	window.top.art.dialog.open('?app=content&controller=model&action=edit&modelid='+id,{
		title:'<?php echo L('edit_model');?>《'+name+'》',
		id:'edit',
		width:'580px',
		height:'420px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
	    cancel: function(){}
	});
}
function model_delete(obj,id,name,items){
	if(items) {
		alert('<?php echo L('model_does_not_allow_delete');?>');
		return false;
	}
	window.top.art.dialog.confirm(name, function(topWin){
		$.get('?app=content&controller=model&action=delete&modelid='+id,function(data){
			if(data) {
				$(obj).parent().parent().fadeOut("slow");
			}
		})
	}, function(){

	});

};
//-->
</script>
</body>
</html>