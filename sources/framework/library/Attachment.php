<?php
/**
 * 附件类
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-1
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Attachment.php 427 2012-11-17 14:36:24Z xutongle $
 */
class Attachment {

	public $contentid;
	public $application;
	public $catid;
	public $attachments;
	public $field;
	public $imageexts = array ('gif','jpg','jpeg','png','bmp' );
	public $uploadedfiles = array ();
	public $downloadedfiles = array ();
	public $error;
	public $upload_root;

	public function __construct($application = '', $catid = 0) {
		$this->catid = intval ( $catid );
		$this->application = isset ( $application ) ? $application : 'content';
		$this->upload_root = C ( 'attachment', 'upload_path' );
		$this->upload_func = 'copy';
	}

	/**
	 * 附件上传方法
	 *
	 * @param $field 上传字段
	 * @param $alowexts 允许上传类型
	 * @param $maxsize 最大上传大小
	 * @param $overwrite 是否覆盖原有文件
	 * @param $thumb_setting 缩略图设置
	 * @param $watermark_enable 是否添加水印
	 */
	public function upload($field, $alowexts = '', $maxsize = 0, $overwrite = 0, $thumb_setting = array(), $watermark_enable = 1) {
		if (! isset ( $_FILES [$field] )) { // 判断附件上传字段是否为空
			$this->error = UPLOAD_ERR_OK;
			return false;
		}
		if (empty ( $alowexts ) || $alowexts == '') { // 判断限制的类型
			$alowexts = C ( 'attachment', 'allowext' );
		}
		$fn = isset ( $_GET ['CKEditorFuncNum'] ) ? $_GET ['CKEditorFuncNum'] : '1';

		$this->field = $field;
		$this->savepath = $this->upload_root . date ( 'Y/md/' );
		$this->alowexts = $alowexts;
		$this->maxsize = $maxsize;
		$this->overwrite = $overwrite;
		$uploadfiles = array ();
		$description = isset ( $GLOBALS [$field . '_description'] ) ? $GLOBALS [$field . '_description'] : array ();
		if (is_array ( $_FILES [$field] ['error'] )) {
			$this->uploads = count ( $_FILES [$field] ['error'] );
			foreach ( $_FILES [$field] ['error'] as $key => $error ) {
				if ($error === UPLOAD_ERR_NO_FILE) continue;
				if ($error !== UPLOAD_ERR_OK) {
					$this->error = $error;
					return false;
				}
				$uploadfiles [$key] = array ('tmp_name' => $_FILES [$field] ['tmp_name'] [$key],'name' => $_FILES [$field] ['name'] [$key],'type' => $_FILES [$field] ['type'] [$key],'size' => $_FILES [$field] ['size'] [$key],'error' => $_FILES [$field] ['error'] [$key],
						'description' => $description [$key],'fn' => $fn );
			}
		} else {
			$this->uploads = 1;
			if (! $description) $description = '';
			$uploadfiles [0] = array ('tmp_name' => $_FILES [$field] ['tmp_name'],'name' => $_FILES [$field] ['name'],'type' => $_FILES [$field] ['type'],'size' => $_FILES [$field] ['size'],'error' => $_FILES [$field] ['error'],'description' => $description,'fn' => $fn );
		}

		if (! Folder::create ( $this->savepath ) && ! is_dir ( $this->savepath )) {
			$this->error = '8';
			return false;
		}
		@chmod ( $this->savepath, 0755 );
		if (! is_writeable ( $this->savepath )) {
			$this->error = '9';
			return false;
		}
		$aids = array ();
		foreach ( $uploadfiles as $k => $file ) {
			$fileext = File::get_suffix ( $file ['name'] );
			if ($file ['error'] != 0) {
				$this->error = $file ['error'];
				return false;
			}
			if (! preg_match ( "/^(" . $this->alowexts . ")$/", $fileext )) {
				$this->error = '10';
				return false;
			}
			if ($this->maxsize && $file ['size'] > $this->maxsize) {
				$this->error = '11';
				return false;
			}
			if (! $this->isuploadedfile ( $file ['tmp_name'] )) {
				$this->error = '12';
				return false;
			}
			$temp_filename = $this->getname ( $fileext );
			$savefile = $this->savepath . $temp_filename;
			$savefile = preg_replace ( "/(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)(\.|$)/i", "_\\1\\2", $savefile );
			$filepath = preg_replace ( new_addslashes ( "|^" . $this->upload_root . "|" ), "", $savefile );
			if (! $this->overwrite && file_exists ( $savefile )) continue;
			$upload_func = $this->upload_func;
			if (@$upload_func ( $file ['tmp_name'], $savefile )) {
				$this->uploadeds ++;
				@chmod ( $savefile, 0755 );
				@unlink ( $file ['tmp_name'] );
				$file ['name'] = iconv ( "utf-8", CHARSET, $file ['name'] );
				$uploadedfile = array ('filename' => $file ['name'],'filepath' => $filepath,'filesize' => $file ['size'],'fileext' => $fileext,'fn' => $file ['fn'] );
				if ($this->is_image ( $file ['name'] )) {
					$thumb_enable = is_array ( $thumb_setting ) && ($thumb_setting [0] > 0 || $thumb_setting [1] > 0) ? 1 : 0;
					$image = new Image ( $thumb_enable );
					if ($thumb_enable) {
						$image->thumb ( $savefile, '', $thumb_setting [0], $thumb_setting [1] );
					}
					if ($watermark_enable) {
						$image->watermark ( $savefile, $savefile );
					}
				}
				$aids [] = $this->add ( $uploadedfile );
			}
		}
		return $aids;
	}

