<table cellpadding="2" cellspacing="1" width="98%">

	<tr>
		<td>默认播放器</td>
		<td><?php
$playerlists = array ('0' => '请选择默认播放器' );
$playerlist = S('common/player');
foreach ( ( array ) $playerlist as $k => $v ) {
    $playerlists [$v ['playerid']] = $v ['subject'];
}
echo Form::select ( $playerlists, $setting ['defaultplayer'], 'name="setting[defaultplayer]"' );
?></td>
	</tr>

	<tr>
		<td>允许上传的视频类型</td>
		<td><input type="text" name="setting[upload_allowext]"
			value="flv|rm|rmvb|avi|wmv|swf" size="40" class="input-text"></td>
	</tr>
	<tr>
		<td>是否从已上传中选择</td>
		<td><input type="radio" name="setting[isselectimage]" value="1"> 是 <input
			type="radio" name="setting[isselectimage]" value="0" checked> 否</td>
	</tr>
	<tr>
		<td>允许同时上传的个数</td>
		<td><input type="text" name="setting[upload_number]" value="10" size=3></td>
	</tr>

	<tr>
		<td>文本框高度(px)</td>
		<td><input type="text" name="setting[textheight]" value="100" size=3>
			一行一个视频地址,视频格式为 名称$地址,如 第1集$a.rmvb,也可直接视频地址,默认用第?集的模式填充</td>
	</tr>

	<tr>
		<td>播放页是否生成静态?</td>
		<td><input type="radio" name="setting[ishtml]" value="1" checked
			onclick="$('#playhtml').show();"> 是 <input type="radio"
			name="setting[ishtml]" value="0" onclick="$('#playhtml').hide(300);">
			否</td>
	</tr>

	<tbody id="playhtml">
		<tr>
			<td>静态播放页文件扩展名</td>
			<td><input type="text" name="setting[fileext]" value=".html" size=5>
				(fileext)</td>
		</tr>

		<tr>
			<td>播放页url连接符</td>
			<td><input type="text" name="setting[ljf]" value="_" size=5> (ljf)</td>
		</tr>

		<tr>
			<td>播放页url规则</td>
			<td><input type="radio" name="setting[purl]" value="1" checked> 字段名
				ljf 集数.fileext <input type="radio" name="setting[purl]" value="0">
				集数.fileext</td>
		</tr>
	</tbody>
</table>