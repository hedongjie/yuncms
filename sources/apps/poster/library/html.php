<?php
/**
 * 广告生成
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: html.php 59 2012-11-05 12:48:20Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class html {
	private $db, $s_db;

	public function __construct() {
		$this->s_db = Loader::model ( 'poster_space_model' );
		$this->db = Loader::model ( 'poster_model' );
	}

	/**
	 * 生成广告js文件
	 *
	 * @param intval $id
	 *        	广告版位ID
	 * @return boolen 成功返回true
	 */
	public function create_js($id = 0) {
		$id = intval ( $id );
		if (! $id) {
			$this->msg = L ( 'no_create_js' );
			return false;
		}
		$r = $this->s_db->getby_spaceid ( $id );
		$now = TIME;
		if ($r ['setting']) $space_setting = string2array ( $r ['setting'] );
		if ($r ['type'] == 'code') return true;
		$poster_template = S ( 'common/poster_template' );
		if ($poster_template [$r ['type']] ['option']) {
			$where = "`spaceid`='" . $id . "' AND `disabled`=0 AND `startdate`<='" . $now . "' AND (`enddate`>='" . $now . "' OR `enddate`=0) ";
			$pinfo = $this->db->where($where)->order('listorder ASC, id DESC')->select ( );
			if (is_array ( $pinfo ) && ! empty ( $pinfo )) {
				foreach ( $pinfo as $k => $rs ) {
					if ($rs ['setting']) {
						$rs ['setting'] = string2array ( $rs ['setting'] );
						$pinfo [$k] = $rs;
					} else
						unset ( $pinfo [$k] );
				}
				extract ( $r );
			} else
				return true;
		} else {
			$where = " `spaceid`='" . $id . "' AND `disabled`=0 AND `startdate`<='" . $now . "' AND (`enddate`>='" . $now . "' OR `enddate`=0)";
			$pinfo = $this->db->where($where)->order('listorder ASC, id DESC')->find();
			if (is_array ( $pinfo ) && isset($pinfo ['setting'])) $pinfo ['setting'] = string2array ( $pinfo ['setting'] );
			extract ( $r );
			if (! is_array ( $pinfo ) || empty ( $pinfo )) return true;
			extract ( $pinfo, EXTR_PREFIX_SAME, 'p' );
		}
		$file = DATA_PATH . $path;
		ob_start ();
		include template ( 'poster', $type );
		$data = ob_get_contents ();
		ob_end_clean ();
		File::write ( $file, $data );
		return true;
	}
}