	/**
	 * 附件下载
	 *
	 * @param $field 预留字段
	 * @param $value 传入下载内容
	 * @param $watermark 是否加入水印
	 * @param $ext 下载扩展名
	 * @param $absurl 绝对路径
	 * @param
	 *        	$basehref
	 */
	public function download($field, $value, $watermark = '0', $ext = 'gif|jpg|jpeg|bmp|png', $absurl = '', $basehref = '') {
		$this->att_db = Loader::model ( 'attachment_model' );
		$upload_url = C ( 'attachment', 'upload_url' );
		$this->field = $field;
		$dir = date ( 'Y/md/' );
		$uploadpath = $upload_url . $dir;
		$uploaddir = $this->upload_root . $dir;
		$string = new_stripslashes ( $value );
		if (! preg_match_all ( "/(href|src)=([\"|']?)([^ \"'>]+\.($ext))\\2/i", $string, $matches )) return $value;
		$remotefileurls = array ();
		foreach ( $matches [3] as $matche ) {
			if (strpos ( $matche, '://' ) === false) continue;
			Folder::create ( $uploaddir );
			$remotefileurls [$matche] = $this->fillurl ( $matche, $absurl, $basehref );
		}
		unset ( $matches, $string );
		$remotefileurls = array_unique ( $remotefileurls );
		$oldpath = $newpath = array ();
		foreach ( $remotefileurls as $k => $file ) {
			if (strpos ( $file, '://' ) === false || strpos ( $file, $upload_url ) !== false) continue;
			$filename = File::get_suffix ( $file );
			$file_name = basename ( $file );
			$filename = $this->getname ( $filename );

			$newfile = $uploaddir . $filename;
			$upload_func = $this->upload_func;
			if ($upload_func ( $file, $newfile )) {
				$oldpath [] = $k;
				$newpath [] = $uploadpath . $filename;
				@chmod ( $newfile, 0777 );
				$fileext = File::get_suffix ( $filename );
				if ($watermark) {
					watermark ( $newfile, $newfile );
				}
				$filepath = $dir . $filename;
				$downloadedfile = array ('filename' => $filename,'filepath' => $filepath,'filesize' => filesize ( $newfile ),'fileext' => $fileext );
				$aid = $this->add ( $downloadedfile );
				$this->downloadedfiles [$aid] = $filepath;
			}
		}
		return str_replace ( $oldpath, $newpath, $value );
	}

