<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
error_reporting ( E_ERROR );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php if(isset($addbg)) { ?>
	class="addbg" <?php } ?>>
<head>
<meta http-equiv="Content-Type"
	content="text/html; charset=<?php echo CHARSET?>" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title><?php echo L('admin_site_title')?></title>
<link href="<?php echo CSS_PATH?>reset.css" rel="stylesheet"
	type="text/css" />
<link href="<?php echo CSS_PATH?>system.css" rel="stylesheet"
	type="text/css" />
<link href="<?php echo CSS_PATH?>table_form.css" rel="stylesheet"
	type="text/css" />
<script language="javascript" type="text/javascript"
	src="<?php echo JS_PATH?>jquery-1.4.2.min.js"></script>
<?php
if (isset ( $show_dialog )) {
	?>
<script language="javascript" type="text/javascript"
	src="<?php echo JS_PATH?>artDialog/jquery.artDialog.js?skin=default"></script>
<script language="javascript" type="text/javascript"
	src="<?php echo JS_PATH?>artDialog/plugins/iframeTools.js"></script>
<?php } ?>
<script language="javascript" type="text/javascript"
	src="<?php echo JS_PATH?>admin_common.js"></script>
<?php if(isset($show_validator)) { ?>
<script language="javascript" type="text/javascript"
	src="<?php echo JS_PATH?>formvalidator.js" charset="UTF-8"></script>
<script language="javascript" type="text/javascript"
	src="<?php echo JS_PATH?>formvalidatorregex.js" charset="UTF-8"></script>
<?php } ?>
<script type="text/javascript">
	window.focus();
</script>
</head>
<body>
<?php if(!isset($show_header)) { ?>
<div class="subnav">
  <div class="content-menu ib-a blue line-x">
    <?php if(isset($big_menu)) { echo '<a class="add fb" href="'.$big_menu[0].'"><em>'.$big_menu[1].'</em></a>　';} else {$big_menu = '';} ?>
    <?php $menuid = isset($_GET['menuid']) ? intval($_GET['menuid']) : 0 ;echo admin::submenu($menuid,$big_menu); ?>
  </div>
</div>
<?php } ?>
<style type="text/css">
html {
	_overflow-y: scroll
}
</style>
