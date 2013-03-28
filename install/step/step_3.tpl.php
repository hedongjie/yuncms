<?php
include 'header.tpl.php';
?>
<div class="body_box">
	<div class="main_box">
		<div class="hd">
			<div class="bz a3">
				<div class="jj_bg"></div>
			</div>
		</div>
		<div class="ct">
			<div class="bg_t"></div>
			<div class="clr">
				<div class="l"></div>
				<div class="ct_box nobrd i6v">
					<div class="nr">
						<form id="install" action="index.php?" method="post">
			<input type="hidden" name="step" value="4">
							<fieldset>
								<legend>必选模块</legend>
								<div class="content">
									<label><input type="checkbox" name="admin" value="admin"
										checked disabled>后台管理模块</label> <label><input type="checkbox"
										name="content" value="content" checked disabled>内容模块</label> <label><input
										type="checkbox" name="member" value="member" checked disabled>会员模型</label>
									<label><input type="checkbox" name="pay" value="pay" checked
										disabled>财务模块</label> <label><input type="checkbox"
										name="special" value="special" checked disabled>专题模块</label> <label><input
										type="checkbox" name="search" value="search" checked disabled>全文搜索</label>
								</div>
							</fieldset>

							<fieldset>
								<legend>可选模块</legend>
								<div class="content">
<?php
$count = count ( $YUNCMS_APPS ['name'] );
$j = 0;
foreach ( $YUNCMS_APPS ['name'] as $i => $app ) {
    if ($j % 5 == 0)
        echo "<tr >";
    ?>
	<label><input type="checkbox" name="selectapp[]"
										value="<?php echo $app?>" checked><?php echo $YUNCMS_APPS['appname'][$i]?>模块</label>
	<?php
    if ($j % 5 == 4)
        echo "</tr>";
    $j ++;
}
?>
    </div>
							</fieldset>
							<fieldset>
								<legend>可选数据</legend>
								<div class="content">
									<label style="width: auto"><input type="checkbox"
										name="testdata" value="1" checked>默认测试数据 （用于新手和调试用户）</label>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div class="bg_b"></div>
		</div>
		<div class="btn_box">
			<a href="javascript:history.go(-1);" class="s_btn pre">上一步</a><a
				href="javascript:void(0);"
				onClick="$('#install').submit();" class="x_btn">下一步</a>
		</div>
	</div>
</div>
</body>
</html>