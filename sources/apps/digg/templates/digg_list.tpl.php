<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<div class="pad-lr-10">
<form name="searchform" action="" method="get" >
<input type="hidden" value="digg" name="app">
<input type="hidden" value="range" name="controller">
<input type="hidden" value="init" name="action">
<table width="100%" cellspacing="0" class="search-form">
    <tbody>
		<tr>
		<td>
		<div class="explain-col">
 		<?php echo L('category')?>：<?php echo Form::select_category('category_content', $catid, 'name="catid"', L('please_select'), '', 0, 1)?>
 		<?php echo L('time')?>：<?php echo Form::select(array('1'=>L('today'), '2'=>L('yesterday'), '3'=>L('this_week'), '4'=>L('this_month'), '5'=>L('all')), $datetype, 'name="datetype"')?>
 		<?php echo L('sort')?>：<?php echo Form::select($order_list, $order, 'name="order"')?>
				<input type="submit" name="search" class="button" value="<?php echo L('view')?>" />
	</div>
		</td>
		</tr>
    </tbody>
</table>
</form>
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
	<tr>
		<th align="left" width="300"><?php echo L('title')?></th>
			<th align="left"><?php echo L('total')?></th>
			<th align="left"><?php echo L('supports')?></th>
			<th align="left"><?php echo L('againsts')?></th>
		</tr>
	</thead>
<tbody>
<?php
	if(is_array($data) && !empty($data))foreach($data as $k=>$v) {
?>
    <tr>
		<td align="left"><a href="<?php echo $v['url']?>" target="_blank"><?php echo $v['title']?></a></td>
		<td align="left" <?php if ($order == -1) echo 'class="on"';?>><?php echo  $v['total']?></td>
		<td align="left" <?php if($order == 'supports') echo 'class="on"';?>><?php echo $v['supports']?></td>
		<td align="left" <?php if($order == 'againsts') echo 'class="on"';?>><?php echo $v['againsts']?></td>
    </tr>
<?php
	}
?>
</tbody>
</table>
<div id="pages"><?php echo $pages?></div>
</div>
</div>
</body>
</html>