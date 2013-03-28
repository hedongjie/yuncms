<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<script type="text/javascript">
	$(function(){
		$.formValidator.initConfig({
			formid:"myform",
			autotip:true,
			onerror:function(msg,obj){
				window.top.art.dialog.alert(msg,function(){
					$(obj).focus();
					}
				);
			}
		});
		$("#badword")
			.formValidator({onshow:"<?php echo L("input").L('badword_name')?>",onfocus:"<?php echo L("input").L('badword_name')?>"})
			.inputValidator({min:1,onerror:"<?php echo L("input").L('badword_name')?>"})
			.regexValidator({regexp:"notempty",datatype:"enum",param:'i',onerror:"<?php echo L('site_dirname_err_msg')?>"})
			.ajaxValidator({type : "get",url : "?app=admin&controller=badword&action=public_name",data :"",datatype : "html",async:'false',success : function(data){if( data == "1" ){return true;}else{return false;}},buttons: $("#dosubmit"),onerror : "<?php echo L('badword_name').L('exists')?>",onwait : "<?php echo L('connecting')?>"});
 		$("#replaceword")
 			.formValidator({empty:true,onshow:"<?php echo L('badword_noreplace')?>",onfocus:"<?php echo L("input").L('badword_replacename')?>",oncorrect:"<?php echo L('format_right')?>",onempty:"<?php echo L('badword_notreplace')?>"})
 	 	 	.inputValidator({min:1,onerror:"<?php echo L("input").L('badword_replacename')?>"});
	})
</script>

<div class="pad_10">
  <table cellpadding="2" cellspacing="1" class="table_form" width="100%">
    <form action="?app=admin&controller=badword&action=add" method="post"
			name="myform" id="myform">
      <tr>
        <th width="20%"> <?php echo L('badword_name')?> :</th>
        <td><input type="text" name="badword" id="badword" size="20"></td>
      </tr>
      <tr>
        <th width="20%"> <?php echo L('badword_replacename')?> :</th>
        <td><input type="text" name="replaceword" id="replaceword" size="20"></td>
      </tr>
      <tr>
        <th width="20%"> <?php echo L('badword_level')?> :</th>
        <td><select size="1" id="workflowid" name="info[level]">
            <option selected="" value="1"><?php echo L('badword_common')?></option>
            <option value="2"><?php echo L('badword_dangerous')?></option>
          </select>
          <?php echo L('badword_level_info')?></td>
      </tr>
      <input type="submit" name="dosubmit" id="dosubmit" class="dialog"
				value=" <?php echo L('submit')?> ">
    </form>
  </table>
</div>
</body></html>