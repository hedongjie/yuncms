<?php
/**
 * 角色操作接口
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class role_op {

	public function __construct() {
		$this->db = Loader::model ( 'admin_role_model' );
		$this->priv_db = Loader::model ( 'admin_role_priv_model' );
	}

	/**
	 * 获取角色中文名称
	 *
	 * @param int $roleid 角色ID
	 */
	public function get_rolename($roleid) {
		$roleid = intval ( $roleid );
		$info = $this->db->where ( array ('roleid' => $roleid ) )->field ( 'roleid,rolename' )->find ();
		return $info;
	}

	/**
	 * 检查角色名称重复
	 *
	 * @param $name 角色组名称
	 */
	public function checkname($name) {
		$info = $this->db->where ( array ('rolename' => $name ) )->field ( 'roleid' )->find ();
		if ($info ['roleid']) {
			return true;
		}
		return false;
	}

	/**
	 * 获取菜单表信息
	 *
	 * @param int $menuid 菜单ID
	 * @param int $menu_info 菜单数据
	 */
	public function get_menuinfo($menuid, $menu_info) {
		$menuid = intval ( $menuid );
		unset ( $menu_info [$menuid] ['id'] );
		return $menu_info [$menuid];
	}

	/**
	 * 检查指定菜单是否有权限
	 *
	 * @param array $data menu表中数组
	 * @param int $roleid 需要检查的角色ID
	 */
	public function is_checked($data, $roleid, $priv_data) {
		$priv_arr = array ('application','controller','action','data' );
		if ($data ['application'] == '') return false;
		foreach ( $data as $key => $value ) {
			if (! in_array ( $key, $priv_arr )) unset ( $data [$key] );
		}
		$data ['roleid'] = $roleid;
		$info = in_array ( $data, $priv_data );
		if ($info) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 是否为设置状态
	 */
	public function is_setting($roleid) {
		$roleid = intval ( $roleid );
		$result = $this->priv_db->where ( array ('roleid' => $roleid,'application' => array ('neq','' ) ) )->find ();
		return $result ? true : false;
	}
}
?>