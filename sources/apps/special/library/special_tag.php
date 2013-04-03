<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: special_tag.php 910 2012-06-21 03:47:05Z 85825770@qq.com $
 */
class special_tag {
	private $db, $c;
	public function __construct() {
		$this->db = Loader::model ( 'special_model' );
		$this->c = Loader::model ( 'special_content_model' );
	}

	/**
	 * lists调用方法
	 *
	 * @param array $data
	 *        	标签配置传递过来的配置数组，根据配置生成sql
	 */
	public function lists($data) {
		$where = "1";
		if (isset ( $data ['elite'] ) && ! empty ( $data ['elite'] )) $where .= " AND `elite`='1'";
		if (isset ( $data ['thumb'] ) && ! empty ( $data ['thumb'] )) $where .= " AND `thumb`!=''";
		$listorder = array ('id ASC','id DESC','listorder ASC, id DESC','listorder DESC, id DESC' );
		return $this->db->where ( $where )->limit ( $data ['limit'] )->order ( $listorder [$data ['listorder']] )->select ();
	}

	/**
	 * 标签中计算分页的方法
	 *
	 * @param array $data 标签配置数组，根据数组计算出分页
	 */
	public function count($data) {
		$where = array ();
		if ($data ['action'] == 'lists') {
			if ($data ['elite']) $where ['elite'] = 1;
			if ($data ['thumb']) $where ['thumb'] = array ('neq','' );
			$res = $this->db->where ( $where )->count ();
		} elseif ($data ['action'] == 'content_list') {
			if ($data ['specialid']) $where ['specialid'] = $data ['specialid'];
			if ($data ['typeid']) $where ['typeid'] = $data ['typeid'];
			if ($data ['thumb']) $where ['thumb'] = array ('neq','' );
			$res = $this->db->where ( $where )->count ();
		} elseif ($data ['action'] == 'hits') {
			$hitsid = 'special-c';
			if ($data ['specialid'])
				$hitsid .= $data ['specialid'] . '-';
			else
				$hitsid .= '%-';
			$hitsid = $hitsid .= '%';
			$hits_db = Loader::model ( 'hits_model' );
			$res = $hits_db->where ( array ('hitsid' => array ('like',$hitsid ) ) )->count ();
		}
		return $res;
	}

	/**
	 * 点击排行调用方法
	 *
	 * @param array $data 标签配置数组
	 */
	public function hits($data) {
		$hitsid = 'special-c-';
		if ($data ['specialid'])
			$hitsid .= $data ['specialid'] . '-';
		else
			$hitsid .= '%-';
		$hitsid = $hitsid .= '%';
		$this->hits_db = Loader::model ( 'hits_model' );
		$listorders = array ('views DESC','yesterdayviews DESC','dayviews DESC','weekviews DESC','monthviews DESC' );
		$result = $this->hits_db->where(array ('hitsid' => array ('like',$hitsid ) ))->limit($data ['limit'])->order($listorders [$data ['listorder']])->select ();
		foreach ( $result as $key => $r ) {
			$ids = explode ( '-', $r ['hitsid'] );
			$id = $ids [3];
			$re = $this->c->getby_id ( $id );
			$result [$key] ['title'] = $re ['title'];
			$result [$key] ['url'] = $re ['url'];
		}
		return $result;
	}

	/**
	 * 内容列表调用方法
	 *
	 * @param array $data
	 *        	标签配置数组
	 */
	public function content_list($data) {
		$where = array ();
		if ($data ['specialid']) $where ['specialid'] = $data ['specialid'];
		if ($data ['typeid']) $where ['typeid'] = $data ['typeid'];
		if ($data ['thumb']) $where ['thumb'] = array ('neq','' );
		$listorder = array ('id ASC','id DESC','listorder ASC','listorder DESC' );
		$result = $this->c->where ( $where )->limit ( $data ['limit'] )->order ( $listorder [$data ['listorder']] )->select ();
		if (is_array ( $result )) {
			foreach ( $result as $k => $r ) {
				if ($r ['curl']) {
					$content_arr = explode ( '|', $r ['curl'] );
					$r ['url'] = go ( $content_arr ['1'], $content_arr ['0'] );
				}
				$res [$k] = $r;
			}
		} else {
			$res = array ();
		}
		return $res;
	}

	/**
	 * 获取专题分类方法
	 *
	 * @param intval $specialid 专题ID
	 * @param string $value 默认选中值
	 * @param intval $id onchange影响HTML的ID
	 *
	 */
	public function get_type($specialid = 0, $value = '', $id = '') {
		$type_db = Loader::model ( 'type_model' );
		$data = $arr = array ();
		$data = $type_db->where ( array ('application' => 'special','parentid' => $specialid ) )->select ();
		foreach ( $data as $r ) {
			$arr [$r ['typeid']] = $r ['name'];
		}
		$html = $id ? ' id="typeid" onchange="$(\'#' . $id . '\').val(this.value);"' : 'name="typeid", id="typeid"';
		return Form::select ( $arr, $value, $html, L ( 'please_select' ) );
	}

	/**
	 * 标签生成方法
	 */
	public function yun_tag() {
		$result = S ( 'common/special' );
		if (isset ( $result ) && is_array ( $result )) {
			$specials = array (L ( 'please_select' ) );
			foreach ( $result as $r ) {
				$specials [$r ['id']] = $r ['title'];
			}
		}
		return array ('do' => array ('lists' => L ( 'special_list', '', 'special' ),'content_list' => L ( 'content_list', '', 'special' ),'hits' => L ( 'hits_order', '', 'special' ) ),
				'lists' => array ('elite' => array ('name' => L ( 'iselite', '', 'special' ),'htmltype' => 'radio','defaultvalue' => '0','data' => array (L ( 'no' ),L ( 'yes' ) ) ),
						'thumb' => array ('name' => L ( 'get_thumb', '', 'special' ),'htmltype' => 'radio','defaultvalue' => '0','data' => array (L ( 'no' ),L ( 'yes' ) ) ),
						'listorder' => array ('name' => L ( 'order_type', '', 'special' ),'htmltype' => 'select','defaultvalue' => '3','data' => array (L ( 'id_asc', '', 'special' ),L ( 'id_desc', '', 'special' ),L ( 'order_asc', '', 'special' ),L ( 'order_desc', '', 'special' ) ) ) ),
				'content_list' => array ('specialid' => array ('name' => L ( 'special_id', '', 'special' ),'htmltype' => 'input_select','data' => $specials,'ajax' => array ('name' => L ( 'for_type', '', 'special' ),'action' => 'get_type','id' => 'typeid' ) ),
						'thumb' => array ('name' => L ( 'content_thumb', '', 'special' ),'htmltype' => 'radio','defaultvalue' => '0','data' => array (L ( 'no' ),L ( 'yes' ) ) ),
						'listorder' => array ('name' => L ( 'order_type', '', 'special' ),'htmltype' => 'select','defaultvalue' => '3','data' => array (L ( 'id_asc', '', 'special' ),L ( 'id_desc', '', 'special' ),L ( 'order_asc', '', 'special' ),L ( 'order_desc', '', 'special' ) ) ) ),
				'hits' => array ('specialid' => array ('name' => L ( 'special_id', '', 'special' ),'htmltype' => 'input_select','data' => $specials ),
						'listorder' => array ('name' => L ( 'order_type', '', 'special' ),'htmltype' => 'select','data' => array (L ( 'total', '', 'special' ),L ( 'yesterday', '', 'special' ),L ( 'today', '', 'special' ),L ( 'week', '', 'special' ),L ( 'month', '', 'special' ) ) ) ) );
	}
}