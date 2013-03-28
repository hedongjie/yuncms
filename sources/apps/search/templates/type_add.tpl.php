<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<script type="text/javascript">
<!--
	$(function(){
		$.formValidator.initConfig({
			formid:"myform",
			autotip:true,onerror:function(msg,obj){
				window.top.art.dialog.alert(msg);
				$(obj).focus();
			}
		});
		$("#name")
			.formValidator({
				onshow:"<?php echo L("input").L('type_name')?>",
				onfocus:"<?php echo L("input").L('type_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('type_name')?>"
			})
			.regexValidator({
				regexp:"yun_username",
				datatype:"enum",
				onerror:"<?php echo L('type_name').L('format_incorrect', '','member')?>"
			});
	})
//-->
</script>
<div class="pad-lr-10">
<form action="<?php echo U('search/search_type/add');?>" method="post" id="myform">
	<table width="100%"  class="table_form">
	<tr>
    <th width="120"><?php echo L('select_application_name')?>：</th>
    <td class="y-bg"><?php echo Form::select($application_data,'','name="application" onchange="change_application(this.value)"')?></td>
  </tr>
<tr id="modelid_display" style="display:none">
    <th width="120"><?php echo L('select_model_name')?>：</th>
    <td class="y-bg"><?php echo Form::select($model_data,'','name="info[modelid]"')?></td>
  </tr>
  <tr>
    <th width="120"><?php echo L('type_name')?>：</th>
    <td class="y-bg"><input type="text" class="input-text" name="info[name]" id="name" size="30" /></td>
  </tr>
    <tr>
    <th><?php echo L('description')?>：</th>
    <td class="y-bg"><textarea name="info[description]" maxlength="255" style="width:300px;height:60px;"></textarea></td>
  </tr>
</table>

<div class="bk15"></div>
    <input type="submit" class="dialog" id="dosubmit" name="dosubmit" value="<?php echo L('submit')?>" />
</form>
</div>
<SCRIPT LANGUAGE="JavaScript">
<!--
	function change_application(module) {
	if(module=='content') {
		$('#modelid_display').css('display','');
	}else {
		$('#modelid_display').css('display','none');
	}
}
//-->
</SCRIPT>
</body>
</html>