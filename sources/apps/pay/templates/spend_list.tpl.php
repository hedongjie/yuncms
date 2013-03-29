<?php
	defined('IN_ADMIN') or exit('No permission resources.');
	include $this->admin_tpl('header','admin');
?>
<div class="pad_10">
<div class="table-list">
<form name="searchform" action="" method="get" >
<input type="hidden" value="pay" name="app">
<input type="hidden" value="spend" name="controller">
<input type="hidden" value="init" name="action">
<input type="hidden" value="<?php echo $_GET['menuid']?>" name="menuid">
<table width="100%" cellspacing="0" class="search-form">
    <tbody>
		<tr>
		<td>
		<div class="explain-col">
		<?php echo  Form::select(array('1'=>L('username'), '2'=>L('userid')), $user_type, 'name="user_type"')?>： <input type="text" value="<?php echo $username?>" class="input-text" name="username">
		<?php echo L('from')?>  <?php echo Form::date('starttime',Format::date($starttime))?> <?php echo L('to')?>   <?php echo Form::date('endtime',Format::date($endtime))?>

		<?php echo  Form::select(array(''=>L('op'), '1'=>L('username'), '2'=>L('userid')), $op_type, 'name="op_type"')?>：
		<input type="text" value="<?php echo $op?>" class="input-text" name="op">
		<?php echo  Form::select(array(''=>L('expenditure_patterns'), '1'=>L('money'), '2'=>L('point')), $type, 'name="type"')?>
		<input type="submit" value="<?php echo L('search')?>" class="button" name="dosubmit">
		</div>
		</td>
		</tr>
    </tbody>
</table>
</form>
    <table width="100%" cellspacing="0">
        <thead>
            <tr>
            <th width="10%"><?php echo L('username')?></th>
            <th width="20%"><?php echo L('content_of_consumption')?></th>
            <th width="15%"><?php echo L('empdisposetime')?> </th>
            <th width="9%"><?php echo L('op')?></th>
            <th width="8%"><?php echo L('expenditure_patterns')?></th>
            <th width="8%"><?php echo L('consumption_quantity')?></th>
            </tr>
        </thead>
    <tbody>
 <?php
if(is_array($list)){
	$amount = $point = 0;
	foreach($list as $info){
?>
	<tr>
	<td width="10%" align="center"><?php echo $info['username']?></td>
	<td width="20%" align="center"><?php echo $info['msg']?></td>
	<td  width="15%" align="center"><?php echo Format::date($info['creat_at'], 1)?></td>
	<td width="9%" align="center"><?php if (!empty($info['op_userid'])) {echo $info['op_username'];} else {echo L('self');}?></td>
	<td width="8%" align="center"><?php if ($info['type'] == 1) {echo L('money');} elseif($info['type'] == 2) {echo L('point');}?></td>
	<td width="8%" align="center"><?php echo $info['value']?></td>
	</tr>
<?php
	}
}
?>
    </tbody>
    </table>

 <div id="pages"> <?php echo $pages?></div>
</div>
</div>
</form>
</body>
</html>
<script type="text/javascript">
<!--
	function discount(id, name) {
	window.top.art.dialog.open('?app=pay&controller=payment&action=public_discount&id='+id ,{
		title:'<?php echo L('discount')?>--'+name,
		id:'discount',
		width:'500px',
		height:'200px',
		yesFn: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
	    noFn: function(){}
	});
}
function detail(id, name) {
	window.top.art.dialog.open('?app=pay&controller=payment&action=public_pay_detail&id='+id ,{
		title:'<?php echo L('discount')?>--'+name,
		id:'discount',
		width:'500px',
		height:'550px'
	});
}
//-->
</script>