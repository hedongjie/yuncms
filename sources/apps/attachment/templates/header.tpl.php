<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php if(isset($addbg)) { ?>
	class="addbg" <?php } ?>>
<head>
<meta http-equiv="Content-Type"
	content="text/html; charset=utf-8 echo CHARSET?>" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title><?php echo L('website_manage');?></title>
<link href="<?php echo CSS_PATH?>reset.css" rel="stylesheet"
	type="text/css" />
<link href="<?php echo CSS_PATH;?>system.css" rel="stylesheet"
	type="text/css" />
<link href="<?php echo CSS_PATH?>table_form.css" rel="stylesheet"
	type="text/css" />
<?php
if (isset ( $show_dialog )) {
    ?>
<script language="javascript" type="text/javascript"
	src="<?php echo JS_PATH?>artDialog4/jquery.artDialog.js?skin=default"></script>
<script language="javascript" type="text/javascript"
	src="<?php echo JS_PATH?>artDialog4/plugins/iframeTools.js"></script>
<?php } ?>
</head>
<body>
	<link rel="stylesheet" type="text/css"
		href="<?php echo CSS_PATH?>style/styles1.css" title="styles1"
		media="screen" />
	<link rel="alternate stylesheet" type="text/css"
		href="<?php echo CSS_PATH?>style/styles2.css" title="styles2"
		media="screen" />
	<link rel="alternate stylesheet" type="text/css"
		href="<?php echo CSS_PATH?>style/styles3.css" title="styles3"
		media="screen" />
	<link rel="alternate stylesheet" type="text/css"
		href="<?php echo CSS_PATH?>style/styles4.css" title="styles4"
		media="screen" />
	<script language="javascript" type="text/javascript"
		src="<?php echo JS_PATH?>jquery-1.4.2.min.js"></script>
	<script language="javascript" type="text/javascript"
		src="<?php echo JS_PATH?>admin_common.js"></script>
	<script language="javascript" type="text/javascript"
		src="<?php echo JS_PATH?>styleswitch.js"></script>
<?php if(isset($show_validator)) { ?>
<script language="javascript" type="text/javascript"
		src="<?php echo JS_PATH?>formvalidator.js" charset="UTF-8"></script>
	<script language="javascript" type="text/javascript"
		src="<?php echo JS_PATH?>formvalidatorregex.js" charset="UTF-8"></script>
<?php } ?>
<style type="text/css">
html {
	_overflow-y: scroll
}
</style>