<?php
/**
 * 附件管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: ManageController.php 277 2012-11-08 09:42:56Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class ManageController extends admin {

	private $db;

	public function __construct() {
		parent::__construct ();
		Loader::helper ( 'attachment:global' );
		$this->upload_url = C ( 'attachment', 'upload_url' );
		$this->upload_path = C ( 'attachment', 'upload_path' );
		$this->imgext = array ('jpg','gif','png','bmp','jpeg' );
		$this->db = Loader::model ( 'attachment_model' );
		$this->attachment = Loader::lib ( 'Attachment' );
		$this->admin_username = cookie ( 'admin_username' );
	}

	/**
	 * 附件列表
	 */
	public function init() {
		$where = '';
		if (isset ( $_GET ['dosubmit'] )) {
			if (isset ( $_GET ['info'] ) && (is_array ( $_GET ['info'] ) && ! empty ( $_GET ['info'] ))) extract ( $_GET ['info'] );
			if (isset ( $filename )) $where = "AND `filename` LIKE '%$filename%' ";
			if (isset ( $start_uploadtime ) && isset ( $end_uploadtime )) {
				$start = strtotime ( $start_uploadtime );
				$end = strtotime ( $end_uploadtime );
				if ($start > $end) showmessage ( L ( 'range_not_correct' ), HTTP_REFERER );
				$where .= "AND `uploadtime` >= '$start' AND  `uploadtime` <= '$end' ";
			}
			if (isset ( $fileext )) $where .= "AND `fileext`='$fileext' ";
			$status = isset ( $_GET ['status'] ) ? trim ( $_GET ['status'] ) : '';
			if ($status != '' && ($status == 1 || $status == 0)) $where .= "AND `status`='$status' ";
			$application = isset ( $_GET ['application'] ) ? trim ( $_GET ['application'] ) : '';
			if (isset ( $application ) && $application != '') $where .= "AND `application`='$application' ";
		}
		if ($where) $where = substr ( $where, 3 );
		$category = S ( 'common/category_content' );
		$applications = S ( 'common/application' );
		$page = isset ( $_GET ['page'] ) ? $_GET ['page'] : 1;
		$infos = $this->db->where($where)->order('uploadtime DESC')->listinfo ($page, 20 );
		$pages = $this->db->pages;
		include $this->admin_tpl ( 'attachment_list' );
	}

	/**
	 * 目录浏览模式添加图片
	 */
	public function dir() {
		if (! $this->admin_username) return false;
		$dir = isset ( $_GET ['dir'] ) && trim ( $_GET ['dir'] ) ? str_replace ( array ('..\\','../','./','.\\' ), '', trim ( $_GET ['dir'] ) ) : '';
		$filepath = $this->upload_path . $dir;
		$list = glob ( $filepath . '/' . '*' );
		if (! empty ( $list )) rsort ( $list );
		$local = str_replace ( array (SOURCE_PATH,BASE_PATH,DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR ), array ('','',DIRECTORY_SEPARATOR ), $filepath );
		include $this->admin_tpl ( 'attachment_dir' );
	}

	/**
	 * 目录模式附件删除
	 */
	public function pulic_dirmode_del() {
		$filename = urldecode ( $_GET ['filename'] );
		$dir = urldecode ( $_GET ['dir'] );
		$file = BASE_PATH . $dir . DIRECTORY_SEPARATOR . $filename;
		$file = str_replace ( array ('/','\\' ), DIRECTORY_SEPARATOR, $file );
		if (@unlink ( $file )) {
			echo '1';
		} else {
			echo '0';
		}
	}

	/**
	 * 删除附件
	 */
	public function delete() {
		$aid = intval($_GET ['aid']);
		$attachment_index = Loader::model ( 'attachment_index_model' );
		if ($this->attachment->delete (array ('aid' => $aid ))) {
			$attachment_index->where ( array ('aid' => $aid ) )->delete();
			exit ( '1' );
		} else {
			exit ( '0' );
		}
	}

	/**
	 * 批量删除附件
	 */
	public function public_delete_all() {
		$del_arr = array ();
		$del_arr = $_POST ['aid'];
		$attachment_index = Loader::model ( 'attachment_index_model' );
		if (is_array ( $del_arr )) {
			foreach ( $del_arr as $v ) {
				$aid = intval ( $v );
				$this->attachment->delete(array ('aid' => $aid ));
				$attachment_index->where ( array ('aid' => $aid ) )->delete();
			}
			showmessage ( L ( 'delete' ) . L ( 'success' ), HTTP_REFERER );
		}
	}

	/**
	 * 查看缩略图
	 */
	public function pullic_showthumbs() {
		$aid = intval ( $_GET ['aid'] );
		$info = $this->db->where ( array ('aid' => $aid ) )->find();
		if ($info) {
			$infos = glob ( dirname ( $this->upload_path . $info ['filepath'] ) . '/thumb_*' . basename ( $info ['filepath'] ) );
			foreach ( $infos as $n => $thumb ) {
				$thumbs [$n] ['thumb_url'] = str_replace ( $this->upload_path, $this->upload_url, $thumb );
				$thumbinfo = explode ( '_', basename ( $thumb ) );
				$thumbs [$n] ['thumb_filepath'] = $thumb;
				$thumbs [$n] ['width'] = $thumbinfo [1];
				$thumbs [$n] ['height'] = $thumbinfo [2];
			}
		}
		$show_header = 1;
		include $this->admin_tpl ( 'attachment_thumb' );
	}

	/**
	 * 删除缩略图
	 */
	public function pullic_delthumbs() {
		$filepath = urldecode ( $_GET ['filepath'] );
		$reslut = @unlink ( $filepath );
		if ($reslut) exit ( '1' );
		exit ( '0' );
	}
}