<?php defined('IN_ADMIN') or exit('No permission resources.');?>
<?php include $this->admin_tpl('header', 'admin');?>
<div class="pad-lr-10">

<form name="myform" action="?app=collection&controller=node&action=del" method="post" onsubmit="return confirm('<?php echo L('sure_delete')?>')">
<div class="table-list">
<table width="100%" cellspacing="0">
	<thead>
		<tr>
			<th  align="left" width="20"><input type="checkbox" value="" id="check_box" onclick="selectall('nodeid[]');"></th>
			<th align="left">ID</th>
			<th align="left"><?php echo L('nodename')?></th>
			<th align="left"><?php echo L('lastdate')?></th>
			<th align="left"><?php echo L('content').L('operation')?></th>
			<th align="left"><?php echo L('operation')?></th>
		</tr>
	</thead>
<tbody>
<?php
	foreach($nodelist as $k=>$v) {
?>
    <tr>
		<td align="left"><input type="checkbox" value="<?php echo $v['nodeid']?>" name="nodeid[]"></td>
		<td align="left"><?php echo $v['nodeid']?></td>
		<td align="left"><?php echo $v['name']?></td>
		<td align="left"><?php echo Format::date($v['lastdate'], 1)?></td>
		<td align="left"><a href="?app=collection&controller=node&action=col_url_list&nodeid=<?php echo $v['nodeid']?>">[<?php echo L('collection_web_site')?>]</a>
		<a href="?app=collection&controller=node&action=col_content&nodeid=<?php echo $v['nodeid']?>">[<?php echo L('collection_content')?>]</a>
		 <a href="?app=collection&controller=node&action=publist&nodeid=<?php echo $v['nodeid']?>&status=2" style="color:red">[<?php echo L('public_content')?>]</a>
		</td>
		<td align="left">
		<a href="javascript:void(0)" onclick="test_spider(<?php echo $v['nodeid']?>)">[<?php echo L('test')?>]</a>

		<a href="?app=collection&controller=node&action=edit&nodeid=<?php echo $v['nodeid']?>&menuid=212">[<?php echo L('edit')?>]</a>
		 <a href="javascript:void(0)"  onclick="copy_spider(<?php echo $v['nodeid']?>)">[<?php echo L('copy')?>]</a>
		 <a href="?app=collection&controller=node&action=export&nodeid=<?php echo $v['nodeid']?>">[<?php echo L('export')?>]</a>

		 </td>
    </tr>
<?php
	}

?>
</tbody>
</table>

<div class="btn">
<label for="check_box"><?php echo L('select_all')?>/<?php echo L('cancel')?></label> <input type="submit" class="button" name="dosubmit" value="<?php echo L('delete')?>"/>
 <input type="button" class="button" value="<?php echo L('import_collection_points')?>" onclick="import_spider()" />
</div>

<div id="pages"><?php echo $pages?></div>
</div>
</form>
</div>
<script type="text/javascript">
<!--
function test_spider(id) {
	window.top.art.dialog.open('?app=collection&controller=node&action=public_test&nodeid='+id ,{
		title:'<?php echo L('data_acquisition_testdat')?>',
		id:'test',
		width:'700px',
		height:'500px',
		ok: function(iframeWin, topWin){return true;}
	});
}

function copy_spider(id) {
	window.top.art.dialog.open('?app=collection&controller=node&action=copy&nodeid='+id ,{
		title:'<?php echo L('copy_node')?>',
		id:'test',
		width:'420px',
		height:'120px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
		cancel: function(){}
	});
}

function import_spider() {
	window.top.art.dialog.open('?app=collection&controller=node&action=node_import' ,{
		title:'<?php echo L('import_collection_points')?>',
		id:'test',
		width:'420px',
		height:'200px',
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
		cancel: function(){}
	});
}

window.top.$('#display_center_id').css('display','none');
//-->
</script>
</body>
</html>