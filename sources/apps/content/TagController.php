<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
// 模型缓存路径
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
Loader::helper ( 'content:util' );
/**
 * TAG关键词
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-12
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: TagController.php 178 2013-03-27 22:55:06Z 85825770@qq.com $
 */
class TagController {

	private $db;

	public function __construct() {
		$this->db = Loader::model ( 'content_model' );
	}

	/**
	 * 按照模型搜索
	 */
	public function init() {
		if (! isset ( $_GET ['catid'] )) showmessage ( L ( 'missing_part_parameters' ) );
		$catid = intval ( $_GET ['catid'] );
		$this->categorys = S ( 'common/category_content' );
		if (! isset ( $this->categorys [$catid] )) showmessage ( L ( 'missing_part_parameters' ) );
		if (isset ( $_GET ['info'] ['catid'] ) && $_GET ['info'] ['catid']) {
			$catid = intval ( $_GET ['info'] ['catid'] );
		} else {
			$_GET ['info'] ['catid'] = 0;
		}
		if (isset ( $_GET ['tag'] ) && trim ( $_GET ['tag'] ) != '') {
			$tag = safe_replace ( strip_tags ( $_GET ['tag'] ) );
		} else {
			showmessage ( L ( 'illegal_operation' ) );
		}
		$modelid = $this->categorys [$catid] ['modelid'];
		$modelid = intval ( $modelid );
		if (! $modelid) showmessage ( L ( 'illegal_parameters' ) );
		$CATEGORYS = $this->categorys;

		$this->db->set_model ( $modelid );
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$datas = $infos = array ();
		$infos = $this->db->where(array('keywords',array('like',"%$tag%")))->order('id DESC')->listinfo ($page, 20 );
		$total = $this->db->number;
		if ($total > 0) {
			$pages = $this->db->pages;
			foreach ( $infos as $_v ) {
				if (strpos ( $_v ['url'], '://' ) === false) $_v ['url'] = SITE_URL . $_v ['url'];
				$datas [] = $_v;
			}
		}
		$SEO = seo ( $catid, $tag );
		include template ( 'content', 'tag' );
	}

}