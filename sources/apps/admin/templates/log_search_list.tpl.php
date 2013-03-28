<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
$show_dialog = 1;
include $this->admin_tpl ( 'header', 'admin' );
?>
<div class="pad-lr-10">
	<form name="searchform"
		action="<?php echo U('admin/log/search_log');?>" method="get">
		<input type="hidden" value="admin" name="app"> <input type="hidden"
			value="log" name="controller"> <input type="hidden"
			value="search_log" name="action">
		<table width="100%" cellspacing="0" class="search-form">
			<tbody>
				<tr>
					<td><div class="explain-col"><?php echo L('application')?>: <?php echo Form::select($application_arr,'','name="search[application]"',$default)?> <?php echo L('username')?>  <input
								type="text" value="<?php echo $_GET['search']['username'];?>"
								class="input-text" name="search[username]" size='10'>  <?php echo L('times')?>  <?php echo Form::date('search[start_time]',$_GET['search']['start_time'],'1')?> <?php echo L('to')?>   <?php echo Form::date('search[end_time]',$_GET['search']['end_time'],'1')?>    <input
								type="submit" value="<?php echo L('determine_search')?>"
								class="button" name="dosubmit">
						</div></td>
				</tr>
			</tbody>
		</table>
	</form>
	<div class="table-list">
		<table width="100%" cellspacing="0">
			<thead>
				<tr>
					<th width="80"><?php echo L('username')?></th>
					<th><?php echo L('application')?></th>
					<th><?php echo L('file')?></th>
					<th width="120"><?php echo L('time')?></th>
					<th width="120">IP</th>
				</tr>
			</thead>
			<tbody>
 <?php
	if (is_array ( $infos )) {
		foreach ( $infos as $info ) {
			?>
    <tr>
					<td align="center"><?php echo $info['username']?></td>
					<td align="center"><?php echo $info['application']?></td>
					<td align="left" title="<?php echo $info['querystring']?>"><?php echo str_cut($info['querystring'], 60);?></td>
					<td align="center"><?php echo $info['time'];//echo $info['lastusetime'] ? date('Y-m-d H:i', $info['lastusetime']):''?></td>
					<td align="center"><?php echo $info['ip']?>　</td>
				</tr>
<?php
		}
	}
	?></tbody>
		</table>

		<div id="pages"><?php echo $pages?></div>
	</div>
</div>
</body>
</html>
