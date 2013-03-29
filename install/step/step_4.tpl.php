<?php
include 'header.tpl.php';
?>
<div class="body_box">
	<div class="main_box">
		<div class="hd">
			<div class="bz a4">
				<div class="jj_bg"></div>
			</div>
		</div>
		<div class="ct">
			<div class="bg_t"></div>
			<div class="clr">
				<div class="l"></div>
				<div class="ct_box nobrd i6v">
					<div class="nr">

						<table cellpadding="0" cellspacing="0" class="table_list">
							<tr>
								<th class="col1">目录文件</th>
								<th class="col2">所需状态</th>
								<th class="col3">当前状态</th>
							</tr>
                  <?php foreach ($filesapp as $fileapp) {?>
                  <tr>
								<td><?php echo $fileapp['file']?></td>
								<td><span><img src="images/correct.gif" />&nbsp;可写</span></td>
								<td><?php echo $fileapp['is_writable'] ? '<span><img src="images/correct.gif" />&nbsp;可写</span>' : '<font class="red"><img src="images/error.gif" />&nbsp;不可写</font>'?></td>
							</tr>
					<?php } ?>
                </table>
					</div>
				</div>
			</div>
		</div>
		<div class="bg_b"></div>
	</div>
	<div class="btn_box">
		<a href="javascript:history.go(-1);" class="s_btn">上一步</a>
             <?php if($no_writablefile == 0) {?>
            <a href="javascript:void(0);"
			onClick="$('#install').submit();" class="x_btn">下一步</a>
            <?php } else {?>
			<a onClick="alert('存在不可写目录或者文件');" class="x_btn pre">检测不通过</a>
            <?php } ?>
            </div>
	<form id="install" action="index.php?" method="post">
		<input type="hidden" name="step" value="5">
			<input type="hidden"
			id="selectmod" name="selectapp" value="<?php echo $selectapp?>" /> <input
			type="hidden" name="testdata" value="<?php echo $testdata?>" />
	</form>
</div>
</div>
</body>
</html>
