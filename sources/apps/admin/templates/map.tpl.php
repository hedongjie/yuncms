<?php
defined ( 'IN_ADMIN' ) or exit ( 'No permission resources.' );
include $this->admin_tpl ( 'header' );
?>
<div class="pad-10">
	<?php
	$n = 1;
	foreach ( $menu as $key => $v ) {
		if ($n == 1)
			echo '<div class="map-menu lf">';
		?>
		<ul>
		<li class="title"><?php echo L($v['name'])?></li>
			<?php foreach ($v['childmenus'] as $k=>$r){?>
				<li class="title2"><?php echo L($r['name'])?></li>
				<?php

$menus = admin::admin_menu ( $r ['id'] );
			foreach ( $menus as $s => $r ) {
				?>
				<li><a
			href="javascript:go('index.php?app=<?php echo $r['application']?>&controller=<?php echo $r['controller']?>&action=<?php echo $r['action']?>&menuid=<?php echo $r['id']?><?php echo isset($r['data']) ? $r['data'] : ''?>')"><?php echo L($r['name'])?></a></li>
			<?php }}?>
		</ul>
		<?php
		if ($n % 2 == 0)
			echo '</div><div class="map-menu lf">';
		$n ++;
	}
	?>
</div>
</div>
<script type="text/javascript">
<!--
 function go(url) {
	 window.top.document.getElementById('rightMain').src=url;
	 window.top.art.dialog({id:'map'}).close();
}
//-->
</script>
</body>
</html>