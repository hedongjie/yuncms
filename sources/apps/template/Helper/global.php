<?php
/**
 * 模版应用函数
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: global.php 252 2012-11-07 14:52:09Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 生成模板中所有YUN标签的MD5
 *
 * @param $file 模板文件地址
 *
 */
function tag_md5($file) {
	$data = file_get_contents ( $file );
	preg_match_all ( "/\{yun:(\w+)\s+([^}]+)\}/i", stripslashes ( $data ), $matches );
	$arr = array ();
	if (is_array ( $matches ) && ! empty ( $matches )) foreach ( $matches [0] as $k => $v ) {
		if (! $v) continue;
		$md5 = md5 ( $v );
		$arr [0] [$k] = $md5;
		$arr [1] [$md5] = $v;
	}
	return $arr;
}

/**
 * 生成YUN标签 @param $op 操作名 @param $data 数据
 */
function creat_yun_tag($op, $data) {
	$str = '{yun:' . $op . ' ';
	if (is_array ( $data )) {
		foreach ( $data as $k => $v ) {
			if ($v) $str .= $str ? " $k=\"$v\"" : "$k=\"$v\"";
		}
	} else {
		$str .= $data;
	}
	return $str . '}';
}

/**
 * 替换模板中的YUN标签
 *
 * @param $filepath 文件地址
 * @param $old_tag 老XT标签
 * @param $new_tag 新YUN标签
 * @param $style 风格
 * @param $dir 目录名
 */
function replace_yun_tag($filepath, $old_tag, $new_tag, $style, $dir) {
	if (file_exists ( $filepath )) {
		creat_template_bak ( $filepath, $style, $dir );
		$data = @file_get_contents ( $filepath );
		$data = str_replace ( $old_tag, $new_tag, $data );
		if (! is_writable ( $filepath )) return false;
		@file_put_contents ( $filepath, $data );
		return true;
	}
}

/**
 * 生成模板临时文件 @param $filepath 文件地址 @param $style 风格 @param $dir 目录名
 */
function creat_template_bak($filepath, $style, $dir) {
	$filename = basename ( $filepath );
	Loader::model ( 'template_bak_model' )->insert ( array ('creat_at' => TIME,'fileid' => $style . "_" . $dir . "_" . $filename,'userid' => cookie ( 'userid' ),'username' => cookie ( 'admin_username' ),'template' => new_addslashes ( file_get_contents ( $filepath ) ) ) );
}

/**
 * 生成标签选项
 *
 * @param $id HTML
 *        	ID号
 * @param $data 生成条件
 * @param $value 当前值
 * @param $op 操作名
 * @return html 返回HTML代码
 */
