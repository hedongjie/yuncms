<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: comment_tag.php 273 2013-04-01 09:30:54Z 85825770@qq.com $
 */
class comment_tag {
	// 数据库连接
	private $comment_db, $comment_data_db, $comment_table_db;
	public function __construct() {
		$this->comment_db = Loader::model ( 'comment_model' );
		$this->comment_data_db = Loader::model ( 'comment_data_model' );
		$this->comment_table_db = Loader::model ( 'comment_table_model' );
	}

	/**
	 * YUN标签数据数量计算函数
	 *
	 * @param array $data YUN标签中的配置参数传入
	 */
	public function count($data) {
		if ($data ['action'] == 'get_comment') return 0;
		$commentid = $data ['commentid'];
		if (empty ( $commentid )) return false;
		Loader::helper ( 'comment:global' );
		list ( $module, $contentid ) = decode_commentid ( $commentid );
		$comment = $this->comment_db->getby_commentid ( $commentid );
		if (! $comment) return false;
		return $comment ['total'];
	}

	/**
	 * 获取评论总表信息
	 *
	 * @param array $data YUN标签中的配置参数传入
	 */
	public function get_comment($data) {
		$commentid = $data ['commentid'];
		if (empty ( $commentid )) return false;
		return $this->comment_db->getby_commentid ( $commentid );
	}

	/**
	 * 获取评论数据
	 *
	 * @param array $data YUN标签中的配置参数传入
	 */
	public function lists($data) {
		$commentid = $data ['commentid'];
		if (empty ( $commentid )) return false;
		Loader::func ( 'comment:global' );
		list ( $module, $contentid ) = decode_commentid ( $commentid );
		$comment = $this->comment_db->getby_commentid ( $commentid );
		if (! $comment) return false;
		// 设置存储数据表
		$this->comment_data_db->table_name ( $comment ['tableid'] );
		$hot = 'id';
		if (isset ( $data ['hot'] ) && ! empty ( $data ['hot'] )) $hot = 'support desc, id';
		$sql = array ('commentid' => $commentid,'status' => 1 );
		return $this->comment_data_db->where($sql)->limit($data ['limit'])->order($hot . ' desc ')->select ();
	}

	/**
	 * 评论排行榜
	 *
	 * @param array $data YUN标签中的配置参数传入
	 */
	public function bang($data) {
		$data ['limit'] = intval ( $data ['limit'] );
		if (! isset ( $data ['limit'] ) || empty ( $data ['limit'] )) $data ['limit'] = 10;
		return $this->comment_db->limit($data ['limit'])->order("total desc")->select ();
	}

	/**
	 * YUN标签，可视化显示参数配置。
	 */
	public function yun_tag() {
		return array ('do' => array ('lists' => L ( 'list', '', 'comment' ),'get_comment' => L ( 'comments_on_the_survey', '', 'comment' ),'bang' => L ( 'comment_bang', '', 'comment' ) ),
				'lists' => array ('commentid' => array ('name' => L ( 'comments_id', '', 'comment' ),'htmltype' => 'input','validator' => array ('min' => 1 ) ),
						'hot' => array ('name' => L ( 'sort', '', 'comment' ),'htmltype' => 'select','data' => array ('0' => L ( 'new', '', 'comment' ),'1' => L ( 'hot', '', 'comment' ) ) ) ),
				'get_comment' => array ('commentid' => array ('name' => L ( 'comments_id', '', 'comment' ),'htmltype' => 'input','defaultdata' => '$commentid' ) ) );
	}
}