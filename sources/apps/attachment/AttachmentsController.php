<?php
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: AttachmentsController.php 281 2013-04-02 04:05:16Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::session ();
error_reporting ( E_ERROR );
class AttachmentsController {

	private $att_db;

	public function __construct() {
		Loader::helper ( 'attachment:global' );
		$this->upload_url = C ( 'attachment', 'upload_url' );
		$this->upload_path = C ( 'attachment', 'upload_path' );
		$this->imgext = array ('jpg','gif','png','bmp','jpeg' );
		$this->userid = cookie ( 'userid' ) ? cookie ( 'userid' ) : 0;
		$this->isadmin = isset($_SESSION['roleid']) ? 1 : 0;
		$this->groupid = cookie ( '_groupid' ) ? cookie ( '_groupid' ) : 1;
		$this->admin_username = cookie ( 'admin_username' );
	}

	/**
	 * 常规上传
	 */
	public function upload() {
		$grouplist = S ( 'member/grouplist' );
		if ($this->isadmin == 0 && ! $grouplist [$this->groupid] ['allowattachment']) return false;
		$application = trim ( $_GET ['application'] );
		$catid = intval ( $_GET ['catid'] );
		$site_allowext = C ( 'attachment', 'allowext' );
		$attachment = new Attachment ( $application, $catid );
		$attachment->set_userid ( $this->userid );
		$a = $attachment->upload ( 'upload', $site_allowext );
		if ($a) {
			$filepath = $attachment->uploadedfiles [0] ['filepath'];
			$fn = $attachment->uploadedfiles [0] ['fn'];
			$attachment->mkhtml ( $fn,$this->upload_url . $filepath, '' );
		}
	}

	/**
	 * swfupload上传附件
	 */
	public function swfupload() {
		$grouplist = S ( 'member/grouplist' );
		if (isset ( $_POST ['dosubmit'] )) {
			if ($_POST ['swf_auth_key'] != md5 ( C ( 'config', 'auth_key' ) . $_POST ['SWFUPLOADSESSID'] ) || ($_POST ['isadmin'] == 0 && ! $grouplist [$_POST ['groupid']] ['allowattachment'])) exit ();
			$catid = isset ( $_POST ['catid'] ) ? intval ( $_POST ['catid'] ) : 0;
			$attachment = new Attachment ( $_POST ['application'], $catid );
			$attachment->set_userid ( $_POST ['userid'] );
			$aids = $attachment->upload ( 'Filedata', $_POST ['filetype_post'], '', '', array ($_POST ['thumb_width'],$_POST ['thumb_height'] ), $_POST ['watermark_enable'] );
			if ($aids [0]) {
				$filename = (strtolower ( CHARSET ) != 'utf-8') ? iconv ( 'gbk', 'utf-8', $attachment->uploadedfiles [0] ['filename'] ) : '';
				if ($attachment->uploadedfiles [0] ['isimage']) {
					echo $aids [0] . ',' . $this->upload_url . $attachment->uploadedfiles [0] ['filepath'] . ',' . $attachment->uploadedfiles [0] ['isimage'] . ',' . $filename;
				} else {
					$fileext = $attachment->uploadedfiles [0] ['fileext'];
					if ($fileext == 'zip' || $fileext == 'rar')
						$fileext = 'rar';
					elseif ($fileext == 'doc' || $fileext == 'docx')
						$fileext = 'doc';
					elseif ($fileext == 'xls' || $fileext == 'xlsx')
						$fileext = 'xls';
					elseif ($fileext == 'ppt' || $fileext == 'pptx')
						$fileext = 'ppt';
					elseif ($fileext == 'flv' || $fileext == 'swf' || $fileext == 'rm' || $fileext == 'rmvb')
						$fileext = 'flv';
					else
						$fileext = 'do';
					echo $aids [0] . ',' . $this->upload_url . $attachment->uploadedfiles [0] ['filepath'] . ',' . $fileext . ',' . $filename;
				}
				exit ();
			} else {
				echo '0,' . $attachment->error ();
				exit ();
			}
		} else {
			if ($this->isadmin == 0 && ! $grouplist [$this->groupid] ['allowattachment']) showmessage ( L ( 'att_no_permission' ) );
			$args = $_GET ['args'];
			$authkey = $_GET ['authkey'];
			if (upload_key ( $args ) != $authkey) showmessage ( L ( 'attachment_parameter_error' ) );
			extract ( getswfinit ( $_GET ['args'] ) );
			$file_size_limit = byte_format ( C ( 'attachment', 'maxsize' ) * 1024 );
			$att_not_used = cookie ( 'att_json' );
			if (empty ( $att_not_used ) || ! isset ( $att_not_used )) $tab_status = ' class="on"';
			if (! empty ( $att_not_used )) $div_status = ' hidden';
			// 获取临时未处理文件列表
			$att = $this->att_not_used ();
			include $this->admin_tpl ( 'swfupload' );
		}
	}

