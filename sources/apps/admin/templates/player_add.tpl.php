<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<?php if($_GET['action']!='edit'){ ?>
<script type="text/javascript">
<!--
	$(function(){
		$.formValidator.initConfig({
			formid:"myform",
			autotip:true,
			onerror:function(msg,obj){
				window.top.art.dialog({content:msg,lock:true,width:'200',height:'50'}, function(){this.close();$(obj).focus();})}});
		$("#subject").formValidator({onshow:"<?php echo L('input').L('player_name');?>",
			onfocus:"<?php echo L('input').L('player_name')?>"}).inputValidator({min:1,onerror:"<?php echo L('input').L('player_name')?>"})
			.ajaxValidator({type : "get",url : "",data :"app=admin&controller=player&action=public_name",datatype : "html",async:'false',
				success : function(data){	if( data == "1" ){return true;}else{return false;}},buttons: $("#dosubmit"),onerror : "<?php echo L('player_name').L('exists')?>",
				onwait : "<?php echo L('connecting')?>"});
	})
//-->
</script>
<?php } ?>

<div class="pad_10">
	<form action="<?php echo U('admin/player/add');?>" method="post"
		name="myform" id="myform">
		<table width="100%" cellpadding="2" cellspacing="1" class="table_form">
			<tr>
				<th width="70"><?php echo L('player_name');?> :</th>
				<td><input type="text" name="info[subject]" id="subject" size="25"
					value=""></td>
			</tr>
			<tr>
				<th><?php echo L('player_code');?> :</th>
				<td><TEXTAREA NAME="info[code]" ROWS="15" COLS="50" id="code"></TEXTAREA><br><?php echo L('player_code_note');?></td>
			</tr>

			<input type="submit" name="dosubmit" id="dosubmit" class="dialog"
				value=" <?php echo L('submit')?> ">

		</table>
	</form>
</div>
</body>
</html>