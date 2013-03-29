<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<script type="text/javascript">
<!--
	$(function(){
		$.formValidator.initConfig({
			formid:"myform",
			autotip:true,
			onerror:function(msg,obj){
				window.top.art.dialog.alert(msg);
				$(obj).focus();
			}
		});
		$("#name")
			.formValidator({
				onshow:"<?php echo L('input').L('name')?>",
				onfocus:"<?php echo L('input').L('name')?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L('input').L('name')?>"
			})
			.ajaxValidator({
				type : "get",
				url : "?app=tag&controller=tag&action=public_name&id=<?php echo $id?>",
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
				onerror : "<?php echo L('name').L('exists')?>",
				onwait : "<?php echo L('connecting')?>"
			})
			.defaultPassed();
		$("#cache")
			.formValidator({
				onshow:"<?php echo L("enter_the_cache_input_will_not_be_cached")?>",
				onfocus:"<?php echo L("enter_the_cache_input_will_not_be_cached")?>",
				empty:true
			})
			.regexValidator({
				regexp:"num1",
				datatype:'enum',
				param:'i',
				onerror:"<?php echo L("cache_time_can_only_be_positive")?>"
			})
			.defaultPassed();
		$("#num")
			.formValidator({
				onshow:"<?php echo L('input').L("num")?>",
				onfocus:"<?php echo L('input').L("num")?>",
				empty:true
			})
			.regexValidator({
				regexp:"num1",
				datatype:'enum',
				param:'i',
				onerror:"<?php echo L('that_shows_only_positive_numbers')?>"
			})
			.defaultPassed();
		$("#return")
			.formValidator({
				onshow:"<?php echo L('return_value')?>",
				onfocus:"<?php echo L('return_value')?>",
				empty:true
			});
		show_action('position');
	})

	function show_action(obj) {
		$('.xt_action_list').hide();
		$('#action_'+obj).show();
	}
//-->
</script>

<div class="pad-10">
<form action="?app=tag&controller=tag&action=edit&id=<?php echo $id?>" method="post" id="myform">
<div>
<fieldset>
	<legend><?php echo L('tag_call_setting')?></legend>
	<table width="100%"  class="table_form">
    <tr>
		<th width="80"><?php echo L('stdcall')?>：</th>
		<td class="y-bg"><?php echo Form::radio(array('0'=>L('model_configuration'), '1'=>L('custom_sql'), '2'=> L('block')), isset($type) ? $type : 0, 'name="type" onclick="location.href=\''.Core_Request::get_url().'&type=\'+this.value"')?></td>
	</tr>
  <?php if ($type==0) :?>
    <tr>
		<th><?php echo L('select_model')?>：</th>
		<td class="y-bg"><?php echo Form::select($applications, $application, 'name="application" id="application" onchange="location.href=\''.Core_Request::get_url().'&application=\'+this.value"')?><script type="text/javascript">$(function(){$("#application").formValidator({onshow:"<?php echo L('please_select_model')?>",onfocus:"<?php echo L('please_select_model')?>"}).inputValidator({min:1, onerror:'<?php echo L('please_select_model')?>'});});</script></td>
	</tr>
  <?php if ($application):?>
    <tr>
		<th><?php echo L('selectingoperation')?>：</th>
		<td class="y-bg"><?php echo Form::radio($html['do'], $do, 'name="do" onclick="location.href=\''.Core_Request::get_url().'&do=\'+this.value"')?></td>
	  </tr>
	  <?php endif;?>
	  <?php if(isset($html[$do]) && is_array($html[$do]) && $do)foreach($html[$do] as $k=>$v):?>
		  <tr>
		<th><?php echo $v['name']?>：</th>
		<td class="y-bg"><?php echo creat_form($k, $v, $form_data[$k])?></td>
	</tr>
  <?php endforeach;?>
  <?php elseif ($type==1) :?>
    <tr>
		<th valign="top"><?php echo L('custom_sql')?>：</th>
		<td class="y-bg"><textarea name="data" id="data" style="width:386px;height:178px;"><?php echo $form_data['sql']?></textarea><script type="text/javascript">$(function(){$("#data").formValidator({onshow:"<?php echo L('please_enter_a_sql')?>",onfocus:"<?php echo L('please_enter_a_sql')?>"}).inputValidator({min:1, onerror:'<?php echo L('please_enter_a_sql')?>'});});</script></td>
  </tr>
  <tr>
		<th valign="top"><?php echo L('over_dbsource')?>：</th>
		<td class="y-bg"><?php echo Form::select($dbsource, $form_data['dbsource'], 'name="dbsource" id="dbsource" ')?><script type="text/javascript">$(function(){$("#dbsource").formValidator({onshow:"<?php echo L('please_select_dbsource')?>",onfocus:"<?php echo L('please_select_dbsource')?>"}).inputValidator({min:1, onerror:'<?php echo L('please_select_dbsource')?>'});});</script></td>
  </tr>
  <?php else :?>
  <tr>
		<th valign="top"><?php echo L('block').L('name')?>：</th>
		<td class="y-bg"><input type="text" name="block" size="25" value="<?php echo $edit_data['data']?>" id="block"><script type="text/javascript">$(function(){$("#block").formValidator({onshow:"<?php echo L('please_input_block_name')?>",onfocus:"<?php echo L('please_input_block_name')?>"}).inputValidator({min:1, onerror:'<?php echo L('please_input_block_name')?>'});});</script></td>
  </tr>
  <?php endif;?>
</table>
</fieldset>
<div class="bk15"></div>
<fieldset>
	<legend><?php echo L('vlan')?></legend>
	<table width="100%"  class="table_form">
	<tr>
		<th width="80"><?php echo L('name')?>：</th>
		<td class="y-bg"><input type="text" class="input-text" name="name" id="name" size="30" value="<?php echo $edit_data['name']?>" /></td>
	</tr>
	<tr>
		<th width="80"><?php echo L('ispage')?>：</th>
		<td class="y-bg"><input type="text" name="page" id='page' value="<?php echo $edit_data['page']?>"/> <?php echo L('common_variables')?>:<a href="javascript:void(0);" onclick="javascript:$('#page').val('$_GET[page]');"><font color="red">$_GET[page]</font></a>、<a href="javascript:void(0);" onclick="javascript:$('#page').val('$page');"><font color="red">$page</font></a>，<?php echo L('no_input_no_page')?></td>
	</tr>
    <tr>
		<th width="80"><?php echo L('num')?>：</th>
		<td class="y-bg"><input type="text" name="num" id="num" size="30" value="<?php echo $edit_data['num']?>" /></td>
	</tr>
	<tr>
		<th width="80"><?php echo L('data_return')?>：</th>
		<td class="y-bg"><input type="text" name="return" id="return" size="30" value="<?php echo $edit_data['return']?>" /> </td>
	</tr>
	<tr>
		<th width="80"><?php echo L('cache_times')?>：</th>
		<td class="y-bg"><input type="text" name="cache" id="cache" size="30" value="<?php echo $edit_data['cache']?>" /> </td>
	</tr>

</table>
</fieldset>
<div class="bk15"></div>
    <input type="submit" class="dialog" id="dosubmit" name="dosubmit" value="" />
</div>
</div>
</form>
<script type="text/javascript">
<!--
	function showcode(obj) {
	if (obj==3){
		$('#template_code').show();
	} else {
		$('#template_code').hide();
	}
}
//-->
</script>
</body>
</html>