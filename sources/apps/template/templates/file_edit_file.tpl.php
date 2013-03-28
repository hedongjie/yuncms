<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<script type="text/javascript" src="<?php echo JS_PATH?>edit_area/edit_area_full.js"></script>
<script language="Javascript" type="text/javascript">
editAreaLoader.init({
	id: "code"
	,start_highlight: true
	,allow_toggle: true
	,word_wrap: true
	,language: "zh"
	,syntax: "<?php echo File::get_suffix(C('template','ext'))?>"	//语法
});
</script>
<style type="text/css">
	html{_overflow:hidden}
	.frmaa{float:left;width:79%; }
	.rraa{float: right;width:20%;}
	.pt{margin-top: 4px;}

</style>
<body style="overflow:hidden">
<div class="pad-10" style="padding-bottom:0px">
<div class="rraa">
<h3 class="f14"><?php echo L('common_variables')?></h3>
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{$CSS_PATH}','');" value="{$CSS_PATH}" title="<?php echo L('click_into')?>"/><br />
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{$JS_PATH}','');" value="{$JS_PATH}" title="<?php echo L('click_into')?>"/><br />
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{$IMG_PATH}','');" value="{$IMG_PATH}" title="<?php echo L('click_into')?>"/><br />
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{SITE_URL}','');" value="{SITE_URL}" title="<?php echo L('click_into')?>"/><br />
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{loop $data $n $r}','');" value="{loop $data $n $r}" title="<?php echo L('click_into')?>"/><br />
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{$r[\'url\']}','');" value="{$r['url']}" title="<?php echo L('click_into')?>"/><br />
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{$r[\'title\']}','');" value="{$r['title']}" title="<?php echo L('click_into')?>"/><br />
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{$r[\'thumb\']}','');" value="{$r['thumb']}" title="<?php echo L('click_into')?>"/><br />
<input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '{strip_tags($r[description])}','');" value="{strip_tags($r[description])}" title="<?php echo L('click_into')?>"/><br />
<?php if (is_array($file_t_v[$file_t])) { foreach($file_t_v[$file_t] as $k => $v) {?>
 <input type="button" class="button pt" onClick="javascript:editAreaLoader.insertTags('code', '<?php echo $k?>','');" value="<?php echo $k?>" title="<?php echo $v ? $v :L('click_into')?>"/><br />
 <?php } }?>
</div>
<div class="frmaa">
<form action="?app=template&controller=file&action=edit_file&style=<?php echo $this->style?>&dir=<?php echo $dir?>&file=<?php echo $file?>" method="post" name="myform" id="myform">
<textarea name="code" id="code" style="height: 80%;width:99%; visibility:inherit"><?php echo $data?></textarea>
<div class="bk10"></div>
<?php if ($is_write==0){echo '<font color="red">'.L("file_does_not_writable").'</font>';}?> <?php if (application_exists('tag')) {?><input type="button" class="button" onClick="create_tag()" value="<?php echo L('create_tag')?>" /> <input type="button" class="button" onClick="select_tag()" value="<?php echo L('select_tag')?>" /> <?php }?>
<input type="submit" id="dosubmit" class="button pt" name="dosubmit" value="<?php echo L('submit')?>" />
</form>
</div>
</div>
<script type="text/javascript">
function create_tag() {
	window.top.art.dialog.open('?app=tag&controller=tag&action=add&ac=js', {
		id:'add',
		title:"<?php echo L('create_tag')?>",
		width:'700px',
		height:'500px',
		lock:true,
		ok: function(iframeWin, topWin){
			var form = iframeWin.document.getElementById('dosubmit');
			form.click();
			return false;
		},
	    cancel: function(){}
	});
}

function call(text) {
	$('#code').focus();
    var str = document.selection.createRange();
	var text_lenght = parseInt($('#text_lenght').val());
	str.moveStart("character", text_lenght);
	str.select();
	str.text = text;
}

function GetPoint() {
	if ($.browser.msie) {
		rng = event.srcElement.createTextRange();
		rng.moveToPoint(event.x,event.y);
		rng.moveStart("character",-event.srcElement.value.length);
		var text_lenght = rng.text.length;
	} else {
		//alert($('#code').selectionStart);
	}
	$('#text_lenght').val(text_lenght);
}

function select_tag() {
	window.top.art.dialog.open('?app=tag&controller=tag&action=lists',{
		id:'list',
		title:"<?php echo L('tag_list')?>",
		width:'700px',
		height:'500px',
		lock:true,
		ok: function(){
			
		}
	});
}
//-->
</SCRIPT>
</script>
</body>
</html>