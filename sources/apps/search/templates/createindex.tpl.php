<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header','admin');
?>
<div class="pad-10">
<div class="explain-col">
<form action="" method="get">
<input type="hidden" name="app" value="search">
<input type="hidden" name="controller" value="search_admin">
<input type="hidden" name="action" value="createindex">
<input type="hidden" name="menuid" value="171">
<?php echo L('re_index_note');?> <input type="text" name="pagesize" value="100" size="5"> <?php echo L('tiao');?> <input type="submit" name="dosubmit" class="button" value="<?php echo L('confirm_reindex');?>"></form>
</div>

<script language="JavaScript">
<!--
	window.top.$('#display_center_id').css('display','none');
//-->
</script>