<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
// 模型缓存路径
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
error_reporting ( E_ERROR );
class NodeController extends admin {
	private $db;

	// HTML标签
	private static $html_tag = array ("<p([^>]*)>(.*)</p>[|]" => '<p>',"<a([^>]*)>(.*)</a>[|]" => '<a>',"<script([^>]*)>(.*)</script>[|]" => '<script>',"<iframe([^>]*)>(.*)</iframe>[|]" => '<iframe>',"<table([^>]*)>(.*)</table>[|]" => '<table>',"<span([^>]*)>(.*)</span>[|]" => '<span>',
			"<b([^>]*)>(.*)</b>[|]" => '<b>',"<img([^>]*)>[|]" => '<img>',"<object([^>]*)>(.*)</object>[|]" => '<object>',"<embed([^>]*)>(.*)</embed>[|]" => '<embed>',"<param([^>]*)>(.*)</param>[|]" => '<param>','<div([^>]*)>[|]' => '<div>','</div>[|]' => '</div>','<!--([^>]*)-->[|]' => '<!-- -->' );

	// 网址类型
	private $url_list_type = array ();
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'collection_node_model' );
		$this->url_list_type = array ('1' => L ( 'sequence' ),'2' => L ( 'multiple_pages' ),'3' => L ( 'single_page' ),'4' => 'RSS' );
	}

	/**
	 * node list
	 */
	public function manage() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$nodelist = $this->db->order ( 'nodeid DESC' )->listinfo ( $page, 15 );
		$pages = $this->db->pages;
		include $this->admin_tpl ( 'node_list' );
	}

	/**
	 * add node
	 */
	public function add() {
		header ( "Cache-control: private" );
		if (isset ( $_POST ['dosubmit'] )) {
			$data = isset ( $_POST ['data'] ) ? $_POST ['data'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			$customize_config = isset ( $_POST ['customize_config'] ) ? $_POST ['customize_config'] : '';
			if (! $data ['name'] = trim ( $data ['name'] )) {
				showmessage ( L ( 'nodename' ) . L ( 'empty' ), HTTP_REFERER );
			}
			if ($this->db->getby_name ( $data ['name'] )) {
				showmessage ( L ( 'nodename' ) . L ( 'exists' ), HTTP_REFERER );
			}
			$data ['urlpage'] = isset ( $_POST ['urlpage' . $data ['sourcetype']] ) ? $_POST ['urlpage' . $data ['sourcetype']] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			$data ['customize_config'] = array ();
			if (is_array ( $customize_config )) foreach ( $customize_config ['en_name'] as $k => $v ) {
				if (empty ( $v ) || empty ( $customize_config ['name'] [$k] )) continue;
				$data ['customize_config'] [] = array ('name' => $customize_config ['name'] [$k],'en_name' => $v,'rule' => $customize_config ['rule'] [$k],'html_rule' => $customize_config ['html_rule'] [$k] );
			}
			$data ['customize_config'] = array2string ( $data ['customize_config'] );
			if ($this->db->insert ( $data )) {
				showmessage ( L ( 'operation_success' ), '?app=collection&controller=node&action=manage' );
			} else {
				showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
			}
		} else {
			$show_dialog = $show_validator = true;
			include $this->admin_tpl ( 'node_form' );
		}
	}

	/**
	 * 修改采集配置
	 */
	public function edit() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$data = $this->db->getby_nodeid ( $nodeid );
		if (isset ( $_POST ['dosubmit'] )) {
			$datas = $data;
			unset ( $data );
			$data = isset ( $_POST ['data'] ) ? $_POST ['data'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			$customize_config = isset ( $_POST ['customize_config'] ) ? $_POST ['customize_config'] : '';
			if (! $data ['name'] = trim ( $data ['name'] )) {
				showmessage ( L ( 'nodename' ) . L ( 'empty' ), HTTP_REFERER );
			}
			if ($datas ['name'] != $data ['name']) {
				if ($this->db->getby_name ( $data ['name'] )) {
					showmessage ( L ( 'nodename' ) . L ( 'exists' ), HTTP_REFERER );
				}
			}

			$data ['urlpage'] = isset ( $_POST ['urlpage' . $data ['sourcetype']] ) ? $_POST ['urlpage' . $data ['sourcetype']] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			$data ['customize_config'] = array ();
			if (is_array ( $customize_config )) foreach ( $customize_config ['en_name'] as $k => $v ) {
				if (empty ( $v ) || empty ( $customize_config ['name'] [$k] )) continue;
				$data ['customize_config'] [] = array ('name' => $customize_config ['name'] [$k],'en_name' => $v,'rule' => $customize_config ['rule'] [$k],'html_rule' => $customize_config ['html_rule'] [$k] );
			}
			$data ['customize_config'] = array2string ( $data ['customize_config'] );
			if ($this->db->where ( array ('nodeid' => $nodeid ) )->update ( $data )) {
				showmessage ( L ( 'operation_success' ), '?app=collection&controller=node&action=manage' );
			} else {
				showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
			}
		} else {
			$model_cache = S ( 'common/model' );
			foreach ( $model_cache as $k => $v ) {
				$modellist [0] = L ( 'select_model' );
				$modellist [$k] = $v ['name'];
			}
			if (isset ( $data ['customize_config'] )) {
				$data ['customize_config'] = string2array ( $data ['customize_config'] );
			}
			$show_dialog = $show_validator = true;
			include $this->admin_tpl ( 'node_form' );
		}
	}

	/**
	 * 采集网址
	 */
	public function col_url_list() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		if ($data = $this->db->getby_nodeid ( $nodeid )) {
			Loader::lib ( 'collection:collection', false );
			$urls = collection::url_list ( $data );
			$total_page = count ( $urls );
			if ($total_page > 0) {
				$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 0;
				$url_list = $urls [$page];
				$url = collection::get_url_lists ( $url_list, $data );
				$history_db = Loader::model ( 'collection_history_model' );
				$content_db = Loader::model ( 'collection_content_model' );
				$total = count ( $url );
				$re = 0;
				if (is_array ( $url ) && ! empty ( $url )) foreach ( $url as $v ) {
					if (empty ( $v ['url'] ) || empty ( $v ['title'] )) continue;
					$v = new_addslashes ( $v );
					$v ['title'] = strip_tags ( $v ['title'] );
					$md5 = md5 ( $v ['url'] );
					if (! $history_db->getby_md5 ( $md5 )) {
						$history_db->insert ( array ('md5' => $md5 ) );
						$content_db->insert ( array ('nodeid' => $nodeid,'status' => 0,'url' => $v ['url'],'title' => $v ['title'] ) );
					} else {
						$re ++;
					}
				}
				$show_header = $show_dialog = true;
				if ($total_page <= $page) {
					$this->db->where ( array ('nodeid' => $nodeid ) )->update ( array ('lastdate' => TIME ) );
				}
				include $this->admin_tpl ( 'col_url_list' );
			} else {
				showmessage ( L ( 'not_to_collect' ) );
			}
		} else {
			showmessage ( L ( 'notfound' ) );
		}
	}

	/**
	 * 采集文章
	 */
	public function col_content() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		if ($data = $this->db->getby_nodeid ( $nodeid )) {
			Loader::lib ( 'collection:collection', false );
			$content_db = Loader::model ( 'collection_content_model' );
			// 更新附件状态
			$attach_status = false;
			if (C ( 'attachment', 'stat' )) {
				$this->attachment_db = Loader::model ( 'attachment_model' );
				$attach_status = true;
			}
			$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
			$total = isset ( $_GET ['total'] ) ? intval ( $_GET ['total'] ) : 0;
			if (empty ( $total )) $total = $content_db->where ( array ('nodeid' => $nodeid,'status' => 0 ) )->count ();
			$total_page = ceil ( $total / 2 );
			$list = $content_db->where ( array ('nodeid' => $nodeid,'status' => 0 ) )->field ( 'id,url' )->limit ( 2 )->order ( 'id desc' )->select ();
			$i = 0;
			if (! empty ( $list ) && is_array ( $list )) {
				foreach ( $list as $v ) {
					$GLOBALS ['downloadfiles'] = array ();
					$html = collection::get_content ( $v ['url'], $data );
					// 更新附件状态
					if ($attach_status) {
						$this->attachment_db->api_update ( $GLOBALS ['downloadfiles'], 'cj-' . $v ['id'], 1 );
					}
					$content_db->where(array ('id' => $v ['id'] ))->update ( array ('status' => 1,'data' => array2string ( $html ) ) );
					$i ++;
				}
			} else {
				showmessage ( L ( 'url_collect_msg' ), '?app=collection&controller=node&action=manage' );
			}
			if ($total_page > $page) {
				showmessage ( L ( 'collectioning' ) . ($i + ($page - 1) * 2) . '/' . $total . '<script type="text/javascript">location.href="?app=collection&controller=node&action=col_content&page=' . ($page + 1) . '&nodeid=' . $nodeid . '&total=' . $total . '"</script>', '?app=collection&controller=node&action=col_content&page=' . ($page + 1) . '&nodeid=' . $nodeid . '&total=' . $total );
			} else {
				$this->db->update ( array ('lastdate' => TIME ), array ('nodeid' => $nodeid ) );
				showmessage ( L ( 'collection_success' ), '?app=collection&controller=node&action=manage' );
			}
		}
	}

	/**
	 * 文章列表
	 */
	public function publist() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$node = $this->db->where ( array ('nodeid' => $nodeid ) )->field ( 'name' )->find ();
		$content_db = Loader::model ( 'collection_content_model' );
		$status = isset ( $_GET ['status'] ) ? intval ( $_GET ['status'] ) : '';
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$sql = array ('nodeid' => $nodeid );
		if ($status) {
			$sql ['status'] = $status - 1;
		}
		$data = $content_db->where ( $sql )->order ( 'id desc' )->listinfo ( $page );
		$pages = $content_db->pages;
		$show_header = true;
		include $this->admin_tpl ( 'publist' );
	}

	/**
	 * 导入文章
	 */
	public function import() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$id = isset ( $_GET ['id'] ) ? $_GET ['id'] : '';
		$type = isset ( $_GET ['type'] ) ? trim ( $_GET ['type'] ) : '';
		if ($type == 'all') {
		} else {
			$ids = implode ( ',', $id );
		}
		$program_db = Loader::model ( 'collection_program_model' );
		$program_list = $program_db->where ( array ('nodeid' => $nodeid ) )->field ( 'id, catid' )->select ();
		$cat = S ( 'common/category_content' );
		include $this->admin_tpl ( 'import_program' );
	}

	/**
	 * 删除文章
	 */
	public function content_del() {
		$id = isset ( $_GET ['id'] ) ? $_GET ['id'] : '';
		$history = isset ( $_GET ['history'] ) ? $_GET ['history'] : '';
		if (is_array ( $id )) {
			$collection_content_db = Loader::model ( 'collection_content_model' );
			$history_db = Loader::model ( 'collection_history_model' );
			$del_array = $id;
			$ids = implode ( ',', $id );
			if ($history) {
				$data = $collection_content_db->where ( array ('id' => array ('in',$ids ) ) )->field ( 'url' )->select ();
				foreach ( $data as $v ) {
					$list [] = md5 ( $v ['url'] );
				}
				$md5 = implode ( ',', $list );
				$history_db->where ( array ('md5' => array ('in',$md5 ) ) )->delete ();
			}
			$collection_content_db->where ( array ('id' => array ('in',$ids ) ) )->delete ();
			// 同时删除关联附件
			if (! empty ( $del_array )) {
				$attachment = Loader::model ( 'attachment_model' );
				foreach ( $del_array as $id ) {
					$attachment->api_delete ( 'cj-' . $id );
				}
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}

	/**
	 * 添加导入方案
	 */
	public function import_program_add() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$ids = isset ( $_GET ['ids'] ) ? $_GET ['ids'] : '';
		$catid = isset ( $_GET ['catid'] ) && intval ( $_GET ['catid'] ) ? intval ( $_GET ['catid'] ) : showmessage ( L ( 'please_select_cat' ), HTTP_REFERER );
		$type = isset ( $_GET ['type'] ) ? trim ( $_GET ['type'] ) : '';

		include dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'spider_funs' . DIRECTORY_SEPARATOR . 'config.php';

		// 读取栏目缓存
		$catlist = S ( 'common/category_content' );
		$cat = $catlist [$catid];
		$cat ['setting'] = string2array ( $cat ['setting'] );
		if ($cat ['type'] != 0) showmessage ( L ( 'illegal_section_parameter' ), HTTP_REFERER );
		if (isset ( $_POST ['dosubmit'] )) {
			$config = array ();
			$model_field = isset ( $_POST ['model_field'] ) ? $_POST ['model_field'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			$node_field = isset ( $_POST ['node_field'] ) ? $_POST ['node_field'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			$funcs = isset ( $_POST ['funcs'] ) ? $_POST ['funcs'] : array ();

			$config ['add_introduce'] = isset ( $_POST ['add_introduce'] ) && intval ( $_POST ['add_introduce'] ) ? intval ( $_POST ['add_introduce'] ) : 0;
			$config ['auto_thumb'] = isset ( $_POST ['auto_thumb'] ) && intval ( $_POST ['auto_thumb'] ) ? intval ( $_POST ['auto_thumb'] ) : 0;
			$config ['introcude_length'] = isset ( $_POST ['introcude_length'] ) && intval ( $_POST ['introcude_length'] ) ? intval ( $_POST ['introcude_length'] ) : 0;
			$config ['auto_thumb_no'] = isset ( $_POST ['auto_thumb_no'] ) && intval ( $_POST ['auto_thumb_no'] ) ? intval ( $_POST ['auto_thumb_no'] ) : 0;
			$config ['content_status'] = isset ( $_POST ['content_status'] ) && intval ( $_POST ['content_status'] ) ? intval ( $_POST ['content_status'] ) : 1;

			foreach ( $node_field as $k => $v ) {
				if (empty ( $v )) continue;
				$config ['map'] [$model_field [$k]] = $v;
			}

			foreach ( $funcs as $k => $v ) {
				if (empty ( $v )) continue;
				$config ['funcs'] [$model_field [$k]] = $v;
			}

			$data = array ('config' => array2string ( $config ),'nodeid' => $nodeid,'modelid' => $cat ['modelid'],'catid' => $catid );
			$program_db = Loader::model ( 'collection_program_model' );
			if ($id = $program_db->insert ( $data, true )) {
				showmessage ( L ( 'program_add_operation_success' ), '?app=collection&controller=node&action=import_content&programid=' . $id . '&nodeid=' . $nodeid . '&ids=' . $ids . '&type=' . $type );
			} else {
				showmessage ( L ( 'illegal_parameters' ) );
			}
		}

		// 读取数据模型缓存
		$model = S ( 'model/model_field_' . $cat ['modelid'] );
		if (empty ( $model )) showmessage ( L ( 'model_does_not_exist_please_update_the_cache_model' ) );
		$node_data = $this->db->where ( array ('nodeid' => $nodeid ) )->field ( "customize_config" )->find ();
		$node_data ['customize_config'] = string2array ( $node_data ['customize_config'] );
		$node_field = array ('' => L ( 'please_choose' ),'title' => L ( 'title' ),'author' => L ( 'author' ),'comeform' => L ( 'comeform' ),'time' => L ( 'time' ),'content' => L ( 'content' ) );
		if (is_array ( $node_data ['customize_config'] )) foreach ( $node_data ['customize_config'] as $k => $v ) {
			if (empty ( $v ['en_name'] ) || empty ( $v ['name'] )) continue;
			$node_field [$v ['en_name']] = $v ['name'];
		}
		$show_header = true;
		include $this->admin_tpl ( 'import_program_add' );
	}

	/**
	 * 导入文章到模型
	 */
	public function import_content() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$programid = isset ( $_GET ['programid'] ) ? intval ( $_GET ['programid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$ids = isset ( $_GET ['ids'] ) ? $_GET ['ids'] : '';
		$type = isset ( $_GET ['type'] ) ? trim ( $_GET ['type'] ) : '';
		if (! $node = $this->db->where ( array ('nodeid' => $nodeid ) )->field ( 'coll_order,content_page' )->find ()) {
			showmessage ( L ( 'node_not_found' ), '?app=collection&controller=node&action=manage' );
		}
		$program_db = Loader::model ( 'collection_program_model' );
		$collection_content_db = Loader::model ( 'collection_content_model' );
		$content_db = Loader::model ( 'content_model' );
		// 更新附件状态
		$attach_status = false;
		if (C ( 'attachment', 'stat' )) {
			$attachment_db = Loader::model ( 'attachment_model' );
			$att_index_db = Loader::model ( 'attachment_index_model' );
			$attach_status = true;
		}
		$order = $node ['coll_order'] == 1 ? 'id desc' : '';
		$str = L ( 'operation_success' );
		$url = '?app=collection&controller=node&action=publist&nodeid=' . $nodeid . '&status=2';
		if ($type == 'all') {
			$total = isset ( $_GET ['total'] ) && intval ( $_GET ['total'] ) ? intval ( $_GET ['total'] ) : '';
			if (empty ( $total )) $total = $collection_content_db->where(array ('nodeid' => $nodeid,'status' => 1 ))->count (  );
			$total_page = ceil ( $total / 20 );
			$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
			$total_page = ceil ( $total / 20 );
			$data = $collection_content_db->where ( array ('nodeid' => $nodeid,'status' => 1 ) )->field ( 'id, data', '20' )->order ( $order )->select ();
		} else {
			$data = $collection_content_db->field ( 'id, data' )->where ( array ('status' => 1,'nodeid' => $nodeid,'id' => array ('in',$ids ) ) )->order ( $order )->select ();
			$total = count ( $data );
			$str = L ( 'operation_success' ) . $total . L ( 'article_was_imported' );
		}
		$program = $program_db->getby_id ( $programid );
		$program ['config'] = string2array ( $program ['config'] );
		$_POST ['add_introduce'] = $program ['config'] ['add_introduce'];
		$_POST ['introcude_length'] = $program ['config'] ['introcude_length'];
		$_POST ['auto_thumb'] = $program ['config'] ['auto_thumb'];
		$_POST ['auto_thumb_no'] = $program ['config'] ['auto_thumb_no'];
		$_POST ['spider_img'] = 0;
		$i = 0;
		$content_db->set_model ( $program ['modelid'] );
		$coll_contentid = array ();

		// 加载所有的处理函数
		$funcs_file_list = glob ( dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'spider_funs' . DIRECTORY_SEPARATOR . '*.php' );
		foreach ( $funcs_file_list as $v ) {
			include $v;
		}
		foreach ( $data as $k => $v ) {
			$sql = array ('catid' => $program ['catid'],'status' => $program ['config'] ['content_status'] );
			$v ['data'] = string2array ( $v ['data'] );

			foreach ( $program ['config'] ['map'] as $a => $b ) {
				if (isset ( $program ['config'] ['funcs'] [$a] ) && function_exists ( $program ['config'] ['funcs'] [$a] )) {
					$GLOBALS ['field'] = $a;
					$sql [$a] = $program ['config'] ['funcs'] [$a] ( $v ['data'] [$b] );
				} else {
					$sql [$a] = $v ['data'] [$b];
				}
			}
			if ($node ['content_page'] == 1) $sql ['paginationtype'] = 2;
			$contentid = $content_db->add_content ( $sql, 1 );
			if ($contentid) {
				$coll_contentid [] = $v ['id'];
				$i ++;
				// 更新附件状态,将采集关联重置到内容关联
				if ($attach_status) {
					$datas = $att_index_db->where ( array ('keyid' => 'cj-' . $v ['id'] ) )->key ( 'aid' )->select ();
					if (! empty ( $datas )) {
						$datas = array_keys ( $datas );
						$datas = implode ( ',', $datas );
						$att_index_db->where ( array ('keyid' => 'cj-' . $v ['id'] ) )->update ( array ('keyid' => 'c-' . $program ['catid'] . '-' . $contentid ) );
						$attachment_db->where ( array ('aid' => array ('in',$datas ) ) )->update ( array ('application' => 'content' ) );
					}
				}
			} else {
				$collection_content_db->where ( array ('id' => $v ['id'] ) )->delete ();
			}
		}
		$sql_id = implode ( ',', $coll_contentid );
		$collection_content_db->where ( array ('id' => array ('in',$sql_id ) ) )->update ( array ('status' => 2 ) );
		if ($type == 'all' && $total_page > $page) {
			$str = L ( 'are_imported_the_import_process' ) . (($page - 1) * 20 + $i) . '/' . $total . '<script type="text/javascript">location.href="?app=collection&controller=node&action=import_content&nodeid=' . $nodeid . '&programid=' . $programid . '&type=all&page=' . ($page + 1) . '&total=' . $total . '"</script>';
			$url = '';
		}
		showmessage ( $str, $url );
	}

	/**
	 * URL配置显示结果
	 */
	public function public_url() {
		$sourcetype = isset ( $_GET ['sourcetype'] ) && intval ( $_GET ['sourcetype'] ) ? intval ( $_GET ['sourcetype'] ) : showmessage ( L ( 'illegal_parameters' ) );
		$pagesize_start = isset ( $_GET ['pagesize_start'] ) && intval ( $_GET ['pagesize_start'] ) ? intval ( $_GET ['pagesize_start'] ) : 1;
		$pagesize_end = isset ( $_GET ['pagesize_end'] ) && intval ( $_GET ['pagesize_end'] ) ? intval ( $_GET ['pagesize_end'] ) : 10;
		$par_num = isset ( $_GET ['par_num'] ) && intval ( $_GET ['par_num'] ) ? intval ( $_GET ['par_num'] ) : 1;
		$urlpage = isset ( $_GET ['urlpage'] ) && trim ( $_GET ['urlpage'] ) ? trim ( $_GET ['urlpage'] ) : showmessage ( L ( 'illegal_parameters' ) );
		$show_header = true;
		include $this->admin_tpl ( 'node_public_url' );
	}

	/**
	 * 测试文章内容采集
	 */
	public function public_test_content() {
		$url = isset ( $_GET ['url'] ) ? urldecode ( $_GET ['url'] ) : exit ( '0' );
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		if ($data = $this->db->getby_nodeid ( $nodeid )) {
			Loader::lib ( 'collection:collection', false );
			print_r ( collection::get_content ( $url, $data ) );
		} else {
			showmessage ( L ( 'notfound' ) );
		}
	}

	/**
	 * 测试文章URL采集
	 */
	public function public_test() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		if ($data = $this->db->getby_nodeid ( $nodeid )) {
			Loader::lib ( 'collection:collection', false );
			$urls = collection::url_list ( $data, 1 );
			if (! empty ( $urls )) foreach ( $urls as $v ) {
				$url = collection::get_url_lists ( $v, $data );
			}
			$show_header = $show_dialog = true;
			include $this->admin_tpl ( 'public_test' );
		} else {
			showmessage ( L ( 'notfound' ) );
		}
	}
	public function import_program_del() {
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$program_db = Loader::model ( 'collection_program_model' );
		if ($program_db->where ( array ('id' => $id ) )->delete ()) {
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'illegal_parameters' ) );
		}
	}

	/**
	 * 导入采集点
	 */
	public function node_import() {
		if (isset ( $_POST ['dosubmit'] )) {
			$filename = $_FILES ['file'] ['tmp_name'];
			if (strtolower ( substr ( $_FILES ['file'] ['name'], - 3, 3 ) ) != 'txt') {
				showmessage ( L ( 'only_allowed_to_upload_txt_files' ), HTTP_REFERER );
			}
			$data = json_decode ( base64_decode ( file_get_contents ( $filename ) ), true );
			if (CHARSET == 'gbk') $data = array_iconv ( $data, 'utf-8', 'gbk' );
			@unlink ( $filename );
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			if ($this->db->where ( array ('name' => $name ) )->field ( 'nodeid' )->find ()) {
				showmessage ( L ( 'nodename' ) . L ( 'exists' ), HTTP_REFERER );
			}
			$data ['name'] = $name;
			$data = new_addslashes ( $data );
			if ($this->db->insert ( $data )) {
				showmessage ( L ( 'operation_success' ), '', '', 'test' );
			} else {
				showmessage ( L ( 'operation_failure' ) );
			}
		} else {
			$show_header = $show_validator = true;
			include $this->admin_tpl ( 'node_import' );
		}
	}

	/**
	 * 采集节点名验证
	 */
	public function public_name() {
		$name = isset ( $_GET ['name'] ) && trim ( $_GET ['name'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['name'] ) ) : trim ( $_GET ['name'] )) : exit ( '0' );
		$nodeid = isset ( $_GET ['nodeid'] ) && intval ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : '';
		$data = array ();
		if ($nodeid) {
			$data = $this->db->where ( array ('nodeid' => $nodeid ) )->field ( 'name' )->find ();
			if (! empty ( $data ) && $data ['name'] == $name) {
				exit ( '1' );
			}
		}
		if ($this->db->where ( array ('name' => $name ) )->field ( 'nodeid' )->find ()) {
			exit ( '0' );
		} else {
			exit ( '1' );
		}
	}

	/**
	 * 导出采集配置
	 */
	public function export() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		if ($data = $this->db->getby_nodeid ( $nodeid )) {
			unset ( $data ['nodeid'], $data ['name'] );
			if (CHARSET == 'gbk') $data = array_iconv ( $data );
			header ( "Content-type: application/octet-stream" );
			header ( "Content-Disposition: attachment; filename=yun_collection_" . $nodeid . '.txt' );
			echo base64_encode ( json_encode ( $data ) );
		} else {
			showmessage ( L ( 'notfound' ) );
		}
	}

	/**
	 * 删除采集节点
	 */
	public function del() {
		if (isset ( $_POST ['dosubmit'] )) {
			$nodeid = isset ( $_POST ['nodeid'] ) ? $_POST ['nodeid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			foreach ( $nodeid as $k => $v ) {
				if (intval ( $v )) {
					$nodeid [$k] = intval ( $v );
				} else {
					unset ( $nodeid [$k] );
				}
			}
			$nodeid = implode ( ',', $nodeid );
			$this->db->where ( array ('nodeid' => array ('in',$nodeid ) ) )->delete ();
			$content_db = Loader::model ( 'collection_content_model' );
			$content_db->where ( array ('nodeid' => array ('in',$nodeid ) ) )->delete ();
			showmessage ( L ( 'operation_success' ), '?app=collection&controller=node&action=manage' );
		} else {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		}
	}

	/**
	 * 复制采集
	 */
	public function copy() {
		$nodeid = isset ( $_GET ['nodeid'] ) ? intval ( $_GET ['nodeid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		if ($data = $this->db->getby_nodeid ( $nodeid )) {
			if (isset ( $_POST ['dosubmit'] )) {
				unset ( $data ['nodeid'] );
				$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
				if ($this->db->where ( array ('name' => $name ) )->field ( 'nodeid' )->find ()) {
					showmessage ( L ( 'nodename' ) . L ( 'exists' ), HTTP_REFERER );
				}
				$data ['name'] = $name;
				$data = new_addslashes ( $data );
				if ($this->db->insert ( $data )) {
					showmessage ( L ( 'operation_success' ), '', '', 'test' );
				} else {
					showmessage ( L ( 'operation_failure' ) );
				}
			} else {
				$show_validator = $show_header = true;
				include $this->admin_tpl ( 'node_copy' );
			}
		} else {
			showmessage ( L ( 'notfound' ) );
		}
	}
}
?>