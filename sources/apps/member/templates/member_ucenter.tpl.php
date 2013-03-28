<?php
defined('IN_YUNCMS') or exit('No permission resources.');
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
	$("#uc_api")
		.formValidator({
			onshow:"<?php echo L('setting_ucenter_type')?>",
			onfocus:"<?php echo L('setting_ucenter_type')?>",
			tipcss:{width:'300px'},
			empty:true
		})
		.inputValidator({
			onerror:"<?php echo L('setting_ucenter_type')?>"
		})
		.regexValidator({
			regexp:"http:\/\/(.+)[^/]$",
			onerror:"<?php echo L('setting_ucenter_type')?>"
		});
	$("#uc_appid")
		.formValidator({
			empty:true,
			onshow:"<?php echo L('input').L('setting_ucenter_appid')?>",
			onfocus:"<?php echo L('input').L('setting_ucenter_appid')?>"
		})
		.regexValidator({
			regexp:"^\\d{1,8}$",
			onerror:"<?php echo L('setting_ucenter_appid').L('must_be_number')?>"
		});
})
//-->
</script>
<div class="pad-lr-10">
<div class="common-form">
<form name="myform" action="?app=member&controller=ucenter_setting&action=init" method="post" id="myform">
<table width="100%"  class="table_form">
<tr>
	<td colspan="2">
		<?php echo L('uc_notice')?></td>
  </tr>
  <tr>
    <th width="140"></th>
    <td class="y-bg" >
    <input name="setuc[ucenter]" value="1" type="radio"  <?php echo ($ucenter == '1') ? ' checked' : ''?> onClick="$('#ucenter').show()"> <?php echo L('uc_on')?>&nbsp;&nbsp;&nbsp;&nbsp;
	 <input name="setuc[ucenter]" value="0" type="radio"  <?php echo ($ucenter =='0') ? ' checked' : ''?> onClick="$('#ucenter').hide()"> <?php echo L('uc_off')?></td>
  </tr>
</table>
<table width="100%"  class="table_form" id=ucenter <?php if($ucenter =='0') echo 'style="display:none"';?>>
  <tr>
    <th width="140"><?php echo L('uc_api_host')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_api]" id="uc_api" size="50" value="<?php echo $uc_api ?>"/></td>
  </tr>
  <tr>
    <th width="140"><?php echo L('uc_api_ip')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_ip]" id="uc_ip" size="40" value="<?php echo $uc_ip ?>"/></td>
  </tr>
   <tr>
    <th width="140"><?php echo L('uc_db_host')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_dbhost]" id="uc_dbhost" size="20" value="<?php echo $uc_dbhost ?>"/></td>
  </tr>
  <tr>
    <th width="140"><?php echo L('uc_db_username')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_dbuser]" id="uc_dbuser" size="20" value="<?php echo $uc_dbuser ?>"/></td>
  </tr>
  <tr>
    <th width="140"><?php echo L('uc_db_password')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_dbpw]" id="uc_dbpw" size="20" value="<?php echo $uc_dbpw ?>"/></td>
  </tr>
  <tr>
    <th width="140"><?php echo L('uc_dbname')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_dbname]" id="uc_dbname" size="20" value="<?php echo $uc_dbname ?>"/></td>
  </tr>
  <tr>
    <th width="140"><?php echo L('uc_db_pre')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_dbtablepre]" id="uc_dbtablepre" size="20" value="<?php echo $uc_dbtablepre ?>"/> <input type="button" value="<?php echo L('uc_test_database')?>" class="button"  onclick="mysql_test()" /></td>
  </tr>
  <tr>
    <th width="140"><?php echo L('uc_db_charset')?></th>
    <td class="y-bg">
    <select name="setuc[uc_dbcharset]" id="uc_dbcharset"  />
				<option value=""><?php echo L('please_select')?></option>
				<option value="gbk" <?php if(isset($uc_dbcharset) && $uc_dbcharset == 'gbk'){echo 'selected';}?>>GBK</option>
				<option value="utf8"  <?php if(isset($uc_dbcharset) && $uc_dbcharset == 'utf8'){echo 'selected';}?>>UTF-8</option>
			</select></td>
  </tr>
  <tr>
    <th width="140"><?php echo L('uc_key')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_key]" id="uc_key" size="50" value="<?php echo $uc_key ?>"/></td>
  </tr>
   <tr>
    <th width="140"><?php echo L('uc_appid')?></th>
    <td class="y-bg"><input type="text" class="input-text" name="setuc[uc_appid]" id="uc_appid" size="2" value="<?php echo $uc_appid ?>"/></td>
  </tr>
  </table>
</div>
<div class="bk15"></div>
<input name="dosubmit" type="submit" value="<?php echo L('submit')?>" class="button">
</div>
</div>
</form>
<script type="text/javascript">
function mysql_test() {
	$.post('?app=member&controller=ucenter_setting&action=public_myqsl_test', {
		host:$('#uc_dbhost').val(),
		username:$('#uc_dbuser').val(),
		password:$('#uc_dbpw').val()},
		function(data){
			if(data==1){
				alert('<?php echo L('connect_success')?>')
			}else{
				alert('<?php echo L('connect_failed')?>')
			}
		});
}
</script>
</body>
</html>