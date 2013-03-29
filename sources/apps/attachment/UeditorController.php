<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: UeditorController.php 95 2013-03-23 15:27:53Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::session ();
// error_reporting ( E_ERROR );
class UeditorController {
	private $db;

	public function __construct() {
		Loader::func ( 'attachment:global' );
		$this->upload_url = C ( 'attachment', 'upload_url' );
		$this->upload_path = C ( 'attachment', 'upload_path' );
		$this->imgext = array ('jpg','gif','png','bmp','jpeg' );
		$this->userid = cookie_get ( 'userid' ) ? cookie_get ( 'userid' ) : 0;
		$this->isadmin = isset ( $_SESSION ['roleid'] ) ? 1 : 0;
		$this->groupid = cookie_get ( '_groupid' ) ? cookie_get ( '_groupid' ) : 1;
		$this->admin_username = cookie_get ( 'admin_username' );
	}

	public function manage() {
		if (! $this->admin_username) return false;
		set_time_limit ( 0 ); // 最多显示400张
		$infos = Loader::model ( 'attachment_model' )->select ( '', 'storage,filepath', 400, 'aid DESC' );
		$str = "";
		foreach ( $infos as $r ) {
			$ext = File::get_suffix ( $r ['filepath'] );
			if (in_array ( $ext, $this->imgext )) {
				$str .= upload_url ( $r ['storage'] ) . $r ['filepath'] . 'ue_separate_ue';
			}
		}
		exit ( $str );
	}

	/**
	 * 图片上传
	 */
	public function upimg() {
		$grouplist = S ( 'member/grouplist' );
		if ($this->isadmin == 0 && ! $grouplist [$this->groupid] ['allowattachment']) {
			$return = array ('original' => '','state' => '不允许上传该类型附件！' );
			exit ( json_encode ( $return ) );
		}
		$application = isset ( $_GET ['application'] ) ? trim ( $_GET ['application'] ) : ''; // 应用名称
		$catid = isset ( $_GET ['catid'] ) ? intval ( $_GET ['catid'] ) : 0; // 栏目ID
		$oriname = isset ( $_POST ['fileName'] ) ? $_POST ['fileName'] : ''; // 原始文件名，表单名固定，不可配置
		$site_allowext = C ( 'attachment', 'allowext' ); // 允许的后缀
		$attachment = new Attachment ( $application, $catid );
		$attachment->set_userid ( $this->userid );
		$aids = $attachment->upload ( 'upfile', $site_allowext, '', '', array (0,0 ) );
		if ($aids [0]) {
			$title = (strtolower ( CHARSET ) != 'utf-8') ? iconv ( 'gbk', 'utf-8', $attachment->uploadedfiles [0] ['filename'] ) : $attachment->uploadedfiles [0] ['filename'];
			$return = array ('url' => upload_url ( $attachment->uploadedfiles [0] ['storage'] ) . $attachment->uploadedfiles [0] ['filepath'],'title' => $title,'original' => $oriname,'state' => 'SUCCESS' );
		} else {
			$return = array ('original' => $oriname,'state' => $attachment->error () );
		}
		exit ( json_encode ( $return ) );
	}

	/**
	 * 附件上传
	 */
	public function upfile() {
		$grouplist = S ( 'member/grouplist' );
		if ($_POST ['swf_auth_key'] != md5 ( C ( 'framework', 'auth_key' ) . $_POST ['SWFUPLOADSESSID'] ) || ($_POST ['isadmin'] == 0 && ! $grouplist [$_POST ['groupid']] ['allowattachment'])) exit ();
		$application = trim ( $_GET ['application'] );
		$catid = intval ( $_GET ['catid'] );
		$attachment = new Attachment ( $application, $catid );
		$attachment->set_userid ( $_POST ['userid'] );
		$aids = $attachment->upload ( 'upfile', C ( 'attachment', 'allowext' ), '', '', array (0,0 ) );
		if ($aids [0]) {
			$filepath = $attachment->uploadedfiles [0] ['filepath'];
			$return = array ('url' => upload_url ( $attachment->uploadedfiles [0] ['storage'] ) . $filepath,'fileType' => '.' . $attachment->uploadedfiles [0] ['fileext'],'original' => $attachment->uploadedfiles [0] ['filename'],'state' => 'SUCCESS' );
		} else {
			$return = array ('original' => $attachment->uploadedfiles [0] ['filename'],'state' => $attachment->error () );
		}
		exit ( json_encode ( $return ) );
	}

