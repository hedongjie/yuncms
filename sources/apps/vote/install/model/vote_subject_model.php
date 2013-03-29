<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class vote_subject_model extends Model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'vote_subject';
		parent::__construct ();
	}

	/**
	 * 说明: 取得投票信息, 返回数组
	 *
	 * @param $subjectid 投票ID
	 */
	public function get_subject($subjectid) {
		if (! $subjectid) return FALSE;
		return $this->getby_subjectid ( $subjectid );
	}
}
?>