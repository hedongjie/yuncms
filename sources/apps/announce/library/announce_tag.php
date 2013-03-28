<?php
/**
 * 公告TAG
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: announce_tag.php 304 2012-11-11 01:22:54Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class announce_tag {
	private $db;
	public function __construct() {
		$this->db = Loader::model ( 'announce_model' );
	}

	/**
	 * 公告列表方法
	 *
	 * @param array $data
	 *        	传递过来的参数
	 * @param
	 *        	return array 数据库中取出的数据数组
	 */
	public function lists($data) {
		$where = array ('endtime' => array (array ('gt',date ( 'Y-m-d' ) ),array ('eq','0000-00-00' ),'or' ) );
		$data = $this->db->where ( $where )->order ( 'aid DESC' )->limit ( $data ['limit'] )->select ();
		$return = array ();
		if ($data) {
			foreach ( $data as $value ) {
				$return [$value ['aid']] = $value;
				$return [$value ['aid']] ['url'] = U ( 'announce/index/show', array ('aid' => $value ['aid'] ) );
			}
		}
		return $return;
	}
	public function count() {
	}

	/**
	 * YUN标签初始方法
	 */
	public function yun_tag() {
		return array ('do' => array ('lists' => L ( 'lists', '', 'announce' ) ),'lists' => array () );
	}
}