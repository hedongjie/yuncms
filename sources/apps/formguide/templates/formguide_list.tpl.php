<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_dialog = $show_header = 1;
include $this->admin_tpl('header', 'admin');
?>
<div class="subnav">
    <div class="content-menu ib-a blue line-x">
    <?php if(isset($big_menu)) echo '<a class="add fb" href="'.$big_menu[0].'"><em>'.$big_menu[1].'</em></a>　';?>
    <?php echo admin::submenu($_GET['menuid'],$big_menu); ?><span>|</span><a href="<?php $b = big_menu('index.php?app=formguide&controller=formguide&action=setting', 'setting', L('module_setting'), 550, 350);echo $b[0]?>"><em><?php echo L('module_setting')?></em></a>
    </div>
</div>
<div class="pad-lr-10">
<form name="myform" action="?app=formguide&controller=formguide&action=listorder" method="post">
<div class="table-list">
    <table width="100%" cellspacing="0">
        <thead>
            <tr>
            <th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('formid[]');"></th>
			<th align="center"><?php echo L('name_items')?></th>
			<th width='100' align="center"><?php echo L('tablename')?></th>
			<th width='150' align="center"><?php echo L('introduction')?></th>
			<th width="140" align="center"><?php echo L('create_time')?></th>
			<th width="160" align="center"><?php echo L('call')?></th>
			<th width="220" align="center"><?php echo L('operations_manage')?></th>
            </tr>
        </thead>
    <tbody>
 <?php
if(is_array($data)){
	foreach($data as $form){
?>
	<tr>
	<td align="center">
	<input type="checkbox" name="formid[]" value="<?php echo $form['modelid']?>">
	</td>
	<td><?php echo $form['name']?> [<a href="<?php echo U('formguide/index/show',array('formid'=>$form['modelid']));?>" target="_blank"><?php echo L('visit_front')?></a>] <?php if ($form['items']) {?>(<?php echo $form['items']?>)<?php }?></td>
	<td align="center"><?php echo $form['tablename']?></td>
	<td align="center"><?php echo $form['description']?></td>
	<td align="center"><?php echo date('Y-m-d H:i:s', $form['addtime'])?></td>
	<td align="center"><input type="text" value="<script language='javascript' src='{SITE_URL}index.php?app=formguide&controller=index&action=show&formid=<?php echo $form['modelid']?>&do=js'></script>"></td>
	<td align="center"><a href="?app=formguide&controller=formguide_info&action=init&formid=<?php echo $form['modelid']?>&menuid=<?php echo $_GET['menuid']?>"><?php echo L('info_list')?></a> | <a href="?app=formguide&controller=formguide_field&action=add&formid=<?php echo $form['modelid']?>"><?php echo L('field_add')?></a> | <a href="?app=formguide&controller=formguide_field&action=init&formid=<?php echo $form['modelid']?>"><?php echo L('field_manage')?></a> <br /><a href="?app=formguide&controller=formguide&action=public_preview&formid=<?php echo $form['modelid']?>"><?php echo L('preview')?></a> | <a href="javascript:edit('<?php echo $form['modelid']?>', '<?php echo safe_replace($form['name'])?>');void(0);"><?php echo L('modify')?></a> | <a href="?app=formguide&controller=formguide&action=disabled&formid=<?php echo $form['modelid']?>&val=<?php echo $form['disabled'] ? 0 : 1;?>"><?php if ($form['disabled']==0) { echo L('field_disabled'); } else { echo L('field_enabled'); }?></a> | <a href="?app=formguide&controller=formguide&action=delete&formid=<?php echo $form['modelid']?>" onClick="return confirm('<?php echo L('confirm', array('message' => addslashes(htmlspecialchars($form['name']))))?>')"><?php echo L('del')?></a> | <a href="javascript:stat('<?php echo $form['modelid']?>', '<?php echo safe_replace($form['name'])?>');void(0);"><?php echo L('stat')?></a></td>
	</tr>
<?php
	}
}
?>
</tbody>
    </table>

    <div class="btn"><label for="check_box"><?php echo L('selected_all')?>/<?php echo L('cancel')?></label>
		<input name="submit" type="submit" class="button" value="<?php echo L('remove_all_selected')?>" onClick="document.myform.action='?app=formguide&controller=formguide&action=delete';return confirm('<?php echo L('affirm_delete')?>')">&nbsp;&nbsp;</div>  </div>
 <div id="pages"><?php echo $this->db->pages;?></div>
</form>
</div>
</body>
</html>
<script type="text/javascript">
function edit(id, name) {
	window.top.art.dialog.open('?app=formguide&controller=formguide&action=edit&formid='+id,{
		title:'<?php echo L('edit_formguide')?> '+name+' ',
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
function stat(id, name) {
	window.top.art.dialog.open('?app=formguide&controller=formguide&action=stat&formid='+id,{
		title:'<?php echo L('stat_formguide')?> '+name+' ',
		id:'edit',
		width:'700px',
		height:'500px',
		ok: function(iframeWin, topWin){
		}
	});
}
</script>