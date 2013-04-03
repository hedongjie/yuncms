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
		<?php echo L('all_licensetype')?>: &nbsp;&nbsp; <a href="?app=license&controller=client&menuid=<?php echo $_GET['menuid']?>"><?php echo L('all')?></a> &nbsp;
		<?php
	if(is_array($this->type)){
	foreach($this->type as $typeid => $type){
		?><a href="?app=license&controller=client&typeid=<?php echo $typeid;?>&menuid=<?php echo $_GET['menuid']?>"><?php echo $type;?></a>&nbsp;
		<?php }}?>
		</div>
		</td>
		</tr>
    </tbody>
</table>
<form name="myform" id="myform" action="?app=license&controller=client&action=listorder" method="post" >
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('licenseid[]');"></th>
			<th width="35" align="center"><?php echo L('listorder')?></th>
			<th><?php echo L('license_name')?></th>
			<th><?php echo L('url')?></th>
			<th width="12%" align="center">CHARSET</th>
			<th width="8%" align="center">Version</th>
			<th width="8%" align="center">Release</th>
			<th width="15%" align="center">Email</th>
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
		<td align="center" width="35"><input type="checkbox" name="clientid[]" value="<?php echo $info['clientid']?>"></td>
		<td align="center" width="35"><input name='listorders[<?php echo $info['licenseid']?>]' type='text' size='3' value='<?php echo $info['listorder']?>' class="input-text-c"></td>
		<td align="center"><a href="<?php echo $info['siteurl'];?>" title="<?php echo L('go_website')?>" target="_blank"><?php echo $info['sitename']?></a> </td>
		<td align="center"><a href="<?php echo $info['siteurl'];?>" title="<?php echo L('go_website')?>" target="_blank"><?php echo $info['siteurl']?></a> </td>
		<td align="center"><?php echo $info['charset']?></td>
		<td align="center"><?php echo $info['version']?></td>
		<td align="center"><?php echo $info['release']?></td>
		<td align="center"><?php echo $info['email']?></td>
		<td align="center" width="10%"><?php echo $this->type[$info['typeid']];?></td>
		<td align="center" width="12%"><a href="###" onclick="look(<?php echo $info['clientid']?>, '<?php echo new_addslashes($info['sitename'])?>')" title="<?php echo L('look')?>"><?php echo L('look')?></a> |  <a href="###" onclick="import_license(<?php echo $info['clientid']?>, '<?php echo new_addslashes($info['sitename'])?>')" title="<?php echo L('import_license')?>"><?php echo L('import_license')?></a> |  <a href="javascript:;" onclick="data_delete(this,'<?php echo $info['clientid']?>','<?php echo L('del_cofirm');?>')"><?php echo L('delete')?></a>
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
	window.top.art.dialog.open('?app=license&controller=client&action=look&clientid='+id,{title:'<?php echo L('look')?>《'+name+'》<?php echo L('license')?>',id:'edit',width:'700px',height:'500px'});
}
function import_license(id, name) {
	window.top.art.dialog.open('?app=license&controller=client&action=import&clientid='+id,{
		title:'<?php echo L('import')?>《'+name+'》<?php echo L('license')?>',
		id:'edit',
		width:'700px',
		height:'600px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');form.click();return false;
		},
		cancel: function(){
		}
	});
}
function data_delete(obj,id,name) {
	window.top.art.dialog.confirm(name,
		function(topWin){
			$.get('?app=license&controller=client&action=delete&clientid='+id,function(data){
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
