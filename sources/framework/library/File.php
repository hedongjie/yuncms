<?php
/**
 * File Class
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: File.php 2 2013-01-14 07:14:05Z xutongle $
 */
class File {

	/**
	 * 文件读取模式
	 * @var int
	 */
	const FILE_READ_MODE = 0644;

	/**
	 * 文件写入模式
	 * @var int
	 */
	const FILE_WRITE_MODE = 0666;

	/**
	 * 目录读取模式
	 * @var int
	 */
	const DIR_READ_MODE = 0755;

	/**
	 * 目录写入模式
	 * @var int
	 */
	const DIR_WRITE_MODE = 0777;

	/**
	 * 以读的方式打开文件，具有较强的平台移植性
	 *
	 * @var string
	 */
	const READ = 'rb';

	/**
	 * 以读写的方式打开文件，具有较强的平台移植性
	 *
	 * @var string
	 */
	const READWRITE = 'rb+';

	/**
	 * 以写的方式打开文件，具有较强的平台移植性
	 *
	 * @var string
	 */
	const WRITE = 'wb';

	/**
	 * 以读写的方式打开文件，具有较强的平台移植性
	 *
	 * @var string
	 */
	const WRITEREAD = 'wb+';

	/**
	 * 以追加写入方式打开文件，具有较强的平台移植性
	 *
	 * @var string
	 */
	const APPEND_WRITE = 'ab';

	/**
	 * 以追加读写入方式打开文件，具有较强的平台移植性
	 *
	 * @var string
	 */
	const APPEND_WRITEREAD = 'ab+';

	/**
	 * 删除文件
	 *
	 * @param string $filename 文件名称
	 * @return boolean
	 */
	public static function delete($filename) {
		return @unlink ( $filename );
	}

	/**
	 * 写文件
	 *
	 * @param string $filename 文件绝对路径
	 * @param string $data 数据
	 * @param string $method 读写模式,默认模式为rb+
	 * @param bool $if_chmod 是否将文件属性改为可读写,默认为true
	 * @return int 返回写入的字节数
	 */
	public static function write($filename, $data, $method = self::READWRITE, $if_chmod = true) {
		touch ( $filename );
		if (! $handle = fopen ( $filename, $method )) return false;
		if (C ( 'config', 'lock_ex' )) flock ( $handle, LOCK_EX );
		$write_check = fwrite ( $handle, $data );
		$method == self::READWRITE && ftruncate ( $handle, strlen ( $data ) );
		fclose ( $handle );
		$if_chmod && @chmod ( $filename, self::DIR_WRITE_MODE );
		return $write_check;
	}

	/**
	 * 读取文件
	 *
	 * @param string $filename 文件绝对路径
	 * @param string $method 读取模式默认模式为rb
	 * @return string 从文件中读取的数据
	 */
	public static function read($filename, $method = self::READ) {
		$data = '';
		if (! file_exists ( $filename )) return false;
		if (! $handle = fopen ( $filename, $method )) return false;
		while ( ! feof ( $handle ) )
			$data .= fgets ( $handle, 4096 );
		fclose ( $handle );
		return $data;
	}

	/**
	 * 文件下载
	 *
	 * @param $filepath 文件路径
	 * @param $filename 文件名称
	 */
	public static function down($filepath, $filename = '') {
		if (! $filename) $filename = basename ( $filepath );
		if (Core_Request::is_ie ()) $filename = rawurlencode ( $filename );
		$filetype = self::get_suffix ( $filename );
		$filesize = sprintf ( "%u", filesize ( $filepath ) );
		if (ob_get_length () !== false) @ob_end_clean ();
		header ( 'Pragma: public' );
		header ( 'Last-Modified: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
		header ( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header ( 'Cache-Control: pre-check=0, post-check=0, max-age=0' );
		header ( 'Content-Transfer-Encoding: binary' );
		header ( 'Content-Encoding: none' );
		header ( 'Content-type: ' . $filetype );
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header ( 'Content-length: ' . $filesize );
		readfile ( $filepath );
		exit ();
	}

	/**
	 * 检查指定的文件名是否是正常的文件
	 *
	 * @param string $filename
	 * @return boolean
	 */
	public static function is_file($filename) {
		return $filename ? is_file ( $filename ) : false;
	}

	/**
	 * 取得文件信息
	 *
	 * @param string $filename 文件名字
	 * @return array 文件信息
	 */
	public static function get_info($filename) {
		return self::is_file ( $filename ) ? stat ( $filename ) : array ();
	}

	/**
	 * 取得文件后缀
	 *
	 * @param string $filename 文件名称
	 * @return string
	 */
	public static function get_suffix($filename) {
		if (false === ($rpos = strrpos ( $filename, '.' ))) return '';
		return substr ( $filename, $rpos + 1 );
	}
}