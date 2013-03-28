<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class vote_option_model extends model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'vote_option';
		parent::__construct ();
	}
	/**
	 * 说明:添加投票选项操作
	 *
	 * @param $data 选项数组
	 * @param $subjectid 投票标题ID
	 */
	public function add_options($data, $subjectid) {
		// 判断传递的数据类型是否正确
		if (! is_array ( $data )) return FALSE;
		if (! $subjectid) return FALSE;
		foreach ( $data as $key => $val ) {
			if (trim ( $val ) == '') continue;
			$newoption = array ('subjectid' => $subjectid,'option' => $val,'image' => '','listorder' => 0 );
			$this->insert ( $newoption );
		}
		return TRUE;
	}

	/**
	 * 说明:更新选项
	 *
	 * @param $data 数组
	 *        	Array ( [44] => 443 [43(optionid)] => 334(option 值) )
	 * @param
	 *        	$subjectid
	 */
	public function update_options($data) {
		// 判断传递的数据类型是否正确
		if (! is_array ( $data )) return FALSE;
		foreach ( $data as $key => $val ) {
			if (trim ( $val ) == '') continue;
			$newoption = array ('option' => $val );
			$this->where(array ('optionid' => $key ))->update ( $newoption );
		}
		return TRUE;
	}

	/**
	 * 说明:选项排序
	 *
	 * @param $data 选项数组
	 */
	public function set_listorder($data) {
		if (! is_array ( $data )) return FALSE;
		foreach ( $data as $key => $val ) {
			$val = intval ( $val );
			$key = intval ( $key );
			$this->where(array ($keyid => $key ))->update ( array('listorder'=>$val) );
		}
		return true;
	}

	/**
	 * 说明:删除指定 投票ID对应的选项
	 *
	 * @param
	 *        	$data
	 * @param
	 *        	$subjectid
	 */
	public function del_options($subjectid) {
		if (! $subjectid) return FALSE;
		$this->where(array ('subjectid' => $subjectid ))->delete (  );
		return TRUE;
	}

	/**
	 * 说明: 查询 该投票的 选项
	 *
	 * @param $subjectid 投票ID
	 */
	public function get_options($subjectid) {
		if (! $subjectid) return FALSE;
		return $this->where(array ('subjectid' => $subjectid ))->order('optionid ASC')->select ();
	}
	/**
	 * 说明:删除单条对应ID的选项记录
	 *
	 * @param $optionid 投票选项ID
	 */
	public function del_option($optionid) {
		if (! $optionid) return FALSE;
		return $this->where(array ('optionid' => $optionid ))->delete (  );
	}
}
?>