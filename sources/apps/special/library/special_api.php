<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 专题执行接口类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: special_api.php 996 2012-07-07 01:05:41Z 85825770@qq.com $
 */
class special_api {
	private $db, $type_db, $c_db, $data_db;
	public $pages;
	public function __construct() {
		$this->db = Loader::model ( 'special_model' ); // 专题数据模型
		$this->type_db = Loader::model ( 'type_model' ); // 专题分类数据模型
		$this->c_db = Loader::model ( 'special_content_model' ); // 专题内容数据模型
		$this->data_db = Loader::model ( 'special_c_data_model' );
	}

	/**
	 * 更新分类
	 *
	 * @param intval $pid 专题ID
	 * @param string $type 分类字符串 每行一个分类。格式为：分类名|分类目录，例:最新新闻|news last
	 * @param string $a 添加时直接加入到数据库，修改是需要判断。
	 * @return boolen
	 */
	public function _update_type($specialid, $type, $a = 'add') {
		$specialid = intval ( $specialid );
		if (! $specialid) return false;
		$special_info = $this->db->getby_id ( $specialid );
		$app_path = substr ( SITE_URL, 0, - 1 );
		foreach ( $type as $k => $v ) {
			if (! $v ['name'] || ! $v ['typedir']) continue;
			// 添加时，无需判断直接加到数据表中，修改时应判断是否为新添加、修改还是删除
			if ($a == 'add' && ! $v ['del']) {
				$typeid = $this->type_db->insert ( array ('application' => 'special','name' => $v ['name'],'listorder' => $v ['listorder'],'typedir' => $v ['typedir'],'parentid' => $specialid,'listorder' => $k ), true );
				if ($special_info ['ishtml'])
					$url = $app_path . C ( 'system', 'html_root' ) . '/special/' . $special_info ['filename'] . '/' . $v ['typedir'] . '/' . 'type-' . $typeid . '.html';
				else
					$url = SITE_URL . 'index.php?app=special&controller=index&action=type&specialid=' . $specialid . '&typeid=' . $typeid;
				$this->type_db->where(array ('typeid' => $typeid ))->update ( array ('url' => $url ) );
			} elseif ($a == 'edit') {
				if ((! isset ( $v ['typeid'] ) || empty ( $v ['typeid'] )) && (! isset ( $v ['del'] ) || empty ( $v ['del'] ))) {
					$typeid = $this->type_db->insert ( array ('application' => 'special','name' => $v ['name'],'listorder' => $v ['listorder'],'typedir' => $v ['typedir'],'parentid' => $specialid,'listorder' => $k ), true );
					if ($special_info ['ishtml'])
						$url = $app_path . C ( 'system', 'html_root' ) . '/special/' . $special_info ['filename'] . '/' . $v ['typedir'] . '/' . 'type-' . $typeid . '.html';
					else
						$url = SITE_URL . 'index.php?app=special&controller=index&action=type&specialid=' . $specialid . '&typeid=' . $typeid;
					$v ['url'] = $url;
					$this->type_db->where ( array ('typeid' => $typeid ) )->update ( $v );
				}
				if ((! isset ( $v ['del'] ) || empty ( $v ['del'] )) && $v ['typeid']) {
					$this->type_db->where ( array ('typeid' => $v ['typeid'] ) )->update ( array ('name' => $v ['name'],'typedir' => $v ['typedir'],'listorder' => $v ['listorder'] ) );
					if ($special_info ['ishtml'])
						$url = $app_path . C ( 'system', 'html_root' ) . '/special/' . $special_info ['filename'] . '/' . $v ['typedir'] . '/' . 'type-' . $v ['typeid'] . '.html';
					else
						$url = SITE_URL . 'index.php?app=special&controller=index&action=type&specialid=' . $specialid . '&typeid=' . $v ['typeid'];
					$v ['url'] = $url;
					$typeid = $v ['typeid'];
					unset ( $v ['typeid'] );
					$this->type_db->where ( array ('typeid' => $typeid ) )->update ( $v );
				}
				if ($v ['typeid'] && $v ['del']) {
					$this->delete_type ( $v ['typeid'], $special_info ['ishtml'] );
				}
			}
		}
		return true;
	}

	/**
	 * 调取内容信息
	 *
	 * @param intval $modelid 模型ID
	 * @param string $where sql语句
	 * @param intval $page 分页
	 * @return array 返回调取的数据
	 */
	public function _get_import_data($modelid = 0, $where = '', $page) {
		$c = Loader::model ( 'content_model' );
		if (! $modelid) return '';
		$c->set_model ( $modelid );
		$data = $c->where($where)->order('id  DESC')->listinfo ($page );
		$this->pages = $c->pages;
		return $data;
	}

