<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-5
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Create_htmlController.php 252 2012-11-07 14:52:09Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
error_reporting ( E_ERROR );
class Create_htmlController extends admin {

	private $db;
	public $categorys;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'content_model' );
		$this->categorys = S ( 'common/category_content' );
		foreach ( $_GET as $k => $v ) {
			$_POST [$k] = $v;
		}
	}

	public function update_urls() {
		if (isset ( $_POST ['dosubmit'] )) {
			extract ( $_POST, EXTR_SKIP );
			$this->url = Loader::lib ( 'content:url' );
			$modelid = intval ( $_POST ['modelid'] );
			if ($modelid) {
				// 设置模型数据表名
				$this->db->set_model ( $modelid );
				$table_name = $this->db->table_name;
				if ($type == 'lastinput') {
					$offset = 0;
				} else {
					$page = max ( intval ( $page ), 1 );
					$offset = $pagesize * ($page - 1);
				}
				$where = array('status'=>99);
				$order = 'ASC';
				if (! isset ( $first ) && is_array ( $catids ) && $catids [0] > 0) {
					S ( 'content/url_show_' . $_SESSION ['userid'], $catids );
					$catids = implode ( ',', $catids );
					$where['catid'] = array('in',$catids);
					$first = 1;
				} elseif ($first) {
					$catids = S ( 'content/url_show_' . $_SESSION ['userid'] );
					$catids = implode ( ',', $catids );
					$where['catid'] = array('in',$catids);
				} else {
					$first = 0;
				}

				if ($type == 'lastinput' && $number) {
					$offset = 0;
					$pagesize = $number;
					$order = 'DESC';
				} elseif ($type == 'date') {
					if ($fromdate) {
						$fromtime = strtotime ( $fromdate . ' 00:00:00' );
						$where['inputtime'] = array('egt',$fromtime);
					}
					if ($todate) {
						$totime = strtotime ( $todate . ' 23:59:59' );
						$where['inputtime'] = array('elt',$totime);
					}
				} elseif ($type == 'id') {
					$fromid = intval ( $fromid );
					$toid = intval ( $toid );
					if ($fromid) $where['id'] = array('egt',$fromid);
					if ($toid) $where['id'] = array('elt',$toid);
				}

				if (! isset ( $total ) && $type != 'lastinput') {
					$total = $this->db->where($where)->count();
					$pages = ceil ( $total / $pagesize );
					$start = 1;
				}
				$data = $this->db->where($where)->order(`id ` .$order)->limit($offset,$pagesize)->select ( );
				foreach ( $data as $r ) {
					if ($r ['islink'] || $r ['upgrade']) continue;
					// 更新URL链接
					$this->urls ( $r ['id'], $r ['catid'], $r ['inputtime'], $r ['prefix'] );

				}

				if ($pages > $page) {
					$page ++;
					$http_url = Core_Request::get_url ();
					$creatednum = $offset + count ( $data );
					$percent = round ( $creatednum / $total, 2 ) * 100;

					$message = L ( 'need_update_items', array ('total' => $total,'creatednum' => $creatednum,'percent' => $percent ) );
					$forward = $start ? "?app=content&controller=create_html&action=update_urls&type=$type&dosubmit=1&first=$first&fromid=$fromid&toid=$toid&fromdate=$fromdate&todate=$todate&pagesize=$pagesize&page=$page&pages=$pages&total=$total&modelid=$modelid" : preg_replace ( "/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page=$page&pages=$pages&total=$total", $http_url );
				} else {
					S ( 'content/url_show_' . $_SESSION ['userid'], '' );
					$message = L ( 'create_update_success' );
					$forward = '?app=content&controller=create_html&action=update_urls';
				}
				showmessage ( $message, $forward, 200 );
			} else {
				// 当没有选择模型时，需要按照栏目来更新
				if (! isset ( $set_catid )) {
					if ($catids [0] != 0) {
						$update_url_catids = $catids;
					} else {
						foreach ( $this->categorys as $catid => $cat ) {
							if ($cat ['child'] || $cat ['type'] != 0) continue;
							$update_url_catids [] = $catid;
						}
					}
					S ( 'content/update_url_catid' . '-' . $_SESSION ['userid'], $update_url_catids );
					$message = L ( 'start_update_urls' );
					$forward = "?app=content&controller=create_html&action=update_urls&set_catid=1&pagesize=$pagesize&dosubmit=1";
					showmessage ( $message, $forward, 200 );
				}
				$catid_arr = S ( 'content/update_url_catid' . '-' . $_SESSION ['userid'] );
				$autoid = isset ( $autoid ) ? intval ( $autoid ) : 0;
				if (! isset ( $catid_arr [$autoid] )) showmessage ( L ( 'create_update_success' ), '?app=content&controller=create_html&action=update_urls', 200 );
				$catid = $catid_arr [$autoid];

				$modelid = $this->categorys [$catid] ['modelid'];
				// 设置模型数据表名
				$this->db->set_model ( $modelid );
				$table_name = $this->db->table_name;

				$page = max ( intval ( $page ), 1 );
				$offset = $pagesize * ($page - 1);
				$where = array('status'=>99,'catid'=>$catid);
				$order = 'ASC';

				if (! isset ( $total )) {
					$total = $this->db->where($where)->count ();
					$pages = ceil ( $total / $pagesize );
					$start = 1;
				}
				$data = $this->db->where($where)->order(`id ASC`)->limit($offset,$pagesize)->select();
				foreach ( $data as $r ) {
					if ($r ['islink'] || $r ['upgrade']) continue;
					// 更新URL链接
					$this->urls ( $r ['id'], $r ['catid'], $r ['inputtime'], $r ['prefix'] );
				}
				if ($pages > $page) {
					$page ++;
					$http_url = Core_Request::get_url ();
					$creatednum = $offset + count ( $data );
					$percent = round ( $creatednum / $total, 2 ) * 100;
					$message = '【' . $this->categorys [$catid] ['catname'] . '】 ' . L ( 'have_update_items', array ('total' => $total,'creatednum' => $creatednum,'percent' => $percent ) );
					$forward = $start ? "?app=content&controller=create_html&action=update_urls&type=$type&dosubmit=1&first=$first&fromid=$fromid&toid=$toid&fromdate=$fromdate&todate=$todate&pagesize=$pagesize&page=$page&pages=$pages&total=$total&autoid=$autoid&set_catid=1" : preg_replace ( "/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page=$page&pages=$pages&total=$total", $http_url );
				} else {
					$autoid ++;
					$message = L ( 'updating' ) . $this->categorys [$catid] ['catname'] . " ...";
					$forward = "?app=content&controller=create_html&action=update_urls&set_catid=1&pagesize=$pagesize&dosubmit=1&autoid=$autoid";
				}
				showmessage ( $message, $forward, 200 );
			}

		} else {
			$show_header = $show_dialog = '';
			$admin_username = cookie ( 'admin_username' );
			$modelid = isset ( $_GET ['modelid'] ) ? intval ( $_GET ['modelid'] ) : 0;
			$tree = Loader::lib ( 'Tree' );
			$tree->icon = array ('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ' );
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$categorys = array ();
			if (! empty ( $this->categorys )) {
				foreach ( $this->categorys as $catid => $r ) {
					if ($r ['type'] != 0 && $r ['child'] == 0) continue;
					if ($modelid && $modelid != $r ['modelid']) continue;
					$r ['disabled'] = $r ['child'] ? 'disabled' : '';
					$categorys [$catid] = $r;
				}
			}
			$str = "<option value='\$catid' \$selected \$disabled>\$spacer \$catname</option>";
			$tree->init ( $categorys );
			$string .= $tree->get_tree ( 0, $str );
			include $this->admin_tpl ( 'update_urls' );
		}
	}

	private function urls($id, $catid = 0, $inputtime = 0, $prefix = '') {
		$urls = $this->url->show ( $id, 0, $catid, $inputtime, $prefix, '', 'edit' );
		// 更新到数据库
		$url = $urls [0];
		$this->db->where(array ('id' => $id ))->update ( array ('url' => $url ) );
		return $urls;
	}

	/**
	 * 生成内容页
	 */
	public function show() {
		if (isset ( $_POST ['dosubmit'] )) {
			extract ( $_POST, EXTR_SKIP );
			$this->html = Loader::lib ( 'content:html' );
			$modelid = intval ( $_POST ['modelid'] );
			if ($modelid) {
				// 设置模型数据表名
				$this->db->set_model ( $modelid );
				$table_name = $this->db->table_name;
				if ($type == 'lastinput') {
					$offset = 0;
				} else {
					$page = max ( intval ( $page ), 1 );
					$offset = $pagesize * ($page - 1);
				}
				$where = array('status'=>99);
				$order = 'ASC';

				if (! isset ( $first ) && is_array ( $catids ) && $catids [0] > 0) {
					S ( 'content/html_show_' . $_SESSION ['userid'], $catids );
					$catids = implode ( ',', $catids );
					$where['catid']=array('in',$catids);
					$first = 1;
				} elseif ($first) {
					$catids = S ( 'content/html_show_' . $_SESSION ['userid'] );
					$catids = implode ( ',', $catids );
					$where['catid']=array('in',$catids);
				} else {
					$first = 0;
				}
				if (count ( $catids ) == 1 && $catids [0] == 0) {
					$message = L ( 'create_update_success' );
					$forward = '?app=content&controller=create_html&action=show';
					showmessage ( $message, $forward );
				}
				if ($type == 'lastinput' && $number) {
					$offset = 0;
					$pagesize = $number;
					$order = 'DESC';
				} elseif ($type == 'date') {
					if ($fromdate) {
						$fromtime = strtotime ( $fromdate . ' 00:00:00' );
						$where['inputtime'] = array('egt',$fromtime);
					}
					if ($todate) {
						$totime = strtotime ( $todate . ' 23:59:59' );
						$where['inputtime'] = array('elt',$totime);
					}
				} elseif ($type == 'id') {
					$fromid = intval ( $fromid );
					$toid = intval ( $toid );
					if ($fromid) $where['id'] = array('egt',$fromid);
					if ($toid) $where['id'] = array('elt',$toid);
				}
				if (! isset ( $total ) && $type != 'lastinput') {
					$total = $this->db->where($where)->count();
					$pages = ceil ( $total / $pagesize );
					$start = 1;
				}
				$data = $this->db->where($where)->order(`id ` .$order)->limit($offset,$pagesize)->select ();
				$tablename = $this->db->table_name . '_data';
				$this->url = Loader::lib ( 'content:url' );
				foreach ( $data as $r ) {
					if ($r ['islink']) continue;
					$this->db->table_name = $tablename;
					$r2 = $this->db->get_one ( array ('id' => $r ['id'] ) );
					if ($r) $r = array_merge ( $r, $r2 );
					if ($r ['upgrade']) {
						$urls [1] = $r ['url'];
					} else {
						$urls = $this->url->show ( $r ['id'], '', $r ['catid'], $r ['inputtime'] );
					}
					$this->html->show ( $urls [1], $r, 0, 'edit', $r ['upgrade'] );
				}
				if ($pages > $page) {
					$page ++;
					$http_url = Core_Request::get_url ();
					$creatednum = $offset + count ( $data );
					$percent = round ( $creatednum / $total, 2 ) * 100;

					$message = L ( 'need_update_items', array ('total' => $total,'creatednum' => $creatednum,'percent' => $percent ) );
					$forward = $start ? "?app=content&controller=create_html&action=show&type=$type&dosubmit=1&first=$first&fromid=$fromid&toid=$toid&fromdate=$fromdate&todate=$todate&pagesize=$pagesize&page=$page&pages=$pages&total=$total&modelid=$modelid" : preg_replace ( "/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page=$page&pages=$pages&total=$total", $http_url );
				} else {
					C ( 'content/html_show_' . $_SESSION ['userid'], '' );
					$message = L ( 'create_update_success' );
					$forward = '?app=content&controller=create_html&action=show';
				}
				showmessage ( $message, $forward, 200 );
			} else {
				// 当没有选择模型时，需要按照栏目来更新
				if (! isset ( $set_catid )) {
					if ($catids [0] != 0) {
						$update_url_catids = $catids;
					} else {
						foreach ( $this->categorys as $catid => $cat ) {
							if ($cat ['child'] || $cat ['type'] != 0) continue;
							$setting = string2array ( $cat ['setting'] );
							if (! $setting ['content_ishtml']) continue;
							$update_url_catids [] = $catid;
						}
					}
					S ( 'content/update_html_catid' . '-' . $_SESSION ['userid'], $update_url_catids );
					$message = L ( 'start_update' );
					$forward = "?app=content&controller=create_html&action=show&set_catid=1&pagesize=$pagesize&dosubmit=1";
					showmessage ( $message, $forward, 200 );
				}
				if (count ( $catids ) == 1 && $catids [0] == 0) {
					$message = L ( 'create_update_success' );
					$forward = '?app=content&controller=create_html&action=show';
					showmessage ( $message, $forward, 200 );
				}
				$catid_arr = S ( 'content/update_html_catid' . '-' . $_SESSION ['userid'] );
				$autoid = $autoid ? intval ( $autoid ) : 0;
				if (! isset ( $catid_arr [$autoid] )) showmessage ( L ( 'create_update_success' ), '?app=content&controller=create_html&action=show', 200 );
				$catid = $catid_arr [$autoid];

				$modelid = $this->categorys [$catid] ['modelid'];
				// 设置模型数据表名
				$this->db->set_model ( $modelid );
				$table_name = $this->db->table_name;

				$page = max ( intval ( $page ), 1 );
				$offset = $pagesize * ($page - 1);
				$where=array('status'=>99,'catid'=>$catid);
				$order = 'ASC';

				if (! isset ( $total )) {
					$total = $this->db->where($where)->count();
					$pages = ceil ( $total / $pagesize );
					$start = 1;
				}
				$data = $this->db->where($where)->order(`id ` .$order)->limit($offset,$pagesize)->select ();
				$tablename = $this->db->table_name . '_data';
				$this->url = Loader::lib ( 'content:url' );
				foreach ( $data as $r ) {
					if ($r ['islink']) continue;
					// 写入文件
					$this->db->table_name = $tablename;
					$r2 = $this->db->getby_id ( $r ['id'] );
					if ($r2) $r = array_merge ( $r, $r2 );
					if ($r ['upgrade']) {
						$urls [1] = $r ['url'];
					} else {
						$urls = $this->url->show ( $r ['id'], '', $r ['catid'], $r ['inputtime'] );
					}
					$this->html->show ( $urls [1], $r, 0, 'edit', $r ['upgrade'] );
				}
				if ($pages > $page) {
					$page ++;
					$http_url = Core_Request::get_url ();
					$creatednum = $offset + count ( $data );
					$percent = round ( $creatednum / $total, 2 ) * 100;
					$message = '【' . $this->categorys [$catid] ['catname'] . '】 ' . L ( 'have_update_items', array ('total' => $total,'creatednum' => $creatednum,'percent' => $percent ) );
					$forward = $start ? "?app=content&controller=create_html&action=show&type=$type&dosubmit=1&first=$first&fromid=$fromid&toid=$toid&fromdate=$fromdate&todate=$todate&pagesize=$pagesize&page=$page&pages=$pages&total=$total&autoid=$autoid&set_catid=1" : preg_replace ( "/&page=([0-9]+)&pages=([0-9]+)&total=([0-9]+)/", "&page=$page&pages=$pages&total=$total", $http_url );
				} else {
					$autoid ++;
					$message = L ( 'start_update' ) . $this->categorys [$catid] ['catname'] . " ...";
					$forward = "?app=content&controller=create_html&action=show&set_catid=1&pagesize=$pagesize&dosubmit=1&autoid=$autoid";
				}
				showmessage ( $message, $forward, 200 );
			}

		} else {
			$show_header = $show_dialog = '';
			$admin_username = cookie ( 'admin_username' );
			$modelid = isset ( $_GET ['modelid'] ) ? intval ( $_GET ['modelid'] ) : 0;
			$tree = Loader::lib ( 'Tree' );
			$tree->icon = array ('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ' );
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$categorys = array ();
			if (! empty ( $this->categorys )) {
				foreach ( $this->categorys as $catid => $r ) {
					if ($r ['type'] != 0 && $r ['child'] == 0) continue;
					if ($modelid && $modelid != $r ['modelid']) continue;
					if ($r ['child'] == 0) {
						$setting = string2array ( $r ['setting'] );
						if (! $setting ['content_ishtml']) continue;
					}
					$r ['disabled'] = $r ['child'] ? 'disabled' : '';
					$categorys [$catid] = $r;
				}
			}
			$str = "<option value='\$catid' \$selected \$disabled>\$spacer \$catname</option>";

			$tree->init ( $categorys );
			$string .= $tree->get_tree ( 0, $str );
			include $this->admin_tpl ( 'create_html_show' );
		}

	}

	/**
	 * 生成栏目页
	 */
	public function category() {
		if (isset ( $_POST ['dosubmit'] )) {
			extract ( $_POST, EXTR_SKIP );
			$this->html = Loader::lib ( 'content:html' );
			$referer = isset ( $referer ) ? urlencode ( $referer ) : '';
			$modelid = intval ( $_POST ['modelid'] );
			if (! isset ( $set_catid )) {
				if ($catids [0] != 0) {
					$update_url_catids = $catids;
				} else {
					foreach ( $this->categorys as $catid => $cat ) {
						if ($cat ['type'] == 2 || ! $cat ['ishtml']) continue;
						if ($modelid && ($modelid != $cat ['modelid'])) continue;
						$update_url_catids [] = $catid;
					}
				}
				S ( 'content/update_html_catid' . '-' . $_SESSION ['userid'], $update_url_catids );
				$message = L ( 'start_update_category' );
				$forward = "?app=content&controller=create_html&action=category&set_catid=1&pagesize=$pagesize&dosubmit=1&modelid=$modelid&referer=$referer";
				showmessage ( $message, $forward );
			}
			$catid_arr = S ( 'content/update_html_catid' . '-' . $_SESSION ['userid'] );

			$autoid = isset ( $autoid ) ? intval ( $autoid ) : 0;
			if (! isset ( $catid_arr [$autoid] )) {
				if (! empty ( $referer ) && $this->categorys [$catid_arr [0]] ['type'] != 1) {
					showmessage ( L ( 'create_update_success' ), '?app=content&controller=content&action=init&catid=' . $catid_arr [0], 200 );
				} else {
					showmessage ( L ( 'create_update_success' ), '?app=content&controller=create_html&action=category', 200 );
				}
			}
			$catid = $catid_arr [$autoid];
			$page = isset ( $page ) ? $page : 1;
			$j = 1;
			do {
				$this->html->category ( $catid, $page );
				$page ++;
				$j ++;
				$total_number = isset ( $total_number ) ? $total_number : PAGES;
			} while ( $j <= $total_number && $j < $pagesize );
			if ($page <= $total_number) {
				$endpage = intval ( $page + $pagesize );
				$message = L ( 'updating' ) . $this->categorys [$catid] ['catname'] . L ( 'start_to_end_id', array ('page' => $page,'endpage' => $endpage ) );
				$forward = "?app=content&controller=create_html&action=category&set_catid=1&pagesize=$pagesize&dosubmit=1&autoid=$autoid&page=$page&total_number=$total_number&modelid=$modelid&referer=$referer";
			} else {
				$autoid ++;
				$message = $this->categorys [$catid] ['catname'] . L ( 'create_update_success' );
				$forward = "?app=content&controller=create_html&action=category&set_catid=1&pagesize=$pagesize&dosubmit=1&autoid=$autoid&modelid=$modelid&referer=$referer";
			}
			showmessage ( $message, $forward, 200 );
		} else {
			$show_header = $show_dialog = '';
			$admin_username = cookie ( 'admin_username' );
			$modelid = isset ( $_GET ['modelid'] ) ? intval ( $_GET ['modelid'] ) : 0;
			$tree = Loader::lib ( 'Tree' );
			$tree->icon = array ('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ' );
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$categorys = array ();
			if (! empty ( $this->categorys )) {
				foreach ( $this->categorys as $catid => $r ) {
					if ($r ['type'] == 2 && $r ['child'] == 0) continue;
					if ($modelid && $modelid != $r ['modelid']) continue;
					if ($r ['child'] == 0) {
						if (! $r ['ishtml']) continue;
					}
					$categorys [$catid] = $r;
				}
			}
			$str = "<option value='\$catid' \$selected>\$spacer \$catname</option>";
			$tree->init ( $categorys );
			$string .= $tree->get_tree ( 0, $str );
			include $this->admin_tpl ( 'create_html_category' );
		}

	}

	/**
	 * 生成首页
	 */
	public function public_index() {
		$this->html = Loader::lib ( 'content:html' );
		$size = $this->html->index ();
		showmessage ( L ( 'index_create_finish', array ('size' => byte_format ( $size ) ) ) );
	}

	/**
	 * 批量生成内容页
	 */
	public function batch_show() {
		if (isset ( $_POST ['dosubmit'] )) {
			$catid = intval ( $_GET ['catid'] );
			if (! $catid) showmessage ( L ( 'missing_part_parameters' ) );
			$modelid = $this->categorys [$catid] ['modelid'];
			$setting = string2array ( $this->categorys [$catid] ['setting'] );
			$content_ishtml = $setting ['content_ishtml'];
			if ($content_ishtml) {
				$this->url = Loader::lib ( 'content:url' );
				$this->db->set_model ( $modelid );
				if (empty ( $_POST ['ids'] )) showmessage ( L ( 'you_do_not_check' ) );
				$this->html = Loader::lib ( 'content:html' );
				$ids = implode ( ',', $_POST ['ids'] );
				$rs = $this->db->where(array('catid'=>array('in',$ids)))->select ();
				$tablename = $this->db->table_name . '_data';
				foreach ( $rs as $r ) {
					if ($r ['islink']) continue;
					$this->db->table_name = $tablename;
					$r2 = $this->db->getby_id ( $r ['id'] );
					if ($r2) $r = array_merge ( $r, $r2 );
					// 判断是否为升级或转换过来的数据
					if (! $r ['upgrade']) {
						$urls = $this->url->show ( $r ['id'], '', $r ['catid'], $r ['inputtime'] );
					} else {
						$urls [1] = $r ['url'];
					}
					$this->html->show ( $urls [1], $r, 0, 'edit', $r ['upgrade'] );
				}
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			}
		}
	}
}