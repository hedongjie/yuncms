<?php
/**
 * 碎片TAG
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: block_tag.php 71 2012-11-05 12:51:29Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class block_tag {

	private $db;

	public function __construct() {
		$this->db = Loader::model ( 'block_model' );
	}

	/**
	 * XT标签中调用数据
	 *
	 * @param array $data 配置数据
	 */
	public function yun_tag($data) {
		$r = $this->db->where ( array ('pos' => $data ['pos'] ) )->select();
		$str = '';
		if (! empty ( $r ) && is_array ( $r )) foreach ( $r as $v ) {
			if (defined ( 'IN_ADMIN' ) && ! defined ( 'HTML' )) $str .= '<div id="block_id_' . $v ['id'] . '" class="admin_block" blockid="' . $v ['id'] . '">';
			if ($v ['type'] == '2') {
				extract ( $v, EXTR_OVERWRITE );
				$data = string2array ( $data );
				if (! defined ( 'HTML' )) {
					ob_start ();
					include $this->template_url ( $id );
					$str .= ob_get_contents ();
					ob_clean ();
				} else {
					include $this->template_url ( $id );
				}
			} else {
				$str .= $v ['data'];
			}
			if (defined ( 'IN_ADMIN' ) && ! defined ( 'HTML' )) $str .= '</div>';
		}
		return $str;
	}

	/**
	 * 生成模板返回路径
	 *
	 * @param integer $id 碎片ID号
	 * @param string $template 风格
	 */
	public function template_url($id, $template = '') {
		$filepath = CACHE_PATH . 'template' . DIRECTORY_SEPARATOR . 'block' . DIRECTORY_SEPARATOR . $id . '.php';
		$dir = dirname ( $filepath );
		if ($template) {
			if (! is_dir ( $dir )) mkdir ( $dir, 0777, true );
			$tpl = loader::lib ( 'Template' );
			$str = $tpl->template_parse ( new_stripslashes ( $template ) );
			@file_put_contents ( $filepath, $str );
		} else {
			if (! file_exists ( $filepath )) {
				if (! is_dir ( $dir )) mkdir ( $dir, 0777, true );
				$tpl = loader::lib ( 'Template' );
				$str = $this->db->get_one ( array ('id' => $id ), 'template' );
				$str = $tpl->template_parse ( $str ['template'] );
				@file_put_contents ( $filepath, $str );
			}
		}
		return $filepath;
	}
}