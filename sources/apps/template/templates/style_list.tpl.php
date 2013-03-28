<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<div class="pad-lr-10">
<div class="table-list">
<form action="<?php echo U('template/style/updatename');?>" method="post">
    <table width="100%" cellspacing="0">
        <thead>
		<tr>
		<th width="80"><?php echo L("style_identity")?></th>
		<th><?php echo L("style_chinese_name")?></th>
		<th><?php echo L("author")?></th>
		<th width="80"><?php echo L("style_version")?></th>
		<th><?php echo L("status")?></th>
		<th width="200"><?php echo L('operations_manage')?></th>
		</tr>
        </thead>
<tbody>
<?php
if(is_array($list)){
	foreach($list as $v){
?>
<tr>
<td width="80" align="center"><?php echo $v['dirname']?></td>
<td align="center"><input type="text" name="name[<?php echo $v['dirname']?>]" value="<?php echo $v['name']?>" /></td>
<td align="center"><?php if($v['homepage']) {echo  '<a href="'.$v['homepage'].'" target="_blank">';}?><?php echo $v['author']?><?php if($v['homepage']) {echo  '</a>';}?></td>
<td align="center"><?php echo $v['version']?></td>
<td align="center"><?php if($v['disable']){echo L('icon_locked');}else{echo L("icon_unlock");}?></td>
<td align="center"  width="150">
<?php if(C('template','name') != $v['dirname']){?>
	<a href="<?php echo U('template/style/set_default',array('style'=>$v['dirname']));?>"><?php echo L('set_default')?></a> |
<?php }else{?>
	<font color="#FF0000"><?php echo L('default')?></font> |
<?php }?>
<a href="<?php echo U('template/style/disable',array('style'=>$v['dirname']));?>"><?php if($v['disable']){echo L("enable");}else{echo L('disable');}?></a> |
<a href="<?php echo U('template/file/init',array('style'=>$v['dirname']));?>"><?php echo L("detail")?></a> |
<a href="<?php echo U('template/style/export',array('style'=>$v['dirname']));?>"><?php echo L('export')?></a></td>
</tr>
<?php
	}}
?>
</tbody>
</table>
<div class="btn"><input type="submit" class="button" name="dosubmit" value="<?php echo L('submit')?>" /></div>
</form>
</div>
</div>
<div id="pages"><?php echo $pages?></div>
</body>
</html>