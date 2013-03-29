<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
$show_validator = true;
include $this->admin_tpl ( 'header' );
?>
<script type="text/javascript">
  $(document).ready(function() {
	$.formValidator.initConfig({
		autotip:true,
		formid:"myform",
		onerror:function(msg){}
	});
	$("#realname")
		.formValidator({
			onshow:"<?php echo L('input').L('realname')?>",
			onfocus:"<?php echo L('realname').L('between_2_to_20')?>"
		})
		.inputValidator({
			min:2,
			max:20,
			onerror:"<?php echo L('realname').L('between_2_to_20')?>"
		})
	$("#mobile")
		.formValidator({
			empty:true,onshow:"<?php echo L('input').L('mobile')?>",
			onfocus:"<?php echo L('mobile').L('format_incorrect')?>",
			oncorrect:"<?php echo L('mobile').L('format_right')?>"
		})
		.regexValidator({
			regexp:"mobile",
			datatype:"enum",
			onerror:"<?php echo L('mobile').L('format_incorrect')?>"
		})
	$("#email")
		.formValidator({
			onshow:"<?php echo L('input').L('email')?>",
			onfocus:"<?php echo L('input').L('email')?>",
			oncorrect:"<?php echo L('email').L('format_right')?>"
		})
		.regexValidator({
			regexp:"email",
			datatype:"enum",
			onerror:"<?php echo L('email').L('format_incorrect')?>"
		})
		.ajaxValidator({
	    	type : "get",
			url : "?app=admin&controller=admin&action=public_email_ajx",
			data :"",
			datatype : "html",
			async:'false',
			success : function(data){
            	if( data == "1" ){
            	    return true;
				}else{
            	    return false;
				}
			},
			buttons: $("#dosubmit"),
			onerror : "<?php echo L('email_already_exists')?>",
			onwait : "<?php echo L('connecting_please_wait')?>"
		})
		.defaultPassed();
  })
</script>
<div class="pad_10">
	<div class="common-form">
		<form name="myform"
			action="?app=admin&controller=private&action=public_edit_info"
			method="post" id="myform">
			<table width="100%" class="table_form contentWrap">
				<tr>
					<td width="80"><?php echo L('username')?></td>
					<td><?php echo $username?></td>
				</tr>

				<tr>
					<td width="80"><?php echo L('lastlogintime')?></td>
					<td><?php echo $lastlogintime ? date('Y-m-d H:i:s',$lastlogintime) : ''?></td>
				</tr>

				<tr>
					<td width="80"><?php echo L('lastloginip')?></td>
					<td><?php echo $lastloginip?></td>
				</tr>

				<tr>
					<td><?php echo L('realname')?></td>
					<td><input type="text" name="info[realname]" id="realname"
						class="input-text" size="30" value="<?php echo $realname?>"></input>
					</td>
				</tr>
				<tr>
					<td><?php echo L('email')?></td>
					<td><input type="text" name="info[email]" id="email"
						class="input-text" size="40" value="<?php echo $email?>"></input>
					</td>
				</tr>
				<tr>
					<td><?php echo L('mobile')?></td>
					<td><input type="text" name="info[mobile]" id="mobile"
						class="input-text" value="<?php echo $mobile?>"></input></td>
				</tr>
			</table>

			<div class="bk15"></div>
			<input name="dosubmit" type="submit" value="<?php echo L('submit')?>"
				class="button" id="dosubmit">
		</form>
	</div>
</div>
</body>
</html>
