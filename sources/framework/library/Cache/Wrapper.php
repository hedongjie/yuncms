<?php
/**
 * 缓存流
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Wrapper.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Cache_Wrapper { // implements WrapperInterface

	public $dir_mode = 16895; // 040000 + 0222;
	public $file_mode = 33279; // 0100000 + 0777;

	public function __construct() {

	}

	public function stream_open($path, $mode, $options, &$opened_path) {
		$this->position = 0;
		$this->key = trim ( substr ( $path, 8 ) );
		$this->mode = $mode;
		$this->options = $options;

		if (in_array ( $this->mode, array ('r','r+','rb' ) )) {
			if ($this->cachecontent = S ( $this->key )) {
				$this->get_file_info ( $this->key );
				$this->stat ['mode'] = $this->stat [2] = $this->file_mode;
			} else {
				trigger_error ( "fopen({$path}): failed to read from Cache: No such key.", E_USER_WARNING );
				return false;
			}
		} elseif (in_array ( $this->mode, array ('a','a+','ab' ) )) {
			if ($this->cachecontent = S ( $this->key )) {
				$this->get_file_info ( $this->key );
				$this->stat ['mode'] = $this->stat [2] = $this->file_mode;
				$this->position = strlen ( $this->cachecontent );
			} else {
				$this->cachecontent = '';
				$this->stat ['ctime'] = $this->stat [10] = time ();
			}
		} elseif (in_array ( $this->mode, array ('x','x+','xb' ) )) {
			if (! S ( $this->key )) {
				$this->cachecontent = '';
				$this->statinfo_init ();
				$this->stat ['ctime'] = $this->stat [10] = time ();
			} else {
				trigger_error ( "fopen({$path}): failed to create at Cache: Key exists.", E_USER_WARNING );
				return false;
			}
		} elseif (in_array ( $this->mode, array ('w','w+','wb' ) )) {
			$this->cachecontent = '';
			$this->statinfo_init ();
			$this->stat ['ctime'] = $this->stat [10] = time ();
		} else {
			$this->cachecontent = S ( $this->key );
		}

		return true;
	}

	public function stream_read($count) {
		if (in_array ( $this->mode, array ('w','x','a','wb','xb','ab' ) )) {
			return false;
		}
		$ret = substr ( $this->cachecontent, $this->position, $count );
		$this->position += strlen ( $ret );
		$this->stat ['atime'] = $this->stat [8] = time ();
		$this->stat ['uid'] = $this->stat [4] = 0;
		$this->stat ['gid'] = $this->stat [5] = 0;
		return $ret;
	}

	public function stream_write($data) {
		if (in_array ( $this->mode, array ('r','rb' ) )) {
			return false;
		}

		$left = substr ( $this->cachecontent, 0, $this->position );
		$right = substr ( $this->cachecontent, $this->position + strlen ( $data ) );
		$this->cachecontent = $left . $data . $right;

		if (S ( $this->key, $this->cachecontent )) {
			$this->stat ['mtime'] = $this->stat [9] = time ();
			$this->position += strlen ( $data );
			return $this->stat ['size'] = $this->stat [7] = strlen ( $data );
		} else
			return false;
	}

	public function stream_close() {
		S ( $this->key . '.meta', serialize ( $this->stat ) );
	}

	public function stream_eof() {
		return $this->position >= strlen ( $this->cachecontent );
	}

	public function stream_tell() {

		return $this->position;
	}

	public function stream_seek($offset, $whence = SEEK_SET) {

		switch ($whence) {
			case SEEK_SET :

				if ($offset < strlen ( $this->cachecontent ) && $offset >= 0) {
					$this->position = $offset;
					return true;
				} else
					return false;

				break;

			case SEEK_CUR :

				if ($offset >= 0) {
					$this->position += $offset;
					return true;
				} else
					return false;

				break;

			case SEEK_END :

				if (strlen ( $this->cachecontent ) + $offset >= 0) {
					$this->position = strlen ( $this->cachecontent ) + $offset;
					return true;
				} else
					return false;

				break;

			default :

				return false;
		}
	}

	public function stream_stat() {
		return $this->stat;
	}

	// ============================================
	public function mkdir($path, $mode, $options) {
		$path = trim ( substr ( $path, 8 ) );

		// echo "回调mkdir\n";
		$path = rtrim ( $path, '/' );

		$this->stat = $this->get_file_info ( $path );
		$this->stat ['ctime'] = $this->stat [10] = time ();
		$this->stat ['mode'] = $this->stat [2] = $this->dir_mode;

		// echo "生成新的stat数据" . print_r( $this->stat , 1 );

		S ( $path . '.meta', serialize ( $this->stat ) );

		// echo "写入MC. key= " . $path.'.meta ' . memcache_get( $this->mc ,
		// $path.'.meta' );
		return true;
	}

	public function rename($path_from, $path_to) {
		$path_from = trim ( substr ( $path_from, 8 ) );
		$path_to = trim ( substr ( $path_to, 8 ) );

		S ( $path_to, S ( $path_from ) );
		S ( $path_to . '.meta', S ( $path_from . '.meta' ) );
		S ( $path_from ,'');
		S ( $path_from . '.meta' ,'');
		clearstatcache ( true );
		return true;
	}

	public function rmdir($path, $options) {
		$path = trim ( substr ( $path, 8 ) );

		$path = rtrim ( $path, '/' );

		S ( $path . '.meta','' );
		clearstatcache ( true );
		return true;
	}

	public function unlink($path) {
		$path = trim ( substr ( $path, 8 ) );
		$path = rtrim ( $path, '/' );
		S ( $path ,'');
		S ( $path . '.meta','' );
		clearstatcache ( true );
		return true;
	}

	public function url_stat($path, $flags) {
		$path = trim ( substr ( $path, 8 ) );
		$path = rtrim ( $path, '/' );

		if (! $this->is_file_info_exists ( $path )) {
			return false;
		} else {
			if ($stat = S ( $path . '.meta' )) {
				$this->stat = unserialize ( $stat );
				if (is_array ( $this->stat )) {
					if ($this->stat ['mode'] == $this->dir_mode || $c = S ( $path )) {
						return $this->stat;
					} else {
						S ( $path . '.meta','' );
					}
				}
			}
			return false;
		}
	}

	// ============================================

	public function is_file_info_exists($path) {
		// echo "获取MC数据 key= " . $path.'.meta' ;
		$d = S ( $path . '.meta' );
		// echo "\n返回数据为" . $d . "\n";
		return $d;
	}

	public function get_file_info($path) {
		if ($stat = S ( $path . '.meta' ))
			return $this->stat = unserialize ( $stat );
		else
			$this->statinfo_init ();
	}

	public function statinfo_init($is_file = true) {
		$this->stat ['dev'] = $this->stat [0] = 0x8002;
		$this->stat ['ino'] = $this->stat [1] = mt_rand ( 10000, PHP_INT_MAX );

		if ($is_file)
			$this->stat ['mode'] = $this->stat [2] = $this->file_mode;
		else
			$this->stat ['mode'] = $this->stat [2] = $this->dir_mode;

		$this->stat ['nlink'] = $this->stat [3] = 0;
		$this->stat ['uid'] = $this->stat [4] = 0;
		$this->stat ['gid'] = $this->stat [5] = 0;
		$this->stat ['rdev'] = $this->stat [6] = 0;
		$this->stat ['size'] = $this->stat [7] = 0;
		$this->stat ['atime'] = $this->stat [8] = 0;
		$this->stat ['mtime'] = $this->stat [9] = 0;
		$this->stat ['ctime'] = $this->stat [10] = 0;
		$this->stat ['blksize'] = $this->stat [11] = 0;
		$this->stat ['blocks'] = $this->stat [12] = 0;

	}

	public function dir_closedir() {
		return false;
	}

	public function dir_opendir($path, $options) {
		return false;
	}

	public function dir_readdir() {
		return false;
	}

	public function dir_rewinddir() {
		return false;
	}

	public function stream_cast($cast_as) {
		return false;
	}

	public function stream_flush() {
		return false;
	}

	public function stream_lock($operation) {
		return false;
	}

	public function stream_set_option($option, $arg1, $arg2) {
		return false;
	}
}