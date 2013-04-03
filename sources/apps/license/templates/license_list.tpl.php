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
		<?php echo L('all_licensetype')?>: &nbsp;&nbsp; <a href="?app=license&controller=license&menuid=<?php echo $_GET['menuid']?>"><?php echo L('all')?></a> &nbsp;
		<?php
	if(is_array($type_arr)){
	foreach($type_arr as $typeid => $type){
		?><a href="?app=license&controller=license&typeid=<?php echo $typeid;?>&menuid=<?php echo $_GET['menuid']?>"><?php echo $type;?></a>&nbsp;
		<?php }}?>
		</div>
		</td>
		</tr>
    </tbody>
</table>
<form name="myform" id="myform" action="?app=license&controller=license&action=listorder" method="post" >
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('licenseid[]');"></th>
			<th width="35" align="center"><?php echo L('listorder')?></th>
			<th><?php echo L('license_name')?></th>
			<th><?php echo L('url')?></th>
			<th width="12%" align="center"><?php echo L('truename')?></th>
			<th width="8%" align="center"><?php echo L('qq')?></th>
			<th width="8%" align="center"><?php echo L('mobile')?></th>
			<th width="15%" align="center"><?php echo L('telephone')?></th>
			<th width="8%" align="center"><?php echo L('typeid')?></th>
			<th width="12%" align="center"><?php echo L('operations_manage')?></th>
		</tr>
	</thead>
<tbody>
<?php
if(is_array($infos)){
	foreach($infos as $info){
		?>
	<tr>
		<td align="center" width="35"><input type="checkbox" name="licenseid[]" value="<?php echo $info['licenseid']?>"></td>
		<td align="center" width="35"><input name='listorders[<?php echo $info['licenseid']?>]' type='text' size='3' value='<?php echo $info['listorder']?>' class="input-text-c"></td>
		<td align="center"><a href="http://<?php echo $info['domain'];?>" title="<?php echo L('go_website')?>" target="_blank"><?php echo $info['sitename']?></a> </td>
		<td align="center"><a href="http://<?php echo $info['domain'];?>" title="<?php echo L('go_website')?>" target="_blank"><?php echo $info['domain']?></a> </td>
		<td align="center"><?php echo $info['truename']?></td>
		<td align="center"><?php if(!empty($info['qq'])){?><a target="_blank" href="tencent://message/?uin=<?php echo $info['qq']?>&Site=TintSoft&Menu=yes"><img border="0" SRC="http://wpa.qq.com/pa?p=1:<?php echo $info['qq']?>:42" alt="send message"></a><?php }?></td>
		<td align="center"><?php echo $info['mobile']?></td>
		<td align="center"><?php echo $info['telephone']?></td>
		<td align="center" width="10%"><?php echo $type_arr[$info['typeid']];?></td>
		<td align="center" width="12%"><a href="###" onclick="look(<?php echo $info['licenseid']?>, '<?php echo new_addslashes($info['sitename'])?>')" title="<?php echo L('look')?>"><?php echo L('look')?></a> |  <a href="###" onclick="edit(<?php echo $info['licenseid']?>, '<?php echo new_addslashes($info['sitename'])?>')" title="<?php echo L('edit')?>"><?php echo L('edit')?></a> |  <a href="javascript:;" onclick="data_delete(this,'<?php echo $info['licenseid']?>','<?php echo L('del_cofirm');?>')"><?php echo L('delete')?></a>
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
<input name="dosubmit" type="submit" class="button"	value="<?php echo L('listorder')?>">&nbsp;&nbsp;<input type="submit" class="button" name="dosubmit" onClick="document.myform.action='?app=license&controller=license&action=delete'" value="<?php echo L('delete')?>"/></div>
<div id="pages"><?php echo $pages?></div>
</form>
</div>
<script type="text/javascript">
function look(id, name) {
	window.top.art.dialog.open('?app=license&controller=license&action=look&licenseid='+id,{title:'<?php echo L('look')?>《'+name+'》<?php echo L('license')?>',id:'edit',width:'700px',height:'500px'});
}
function edit(id, name) {
	window.top.art.dialog.open('?app=license&controller=license&action=edit&licenseid='+id,{
		title:'<?php echo L('edit')?>《'+name+'》<?php echo L('license')?>',
		id:'edit',
		width:'700px',
		height:'600px',
		ok: function(iframeWin, topWin){var form = iframeWin.document.getElementById('dosubmit');form.click();return false;},cancel: function(){}});
}
function data_delete(obj,id,name) {
	window.top.art.dialog.confirm(name,
		function(topWin){
			$.get('?app=license&controller=license&action=delete&licenseid='+id,function(data){
			if(data == '1') {
				$(obj).parent().parent().fadeOut("slow");
			}
		})
	},
	function(){});
}
</script>
</body>
</html>
