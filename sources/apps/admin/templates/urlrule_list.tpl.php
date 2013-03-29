<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>

<div class="pad_10">
  <div class="table-list">
    <table width="100%" cellspacing="0">
      <thead>
        <tr>
          <th width="60">ID</th>
          <th><?php echo L('respective_application');?></th>
          <th><?php echo L('rulename');?></th>
          <th><?php echo L('urlrule_ishtml');?></th>
          <th><?php echo L('urlrule_example');?></th>
          <th><?php echo L('urlrule_url');?></th>
          <th width="100"><?php echo L('operations_manage');?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($infos as $r) { ?>
        <tr>
          <td align='center'><?php echo $r['urlruleid'];?></td>
          <td align="center"><?php echo $r['application'];?></td>
          <td align="center"><?php echo $r['file'];?></td>
          <td align="center"><?php echo $r['ishtml'] ? L('icon_unlock') : L('icon_locked');?></td>
          <td><?php echo $r['example'];?></td>
          <td><?php echo $r['urlrule'];?></td>
          <td align='center'><a
						href="javascript:edit('<?php echo $r['urlruleid']?>')"><?php echo L('edit');?></a> | <a
						href="<?php echo art_confirm(L('confirm',array('message'=>$r['urlruleid'])), '?app=admin&controller=urlrule&action=delete&urlruleid='.$r['urlruleid'].'&menuid='.$_GET['menuid'])?>"><?php echo L('delete');?></a></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div id="pages"><?php echo $pages;?></div>
  </div>
</div>
<script type="text/javascript">
<!--
function edit(id) {
	window.top.art.dialog.open('?app=admin&controller=urlrule&action=edit&urlruleid='+id,{
		title:'<?php echo L('edit_urlrule');?>《'+id+'》',
		id:'edit',
		width:'800px',
		height:'300px',
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
</body></html>