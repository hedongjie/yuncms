<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: content_tag.php 252 2012-11-07 14:52:09Z xutongle $
 */
class content_tag {
	private $db;
	public function __construct() {
		$this->db = Loader::model ( 'content_model' );
		$this->position = Loader::model ( 'position_data_model' );
	}

	/**
	 * 初始化模型
	 *
	 * @param
	 *        	$catid
	 */
	public function set_modelid($catid) {
		$this->category = S ( 'common/category_content' );
		if ($this->category [$catid] ['type'] != 0) return false;
		$this->modelid = $this->category [$catid] ['modelid'];
		$this->db->set_model ( $this->modelid );
		$this->tablename = $this->db->table_name;
		if (empty ( $this->category )) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 分页统计
	 *
	 * @param
	 *        	$data
	 */
	public function count($data) {
		if ($data ['do'] == 'lists') {
			$catid = intval ( $data ['catid'] );
			if (! $this->set_modelid ( $catid )) return false;
			if (isset ( $data ['where'] )) {
				$sql = $data ['where'];
			} else {
				if ($this->category [$catid] ['child']) {
					$catids_str = $this->category [$catid] ['arrchildid'];
					$pos = strpos ( $catids_str, ',' ) + 1;
					$catids_str = substr ( $catids_str, $pos );
					$where = array ('status' => 99,'catid' => array ('in',$catids_str ) );
				} else {
					$where = array ('status' => 99,'catid' => $catid );
				}
			}
			return $this->db->where ( $where )->count ( $sql );
		}
	}

	/**
	 * 列表页标签
	 *
	 * @param
	 *        	$data
	 */
	public function lists($data) {
		$catid = intval ( $data ['catid'] );
		if (! $this->set_modelid ( $catid )) return false;
		if (isset ( $data ['where'] )) {
			$where = $data ['where'];
		} else {
			$where = array ('status' => 99 );
			if (isset ( $data ['thumb'] )) {
				$where ['thumb'] = array ('neq','' );
			}
			if ($this->category [$catid] ['child']) {
				$catids_str = $this->category [$catid] ['arrchildid'];
				$pos = strpos ( $catids_str, ',' ) + 1;
				$catids_str = substr ( $catids_str, $pos );
				$where ['catid'] = array ('in',$catids_str );
			} else {
				$where ['catid'] = $catid;
			}
		}
		$order = $data ['order'];
		$return = $this->db->where ( $where )->limit ( $data ['limit'] )->order ( $order )->key ( 'id' )->select ();
		// 调用副表的数据
		if (isset ( $data ['moreinfo'] ) && intval ( $data ['moreinfo'] ) == 1) {
			$ids = array ();
			foreach ( $return as $v ) {
				if (isset ( $v ['id'] ) && ! empty ( $v ['id'] )) {
					$ids [] = $v ['id'];
				} else {
					continue;
				}
			}
			if (! empty ( $ids )) {
				$this->db->table_name = $this->db->table_name . '_data';
				$ids = implode ( '\',\'', $ids );
				$r = $this->db->where ( array ('id' => array ('in',$ids ) ) )->key ( 'id' )->select ();
				if (! empty ( $r )) {
					foreach ( $r as $k => $v ) {
						if (isset ( $return [$k] )) $return [$k] = array_merge ( $v, $return [$k] );
					}
				}
			}
		}
		return $return;
	}

	/**
	 * 相关文章标签
	 *
	 * @param
	 *        	$data
	 */
	public function relation($data) {
		$catid = intval ( $data ['catid'] );
		if (! $this->set_modelid ( $catid )) return false;
		$order = isset ( $data ['order'] ) ? trim ( $data ['order'] ) : '';
		$where = array ('status' => 99 );
		$limit = $data ['id'] ? $data ['limit'] + 1 : $data ['limit'];
		if ($data ['relation']) {
			$relations = explode ( '|', $data ['relation'] );
			$relations = array_diff ( $relations, array (null ) );
			$relations = implode ( ',', $relations );
			$key_array = $this->db->where ( array ('id' => array ('in',$relations ) ) )->limit ( $limit )->order ( $order )->key ( 'id' )->select ();
		} elseif ($data ['keywords']) {
			$keywords = str_replace ( '%', '', $data ['keywords'] );
			$keywords_arr = explode ( ' ', $keywords );
			$key_array = array ();
			$number = 0;
			$i = 1;
			foreach ( $keywords_arr as $_k ) {
				$where ['keywords'] = array ('like',"%$_k%" );
				if (isset ( $data ['id'] ) && intval ( $data ['id'] )) {
					$where ['id'] = array ('neq',abs ( intval ( $data ['id'] ) ) );
				}
				$r = $this->db->where ( $where )->limit ( $limit )->key ( 'id' )->select ();
				$number += count ( $r );
				foreach ( $r as $id => $v ) {
					if ($i <= $data ['limit'] && ! in_array ( $id, $key_array )) $key_array [$id] = $v;
					$i ++;
				}
				if ($data ['limit'] < $number) break;
			}
		}
		if (isset ( $data ['id'] ) && isset ( $key_array [$data ['id']] )) unset ( $key_array [$data ['id']] );
		return isset ( $key_array ) && is_array ( $key_array ) ? $key_array : null;
	}

	/**
	 * 排行榜标签
	 *
	 * @param
	 *        	$data
	 */
	public function hits($data) {
		$catid = intval ( $data ['catid'] );
		if (! $this->set_modelid ( $catid )) return false;
		$this->hits_db = Loader::model ( 'hits_model' );
		$desc = $ids = '';
		$array = $ids_array = array ();
		$order = isset ( $data ['order'] ) ? $data ['order'] : '';
		$hitsid = 'c-' . $this->modelid . '-%';
		$where = array ('hitsid' => array ('like',$hitsid ) );
		if (isset ( $data ['day'] )) {
			$updatetime = TIME - intval ( $data ['day'] ) * 86400;
			$where ['updatetime'] = array ('gt',$updatetime );
		}
		if ($this->category [$catid] ['child']) {
			$catids_str = $this->category [$catid] ['arrchildid'];
			$pos = strpos ( $catids_str, ',' ) + 1;
			$catids_str = substr ( $catids_str, $pos );
			$where ['catid'] = array ('in',$catids_str );
		} else {
			$where ['catid'] = $catid;
		}
		$hits = array ();
		$result = $this->hits_db->where ( $where )->limit ( $data ['limit'] )->order ( $order )->select ();
		foreach ( $result as $r ) {
			$pos = strpos ( $r ['hitsid'], '-', 2 ) + 1;
			$ids_array [] = $id = substr ( $r ['hitsid'], $pos );
			$hits [$id] = $r;
		}
		$ids = implode ( ',', $ids_array );
		if ($ids) {
			$where = array ('status' => 99,'id' => array ('in',$ids ) );
		} else {
			$where = array ();
		}
		$this->db->table_name = $this->tablename;
		$result = $this->db->where ( $where )->limit ( $data ['limit'] )->key ( 'id' )->select ();
		foreach ( $ids_array as $id ) {
			if ($result [$id] ['title'] != '') {
				$array [$id] = $result [$id];
				$array [$id] = array_merge ( $array [$id], $hits [$id] );
			}
		}
		return $array;
	}

	/**
	 * 栏目标签
	 *
	 * @param
	 *        	$data
	 */
	public function category($data) {
		$data ['catid'] = intval ( $data ['catid'] );
		$array = array ();
		$categorys = S ( 'common/category_content' );
		$i = 1;
		foreach ( $categorys as $catid => $cat ) {
			if ($i > $data ['limit']) break;
			if (! $cat ['ismenu']) continue;
			if (strpos ( $cat ['url'], '://' ) === false) {
				$cat ['url'] = substr ( SITE_URL, 0, - 1 ) . $cat ['url'];
			}
			if ($cat ['parentid'] == $data ['catid']) {
				$array [$catid] = $cat;
				$i ++;
			}
		}
		return $array;
	}

	/**
	 * 推荐位
	 *
	 * @param
	 *        	$data
	 */
	public function position($data) {
		$where = array ();
		$array = array ();
		$posid = intval ( $data ['posid'] );
		$order = $data ['order'];
		$thumb = (empty ( $data ['thumb'] ) || intval ( $data ['thumb'] ) == 0) ? 0 : 1;
		$catid = (empty ( $data ['catid'] ) || $data ['catid'] == 0) ? '' : intval ( $data ['catid'] );
		if ($catid) {
			$this->category = S ( 'common/category_content' );
		}
		if ($catid && $this->category [$catid] ['child']) {
			$catids_str = $this->category [$catid] ['arrchildid'];
			$pos = strpos ( $catids_str, ',' ) + 1;
			$catids_str = substr ( $catids_str, $pos );
			$where ['catid'] = array ('in',$catids_str );
		} elseif ($catid && ! $this->category [$catid] ['child']) {
			$where ['catid'] = $catid;
		}
		if ($thumb) $where ['thumb'] = 1;
		if (isset ( $data ['where'] )) {
			$data_where = $this->position->where($data ['where'])->get_where();
			$where = array_merge($data_where,$where);
		}
		if (isset ( $data ['expiration'] ) && $data ['expiration'] == 1) {
			$where ['expiration'] =array(array('egt',TIME),array('eq','0'), 'or') ;
		}
		$where ['posid'] = $posid;
		$pos_arr = $this->position->where($where)->order($order)->limit($data ['limit'])->select ();
		if (! empty ( $pos_arr )) {
			foreach ( $pos_arr as $info ) {
				$key = $info ['catid'] . '-' . $info ['id'];
				$array [$key] = string2array ( $info ['data'] );
				$array [$key] ['url'] = go ( $info ['catid'], $info ['id'] );
				$array [$key] ['id'] = $info ['id'];
				$array [$key] ['catid'] = $info ['catid'];
				$array [$key] ['listorder'] = $info ['listorder'];
			}
		}
		return $array;
	}

	/**
	 * 可视化标签
	 */
	public function yun_tag() {
		$positionlist = S ( 'common/position' );
		$poslist = array ();
		if (is_array ( $positionlist )) {
			foreach ( $positionlist as $_v )
				$poslist [$_v ['posid']] = $_v ['name'];
		}
		return array ('do' => array ('lists' => L ( 'list', '', 'content' ),'position' => L ( 'position', '', 'content' ),'category' => L ( 'subcat', '', 'content' ),'relation' => L ( 'related_articles', '', 'content' ),'hits' => L ( 'top', '', 'content' ) ),
				'lists' => array ('catid' => array ('name' => L ( 'catid', '', 'content' ),'htmltype' => 'input_select_category','data' => array ('type' => 0 ),'validator' => array ('min' => 1 ) ),
						'order' => array ('name' => L ( 'sort', '', 'content' ),'htmltype' => 'select','data' => array ('id DESC' => L ( 'id_desc', '', 'content' ),'updatetime DESC' => L ( 'updatetime_desc', '', 'content' ),'listorder ASC' => L ( 'listorder_asc', '', 'content' ) ) ),
						'thumb' => array ('name' => L ( 'thumb', '', 'content' ),'htmltype' => 'radio','data' => array ('0' => L ( 'all_list', '', 'content' ),'1' => L ( 'thumb_list', '', 'content' ) ) ),
						'moreinfo' => array ('name' => L ( 'moreinfo', '', 'content' ),'htmltype' => 'radio','data' => array ('1' => L ( 'yes' ),'0' => L ( 'no' ) ) ) ),
				'position' => array ('posid' => array ('name' => L ( 'posid', '', 'content' ),'htmltype' => 'input_select','data' => $poslist,'validator' => array ('min' => 1 ) ),
						'catid' => array ('name' => L ( 'catid', '', 'content' ),'htmltype' => 'input_select_category','data' => array ('type' => 0 ),'validator' => array ('min' => 0 ) ),
						'thumb' => array ('name' => L ( 'thumb', '', 'content' ),'htmltype' => 'radio','data' => array ('0' => L ( 'all_list', '', 'content' ),'1' => L ( 'thumb_list', '', 'content' ) ) ),
						'order' => array ('name' => L ( 'sort', '', 'content' ),'htmltype' => 'select','data' => array ('listorder DESC' => L ( 'listorder_desc', '', 'content' ),'listorder ASC' => L ( 'listorder_asc', '', 'content' ),'id DESC' => L ( 'id_desc', '', 'content' ) ) ) ),
				'category' => array ('catid' => array ('name' => L ( 'catid', '', 'content' ),'htmltype' => 'input_select_category','data' => array ('type' => 0 ) ) ),
				'relation' => array ('catid' => array ('name' => L ( 'catid', '', 'content' ),'htmltype' => 'input_select_category','data' => array ('type' => 0 ),'validator' => array ('min' => 1 ) ),
						'order' => array ('name' => L ( 'sort', '', 'content' ),'htmltype' => 'select','data' => array ('id DESC' => L ( 'id_desc', '', 'content' ),'updatetime DESC' => L ( 'updatetime_desc', '', 'content' ),'listorder ASC' => L ( 'listorder_asc', '', 'content' ) ) ),
						'relation' => array ('name' => L ( 'relevant_articles_id', '', 'content' ),'htmltype' => 'input' ),'keywords' => array ('name' => L ( 'key_word', '', 'content' ),'htmltype' => 'input' ) ),
				'hits' => array ('catid' => array ('name' => L ( 'catid', '', 'content' ),'htmltype' => 'input_select_category','data' => array ('type' => 0 ),'validator' => array ('min' => 1 ) ),
						'day' => array ('name' => L ( 'day_select', '', 'content' ),'htmltype' => 'input','data' => array ('type' => 0 ) ) ) );
	}
}