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
			});
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
			});
		$("#return")
			.formValidator({
				onshow:"<?php echo L("please_enter_the_data_returned_value_by_default")?>：data。",
				onfocus:"<?php echo L("please_enter_the_data_returned_value_by_default")?>：data。",
				empty:true
			});
		show_do('<?php echo $_GET['do']?>');
	})

	function show_do(obj) {
		$('.xt_action_list').hide();
		$('#action_'+obj).show();
	}
//-->
</script>
<div class="pad-10">
<form action="?app=template&controller=file&action=edit_yun_tag&style=<?php echo $this->style?>&dir=<?php echo $dir?>&file=<?php echo urlencode($file)?>&op=<?php echo $op?>&tag_md5=<?php echo $_GET['tag_md5']?>" method="post" id="myform">
<fieldset>
	<legend><?php echo L("application_configuration")?></legend>
<table width="100%"  class="table_form">
	  <tr>
    <th width="80"><?php echo L("application")?>：</th>
    <td class="y-bg"><?php echo $op?></td>
  </tr>
    <tr>
    <th width="80"><?php echo L('operation')?>：</th>
    <td class="y-bg"> <?php if(isset($html['do']) && is_array($html['do'])) {
	    foreach($html['do'] as $key=>$value) {
				$checked = $_GET['do']==$key ? 'checked' : '';
				echo '<label><input type="radio" name="do" onclick="location.href=\'?'.creat_url($key).'\'" '.$checked.' value="'.$key.'"> '.$value."</label>";
			}
    }?></td>
  </tr>

  <?php
  if(isset($html[$_GET['do']]) && is_array($html[$_GET['do']])){
  	foreach($html[$_GET['do']] as $k=>$v){ ?>
  	  <tr>
    <th width="80"><?php echo $v['name']?>：</th>
    <td class="y-bg"><?php echo creat_form($k,$v,$_GET[$k], $op)?></td>
  </tr>
  <?php if(isset($v['ajax']['name'])  && !empty($v['ajax']['name'])) {?>
  	  <tr>
  	  	<th width="80"><?php echo $v['ajax']['name']?>：<?php if(isset($_GET[$v['ajax']['id']]) && !empty($_GET[$v['ajax']['id']])) echo '<script type="text/javascript">$.get(\'?app=template&controller=file&action=public_ajax_get\', { html: \''.$_GET[$k].'\', id:\''.$v['ajax']['id'].'\', value:\''.$_GET[$v['ajax']['id']].'\', do: \''.$v['ajax']['do'].'\', op: \''.$op.'\', style: \'default\'}, function(data) {$(\'#'.$k.'_td\').html(data)});</script>'?></th>
  	  	<td class="y-bg"><input type="text" size="20" value="<?php echo $_GET[$v['ajax']['id']]?>" id="<?php echo $v['ajax']['id']?>" name="<?php echo $v['ajax']['id']?>" class="input-text"><span id="<?php echo $k?>_td"></span></td>
  	 </tr>
  <?php }}}?>

</table>
</fieldset>
<div class="bk15"></div>
<fieldset>
	<legend><?php echo L('vlan')?></legend>
		<table width="100%"  class="table_form">
	  <tr>
    <th width="80"><?php echo L("public_allowpageing")?>：</th>
    <td class="y-bg"><input type="radio" name="page" value="$page"<?php if (isset($_GET['page'])) {echo ' checked';}?> /> <?php echo L("yes")?>  <input type="radio" name="page" value=""<?php if (!isset($_GET['page'])) {echo ' checked';}?> /> <?php echo L("no")?></td>
  </tr>
    <tr>
    <th width="80"><?php echo L("num")?>：</th>
    <td class="y-bg"><input type="text" name="num" id="num" size="30" value="<?php echo $_GET['num']?>" /></td>
  </tr>
   <tr>
    <th width="80"><?php echo L("check")?>：</th>
    <td class="y-bg"><input type="text" name="return" id="return" size="30" value="<?php echo $_GET['return']?>" /> </td>
  </tr>
   <tr>
    <th width="80"><?php echo L("buffer_time")?>：</th>
    <td class="y-bg"><input type="text" name="cache" id="cache" size="30" value="<?php echo $_GET['cache']?>" /> </td>
  </tr>
</table>
</fieldset>
<input type="submit" class="dialog" id="dosubmit" name="dosubmit" value="<?php echo L('submit')?>" />
</div>
</form>
</body>
</html>