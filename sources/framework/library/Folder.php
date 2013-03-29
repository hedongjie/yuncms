<?php
/**
 * Folder class
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Folder.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Folder {
	private static $_errormsgs = array ();

	/**
	 * 转义路径
	 *
	 * @param string $path
	 * @return string
	 */
	public static function path($path) {
		return rtrim ( preg_replace ( "|[\/]+|", DIRECTORY_SEPARATOR, $path ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
	}

	/**
	 * 创建目录
	 *
	 * @param string $structure
	 * @param string $mode
	 */
	public static function create($structure, $mode = 0755) {
		if (is_dir ( $structure ) || $structure == '') {
			return true;
		}
		if (self::create ( dirname ( $structure ), $mode )) {
			return @mkdir ( $structure, $mode );
		} else {
			self::$_errormsgs [] = sprintf ( 'can not mkdir %s', $structure );
			return false;
		}
	}

	/**
	 * 删除目录及目录下面的所有文件
	 *
	 * @param string $path
	 * @return bool TRUE，失败则返回 FALSE
	 */
	static function delete($path) {
		$path = self::path ( $path );
		if (! is_dir ( $path )) return false;
		$items = glob ( $path . '*' );
		if (! is_array ( $items )) return true;
		foreach ( $items as $v ) {
			if (is_dir ( $v )) {
				self::delete ( $v );
			} else {
				if (! @unlink ( $v )) {
					self::$_errormsgs [] = sprintf ( 'can not delete file %s', $v );
					return false;
				}
			}
		}
		if (! @rmdir ( $path )) {
			self::$_errormsgs [] = sprintf ( 'can not rmdir %s', $path );
			return false;
		}
		return true;
	}

	/**
	 * 清除文件夹下所有文件以及文件夹
	 *
	 * @param string $path
	 */
	static function clear($path) {
		if (! is_dir ( $path )) return false;
		$path = self::path ( $path );
		$items = glob ( $path . '*' );
		if (! is_array ( $items )) return true;
		foreach ( $items as $v ) {
			if (is_dir ( $v )) {
				self::delete ( $v );
			} else {
				if (! @unlink ( $v )) {
					self::$_errormsgs [] = sprintf ( 'can not delete file %s', $v );
					return false;
				}
			}
		}
		return true;
	}

	static function rename($oldpath, $newpath) {
		return rename ( $oldpath, $newpath );
	}

	/**
	 * 移动目录文件到另外一个目录
	 *
	 * @param string $source
	 *        	原目录
	 * @param string $target
	 *        	新目录
	 * @throws ct_exception
	 */
	static function move($source, $target) {
		if (! is_dir ( $source )) return false;
		if (! is_dir ( $target )) self::create ( $target );
		$source = self::path ( $source );
		$target = self::path ( $target );
		$items = glob ( $source . '*' );
		if (! is_array ( $items )) return true;
		foreach ( $items as $v ) {
			$basename = basename ( $v );
			$to = $target . DIRECTORY_SEPARATOR . $basename;
			if (is_dir ( $v )) {
				self::move ( $v, $to );
			} else {
				if (! @rename ( $v, $to )) {
					self::$_errormsgs [] = sprintf ( 'can not move file %s to %s', $v, $to );
					return false;
				}
			}
		}
		if (! @rmdir ( $source )) throw new Exception ( "can not rmdir $source" );
		return true;
	}

	/**
	 * 拷贝目录及下面所有文件
	 *
	 * @param string $source
	 * @param string $target
	 * @param string $mode
	 * @return string
	 */
	static function copy($source, $target, $mode = 0755) {
		if (PHP_OS == 'WINNT') $mode = null;
		$source = self::path ( $source );
		$target = self::path ( $target );
		if (! is_dir ( $source )) return false;
		if (! is_dir ( $target )) self::create ( $target );
		$items = glob ( $source . '*' );
		if (! is_array ( $items )) return true;
		foreach ( $items as $v ) {
			$path = $target . DIRECTORY_SEPARATOR . basename ( $v );
			if (is_dir ( $v )) {
				self::copy ( $v, $path );
			} else {
				if (! @copy ( $v, $path )) {
					self::$_errormsgs [] = sprintf ( 'can not copy file %s to %s', $v, $path );
					return false;
				} else {
					@chmod ( $path, $mode );
				}
			}
		}
		return true;
	}

	/**
	 * 转换目录下面的所有文件编码格式
	 *
	 * @param string $in_charset
	 * @param string $out_charset
	 * @param string $dir
	 * @param string $fileexts
	 * @return string
	 */
	static function dir_iconv($in_charset, $out_charset, $dir, $fileexts = 'php|html|htm|shtml|shtm|js|txt|xml') {
		if ($in_charset == $out_charset) return false;
		$list = self::dir_list ( $dir );
		foreach ( $list as $v ) {
			if (preg_match ( "/\.($fileexts)/i", $v ) && is_file ( $v )) {
				file_put_contents ( $v, iconv ( $in_charset, $out_charset, file_get_contents ( $v ) ) );
			}
		}
		return true;
	}

	/**
	 * 列出目录下所有文件
	 *
	 * @param string $path
	 * @param string $exts
	 * @param array $list
	 * @return array
	 */
	static function dir_list($path, $exts = '', $list = array()) {
		$path = self::path ( $path );
		$files = glob ( $path . '*' );
		foreach ( $files as $v ) {
			$fileext = self::file_ext ( $v );
			if (! $exts || preg_match ( "/\.($exts)/i", $v )) {
				$list [] = $v;
				if (is_dir ( $v )) {
					$list = self::dir_list ( $v, $exts, $list );
				}
			}
		}
		return $list;
	}

	static function chmod($path, $mode = 0755) {
		if (! is_dir ( $path )) return false;
		$mode = intval ( $mode, 8 );
		if (! @chmod ( $path, $mode )) {
			self::$_errormsgs [] = sprintf ( '%s not changed to %s', $path, $mode );
		}
		$path = self::path ( $path );
		$items = glob ( $path . '*' );
		if (! is_array ( $items )) return true;
		foreach ( $items as $item ) {
			if (is_dir ( $item )) {
				self::chmod ( $item, $mode );
			} else {
				if (! @chmod ( $item, $mode )) {
					self::$_errormsgs [] = sprintf ( '%s not changed to %s', $item, $mode );
				}
			}
		}
		return true;
	}

	/**
	 * 设置目录下面的所有文件的访问和修改时间
	 *
	 * @param string $path
	 * @param int $mtime
	 * @param int $atime
	 * @return array true
	 */
	static function touch($path, $mtime = TIME, $atime = TIME) {
		if (! is_dir ( $path )) return false;
		if (! @touch ( $path, $mtime, $atime )) {
			self::$_errormsgs [] = sprintf ( '%s not touch to %s', $path, $mtime );
		}
		$path = self::path ( $path );
		if (! is_dir ( $path )) touch ( $path, $mtime, $atime );
		$items = glob ( $path . '*' );
		if (! is_array ( $items )) return true;
		foreach ( $items as $item ) {
			if (is_dir ( $item )) {
				self::touch ( $path, $mtime, $atime );
			} else {
				if (! @touch ( $item, $mtime, $atime )) {
					self::$_errormsgs [] = sprintf ( '%s not touch to %s', $item, $mtime );
				}
			}
		}
		return true;
	}

	/**
	 * 目录列表
	 *
	 * @param string $path
	 * @param int $parentid
	 * @param array $dirs
	 * @return array
	 */
	static function tree($path, $parentid = 0, $dirs = array()) {
		global $id;
		if ($parentid == 0) $id = 0;
		if (! is_dir ( $path )) return false;
		$path = self::path ( $path );
		$items = glob ( $path . '*' );
		if (! is_array ( $items )) return $dirs;
		foreach ( $items as $item ) {
			if (is_dir ( $item )) {
				$id ++;
				$dirs [$id] = array ('id' => $id,'parentid' => $parentid,'name' => basename ( $item ),'dir' => $item . '/' );
				$dirs = self::tree ( $item, $id, $dirs );
			}
		}
		return $dirs;
	}

	/**
	 * 返回错误信息
	 *
	 * @return string 错误信息
	 */
	static function errormsgs() {
		return self::$_errormsgs;
	}
}