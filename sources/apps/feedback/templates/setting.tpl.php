<?php
defined('IN_ADMIN') or exit('No permission resources.');
$show_header = 1;
include $this->admin_tpl('header', 'admin');
?>
<form method="post" action="<?php echo U('feedback/feedback/setting');?>" id="myform" name="myform">
<table width="100%" cellpadding="0" cellspacing="1" class="table_form">
	<tr>
		<th><strong><?php echo L('allowed_send_mail')?>：</strong></th>
		<td><input name="setting[sendmail]" type="radio" value="1" >&nbsp;<?php echo L('yes')?>&nbsp;&nbsp;<input name="setting[sendmail]" type="radio" value="0" checked>&nbsp;<?php echo L('no')?></td>
	</tr>
	<tr id="mailaddress" style="display:none;">
		<th><strong><?php echo L('e-mail_address')?>：</strong></th>
		<td><input type="text" name="setting[mails]" id="mails" class="input-text" size="50"> <?php echo L('multiple_with_commas')?></td>
	</tr>
	<tr>
		<th width="130"><?php echo L('allows_more_ip')?>：</th>
		<td><input type='radio' name='setting[allowmultisubmit]' value='1' <?php if($allowmultisubmit == 1) {?>checked<?php }?>> <?php echo L('yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
	  <input type='radio' name='setting[allowmultisubmit]' value='0' <?php if($allowmultisubmit == 0) {?>checked<?php }?>> <?php echo L('no')?></td>
	</tr>
	<tr id="setting" style="<?php if ($allowmultisubmit == 0) {?>dispaly:none<?php }?>">
		<th width="130"><?php echo L('interval')?>：</th>
		<td><input type="text" value="<?php echo $interval?>" name="setting[interval]" size="10" class="input-text"> <?php echo L('minute')?></td>
	</tr>
	<tr>
		<th><?php echo L('allowunreg')?>：</th>
		<td><input type='radio' name='setting[allowunreg]' value='1' <?php if($allowunreg == 1) {?>checked<?php }?>> <?php echo L('yes')?>&nbsp;&nbsp;&nbsp;&nbsp;
	  <input type='radio' name='setting[allowunreg]' value='0' <?php if($allowunreg == 0) {?>checked<?php }?>> <?php echo L('no')?></td>
	</tr>
	<tr>
		<th><?php echo L('mailmessage')?>：</th>
		<td><textarea cols="50" rows="6" id="mailmessage" name="setting[mailmessage]"><?php echo $mailmessage?></textarea></td>
	</tr>
	<tr style="display:none">
		<td>&nbsp;</td>
		<td><input type="submit" name="dosubmit" id="dosubmit" class="dialog" value=" <?php echo L('ok')?> ">&nbsp;<input type="reset" class="dialog" value=" <?php echo L('clear')?> "></td>
	</tr>
</table>
</form>
<script type="text/javascript">
$("input:radio[name='setting[allowmultisubmit]']").click(function (){
	if($("input:radio[name='setting[allowmultisubmit]'][checked]").val()==0) {
		$("#setting").hide();
	} else if($("input:radio[name='setting[allowmultisubmit]'][checked]").val()==1) {
		$("#setting").show();
	}
});
$("input:radio[name='setting[sendmail]']").click(function (){
	if($("input:radio[name='setting[sendmail]'][checked]").val()==0) {
		$("#mailaddress").hide();
	} else if($("input:radio[name='setting[sendmail]'][checked]").val()==1) {
		$("#mailaddress").show();
	}
});
</script>
</body>
</html>