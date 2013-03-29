<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 * 数据源调用接口
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: CallController.php 254 2012-11-08 01:00:18Z xutongle $
 */
class CallController {
	private $db;
	public function __construct() {
		$this->db = Loader::model ( 'datacall_model' );
	}
	public function get() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : exit ();
		if ($data = $this->db->getby_id ( $id )) {
			if (! $str = S ( 'dbsource_' . $id )) {
				if ($data ['type'] == 1) { // 自定义SQL调用
					$get_db = Loader::model ( "get_model" );
					$sql = $data['data'].(!empty($data['num']) ? " LIMIT $data[num]" : '');
					$str = $get_db->query ($sql );
				} else {
					$filepath = APPS_PATH . $data ['application'] . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . $data ['application'] . '_tag.php';
					if (file_exists ( $filepath )) {
						$yun_tag = Loader::lib ( $data ['application'].':'.$data ['application'] . '_tag' );
						if (! method_exists ( $yun_tag, $data ['do'] )) exit ();
						$sql = string2array ( $data ['data'] );
						$sql ['do'] = $data ['do'];
						$sql ['limit'] = $data ['num'];
						unset ( $data ['num'] );
						$str = $yun_tag->$data ['do'] ( $sql );
					} else {
						exit ();
					}
				}
				if ($data ['cache']) S ( 'tpl_data/dbsource_' . $id, $str,$data ['cache'] );
			}
			echo $this->_format ( $data ['id'], $str, $data ['dis_type'] );
		}
	}
	private function _format($id, $data, $type) {
		switch ($type) {
			case '1' : // json
				if (CHARSET == 'gbk') $data = array_iconv ( $data, 'gbk', 'utf-8' );
				return json_encode ( $data );
				break;

			case '2' : // xml
				$xml = Loader::lib ( 'Xml' );
				return $xml->xml_serialize ( $data );
				break;

			case '3' : // js
				Loader::func ( 'dbsource:global' );
				ob_start ();
				include template_url ( $id );
				$html = ob_get_contents ();
				ob_clean ();
				return format_js ( $html );
				break;
		}
	}
}