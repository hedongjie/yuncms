<?php
/**
 * 前台广告展示
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 59 2012-11-05 12:48:20Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class IndexController {
	function __construct() {
		$this->db = Loader::model ( 'poster_model' );
		$this->s_db = Loader::model ( 'poster_stat_model' );
	}
	public function init() {
	}

	/**
	 * 统计广告点击次数
	 */
	public function poster_click() {
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : 0;
		$r = $this->db->get_one ( array ('id' => $id ) );
		if (! is_array ( $r ) && empty ( $r )) return false;
		$ip_area = Loader::lib ( 'lib/lib_ip' );
		$ip = IP;
		$area = $ip_area->get ( $ip );
		$username = cookie ( 'username' ) ? cookie ( 'username' ) : '';
		if ($id) {
			$this->s_db->insert ( array ('pid' => $id,'username' => $username,'area' => $area,'ip' => $ip,'referer' => HTTP_REFERER,'clicktime' => TIME,'type' => 1 ) );
		}
		$this->db->update ( array ('clicks' => '+1' ), array ('id' => $id ) );
		$setting = string2array ( $r ['setting'] );
		if (count ( $setting ) == 1) {
			$url = $setting ['1'] ['linkurl'];
		} else {
			$url = isset ( $_GET ['url'] ) ? $_GET ['url'] : $setting ['1'] ['linkurl'];
		}
		header ( 'Location: ' . $url );
	}

	/**
	 * php方式展示广告
	 */
	public function show_poster() {
		if (! $_GET ['id']) exit ();
		$id = intval ( $_GET ['id'] );
		$sdb = Loader::Model ( 'poster_space_model' );
		$now = TIME;
		$r = $sdb->get_one ( array ('spaceid' => $_GET ['id'] ) );
		if ($r ['setting']) $r ['setting'] = string2array ( $r ['setting'] );
		$poster_template = S ( 'common/poster_template' );
		if ($poster_template [$r ['type']] ['option']) {
			$where = "`spaceid`='" . $id . "' AND `disabled`=0 AND `startdate`<='" . $now . "' AND (`enddate`>='" . $now . "' OR `enddate`=0) ";
			$pinfo = $this->db->select ( $where, '*', '', '`listorder` ASC, `id` DESC' );
			if (is_array ( $pinfo ) && ! empty ( $pinfo )) {
				foreach ( $pinfo as $k => $rs ) {
					if ($rs ['setting']) {
						$rs ['setting'] = string2array ( $rs ['setting'] );
						$pinfo [$k] = $rs;
					} else {
						unset ( $pinfo [$k] );
					}
				}
				extract ( $r );
			} else {
				return true;
			}
		} else {
			$where = " `spaceid`='" . $id . "' AND `disabled`=0 AND `startdate`<='" . $now . "' AND (`enddate`>='" . $now . "' OR `enddate`=0)";
			$pinfo = $this->db->get_one ( $where, '*', '`listorder` ASC, `id` DESC' );
			if (is_array ( $pinfo ) && $pinfo ['setting']) {
				$pinfo ['setting'] = string2array ( $pinfo ['setting'] );
			}
			extract ( $r );
			if (! is_array ( $pinfo ) || empty ( $pinfo )) return true;
			extract ( $pinfo, EXTR_PREFIX_SAME, 'p' );
		}
		include template ( 'poster', $type );
	}

	/**
	 * js传值，统计展示次数
	 */
	public function show() {
		$spaceid = $_GET ['spaceid'] ? intval ( $_GET ['spaceid'] ) : 0;
		$id = $_GET ['id'] ? intval ( $_GET ['id'] ) : 0;
		if (! $spaceid || ! $id) {
			exit ( 0 );
		} else {
			$this->show_stat ( $spaceid, $id );
		}
	}

	/**
	 * 统计广告展示次数
	 *
	 * @param intval $spaceid
	 *        	广告版位ID
	 * @param intval $id
	 *        	广告ID
	 * @return boolen
	 */
	protected function show_stat($spaceid = 0, $id = 0) {
		$M = new_htmlspecialchars ( S ( 'common/poster' ) );
		if ($M ['enablehits'] == 0) return true;
		$spaceid = intval ( $spaceid );
		$id = intval ( $id );
		if (! $id) return false;
		if (! $spaceid) {
			$r = $this->db->where ( array ('id' => $id ) )->field ( 'spaceid' )->find ();
			$spaceid = $r ['spaceid'];
		}
		$ip = IP;
		$ip_area = Loader::lib ( 'lib/lib_ip' );
		$area = $ip_area->get ( $ip );
		$username = cookie ( 'username' ) ? cookie ( 'username' ) : '';
		$this->db->where ( array ('id' => $id ) )->update ( array ('hits' => '+1' ) );
		$this->s_db->insert ( array ('pid' => $id,'spaceid' => $spaceid,'username' => $username,'area' => $area,'ip' => $ip,'referer' => HTTP_REFERER,'clicktime' => TIME,'type' => 0 ) );
		return true;
	}
}