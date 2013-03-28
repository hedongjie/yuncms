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
				window.top.art.dialog.alert(msg);
				$(obj).focus();
			}
		});
		$("#sitename")
			.formValidator({
				onshow:"<?php echo L('input').L('copyfrom_name');?>",
				onfocus:"<?php echo L('input').L('copyfrom_name');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L('input').L('copyfrom_name');?>"
			})
			.defaultPassed();
		$("#siteurl")
			.formValidator({
				onshow:"<?php echo L('input').L('copyfrom_url');?>",
				onfocus:"<?php echo L('input').L('copyfrom_url');?>",
				empty:false
			})
			.inputValidator({
				onerror:"<?php echo L('input').L('copyfrom_url');?>"
			})
			.regexValidator({
				regexp:"^http://",
				onerror:"<?php echo L('copyfrom_url_tips');?>"
			})
			.defaultPassed();
	})
//-->
</script>

<div class="pad_10">
  <form action="<?php echo U('admin/copyfrom/edit');?>" method="post"
		name="myform" id="myform">
    <table width="100%" cellpadding="2" cellspacing="1" class="table_form">
      <tr>
        <th width="60"><?php echo L('copyfrom_name');?> :</th>
        <td><input type="text" name="info[sitename]" id="sitename" size="25"
					value="<?php echo $sitename;?>"></td>
      </tr>
      <tr>
        <th><?php echo L('copyfrom_url')?> :</th>
        <td><input type="text" name="info[siteurl]" id="siteurl" size="25"
					value="<?php echo $siteurl;?>"></td>
      </tr>
      <tr>
        <th><?php echo L('copyfrom_logo')?> :</th>
        <td><?php echo Form::images('info[thumb]', 'thumb', $thumb, 'admin')?></td>
      </tr>
      <input type="hidden" name="id" value="<?php echo $id?>">
      <input type="submit" name="dosubmit" id="dosubmit" class="dialog"
				value=" <?php echo L('submit')?> ">
    </table>
  </form>
</div>
</body></html>