	/**
	 * 远程图片抓取
	 */
	public function get_remoteimage() {
		$grouplist = S ( 'member/grouplist' );
		if ($this->isadmin == 0 && ! $grouplist [$this->groupid] ['allowattachment']) exit ();
		$application = trim ( $_GET ['application'] );
		$catid = intval ( $_GET ['catid'] );
		$site_allowext = C ( 'attachment', 'allowext' );
		$watermark_enable = C ( 'attachment', 'watermark_enable' );
		$uri = htmlspecialchars ( $_POST ['upfile'] );
		$uri = str_replace ( "&amp;", "&", $uri );
		// 忽略抓取时间限制
		set_time_limit ( 0 );
		// ue_separate_ue ue用于传递数据分割符号
		$imgUrls = explode ( "ue_separate_ue", $uri );
		$tmpNames = array ();
		$aids = array ();
		foreach ( $imgUrls as $imgUrl ) {
			$attachment = new Attachment ( $application, $catid );
			$attachment->set_userid ( $this->userid );
			$aid = $attachment->catcher ( 'upfile', $imgUrl, $watermark_enable, $site_allowext );
			array_push ( $tmpNames, upload_url ( $attachment->uploadedfiles [0] ['storage'] ) . $attachment->uploadedfiles [0] ['filepath'] );
			array_push ( $aids, $aid [0] );
		}
		echo "{'url':'" . implode ( "ue_separate_ue", $tmpNames ) . "','tip':'远程图片抓取成功！','srcUrl':'" . $uri . "','aids':'" . implode ( "ue_separate_ue", $aids ) . "'}";
	}

	/**
	 * 涂鸦
	 */
	public function scrawl() {
		$grouplist = S ( 'member/grouplist' );
		if ($_GET ['swf_auth_key'] != md5 ( C ( 'framework', 'auth_key' ) . $_GET ['SWFUPLOADSESSID'] ) || ($_GET ['isadmin'] == 0 && ! $grouplist [$_GET ['groupid']] ['allowattachment'])) exit ();
		$application = trim ( $_GET ['application'] );
		$catid = intval ( $_GET ['catid'] );
		$attachment = new Attachment ( $application, $catid );
		$attachment->set_userid ( $_GET ['userid'] );
		if (isset ( $_GET ['act'] ) && $_GET ['act'] == "tmpImg") { // 背景上传
			$filepath = $attachment->upload_tmp ( 'upfile' );
			if ($filepath) {
				echo "<script>parent.ue_callback('" . $this->upload_url . $filepath . "','SUCCESS')</script>";
				exit ();
			} else {
				echo "<script>parent.ue_callback('','" . $attachment->error () . "')</script>";
				exit ();
			}
		} else {
			$watermark_enable = isset($_GET ['watermark_enable']) ? $_GET ['watermark_enable'] : 0;
			$aids = $attachment->upload_base64 ( 'content', $watermark_enable);
			if ($aids [0]) {
				$filename = (strtolower ( CHARSET ) != 'utf-8') ? iconv ( 'gbk', 'utf-8', $attachment->uploadedfiles [0] ['filename'] ) : $attachment->uploadedfiles [0] ['filename'];
				Folder::delete($this->upload_path . "tmp/");// 上传成功后删除临时目录
				echo "{'url':'" . upload_url($attachment->uploadedfiles [0]['storage']) . $attachment->uploadedfiles [0] ['filepath'] . "','state':'SUCCESS','aid':'" . $aids [0] . "','filename':'" . $filename . "'}";
				exit ();
			} else {
				echo "{'state':'" . $attachment->error () . "'}";
				exit ();
			}
		}
	}

}