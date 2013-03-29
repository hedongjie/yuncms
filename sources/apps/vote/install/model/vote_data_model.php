<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class vote_data_model extends model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'vote_data';
		parent::__construct ();
	}

	/**
	 * 说明: 查询 该投票的 投票信息
	 *
	 * @param $subjectid 投票ID
	 */
	public function get_vote_data($subjectid) {
		if (! $subjectid) return FALSE;
		return $this->where(array ('subjectid' => $subjectid ))->order('optionid ASC')->select ();
	}
}
?>