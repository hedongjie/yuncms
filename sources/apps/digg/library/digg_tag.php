<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class digg_tag {
	// 数据库连接
	private $digg_db;

	public function __construct() {
		$this->digg_db = Loader::model ( 'digg_model' );
		$this->content_db = Loader::model ( 'content_model' );
	}

	/**
	 *
	 *
	 * 关注排行榜
	 *
	 * @param array $data
	 *        	XT标签中的配置参数传入
	 */
	public function bang($data) {
		$data ['limit'] = intval ( $data ['limit'] );
		if (! isset ( $data ['limit'] ) || empty ( $data ['limit'] )) $data ['limit'] = 10;
		return $this->digg_db->order($data ['order'])->limit($data ['limit'])->select ();
	}

	/**
	 * yun标签，可视化显示参数配置。
	 */
	public function yun_tag() {
		return array ('do' => array ('bang' => L ( 'comment_bang', '', 'comment' ) ),'bang' => array ('hot' => array ('name' => L ( 'sort', '', 'comment' ),'htmltype' => 'select','data' => array ('0' => L ( 'new', '', 'comment' ),'1' => L ( 'hot', '', 'comment' ) ) ) ) );
	}
}