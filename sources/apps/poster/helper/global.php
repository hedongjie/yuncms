<?php
/**
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: global.php 59 2012-11-05 12:48:20Z xutongle $
 */
/**
 * 广告模板配置函数
 */
function get_types() {
	$poster_template = S ( 'common/poster_template' );
	$TYPES = array ();
	if (is_array ( $poster_template ) && ! empty ( $poster_template )) {
		foreach ( $poster_template as $k => $template ) {
			$TYPES [$k] = $template ['name'];
		}
	} else {
		$TYPES = array ('banner' => L ( 'banner', '', 'poster' ),'fixure' => L ( 'fixure' ),'float' => L ( 'float' ),'couplet' => L ( 'couplet' ),'imagechange' => L ( 'imagechange' ),'imagelist' => L ( 'imagelist' ),'text' => L ( 'text' ),'code' => L ( 'code' ) );
	}
	return $TYPES;
}