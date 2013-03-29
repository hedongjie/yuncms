<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
$show_dialog = 1;
include $this->admin_tpl ( 'header', 'admin' );
?>
<div class="pad-lr-10">
	<div class="table-list">
		<table width="100%" cellspacing="0">
			<thead>
				<tr>
					<th width="220" align="center"><?php echo L('applicationname')?></th>
					<th width='220' align="center"><?php echo L('applicationpath')?></th>
					<th width="14%" align="center"><?php echo L('versions')?></th>
					<th width='10%' align="center"><?php echo L('installdate')?></th>
					<th width="10%" align="center"><?php echo L('updatetime')?></th>
					<th width="12%" align="center"><?php echo L('operations_manage')?></th>
				</tr>
			</thead>
			<tbody>
 <?php
	if (is_array ( $directory )) {
		foreach ( $directory as $d ) {
			if (array_key_exists ( $d, $applications )) {
				?>
	<tr>
					<td align="center" width="220"><?php echo $applications[$d]['name']?></td>
					<td width="220" align="center"><?php echo $d?></td>
					<td align="center"><?php echo $applications[$d]['version']?></td>
					<td align="center"><?php echo $applications[$d]['installdate']?></td>
					<td align="center"><?php echo $applications[$d]['updatedate']?></td>
					<td align="center">
	<?php if ($applications[$d]['iscore']) {?><span style="color: #999"><?php echo L('ban')?></span><?php } else {?><a
						href="javascript:void(0);"
						onclick="if(confirm('<?php echo L('confirm', array('message'=>$applications[$d]['name']))?>')){uninstall('<?php echo $d?>');return false;}"><font
							color="red"><?php echo L('unload')?></font></a><?php }?>
	</td>
				</tr>
<?php
			} else {
				$application = $isinstall = $applicationname = '';
				if (file_exists ( APPS_PATH . $d . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'config.inc.php' )) {
					require APPS_PATH . $d . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'config.inc.php';
					$isinstall = L ( 'install' );
				} else {
					$application = L ( 'unknown' );
					$isinstall = L ( 'no_install' );
				}
				?>
	<tr class="on">
					<td align="center" width="220"><?php echo $applicationname?></td>
					<td width="220" align="center"><?php echo $d?></td>
					<td align="center"><?php echo L('unknown')?></td>
					<td align="center"><?php echo L('unknown')?></td>
					<td align="center"><?php echo L('uninstall_now')?></td>
					<td align="center">
	<?php if ($isinstall!=L('no_install')) {?> <a
						href="javascript:install('<?php echo $d?>');void(0);"><font
							color="#009933"><?php echo $isinstall?></font><?php } else {?><font
							color="#009933"><?php echo $isinstall?></font><?php }?></a>
					</td>
				</tr>
<?php }}}?>
</tbody>
		</table>
	</div>
	<div id="pages"><?php echo $pages?></div>
</div>
<script type="text/javascript">
<!--

	function install(id) {
		window.top.art.dialog.open('?app=admin&controller=application&action=install&application='+id,{
			title:'<?php echo L('application_istall')?>',
			id:'install',
			width:'500px',
			height:'260px',
			ok: function(iframeWin, topWin){
				var form = iframeWin.document.getElementById('dosubmit');
				form.click();
				return false;
			},
			cancel: function(){}
		});
	}

	function uninstall(id) {
		window.top.art.dialog.open('?app=admin&controller=application&action=uninstall&application='+id,{
			title:'<?php echo L('application_unistall', '', 'admin')?>',
			id:'install',
			width:'500px',
			height:'260px',
			ok: function(iframeWin, topWin){
				var form = iframeWin.document.getElementById('dosubmit');
				form.click();
				return false;
			},
			cancel: function(){}
		});
	}
//-->
</script>
</body>
</html>