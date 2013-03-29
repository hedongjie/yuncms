<?php
/**
 * 更新缓存
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class Cache_allController extends admin {
	private $cache_api;

	public function init() {
		if (isset ( $_POST ['dosubmit'] ) || isset ( $_GET ['dosubmit'] )) {
			$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 0;
			$apps = array (
					array ('name' => L ( 'application' ),'function' => 'application' ),
					array ('name' => L ( 'category' ),'function' => 'category' ),
					array ('name' => L ( 'downserver' ),'function' => 'downserver' ),
					array ('name' => L ( 'badword_name' ),'function' => 'badword' ),
					array ('name' => L ( 'ipbanned' ),'function' => 'ipbanned' ),
					array ('name' => L ( 'keylink' ),'function' => 'keylink' ),
					array ('name' => L ( 'linkage' ),'function' => 'linkage' ),
					//array ('name' => L ( 'area' ),'function' => 'area' ),
					//array ('name' => L ( 'player' ),'function' => 'player' ),
					array ('name' => L ( 'position' ),'function' => 'position' ),
					array ('name' => L ( 'admin_role' ),'function' => 'admin_role' ),
					array ('name' => L ( 'urlrule' ),'function' => 'urlrule' ),
					array ('name' => L ( 'model' ),'function' => 'model' ),
					array ('name' => L ( 'type' ),'function' => 'type','param' => 'content' ),
					array ('name' => L ( 'workflow' ),'function' => 'workflow' ),
					array ('name' => L ( 'dbsource' ),'function' => 'dbsource' ),
					//array ('name' => L ( 'member_setting' ),'function' => 'member_setting' ),
					array ('name' => L ( 'member_group' ),'function' => 'member_group' ),
					//array ('name' => L ( 'membermodel' ),'function' => 'member_model' ),
					//array ('name' => L ( 'member_model_field' ),'function' => 'member_model_field' ),
					array ('name' => L ( 'search_type' ),'function' => 'type','param' => 'search' ),
					array ('name' => L ( 'search_setting' ),'function' => 'search_setting' ),
					array ('name' => L ( 'update_vote_setting' ),'function' => 'vote_setting' ),
					array ('name' => L ( 'update_link_setting' ),'function' => 'link_setting' ),
					//array('name' => L('special'), 'function' => 'special'),
					array ('name' => L ( 'setting' ),'function' => 'setting' ),
					array ('name' => L ( 'database' ),'function' => 'database' ),
					//array('name' => L('update_formguide_model'), 'mod' =>'formguide', 'file' => 'formguide', 'function' =>'public_cache'),
					array ('name' => L ( 'update_cache', '', 'admin' ),'function' => 'category_cache','target' => 'iframe','link' => 'app=admin&controller=category&action=public_cache&application=admin' ),
					//array('name' => L('cache_file'), 'function' => 'cache2database'),
					array ('name' => L ( 'cache_copyfrom' ),'function' => 'copyfrom' ),
					array ('name' => L ( 'clear_files' ),'function' => 'del_file' )
			 );
			$this->cache_api = Loader::lib ( 'admin:cache_api' );
			$m = $apps [$page];
			if (isset ( $m ['mod'] ) && $m ['function']) {
				if ($m ['file'] == '') $m ['file'] = $m ['function'];
				$APP = S ( 'common/application' );
				if (in_array ( $m ['mod'], array_keys ( $APP ) )) {
					$cache = Loader::lib ( $m ['mod'] . ':' . $m ['file'] );
					$cache->$m ['function'] ();
				}
			} else if (isset ( $m ['target'] ) && $m ['target'] == 'iframe') {
				$str = '<script type="text/javascript">window.parent.frames["hidden"].location="index.php?';
				$str .= $m ['link'];
				$str .= '";</script>';
				echo $str;
			} else {
				$this->cache_api->cache ( $m ['function'], isset ( $m ['param'] ) ? $m ['param'] : '' );
			}
			$page ++;
			if (! empty ( $apps [$page] )) {
				echo '<script type="text/javascript">window.parent.addtext("<li>' . L ( 'update' ) . $m ['name'] . L ( 'cache_file_success' ) . '..........</li>");</script>';
				showmessage ( L ( 'update' ) . $m ['name'] . L ( 'cache_file_success' ), U ( 'admin/cache_all/init', array ('page' => $page,'dosubmit' => '1' ) ), 0 );
			} else {
				echo '<script type="text/javascript">window.parent.addtext("<li>' . L ( 'update' ) . $m ['name'] . L ( 'site_cache_success' ) . '..........</li>")</script>';
				showmessage ( L ( 'update' ) . $m ['name'] . L ( 'site_cache_success' ), 'blank' );
			}
		} else {
			include $this->admin_tpl ( 'cache_all' );
		}
	}
}