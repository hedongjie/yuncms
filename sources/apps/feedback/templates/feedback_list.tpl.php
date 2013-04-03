<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_header = 1;
include $this->admin_tpl('header', 'admin');
?>
<div class="subnav">
    <div class="content-menu ib-a blue line-x">
    <?php if(isset($big_menu)) echo '<a class="add fb" href="'.$big_menu[0].'"><em>'.$big_menu[1].'</em></a>　';?>
    <?php echo admin::submenu($_GET['menuid'],$big_menu); ?><span>|</span><a href="<?php $b = big_menu('index.php?app=feedback&controller=feedback&action=setting', 'setting', L('module_setting'), 550, 350);echo $b[0]?>"><em><?php echo L('module_setting')?></em></a>
    </div>
</div>
<div class="pad-lr-10">
<form name="myform" action="<?php echo U('formguide/formguide_info/delete');?>" method="post">
<div class="table-list">
    <table width="100%" cellspacing="0">
        <thead>
            <tr>
            <th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('fid[]');"></th>
			<th align="center"><?php echo L('username')?></th>
			<th width='250' align="center"><?php echo L('userip')?></th>
			<th width='250' align="center"><?php echo L('times')?></th>
			<th width="250" align="center"><?php echo L('operation')?></th>
            </tr>
        </thead>
    <tbody>
 <?php
if(is_array($datas)){
	foreach($datas as $d){
?>
	<tr>
	<td align="center">
	<input type="checkbox" name="did[]" value="<?php echo $d['fid']?>">
	</td>
	<td><?php echo !empty($d['username']) ? $d['username'] : '游客'?> </td>
	<td align="center"><?php echo $d['ip']?></td>
	<td align="center"><?php echo date('Y-m-d', $d['datetime'])?></td>
	<td align="center"><a href="javascript:check('<?php echo $formid?>', '<?php echo $d['dataid']?>', '<?php echo safe_replace($d['username'])?>');void(0);"><?php echo L('check')?></a> | <a href="?app=formguide&controller=formguide_info&action=public_delete&formid=<?php echo $formid?>&did=<?php echo $d['dataid']?>" onClick="return confirm('<?php echo L('confirm', array('message' => L('delete')))?>')"><?php echo L('del')?></a></td>
	</tr>
<?php
	}
}
?>
</tbody>
    </table>

    <div class="btn"><label for="check_box"><?php echo L('selected_all')?>/<?php echo L('cancel')?></label>
		<input name="submit" type="submit" class="button" value="<?php echo L('remove_all_selected')?>" onClick="document.myform.action='?app=feedback&controller=feedback&action=public_delete&formid=<?php echo $formid?>';return confirm('<?php echo L('affirm_delete')?>')">&nbsp;&nbsp;</div>  </div>
 <div id="pages"><?php echo $pages;?></div>
</form>
</div>
</body>
</html>
<script type="text/javascript">
function check(id, did, title){
	window.top.art.dialog.open('?app=feedback&controller=feedback&action=public_view&formid='+id+'&did='+did,{
		title:'<?php echo L('check')?>--'+title+'<?php echo L('submit_info')?>',
		id:'edit',
		width:'700px',
		height:'500px',
		ok: function(iframeWin, topWin){
		}
	});
}
</script>