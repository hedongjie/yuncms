<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header', 'admin' );
?>
<script language="JavaScript"
	src="<?php echo JS_PATH?>jquery.imgpreview.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		var obj=$("#imgPreview a[rel]");
		if(obj.length>0) {
			$('#imgPreview a[rel]').imgPreview({
				srcAttr: 'rel',
			    imgCSS: { width: 200 }
			});
		}
	});
</script>
<div class="bk15"></div>
<div class="pad-lr-10">
	<table width="100%" cellspacing="0" class="search-form">
		<tbody>
			<tr>
				<td><div class="explain-col">
						<a href="<?php echo U('attachment/manage');?>"><?php echo L('database_schema')?></a>
					</div></td>
			</tr>
		</tbody>
	</table>
	<div class="table-list">
		<table width="100%" cellspacing="0" id="imgPreview">
			<tr>
				<td align="left"><?php echo L("local_dir")?>：<?php echo $local?></td>
				<td></td>
			</tr>
<?php if ($dir !='' && $dir != '.'):?>
<tr>
				<td align="left"><a
					href="<?php echo U('attachment/manage/dir',array('dir'=>stripslashes(dirname($dir)),'menuid'=>$_GET['menuid']));?>"><img
						src="<?php echo IMG_PATH?>folder-closed.gif" /><?php echo L("parent_directory")?></a></td>
				<td></td>
			</tr>
<?php endif;?>
<?php

if (is_array ( $list )) {
	foreach ( $list as $v ) {
		$filename = basename ( $v )?>
<tr>
<?php

if (is_dir ( $v )) {
			$dir = isset ( $_GET ['dir'] ) && ! empty ( $_GET ['dir'] ) ? stripslashes ( $_GET ['dir'] ) . '/' : '';
			echo '<td align="left"><img src="' . IMG_PATH . 'folder-closed.gif" /> <a href="' . U ( 'attachment/manage/dir', array (
					'menuid'=>$_GET['menuid'],'dir' => $dir
			) ) . $filename . '"><b>' . $filename . '</b></a></td><td width="10%"></td>';
		} else {
			echo '<td align="left" ><img src="' . file_icon ( $filename, 'gif' ) . '" /><a rel="' . $local . '/' . $filename . '">' . $filename . '</a></td><td width="10%"><a href="javascript:;" onclick="preview(\'' . $local . '/' . $filename . '\')">' . L ( 'preview' ) . '</a> | <a href="javascript:;" onclick="att_delete(this,\'' . urlencode ( $filename ) . '\',\'' . urlencode ( $local ) . '\')">' . L ( 'delete' ) . '</a> </td>';
		}
		?>
</tr>
<?php
	}
}
?>
</table>
	</div>
</div>
</body>
<script type="text/javascript">
/**
 * 查看缩略图
 */
function preview(filepath) {
	if(IsImg(filepath)) {
		window.top.art.dialog({
			padding: 0,
			lock: true,
			title:'<?php echo L('preview')?>',
		    content:'<img src="'+filepath+'" width="500px" height="400px"/>',
		    lock: true
		});
	} else {
		window.top.art.dialog({
			title:'<?php echo L('preview')?>',
			lock: true,
			content:'<a href="'+filepath+'" target="_blank"/><img src="<?php echo IMG_PATH?>admin_img/down.gif"><?php echo L('click_open')?></a>'
		});
	}
}

/**
 * 删除附件
 */
function att_delete(obj,filename,localdir){
	 window.top.art.dialog({content:'<?php echo L('del_confirm')?>', fixed:true, style:'confirm', id:'att_delete'},
	function(){
	$.get('?app=attachment&controller=manage&action=pulic_dirmode_del&filename='+filename+'&dir='+localdir,function(data){
				if(data) $(obj).parent().parent().fadeOut("slow");
			})

		 },
	function(){});
};

/**
 * 是否是图片
 */
function IsImg(url){
	  var sTemp;
	  var b=false;
	  var opt="jpg|gif|png|bmp|jpeg";
	  var s=opt.toUpperCase().split("|");
	  for (var i=0;i<s.length ;i++ ){
	    sTemp=url.substr(url.length-s[i].length-1);
	    sTemp=sTemp.toUpperCase();
	    s[i]="."+s[i];
	    if (s[i]==sTemp){
	      b=true;
	      break;
	    }
	  }
	  return b;
}
</script>
</html>