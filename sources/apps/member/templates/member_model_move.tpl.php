<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<div class="pad_10">
<div class="common-form">
<form name="myform" action="?app=member&controller=member_model&action=move" method="post" id="myform">
<input type="hidden" name="from_modelid" value="<?php echo $_GET['modelid']?>">
<fieldset>
	<legend><?php echo L('move').L('model_member')?></legend>
	<div class="bk10"></div>
	<div class="explain-col">
		<?php echo L('move_member_model_alert')?>
	</div>
	<div class="bk10"></div>
	<table width="100%" class="table_form">
		<tr>
			<td width="120"><?php echo L('from_model_name')?></td>
			<td>
				<?php echo $modellist[$_GET['modelid']];?>

			</td>
		</tr>
		<tr>
			<td width="120"><?php echo L('to_model_name')?></td>
			<td>
				<?php echo Form::select($modellist, 0, 'id="to_modelid" name="to_modelid"', L('please_select'))?>
			</td>
		</tr>
	</table>
</fieldset>

    <div class="bk15"></div>
    <input name="dosubmit" id="dosubmit" type="submit" value="<?php echo L('submit')?>" class="dialog">
</form>
</div>
</div>
</body>
</html>