	/**
	 * 信息推荐至专题接口
	 *
	 * @param array $param
	 *        	属性 请求时，为模型、栏目数组。 例：array('modelid'=>1, 'catid'=>12);
	 *        	提交添加为二维信息数据 。例：array(1=>array('title'=>'多发发送方法', ....))
	 * @param array $arr 参数 表单数据，只在请求添加时传递。
	 * @return 返回专题的下拉列表
	 */
	public function _get_special($param = array(), $arr = array()) {
		if ($arr ['dosubmit']) {
			foreach ( $param as $id => $v ) {
				if (! $arr ['specialid'] || ! $arr ['typeid']) continue;
				if (! $this->c_db->where(array ('title' => $v ['title'],'specialid' => $arr ['specialid'] ) )->find ( )) {
					$info ['specialid'] = $arr ['specialid'];
					$info ['typeid'] = $arr ['typeid'];
					$info ['title'] = $v ['title'];
					$info ['thumb'] = $v ['thumb'];
					$info ['url'] = $v ['url'];
					$info ['curl'] = $v ['id'] . '|' . $v ['catid'];
					$info ['description'] = $v ['description'];
					$info ['userid'] = $v ['userid'];
					$info ['username'] = $v ['username'];
					$info ['inputtime'] = $v ['inputtime'];
					$info ['updatetime'] = $v ['updatetime'];
					$info ['islink'] = 1;
					$this->c_db->insert ( $info, true );
				}
			}
			return true;
		} else {
			$datas = S ( 'common/special' );
			$special = array (L ( 'please_select' ) );
			if (is_array ( $datas )) {
				foreach ( $datas as $sid => $d ) {
					$special [$sid] = $d ['title'];
				}
			}
			return array ('specialid' => array ('name' => L ( 'special_list', '', 'special' ),'htmltype' => 'select','data' => $special,'ajax' => array ('name' => L ( 'for_type', '', 'special' ),'action' => '_get_type','m' => 'special','id' => 'typeid' ) ),
					'validator' => '$(\'#specialid\').formValidator({autotip:true,onshow:"' . L ( 'please_choose_special', '', 'special' ) . '",oncorrect:"' . L ( 'true', '', 'special' ) . '"}).inputValidator({min:1,onerror:"' . L ( 'please_choose_special', '', 'special' ) . '"});$(\'#typeid\').formValidator({autotip:true,onshow:"' . L ( 'please_choose_type', '', 'special' ) . '",oncorrect:"' . L ( 'true', '', 'special' ) . '"}).inputValidator({min:1,onerror:"' . L ( 'please_choose_type', '', 'special' ) . '"});' );
		}
	}

	/**
	 * 获取分类
	 *
	 * @param intval $specialid 专题ID
	 */
	public function _get_type($specialid = 0) {
		$type_db = Loader::model ( 'type_model' );
		$data = $arr = array ();
		$data = $type_db->where(array ('application' => 'special','parentid' => $specialid ) )->select ( );
		foreach ( $data as $r ) {
			$arr [$r ['typeid']] = $r ['name'];
		}
		return Form::select ( $arr, '', 'name="typeid", id="typeid"', L ( 'please_select' ) );
	}

	/**
	 * 调取专题的附属分类
	 *
	 * @param intval $specialid 专题ID
	 * @return array 专题的附属分类
	 */
	public function _get_types($specialid = 0) {
		if (! $specialid) return false;
		$rs = $this->type_db->where(array ('parentid' => $specialid ))->field('typeid, name')->select ( );
		$types = array ();
		foreach ( $rs as $r ) {
			$types [$r ['typeid']] = $r ['name'];
		}
		return $types;
	}

	/**
	 * 删除专题 执行删除操作的方法，同时删除专题下的分类、信息、及生成静态文件和图片
	 *
	 * @param intval $id 专题ID
	 * @return boolen
	 */
	public function _del_special($id = 0) {
		$id = intval ( $id );
		if (! $id) return false;
		// 检查专题下是否有信息
		$rs = $this->c_db->where(array ('specialid' => $id ) )->field('id')->select ( );
		$info = $this->db->where ( array ('id' => $id ))->field('ispage, filename, ishtml' )->find();
		// 有信息时，循环删除
		if (is_array ( $rs ) && ! empty ( $rs )) {
			foreach ( $rs as $r ) {
				$this->_delete_content ( $r ['id'], $info ['ishtml'] );
			}
		}
		// 删除专题的附属分类
		$type_info = $this->type_db->where(array ('application' => 'special','parentid' => $id ))->field('typeid')->select ();
		if (is_array ( $type_info ) && ! empty ( $type_info )) {
			foreach ( $type_info as $t ) {
				$this->delete_type ( $t ['typeid'], $info ['ishtml'] );
			}
		}
		$this->db->where(array ('id' => $id ))->delete (  );
		if ($info ['ishtml']) {
			Folder::delete ( BASE_PATH . C ( 'system', 'html_root' ) . DIRECTORY_SEPARATOR . 'special' . DIRECTORY_SEPARATOR . $info ['filename'] ); // 删除专题目录
		}
		if (C ( 'attachment', 'stat' )) {
			$keyid = 'special-' . $id;
			$this->attachment_db = Loader::model ( 'attachment_model' );
			$this->attachment_db->api_delete ( $keyid );
		}
		return true;
	}

