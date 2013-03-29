<?php
/**
 * 友情连接TAG
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: link_tag.php 432 2012-11-18 11:19:36Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class link_tag {
	private $link_db, $type_db;
	public function __construct() {
		$this->link_db = Loader::model ( 'link_model' );
		$this->type_db = Loader::model ( 'type_model' );
	}

	/**
	 * 取出该分类的详细 信息
	 *
	 * @param $typeid 分类ID
	 */
	public function get_type($data) {
		$typeid = intval ( $data ['typeid'] );
		if ($typeid == '0') {
			$arr = array ();
			$arr ['name'] = '默认分类';
			return $arr;
		} else {
			$r = $this->type_db->getby_typeid ( $typeid );
			return new_htmlspecialchars ( $r );
		}
	}

	/**
	 * 友情链接
	 *
	 * @param
	 *        	$data
	 */
	public function lists($data) {
		$typeid = intval ( $data ['typeid'] ); // 分类ID
		$linktype = isset ( $data ['linktype'] ) ? $data ['linktype'] : 0;
		if ($typeid != '' || $typeid == '0') {
			$sql = array ('typeid' => $typeid,'linktype' => $linktype,'passed' => '1' );
		} else {
			$sql = array ('linktype' => $linktype,'passed' => '1' );
		}
		$r = $this->link_db->where($sql)->order('listorder ' . $data ['order'])->limit($data ['limit'])->select (  );
		return new_htmlspecialchars ( $r );
	}

	/**
	 * 返回该分类下的友情链接 .
	 * ..
	 *
	 * @param $data 传入数组参数
	 */
	public function type_list($data) {
		$linktype = isset ( $data ['linktype'] ) ? $data ['linktype'] : 0;
		if (isset ( $data ['typeid'] )) {
			$typeid = isset ( $data ['typeid'] ) ? intval ( $data ['typeid'] ) : 0;
			$sql = array ('typeid' => $typeid,'linktype' => $linktype,'passed' => '1' );
		} else {
			$sql = array ('linktype' => $linktype,'passed' => '1' );
		}
		$r = $this->link_db->where($sql)->order($data ['order'])->limit($data ['limit'])->select ( );
		return new_htmlspecialchars ( $r );
	}

	/**
	 * 首页 友情链接分类 循环 .
	 *
	 * @param
	 *        	$data
	 */
	public function type_lists($data) {
		if (! in_array ( $data ['listorder'], array ('desc','asc' ) )) {
			$data ['listorder'] = 'desc';
		}
		$sql = array ('application' => APP );
		$r = $this->type_db->where($sql)->order('listorder ' . $data ['listorder'])->limit($data ['limit'])->select (  );
		return new_htmlspecialchars ( $r );
	}

	/**
	 * 读取站点下的友情链接分类 ...
	 */
	public function get_typelist($value = '', $id = '') {
		$arr = $this->type_db->where(array ('application' => 'link' ))->key('typeid')->select (  );
		$html = $id ? ' id="typeid" onchange="$(\'#' . $id . '\').val(this.value);"' : 'name="typeid", id="typeid"';
		return Form::select ( $arr, $value, $html, L ( 'please_select' ) );
	}
	public function count() {
	}

	/**
	 * yun 标签调用
	 */
	public function yun_tag() {
		return array ('do' => array ('type_list' => L ( 'link_list', '', 'link' ) ),
				'type_list' => array ('linktype' => array ('name' => L ( 'link_type', '', 'link' ),'htmltype' => 'select','data' => array ('0' => L ( 'word_link', '', 'link' ),'1' => L ( 'logo_link', '', 'link' ) ) ),
						'order' => array ('name' => L ( 'sort' ),'htmltype' => 'select','data' => array ('listorder DESC' => L ( 'listorder_desc', '', 'content' ),'listorder ASC' => L ( 'listorder_asc', '', 'content' ) ) ) ) );
	}
}