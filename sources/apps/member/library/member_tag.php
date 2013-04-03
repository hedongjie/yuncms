<?php
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: member_tag.php 205 2013-03-29 23:26:40Z 85825770@qq.com $
 */
class member_tag {
	private $db, $favorite_db;

	public function __construct() {
		$this->db = Loader::model ( 'member_model' );
		$this->favorite_db = Loader::model ( 'favorite_model' );
	}

	/**
	 * 获取收藏列表
	 *
	 * @param array $data
	 *        	数据信息{userid:用户id;limit:读取数;order:排序字段}
	 * @return array 收藏列表数组
	 */
	public function favoritelist($data) {
		$userid = intval ( $data ['userid'] );
		$limit = $data ['limit'];
		$order = $data ['order'];
		$favoritelist = $this->favorite_db->where(array ('userid' => $userid ))->order($order)->limit($limit)->select ();
		return $favoritelist;
	}

	/**
	 * 读取收藏文章数
	 *
	 * @param array $data
	 *        	数据信息{userid:用户id;limit:读取数;order:排序字段}
	 * @return int 收藏数
	 */
	public function count($data) {
		$userid = intval ( $data ['userid'] );
		return $this->favorite_db->where(array ('userid' => $userid ))->count (  );
	}

	public function yun_tag() {
		return array ('do' => array ('favoritelist' => L ( 'favorite_list', '', 'member' ) ),'favoritelist' => array ('userid' => array ('name' => L ( 'uid' ),'htmltype' => 'input' ) ) );
	}
}