	/**
	 * 图片转存
	 *
	 * @param $field 预留字段
	 * @param $file 传入下载内容
	 * @param $watermark 是否加入水印
	 * @param $ext 下载扩展名
	 * @param $absurl 绝对路径
	 * @param
	 *        	$basehref
	 */
	public function catcher($field, $file, $watermark = '0', $ext = 'gif|jpg|jpeg|bmp|png', $absurl = '', $basehref = '') {
		$this->field = $field;
		if (strpos ( $file, '://' ) === false) continue;
		$uploaddir = $this->upload_root . date ( 'Y/md/' );
		Folder::create ( $uploaddir );
		$fileext = File::get_suffix ( $file ); // 获取后缀
		$file_name = basename ( $file ); // 获取原始文件名
		$temp_filename = $this->getname ( $fileext ); // 获取保存的文件名
		$upload_func = $this->upload_func;
		$savefile = $uploaddir . $temp_filename;
		ini_set ( 'user_agent', 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)' );
		$savefile = preg_replace ( "/(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)(\.|$)/i", "_\\1\\2", $savefile );
		$filepath = preg_replace ( new_addslashes ( "|^" . $this->upload_root . "|" ), "", $savefile );
		$aids = array ();
		if ($upload_func ( $file, $savefile )) {
			@chmod ( $savefile, 0755 );
			$file_name = iconv ( "utf-8", CHARSET, $file_name );
			$downloadedfile = array ('filename' => $file_name,'filepath' => $filepath,'filesize' => filesize ( $savefile ),'fileext' => $fileext );
			if ($watermark) { // 添加水印
				watermark ( $savefile, $savefile );
			}
			$aids [] = $this->add ( $downloadedfile );
		}
		return $aids;
	}

	/**
	 * base64上传文件
	 */
	public function upload_base64($field, $watermark_enable = 1) {
		$base64Data = $_POST [$field];
		if (! isset ( $base64Data ) && ! empty ( $base64Data )) {
			$this->error = UPLOAD_ERR_OK;
			return false;
		}
		$this->alowexts = C ( 'attachment', 'allowext' );
		$img = base64_decode ( $base64Data );
		$this->savepath = $this->upload_root . date ( 'Y/md/' );
		$this->uploads = 1;

		if (! Folder::create ( $this->savepath ) && ! is_dir ( $this->savepath )) {
			$this->error = '8';
			return false;
		}
		@chmod ( $this->savepath, 0755 );
		if (! is_writeable ( $this->savepath )) {
			$this->error = '9';
			return false;
		}
		$aids = array ();
		$filename = $this->getname ( "png" );
		$savefile = $this->savepath . $filename;
		$filepath = preg_replace ( new_addslashes ( "|^" . $this->upload_root . "|" ), "", $savefile );
		if (file_put_contents ( $savefile, $img )) {
			@chmod ( $savefile, 0755 );
			$uploadedfile = array ('filename' => $filename,'filepath' => $filepath,'filesize' => strlen ( $img ),'fileext' => "png",'fn' => "1" );
			if ($watermark_enable) {
				watermark ( $savefile, $savefile );
			}
			$aids [] = $this->add ( $uploadedfile );
		}
		return $aids;
	}

	/**
	 * 上传无记录的临时文件
	 *
	 * @param unknown_type $field
	 */
	public function upload_tmp($field) {
		$tmpPath = $this->upload_root . "tmp/";
		if (! isset ( $_FILES [$field] )) { // 判断附件上传字段是否为空
			$this->error = UPLOAD_ERR_OK;
			return false;
		}
		// 判断限制的类型
		$this->alowexts = C ( 'attachment', 'allowext' );
		$this->savepath = $tmpPath;
		$this->maxsize = C ( 'attachment', 'maxsize' ) * 1024;
		$this->uploads = 1;

		if (! Folder::create ( $this->savepath ) && ! is_dir ( $this->savepath )) {
			$this->error = '8';
			return false;
		}
		@chmod ( $this->savepath, 0755 );
		if (! is_writeable ( $this->savepath )) {
			$this->error = '9';
			return false;
		}
		$file = $_FILES [$field];
		if (is_array ( $file ['error'] )) {
			$this->error = '5';
			return false;
		} else {
			$this->uploads = 1;
		}
		$fileext = File::get_suffix ( $file ['name'] );
		if ($file ['error'] != 0) {
			$this->error = $file ['error'];
			return false;
		}
		if (! preg_match ( "/^(" . $this->alowexts . ")$/", $fileext )) {
			$this->error = '10';
			return false;
		}
		if ($this->maxsize && $file ['size'] > $this->maxsize) {
			$this->error = '11';
			return false;
		}
		if (! $this->isuploadedfile ( $file ['tmp_name'] )) {
			$this->error = '12';
			return false;
		}
		$filename = $this->getname ( "png" );
		$savefile = $this->savepath . $filename;
		$filepath = preg_replace ( new_addslashes ( "|^" . $this->upload_root . "|" ), "", $savefile );
		$upload_func = $this->upload_func;
		if (@$upload_func ( $file ['tmp_name'], $savefile )) {
			@chmod ( $savefile, 0755 );
			@unlink ( $file ['tmp_name'] );
			return $filepath;
		} else {
			return false;
		}
	}