	/**
	 * 导入的数据添加到数据表
	 *
	 * @param intval $modelid 模型ID
	 * @param intval $specialid 信息的所属专题ID
	 * @param intval $id 信息的ID
	 * @param intval $typeid 信息的分类ID
	 * @param intval $listorder 信息的排序
	 */
	public function _import($modelid, $specialid, $id, $typeid, $listorder = 0) {
		if (! $specialid || ! $id || ! $typeid) return false;
		$c = Loader::model ( 'content_model' );
		$c->set_model ( $modelid );
		$info = $c->where ( array ('id' => $id,'status' => 99 ))->field('id, catid, title, thumb, url, description, username, inputtime, updatetime' )->find();
		if ($info) {
			$info ['curl'] = $info ['id'] . '|' . $info ['catid'];
			unset ( $info ['id'], $info ['catid'] );
			if (! $this->c_db->get_one ( array ('title' => addslashes ( $info ['title'] ),'specialid' => $specialid,'typeid' => $typeid ) )) {
				$info ['specialid'] = $specialid;
				$info ['typeid'] = $typeid;
				$info ['islink'] = 1;
				$info ['listorder'] = $listorder;
				$info = new_addslashes ( $info );
				return $this->c_db->insert ( $info, true );
			}
		}
		return false;
	}

	/**
	 * 删除专题分类
	 *
	 * @param intval $typeid 专题附属分类ID
	 * @param intval $ishtml 专题是否生成静态
	 */
	private function delete_type($typeid = 0, $ishtml = 0) {
		$typeid = intval ( $typeid );
		if (! $typeid) return false;
		$info = $this->type_db->getby_typeid ( $typeid );
		if ($ishtml) {
			for($i = 1; $i > 0; $i ++) {
				if ($i == 1)
					$file = str_replace ( SITE_URL, '', $info ['url'] );
				else
					$file = str_replace ( array (SITE_URL,'.html' ), array ('','-' . $i . '.html' ), $info ['url'] );
				if (! file_exists ( BASE_PATH . $file )) {
					break;
				} else {
					unlink ( BASE_PATH . $file ); // 删除生成的静态文件
				}
			}
		}
		$this->type_db->where(array ('typeid' => $typeid ))->delete (  ); // 删除数据表记录
		return true;
	}

	/**
	 * 删除专题信息，同时删除专题的信息，及相关的静态文件、图片
	 *
	 * @param intval $cid 专题信息ID
	 * @param intval $ishtml 专题是否生成静态
	 */
	public function _delete_content($cid = 0, $ishtml = 0) {
		$info = $this->c_db->where ( array ('id' => $cid ) )->field ( 'inputtime, isdata' )->find ();
		if ($info ['isdata']) {
			if ($ishtml) {
				Loader::helper ( 'special:global' );
				for($i = 1; $i > 0; $i ++) {
					$file = content_url ( $cid, $i, $info ['inputtime'] );
					if (! file_exists ( BASE_PATH . $file [1] )) {
						break;
					} else {
						unlink ( BASE_PATH . $file [1] ); // 删除生成的静态文件
					}
				}
			}
			// 删除全站搜索数据
			$this->search_api ( $cid, '', '', 'delete' );

			// 删除数据统计表数据
			$count = Loader::model ( 'hits_model' );
			$hitsid = 'special-c-' . $info ['specialid'] . '-' . $cid;
			$count->where ( array ('hitsid' => $hitsid ) )->delete ();
			// 删除信息内容表中的数据
			$this->data_db->where ( array ('id' => $cid ) )->delete ();
		}
		$this->c_db->where ( array ('id' => $cid ) )->delete (); // 删除信息表中的数据
		return true;
	}

	/**
	 * 添加到全站搜索
	 *
	 * @param intval $id 文章ID
	 * @param array $data 数组
	 * @param string $title 标题
	 * @param string $action 动作
	 */
	private function search_api($id = 0, $data = array(), $title, $action = 'update') {
		$this->search_db = Loader::model ( 'search_model' );
		$type_arr = S ( 'search/type_application' );
		$typeid = $type_arr ['special'];
		if ($action == 'update') {
			$fulltextcontent = $data ['content'];
			return $this->search_db->update_search ( $typeid, $id, $fulltextcontent, $title );
		} elseif ($action == 'delete') {
			$this->search_db->delete_search ( $typeid, $id );
		}
	}
}