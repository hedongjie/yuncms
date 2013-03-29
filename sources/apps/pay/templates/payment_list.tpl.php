<?php
	defined('IN_ADMIN') or exit('No permission resources.');
	include $this->admin_tpl('header', 'admin');
?>
<div class="pad_10">
<div class="table-list">
    <table width="100%" cellspacing="0">
        <thead>
            <tr>
            <th width="10%"  align="left"><?php echo L('payment_mode').L('name')?></th>
            <th width="5%"><?php echo L('plus_version')?></th>
            <th width="15%"><?php echo L('plus_author')?></th>
            <th width="45%"><?php echo L('desc')?></th>
             <th width="10%"><?php echo L('listorder')?></th>
            <th width="15%"><?php echo L('operations_manage')?></th>
            </tr>
        </thead>
    <tbody>
 <?php
if(is_array($infos['data'])){
	foreach($infos['data'] as $info){
?>
	<tr>
	<td width="10%"><?php echo $info['pay_name']?></td>
	<td  width="5%" align="center"><?php echo $info['version']?></td>
	<td  width="15%" align="center"><?php echo $info['author']?></td>
	<td width="45%" align="center"><?php echo $info['pay_desc']?></td>
	<td width="10%" align="center"><?php echo $info['pay_order']?></td>
	<td width="15%" align="center">
	<?php if ($info['enabled']) {?>
	<a href="javascript:edit('<?php echo $info['pay_id']?>', '<?php echo $info['pay_name']?>')"><?php echo L('edit')?></a> |
	<a href="<?php echo art_confirm(L('confirm',array('message'=>$info['pay_name'])), '?app=pay&controller=payment&action=delete&id='.$info['pay_id'])?>"><?php echo L('plus_uninstall')?></a>
	<?php } else {?>
	<a href="javascript:add('<?php echo $info['pay_code']?>', '<?php echo $info['pay_name']?>')"><?php echo L('plus_install')?></a>
	<?php }?>
	</td>
	</tr>
<?php
	}
}
?>
    </tbody>
    </table>

    <div class="btn"></div>  </div>

 <div id="pages"> <?php echo $pages?></div>
</div>
</div>
</body>
</html>
<script type="text/javascript">
<!--
	function add(id, name) {
		window.top.art.dialog.open('?app=pay&controller=payment&action=add&code='+id ,{
			title:'<?php echo L('add')?>--'+name,
			id:'add',
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
	function edit(id, name) {
		window.top.art.dialog.open('?app=pay&controller=payment&action=edit&id='+id ,{
			title:'<?php echo L('edit')?>--'+name,
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