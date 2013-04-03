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
		<td><?php echo $siteurl?></td>
	</tr>
	<tr>
		<th width="100">UUID：</th>
		<td><?php echo $uuid;?></td>
	</tr>
	<tr>
		<th width="100">CHARSET：</th>
		<td><?php echo $charset;?></td>
	</tr>
	<tr>
		<th width="100">Version：</th>
		<td><?php echo $version;?></td>
	</tr>
	<tr>
		<th width="100">Release：</th>
		<td><?php echo $release;?></td>
	</tr>
	<tr>
		<th width="100">OS：</th>
		<td><?php echo $os;?></td>
	</tr>
	<tr>
		<th width="100">PHP：</th>
		<td><?php echo $php;?></td>
	</tr>
	<tr>
		<th width="100">Mysql：</th>
		<td><?php echo $mysql;?></td>
	</tr>
	<tr>
		<th width="100">Browser：</th>
		<td><?php echo $browser;?></td>
	</tr>
	<tr>
		<th width="100">UserName：</th>
		<td><?php echo $username;?></td>
	</tr>
	<tr>
		<th width="100">Email：</th>
		<td><?php echo $email;?></td>
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