<?php
/**
 * 公告前台
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: IndexController.php 304 2012-11-11 01:22:54Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class IndexController {
	public $db;
	public function __construct() {
		$this->db = Loader::model ( 'announce_model' );
	}
	public function init() {
	}

	/**
	 * 展示公告
	 */
	public function show() {
		if (! isset ( $_GET ['aid'] )) showmessage ( L ( 'illegal_operation' ) );
		$_GET ['aid'] = intval ( $_GET ['aid'] );
		$where = array ('aid' => $_GET ['aid'],'passed' => '1','endtime' => array (array ('gt',date ( 'Y-m-d' ) ),array ('eq','0000-00-00' ),'or' ) );
		$r = $this->db->where ( $where )->find ();
		if ($r ['aid']) {
			$this->db->where ( array ('aid' => $r ['aid'] ) )->update ( array ('hits' => '+=1' ) );
			$template = $r ['show_template'] ? $r ['show_template'] : 'show';
			extract ( $r );
			$SEO = seo ( '', $title );
			include template ( 'announce', $template, $r ['style'] );
		} else {
			showmessage ( L ( 'no_exists' ) );
		}
	}
}