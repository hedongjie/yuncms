<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<div id="main_frameid" class="pad-10 display"
	style="_margin-right: -12px; _width: 98.9%;">
	<script type="text/javascript">
$(function(){
	if ($.browser.msie && parseInt($.browser.version) < 7)
		$('#browserVersionAlert').show();
});
</script>
	<div class="explain-col mb10" style="display: none"
		id="browserVersionAlert">
<?php echo L('ie8_tip')?></div>
	<div class="col-2 lf mr10" style="width: 48%">
		<h6><?php echo L('personal_information')?></h6>
		<div class="content">
	<?php echo L('main_hello')?><?php echo $admin_username?><br />
	<?php echo L('main_role')?><?php echo $rolename?> <br />
			<div class="bk20 hr">
				<hr />
			</div>
	<?php echo L('main_last_logintime')?><?php echo date('Y-m-d H:i:s',$logintime)?><br />
	<?php echo L('main_last_loginip')?><?php echo $loginip?> <br />
		</div>
	</div>
	<div class="col-2 col-auto">
		<h6><?php echo L('main_safety_tips')?></h6>
		<div class="content" style="color: #ff0000;">
<?php if($yun_writeable) {?>
<?php echo L('main_safety_permissions')?><br />
<?php } ?>
<?php if(C('framework','debug') != 0) {?>
<?php echo L('main_safety_debug')?><br />
<?php } ?>
<?php if(!C('log','enable')) {?>
<?php echo L('main_safety_errlog')?><br />
<?php } ?>
	<div class="bk20 hr">
				<hr />
			</div>
<?php if(C('framework','execution_sql')) {?>
<?php echo L('main_safety_sql')?> <br />
<?php } ?>
<?php if($logsize_warning) {?>
<?php echo L('main_safety_log',array('size'=>C ( 'log', 'file_size' ).'KB'))?> <br />
<?php } ?>
	</div>
	</div>
	<div class="bk10"></div>
	<div class="col-2 lf mr10" style="width: 48%">
		<h6><?php echo L('main_shortcut')?></h6>
		<div class="content" id="admin_panel">
	<?php foreach($adminpanel as $v) {?>
		<span> [<a target="right"
				href="<?php echo $v['url'].'&menuid='.$v['menuid'];?>"><?php echo L($v['name'])?></a>]
			</span>
	<?php }?>
	</div>
	</div>
	<div class="col-2 col-auto">
		<h6><?php echo L('main_sysinfo')?></h6>
		<div class="content">
	<?php echo L('main_leaps_version')?>Leaps <?php echo LEAPS_VERSION?>  Release <?php echo LEAPS_RELEASE?> [<a
				href="https://github.com/xutongle/Leaps" target="_blank"><?php echo L('main_latest_version')?></a>]<br />
	<?php echo L('main_os')?><?php echo $sysinfo['os']?> <br />
	<?php echo L('main_web_server')?><?php echo $sysinfo['web_server']?> <br />
	<?php echo L('main_sql_version')?><?php echo $sysinfo['mysqlv']?><br />
	<?php echo L('main_upload_limit')?><?php echo $sysinfo['fileupload']?><br />
		</div>
	</div>
	<div class="bk10"></div>
	<div class="col-2 lf mr10" style="width: 48%">
		<h6
			onclick="art.dialog.open('?app=admin&controller=index&action=public_our', {id:'map',title:'<?php echo L('main_our')?>', width:'400px', height:'300px', lock:true});void(0);"><?php echo L('main_product_team')?></h6>
		<div class="content">
	<?php echo L('main_copyright')?><?php echo $product_copyright?><br />
	<?php echo L('main_product_planning')?><?php echo $architecture?><br />
	<?php echo L('main_product_dev')?><?php echo $programmer;?><br />
	<?php echo L('main_product_ui')?><?php echo $designer;?><br />
	<?php echo L('main_product_site')?><a href="http://www.tintsoft.com/"
				target="_blank">http://www.yuncms.net/</a> <br />
	<?php echo L('main_product_bbs')?><a href="http://bbs.tintsoft.com/"
				target="_blank">http://bbs.yuncms.net/</a>
		</div>
	</div>

	<div class="col-2 col-auto">
		<h6><?php echo L('main_license')?></h6>
		<div class="content">
	<?php echo L('main_version')?>YUNCMS <?php echo YUNCMS_VERSION?>  Release <?php echo YUNCMS_RELEASE?> [<a
				href="http://buy.yuncms.net" target="_blank"><?php echo L('main_support')?></a>]<br />
	<?php echo L('main_license_type')?><span id="yuncms_license"></span> <br />
	<?php echo L('main_serial_number')?><span id="yuncms_sn"></span> <br />
		</div>
	</div>
	<div class="bk10"></div>
</div>
<script type="text/javascript">
(function(){
	$(function () {
		window.art.dialog({
			left: '100%',
			top: '100%',
			title: '欢迎使用YUNCMS v2013!',
			icon: 'face-smile',
			content: '若需要商业定制，请联系：<br>xutongle@gmail.com',
			width: 200,
			fixed: true
		})
	})
})();

</script>
</body>
</html>