	/**
	 * 附件删除方法
	 *
	 * @param $where 删除sql语句
	 */
	public function delete($where) {
		$this->att_db = Loader::model ( 'attachment_model' );
		$result = $this->att_db->where ( $where )->select ();
		foreach ( $result as $r ) {
			$image = $this->upload_root . $r ['filepath'];
			@unlink ( $image );
			$thumbs = glob ( dirname ( $image ) . '/*' . basename ( $image ) );
			if ($thumbs) foreach ( $thumbs as $thumb )
				@unlink ( $thumb );
		}
		return $this->att_db->where ( $where )->delete ();
	}

	/**
	 * 附件添加如数据库
	 *
	 * @param $uploadedfile 附件信息
	 */
	public function add($uploadedfile) {
		$this->att_db = Loader::model ( 'attachment_model' );
		$uploadedfile ['application'] = $this->application;
		$uploadedfile ['catid'] = $this->catid;
		$uploadedfile ['userid'] = $this->userid;
		$uploadedfile ['uploadtime'] = TIME;
		$uploadedfile ['uploadip'] = IP;
		$uploadedfile ['status'] = C ( 'attachment', 'stat' ) ? 0 : 1;
		$uploadedfile ['authcode'] = md5 ( $uploadedfile ['filepath'] );
		$uploadedfile ['filename'] = strlen ( $uploadedfile ['filename'] ) > 49 ? $this->getname ( $uploadedfile ['fileext'] ) : $uploadedfile ['filename'];
		$uploadedfile ['isimage'] = in_array ( $uploadedfile ['fileext'], $this->imageexts ) ? 1 : 0;
		$aid = $this->att_db->api_add ( $uploadedfile );
		$this->uploadedfiles [] = $uploadedfile;
		return $aid;
	}
	public function set_userid($userid) {
		$this->userid = $userid;
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
	 * 获取缩略图地址..
	 *
	 * @param $image 图片路径
	 */
	public function get_thumb($image) {
		return str_replace ( '.', '_thumb.', $image );
	}

	/**
	 * 获取附件名称
	 *
	 * @param $fileext 附件扩展名
	 */
	public function getname($fileext) {
		return date ( 'Ymdhis' ) . rand ( 100, 999 ) . '.' . $fileext;
	}

	/**
	 * 返回附件大小
	 *
	 * @param $filesize 图片大小
	 */
	public function size($filesize) {
		if ($filesize >= 1073741824) {
			$filesize = round ( $filesize / 1073741824 * 100 ) / 100 . ' GB';
		} elseif ($filesize >= 1048576) {
			$filesize = round ( $filesize / 1048576 * 100 ) / 100 . ' MB';
		} elseif ($filesize >= 1024) {
			$filesize = round ( $filesize / 1024 * 100 ) / 100 . ' KB';
		} else {
			$filesize = $filesize . ' Bytes';
		}
		return $filesize;
	}

	/**
	 * 判断文件是否是通过 HTTP POST 上传的
	 *
	 * @param string $file
	 * @return bool HTTP POST 上传的则返回 TRUE
	 */
	public function isuploadedfile($file) {
		return is_uploaded_file ( $file ) || is_uploaded_file ( str_replace ( '\\\\', '\\', $file ) );
	}

	/**
	 * 补全网址
	 *
	 * @param string $surl
	 * @param string $absurl
	 * @param string $basehref
	 * @return string
	 */
	public function fillurl($surl, $absurl, $basehref = '') {
		if ($basehref != '') {
			$preurl = strtolower ( substr ( $surl, 0, 6 ) );
			if ($preurl == 'http://' || $preurl == 'ftp://' || $preurl == 'mms://' || $preurl == 'rtsp://' || $preurl == 'thunde' || $preurl == 'emule://' || $preurl == 'ed2k://')
				return $surl;
			else
				return $basehref . '/' . $surl;
		}
		$i = 0;
		$dstr = '';
		$pstr = '';
		$okurl = '';
		$pathStep = 0;
		$surl = trim ( $surl );
		if ($surl == '') return '';
		$urls = @parse_url ( SITE_HOST );
		$HomeUrl = $urls ['host'];
		$BaseUrlPath = $HomeUrl . $urls ['path'];
		$BaseUrlPath = preg_replace ( "/\/([^\/]*)\.(.*)$/", '/', $BaseUrlPath );
		$BaseUrlPath = preg_replace ( "/\/$/", '', $BaseUrlPath );
		$pos = strpos ( $surl, '#' );
		if ($pos > 0) $surl = substr ( $surl, 0, $pos );
		if ($surl [0] == '/') {
			$okurl = 'http://' . $HomeUrl . '/' . $surl;
		} elseif ($surl [0] == '.') {
			if (strlen ( $surl ) <= 2)
				return '';
			elseif ($surl [0] == '/') {
				$okurl = 'http://' . $BaseUrlPath . '/' . substr ( $surl, 2, strlen ( $surl ) - 2 );
			} else {
				$urls = explode ( '/', $surl );
				foreach ( $urls as $u ) {
					if ($u == "..")
						$pathStep ++;
					else if ($i < count ( $urls ) - 1)
						$dstr .= $urls [$i] . '/';
					else
						$dstr .= $urls [$i];
					$i ++;
				}
				$urls = explode ( '/', $BaseUrlPath );
				if (count ( $urls ) <= $pathStep)
					return '';
				else {
					$pstr = 'http://';
					for($i = 0; $i < count ( $urls ) - $pathStep; $i ++) {
						$pstr .= $urls [$i] . '/';
					}
					$okurl = $pstr . $dstr;
				}
			}
		} else {
			$preurl = strtolower ( substr ( $surl, 0, 6 ) );
			if (strlen ( $surl ) < 7)
				$okurl = 'http://' . $BaseUrlPath . '/' . $surl;
			elseif ($preurl == "http:/" || $preurl == 'ftp://' || $preurl == 'mms://' || $preurl == "rtsp://" || $preurl == 'thunde' || $preurl == 'emule:' || $preurl == 'ed2k:/')
				$okurl = $surl;
			else
				$okurl = 'http://' . $BaseUrlPath . '/' . $surl;
		}
		$preurl = strtolower ( substr ( $okurl, 0, 6 ) );
		if ($preurl == 'ftp://' || $preurl == 'mms://' || $preurl == 'rtsp://' || $preurl == 'thunde' || $preurl == 'emule:' || $preurl == 'ed2k:/') {
			return $okurl;
		} else {
			$okurl = preg_replace ( '/^(http:\/\/)/i', '', $okurl );
			$okurl = preg_replace ( '/\/{1,}/i', '/', $okurl );
			return 'http://' . $okurl;
		}
	}

	/**
	 * 返回错误信息
	 */
	public function error() {
		$UPLOAD_ERROR = array (0 => L ( 'att_upload_succ' ),1 => L ( 'att_upload_limit_ini' ),2 => L ( 'att_upload_limit_filesize' ),3 => L ( 'att_upload_limit_part' ),4 => L ( 'att_upload_nofile' ),5 => '',6 => L ( 'att_upload_notemp' ),7 => L ( 'att_upload_temp_w_f' ),
				8 => L ( 'att_upload_create_dir_f' ),9 => L ( 'att_upload_dir_permissions' ),10 => L ( 'att_upload_limit_ext' ),11 => L ( 'att_upload_limit_setsize' ),12 => L ( 'att_upload_not_allow' ),13 => L ( 'att_upload_limit_time' ) );
		return iconv ( CHARSET, "utf-8", $UPLOAD_ERROR [$this->error] );
	}

	/**
	 * ck编辑器返回
	 *
	 * @param
	 *        	$fn
	 * @param $fileurl 路径
	 * @param $message 显示信息
	 */
	public function mkhtml($fn, $fileurl, $message) {
		$str = '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $fn . ', \'' . $fileurl . '\', \'' . $message . '\');</script>';
		exit ( $str );
	}

	/**
	 * flash上传调试方法
	 *
	 * @param
	 *        	$id
	 */
	public function uploaderror($id = 0) {
		file_put_contents ( SOURCE_PATH . 'xxx.txt', $id );
	}
}