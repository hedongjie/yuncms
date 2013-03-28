<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = 1;
include $this->admin_tpl('header', 'admin');
?>
<div class="pad-lr-10">
<table width="100%" cellspacing="0" class="search-form">
    <tbody>
		<tr>
		<td><div class="explain-col">
		<?php echo L('all_linktype')?>: &nbsp;&nbsp; <a href="?app=link&controller=link"><?php echo L('all')?></a> &nbsp;&nbsp;
		<a href="?app=link&controller=link&typeid=0">默认分类</a>&nbsp;
		<?php
	if(is_array($type_arr)){
	foreach($type_arr as $typeid => $type){
		?><a href="?app=link&controller=link&typeid=<?php echo $typeid;?>"><?php echo $type;?></a>&nbsp;
		<?php }}?>
		</div>
		</td>
		</tr>
    </tbody>
</table>
<form name="myform" id="myform" action="?app=link&controller=link&action=listorder" method="post" >
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('linkid[]');"></th>
			<th width="35" align="center"><?php echo L('listorder')?></th>
			<th><?php echo L('link_name')?></th>
			<th width="12%" align="center"><?php echo L('logo')?></th>
			<th width="10%" align="center"><?php echo L('typeid')?></th>
			<th width='10%' align="center"><?php echo L('link_type')?></th>
			<th width="8%" align="center"><?php echo L('status')?></th>
			<th width="8%" align="center"><?php echo L('hits')?></th>
			<th width="12%" align="center"><?php echo L('operations_manage')?></th>
		</tr>
	</thead>
<tbody>
<?php
if(is_array($infos)){
	foreach($infos as $info){
		?>
	<tr>
		<td align="center" width="35"><input type="checkbox" name="linkid[]" value="<?php echo $info['linkid']?>"></td>
		<td align="center" width="35"><input name='listorders[<?php echo $info['linkid']?>]' type='text' size='3' value='<?php echo $info['listorder']?>' class="input-text-c"></td>
		<td><a href="<?php echo $info['url'];?>" title="<?php echo L('go_website')?>" target="_blank"><?php echo $info['name']?></a> </td>
		<td align="center" width="12%"><?php if($info['linktype']==1){?><img src="<?php echo $info['logo'];?>" width=83 height=31><?php }?></td>
		<td align="center" width="10%"><?php if($info['typeid']) echo $type_arr[$info['typeid']]; else echo '默认分类';?></td>
		<td align="center" width="10%"><?php if($info['linktype']==0){echo L('word_link');}else{echo L('logo_link');}?></td>
		<td width="8%" align="center"><?php if($info['passed']=='0'){?>
		<a	href="<?php echo art_confirm(L('pass_or_not'), '?app=link&controller=link&action=check&linkid='.$info['linkid']);?>"><font color=red><?php echo L('audit')?></font></a><?php }else{echo L('passed');}?></td>
		<td width="8%" align="center"><?php echo $info['hits']?></td>
		<td align="center" width="12%"><a href="###" onclick="edit(<?php echo $info['linkid']?>, '<?php echo new_addslashes($info['name'])?>')" title="<?php echo L('edit')?>"><?php echo L('edit')?></a> |
			<a href="<?php echo art_confirm(L('confirm', array('message' => new_addslashes($info['name']))), '?app=link&controller=link&action=delete&linkid='.$info['linkid']);?>"><?php echo L('delete')?></a>
		</td>
	</tr>
	<?php
	}
}
?>
</tbody>
</table>
</div>
<div class="btn">
<input name="dosubmit" type="submit" class="button"
	value="<?php echo L('listorder')?>">&nbsp;&nbsp;<input type="submit" class="button" name="dosubmit" onClick="document.myform.action='?app=link&controller=link&action=delete'" value="<?php echo L('delete')?>"/></div>
<div id="pages"><?php echo $pages?></div>
</form>
</div>
<script type="text/javascript">

function edit(id, name) {
	window.top.art.dialog.open('?app=link&controller=link&action=edit&linkid='+id,{
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
function checkuid() {
	var ids='';
	$("input[name='linkid[]']:checked").each(function(i, n){
		ids += $(n).val() + ',';
	});
	if(ids=='') {
		window.top.art.dialog({content:"<?php echo L('before_select_operations')?>",lock:true,width:'200',height:'50',time:1.5},function(){});
		return false;
	} else {
		myform.submit();
	}
}
</script>
</body>
</html>
