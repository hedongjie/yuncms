<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 * 新闻心情
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Mood_adminController.php 1112 2012-10-23 23:22:20Z
 *          85825770@qq.com $
 */
class Mood_adminController extends admin {

	private $db;
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'mood_model' );
	}

	// 排行榜查看
	public function init() {
		$mood_program = S ( 'common/mood_program' );
		$catid = isset ( $_GET ['catid'] ) && intval ( $_GET ['catid'] ) ? intval ( $_GET ['catid'] ) : '';
		$datetype = isset ( $_GET ['datetype'] ) && intval ( $_GET ['datetype'] ) ? intval ( $_GET ['datetype'] ) : 0;
		$order = isset ( $_GET ['order'] ) && intval ( $_GET ['order'] ) ? intval ( $_GET ['order'] ) : 0;
		$where = array();
		if ($catid) {
			switch ($datetype) {
				case 1 : // 今天
					$where['lastupdate'] = array('between',array((strtotime ( date ( 'Y-m-d' ) . " 00:00:00" )),(strtotime ( date ( 'Y-m-d' ) . " 23:59:59" ))));
					break;

				case 2 : // 昨天
					$where['lastupdate'] = array('between',array((strtotime ( date ( 'Y-m-d' ) . " 00:00:00" ) - 86400),(strtotime ( date ( 'Y-m-d' ) . " 23:59:59" ) - 86400)));
					break;

				case 3 : // 本周
					$week = date ( 'w' );
					if (empty ( $week )) $week = 7;
					$where['lastupdate'] = array('between',array((strtotime ( date ( 'Y-m-d' ) . " 23:59:59" ) - 86400 * $week),(strtotime ( date ( 'Y-m-d' ) . " 23:59:59" ) + (86400 * (7 - $week)))));
					break;

				case 4 : // 本月
					$day = date ( 't' );
					$where['lastupdate'] = array('between',array(strtotime ( date ( 'Y-m-1' ) . " 00:00:00" ),strtotime ( date ( 'Y-m-' . $day ) . " 23:59:59" )));
					break;

				case 5 : // 所有
					$where['lastupdate'] = array('elt',TIME);
					break;
			}
			$sql_order = '';
			if ($order == '-1') {
				$sql_order = " `total` desc";
			} elseif ($order) {
				$sql_order = "`n$order` desc";
			}
			$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
			$data = $this->db->where($where)->order($sql_order)->listinfo ( $page );
			$content_db = Loader::model ( 'content_model' );
			$contentid = '';
			foreach ( $data as $v ) {
				$contentid .= $contentid ? "','" . $v ['contentid'] : $v ['contentid'];
			}
			$content_db->set_catid ( $catid );
			$content_data = $content_db->where ( array ('id' => array ('in',$contentid ) ) )->field ( 'id,url,title' )->listinfo ();
			foreach ( $content_data as $k => $v ) {
				$content_data [$v ['id']] = array ('title' => $v ['title'],'url' => $v ['url'] );
				unset ( $content_data [$k] );
			}
			$pages = $content_db->pages;
		}
		$order_list = array ('-1' => L ( 'total' ) );
		foreach ( $mood_program as $k => $v ) {
			$order_list [$k] = $v ['name'];
		}
		include $this->admin_tpl ( 'mood_list' );
	}

	// 配置
	public function setting() {
		$mood_program = S ( 'common/mood_program' );
		if (isset ( $_POST ['dosubmit'] )) {
			$use = isset ( $_POST ['use'] ) ? $_POST ['use'] : '';
			$name = isset ( $_POST ['name'] ) ? $_POST ['name'] : '';
			$pic = isset ( $_POST ['pic'] ) ? $_POST ['pic'] : '';
			$data = array ();
			foreach ( $name as $k => $v ) {
				$data [$k] = array ('use' => $use [$k],'name' => $v,'pic' => $pic [$k] );
			}
			S ( 'common/mood_program', $data );
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			include $this->admin_tpl ( 'mood_setting' );
		}
	}
}