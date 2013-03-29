<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<div class="pad_10">
	<div class="table-list">
		<form name="myform"
			action="?app=admin&controller=role&action=listorder" method="post">
			<table width="100%" cellspacing="0">
				<thead>
					<tr>
						<th width="5%"><?php echo L('userid')?></th>
						<th width="10%" align="left"><?php echo L('username')?></th>
						<th width="10%" align="left"><?php echo L('userinrole')?></th>
						<th width="10%" align="left"><?php echo L('lastloginip')?></th>
						<th width="20%" align="left"><?php echo L('lastlogintime')?></th>
						<th width="15%" align="left"><?php echo L('email')?></th>
						<th width="10%"><?php echo L('realname')?></th>
						<th width="10%"><?php echo L('mobile')?></th>
						<th width="15%"><?php echo L('operations_manage')?></th>
					</tr>
				</thead>
				<tbody>
<?php $admin_founders = explode(',',C('system','admin_founders'));?>
<?php

if (is_array ( $infos )) {
	foreach ( $infos as $info ) {
		?>
<tr>
						<td width="5%" align="center"><?php echo $info['userid']?></td>
						<td width="10%"><?php echo $info['username']?></td>
						<td width="10%"><?php echo $roles[$info['roleid']]?></td>
						<td width="10%"><?php echo $info['lastloginip']?></td>
						<td width="15%"><?php echo $info['lastlogintime'] ? date('Y-m-d H:i:s',$info['lastlogintime']) : ''?></td>
						<td width="15%"><?php echo $info['email']?></td>
						<td width="10%" align="center"><?php echo $info['realname']?></td>
						<td width="10%" ><?php echo $info['mobile']?></td>
						<td width="15%" align="center"><a
							href="javascript:edit(<?php echo $info['userid']?>, '<?php echo new_addslashes($info['username'])?>')"><?php echo L('edit')?></a> |
<?php if(!in_array($info['userid'],$admin_founders)) {?>
<a href="javascript:;"
							onclick="data_delete(this,'<?php echo $info['userid']?>','<?php echo L('admin_del_cofirm');?>')"><?php echo L('delete')?></a>
<?php } else {?>
<font color="#cccccc"><?php echo L('delete')?></font>
<?php } ?>
</td>
					</tr>
<?php	}}?>
</tbody>
			</table>
			<div id="pages"> <?php echo isset($pages) ? $pages : ''?></div>
		</form>
	</div>
</div>
</body>
</html>
<script type="text/javascript">
<!--
	function edit(id, name) {
		window.top.art.dialog.open('?app=admin&controller=admin&action=edit&userid='+id ,{
			title:'<?php echo L('edit')?>--'+name,
			id:'edit',
			width:'500px',
			height:'400px',
			ok: function(iframeWin, topWin){
				var form = iframeWin.document.getElementById('dosubmit');
				form.click();
				return false;
			},
			cancel: function(){}
		});
	}

	function data_delete(obj,id,name) {
		window.top.art.dialog.confirm(
			name,
			function(topWin){
				$.get('?app=admin&controller=admin&action=delete&userid='+id,function(data){
				if(data == '1') {
					window.top.art.dialog.alert('<?php echo L('this_object_not_del');?>');
				}else{
					$(obj).parent().parent().fadeOut("slow");
				}
			})
		},
		function(){});
	}
//-->
</script>