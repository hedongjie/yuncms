<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<div class="bk15"></div>
<div class="pad_10">
<div class="table-list">
    <table width="100%" cellspacing="0">
        <thead>
		<tr>
		<th><?php echo L('time')?></th>
		<th><?php echo L('who')?></th>
		<th width="150"><?php echo L('operations_manage')?></th>
		</tr>
        </thead>
        <tbody>
<?php
if(is_array($list)){
	foreach($list as $v){
?>
<tr>
<td align="center"><?php echo Format::date($v['creat_at'], 1)?></td>
<td align="center"><?php echo $v['username']?></td>
<td align="center"><a href="<?php echo art_confirm(L('are_you_sure_you_want_to_restore'), '?app=template&controller=template_bak&action=restore&id='.$v['id'].'&style='.$this->style.'&dir='.$this->dir.'&filename='.$this->filename)?>" ><?php echo L('restore')?></a> |
<a href="<?php echo art_confirm(L('confirm', array('message'=>Format::date($v['creat_at'], 1))), '?app=template&controller=template_bak&action=del&id='.$v['id'].'&style='.$this->style.'&dir='.$this->dir.'&filename='.$this->filename)?>" ><?php echo L('delete')?></a></td>
</tr>
<?php
	}}?>
</tbody>
</table>
</from>
</div>
</div>
<div id="pages"><?php echo $pages?></div>
</body>
</html>