<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class license_tag {
	private $link_db, $type_db;
	public function __construct() {
		$this->license_db = Loader::model ( 'license_model' );
		$this->type_db = Loader::model ( 'type_model' );
	}
	/**
	 * 取出该分类的详细 信息
	 *
	 * @param $typeid 分类ID
	 */
	public function get_type($data) {
		$typeid = intval ( $data ['typeid'] );
		$r = $this->type_db->getby_typeid ( $typeid );
		return new_htmlspecialchars ( $r );
	}
	/**
	 * 授权
	 *
	 * @param $data
	 */
	public function lists($data) {
		$typeid = intval ( $data ['typeid'] ); // 分类ID
		if ($typeid != '' || $typeid == '0') $sql = array ('typeid' => $typeid );
		$r = $this->license_db->where($sql)->order('listorder ' . $data ['order'])->limit($data ['limit'])->select ();
		return new_htmlspecialchars ( $r );
	}
	/**
	 * 返回该分类下的授权 .
	 * ..
	 *
	 * @param $data 传入数组参数
	 */
	public function type_list($data) {
		$typeid = $data ['typeid'];
		if ($typeid) {
			if (is_int ( $typeid )) return false;
			$sql = array ('typeid' => $typeid );
		}
		$r = $this->license_db->where($sql)->order($data ['order'])->limit($data ['limit'])->select ();
		return new_htmlspecialchars ( $r );
	}

	/**
	 * 首页 授权分类 循环 .
	 *
	 * @param $data
	 */
	public function type_lists($data) {
		if (! in_array ( $data ['listorder'], array ('desc','asc' ) )) $data ['listorder'] = 'desc';
		$sql = array ('application' => 'license' );
		$r = $this->type_db->where($sql)->order('listorder ' . $data ['listorder'])->limit($data ['limit'])->select ();
		return new_htmlspecialchars ( $r );
	}
	/**
	 * 读取站点下的授权分类 ...
	 */
	public function get_typelist($value = '', $id = '') {
		$data = $arr = array ();
		$data = $this->type_db->where(array ('application' => 'license' ))->select (  );
		foreach ( $data as $r )
			$arr [$r ['typeid']] = $r ['name'];
		$html = $id ? ' id="typeid" onchange="$(\'#' . $id . '\').val(this.value);"' : 'name="typeid", id="typeid"';
		return form::select ( $arr, $value, $html, L ( 'please_select' ) );
	}
	public function count() {
	}

	/**
	 * YUN标签调用
	 */
	public function yun_tag() {
		return array ('do' => array ('type_list' => L ( 'license_list', '', 'license' ) ),
					'type_list' => array ('order' => array ('name' => L ( 'sort', '', 'comment' ),'htmltype' => 'select','data' => array ('listorder DESC' => L ( 'listorder_desc', '', 'content' ),'listorder ASC' => L ( 'listorder_asc', '', 'content' ) ) ) ) );
	}
}