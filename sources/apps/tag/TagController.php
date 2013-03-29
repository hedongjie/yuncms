<?php
/**
 * 标签向导
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-5
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: TagController.php 1025 2012-07-13 00:30:38Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
Loader::helper ( 'dbsource:global' );
error_reporting ( E_ERROR );
class TagController extends admin {
	private $db, $dbsource;
	public function __construct() {
		$this->db = Loader::model ( 'tag_model' );
		$this->dbsource = Loader::model ( 'dbsource_model' );
		parent::__construct ();
	}

	/**
	 * 标签向导列表
	 */
	public function init() {
		$page = isset ( $_POST ['page'] ) && intval ( $_POST ['page'] ) ? intval ( $_POST ['page'] ) : 1;
		$list = $this->db->order ( 'id desc' )->listinfo ( $page, 20 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( '?app=tag&controller=tag&action=add', 'add', L ( 'add_tag' ), 700, 500 );
		include $this->admin_tpl ( 'tag_list' );
	}

	/**
	 * 添加标签向导
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'name' ) . L ( 'empty' ) );
			$cache = isset ( $_POST ['cache'] ) && intval ( $_POST ['cache'] ) ? intval ( $_POST ['cache'] ) : 0;
			$num = isset ( $_POST ['num'] ) && intval ( $_POST ['num'] ) ? intval ( $_POST ['num'] ) : 0;
			$type = isset ( $_POST ['type'] ) && intval ( $_POST ['type'] ) ? intval ( $_POST ['type'] ) : 0;
			$ac = isset ( $_GET ['ac'] ) && ! empty ( $_GET ['ac'] ) ? trim ( $_GET ['ac'] ) : '';
			// 检查名称是否已经存在
			if ($this->db->getby_name ( $name )) showmessage ( L ( 'name' ) . L ( 'exists' ) );
			if ($type == '1') { // 自定义SQL
				$sql = isset ( $_POST ['data'] ) && trim ( $_POST ['data'] ) ? trim ( $_POST ['data'] ) : showmessage ( L ( 'custom_sql' ) . L ( 'empty' ) );
				$data ['sql'] = $sql;
				$tag = '<!--{yun:get sql="' . $sql . '" ';
				if ($cache) {
					$tag .= 'cache="' . $cache . '" ';
				}
				if ($_POST ['page']) {
					$tag .= 'page="' . $_POST ['page'] . '" ';
				}
				if ($_POST ['dbsource']) {
					$data ['dbsource'] = $_POST ['dbsource'];
					$tag .= 'dbsource="' . $_POST ['dbsource'] . '" ';
				}
				if ($_POST ['return']) {
					$tag .= 'return="' . $_POST ['return'] . '"';
				}
				$tag .= '}' . $name . ' start-->';
			} elseif ($type == 0) { // 模型配置
				$application = isset ( $_POST ['application'] ) && trim ( $_POST ['application'] ) ? trim ( $_POST ['application'] ) : showmessage ( L ( 'please_select_model' ) );
				$do = isset ( $_POST ['do'] ) && trim ( $_POST ['do'] ) ? trim ( $_POST ['do'] ) : showmessage ( L ( 'please_select_action' ) );
				$html = yun_tag_class ( $application );
				$data = array ();
				$tag = '<!--{yun:' . $application . ' do="' . $do . '" ';
				if (isset ( $html [$do] ) && is_array ( $html [$do] )) {
					foreach ( $html [$do] as $key => $val ) {
						$val ['validator'] ['reg_msg'] = isset ( $val ['validator'] ['reg_msg'] ) ? $val ['validator'] ['reg_msg'] : $val ['name'] . L ( 'inputerror' );
						$$key = isset ( $_POST [$key] ) && trim ( $_POST [$key] ) ? trim ( $_POST [$key] ) : '';
						if (! empty ( $val ['validator'] )) {
							if (isset ( $val ['validator'] ['min'] ) && strlen ( $$key ) < $val ['validator'] ['min']) {
								showmessage ( $val ['name'] . L ( 'should' ) . L ( 'is_greater_than' ) . $val ['validator'] ['min'] . L ( 'lambda' ) );
							}
							if (isset ( $val ['validator'] ['max'] ) && strlen ( $$key ) > $val ['validator'] ['max']) {
								showmessage ( $val ['name'] . L ( 'should' ) . L ( 'less_than' ) . $val ['validator'] ['max'] . L ( 'lambda' ) );
							}
							if (isset ( $val ['validator'] ['reg'] ) && ! preg_match ( '/' . $val ['validator'] ['reg'] . '/' . $val ['validator'] ['reg_param'], $$key )) {
								showmessage ( $val ['name'] . $val ['validator'] ['reg_msg'] );
							}
						}
						$tag .= $key . '="' . $$key . '" ';
						$data [$key] = $$key;
					}
				}
				if ($_POST ['page']) {
					$tag .= 'page="' . $_POST ['page'] . '" ';
				}
				if ($num) {
					$tag .= ' num="' . $num . '" ';
				}
				if ($_POST ['return']) {
					$tag .= ' return="' . $_POST ['return'] . '" ';
				}
				if ($cache) {
					$tag .= ' cache="' . $cache . '" ';
				}
				$tag .= '}' . $name . ' start-->';
			} else { // 碎片
				$data = isset ( $_POST ['block'] ) && trim ( $_POST ['block'] ) ? trim ( $_POST ['block'] ) : showmessage ( L ( 'block_name_not_empty' ) );
				$tag = '<!--{yun:block pos="' . $data . '"}' . $name . ' start-->';
			}
			$tag .= "\n" . '{loop $data $n $r}' . "\n" . '<li><a href="{$r[\'url\']}" title="{$r[\'title\']}">{$r[\'title\']}</a></li>' . "\n" . '{/loop}' . "\n" . '<!--{/yun}' . $name . ' end-->';
			$data = is_array ( $data ) ? array2string ( $data ) : $data;
			$this->db->insert ( array ('tag' => $tag,'name' => $name,'type' => $type,'application' => $application,'do' => $do,'data' => $data,'page' => $_POST ['page'],'return' => $_POST ['return'],'cache' => $cache,'num' => $num ) );
			if ($ac == 'js') {
				include $this->admin_tpl ( 'tag_show' );
			} else {
				showmessage ( L ( 'operation_success' ), '', '', 'add' );
			}
		} else {
			$applications = array_merge ( array ('' => L ( 'please_select' ) ), C ( 'application' ) );
			$show_header = $show_validator = true;
			$type = isset ( $_GET ['type'] ) && intval ( $_GET ['type'] ) ? intval ( $_GET ['type'] ) : 0;
			$dbsource_data = $dbsource = array ();
			$dbsource [] = L ( 'please_select' );
			$dbsource_data = $this->dbsource->field ( 'name' )->select ();
			foreach ( $dbsource_data as $dbs ) {
				$dbsource [$dbs ['name']] = $dbs ['name'];
			}
			$ac = isset ( $_GET ['ac'] ) && ! empty ( $_GET ['ac'] ) ? trim ( $_GET ['ac'] ) : '';
			$application = isset ( $_GET ['application'] ) && trim ( $_GET ['application'] ) ? trim ( $_GET ['application'] ) : '';
			$do = isset ( $_GET ['do'] ) && trim ( $_GET ['do'] ) ? trim ( $_GET ['do'] ) : '';
			if ($application) $html = yun_tag_class ( $application );
			include $this->admin_tpl ( 'tag_add' );
		}
	}

	/**
	 * 修改标签向导
	 */
	public function edit() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		if (! $edit_data = $this->db->getby_id ( $id )) showmessage ( L ( 'notfound' ) );
		if (isset ( $_POST ['dosubmit'] )) {
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'name' ) . L ( 'empty' ) );
			$cache = isset ( $_POST ['cache'] ) && intval ( $_POST ['cache'] ) ? intval ( $_POST ['cache'] ) : 0;
			$num = isset ( $_POST ['num'] ) && intval ( $_POST ['num'] ) ? intval ( $_POST ['num'] ) : 0;
			$type = isset ( $_POST ['type'] ) && intval ( $_POST ['type'] ) ? intval ( $_POST ['type'] ) : 0;
			// 检查名称是否已经存在
			if ($edit_data ['name'] != $name) {
				if ($this->db->where ( array ('name' => $name ) )->field ( 'id' )->find ()) showmessage ( L ( 'name' ) . L ( 'exists' ) );
			}
			if ($type == '1') { // 自定义SQL
				$sql = isset ( $_POST ['data'] ) && trim ( $_POST ['data'] ) ? trim ( $_POST ['data'] ) : showmessage ( L ( 'custom_sql' ) . L ( 'empty' ) );
				$data ['sql'] = $sql;
				$tag = '<!--{yun:get sql="' . $sql . '" ';
				if ($cache) {
					$tag .= 'cache="' . $cache . '" ';
				}
				if ($_POST ['page']) {
					$tag .= 'page="' . $_POST ['page'] . '" ';
				}
				if ($_POST ['dbsource']) {
					$data ['dbsource'] = $_POST ['dbsource'];
					$tag .= 'dbsource="' . $_POST ['dbsource'] . '" ';
				}
				if ($_POST ['return']) {
					$tag .= 'return="' . $_POST ['return'] . '"';
				}
				$tag .= '}' . $name . ' start-->';
			} elseif ($type == 0) { // 模型配置
				$application = isset ( $_POST ['application'] ) && trim ( $_POST ['application'] ) ? trim ( $_POST ['application'] ) : showmessage ( L ( 'please_select_model' ) );
				$do = isset ( $_POST ['do'] ) && trim ( $_POST ['do'] ) ? trim ( $_POST ['do'] ) : showmessage ( L ( 'please_select_action' ) );
				$html = yun_tag_class ( $application );
				$data = array ();
				$tag = '<!--{yun:' . $application . ' do="' . $do . '" ';
				if (isset ( $html [$do] ) && is_array ( $html [$do] )) {
					foreach ( $html [$do] as $key => $val ) {
						$val ['validator'] ['reg_msg'] = isset ( $val ['validator'] ['reg_msg'] ) ? $val ['validator'] ['reg_msg'] : $val ['name'] . L ( 'inputerror' );
						$$key = isset ( $_POST [$key] ) && trim ( $_POST [$key] ) ? trim ( $_POST [$key] ) : '';
						if (! empty ( $val ['validator'] )) {
							if (isset ( $val ['validator'] ['min'] ) && strlen ( $$key ) < $val ['validator'] ['min']) {
								showmessage ( $val ['name'] . L ( 'should' ) . L ( 'is_greater_than' ) . $val ['validator'] ['min'] . L ( 'lambda' ) );
							}
							if (isset ( $val ['validator'] ['max'] ) && strlen ( $$key ) > $val ['validator'] ['max']) {
								showmessage ( $val ['name'] . L ( 'should' ) . L ( 'less_than' ) . $val ['validator'] ['max'] . L ( 'lambda' ) );
							}
							if (isset ( $val ['validator'] ['reg'] ) && ! preg_match ( '/' . $val ['validator'] ['reg'] . '/' . $val ['validator'] ['reg_param'], $$key )) {
								showmessage ( $val ['name'] . $val ['validator'] ['reg_msg'] );
							}
						}
						$tag .= $key . '="' . $$key . '" ';
						$data [$key] = $$key;
					}
				}
				if ($_POST ['page']) {
					$tag .= 'page="' . $_POST ['page'] . '" ';
				}
				if ($num) {
					$tag .= ' num="' . $num . '" ';
				}
				if ($_POST ['return']) {
					$tag .= ' return="' . $_POST ['return'] . '" ';
				}
				if ($cache) {
					$tag .= ' cache="' . $cache . '" ';
				}
				$tag .= '}' . $name . ' start-->';
			} else { // 碎片
				$data = isset ( $_POST ['block'] ) && trim ( $_POST ['block'] ) ? trim ( $_POST ['block'] ) : showmessage ( L ( 'block_name_not_empty' ) );
				$tag = '<!--{yun:block pos="' . $data . '"}' . $name . ' start-->';
			}
			$tag .= "\n" . '{loop $data $n $r}' . "\n" . '<li><a href="{$r[\'url\']}" title="{$r[\'title\']}">{$r[\'title\']}</a></li>' . "\n" . '{/loop}' . "\n" . '<!--{/yun}' . $name . ' end-->';
			$data = is_array ( $data ) ? array2string ( $data ) : $data;
			$this->db->where ( array ('id' => $id ) )->update ( array ('tag' => $tag,'name' => $name,'type' => $type,'application' => $application,'do' => $do,'data' => $data,'page' => $_POST ['page'],'return' => $_POST ['return'],'cache' => $cache,'num' => $num ) );
			showmessage ( L ( 'operation_success' ), '', '', 'edit' );
		} else {
			$applications = array_merge ( array ('' => L ( 'please_select' ) ), C ( 'application' ) );
			$show_header = $show_validator = true;
			$type = isset ( $_GET ['type'] ) && intval ( $_GET ['type'] ) ? intval ( $_GET ['type'] ) : $edit_data ['type'];
			$dbsource_data = $dbsource = array ();
			$dbsource [] = L ( 'please_select' );
			$dbsource_data = $this->dbsource->field ( 'name' )->select ();
			foreach ( $dbsource_data as $dbs ) {
				$dbsource [$dbs ['name']] = $dbs ['name'];
			}
			$application = isset ( $_GET ['application'] ) && trim ( $_GET ['application'] ) ? trim ( $_GET ['application'] ) : $edit_data ['application'];
			$do = isset ( $_GET ['do'] ) && trim ( $_GET ['do'] ) ? trim ( $_GET ['do'] ) : $edit_data ['do'];
			if ($edit_data ['type'] == 0 || $edit_data ['type'] == 1) $form_data = string2array ( $edit_data ['data'] );
			if ($application) $html = yun_tag_class ( $application );
			include $this->admin_tpl ( 'tag_edit' );
		}
	}

	/**
	 * 删除标签向导
	 */
	public function del() {
		$id = isset ( $_GET ['id'] ) ? $_GET ['id'] : '';
		if (is_array ( $id )) {
			foreach ( $id as $key => $v ) {
				if (intval ( $v ))
					$id [$key] = intval ( $v );
				else
					unset ( $id [$key] );
			}
			$sql = implode ( ',', $id );
			$where = array ('id' => array ('in',$sql ) );
			$this->db->where ( $where )->delete ();
			showmessage ( L ( 'operation_success' ),'?app=tag&controller=tag&action=init&menuid='.$_GET['menuid'] );
		} else {
			$id = intval ( $id );
			if (empty ( $id )) showmessage ( L ( 'illegal_parameters' ),'?app=tag&controller=tag&action=init&menuid='.$_GET['menuid']);
			if ($this->db->where ( array ('id' => $id ) )->delete ()) {
				showmessage ( L ( 'operation_success' ), '?app=tag&controller=tag&action=init&menuid='.$_GET['menuid'] );
			} else {
				showmessage ( L ( 'operation_failure' ), '?app=tag&controller=tag&action=init&menuid='.$_GET['menuid']);
			}
		}
	}

	/**
	 * 检验是否重名
	 */
	public function public_name() {
		$name = isset ( $_GET ['name'] ) && trim ( $_GET ['name'] ) ? (C ( 'config', 'charset' ) == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['name'] ) ) : trim ( $_GET ['name'] )) : exit ( '0' );
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : '';
		$data = array ();
		if ($id) {
			$data = $this->db->where ( array ('id' => $id ) )->field ( 'name' )->find ();
			if (! empty ( $data ) && $data ['name'] == $name) exit ( '1' );
		}
		if ($this->db->where ( array ('name' => $name ) )->field ( 'id' )->find ())
			exit ( '0' );
		else
			exit ( '1' );
	}
}