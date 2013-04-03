<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<script type="text/javascript">
<!--
$(function(){
	$.formValidator.initConfig({formid:"myform",autotip:true,onerror:function(msg,obj){window.top.art.dialog.alert(msg);$(obj).focus();}});
	$("#license_truename").formValidator({onshow:"<?php echo L("input").L('truename')?>",onfocus:"<?php echo L("input").L('truename')?>"}).inputValidator({min:1,onerror:"<?php echo L("input").L('truename')?>"});
 	$("#license_telephone").formValidator({onshow:"<?php echo L("input").L('telephone')?>",onfocus:"<?php echo L("input").L('telephone')?>"}).inputValidator({min:1,onerror:"<?php echo L("input").L('telephone')?>"}).regexValidator({regexp:"tel",datatype:"enum",onerror:"<?php echo L('telephone').L('format_incorrect')?>"});
 	$("#license_mobile").formValidator({onshow:"<?php echo L("input").L('mobile')?>",onfocus:"<?php echo L("input").L('mobile')?>"}).inputValidator({min:1,max:11,onerror:"<?php echo L("mobile").L('format_incorrect')?>"}).regexValidator({regexp:"mobile",datatype:"enum",onerror:"<?php echo L('mobile').L('format_incorrect')?>"});
 	$("#license_email").formValidator({onshow:"<?php echo L("input").L('email')?>",onfocus:"<?php echo L("input").L('email')?>"}).inputValidator({min:1,onerror:"<?php echo L("input").L('email')?>"}).regexValidator({regexp:"email",datatype:"enum",onerror:"<?php echo L('email').L('format_incorrect')?>"});
 	$("#license_msn").formValidator({onshow:"<?php echo L("input").L('msn')?>",onfocus:"<?php echo L("input").L('msn')?>"}).inputValidator({min:1,onerror:"<?php echo L("input").L('msn')?>"}).regexValidator({regexp:"email",datatype:"enum",onerror:"<?php echo L('msn').L('format_incorrect')?>"});
 	$("#license_qq").formValidator({onshow:"<?php echo L("input").L('qq')?>",onfocus:"<?php echo L("input").L('qq')?>"}).inputValidator({min:5,max:11,onerror:"<?php echo L("qq").L('format_incorrect')?>"}).regexValidator({regexp:"qq",datatype:"enum",onerror:"<?php echo L('qq').L('format_incorrect')?>"});
 	$("#license_address").formValidator({onshow:"<?php echo L("input").L('address')?>",onfocus:"<?php echo L("input").L('address')?>"}).inputValidator({min:1,onerror:"<?php echo L("input").L('address')?>"});
 	$("#license_postcode").formValidator({onshow:"<?php echo L("input").L('postcode')?>",onfocus:"<?php echo L("input").L('postcode')?>"}).inputValidator({min:1,max:6,onerror:"<?php echo L("postcode").L('format_incorrect')?>"}).regexValidator({regexp:"zipcode",datatype:"enum",onerror:"<?php echo L('postcode').L('format_incorrect')?>"});
});
//-->
</script>

<div class="pad_10">
<form action="?app=license&controller=license&action=edit&licenseid=<?php echo $licenseid; ?>" method="post" name="myform" id="myform">
<table cellpadding="2" cellspacing="1" class="table_form" width="100%">
	<tr>
		<th width="20%"><?php echo L('typeid')?>：</th>
		<td><select name="license[typeid]" id=""><?php foreach($types as $key=>$val){?>
		<option value="<?php echo $key;?>" <?php if($key==$typeid){echo "selected";}?>><?php echo $val;?></option>
		<?php }?></select></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('license_name')?>：</th>
		<td><?php echo $sitename?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('url')?>：</th>
		<td><?php echo $domain?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('truename')?>：</th>
		<td><input type="text" name="license[truename]" id="license_truename" size="20" value="<?php echo $truename;?>" class="input-text"></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('telephone')?>：</th>
		<td><input type="text" name="license[telephone]" id="license_telephone" size="30" value="<?php echo $telephone;?>" class="input-text"></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('mobile')?>：</th>
		<td><input type="text" name="license[mobile]" id="license_mobile" size="15" value="<?php echo $mobile;?>" class="input-text"></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('email')?>：</th>
		<td><input type="text" name="license[email]"  id="license_email" size="30" value="<?php echo $email;?>" class="input-text"></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('msn')?>：</th>
		<td><input type="text" name="license[msn]" id="license_msn" size="30" value="<?php echo $msn;?>" class="input-text"></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('qq')?>：</th>
		<td><input type="text" name="license[qq]" id="license_qq" size="10" value="<?php echo $qq;?>" class="input-text"></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('address')?>：</th>
		<td><input type="text" name="license[address]" id="license_address" size="50" value="<?php echo $address;?>" class="input-text"></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('postcode')?>：</th>
		<td><input type="text" name="license[postcode]" id="license_postcode" size="10" value="<?php echo $postcode;?>" class="input-text"></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('licensekey')?>：</th>
		<td><?php echo $licensekey;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('starttime')?>：</th>
		<td><?php $starttime = $starttime == '0000-00-00' ? '' : $starttime ; echo Form::date('license[starttime]', $starttime);?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('endtime')?>：</th>
		<td><?php echo Form::date('license[endtime]', $endtime);?></td>
	</tr>
	<tr>
		<th></th>
		<td><input type="hidden" name="forward" value="add"> <input type="submit" name="dosubmit" id="dosubmit" class="dialog" value=" <?php echo L('submit')?> "></td>
	</tr>
</table>
</form>
</div>
</body>
</html>
