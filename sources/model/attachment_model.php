<?php
/**
 * 附件表
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class attachment_model extends Model {
	public $table_name = '';

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'attachment';
		parent::__construct ();
	}

	public function api_add($uploadedfile) {
		$uploadfield = array ();
		$uploadfield = $uploadedfile;
		unset ( $uploadfield ['fn'] );
		$uploadfield = new_addslashes ( $uploadfield );
		$this->insert ( $uploadfield );
		$aid = $this->insert_id ();
		$uploadedfile ['aid'] = $aid;
		return $aid;
	}

	/**
	 * 附件更新接口.
	 *
	 * @param string $content 可传入空，html，数组形式url，url地址，传入空时，以cookie方式记录。
	 * @param string 传入附件关系表中的组装id
	 *        @isurl intval 为本地地址时设为1,以cookie形式管理时设置为2
	 */
	public function api_update($content, $keyid, $isurl = 0) {
		if (! C ( 'attachment', 'stat' )) return false;
		$keyid = trim ( $keyid );
		$isurl = intval ( $isurl );
		if ($isurl == 2 || empty ( $content )) {
			$this->api_update_cookie ( $keyid );
		} else {
			$att_index_db = Loader::model ( 'attachment_index_model' );
			$upload_url = C ( 'attachment', 'upload_url' );
			if (strpos ( $upload_url, '://' ) !== false) {
				$pos = strpos ( $upload_url, "/", 8 );
				$domain = substr ( $upload_url, 0, $pos ) . '/';
				$dir_name = substr ( $upload_url, $pos + 1 );
			}
			if ($isurl == 0) {
				$pattern = '/(href|src)=\"(.*)\"/isU';
				preg_match_all ( $pattern, $content, $matches );
				if (is_array ( $matches ) && ! empty ( $matches )) {
					$att_arr = array_unique ( $matches [2] );
					foreach ( $att_arr as $_k => $_v )
						$att_arrs [$_k] = md5 ( str_replace ( array ($domain,$dir_name ), '', $_v ) );
				}
			} elseif ($isurl == 1) {
				if (is_array ( $content )) {
					$att_arr = array_unique ( $content );
					foreach ( $att_arr as $_k => $_v )
						$att_arrs [$_k] = md5 ( str_replace ( array ($domain,$dir_name ), '', $_v ) );
				} else {
					$att_arrs [] = md5 ( str_replace ( array ($domain,$dir_name ), '', $content ) );
				}
			}
			$att_index_db->where(array ('keyid' => $keyid ))->delete (  );
			if (is_array ( $att_arrs ) && ! empty ( $att_arrs )) {
				foreach ( $att_arrs as $r ) {
					$infos = $this->where ( array ('authcode' => $r ) )->field('aid')->find();
					if ($infos) {
						$this->where(array ('aid' => $infos ['aid'] ) )->update ( array ('status' => 1 ));
						$att_index_db->insert ( array ('keyid' => $keyid,'aid' => $infos ['aid'] ) );
					}
				}
			}
		}
		cookie ( 'att_json', '' );
		return true;
	}

	/**
	 * cookie 方式关联附件
	 */
	private function api_update_cookie($keyid) {
		if (! C ( 'attachment', 'stat' )) return false;
		$att_index_db = Loader::model ( 'attachment_index_model' );
		$att_json = cookie ( 'att_json' );
		if ($att_json) {
			$att_cookie_arr = explode ( '||', $att_json );
			$att_cookie_arr = array_unique ( $att_cookie_arr );
		} else {
			return false;
		}
		foreach ( $att_cookie_arr as $_att_c )
			$att [] = json_decode ( $_att_c, true );
		foreach ( $att as $_v ) {
			$this->where(array ('aid' => $_v ['aid'] ))->update ( array ('status' => 1 ) );
			$att_index_db->insert ( array ('keyid' => $keyid,'aid' => $_v ['aid'] ) );
		}
	}

	/*
	 * 附件删除接口 @param string 传入附件关系表中的组装id
	 */
	public function api_delete($keyid) {
		if (C ( 'attachment', 'stat' )) return false;
		$keyid = trim ( $keyid );
		if ($keyid == '') return false;
		$att_index_db = Loader::model ( 'attachment_index_model' );
		$attachment = Loader::lib ( 'helper/helper_attachment' );
		$info = $att_index_db->where(array ('keyid' => $keyid ))->key('aid')->select (  );
		if ($info) {
			$att_index_db->where ( array ('keyid' => $keyid ) )->delete();
			foreach ( $info as $_v ) {
				if (! $att_index_db->getby_aid ( $_v ['aid'] )) {
					$attachment->where ( array ('aid' => $_v ['aid'] ) )->delete();
				}
			}
			return true;
		} else {
			return false;
		}
	}
}