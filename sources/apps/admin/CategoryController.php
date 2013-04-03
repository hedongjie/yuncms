<?php
/**
 * 栏目管理
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
error_reporting ( E_ERROR );
class CategoryController extends admin {
	private $db;
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'category_model' );
	}

	/**
	 * 管理栏目
	 */
	public function init() {
		$show_hash = '';
		$tree = new Tree ();
		$models = S ( 'common/model' ); // 加载模型缓存
		$category_items = array ();
		foreach ( $models as $modelid => $model ) {
			$category_items [$modelid] = S ( 'common/category_items_' . $modelid );
		}
		$tree->icon = array ('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ' );
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$categorys = array ();
		// 读取缓存
		$result = S ( 'common/category_content' ); // 加载栏目缓存
		$show_detail = count ( $result ) < 500 ? 1 : 0;
		$parentid = isset ( $_GET ['parentid'] ) ? intval ( $_GET ['parentid'] ) : 0;
		$html_root = C ( 'system', 'html_root' );
		$types = array (0 => L ( 'category_type_system' ),1 => L ( 'category_type_page' ),2 => L ( 'category_type_link' ) );
		if (! empty ( $result ) && is_array ( $result )) {
			foreach ( $result as $r ) {
				$r ['modelname'] = ($r ['modelid'] > 0) ? $models [$r ['modelid']] ['name'] : '';
				$r ['str_manage'] = '';
				if (! $show_detail) {
					if ($r ['parentid'] != $parentid) continue;
					$r ['parentid'] = 0;
					$r ['str_manage'] .= '<a href="' . U ( 'admin/category/init', array ('parentid' => $r ['catid'],'menuid' => $_GET ['menuid'],'s' => $r ['type'] ) ) . '">' . L ( 'manage_sub_category' ) . '</a> | ';
				}
				$r ['str_manage'] .= '<a href="?app=admin&controller=category&action=add&parentid=' . $r ['catid'] . '&menuid=' . $_GET ['menuid'] . '&s=' . $r ['type'] . '">' . L ( 'add_sub_category' ) . '</a> | ';
				$r ['str_manage'] .= '<a href="?app=admin&controller=category&action=edit&catid=' . $r ['catid'] . '&menuid=' . $_GET ['menuid'] . '&type=' . $r ['type'] . '">' . L ( 'edit' ) . '</a> | <a href="' . art_confirm ( L ( 'confirm', array ('message' => addslashes ( $r ['catname'] ) ) ), '?app=admin&controller=category&action=delete&catid=' . $r ['catid'] . '&menuid=' . $_GET ['menuid'] ) . '">' . L ( 'delete' ) . '</a> ';
				$r ['typename'] = $types [$r ['type']];
				$r ['display_icon'] = $r ['ismenu'] ? '' : ' <img src ="' . IMG_PATH . 'icon/gear_disable.png" title="' . L ( 'not_display_in_menu' ) . '">';
				if ($r ['type'] || $r ['child']) {
					$r ['items'] = '';
				} else {
					$r ['items'] = $category_items [$r ['modelid']] [$r ['catid']];
				}
				$r ['help'] = '';
				$setting = string2array ( $r ['setting'] );
				if ($r ['url']) {
					if (preg_match ( '/^(http|https):\/\//', $r ['url'] )) {
						$catdir = $r ['catdir'];
						$prefix = $r ['sethtml'] ? '' : $html_root;
						$catdir = $prefix . '/' . $r ['parentdir'] . $catdir;
						if ($r ['type'] == 0 && $setting ['ishtml'] && strpos ( $r ['url'], '?' ) === false && substr_count ( $r ['url'], '/' ) < 4) $r ['help'] = '<img src="' . IMG_PATH . 'icon/help.png" title="' . L ( 'tips_domain' ) . $r ['url'] . '&#10;' . L ( 'directory_binding' ) . '&#10;' . $catdir . '/">';
					} else {
						$r ['url'] = substr ( SITE_URL, 0, - 1 ) . $r ['url'];
					}
					$r ['url'] = "<a href='{$r['url']}' target='_blank'>" . L ( 'vistor' ) . "</a>";
				} else {
					$r ['url'] = "<a href='?app=admin&controller=category&action=public_cache&menuid=128&application=admin'><font color='red'>" . L ( 'update_backup' ) . "</font></a>";
				}
				$categorys [$r ['catid']] = $r;
			}
		}
		$str = "<tr>
		<td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input-text-c'></td>
		<td align='center'>\$id</td>
		<td >\$spacer\$catname\$display_icon</td>
		<td>\$typename</td>
		<td>\$modelname</td>
		<td align='center'>\$items</td>
		<td align='center'>\$url</td>
		<td align='center'>\$help</td>
		<td align='center' >\$str_manage</td>
		</tr>";
		$tree->init ( $categorys );
		$categorys = $tree->get_tree ( 0, $str );
		include $this->admin_tpl ( 'category_manage' );
	}

	/**
	 * 添加栏目
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['info'] ['type'] = isset ( $_POST ['type'] ) ? intval ( $_POST ['type'] ) : 0; // 默认是内容
			if (isset ( $_POST ['batch_add'] ) && empty ( $_POST ['batch_add'] )) {
				if ($_POST ['info'] ['catname'] == '') showmessage ( L ( 'input_catname' ) );
				if ($_POST ['info'] ['type'] != 2) {
					if ($_POST ['info'] ['catdir'] == '') showmessage ( L ( 'input_dirname' ) );
					if (! $this->public_check_catdir ( 0, $_POST ['info'] ['catdir'] )) showmessage ( L ( 'catname_have_exists' ) );
				}
			}
			$_POST ['info'] ['application'] = 'content';
			$setting = $_POST ['setting'];
			if ($_POST ['info'] ['type'] != 2) {
				// 栏目生成静态配置
				if ($setting ['ishtml']) {
					$setting ['category_ruleid'] = $_POST ['category_html_ruleid'];
				} else {
					$setting ['category_ruleid'] = $_POST ['category_php_ruleid'];
					$_POST ['info'] ['url'] = '';
				}
			}

			// 内容生成静态配置
			if ($setting ['content_ishtml']) {
				$setting ['show_ruleid'] = $_POST ['show_html_ruleid'];
			} else {
				$setting ['show_ruleid'] = $_POST ['show_php_ruleid'];
			}
			if ($setting ['repeatchargedays'] < 1) $setting ['repeatchargedays'] = 1;
			$_POST ['info'] ['sethtml'] = $setting ['create_to_html_root'];

			$_POST ['info'] ['setting'] = array2string ( $setting );
			$end_str = $old_end = '<script type="text/javascript">window.top.art.dialog({id:"test",content:\'<h2>' . L ( "add_success" ) . '</h2><span style="fotn-size:16px;">' . L ( "following_operation" ) . '</span><br /><ul style="fotn-size:14px;"><li><a href="?app=admin&controller=category&action=public_cache&menuid=128&application=admin" target="right"  onclick="window.top.art.dialog({id:\\\'test\\\'}).close()">' . L ( "following_operation_1" ) . '</a></li><li><a href="' . HTTP_REFERER . '" target="right" onclick="window.top.art.dialog({id:\\\'test\\\'}).close()">' . L ( "following_operation_2" ) . '</a></li></ul>\',width:"400px",height:"200px"});</script>';
			if (! isset ( $_POST ['batch_add'] ) || empty ( $_POST ['batch_add'] )) {
				$_POST ['info'] ['letter'] = strtolower ( string_to_pinyin ( $_POST ['info'] ['catname'] ) );
				$catid = $this->db->insert ( $_POST ['info'], true );
				$this->update_priv ( $catid, $_POST ['priv_roleid'] );
				$this->update_priv ( $catid, $_POST ['priv_groupid'], 0 );
			} else { // 批量添加
				$end_str = '';
				$batch_adds = explode ( "\n", $_POST ['batch_add'] );
				foreach ( $batch_adds as $_v ) {
					if (trim ( $_v ) == '') continue;
					$names = explode ( '|', $_v );
					$catname = $names [0];
					$_POST ['info'] ['catname'] = $names [0];
					$_POST ['info'] ['letter'] = strtolower ( string_to_pinyin ( $catname ) );
					$_POST ['info'] ['catdir'] = trim ( $names [1] ) ? trim ( $names [1] ) : trim ( $_POST ['info'] ['letter'] );
					if (! $this->public_check_catdir ( 0, $_POST ['info'] ['catdir'] )) {
						$end_str .= $end_str ? ',' . $_POST ['info'] ['catname'] . '(' . $_POST ['info'] ['catdir'] . ')' : $_POST ['info'] ['catname'] . '(' . $_POST ['info'] ['catdir'] . ')';
						continue;
					}
					$catid = $this->db->insert ( $_POST ['info'], true );
					$this->update_priv ( $catid, $_POST ['priv_roleid'] );
					$this->update_priv ( $catid, $_POST ['priv_groupid'], 0 );
				}
				$end_str = $end_str ? L ( 'follow_catname_have_exists' ) . $end_str : $old_end;
			}
			$this->cache ();
			showmessage ( L ( 'add_success' ) . $end_str );
		} else {
			Loader::helper ( 'admin:global' );
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			$show_validator = '';
			if (isset ( $_GET ['parentid'] )) {
				$parentid = intval ( $_GET ['parentid'] );
				$r = $this->db->where ( array ('catid' => $parentid ) )->find ();
				if ($r) extract ( $r, EXTR_SKIP );
				$setting = string2array ( $setting );
			}
			$type = $_GET ['s'];
			if ($type == 0) {
				$models = S ( 'common/model' );
				if (count ( $models ) < 1) showmessage ( L ( 'please_add_model' ), U ( 'content/model/init' ), 5000 );
				include $this->admin_tpl ( 'category_add' );
			} elseif ($type == 1) {
				include $this->admin_tpl ( 'category_page_add' );
			} else {
				include $this->admin_tpl ( 'category_link' );
			}
		}
	}

	/**
	 * 修改栏目
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$catid = 0;
			$catid = intval ( $_POST ['catid'] );
			$setting = $_POST ['setting'];
			// 栏目生成静态配置
			if ($_POST ['type'] != 2) {
				if ($setting ['ishtml']) {
					$setting ['category_ruleid'] = $_POST ['category_html_ruleid'];
				} else {
					$setting ['category_ruleid'] = $_POST ['category_php_ruleid'];
					$_POST ['info'] ['url'] = '';
				}
			}
			// 内容生成静态配置
			if ($setting ['content_ishtml']) {
				$setting ['show_ruleid'] = $_POST ['show_html_ruleid'];
			} else {
				$setting ['show_ruleid'] = $_POST ['show_php_ruleid'];
			}
			if ($setting ['repeatchargedays'] < 1) $setting ['repeatchargedays'] = 1;
			$_POST ['info'] ['sethtml'] = isset ( $setting ['create_to_html_root'] ) && ! empty ( $setting ['create_to_html_root'] ) ? intval ( $setting ['create_to_html_root'] ) : 0;
			$_POST ['info'] ['setting'] = array2string ( $setting );
			$_POST ['info'] ['application'] = 'content';
			$_POST ['info'] ['letter'] = strtolower ( string_to_pinyin ( $_POST ['info'] ['catname'] ) );
			// 应用权限设置到子栏目
			if ($_POST ['priv_child']) {
				$arrchildid = $this->db->field ( 'arrchildid' )->where ( array ('catid' => $catid ) )->find ();
				if (! empty ( $arrchildid ['arrchildid'] )) {
					$arrchildid_arr = explode ( ',', $arrchildid ['arrchildid'] );
					if (! empty ( $arrchildid_arr )) {
						foreach ( $arrchildid_arr as $arr_v ) {
							$this->update_priv ( $arr_v, $_POST ['priv_groupid'], 0 );
						}
					}
				}
			}
			// 应用模板到所有子栏目
			if ($_POST ['template_child']) {
				$this->categorys = $categorys = $this->db->where ( array ('application' => 'content' ) )->order ( 'listorder ASC, catid ASC' )->key ( 'catid' )->select ();
				$idstr = $this->get_arrchildid ( $catid );
				if (! empty ( $idstr )) {
					$arr = $this->db->field ( 'catid,setting' )->where ( array ('catid' => array ('IN',$idstr ) ) )->select ();
					if (! empty ( $arr )) {
						foreach ( $arr as $v ) {
							$new_setting = array2string ( array_merge ( string2array ( $v ['setting'] ), array ('category_template' => $_POST ['setting'] ['category_template'],'list_template' => $_POST ['setting'] ['list_template'],'show_template' => $_POST ['setting'] ['show_template'] ) ) );
							$this->db->where ( array ('catid' => $v ['catid'] ) )->update ( array ('setting' => $new_setting ) );
						}
					}
				}
			}
			$this->db->where ( array ('catid' => $catid ) )->update ( $_POST ['info'] );
			$this->update_priv ( $catid, $_POST ['priv_roleid'] );
			$this->update_priv ( $catid, $_POST ['priv_groupid'], 0 );
			$this->cache ();
			// 更新附件状态
			if ($_POST ['info'] ['image'] && C ( 'attachment', 'stat' )) {
				$this->attachment_db = Loader::model ( 'attachment_model' );
				$this->attachment_db->api_update ( $_POST ['info'] ['image'], 'catid-' . $catid, 1 );
			}
			showmessage ( L ( 'operation_success' ) . '<script type="text/javascript">window.top.art.dialog({id:"test",content:\'<h2>' . L ( "operation_success" ) . '</h2><span style="fotn-size:16px;">' . L ( "edit_following_operation" ) . '</span><br /><ul style="fotn-size:14px;"><li><a href="?app=admin&controller=category&action=public_cache&menuid=128" target="right"  onclick="window.top.art.dialog({id:\\\'test\\\'}).close()">' . L ( "following_operation_1" ) . '</a></li></ul>\',width:"400px",height:"200px"});</script>', '?app=admin&controller=category&action=init&menuid=128' );
		} else {
			Loader::helper ( 'admin:global' );
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			$show_validator = $catid = $r = '';
			$catid = intval ( $_GET ['catid'] );
			$r = $this->db->where ( array ('catid' => $catid ) )->find ();
			if ($r) extract ( $r );
			$setting = string2array ( $setting );
			$this->priv_db = Loader::model ( 'category_priv_model' );
			$this->privs = $this->priv_db->select ( array ('catid' => $catid ) );
			$type = $_GET ['type'];
			if ($type == 0) {
				include $this->admin_tpl ( 'category_edit' );
			} elseif ($type == 1) {
				include $this->admin_tpl ( 'category_page_edit' );
			} else {
				include $this->admin_tpl ( 'category_link' );
			}
		}
	}

	/**
	 * 排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $id => $listorder ) {
				$this->db->where ( array ('catid' => $id ) )->update ( array ('listorder' => $listorder ) );
			}
			$this->cache ();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'operation_failure' ) );
		}
	}

	/**
	 * 删除栏目
	 */
	public function delete() {
		$catid = intval ( $_GET ['catid'] );
		$categorys = S ( 'common/category_content' );
		$modelid = $categorys [$catid] ['modelid'];
		$items = S ( 'common/category_items_' . $modelid );
		if ($items [$catid]) showmessage ( L ( 'category_does_not_allow_delete' ) );
		$this->delete_child ( $catid );
		$this->db->where ( array ('catid' => $catid ) )->delete ();
		$this->cache ();
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 递归删除栏目
	 *
	 * @param $catid 要删除的栏目id
	 */
	private function delete_child($catid) {
		$catid = intval ( $catid );
		if (empty ( $catid )) return false;
		$r = $this->db->where ( array ('parentid' => $catid ) )->find ();
		if ($r) {
			$this->delete_child ( $r ['catid'] );
			$this->db->where ( array ('catid' => $r ['catid'] ) )->find ();
		}
		return true;
	}
	public function cache() {
		$categorys = array ();
		$models = S ( 'common/model' );
		foreach ( $models as $modelid => $model ) {
			$datas = $this->db->where ( array ('modelid' => $modelid ) )->field ( 'catid,type,items' )->select ();
			$array = array ();
			foreach ( $datas as $r ) {
				if ($r ['type'] == 0) $array [$r ['catid']] = $r ['items'];
			}
			S ( 'common/category_items_' . $modelid, $array );
		}
		$category_arr = $this->db->where ( array ('application' => 'content' ) )->order ( 'listorder ASC' )->select ();
		foreach ( $category_arr as $r ) {
			unset ( $r ['application'] );
			$setting = string2array ( $r ['setting'] );
			if ($r ['type'] == 0) { // 内容模型
				$r ['create_to_html_root'] = $setting ['create_to_html_root'];
				$r ['content_ishtml'] = $setting ['content_ishtml'];
				$r ['workflowid'] = $setting ['workflowid'];
			}
			$r ['ishtml'] = isset ( $setting ['ishtml'] ) ? $setting ['ishtml'] : 0;
			$r ['category_ruleid'] = isset ( $setting ['category_ruleid'] ) ? $setting ['category_ruleid'] : 0;
			$r ['show_ruleid'] = isset ( $setting ['show_ruleid'] ) ? $setting ['show_ruleid'] : 0;
			$r ['isdomain'] = '0';
			if (! preg_match ( '/^(http|https):\/\//', $r ['url'] )) {
				$r ['url'] = substr ( SITE_URL, 0, - 1 ) . $r ['url'];
			} elseif ($r ['ishtml'] == 1) {
				$r ['isdomain'] = '1';
			}
			$categorys [$r ['catid']] = $r;
		}
		S ( 'common/category_content', $categorys );
		return true;
	}

	/**
	 * 更新缓存并修复栏目
	 */
	public function public_cache() {
		$this->repair ();
		$this->cache ();
		showmessage ( L ( 'operation_success' ), U ( 'admin/category/init' ) );
	}

	/**
	 * 修复栏目数据
	 */
	private function repair() {
		@set_time_limit ( 600 );
		$html_root = C ( 'system', 'html_root' );
		$this->categorys = $categorys = array ();
		$this->categorys = $categorys = $this->db->where ( array ('application' => 'content' ) )->order ( 'listorder ASC, catid ASC' )->key ( 'catid' )->select ();
		$this->get_categorys ( $categorys );
		if (! $this->categorys) return;
		if (is_array ( $this->categorys )) {
			foreach ( $this->categorys as $catid => $cat ) {
				if ($cat ['type'] == 2) continue;
				$arrparentid = $this->get_arrparentid ( $catid );
				$setting = string2array ( $cat ['setting'] );
				$arrchildid = $this->get_arrchildid ( $catid );
				$child = is_numeric ( $arrchildid ) ? 0 : 1;
				if ($categorys [$catid] ['arrparentid'] != $arrparentid || $categorys [$catid] ['arrchildid'] != $arrchildid || $categorys [$catid] ['child'] != $child) {
					$this->db->where(array ('catid' => $catid ) )->update ( array ('arrparentid' => $arrparentid,'arrchildid' => $arrchildid,'child' => $child ));
				}
				$parentdir = $this->get_parentdir ( $catid );
				$letter = strtolower ( string_to_pinyin ( $cat ['catname'] ) );
				$listorder = $cat ['listorder'] ? $cat ['listorder'] : $catid;
				$this->sethtml = $setting ['create_to_html_root'];
				// 检查是否生成到根目录
				$this->get_sethtml ( $catid );
				$sethtml = $this->sethtml ? 1 : 0;

				if ($setting ['ishtml']) {
					// 生成静态时
					$url = $this->update_url ( $catid );
					if (! preg_match ( '/^(http|https):\/\//i', $url )) {
						$url = $sethtml ? '/' . $url : $html_root . '/' . $url;
					}
				} else {
					// 不生成静态时
					$url = $this->update_url ( $catid );
					$url = SITE_URL . $url;
				}
				if ($cat ['url'] != $url) {
					$this->db->where ( array ('catid' => $catid ) )->update ( array ('url' => $url ) );
				}
				if ($categorys [$catid] ['parentdir'] != $parentdir || $categorys [$catid] ['sethtml'] != $sethtml || $categorys [$catid] ['letter'] != $letter || $categorys [$catid] ['listorder'] != $listorder) {
					$this->db->where(array ('catid' => $catid ))->update ( array ('parentdir' => $parentdir,'sethtml' => $sethtml,'letter' => $letter,'listorder' => $listorder ) );
				}
			}
		}

		// 删除在非正常显示的栏目
		foreach ( $this->categorys as $catid => $cat ) {
			if ($cat ['parentid'] != 0 && ! isset ( $this->categorys [$cat ['parentid']] )) {
				$this->db->where ( array ('catid' => $catid ) )->delete ();
			}
		}
		return true;
	}

	/**
	 * 获取父栏目是否生成到根目录
	 */
	private function get_sethtml($catid) {
		foreach ( $this->categorys as $id => $cat ) {
			if ($catid == $id) {
				$parentid = $cat ['parentid'];
				if ($this->categorys [$parentid] ['sethtml']) {
					$this->sethtml = 1;
				}
				if ($parentid) {
					$this->get_sethtml ( $parentid );
				}
			}
		}
	}

	/**
	 * 找出子目录列表
	 *
	 * @param array $categorys
	 */
	private function get_categorys($categorys = array()) {
		if (is_array ( $categorys ) && ! empty ( $categorys )) {
			foreach ( $categorys as $catid => $c ) {
				$this->categorys [$catid] = $c;
				$result = array ();
				foreach ( $this->categorys as $_k => $_v ) {
					if ($_v ['parentid']) $result [] = $_v;
				}
				$this->get_categorys ( $r );
			}
		}
		return true;
	}

	/**
	 * 更新栏目链接地址
	 */
	private function update_url($catid) {
		$catid = intval ( $catid );
		if (! $catid) return false;
		$url = Loader::lib ( 'content:url' ); // 调用URL实例
		return $url->category_url ( $catid );
	}

	/**
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 *
	 * 获取父栏目ID列表
	 *
	 * @param integer $catid
	 *        	栏目ID
	 * @param array $arrparentid
	 *        	父目录ID
	 * @param integer $n
	 *        	查找的层次
	 */
	private function get_arrparentid($catid, $arrparentid = '', $n = 1) {
		if ($n > 5 || ! is_array ( $this->categorys ) || ! isset ( $this->categorys [$catid] )) return false;
		$parentid = $this->categorys [$catid] ['parentid'];
		$arrparentid = $arrparentid ? $parentid . ',' . $arrparentid : $parentid;
		if ($parentid) {
			$arrparentid = $this->get_arrparentid ( $parentid, $arrparentid, ++ $n );
		} else {
			$this->categorys [$catid] ['arrparentid'] = $arrparentid;
		}
		$parentid = $this->categorys [$catid] ['parentid'];
		return $arrparentid;
	}

	/**
	 * 获取子栏目ID列表
	 *
	 * @param $catid 栏目ID
	 */
	private function get_arrchildid($catid) {
		$arrchildid = $catid;
		if (is_array ( $this->categorys )) {
			foreach ( $this->categorys as $id => $cat ) {
				if ($cat ['parentid'] && $id != $catid && $cat ['parentid'] == $catid) {
					$arrchildid .= ',' . $this->get_arrchildid ( $id );
				}
			}
		}
		return $arrchildid;
	}

	/**
	 * 获取父栏目路径
	 *
	 * @param
	 *        	$catid
	 */
	function get_parentdir($catid) {
		if ($this->categorys [$catid] ['parentid'] == 0) return '';
		$r = $this->categorys [$catid];
		$setting = string2array ( $r ['setting'] );
		$url = $r ['url'];
		$arrparentid = $r ['arrparentid'];
		unset ( $r );
		if (strpos ( $url, '://' ) === false) {
			if ($setting ['creat_to_html_root']) {
				return '';
			} else {
				$arrparentid = explode ( ',', $arrparentid );
				$arrcatdir = array ();
				foreach ( $arrparentid as $id ) {
					if ($id == 0) continue;
					$arrcatdir [] = $this->categorys [$id] ['catdir'];
				}
				return implode ( '/', $arrcatdir ) . '/';
			}
		} else {
			if ($setting ['create_to_html_root']) {
				if (preg_match ( '/^((http|https):\/\/)?([^\/]+)/i', $url, $matches )) {
					$url = $matches [0] . '/';
					$rs = $this->db->where ( array ('url' => $url ) )->field ( 'parentdir,catid' )->find ();
					if ($catid == $rs ['catid'])
						return '';
					else
						return $rs ['parentdir'];
				} else {
					return '';
				}
			} else {
				$arrparentid = explode ( ',', $arrparentid );
				$arrcatdir = array ();
				krsort ( $arrparentid );
				foreach ( $arrparentid as $id ) {
					if ($id == 0) continue;
					$arrcatdir [] = $this->categorys [$id] ['catdir'];
					if ($this->categorys [$id] ['parentdir'] == '') break;
				}
				krsort ( $arrcatdir );
				return implode ( '/', $arrcatdir ) . '/';
			}
		}
	}

	/**
	 * 检查目录是否存在
	 *
	 * @param $return_method 返回方法
	 * @param $catdir 目录
	 */
	public function public_check_catdir($return_method = 1, $catdir = '') {
		$old_dir = '';
		$catdir = $catdir ? $catdir : $_GET ['catdir'];
		$old_dir = isset ( $_GET ['old_dir'] ) ? $_GET ['old_dir'] : '';
		$r = $this->db->where ( array ('application' => 'content','catdir' => $catdir ) )->find ();
		if ($r && $old_dir != $r ['catdir']) {
			if ($return_method)
				exit ( '0' );
			else
				return false;
		} else {
			if ($return_method)
				exit ( '1' );
			else
				return true;
		}
	}

	/**
	 * 更新权限
	 *
	 * @param
	 *        	$catid
	 * @param
	 *        	$priv_datas
	 * @param
	 *        	$is_admin
	 */
	private function update_priv($catid, $priv_datas, $is_admin = 1) {
		$this->priv_db = Loader::model ( 'category_priv_model' );
		$this->priv_db->where ( array ('catid' => $catid,'is_admin' => $is_admin ) )->delete ();
		if (is_array ( $priv_datas ) && ! empty ( $priv_datas )) {
			foreach ( $priv_datas as $r ) {
				$r = explode ( ',', $r );
				$action = $r [0];
				$roleid = $r [1];
				$this->priv_db->insert ( array ('catid' => $catid,'roleid' => $roleid,'is_admin' => $is_admin,'action' => $action ) );
			}
		}
	}

	/**
	 * 检查栏目权限
	 *
	 * @param $action 动作
	 * @param $roleid 角色
	 * @param $is_admin 是否为管理组
	 */
	private function check_category_priv($action, $roleid, $is_admin = 1) {
		$checked = '';
		foreach ( $this->privs as $priv ) {
			if ($priv ['is_admin'] == $is_admin && $priv ['roleid'] == $roleid && $priv ['action'] == $action) $checked = 'checked';
		}
		return $checked;
	}

	/**
	 * 重新统计栏目信息数量
	 */
	public function count_items() {
		$this->content_db = Loader::model ( 'content_model' );
		$result = S ( 'common/category_content' );
		foreach ( $result as $r ) {
			if ($r ['type'] == 0) {
				$modelid = $r ['modelid'];
				$this->content_db->set_model ( $modelid );
				$number = $this->content_db->where ( array ('catid' => $r ['catid'] ) )->count ();
				$this->db->where ( array ('catid' => $r ['catid'] ) )->update ( array ('items' => $number ) );
			}
		}
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * json方式加载模板
	 */
	public function public_tpl_file_list() {
		$style = isset ( $_GET ['style'] ) && trim ( $_GET ['style'] ) ? trim ( $_GET ['style'] ) : exit ( 0 );
		$catid = isset ( $_GET ['catid'] ) && intval ( $_GET ['catid'] ) ? intval ( $_GET ['catid'] ) : 0;
		$batch_str = isset ( $_GET ['batch_str'] ) ? '[' . $catid . ']' : '';
		if ($catid) {
			$cat = S ( 'common/category_content' );
			$cat = $cat [$catid];
			$cat ['setting'] = string2array ( $cat ['setting'] );
		}
		if (isset ( $_GET ['type'] ) && $_GET ['type'] == 1) {
			$html = array ('page_template' => Form::select_template ( $style, 'content', (isset ( $cat ['setting'] ['page_template'] ) && ! empty ( $cat ['setting'] ['page_template'] ) ? $cat ['setting'] ['page_template'] : 'category'), 'name="setting' . $batch_str . '[page_template]"', 'page' ) );
		} else {
			$html = array (
					'category_template' => Form::select_template ( $style, 'content', (isset ( $cat ['setting'] ['category_template'] ) && ! empty ( $cat ['setting'] ['category_template'] ) ? $cat ['setting'] ['category_template'] : 'category'), 'name="setting' . $batch_str . '[category_template]"', 'category' ),
					'list_template' => Form::select_template ( $style, 'content', (isset ( $cat ['setting'] ['list_template'] ) && ! empty ( $cat ['setting'] ['list_template'] ) ? $cat ['setting'] ['list_template'] : 'list'), 'name="setting' . $batch_str . '[list_template]"', 'list' ),
					'show_template' => Form::select_template ( $style, 'content', (isset ( $cat ['setting'] ['show_template'] ) && ! empty ( $cat ['setting'] ['show_template'] ) ? $cat ['setting'] ['show_template'] : 'show'), 'name="setting' . $batch_str . '[show_template]"', 'show' ) );
		}
		if (isset ( $_GET ['application'] )) {
			unset ( $html );
			if ($_GET ['templates']) {
				$templates = explode ( '|', $_GET ['templates'] );
				if ($_GET ['id']) $id = explode ( '|', $_GET ['id'] );
				if (is_array ( $templates )) {
					foreach ( $templates as $k => $tem ) {
						$t = $tem . '_template';
						if ($id [$k] == '') $id [$k] = $tem;
						$html [$t] = Form::select_template ( $style, $_GET ['application'], $id [$k], 'name="' . $_GET ['name'] . '[' . $t . ']" id="' . $t . '"', $tem );
					}
				}
			}
		}
		if (CHARSET == 'gbk') $html = array_iconv ( $html, 'gbk', 'utf-8' );
		echo json_encode ( $html );
	}

	/**
	 * 快速进入搜索
	 */
	public function public_ajax_search() {
		if ($_GET ['catname']) {
			$where = array ('child' => '0' );
			if (preg_match ( '/([a-z]+)/i', $_GET ['catname'] )) {
				$catname = strtolower ( trim ( $_GET ['catname'] ) );
				$where ['letter'] = array ('like',"%$catname%" );
			} else {
				$catname = trim ( $_GET ['catname'] );
				if (CHARSET == 'gbk') $catname = iconv ( 'utf-8', 'gbk', $catname );
				$where ['catname'] = array ('like',"%$catname%" );
			}
			$result = $this->db->where ( $where )->field ( 'catid,type,catname,letter' )->select ();
			if (CHARSET == 'gbk') {
				$result = array_iconv ( $result, 'gbk', 'utf-8' );
			}
			echo json_encode ( $result );
		}
	}

	/**
	 * json方式读取风格列表，推送部分调用
	 */
	public function public_change_tpl() {
		$models = S ( 'common/model' );
		$modelid = intval ( $_GET ['modelid'] );
		if ($_GET ['modelid']) {
			$style = $models [$modelid] ['default_style'];
			$category_template = $models [$modelid] ['category_template'];
			$list_template = $models [$modelid] ['list_template'];
			$show_template = $models [$modelid] ['show_template'];
			$html = array ('template_list' => $style,'category_template' => Form::select_template ( $style, 'content', $category_template, 'name="setting[category_template]"', 'category' ),
					'list_template' => Form::select_template ( $style, 'content', $list_template, 'name="setting[list_template]"', 'list' ),'show_template' => Form::select_template ( $style, 'content', $show_template, 'name="setting[show_template]"', 'show' ) );
			if (CHARSET == 'gbk') $html = array_iconv ( $html, 'gbk', 'utf-8' );
			echo json_encode ( $html );
		}
	}

	/**
	 * 批量修改
	 */
	public function batch_edit() {
		$categorys = S ( 'common/category_content' );
		if (isset ( $_POST ['dosubmit'] )) {
			$catid = intval ( $_POST ['catid'] );
			$post_setting = $_POST ['setting'];
			// 栏目生成静态配置
			$infos = $info = array ();
			$infos = $_POST ['info'];
			if (empty ( $infos )) showmessage ( L ( 'operation_success' ) );
			$this->attachment_db = Loader::model ( 'attachment_model' );
			foreach ( $infos as $catid => $info ) {
				$setting = string2array ( $categorys [$catid] ['setting'] );
				if ($_POST ['type'] != 2) {
					if ($post_setting [$catid] ['ishtml']) {
						$setting ['category_ruleid'] = $_POST ['category_html_ruleid'] [$catid];
					} else {
						$setting ['category_ruleid'] = $_POST ['category_php_ruleid'] [$catid];
						$info ['url'] = '';
					}
				}
				foreach ( $post_setting [$catid] as $_k => $_setting ) {
					$setting [$_k] = $_setting;
				}
				// 内容生成静态配置
				if ($post_setting [$catid] ['content_ishtml']) {
					$setting ['show_ruleid'] = $_POST ['show_html_ruleid'] [$catid];
				} else {
					$setting ['show_ruleid'] = $_POST ['show_php_ruleid'] [$catid];
				}
				if ($setting ['repeatchargedays'] < 1) $setting ['repeatchargedays'] = 1;
				$info ['sethtml'] = isset ( $post_setting [$catid] ['create_to_html_root'] ) && ! empty ( $post_setting [$catid] ['create_to_html_root'] ) ? $post_setting [$catid] ['create_to_html_root'] : 0;
				$info ['setting'] = array2string ( $setting );

				$info ['application'] = 'content';
				$info ['letter'] = strtolower ( string_to_pinyin ( $info ['catname'] ) );
				$this->db->where ( array ('catid' => $catid ) )->update ( $info );

				// 更新附件状态
				if ($info ['image'] && C ( 'attachment', 'stat' )) {
					$this->attachment_db->api_update ( $info ['image'], 'catid-' . $catid, 1 );
				}
			}
			$this->public_cache ();
			showmessage ( L ( 'operation_success' ), U ( 'admin/category/init' ) );
		} else {
			if (isset ( $_POST ['catids'] )) {
				$template_list = template_list ( 0 );
				foreach ( $template_list as $k => $v ) {
					$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
					unset ( $template_list [$k] );
				}
				$show_validator = '';
				$catid = intval ( $_GET ['catid'] );
				$type = isset ( $_POST ['type'] ) ? intval ( $_POST ['type'] ) : 0;
				if (empty ( $_POST ['catids'] )) showmessage ( L ( 'illegal_parameters' ) );
				$batch_array = $workflows = array ();
				foreach ( $categorys as $catid => $cat ) {
					if ($cat ['type'] == $type && in_array ( $catid, $_POST ['catids'] )) {
						$batch_array [$catid] = $cat;
					}
				}
				if (empty ( $batch_array )) showmessage ( L ( 'please_select_category' ) );
				$workflows = S ( 'common/workflow' );
				if ($workflows) {
					$workflows_datas = array ();
					foreach ( $workflows as $_k => $_v ) {
						$workflows_datas [$_v ['workflowid']] = $_v ['workname'];
					}
				}
				$show_header = true;
				if ($type == 1)
					include $this->admin_tpl ( 'category_batch_edit_page' );
				else
					include $this->admin_tpl ( 'category_batch_edit' );
			} else {
				$type = isset ( $_GET ['select_type'] ) ? intval ( $_GET ['select_type'] ) : 0;
				$tree = Loader::lib ( 'Tree' );
				$tree->icon = array ('&nbsp;&nbsp;│ ','&nbsp;&nbsp;├─ ','&nbsp;&nbsp;└─ ' );
				$tree->nbsp = '&nbsp;&nbsp;';
				$category = array ();
				foreach ( $categorys as $catid => $r ) {
					if ($r ['type'] == 2 && $r ['child'] == 0) continue;
					$category [$catid] = $r;
				}
				$str = "<option value='\$catid' \$selected>\$spacer \$catname</option>";
				$tree->init ( $category );
				$string .= $tree->get_tree ( 0, $str );
				include $this->admin_tpl ( 'category_batch_select' );
			}
		}
	}

	/**
	 * 批量移动文章
	 */
	public function remove() {
		$this->categorys = S ( 'common/category_content' );
		$this->content_db = Loader::model ( 'content_model' );
		if (isset ( $_POST ['dosubmit'] )) {
			$this->content_check_db = Loader::model ( 'content_check_model' );
			if (! $_POST ['fromid']) showmessage ( L ( 'please_input_move_source', '', 'content' ) );
			if (! $_POST ['tocatid']) showmessage ( L ( 'please_select_target_category', '', 'content' ) );
			$tocatid = intval ( $_POST ['tocatid'] );
			$modelid = $this->categorys [$tocatid] ['modelid'];
			if (! $modelid) showmessage ( L ( 'illegal_operation', '', 'content' ) );
			$fromid = array_filter ( $_POST ['fromid'], "intval" );
			$fromid = implode ( ',', $fromid );
			$this->content_db->set_model ( $modelid );
			$this->content_db->update ( array ('catid' => $tocatid ), "catid IN($fromid)" );
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$show_header = '';
			$catid = intval ( $_GET ['catid'] );
			$categorys = array ();
			$modelid = $this->categorys [$catid] ['modelid'];
			$tree = Loader::lib ( 'Tree' );
			$tree->icon = array ('&nbsp;&nbsp;│ ','&nbsp;&nbsp;├─ ','&nbsp;&nbsp;└─ ' );
			$tree->nbsp = '&nbsp;&nbsp;';
			foreach ( $this->categorys as $cid => $r ) {
				if ($modelid && $modelid != $r ['modelid']) continue;
				$r ['disabled'] = $r ['child'] ? 'disabled' : '';
				$r ['selected'] = $cid == $catid ? 'selected' : '';
				$categorys [$cid] = $r;
			}
			$str = "<option value='\$catid' \$disabled>\$spacer \$catname</option>";
			$tree->init ( $categorys );
			$string .= $tree->get_tree ( 0, $str );

			$str = "<option value='\$catid' \$selected>\$spacer \$catname</option>";
			$source_string = '';
			$tree->init ( $categorys );
			$source_string .= $tree->get_tree ( 0, $str );
			include $this->admin_tpl ( 'category_remove' );
		}
	}
}