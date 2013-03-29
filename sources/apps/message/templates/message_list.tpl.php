<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = 1;
include $this->admin_tpl('header','admin');
?>
<div class="pad-lr-10">
<form name="searchform" action="?app=message&controller=message&action=search_message&menuid=<?php echo $_GET['menuid'];?>" method="post" >
<table width="100%" cellspacing="0" class="search-form">
    <tbody>
		<tr>
		<td><div class="explain-col"><?php echo L('query_type')?>:<?php echo Form::select($trade_status,$status,'name="search[status]"', L('all'))?>      <?php echo L('username')?>:  <input type="text" value="" class="input-text" name="search[username]">  <?php echo L('time')?>:  <?php echo Form::date('search[start_time]','','')?> <?php echo L('to')?>   <?php echo Form::date('search[end_time]','','')?>    <input type="submit" value="<?php echo L('search')?>" class="button" name="dosubmit">
		</div>
		</td>
		</tr>
    </tbody>
</table>
</form>
<form name="myform" action="?app=message&controller=message&action=delete" method="post" onsubmit="checkuid();return false;">
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('messageid[]');"></th>
			<th><?php echo L('subject')?></th>
			<th width="35%" align="center"><?php echo L('content')?></th>
			<th width="10%" align="center"><?php echo L('fromuserid')?></th>
			<th width='10%' align="center"><?php echo L('touserid')?></th>
			<th width="15%" align="center"><?php echo L('operations_manage')?></th>
		</tr>
	</thead>
<tbody>
<?php
if(is_array($infos)){
	foreach($infos as $info){
		?>
	<tr>
		<td align="center" width="35"><input type="checkbox"
			name="messageid[]" value="<?php echo $info['messageid']?>"></td>
		<td><?php echo $info['subject']?></td>
		<td align="" widht="35%"><?php echo $info['content'];?></td>
		<td align="center" width="10%"><?php echo $info['send_from_id'];?></td>
		<td align="center" width="10%"><?php echo $info['send_to_id'];?></td>
		<td align="center" width="15%"> <a
			href='?app=message&controller=message&action=delete&messageid=<?php echo $info['messageid']?>'
			onClick="return confirm('<?php echo L('confirm', array('message' => new_addslashes($info['subject'])))?>')"><?php echo L('delete')?></a>
		</td>
	</tr>
	<?php
	}
}
?>
</tbody>
</table>
<div class="btn"><a href="#"
	onClick="javascript:$('input[type=checkbox]').attr('checked', true)"><?php echo L('selected_all')?></a>/<a
	href="#"
	onClick="javascript:$('input[type=checkbox]').attr('checked', false)"><?php echo L('cancel')?></a>
<input name="submit" type="submit" class="button"
	value="<?php echo L('remove_all_selected')?>"
	onClick="return confirm('<?php echo L('confirm', array('message' => L('selected')))?>')">&nbsp;&nbsp;</div>
<div id="pages"><?php echo $pages?></div>
</div>
</form>
</div>
<script type="text/javascript">
function checkuid() {
	var ids='';
	$("input[name='messageid[]']:checked").each(function(i, n){
		ids += $(n).val() + ',';
	});
	if(ids=='') {
		window.top.art.dialog.tips('<?php echo L('before_select_operation')?>');
		return false;
	} else {
		myform.submit();
	}
}
</script>
</body>
</html>