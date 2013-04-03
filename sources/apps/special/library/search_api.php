<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 专题执行接口类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: search_api.php 883 2012-06-13 06:05:36Z 85825770@qq.com $
 */
class search_api {
	private $db, $c;
	public function __construct() {
		$this->db = Loader::model ( 'special_content_model' );
		$this->c = Loader::model ( 'special_c_data_model' );
	}

	/**
	 * 获取内容接口
	 *
	 * @param intval $pagesize 每页个数
	 * @param intval $page 当前页数
	 */
	public function fulltext_api($pagesize = 100, $page = 1) {
		$result = $r = $data = $tem = array ();
		$offset = ($page - 1) * $pagesize;
		$result = $this->db->where ( array ('isdata' => 1 ) )->field ( 'id, title, inputtime' )->limit ( $offset . ',' . $pagesize )->order ( 'id ASC' )->select ();
		foreach ( $result as $r ) {
			$d = $this->c->where ( array ('id' => $r ['id'] ) )->field ( 'content' )->find ();
			$tem ['title'] = addslashes ( $r ['title'] );
			$tem ['fulltextcontent'] = $d ['content'];
			$tem ['adddate'] = $r ['inputtime'];
			$data [$r ['id']] = $tem;
		}
		return $data;
	}

	/**
	 * 计算总数接口
	 */
	public function total() {
		$r = $this->db->where ( array ('isdata' => 1 ))->count();
		return $r ['num'];
	}

	/**
	 * 获取专题下内容数据
	 *
	 * @param string/intval $ids 多个id用“,”分开
	 */
	public function get_search_data($ids) {
		$data = $this->db->where(array('id'=>array('in',$ids)))->field('id, title, thumb, description, url, inputtime')->key('id')->select ();
		return $data;
	}
}