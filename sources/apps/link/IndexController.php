<?php
/**
 * 友情连接
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 462 2012-11-22 00:52:07Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class IndexController {
	public function __construct() {
	}
	public function init() {
		$setting = S ( 'common/link' );
		$SEO = seo ( '', L ( 'link' ), '', '' );
		include template ( 'link', 'index' );
	}

	/**
	 * 友情链接列表页
	 */
	public function list_type() {
		$type_id = trim ( urldecode ( $_GET ['type_id'] ) );
		$type_id = intval ( $type_id );
		if ($type_id == "") $type_id = '0';
		$setting = S ( 'common/link' );
		$SEO = seo ( '', L ( 'link' ), '', '' );
		include template ( 'link', 'list_type' );
	}

	/**
	 * 申请友情链接
	 */
	public function register() {
		if (isset ( $_POST ['dosubmit'] )) {
			if ($_POST ['name'] == "") {
				showmessage ( L ( 'sitename_noempty' ), U ( 'link/index/register' ) );
			}
			if ($_POST ['url'] == "") {
				showmessage ( L ( 'siteurl_not_empty' ), U ( 'link/index/register' ) );
			}
			if (! in_array ( $_POST ['linktype'], array ('0','1' ) )) $_POST ['linktype'] = '0';
			$link_db = Loader::model ( 'link_model' );
			$_POST ['logo'] = new_htmlspecialchars ( $_POST ['logo'] );

			if ($_POST ['linktype'] == '0') {
				$sql = array ('typeid' => $_POST ['typeid'],'linktype' => $_POST ['linktype'],'name' => $_POST ['name'],'url' => $_POST ['url'] );
			} else {
				$sql = array ('typeid' => $_POST ['typeid'],'linktype' => $_POST ['linktype'],'name' => $_POST ['name'],'url' => $_POST ['url'],'logo' => $_POST ['logo'] );
			}
			$link_db->insert ( $sql );
			showmessage ( L ( 'add_success' ), U ( 'link/index' ) );
		} else {
			$setting = S ( 'common/link' );
			if (isset ( $setting ['is_post'] ) && $setting ['is_post'] == '0') {
				showmessage ( L ( 'suspend_application' ), HTTP_REFERER );
			}
			$this->type = Loader::model ( 'type_model' );
			$types = $this->type->get_types (); // 获取站点下所有友情链接分类
			$SEO = seo ( '', L ( 'application_links' ), '', '' );
			include template ( 'link', 'register' );
		}
	}

	/**
	 * 统计友情连接被点击的次数
	 */
	public function count() {
		$linkid = isset ( $_GET ['linkid'] ) ? intval ( $_GET ['linkid'] ) : exit ( '0' );
		echo Loader::model ( 'link_model' )->hits ( $linkid );
	}
}