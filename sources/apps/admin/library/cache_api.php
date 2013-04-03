<?php
/**
 * 缓存更新操作API
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class cache_api {

	private $db;

	public function __construct() {
		$this->db = '';
	}

	/**
	 * 更新缓存
	 *
	 * @param string $model 方法名
	 * @param string $param 参数
	 */
	public function cache($model = '', $param = '') {
		if (file_exists ( SOURCE_PATH . 'model' . DIRECTORY_SEPARATOR . $model . '_model.php' )) {
			$this->db = Loader::model ( $model . '_model' );
			if ($param) {
				$this->$model ( $param );
			} else {
				$this->$model ();
			}
		} else {
			$this->$model ();
		}
	}

	/**
	 * 更新应用缓存方法
	 */
	public function application() {
		$apps = array ();
		$apps = $this->db->where(array ('disabled' => 0 ))->key('application' )->select ();
		S ( 'common/application', $apps );
		return true;
	}

	/**
	 * 更新栏目缓存方法
	 */
	public function category() {
		$categorys = array ();
		$models = S ( 'common/model' );
		foreach ( $models as $modelid => $model ) {
			$datas = $this->db->where(array ('modelid' => $modelid ))->field('catid,type,items')->select ();
			$array = array ();
			foreach ( $datas as $r ) {
				if ($r ['type'] == 0) $array [$r ['catid']] = $r ['items'];
			}
			S ( 'common/category_items_' . $modelid, $array );
		}
		$category_arr = $this->db->where(array ('application' => 'content' ))->order('listorder ASC')->select ( );
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
	 * 更新下载服务器缓存方法
	 */
	public function downserver() {
		$servers = $this->db->key('id')->select();
		S('common/downservers', $servers);
		return true;
	}

	/**
	 * 更新敏感词缓存方法
	 */
	public function badword() {
		$infos = $this->db->field ( 'badid,badword' )->order ( 'badid ASC' )->select ();
		S ( 'common/badword', $infos );
		return true;
	}

	/**
	 * 更新ip禁止缓存方法
	 */
	public function ipbanned() {
		$infos = $this->db->field('ip,expires')->order('ipbannedid desc')->select();
		S('common/ipbanned', $infos);
		return true;
	}

	/**
	 * 更新关联链接缓存方法
	 */
	public function keylink() {
		$infos = $this->db->field('word,url')->order('keylinkid ASC')->select();
		$datas = array();
		if($infos && is_array($infos)){
			foreach($infos as $r) {
				$datas[] = array(0=>$r['word'],1=>$r['url']);
			}
		}
		S('common/keylink', $datas);
		return true;
	}

	/**
	 * 更新地区缓存方法
	 */
	public function area() {
		$infos = $this->db->order ( 'areaid ASC' )->key ( 'areaid' )->select ();
		S ( 'common/area', $infos );
		return true;
	}

	/**
	 * 更新播放器缓存方法
	 */
	public function player() {
		$infos = $this->db->order ( 'playerid ASC' )->key ( 'playerid' )->select ( '', '*', '', 'playerid ASC' );
		S ( 'common/player', $infos );
		return true;
	}

	/**
	 * 更新联动菜单缓存方法
	 */
	public function linkage() {
		$infos = $this->db->where ( array ('keyid' => 0 ) )->select ();
		foreach ( $infos as $r ) {
			$linkageid = intval ( $r ['linkageid'] );
			$r = $this->db->field ( 'name,style' )->where ( array ('linkageid' => $linkageid ) )->find ();
			$info ['title'] = $r ['name'];
			$info ['style'] = $r ['style'];
			$info ['data'] = $this->submenulist ( $linkageid );
			S ( 'linkage/' . $linkageid, $info );
		}
		return true;
	}

	/**
	 * 子菜单列表 @param intval $keyid 菜单id
	 */
	public function submenulist($keyid = 0) {
		$keyid = intval ( $keyid );
		$datas = array ();
		$where = ($keyid > 0) ? array ('keyid' => $keyid ) : '';
		$result = $this->db->where ( $where )->order ( 'listorder ,linkageid' )->select ();
		foreach ( $result as $r ) {
			$datas [$r ['linkageid']] = $r;
		}
		return $datas;
	}

	/**
	 * 更新推荐位缓存方法
	 */
	public function position() {
		$infos = $this->db->order ( 'listorder DESC' )->key ( 'posid' )->select ();
		S ( 'common/position', $infos );
		return $infos;
	}

	/**
	 * 更新管理员角色缓存方法
	 */
	public function admin_role() {
		$infos = $this->db->field ( 'roleid,rolename' )->where ( array ('disabled' => '0' ) )->order ( 'roleid ASC' )->key ( 'roleid' )->select ();
		$role = array ();
		foreach ( $infos as $info ) {
			$role [$info ['roleid']] = $info ['rolename'];
		}
		S ( 'common/role', $role );
		return $infos;
	}

	/**
	 * 更新url规则缓存方法
	 */
	public function urlrule() {
		$datas = $this->db->key ( 'urlruleid' )->select ();
		$basic_data = array ();
		foreach ( $datas as $roleid => $r ) {
			$basic_data [$roleid] = $r ['urlrule'];
		}
		S ( 'common/urlrule_detail', $datas );
		S ( 'common/urlrule', $basic_data );
	}

	/**
	 * 更新模型缓存方法
	 */
	public function model() {
		define ( 'MODEL_PATH', APPS_PATH . 'content' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
		define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
		require MODEL_PATH . 'fields.inc.php';
		$classtypes = array ('form','input','update','output' );
		foreach ( $classtypes as $classtype ) {
			$cache_data = file_get_contents ( MODEL_PATH . 'content_' . $classtype . '.php' );
			$cache_data = str_replace ( '}?>', '', $cache_data );
			foreach ( $fields as $field => $fieldvalue ) {
				if (file_exists ( MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' )) {
					$cache_data_info = file_get_contents ( MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' );
					$cache_data .= substr_between($cache_data_info,'<?php','?>');
				}
			}
			$cache_data .= "\r\n } \r\n?>";
			File::write ( CACHE_MODEL_PATH . 'content_' . $classtype . '.php', $cache_data );
		}
		$model_array = array ();
		$datas = $this->db->where( array ('type' => 0 ))->select ( );
		foreach ( $datas as $r ) {
			$model_array [$r ['modelid']] = $r;
			$this->model_field ( $r ['modelid'] );
		}
		S ( 'common/model', $model_array );
		return true;
	}

	/**
	 * 更新模型字段缓存方法
	 */
	public function model_field($modelid) {
		$field_array = array ();
		$db = Loader::model ( 'model_field_model' );
		$fields = $db->where(array ('modelid' => $modelid,'disabled' => 0 ))->order('listorder ASC')->select ();
		foreach ( $fields as $_value ) {
			$setting = string2array ( $_value ['setting'] );
			$_value = array_merge ( $_value, $setting );
			$field_array [$_value ['field']] = $_value;
		}
		S ( 'model/model_field_' . $modelid, $field_array );
		return true;
	}

	/**
	 * 更新类别缓存方法
	 */
	public function type($param = '') {
		$datas = array ();
		$result_datas = $this->db->where(array ('application' => $param ))->order('listorder ASC,typeid ASC')->select ();
		foreach ( $result_datas as $_key => $_value )
			$datas [$_value ['typeid']] = $_value;
		if ($param == 'search')
			$this->search_type ();
		else
			S ( 'common/type_' . $param, $datas );
		return true;
	}

	/**
	 * 更新投票配置
	 */
	public function vote_setting() {
		$setting = Loader::model ( 'application_model' )->get_setting('vote');
		S ( 'common/vote', $setting );
	}

	/**
	 * 更新友情链接配置
	 */
	public function link_setting() {
		$setting = Loader::model ( 'application_model' )->get_setting ('link');
		S ( 'common/link', $setting );
	}

	/**
	 * 更新工作流缓存方法
	 */
	public function workflow() {
		$datas = array ();
		$workflow_datas = $this->db->select ();
		foreach ( $workflow_datas as $_k => $_v )
			$datas [$_v ['workflowid']] = $_v;
		S ( 'common/workflow', $datas );
		return true;
	}

	/**
	 * 更新数据源缓存方法
	 */
	public function dbsource() {
		$list = $this->db->select ();
		$data = array ();
		if ($list) {
			foreach ( $list as $val ) {
				$data [$val ['name']] = array ('hostname' => $val ['host'] ,'port'=>$val ['port'],'driver'=>$val ['driver'],'database' => $val ['dbname'],'username' => $val ['username'],'password' => $val ['password'],'charset' => $val ['charset'],'prefix' => $val ['dbtablepre'],'pconnect' => false,'autoconnect' => true );
			}
		} else
			return false;
		return S ( 'common/dbsource', $data );
	}

	/**
	 * 更新会员组缓存方法
	 */
	public function member_group() {
		$grouplist = $this->db->key('groupid')->listinfo ();
		S ( 'member/grouplist', $grouplist );
		return true;
	}

	/**
	 * 更新会员配置缓存方法
	 */
	public function member_setting() {
		$setting = Loader::model ( 'application_model' )->get_setting ('member');
		S ( 'member/member_setting', $setting );
		return true;
	}

	/**
	 * 更新会员模型缓存方法
	 */
	public function member_model() {
		define ( 'MEMBER_MODEL_PATH', APPS_PATH . 'member' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
		// 模型缓存路径
		define ( 'MEMBER_CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
		$model_db = Loader::model ( 'model_model' );
		$datas = $model_db->where(array ('type' => 2 ))->key('modelid')->order('sort ASC')->select ( );
		$models = array ();
		foreach ( $datas as $data ) {
			$models [$data ['modelid']] = $data;
		}
		S ( 'common/member_model', $models );
		require MEMBER_MODEL_PATH . 'fields.inc.php';
		// 更新内容模型类：表单生成、入库、更新、输出
		$classtypes = array ('form','input','update','output' );

		foreach ( $classtypes as $classtype ) {
			$cache_data = file_get_contents ( MEMBER_MODEL_PATH . 'member_' . $classtype . '.php' );
			$cache_data = str_replace ( '}?>', '', $cache_data );
			foreach ( $fields as $field => $fieldvalue ) {
				if (file_exists ( MEMBER_MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' )) {
					$cache_data .= file_get_contents ( MEMBER_MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' );
				}
			}
			$cache_data .= "\r\n } \r\n?>";
			File::write ( MEMBER_CACHE_MODEL_PATH . 'member_' . $classtype . '.php', $cache_data );
		}
		return true;
	}

	/**
	 * 更新会员模型字段缓存方法
	 */
	public function member_model_field() {
		$member_model = S ( 'common/member_model' );
		$this->db = Loader::model ( 'model_field_model' );
		if (is_array ( $member_model )) {
			foreach ( $member_model as $modelid => $m ) {
				$field_array = array ();
				$fields = $this->db->where(array ('modelid' => $modelid,'disabled' => 0 ))->order('listorder ASC')->select ( );
				foreach ( $fields as $_value ) {
					$setting = string2array ( $_value ['setting'] );
					$_value = array_merge ( $_value, $setting );
					$field_array [$_value ['field']] = $_value;
				}
				S ( 'model/member_field_' . $modelid, $field_array );
			}
		}
		return true;
	}

	/**
	 * 更新搜索配置缓存方法
	 */
	public function search_setting() {
		$setting = $this->db = Loader::model ( 'application_model' )->get_setting('search' );
		S ( 'search/search', $setting );
		return true;
	}

	/**
	 * 更新搜索类型缓存方法
	 */
	public function search_type() {
		$datas = $search_model = array ();
		$result_datas = $result_datas2 = $this->db->where(array ('application' => 'search' ))->order( 'listorder ASC')->select ();
		foreach ( $result_datas as $_key => $_value ) {
			if (! $_value ['modelid']) continue;
			$datas [$_value ['modelid']] = $_value ['typeid'];
			$search_model [$_value ['modelid']] ['typeid'] = $_value ['typeid'];
			$search_model [$_value ['modelid']] ['name'] = $_value ['name'];
			$search_model [$_value ['modelid']] ['sort'] = $_value ['listorder'];
		}
		S ( 'search/type_model', $datas );
		$datas = array ();
		foreach ( $result_datas2 as $_key => $_value ) {
			if ($_value ['modelid']) continue;
			$datas [$_value ['typedir']] = $_value ['typeid'];
			$search_model [$_value ['typedir']] ['typeid'] = $_value ['typeid'];
			$search_model [$_value ['typedir']] ['name'] = $_value ['name'];
		}
		S ( 'search/type_application', $datas );
		// 搜索header头中使用类型缓存
		S ( 'search/search_model', $search_model );
		return true;
	}

	/**
	 * 更新专题缓存方法
	 */
	public function special() {
		$specials = $this->db->where(array ('disabled' => 0 ))->field('id, title, url, thumb, banner, ishtml')->order('listorder DESC, id DESC')->key('id')->select ( );
		S ( 'common/special', $specials );
		return true;
	}

	/**
	 * 更新数据源模型缓存方法
	 */
	public function database() {
		$application = $APP = array ();
		$APP = S ( 'common/application' );
		if (is_array ( $APP )) {
			foreach ( $APP as $key => $A ) {
				if (file_exists ( APPS_PATH . $key . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . $key . '_tag.php' ) && ! in_array ( $key, array ('message','block' ) )) {
					$application [$key] = $A ['name'];
				}
			}
		}
		$filepath = SOURCE_PATH . 'config' . DIRECTORY_SEPARATOR;
		$application = "<?php\nreturn " . var_export ( $application, true ) . ";\n?>";
		return $file_size = C ( 'framework', 'lock_ex' ) ? file_put_contents ( $filepath . 'application.php', $application, LOCK_EX ) : file_put_contents ( $filepath . 'application.php', $application );
	}

	/**
	 * 根据数据库记录更新缓存
	 */
	public function cache2database() {
		$cache = Loader::model ( 'cache_model' );
		if (! isset ( $_GET ['pages'] ) && empty ( $_GET ['pages'] )) {
			$r = $cache->get_one ( array (), 'COUNT(*) AS num' );
			if ($r ['num']) {
				$total = $r ['num'];
				$pages = ceil ( $total / 20 );
			} else {
				$pages = 1;
			}
		} else {
			$pages = intval ( $_GET ['pages'] );
		}
		$currpage = max ( intval ( $_GET ['currpage'] ), 1 );
		$offset = ($currpage - 1) * 20;
		$result = $cache->select ( array (), '*', $offset . ', 20', 'filename ASC' );
		if (is_array ( $result ) && ! empty ( $result )) {
			foreach ( $result as $re ) {
				if (! file_exists ( CACHE_PATH . $re ['path'] . $re ['filename'] )) {
					$filesize = C ( 'framework', 'lock_ex' ) ? file_put_contents ( CACHE_PATH . $re ['path'] . $re ['filename'], $re ['data'], LOCK_EX ) : file_put_contents ( CACHE_PATH . $re ['path'] . $re ['filename'], $re ['data'] );
				} else {
					continue;
				}
			}
		}
		$currpage ++;
		if ($currpage > $pages) {
			return true;
		} else {
			echo '<script type="text/javascript">window.parent.addtext("<li>' . L ( 'part_cache_success' ) . ($currpage - 1) . '/' . $pages . '..........</li>");</script>';
			showmessage ( L ( 'part_cache_success' ), '?app=admin&controller=cache_all&action=init&page=' . $_GET ['page'] . '&currpage=' . $currpage . '&pages=' . $pages . '&dosubmit=1', 0 );
		}
	}

	/**
	 * 更新删除缓存文件方法
	 */
	public function del_file() {
		$path = DATA_PATH . 'compile' . DIRECTORY_SEPARATOR;
		$files = glob ( $path . '*' );
		if (is_array ( $files )) {
			foreach ( $files as $f ) {
				$dir = basename ( $f );
				if (! in_array ( $dir, array ('block','dbsource' ) )) {
					Folder::clear ( $path . $dir );
				}
			}
		}
		$path = DATA_PATH . 'tpl_data' . DIRECTORY_SEPARATOR;
		$files = glob ( $path . '*' );
		if (is_array ( $files )) {
			foreach ( $files as $f ) {
				$dir = basename ( $f );
				@unlink ( $path . $dir );
			}
		}
		return true;
	}

	/**
	 * 更新来源缓存方法
	 */
	public function copyfrom() {
		$infos = $this->db->order('listorder DESC')->key('id')->select ();
		S ( 'admin/copyfrom', $infos );
		return true;
	}

	/**
	 * 更新网站配置方法
	 */
	public function setting() {
		$setting = $this->db = Loader::model ( 'application_model' )->get_setting('admin');
		S ( 'common/common', $setting );
		return true;
	}
}