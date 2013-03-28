<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header', 'admin' );
?>
<div class="pad-10">
	<form method="post"
		action="?app=announce&controller=admin_announce&action=edit&aid=<?php echo $_GET['aid']?>"
		name="myform" id="myform">
		<table class="table_form" width="100%">
			<tbody>
				<tr>
					<th width="80"><?php echo L('announce_title')?></th>
					<td><input name="announce[title]" id="title"
						value="<?php echo htmlspecialchars($an_info['title'])?>"
						class="input-text" type="text" size="50"></td>
				</tr>
				<tr>
					<th><?php echo L('startdate')?>：</th>
					<td><?php echo Form::date('announce[starttime]', $an_info['starttime'])?></td>
				</tr>
				<tr>
					<th><?php echo L('enddate')?>：</th>
					<td><?php $an_info['endtime'] = $an_info['endtime']=='0000-00-00' ? '' : $an_info['endtime']; echo Form::date('announce[endtime]', $an_info['endtime']);?></td>
				</tr>
				<tr>
					<th><?php echo L('announce_content')?></th>
					<td><textarea name="announce[content]" id="content"><?php echo $an_info['content']?></textarea>
		<?php echo Form::editor('content','basic');?>
		</td>
				</tr>
				<tr>
					<th><strong><?php echo L('available_style')?>：</strong></th>
					<td><?php echo Form::select($template_list, $an_info['style'], 'name="announce[style]" id="style" onchange="load_file_list(this.value)"', L('please_select'))?></td>
				</tr>
				<tr>
					<th><?php echo L('template_select')?>：</th>
					<td id="show_template"><?php if ($an_info['style']) echo '<script type="text/javascript">$.getJSON(\'?app=admin&controller=category&action=public_tpl_file_list&style='.$an_info['style'].'&id='.$an_info['show_template'].'&application=announce&templates=show&name=announce\', function(data){$(\'#show_template\').html(data.show_template);});</script>'?></td>
				</tr>
				<tr>
					<th><?php echo L('announce_status')?></th>
					<td><input name="announce[passed]" type="radio" value="1"
						<?php if($an_info['passed']==1) {?> checked <?php }?>></input>&nbsp;<?php echo L('pass')?>&nbsp;&nbsp;<input
						name="announce[passed]" type="radio" value="0"
						<?php if($an_info['passed']==0) {?> checked <?php }?>>&nbsp;<?php echo L('unpass')?></td>
				</tr>
			</tbody>
		</table>
		<input type="submit" name="dosubmit" id="dosubmit"
			value=" <?php echo L('ok')?> " class="dialog">&nbsp;<input
			type="reset" class="dialog" value=" <?php echo L('clear')?> ">
	</form>
</div>
</body>
</html>
<script type="text/javascript">
function load_file_list(id) {
	$.getJSON('?app=admin&controller=category&action=public_tpl_file_list&style='+id+'&application=announce&templates=show&name=announce', function(data){$('#show_template').html(data.show_template);});
}

$(document).ready(function(){
	$.formValidator.initConfig({
		formid:"myform",
		autotip:true,
		onerror:function(msg,obj){
			window.top.art.dialog({content:msg,lock:true,width:'220',height:'70'}, function(){t
				his.close();$(obj).focus();})
		}
	});
	$('#title')
		.formValidator({
			onshow:"<?php echo L('input_announce_title')?>",
			onfocus:"<?php echo L('title_min_3_chars')?>",
			oncorrect:"<?php echo L('right')?>"
		})
		.inputValidator({
			min:1,
			onerror:"<?php echo L('title_cannot_empty')?>"
		})
		.ajaxValidator({
			type:"get",
			url:"?app=announce&controller=admin_announce&action=public_check_title&aid=<?php echo $_GET['aid']?>",
			data:"",
			datatype:"html",
			cached:false,
			async:'true',
			success : function(data) {
        		if( data == "1" ){
            		return true;
				}else{
            		return false;
				}
			},
			error: function(){alert("<?php echo L('server_no_data')?>");},
			onerror : "<?php echo L('announce_exist')?>",
			onwait : "<?php echo L('checking')?>"
		})
		.defaultPassed();
	$('#starttime')
		.formValidator({
			onshow:"<?php echo L('select_stardate')?>",
			onfocus:"<?php echo L('select_stardate')?>",
			oncorrect:"<?php echo L('right_all')?>"
		})
		.defaultPassed();
	$('#endtime')
		.formValidator({
			onshow:"<?php echo L('select_downdate')?>",
			onfocus:"<?php echo L('select_downdate')?>",
			oncorrect:"<?php echo L('right_all')?>"
		})
		.defaultPassed();
	$("#content")
		.formValidator({
			autotip:true,
			onshow:"",
			onfocus:"<?php echo L('announcements_cannot_be_empty')?>"
		})
		.functionValidator({
	    	fun:function(val,elem){
	    		//获取编辑器中的内容
		    	var data = editor_content.getContent();
		        if(data == ''){
		    	    return "<?php echo L('announcements_cannot_be_empty')?>"
		        } else {
		    		return true;
		    	}
			}
		})
		.defaultPassed();
	$('#style')
		.formValidator({
			onshow:"<?php echo L('select_style')?>",
			onfocus:"<?php echo L('select_style')?>",
			oncorrect:"<?php echo L('right_all')?>"
		})
		.inputValidator({
			min:1,
			onerror:"<?php echo L('select_style')?>"
		})
		.defaultPassed();
});
</script>