<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
error_reporting ( E_ERROR );
/**
 * 数据源管理
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: DataController.php 254 2012-11-08 01:00:18Z xutongle $
 */
class DataController extends admin {
	private $db;
	public function __construct() {
		$this->db = Loader::model ( 'datacall_model' );
		parent::__construct ();
	}

	/**
	 * 数据源
	 */
	public function init() {
		$page = isset ( $_POST ['page'] ) && intval ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$list = $this->db->order('id desc')->listinfo ( $page, 20 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'dbsource/data/add' ), 'add', L ( 'adding_data_source_call' ), 700, 500 );
		include $this->admin_tpl ( 'data_list' );
	}

	/**
	 * 添加数据源
	 */
	public function add() {
		Loader::helper ( 'dbsource:global' );
		if (isset ( $_POST ['dosubmit'] )) {
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'name' ) . L ( 'empty' ) );
			$dis_type = isset ( $_POST ['dis_type'] ) && intval ( $_POST ['dis_type'] ) ? intval ( $_POST ['dis_type'] ) : 1;
			$cache = isset ( $_POST ['cache'] ) && intval ( $_POST ['cache'] ) ? intval ( $_POST ['cache'] ) : 0;
			$num = isset ( $_POST ['num'] ) && intval ( $_POST ['num'] ) ? intval ( $_POST ['num'] ) : 0;
			$type = isset ( $_POST ['type'] ) && intval ( $_POST ['type'] ) ? intval ( $_POST ['type'] ) : 0;
			// 检查名称是否已经存在
			if ($this->db->getby_name ( $name )) showmessage ( L ( 'name' ) . L ( 'exists' ) );
			$sql = array ();
			if ($type == '1') { // 自定义SQL
				$data = isset ( $_POST ['data'] ) && trim ( $_POST ['data'] ) ? trim ( $_POST ['data'] ) : showmessage ( L ( 'custom_sql' ) . L ( 'empty' ) );
				$sql = array ('data' => $data );
			} else { // 模型配置方式
				$application = isset ( $_POST ['application'] ) && trim ( $_POST ['application'] ) ? trim ( $_POST ['application'] ) : showmessage ( L ( 'please_select_model' ) );
				$do = isset ( $_POST ['do'] ) && trim ( $_POST ['do'] ) ? trim ( $_POST ['do'] ) : showmessage ( L ( 'please_select_action' ) );
				$html = yun_tag_class ( $application );
				$data = array ();
				if (isset ( $html [$do] ) && is_array ( $html [$do] )) {
					foreach ( $html [$do] as $key => $val ) {
						$val ['validator'] ['reg_msg'] = isset($val ['validator'] ['reg_msg']) ? $val ['validator'] ['reg_msg'] : $val ['name'] . L ( 'inputerror' );
						$$key = isset ( $_POST [$key] ) && trim ( $_POST [$key] ) ? trim ( $_POST [$key] ) : '';
						if (! empty ( $val ['validator'] )) {
							if (isset ( $val ['validator'] ['min'] ) && strlen ( $$key ) < $val ['validator'] ['min']) {
								showmessage ( $val ['name'] . L ( 'should' ) . L ( 'is_greater_than' ) . $val ['validator'] ['min'] . L ( 'lambda' ) );
							}
							if (isset ( $val ['validator'] ['max'] ) && strlen ( $$key ) > $val ['validator'] ['max']) {
								showmessage ( $val ['name'] . L ( 'should' ) . L ( 'less_than' ) . $val ['validator'] ['max'] . L ( 'lambda' ) );
							}
							if (! preg_match ( '/' . $val ['validator'] ['reg'] . '/' . $val ['validator'] ['reg_param'], $$key )) {
								showmessage ( $val ['name'] . $val ['validator'] ['reg_msg'] );
							}
						}
						$data [$key] = $$key;
					}
				}
				$sql = array ('data' => array2string ( $data ),'application' => $application,'do' => $do );
			}

			if ($dis_type == 3) {
				$sql ['template'] = isset ( $_POST ['template'] ) && trim ( $_POST ['template'] ) ? trim ( $_POST ['template'] ) : '';
			}
			// 初始化数据
			$sql ['name'] = $name;
			$sql ['type'] = $type;
			$sql ['dis_type'] = $dis_type;
			$sql ['cache'] = $cache;
			$sql ['num'] = $num;
			if ($id = $this->db->insert ( $sql, true )) {
				// 当为JS时，输出模板文件
				if ($dis_type == 3) {
					$tpl = Loader::lib ( 'Template' );
					$str = $tpl->template_parse ( $sql ['template'] );
					$filepath = DATA_PATH . 'dbsource' . DIRECTORY_SEPARATOR;
					if (! is_dir ( $filepath )) {
						mkdir ( $filepath, 0777, true );
					}
					@file_put_contents ( $filepath . $id . '.php', $str );
				}
				showmessage ( L ( 'operation_success' ), '', '', 'add' );
			} else {
				showmessage ( L ( 'operation_failure' ) );
			}
		} else {
			$applications = array_merge ( array ('' => L ( 'please_select' ) ), C ( 'application' ) ); // 加载应用
			$show_header = $show_validator = true;
			$type = isset ( $_GET ['type'] ) && intval ( $_GET ['type'] ) ? intval ( $_GET ['type'] ) : 0;
			$application = isset ( $_GET ['application'] ) && trim ( $_GET ['application'] ) ? trim ( $_GET ['application'] ) : '';
			$do = isset ( $_GET ['do'] ) && trim ( $_GET ['do'] ) ? trim ( $_GET ['do'] ) : '';
			if ($application) $html = yun_tag_class ( $application );
			Loader::helper ( 'template:global' );
			include $this->admin_tpl ( 'data_add' );
		}
	}

	/**
	 * 修改数据源
	 */
	public function edit() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		if (! $edit_data = $this->db->getby_id ( $id )) showmessage ( L ( 'notfound' ) );
		Loader::helper ( 'dbsource:global' );
		if (isset ( $_POST ['dosubmit'] )) {
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'name' ) . L ( 'empty' ) );
			$dis_type = isset ( $_POST ['dis_type'] ) && intval ( $_POST ['dis_type'] ) ? intval ( $_POST ['dis_type'] ) : 1;
			$cache = isset ( $_POST ['cache'] ) && intval ( $_POST ['cache'] ) ? intval ( $_POST ['cache'] ) : 0;
			$num = isset ( $_POST ['num'] ) && intval ( $_POST ['num'] ) ? intval ( $_POST ['num'] ) : 0;
			$type = isset ( $_POST ['type'] ) && intval ( $_POST ['type'] ) ? intval ( $_POST ['type'] ) : 0;
			// 检查名称是否已经存在
			if ($edit_data ['name'] != $name) {
				if ($this->db->where ( array ('name' => $name ) )->field('id')->find()) showmessage ( L ( 'name' ) . L ( 'exists' ) );
			}
			$sql = array ();
			if ($type == '1') { // 自定义SQL
				$data = isset ( $_POST ['data'] ) && trim ( $_POST ['data'] ) ? trim ( $_POST ['data'] ) : showmessage ( L ( 'custom_sql' ) . L ( 'empty' ) );
				$sql = array ('data' => $data );
			} else { // 模型配置方式
				$application = isset ( $_POST ['application'] ) && trim ( $_POST ['application'] ) ? trim ( $_POST ['application'] ) : showmessage ( L ( 'please_select_model' ) );
				$do = isset ( $_POST ['do'] ) && trim ( $_POST ['do'] ) ? trim ( $_POST ['do'] ) : showmessage ( L ( 'please_select_action' ) );
				$html = yun_tag_class ( $application );
				$data = array ();
				if (isset ( $html [$do] ) && is_array ( $html [$do] )) {
					foreach ( $html [$do] as $key => $val ) {
						$val ['validator'] ['reg_msg'] = $val ['validator'] ['reg_msg'] ? $val ['validator'] ['reg_msg'] : $val ['name'] . L ( 'inputerror' );
						$$key = isset ( $_POST [$key] ) && trim ( $_POST [$key] ) ? trim ( $_POST [$key] ) : '';
						if (! empty ( $val ['validator'] )) {
							if (isset ( $val ['validator'] ['min'] ) && strlen ( $$key ) < $val ['validator'] ['min']) {
								showmessage ( $val ['name'] . L ( 'should' ) . L ( 'is_greater_than' ) . $val ['validator'] ['min'] . L ( 'lambda' ) );
							}
							if (isset ( $val ['validator'] ['max'] ) && strlen ( $$key ) > $val ['validator'] ['max']) {
								showmessage ( $val ['name'] . L ( 'should' ) . L ( 'less_than' ) . $val ['validator'] ['max'] . L ( 'lambda' ) );
							}
							if (! preg_match ( '/' . $val ['validator'] ['reg'] . '/' . $val ['validator'] ['reg_param'], $$key )) {
								showmessage ( $val ['name'] . $val ['validator'] ['reg_msg'] );
							}
						}
						$data [$key] = $$key;
					}
				}
				$sql = array ('data' => array2string ( $data ),'application' => $application,'do' => $do );
			}

			if ($dis_type == 3) {
				$sql ['template'] = isset ( $_POST ['template'] ) && trim ( $_POST ['template'] ) ? trim ( $_POST ['template'] ) : '';
			}
			// 初始化数据
			$sql ['name'] = $name;
			$sql ['type'] = $type;
			$sql ['dis_type'] = $dis_type;
			$sql ['cache'] = $cache;
			$sql ['num'] = $num;
			if ($this->db->where(array ('id' => $id ))->update ( $sql )) {
				// 当为JS时，输出模板文件
				if ($dis_type == 3) {
					$tpl = Loader::lib ( 'Template' );
					$str = $tpl->template_parse ( $sql ['template'] );
					$filepath = DATA_PATH . 'dbsource' . DIRECTORY_SEPARATOR;
					if (! is_dir ( $filepath )) {
						mkdir ( $filepath, 0777, true );
					}
					@file_put_contents ( $filepath . $id . '.php', $str );
				}

				showmessage ( L ( 'operation_success' ), '', '', 'edit' );
			} else {
				showmessage ( L ( 'operation_failure' ) );
			}
		} else {
			$applications = array_merge ( array ('' => L ( 'please_select' ) ), C ( 'application' ) );
			$show_header = $show_validator = true;
			$type = isset ( $_GET ['type'] ) ? intval ( $_GET ['type'] ) : $edit_data ['type'];
			$application = isset ( $_GET ['application'] ) && trim ( $_GET ['application'] ) ? trim ( $_GET ['application'] ) : $edit_data ['application'];
			$do = isset ( $_GET ['action'] ) && trim ( $_GET ['do'] ) ? trim ( $_GET ['do'] ) : $edit_data ['do'];
			if ($edit_data ['type'] == 0) $form_data = string2array ( $edit_data ['data'] );
			if ($application) $html = yun_tag_class ( $application );
			Loader::helper ( 'template:global' );
			include $this->admin_tpl ( 'data_edit' );
		}
	}

	/**
	 * 删除数据源
	 */
	public function del() {
		$id = isset ( $_GET ['id'] ) ? $_GET ['id'] : '';
		if (is_array ( $id )) {
			foreach ( $id as $key => $v ) {
				if (intval ( $v )) $id [$key] = intval ( $v );
				else unset ( $id [$key] );
			}
			$sql = implode ( '\',\'', $id );
			$this->db->where(array('in',$sql))->delete (  );
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$id = intval ( $id );
			if (empty ( $id )) showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			if ($this->db->where(array ('id' => $id ))->delete (  )) {
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			} else {
				showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
			}
		}
	}

	/**
	 * 判断名称是否可用
	 */
	public function public_name() {
		$name = isset ( $_GET ['name'] ) && trim ( $_GET ['name'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['name'] ) ) : trim ( $_GET ['name'] )) : exit ( '0' );
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : '';
		$data = array ();
		if ($id) {
			$data = $this->db->where ( array ('id' => $id ) )->field('name')->find();
			if (! empty ( $data ) && $data ['name'] == $name) exit ( '1' );
		}
		if ($this->db->where ( array ('name' => $name ) )->field('id')->find()) exit ( '0' );
		else exit ( '1' );
	}
}