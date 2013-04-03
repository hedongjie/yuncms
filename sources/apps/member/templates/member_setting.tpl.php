<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>formvalidator.js" charset="UTF-8"></script>
<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>formValidatorRegex.js" charset="UTF-8"></script>
<script type="text/javascript">
<!--
$(function(){
	$.formValidator.initConfig({
		autotip:true,
		formid:"myform",
		onerror:function(msg){}
	});
	$("#defualtpoint")
		.formValidator({
			tipid:"pointtip",
			onshow:"<?php echo L('input').L('defualtpoint')?>",
			onfocus:"<?php echo L('defualtpoint').L('between_1_to_8_num')?>"
		})
		.regexValidator({
			regexp:"^\\d{1,8}$",
			onerror:"<?php echo L('defualtpoint').L('between_1_to_8_num')?>"
		});
	$("#defualtamount")
		.formValidator({
			tipid:"starnumtip",
			onshow:"<?php echo L('input').L('defualtamount')?>",
			onfocus:"<?php echo L('defualtamount').L('between_1_to_8_num')?>"
		})
		.regexValidator({
			regexp:"^\\d{1,8}$",
			onerror:"<?php echo L('defualtamount').L('between_1_to_8_num')?>"
		});
	$("#rmb_point_rate")
		.formValidator({
			tipid:"rmb_point_rateid",
			onshow:"<?php echo L('input').L('rmb_point_rate')?>",
			onfocus:"<?php echo L('rmb_point_rate').L('between_1_to_8_num')?>"
		})
		.regexValidator({
			regexp:"^\\d{1,8}$",
			onerror:"<?php echo L('rmb_point_rate').L('between_1_to_8_num')?>"
		});
});
//-->
</script>
<div class="pad-lr-10">
<div class="common-form">
<form name="myform" action="?app=member&controller=member_setting&action=manage" method="post" id="myform">
	<table width="100%" class="table_form">
		<tr>
			<td width="200"><?php echo L('allow_register')?></td>
			<td>
				<?php echo L('yes')?><input type="radio" name="info[allowregister]"  class="input-radio" <?php if($member_setting['allowregister']) {?>checked<?php }?> value='1'>
				<?php echo L('no')?><input type="radio" name="info[allowregister]"  class="input-radio" <?php if(!$member_setting['allowregister']) {?>checked<?php }?> value='0'>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('register_model')?></td>
			<td>
				<?php echo L('yes')?><input type="radio" name="info[choosemodel]"  class="input-radio"<?php if($member_setting['choosemodel']) {?>checked<?php }?> value='1'>
				<?php echo L('no')?><input type="radio" name="info[choosemodel]"  class="input-radio"<?php if(!$member_setting['choosemodel']) {?>checked<?php }?> value='0'>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('register_validation')?></td>
			<td>
				<input type="radio" name="info[validation]"  class="input-radio"<?php if(!$member_setting['validation']) {?>checked<?php }?> value='0'>  <?php echo L('no')?><br>
				<input type="radio" name="info[validation]"  class="input-radio"<?php if($member_setting['validation'] == 1) {?>checked<?php }?> value='1'>  <?php echo L('register_email_auth')?><br>
				<input type="radio" name="info[validation]"  class="input-radio"<?php if($member_setting['validation'] == 2) {?>checked<?php }?> value='2'>  <?php echo L('register_verify')?>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('enablcodecheck')?></td>
			<td>
				<?php echo L('yes')?><input type="radio" name="info[enablcodecheck]"  class="input-radio"<?php if($member_setting['enablcodecheck']) {?>checked<?php }?> value='1'>
				<?php echo L('no')?><input type="radio" name="info[enablcodecheck]"  class="input-radio"<?php if(!$member_setting['enablcodecheck']) {?>checked<?php }?> value='0'>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('mobile_checktype')?></td>
			<td>
				<input type="radio" name="info[mobile_checktype]"  class="input-radio" <?php if($member_setting['mobile_checktype']=='1') {?>checked<?php }?> value='2' <?php if($sms_disabled) {?>disabled<?php }?> onclick="$('#sendsms_titleid').hide();">&nbsp;<?php echo L('get_verify')?>&nbsp;
				<input type="radio" name="info[mobile_checktype]"  class="input-radio" <?php if($member_setting['mobile_checktype']=='0' ||$sms_disabled ) {?>checked<?php }?> value='0' onclick="$('#sendsms_titleid').hide();">&nbsp;<?php echo L('no_checksms')?>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('show_app_point')?></td>
			<td>
				<?php echo L('yes')?><input type="radio" name="info[showapppoint]"  class="input-radio"<?php if($member_setting['showapppoint']) {?>checked<?php }?> value='1'>
				<?php echo L('no')?><input type="radio" name="info[showapppoint]"  class="input-radio"<?php if(!$member_setting['showapppoint']) {?>checked<?php }?> value='0'>
			</td>
		</tr>

		<tr>
			<td width="200"><?php echo L('rmb_point_rate')?></td>
			<td>
				<input type="text" name="info[rmb_point_rate]" id="rmb_point_rate" class="input-text" size="4" value="<?php echo $member_setting['rmb_point_rate'];?>">
			</td>
		</tr>

		<tr>
			<td width="200"><?php echo L('defualtpoint')?></td>
			<td>
				<input type="text" name="info[defualtpoint]" id="defualtpoint" class="input-text" size="4" value="<?php echo $member_setting['defualtpoint'];?>">
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('defualtamount')?></td>
			<td>
				<input type="text" name="info[defualtamount]" id="defualtamount" class="input-text" size="4" value="<?php echo $member_setting['defualtamount'];?>">
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('show_register_protocol')?></td>
			<td>
				<?php echo L('yes')?><input type="radio" name="info[showregprotocol]"  class="input-radio" <?php if($member_setting['showregprotocol']) {?>checked<?php }?> value='1'>
				<?php echo L('no')?><input type="radio" name="info[showregprotocol]"  class="input-radio" <?php if(!$member_setting['showregprotocol']) {?>checked<?php }?> value='0'>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('register_protocol')?></td>
			<td>
				<textarea name="info[regprotocol]" id="regprotocol" style="width:80%;height:120px;"><?php echo $member_setting['regprotocol']?></textarea>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('register_verify_message')?></td>
			<td>
				<textarea name="info[registerverifymessage]" id="registerverifymessage" style="width:80%;height:120px;"><?php echo $member_setting['registerverifymessage']?></textarea>
			</td>
		</tr>

		<tr>
			<td width="200"><?php echo L('forgetpasswordmessage')?></td>
			<td>
				<textarea name="info[forgetpassword]" id="forgetpassword" style="width:80%;height:120px;"><?php echo $member_setting['forgetpassword']?></textarea>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('denyusername')?></td>
			<td>
				<textarea name="info[denyusername]" id="denyusername" style="width:40%;height:60px;"><?php foreach($member_setting['denyusername'] as $v) {?><?php echo $v."\r\n";?><?php }?></textarea><?php echo L('denyed_username_setting')?></textarea><?php echo L('denyusername_note')?>
			</td>
		</tr>
		<tr>
			<td width="200"><?php echo L('denyemail')?></td>
			<td>
				<textarea name="info[denyemail]" id="denyemail" style="width:40%;height:60px;"><?php foreach($member_setting['denyemail'] as $v) {?><?php echo $v."\r\n";?><?php }?></textarea><?php echo L('denyemail_note')?>
			</td>
		</tr>
	</table>
    <div class="bk15"></div>
    <input name="dosubmit" type="submit" id="dosubmit" value="<?php echo L('submit')?>" class="button">
</form>
</div>
</div>
</body>
</html>