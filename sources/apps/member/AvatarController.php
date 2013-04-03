<?php
/**
 * 头像管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-14
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: AvatarController.php 213 2013-03-30 00:00:02Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'member:foreground' );
class AvatarController extends foreground {
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 修改头像
	 */
	public function init(){
		$memberinfo = $this->memberinfo;
		if (ucenter_exists ()) {
			$avatarhtml = Loader::lib ( 'member:uc_client' )->uc_avatar ( $this->memberinfo ['ucenterid'] );
		} else {
			$upurl = base64_encode ( U ( 'Member/Avatar/upload', array ('userid' => $this->memberinfo ['userid'] ) ) );
		}
		$avatar = get_memberavatar ( $this->memberinfo ['userid'], false );
		include template ( 'member', 'avatar' );
	}

	/**
	 * 上传头像
	 */
	public function upload(){
		if (isset ( $_GET ['userid'] ) && isset ( $GLOBALS ['HTTP_RAW_POST_DATA'] )) { // 根据用户id创建文件夹
			$userid = intval ( $_GET ['userid'] );
			$avatardata = $GLOBALS ['HTTP_RAW_POST_DATA'];
		} else {
			exit ( '0' );
		}
		$dir1 = ceil ( $userid / 10000 );
		$dir2 = ceil ( $userid % 10000 / 1000 );
		// 创建图片存储文件夹
		$avatarfile = DATA_PATH . 'avatar/';
		$dir = $avatarfile . $dir1 . '/' . $dir2 . '/' . $userid . '/';
		if (! file_exists ( $dir )) Folder::create ( $dir );
		$filename = $dir . $userid . '.zip';
		File::write ( $filename, $avatardata );

		$archive = new PclZip ( $filename );
		if ($archive->extract ( PCLZIP_OPT_PATH, $dir ) == 0) die ( "Error : " . $archive->errorInfo ( true ) );
		// 判断文件安全，删除压缩包和非jpg图片
		$avatararr = array ('180x180.jpg','30x30.jpg','45x45.jpg','90x90.jpg' );
		if ($handle = opendir ( $dir )) {
			while ( false !== ($file = readdir ( $handle )) ) {
				if ($file !== '.' && $file !== '..') {
					if (! in_array ( $file, $avatararr )) {
						File::del ( $dir . $file );
					} else {
						$info = @getimagesize ( $dir . $file );
						if (! $info || $info [2] != 2) File::del ( $dir . $file );
					}
				}
			}
			closedir ( $handle );
		}
		$this->db->where(array ('userid' => $userid ))->update ( array ('avatar' => 1 ) );
		exit ( '1' );
	}
}