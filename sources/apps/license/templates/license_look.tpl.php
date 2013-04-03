<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<div class="pad_10">
<form action="" method="post" name="myform" id="myform">
<table cellpadding="2" cellspacing="1" class="table_form" width="100%">
	<tr>
		<th width="20%"><?php echo L('typeid')?>：</th>
		<td><?php echo $type_arr[$info['typeid']];?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('license_name')?>：</th>
		<td><?php echo $sitename?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('url')?>：</th>
		<td><?php echo $domain?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('truename')?>：</th>
		<td><?php echo $truename;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('telephone')?>：</th>
		<td><?php echo $telephone;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('mobile')?>：</th>
		<td><?php echo $mobile;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('email')?>：</th>
		<td><?php echo $email;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('msn')?>：</th>
		<td><?php echo $msn;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('qq')?>：</th>
		<td><?php echo $qq;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('address')?>：</th>
		<td><?php echo $address;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('postcode')?>：</th>
		<td><?php echo $postcode;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('uuid')?>：</th>
		<td><?php echo $uuid;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('starttime')?>：</th>
		<td><?php echo $starttime;?></td>
	</tr>
	<tr>
		<th width="100"><?php echo L('endtime')?>：</th>
		<td><?php echo $endtime;?></td>
	</tr>
	<tr>
		<th></th>
		<td><input type="hidden" name="forward" value="add"> <input type="submit" name="dosubmit" id="dosubmit" class="dialog" value=" <?php echo L('submit')?> "></td>
	</tr>
</table>
</form>
</div>
</body>
</html>