	/**
	 * 图片裁切
	 *
	 * @return boolean
	 */
	public function crop_upload() {
		if (isset ( $GLOBALS ["HTTP_RAW_POST_DATA"] )) {
			$pic = $GLOBALS ["HTTP_RAW_POST_DATA"];
			if (isset ( $_GET ['width'] ) && ! empty ( $_GET ['width'] )) {
				$width = intval ( $_GET ['width'] );
			}
			if (isset ( $_GET ['height'] ) && ! empty ( $_GET ['height'] )) {
				$height = intval ( $_GET ['height'] );
			}
			if (isset ( $_GET ['file'] ) && ! empty ( $_GET ['file'] )) {
				if (is_image ( $_GET ['file'] ) == false) exit ();
				if (strpos ( $_GET ['file'], C ( 'attachment', 'upload_url' ) ) !== false) {
					$file = $_GET ['file'];
					$basename = basename ( $file );
					$filepath = str_replace ( SITE_URL, '', dirname ( $file ) ) . '/';
					if (strpos ( $basename, 'thumb_' ) !== false) {
						$file_arr = explode ( '_', $basename );
						$basename = array_pop ( $file_arr );
					}
					$new_file = 'thumb_' . $width . '_' . $height . '_' . $basename;
				} else {
					$application = trim ( $_GET ['application'] );
					$catid = intval ( $_GET ['catid'] );
					$attachment = new Attachment ( $application, $catid );
					$uploadedfile ['filename'] = basename ( $_GET ['file'] );
					$uploadedfile ['fileext'] = File::get_suffix ( $_GET ['file'] );
					if (in_array ( $uploadedfile ['fileext'], array ('jpg','gif','jpeg','png','bmp' ) )) {
						$uploadedfile ['isimage'] = 1;
					}
					$file_path = C ( 'attachment', 'upload_path' ) . date ( 'Y/md/' );
					Folder::create ( $file_path );
					$new_file = date ( 'Ymdhis' ) . rand ( 100, 999 ) . '.' . $uploadedfile ['fileext'];
					$uploadedfile ['filepath'] = date ( 'Y/md/' ) . $new_file;
					$aid = $attachment->add ( $uploadedfile );
					$filepath = str_replace ( SITE_URL, '', C ( 'attachment', 'upload_url' ) ) . date ( 'Y/md/' );
				}
				file_put_contents ( BASE_PATH . $filepath . $new_file, $pic );
			} else {
				return false;
			}
			echo SITE_URL . $filepath . $new_file;
			exit ();
		}
	}

	/**
	 * 删除附件
	 */
	public function swfdelete() {
		$attachment = Loader::lib ( 'Attachment' );
		$att_del_arr = explode ( '|', $_GET ['data'] );
		foreach ( $att_del_arr as $n => $att ) {
			if ($att) $attachment->where ( array ('aid' => $att,'userid' => $this->userid,'uploadip' => IP ) )->delete();
		}
	}

	/**
	 * 加载图片库
	 */
	public function album_load() {
		if (! $this->admin_username) return false;
		$where = $uploadtime = '';
		$this->att_db = Loader::model ( 'attachment_model' );
		if ($_GET ['args']) extract ( getswfinit ( $_GET ['args'] ) );
		if (isset ( $_GET ['dosubmit'] )) {
			extract ( $_GET ['info'] );
			if (isset ( $filename )) $where = "AND `filename` LIKE '%$filename%' ";
			if ($uploadtime) {
				$start_uploadtime = strtotime ( $uploadtime . ' 00:00:00' );
				$stop_uploadtime = strtotime ( $uploadtime . ' 23:59:59' );
				$where .= "AND `uploadtime` >= '$start_uploadtime' AND  `uploadtime` <= '$stop_uploadtime'";
			}
			if ($where) $where = substr ( $where, 3 );
		}
		$page = isset ( $_GET ['page'] ) ? $_GET ['page'] : 1;
		$infos = $this->att_db->listinfo ( $where, $order = 'aid DESC', $page, $pagesize = 8, '', 5 );
		foreach ( $infos as $n => $v ) {
			$ext = File::get_suffix ( $v ['filepath'] );
			if (in_array ( $ext, $this->imgext )) {
				$infos [$n] ['src'] = $this->upload_url . $v ['filepath'];
				$infos [$n] ['width'] = '80';
			} else {
				$infos [$n] ['src'] = file_icon ( $v ['filepath'] );
				$infos [$n] ['width'] = '64';
			}
		}
		$pages = $this->att_db->pages;
		include $this->admin_tpl ( 'album_list' );
	}

