<?php
	defined('IN_ADMIN') or exit('No permission resources.');
	include $this->admin_tpl('header','admin');
?>
<div class="pad_10">
<div class="table-list">
<form name="searchform" action="" method="get" >
<input type="hidden" value="pay" name="app">
<input type="hidden" value="payment" name="controller">
<input type="hidden" value="pay_list" name="action">
<input type="hidden" value="<?php echo $_GET['menuid']?>" name="menuid">
<div class="explain-col search-form">
<?php echo L('order_sn')?>  <input type="text" value="<?php echo $trade_sn?>" class="input-text" name="info[trade_sn]">
<?php echo L('username')?>  <input type="text" value="<?php echo $username?>" class="input-text" name="info[username]">
<?php echo L('addtime')?>  <?php echo Form::date('info[start_addtime]',$start_addtime)?><?php echo L('to')?>   <?php echo Form::date('info[end_addtime]',$end_addtime)?>
<?php echo Form::select($trade_status,$status,'name="info[status]"', L('all_status'))?>
<input type="submit" value="<?php echo L('search')?>" class="button" name="dosubmit">
</div>
</form>
    <table width="100%" cellspacing="0">
        <thead>
            <tr>
            <th width="10%"><?php echo L('username')?></th>
            <th width="20%"><?php echo L('order_sn')?></th>
            <th width="15%"><?php echo L('order_time')?></th>
            <th width="9%"><?php echo L('business_mode')?></th>
            <th width="8%"><?php echo L('payment_mode')?></th>
            <th width="8%"><?php echo L('deposit_amount')?></th>
            <th width="10%"><?php echo L('pay_status')?></th>
            <th width="20%"><?php echo L('operations_manage')?></th>
            </tr>
        </thead>
    <tbody>
 <?php
if(is_array($infos)){
	$sum_amount = $sum_amount_succ = $sum_point_succ = $sum_point = '0';
	foreach($infos as $info){
		if($info['type'] == 1) {
			$num_amount++;
			$sum_amount += $info['money'];
			if($info['status'] =='succ') $sum_amount_succ += $info['money'];
		}  elseif ($info['type'] == 2) {
			$num_point++;
			$sum_point += $info['money'];
			if($info['status'] =='succ') $sum_point_succ += $info['money'];
		}

?>
	<tr>
	<td width="10%" align="center"><?php echo $info['username']?></td>
	<td width="20%" align="center"><?php echo $info['trade_sn']?> <a href="javascript:void(0);" onclick="detail('<?php echo $info['id']?>', '<?php echo $info['trade_sn']?>')"><img src="<?php echo IMG_PATH?>admin_img/detail.png"></a></td>
	<td  width="15%" align="center"><?php echo date('Y-m-d H:i:s',$info['addtime'])?></td>
	<td width="9%" align="center"><?php echo L($info['pay_type'])?></td>
	<td width="8%" align="center"><?php echo $info['payment']?></td>
	<td width="8%" align="center"><?php echo $info['money']?> <?php echo ($info['type']==1) ? L('yuan') : L('dian')?></td>
	<td width="10%" align="center"><?php echo L($info['status'])?> </a>
	<td width="20%" align="center">
	<?php if($info['status'] =='succ' || $info['status'] =='error' || $info['status'] =='failed' ||$info['status'] =='timeout' || $info['status'] =='cancel') {?>
	<font color="#cccccc"><?php echo L('change_price')?>  | <?php echo L('closed')?>  |</font> <a href="<?php echo art_confirm(L('trade_record_del'), '?app=pay&controller=payment&action=pay_del&id='.$_GET['menuid']);?>"><?php echo L('delete')?></a>
	<?php } elseif($info['status'] =='waitting' ) {?>
	<a href="javascript:confirmurl('?app=pay&controller=payment&action=public_check&id=<?php echo $info['id']?>', '<?php echo L('check_confirm',array('sn'=>$info['trade_sn']))?>')"><?php echo L('check')?></a> | <a href="?app=pay&controller=payment&action=pay_cancel&id=<?php echo $info['id']?>"><?php echo L('closed')?></a>  | <a href="<?php echo art_confirm(L('trade_record_del'), '?app=pay&controller=payment&action=pay_del&id='.$info['id'].'&menuid='.$_GET['menuid'])?>"><?php echo L('delete')?></a>
	<?php } else {?>
	<a href="javascript:void(0);" onclick="discount('<?php echo $info['id']?>', '<?php echo $info['trade_sn']?>')"><?php echo L('change_price')?></a> | <a href="?app=pay&controller=payment&action=pay_cancel&id=<?php echo $info['id']?>"><?php echo L('closed')?></a>  | <a href="<?php echo art_confirm(L('trade_record_del'), '?app=pay&controller=payment&action=pay_del&id='.$info['id'].'&menuid='.$_GET['menuid'])?>"><?php echo L('delete')?></a>


	<?php }?>
	</td>
	</tr>
<?php
	}
}
?>
    </tbody>
    </table>
<div class="btn text-r">
<?php echo L('thispage').L('totalize')?>  <span class="font-fixh green"><?php echo $number?></span> <?php echo L('bi').L('trade')?>(<?php echo L('money')?>：<span class="font-fixh"><?php echo $num_amount?></span><?php echo L('bi')?>，<?php echo L('point')?>：<span class="font-fixh"><?php echo $num_point?></span><?php echo L('bi')?>)，<?php echo L('total').L('amount')?>：<span class="font-fixh green"><?php echo $sum_amount?></span><?php echo L('yuan')?> ,<?php echo L('trade_succ').L('trade')?>：<span class="font-fixh green"><?php echo $sum_amount_succ?></span><?php echo L('yuan')?> ，总点数：<span class="font-fixh green"><?php echo $sum_point?></span><?php echo L('dian')?> ,<?php echo L('trade_succ').L('trade')?>：<span class="font-fixh green"><?php echo $sum_point_succ?></span><?php echo L('dian')?>
</div>
 <div id="pages"> <?php echo $pages?></div>
</div>
</div>
</form>
</body>
</html>
<script type="text/javascript">
<!--
	function discount(id, name) {
	window.top.art.dialog.open('?app=pay&controller=payment&action=discount&id='+id ,{
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