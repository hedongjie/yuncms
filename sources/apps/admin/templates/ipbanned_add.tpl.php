<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<script type="text/javascript">
<!--
	$(function(){
		$.formValidator.initConfig({
			formid:"myform",
			autotip:true,
			onerror:function(msg,obj){
				window.top.art.dialog.alert(msg,function(){$(obj).focus();});
			}
		});
		$("#ip")
			.formValidator({
				onshow:"<?php echo L('input').L('ipbanned')?>",
				onfocus:"<?php echo L('input').L('ipbanned')?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L('input').L('ipbanned')?>"
			})
			.regexValidator({
				regexp:"notempty",
				datatype:"enum",
				param:'i',
				onerror:"<?php echo L('three_types')?>"
			})
			.ajaxValidator({
				type : "get",
				url : "?app=admin&controller=ipbanned&action=public_name",
				data :"",
				datatype : "html",
				async:'false',
				success : function(data){if( data == "1" ){return true;}else{return false;}},buttons: $("#dosubmit"),onerror : "<?php echo L('ip_exit')?>",onwait : "<?php echo L('connecting')?>"});
		$("#expires")
			.formValidator({onshow:"<?php echo L('input').L('deblocking_time')?>",onfocus:"<?php echo L('input').L('deblocking_time')?>",oncorrect:"<?php echo L('time_isok')?>"})
			.inputValidator({min:1,onerror:"<?php echo L('time_ismust')?>"});
	})
//-->
</script>

<div class="pad_10">
  <form action="<?php echo U('admin/ipbanned/add');?>" method="post"
		name="myform" id="myform">
    <table width="100%" cellpadding="2" cellspacing="1" class="table_form">
      <tr>
        <th width="60">IP :</th>
        <td><input type="text" name="info[ip]" id="ip" size="25"></td>
      </tr>
      <tr>
        <th><?php echo L('deblocking_time')?> :</th>
        <td><?php echo Form::date('info[expires]', '', '')?></td>
      </tr>
      <input type="submit" name="dosubmit" id="dosubmit" class="dialog"
				value=" <?php echo L('submit')?> ">
    </table>
  </form>
</div>
</body></html>