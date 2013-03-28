<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<form name="myform" action="<?php echo U('search/search_type/listorder');?>" method="post">
<div class="pad_10">
<div class="table-list">

<div class="explain-col">
<?php echo L('searh_notice')?>
</div>
<div class="bk10"></div>
    <table width="100%" cellspacing="0" >
        <thead>
			<tr>
			<th width="55"><?php echo L('sort')?></td>
			<th width="35">ID</th>
			<th width="120"><?php echo L('catname')?></th>
			<th width="80"><?php echo L('applicationname')?></th>
			<th width="80"><?php echo L('modlename')?></th>
			<th width="*"><?php echo L('catdescription')?></th>
			<th width="80"><?php echo L('opreration')?></th>
			</tr>
        </thead>
    <tbody>


<?php
foreach($datas as $r) {
?>
<tr>
<td align="center"><input type="text" name="listorders[<?php echo $r['typeid']?>]" value="<?php echo $r['listorder']?>" size="3" class='input-text-c'></td>
<td align="center"><?php echo $r['typeid']?></td>
<td align="center"><?php echo $r['name']?></td>
<td align="center"><?php echo $r['modelid'] ? L('content_application') :$r['typedir'];?></td>
<td align="center"><?php echo $this->model[$r['modelid']]['name']?></td>
<td ><?php echo $r['description']?></td>
<td align="center"><a href="javascript:edit('<?php echo $r['typeid']?>','<?php echo $r['name']?>')"><?php echo L('modify')?></a> | <a href="javascript:;" onclick="data_delete(this,'<?php echo $r['typeid']?>','<?php echo L('sure_delete', '', 'member')?>')"><?php echo L('delete')?></a> </td>
</tr>
<?php } ?>
	</tbody>
    </table>

    <div class="btn"><input type="submit" class="button" name="dosubmit" value="<?php echo L('listorder')?>" /></div>  </div>
</div>
<div id="pages"><?php echo $pages;?></div>
</div>
</form>

<script type="text/javascript">
<!--
function edit(id, name) {
	window.top.art.dialog.open('?app=search&controller=search_type&action=edit&typeid='+id,{title:'<?php echo L('edit_cat')?>《'+name+'》',
		id:'edit',
		width:'580px',
		height:'240px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
	    cancel: function(){}
	});
}
function data_delete(obj,id,name){
	window.top.art.dialog.confirm(
		name,
		function(topWin){
			$.get('?app=search&controller=search_type&action=delete&typeid='+id,function(data){
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