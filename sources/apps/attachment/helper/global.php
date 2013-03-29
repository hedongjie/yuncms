<?php
/**
 * @author		YUNCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, NewsTeng, Inc.
 * @license	http://www.yuncms.net/about/license
 * @link		http://www.yuncms.net
 * $Id: global.php 95 2013-03-23 15:27:53Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 返回附件类型图标
 *
 * @param $file 附件名称
 * @param $type png为大图标，gif为小图标
 */
function file_icon($file, $type = 'png') {
	$ext_arr = array ('doc','docx','ppt','xls','txt','pdf','mdb','jpg','gif','png','bmp','jpeg','rar','zip','swf','flv' );
	$ext = File::get_suffix ( $file );
	if ($type == 'png') {
		if ($ext == 'zip' || $ext == 'rar') $ext = 'rar';
		elseif ($ext == 'doc' || $ext == 'docx') $ext = 'doc';
		elseif ($ext == 'xls' || $ext == 'xlsx') $ext = 'xls';
		elseif ($ext == 'ppt' || $ext == 'pptx') $ext = 'ppt';
		elseif ($ext == 'flv' || $ext == 'swf' || $ext == 'rm' || $ext == 'rmvb') $ext = 'flv';
		else $ext = 'do';
	}
	if (in_array ( $ext, $ext_arr )) return 'statics/images/ext/' . $ext . '.' . $type;
	else return 'statics/images/ext/blank.' . $type;
}

/**
 * 读取swfupload配置类型
 *
 * @param array $args
 *        	flash上传配置信息
 */
function getswfinit($args) {
	$site_setting = S ( 'common/common' );
	$site_allowext = C ( 'attachment', 'allowext' );
	$args = explode ( ',', $args );
	$arr ['file_upload_limit'] = intval ( $args [0] ) ? intval ( $args [0] ) : '8';
	$args ['1'] = ($args [1] != '') ? $args [1] : $site_allowext;
	$arr_allowext = explode ( '|', $args [1] );
	foreach ( $arr_allowext as $k => $v ) {
		$v = '*.' . $v;
		$array [$k] = $v;
	}
	$upload_allowext = implode ( ';', $array );
	$arr ['file_types'] = $upload_allowext;
	$arr ['file_types_post'] = $args [1];
	$arr ['allowupload'] = intval ( $args [2] );
	$arr ['thumb_width'] = isset($args [3]) ? intval ( $args [3] ) : 0;
	$arr ['thumb_height'] = isset($args [4]) ? intval ( $args [4] ) : 0;
	$arr ['watermark_enable'] = isset($args [5]) ? intval ( $args [5] ) : 0 ;
	return $arr;
}

/**
 * 判断是否为图片
 */
function is_image($file) {
	$ext_arr = array ('jpg','gif','png','bmp','jpeg','tiff' );
	$ext = File::get_suffix ( $file );
	return in_array ( $ext, $ext_arr ) ? $ext_arr : false;
}

/**
 * 判断是否为视频
 */
function is_video($file) {
	$ext_arr = array ('rm','mpg','avi','mpeg','wmv','flv','asf','rmvb' );
	$ext = File::get_suffix ( $file );
	return in_array ( $ext, $ext_arr ) ? $ext_arr : false;
}

/**
 * flash上传初始化
 * 初始化swfupload上传中需要的参数
 *
 * @param $application 应用名称
 * @param $catid 栏目id
 * @param $args 传递参数
 * @param $userid 用户id
 * @param $groupid 用户组id
 * @param $isadmin 是否为管理员模式
 */
function initupload($application, $catid = 0, $args, $userid, $groupid = '7', $isadmin = '0') {
	$grouplist = S ( 'member/grouplist' );
	if ($isadmin == 0 && ! $grouplist [$groupid] ['allowattachment']) return false;
	extract ( getswfinit ( $args ) );
	$file_size_limit = C ( 'attachment', 'maxsize' );
	$sess_id = TIME;
	$swf_auth_key = md5 ( C ( 'config', 'auth_key' ) . $sess_id );
	$init = 'var swfu = \'\';
		$(document).ready(function(){
		swfu = new SWFUpload({
			flash_url:"' . JS_PATH . 'swfupload/swfupload.swf?"+Math.random(),
			upload_url:"' . SITE_URL . 'index.php?app=attachment&controller=attachments&action=swfupload&dosubmit=1",
			file_post_name : "Filedata",
			post_params:{"SWFUPLOADSESSID":"' . $sess_id . '","application":"' . $application . '","catid":"' . $catid . '","userid":"' . $userid . '","dosubmit":"1","thumb_width":"' . $thumb_width . '","thumb_height":"' . $thumb_height . '","watermark_enable":"' . $watermark_enable . '","filetype_post":"' . $file_types_post . '","swf_auth_key":"' . $swf_auth_key . '","isadmin":"' . $isadmin . '","groupid":"' . $groupid . '"},
			file_size_limit:"' . $file_size_limit . '",
			file_types:"' . $file_types . '",
			file_types_description:"All Files",
			file_upload_limit:"' . $file_upload_limit . '",
			custom_settings : {progressTarget : "fsUploadProgress",cancelButtonId : "btnCancel"},

			button_image_url: "",
			button_width: 75,
			button_height: 28,
			button_placeholder_id: "buttonPlaceHolder",
			button_text_style: "",
			button_text_top_padding: 3,
			button_text_left_padding: 12,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,

			file_dialog_start_handler : fileDialogStart,
			file_queued_handler : fileQueued,
			file_queue_error_handler:fileQueueError,
			file_dialog_complete_handler:fileDialogComplete,
			upload_progress_handler:uploadProgress,
			upload_error_handler:uploadError,
			upload_success_handler:uploadSuccess,
			upload_complete_handler:uploadComplete
			});
		})';
	return $init;
}

function getfiles($path, &$files = array()) {
	if (! is_dir ( $path )) return;
	$handle = opendir ( $path );
	while ( false !== ($file = readdir ( $handle )) ) {
		if ($file != '.' && $file != '..') {
			$path2 = $path . '/' . $file;
			if (is_dir ( $path2 )) {
				getfiles ( $path2, $files );
			} else {
				if (is_image ( $file )) {
					$files [] = $path . '/' . $file;
				}
			}
		}
	}
	return $files;
}