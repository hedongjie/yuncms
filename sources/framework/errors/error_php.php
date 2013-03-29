<!DOCTYPE html>
<html>
<head>
<title>A PHP Error was encountered</title>
<meta http-equiv="content-type"	content="text/html;charset=utf-8" />
<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
<style>
body {font-family: 'Microsoft Yahei', Verdana, arial, sans-serif;font-size: 14px;}
ul,li {	list-style: none;}
a {text-decoration: none;color: #174B73;}
a:hover {text-decoration: none;color: #FF6600;}
h2 {border-bottom: 1px solid #DDD;padding: 8px 0;font-size: 25px;}
.title {margin: 4px 0;color: #F60;font-weight: bold;}
.message,#trace {padding: 1em;border: solid 1px #000;margin: 10px 0;background: #FFD;line-height: 150%;}
.message {background: #FFD;color: #2E2E2E;border: 1px solid #E0E0E0;}
#trace {background: #E7F7FF;border: 1px solid #E0E0E0;color: #535353;}
.notice {padding: 10px;margin: 5px;color: #666;background: #FCFCFC;border: 1px solid #E0E0E0;}
.red {color: red;font-weight: bold;}
.debug {line-height: 1.5;font-size: 12px;}
ul {padding-left: 48px;margin-bottom: 10px;}
ul li:hover {background: #f3f3f3;}
.err {color: #ff0000;background: #faf6f4;}
h3 {padding-left: 24px;background: #f7f7f7;font-weight: 100;font-size: 12px;margin-bottom: 10px;}
</style>
</head>
<body>
	<div class="notice">
		<h2>A PHP Error was encountered</h2>
		<div>您可以选择 [ <A HREF="javascript:window.location.reload();">重试</A> ] 或者 [<A HREF="javascript:history.back()">返回</A> ]</div>
		<?php if(isset($file)) {?>
		<p>
			<strong>错误位置:</strong> FILE: <span class="red">
				<?php echo $file; ?>
			</span> LINE: <span class="red">
				<?php echo $line;?>
			</span>
		</p>
		<?php }?>

		<p class="title">[ 错误信息 ]</p>
		<p class="message">
			<?php echo $message; ?>
		</p>

		<?php if(isset($trace) && is_array($trace)) {?>
		<p class="title">[ TRACE ]</p>
		<p id="trace">
			<?php foreach($trace as $value){ echo $value.'<br/>';} ?>
		</p>
		<?php }?>

		<?php if(isset($file_lines) && is_array($file_lines)){?>
		<p class="title">[ PHP Debug ]</p>
		<div class="debug">
			<ul>
				<?php foreach($file_lines as $key => $value):
        if($key == $current_line):?>
				<li class="err">
					<?php echo $value; ?>
				</li>
				<?php else: ?>
				<li>
					<?php echo $value; ?>
				</li>
				<?php endif;endforeach; ?>
			</ul>
			<h3>__Stack:</h3>
		</div>
		<?php }?>
	</div>
	<div align="center" style="color: #FF3300; margin: 5pt; font-family: Verdana"> Leaps <sup style='color: gray; font-size: 9pt'><?php echo LEAPS_VERSION?></sup><span style='color: silver'> { Fast & Simple OOP PHP Framework } -- [ WE CAN DO IT JUST LIKE IT ]</span></div>
</body>
</html>