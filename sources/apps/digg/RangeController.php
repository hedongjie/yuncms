<?php
defined('IN_YUNCMS') or exit('No permission resources.');
Loader::lib ( 'admin:admin', false );
class RangeController extends admin {
	public function __construct() {
		parent::__construct();
		$this->db = Loader::model('digg_model');
	}

	/**
	 * 排行榜查看
	 */
	public function init() {
		$where = array();
		$catid = isset($_GET['catid']) &&  intval($_GET['catid']) ? intval($_GET['catid']) : '';
		if(!empty($catid)) $sql .= "catid = $catid AND ";
		$datetype = isset($_GET['datetype']) &&  intval($_GET['datetype']) ? intval($_GET['datetype']) : 0;
		$order = isset($_GET['order']) &&  trim($_GET['order']) ? trim($_GET['order']) : -1;
		switch ($datetype) {
			case 1://今天
				$where['updatetime'] = array('between',array((strtotime ( date ( 'Y-m-d' ) . " 00:00:00" )),(strtotime ( date ( 'Y-m-d' ) . " 23:59:59" ))));
				break;

			case 2://昨天
				$where['updatetime'] = array('between',array((strtotime ( date ( 'Y-m-d' ) . " 00:00:00" ) - 86400),(strtotime ( date ( 'Y-m-d' ) . " 23:59:59" ) - 86400)));
				break;

			case 3://本周
				$week = date('w');
				if (empty($week)) $week = 7;
				$where['updatetime'] = array('between',array((strtotime ( date ( 'Y-m-d' ) . " 23:59:59" ) - 86400 * $week),(strtotime ( date ( 'Y-m-d' ) . " 23:59:59" ) + (86400 * (7 - $week)))));
				break;

			case 4://本月
				$day = date('t');
				$where['updatetime'] = array('between',array(strtotime ( date ( 'Y-m-1' ) . " 00:00:00" ),strtotime ( date ( 'Y-m-' . $day ) . " 23:59:59" )));
				break;

			case 5://所有
				$where['updatetime'] = array('elt',TIME);
				break;
		}
		$sql_order = '';
		if ($order == 'supports') {
			$sql_order = " `supports` desc";
		} elseif ($order) {
			$sql_order = " `againsts` desc";
		}
		$page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
		$data = $this->db->where($where)->order($sql_order)->listinfo($page);
		$pages = $this->db->pages;
		$order_list = array('supports'=>L('supports'),'againsts'=>L('againsts'));
		include $this->admin_tpl('digg_list');
	}
}