function creat_form($id, $data, $value = '', $op = '') {
	if (empty ( $value )) $value = $data ['defaultvalue'];
	$str = $ajax = '';
	if ($data ['ajax'] ['name']) {
		if ($data ['ajax'] ['m']) {
			$url = '$.get(\'?app=content&controller=push&action=public_ajax_get\', {html: this.value, id:\'' . $data ['ajax'] ['id'] . '\', do: \'' . $data ['ajax'] ['do'] . '\', application: \'' . $data ['ajax'] ['m'] . '\'}, function(data) {$(\'#' . $id . '_td\').html(data)});';
		} else {
			$url = '$.get(\'?app=template&controller=file&action=public_ajax_get\', { html: this.value, id:\'' . $data ['ajax'] ['id'] . '\', do: \'' . $data ['ajax'] ['do'] . '\', op: \'' . $op . '\', style: \'default\'}, function(data) {$(\'#' . $id . '_td\').html(data)});';
		}
	}
	switch ($data ['htmltype']) {
		case 'input' :
			if ($data ['ajax'] ['name']) {
				$ajax = 'onblur="' . $url . '"';
			}
			$str .= '<input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" size="30" />';

			break;
		case 'select' :
			if ($data ['ajax'] ['name']) {
				$ajax = 'onchange="' . $url . '"';
			}
			$str .= Form::select ( $data ['data'], $value, "name='$id' id='$id' $ajax" );
			break;
		case 'checkbox' :
			if ($data ['ajax'] ['name']) {
				$ajax = ' onclick="' . $url . '"';
			}
			if (is_array ( $value )) implode ( ',', $value );
			$str .= Form::checkbox ( $data ['data'], $value, "name='" . $id . "[]'" . $ajax, '', '120' );
			break;
		case 'radio' :
			if ($data ['ajax'] ['name']) {
				$ajax = ' onclick="' . $url . '"';
			}
			$str .= Form::radio ( $data ['data'], $value, "name='$id'$ajax", '', '120' );
			break;
		case 'input_select' :
			if ($data ['ajax'] ['name']) {
				$ajax = ';' . $url;
			}
			$str .= '<input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" size="30" />' . Form::select ( $data ['data'], $value, "name='select_$id' id='select_$id' onchange=\"$('#$id').val(this.value);$ajax\"" );
			break;

		case 'input_select_category' :
			if ($data ['ajax'] ['name']) {
				$ajax = ';' . $url;
			}
			$str .= '<input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" size="30" />' . Form::select_category ( '', $value, "name='select_$id' id='select_$id' onchange=\"$('#$id').val(this.value);$ajax\"", '', (isset ( $data ['data'] ['modelid'] ) ? $data ['data'] ['modelid'] : 0), (isset ( $data ['data'] ['type'] ) ? $data ['data'] ['type'] : - 1), (isset ( $data ['data'] ['onlysub'] ) ? $data ['data'] ['onlysub'] : 0) );
			break;
	}
	if (! empty ( $data ['validator'] )) {
		$str .= '<script type="text/javascript">
        $(function(){$("#' . $id . '").formValidator({onshow:"' . L ( 'input' ) . $data ['name'] . '。",onfocus:"' . L ( 'input' ) . $data ['name'] . '。"' . ($data ['empty'] ? ',empty:true' : '') . '})';
		if ($data ['htmltype'] != 'select' && (isset ( $data ['validator'] ['min'] ) || isset ( $data ['validator'] ['max'] ))) {
			$str .= ".inputValidator({" . (isset ( $data ['validator'] ['min'] ) ? 'min:' . $data ['validator'] ['min'] . ',' : '') . (isset ( $data ['validator'] ['max'] ) ? 'max:' . $data ['validator'] ['max'] . ',' : '') . " onerror:'" . $data ['name'] . L ( 'should', '', 'template' ) . (isset ( $data ['validator'] ['min'] ) ? ' ' . L ( 'is_greater_than', '', 'template' ) . $data ['validator'] ['min'] . L ( 'lambda', '', 'template' ) : '') . (isset ( $data ['validator'] ['max'] ) ? ' ' . L ( 'less_than', '', 'template' ) . $data ['validator'] ['max'] . L ( 'lambda', '', 'template' ) : '') . "。'})";
		}
		if ($data ['htmltype'] != 'checkbox' && $data ['htmltype'] != 'radio' && isset ( $data ['validator'] ['reg'] )) {
			$str .= '.regexValidator({regexp:"' . $data ['validator'] ['reg'] . '"' . (isset ( $data ['validator'] ['reg_param'] ) ? ",param:'" . $data ['validator'] ['reg_param'] . "'" : '') . (isset ( $data ['validator'] ['reg_msg'] ) ? ',onerror:"' . $data ['validator'] ['reg_msg'] . '"' : '') . '})';
		}
		$str .= ";});</script>";
	}
	return $str;
}

/**
 * 编辑YUN标签时，生成跳转URL地址
 *
 * @param $action 操作
 */
function creat_url($action) {
	$url = '';
	foreach ( $_GET as $k => $v ) {
		if ($k == 'do') $v = $action;
		$url .= $url ? "&$k=$v" : "$k=$v";
	}
	return $url;
}

/**
 * 生成可视化模板 @param $html 模板代码 @param $style 风格 @param $dir 目录 @param $file 文件名
 */
function visualization($html, $style = '', $dir = '', $file = '') {
	$change = "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . CSS_PATH . "admin_visualization.css\" />
    <script type='text/javascript' src='" . JS_PATH . "jquery-1.4.2.min.js'></script>
    <script language=\"javascript\" type=\"text/javascript\" src=\"" . JS_PATH . "artDialog/jquery.artDialog.js?skin=default\"></script>
    <script language=\"javascript\" type=\"text/javascript\" src=\"" . JS_PATH . "artDialog/plugins/iframeTools.js\"></script>
    <script type='text/javascript'>
    $(function(){
    $('a').attr('href', 'javascript:void(0)').attr('target', '');
    $('.admin_piao_edit').click(function(){
    var url = '?app=template&controller=file&action=edit_yun_tag';
    if($(this).parent('.admin_piao').attr('yun_action') == 'block') url = '?app=block&controller=admin&action=add';
    window.top.art.dialog.open(url+'&style=$style&dir=$dir&file=$file&'+$(this).parent('.admin_piao').attr('data'),{
    title:'" . L ( 'yun_tag', '', 'template' ) . "',
            id:'edit',
            width:'700px',
            height:'500px',
            ok: function(iframeWin, topWin){
            var form = iframeWin.document.getElementById('dosubmit');
            form.click();
            return false;
},
            cancel: function(){}
});
})
            $('.admin_block').click(function(){
            window.top.art.dialog.open('?app=block&controller=admin&action=block_update&id='+$(this).attr('blockid'),{
            title:'" . L ( 'yun_tag', '', 'template' ) . "',
            id:'edit',
            width:'700px',
            height:'500px',
            ok: function(iframeWin, topWin){
            var form = iframeWin.document.getElementById('dosubmit');
            form.click();
            return false;
},
            cancel: function(){}
});
});
})
</script><div id=\"YUN__contentHeight\" style=\"display:none\">80</div>";
	$html = str_replace ( '</body>', $change . '</body>', $html, $num );
	if (! $num) $html .= $change;
	return $html;
}