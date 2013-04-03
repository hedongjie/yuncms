<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 * 评论审核
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: CheckController.php 273 2013-04-01 09:30:54Z 85825770@qq.com $
 */
class CheckController extends admin {
	// 数据库连接
	private $comment_check_db, $comment_db, $comment;
	public function __construct() {
		$this->comment_data_db = Loader::model ( 'comment_data_model' );
		$this->comment_check_db = Loader::model ( 'comment_check_model' );
		$this->comment = Loader::lib ( 'comment/comment' );
	}
	public function checks() {
		$total = $this->comment_check_db->count ( '' );
		$comment_check_data = $this->comment_check_db->order ( 'id desc' )->select ();
		if (empty ( $comment_check_data )) showmessage ( L ( 'no_check_comments' ) . '<script>window.top.$("#display_center_id").css("display","none");</script>' );
		$show_header = true;
		include $this->admin_tpl ( 'comment_check' );
	}
	public function ajax_checks() {
		$id = isset ( $_GET ['id'] ) && $_GET ['id'] ? $_GET ['id'] : (isset ( $_GET ['form'] ) ? showmessage ( L ( 'please_chose_comment' ), HTTP_REFERER ) : exit ( '0' ));
		$type = isset ( $_GET ['type'] ) && intval ( $_GET ['type'] ) ? intval ( $_GET ['type'] ) : exit ( '0' );
		$commentid = isset ( $_GET ['commentid'] ) && trim ( $_GET ['commentid'] ) ? trim ( $_GET ['commentid'] ) : exit ( '0' );
		if (is_array ( $id )) {
			foreach ( $id as $v ) {
				if (! $v = intval ( $v )) {
					continue;
				}
				$this->comment->status ( $commentid, $v, $type );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$id = intval ( $id ) ? intval ( $id ) : exit ( '0' );
			$this->comment->status ( $commentid, $id, $type );
		}
		if ($comment->msg_code != 0) {
			exit ( $comment->get_error () );
		} else {
			exit ( '1' );
		}
	}
	public function public_get_one() {
		$total = $this->comment_check_db->count ();
		$comment_check_data = $this->comment_check_db->where ( 'id desc' )->select ( '', '*', '19,1' );
		$comment_check_data = $comment_check_data [0];
		$r = array ();
		if (is_array ( $comment_check_data ) && ! empty ( $comment_check_data )) {
			$this->comment_data_db->table_name ( $comment_check_data ['tableid'] );
			$r = $this->comment_data_db->get_one ( array ('id' => $comment_check_data ['comment_data_id'] ) );
			$r ['creat_at'] = Format::date ( $r ['creat_at'], 1 );
			if (CHARSET == 'gbk') {
				foreach ( $r as $k => $v ) {
					$r [$k] = iconv ( 'gbk', 'utf-8', $v );
				}
			}
		}
		echo json_encode ( array ('total' => $total,'data' => $r ) );
	}
}