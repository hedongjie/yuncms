<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
if (! application_exists ( 'comment' )) showmessage ( L ( 'application_not_exists' ) );
class comment_api {
	private $db;
	function __construct() {
		$this->db = Loader::model ( 'content_model' );
	}

	/**
	 * 获取评论信息
	 *
	 * @param $module 模型
	 * @param $contentid 文章ID
	 * @param $siteid 站点ID
	 */
	function get_info($module, $contentid) {
		list ( $module, $catid ) = explode ( '_', $module );
		if (empty ( $contentid ) || empty ( $catid )) {return false;}
		$this->db->set_catid ( $catid );
		$r = $this->db->get_one ( array ('catid' => $catid,'id' => $contentid ), '`title`' );
		$category = S ( 'common/category_content' );
		$model = S ( 'common/model' );
		$cat = $category [$catid];
		$data_info = array ();
		if ($cat ['type'] == 0) {
			if ($model [$cat ['modelid']] ['tablename']) {
				$this->db->table_name = $this->db->get_prefix () . $model [$cat ['modelid']] ['tablename'] . '_data';
				$data_info = $this->db->get_one ( array ('id' => $contentid ) );
			}
		}
		if ($r) {
			return array ('title' => $r ['title'],'url' => go ( $catid, $contentid, 1 ),'allow_comment' => (isset ( $data_info ['allow_comment'] ) ? $data_info ['allow_comment'] : 1) );
		} else {
			return false;
		}
	}
}