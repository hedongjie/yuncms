<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: search_model.php 327 2012-11-12 02:33:10Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class search_model extends Model {
	public $table_name = '';
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'search';
		parent::__construct ();
	}

	/**
	 * 添加到全站搜索、修改已有内容
	 *
	 * @param $typeid
	 * @param $id
	 * @param $data
	 * @param $text 不分词的文本
	 * @param $adddate 添加时间
	 * @param $iscreateindex 是否是后台更新全文索引
	 */
	public function update_search($typeid, $id = 0, $data = '', $text = '', $adddate = 0, $iscreateindex = 0) {
		$segment = Loader::lib ( 'Segment' );
		// 分词结果
		$fulltext_data = $segment->get_keyword ( $segment->split_result ( $data ) );
		$fulltext_data = $text . ' ' . $fulltext_data;
		if (! $iscreateindex) {
			$r = $this->where ( array ('typeid' => $typeid,'id' => $id ) )->field('searchid')->find();
		}

		if ($r) {
			$searchid = $r ['searchid'];
			$this->where(array ('typeid' => $typeid,'id' => $id ))->update ( array ('data' => $fulltext_data,'adddate' => $adddate ) );
		} else {
			$searchid = $this->insert ( array ('typeid' => $typeid,'id' => $id,'adddate' => $adddate,'data' => $fulltext_data ), true );
		}
		return $searchid;
	}

	/**
	 * 删除全站搜索内容
	 */
	public function delete_search($typeid, $id) {
		$this->where ( array ('typeid' => $typeid,'id' => $id ) )->delete();
	}
}