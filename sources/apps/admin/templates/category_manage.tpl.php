<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<form name="myform" action="<?php echo U('admin/category/listorder');?>"
	method="post">
	<div class="pad_10">
		<div class="explain-col">
<?php echo L('category_cache_tips');?>，<a
				href="<?php echo U('admin/category/public_cache');?>"><?php echo L('update_cache');?></a>
		</div>
		<div class="bk10"></div>
		<div class="table-list">
			<table width="100%" cellspacing="0">
				<thead>
					<tr>
						<th width="38"><?php echo L('listorder');?></th>
						<th width="30">CatId</th>
						<th><?php echo L('catname');?></th>
						<th align='left' width='50'><?php echo L('category_type');?></th>
						<th align='left' width="50"><?php echo L('modelname');?></th>
						<th align='center' width="40"><?php echo L('items');?></th>
						<th align='center' width="30"><?php echo L('vistor');?></th>
						<th align='center' width="80"><?php echo L('domain_help');?></th>
						<th><?php echo L('operations_manage');?></th>
					</tr>
				</thead>
				<tbody>
    <?php echo $categorys;?>
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
<script language="JavaScript">
<!--
	window.top.$('#display_center_id').css('display','none');
//-->
</script>
</body>
</html>
