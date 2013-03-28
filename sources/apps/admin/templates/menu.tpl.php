<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<?php if(ACTION=='init') {?>

<form name="myform" action="?app=admin&controller=menu&action=listorder"
	method="post">
  <div class="pad-lr-10">
    <div class="table-list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <th width="80"><?php echo L('listorder');?></th>
            <th width="100">id</th>
            <th><?php echo L('menu_name');?></th>
            <th><?php echo L('operations_manage');?></th>
          </tr>
        </thead>
        <tbody>
          <?php echo $menus;?>
        </tbody>
      </table>
      <div class="btn">
        <input type="submit" class="button" name="dosubmit"
					value="<?php echo L('listorder')?>" />
      </div>
    </div>
  </div>
  </div>
</form>
</body>
</html>
<?php } elseif(ACTION=='add') {?>
<script type="text/javascript">
<!--
	$(function(){
		$.formValidator.initConfig({formid:"myform",autotip:true,onError:function(msg,obj){window.art.dialog.alert(msg);$(obj).focus();}});
		$("#language")
			.formValidator({
				onshow:"<?php echo L("input").L('chinese_name')?>",
				onfocus:"<?php echo L("input").L('chinese_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('chinese_name')?>"
			});
		$("#name")
			.formValidator({
				onshow:"<?php echo L("input").L('menu_name')?>",
				onfocus:"<?php echo L("input").L('menu_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('menu_name')?>"
			});
		$("#application")
			.formValidator({
				onshow:"<?php echo L("input").L('application_name')?>",
				onfocus:"<?php echo L("input").L('application_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('application_name')?>"
			});
		$("#controller")
			.formValidator({
				onshow:"<?php echo L("input").L('controller_name')?>",
				onfocus:"<?php echo L("input").L('controller_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('controller_name')?>"
			});
		$("#action")
			.formValidator({
				tipid:'action_tip',
				onshow:"<?php echo L("input").L('action_name')?>",
				onfocus:"<?php echo L("input").L('action_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('action_name')?>"
			});
	})
//-->
</script>
<div class="common-form">
<form name="myform" id="myform"
		action="?app=admin&controller=menu&action=add" method="post">
  <table width="100%" class="table_form contentWrap">
    <tr>
      <th width="200"><?php echo L('menu_parentid')?>：</th>
      <td><select name="info[parentid]">
          <option value="0"><?php echo L('no_parent_menu')?></option>
          <?php echo $select_menus;?>
        </select></td>
    </tr>
    <tr>
      <th> <?php echo L('chinese_name')?>：</th>
      <td><input type="text" name="language" id="language"
					class="input-text"></td>
    </tr>
    <tr>
      <th><?php echo L('menu_name')?>：</th>
      <td><input type="text" name="info[name]" id="name"
					class="input-text"></td>
    </tr>
    <tr>
      <th><?php echo L('application_name')?>：</th>
      <td><input type="text" name="info[application]" id="application"
					class="input-text"></td>
    </tr>
    <tr>
      <th><?php echo L('controller_name')?>：</th>
      <td><input type="text" name="info[controller]" id="controller"
					class="input-text"></td>
    </tr>
    <tr>
      <th><?php echo L('action_name')?>：</th>
      <td><input type="text" name="info[action]" id="action"
					class="input-text">
        <span id="action_tip"></span><?php echo L('ajax_tip')?></td>
    </tr>
    <tr>
      <th><?php echo L('att_data')?>：</th>
      <td><input type="text" name="info[data]" class="input-text"></td>
    </tr>
    <tr>
      <th><?php echo L('menu_display')?>：</th>
      <td><input type="radio" name="info[display]" value="1" checked>
        <?php echo L('yes')?>
        <input
					type="radio" name="info[display]" value="0">
        <?php echo L('no')?></td>
    </tr>
  </table>
  <!--table_form_off-->

  </div>
  <div class="bk15"></div>
  <div class="btn">
    <input type="submit" id="dosubmit" class="button" name="dosubmit"
		value="<?php echo L('submit')?>" />
  </div>
  </div>
</form>
<?php } elseif(ACTION=='edit') {?>
<script type="text/javascript">
<!--
	$(function(){
		$.formValidator.initConfig({
			formid:"myform",
			autotip:true,
			onerror:function(msg,obj){
				window.top.art.dialog({
					content:msg,
					lock:true,
					width:'200px',height:'50px'
				 },function(){this.close();$(obj).focus();}
				)
			}
		});
		$("#language")
			.formValidator({
				onshow:"<?php echo L("input").L('chinese_name')?>",
				onfocus:"<?php echo L("input").L('chinese_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('chinese_name')?>"
			});
		$("#name")
			.formValidator({
				onshow:"<?php echo L("input").L('menu_name')?>",
				onfocus:"<?php echo L("input").L('menu_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('menu_name')?>"
			});
		$("#application")
			.formValidator({
				onshow:"<?php echo L("input").L('application_name')?>",
				onfocus:"<?php echo L("input").L('application_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('application_name')?>"
			});
		$("#controller")
			.formValidator({
				onshow:"<?php echo L("input").L('controller_name')?>",
				onfocus:"<?php echo L("input").L('controller_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('controller_name')?>"
			});
		$("#action")
			.formValidator({
				tipid:'action_tip',
				onshow:"<?php echo L("input").L('action_name')?>",
				onfocus:"<?php echo L("input").L('action_name')?>",
				oncorrect:"<?php echo L('input_right');?>"
			})
			.inputValidator({
				min:1,
				onerror:"<?php echo L("input").L('action_name')?>"
			});
	})
//-->
</script>
<div class="common-form">
<form name="myform" id="myform"
		action="?app=admin&controller=menu&action=edit" method="post">
  <table width="100%" class="table_form contentWrap">
    <tr>
      <th width="200"><?php echo L('menu_parentid')?>：</th>
      <td><select name="info[parentid]" style="width: 200px;">
          <option value="0"><?php echo L('no_parent_menu')?></option>
          <?php echo $select_menus;?>
        </select></td>
    </tr>
    <tr>
      <th> <?php echo L('chinese_name')?>：</th>
      <td><input type="text" name="language" id="language"
					class="input-text" value="<?php echo L($name)?>"></td>
    </tr>
    <tr>
      <th><?php echo L('menu_name')?>：</th>
      <td><input type="text" name="info[name]" id="name"
					class="input-text" value="<?php echo $name?>"></td>
    </tr>
    <tr>
      <th><?php echo L('application_name')?>：</th>
      <td><input type="text" name="info[application]" id="application"
					class="input-text" value="<?php echo $application?>"></td>
    </tr>
    <tr>
      <th><?php echo L('controller_name')?>：</th>
      <td><input type="text" name="info[controller]" id="controller"
					class="input-text" value="<?php echo $controller?>"></td>
    </tr>
    <tr>
      <th><?php echo L('action_name')?>：</th>
      <td><input type="text" name="info[action]" id="action"
					class="input-text" value="<?php echo $action?>">
        <span
					id="action_tip"></span><?php echo L('ajax_tip')?></td>
    </tr>
    <tr>
      <th><?php echo L('att_data')?>：</th>
      <td><input type="text" name="info[data]" class="input-text"
					value="<?php echo $data?>"></td>
    </tr>
    <tr>
      <th><?php echo L('menu_display')?>：</th>
      <td><input type="radio" name="info[display]" value="1"
					<?php if($display) echo 'checked';?>>
        <?php echo L('yes')?>
        <input
					type="radio" name="info[display]" value="0"
					<?php if(!$display) echo 'checked';?>>
        <?php echo L('no')?></td>
    </tr>
  </table>
  <!--table_form_off-->

  </div>
  <div class="bk15"></div>
  <input name="id" type="hidden" value="<?php echo $id?>">
  <div class="btn">
    <input type="submit" id="dosubmit" class="button" name="dosubmit"
		value="<?php echo L('submit')?>" />
  </div>
  </div>
</form>
<?php }?>
</body></html>