	/**
	 * 目录浏览模式添加图片
	 */
	public function album_dir() {
		if (! $this->admin_username) return false;
		if ($_GET ['args']) extract ( getswfinit ( $_GET ['args'] ) );
		$dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? str_replace ( array ('..\\','../','./','.\\' ), '', trim ( $_GET ['dir'] ) ) : '';
		$filepath = $this->upload_path . $dir;
		$list = glob ( $filepath . '/' . '*' );
		if (! empty ( $list )) rsort ( $list );
		$local = str_replace ( array (SOURCE_PATH,BASE_PATH,DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR ), array ('','',DIRECTORY_SEPARATOR ), $filepath );
		$url = ($dir == '.' || $dir == '') ? $this->upload_url : $this->upload_url . str_replace ( '.', '', $dir ) . '/';
		$show_header = true;
		include $this->admin_tpl ( 'album_dir' );
	}

	/**
	 * 设置swfupload上传的json格式cookie
	 */
	public function swfupload_json() {
		$arr ['aid'] = intval ( $_GET ['aid'] );
		$arr ['src'] = trim ( $_GET ['src'] );
		$arr ['filename'] = urlencode ( $_GET ['filename'] );
		$json_str = json_encode ( $arr );
		$att_arr_exist = cookie ( 'att_json' );
		$att_arr_exist_tmp = explode ( '||', $att_arr_exist );
		if (is_array ( $att_arr_exist_tmp ) && in_array ( $json_str, $att_arr_exist_tmp )) {
			return true;
		} else {
			$json_str = $att_arr_exist ? $att_arr_exist . '||' . $json_str : $json_str;
			cookie ( 'att_json', $json_str );
			return true;
		}
	}

	/**
	 * 删除swfupload上传的json格式cookie
	 */
	public function swfupload_json_del() {
		$arr ['aid'] = intval ( $_GET ['aid'] );
		$arr ['src'] = trim ( $_GET ['src'] );
		$arr ['filename'] = urlencode ( $_GET ['filename'] );
		$json_str = json_encode ( $arr );
		$att_arr_exist = cookie ( 'att_json' );
		$att_arr_exist = str_replace ( array ($json_str,'||||' ), array ('','||' ), $att_arr_exist );
		$att_arr_exist = preg_replace ( '/^\|\|||\|\|$/i', '', $att_arr_exist );
		cookie ( 'att_json', $att_arr_exist );
	}

	/**
	 * 获取临时未处理文件列表
	 *
	 * @return Ambigous <boolean, mixed, string>
	 */
	private function att_not_used() {
		$this->att_db = Loader::model ( 'attachment_model' );
		if ($att_json = cookie ( 'att_json' )) {
			if ($att_json) $att_cookie_arr = explode ( '||', $att_json );
			foreach ( $att_cookie_arr as $_att_c )
				$att [] = json_decode ( $_att_c, true );
			if (is_array ( $att ) && ! empty ( $att )) {
				foreach ( $att as $n => $v ) {
					$ext = File::get_suffix ( $v ['src'] );
					if (in_array ( $ext, $this->imgext )) {
						$att [$n] ['fileimg'] = $v ['src'];
						$att [$n] ['width'] = '80';
						$att [$n] ['filename'] = urldecode ( $v ['filename'] );
					} else {
						$att [$n] ['fileimg'] = file_icon ( $v ['src'] );
						$att [$n] ['width'] = '64';
						$att [$n] ['filename'] = urldecode ( $v ['filename'] );
					}
					$this->cookie_att .= '|' . $v ['src'];
				}
			}
		}
		return isset ( $att ) ? $att : false;
	}

	final public static function admin_tpl($file, $app = '') {
		 $app = empty( $app) ? APP :  $app;
		if(empty( $app)) return false;
		return APPS_PATH.$app.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$file.'.tpl.php';
	}
}