<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: global.php 200 2013-03-29 23:15:00Z 85825770@qq.com $
 */

/**
 * 解析评论ID
 *
 * @param $commentid 评论ID
 */
function decode_commentid($commentid) {
	return explode ( '-', $commentid );
}

/**
 * 通过API接口调用标题和URL数据
 *
 * @param string $commentid
 *        	评论ID
 * @return array($title, $url) 返回数据
 */
function get_comment_api($commentid) {
	list ( $applications, $contentid ) = id_decode ( $commentid );
	if (empty ( $applications ) || empty ( $contentid )) {
		return false;
	}
	$comment_api = '';
	$application = explode ( '_', $applications );
	$comment_api = Loader::lib ( $application [0] . ':comment_api' );
	if (empty ( $comment_api ))
		return false;
	return $comment_api->get_info ( $applications, $contentid );
}