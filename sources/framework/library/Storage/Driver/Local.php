<?php
/**
 * 本地文件系统存储
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-1-9
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Local.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Storage_Driver_Local extends Storage_Abstract {

	/**
	 * 存储域
	 *
	 * @var string
	 */
	public $domain = null;

	/**
	 * 本地存储路径
	 *
	 * @var string
	 */
	public $path = '';

	/**
	 * 网络访问URL
	 *
	 * @var string
	 */
	public $url = '';

	/**
	 * 构造函数
	 *
	 * @param array $config 配置数组
	 */
	public function __construct($config = array()) {
		$this->path = isset ( $config ['path'] ) && ! empty ( $config ['path'] ) ? $config ['path'] : BASE_PATH . 'storage' . DIRECTORY_SEPARATOR;
		$this->url = isset ( $config ['url'] ) && ! empty ( $config ['url'] ) ? $config ['url'] : SITE_URL . 'storage/';
	}

	public function set_domain($domain) {
		$this->domain_path = $this->path . $domain . DIRECTORY_SEPARATOR;
		$this->domain_url = $this->url . $domain . DIRECTORY_SEPARATOR;
		if (is_dir ( $this->domain_path ) || is_writable ( $this->domain_path )) {
			return true;
		}
		return false;
	}

	/**
	 * 创建存储域
	 *
	 * @param string $domain 存储域
	 */
	public function create_domain($domain) {
		if (! $this->check_domain ( $domain )) {
			@mkdir ( $this->domain_path );
			@chmod ( $this->domain_path, 0755 );
		}
		return true;
	}

	public function check_domain($domain) {
		$this->domain_path = $this->path . $domain . DIRECTORY_SEPARATOR;
		$this->domain_url = $this->url . $domain . DIRECTORY_SEPARATOR;
		if (is_dir ( $this->domain_path ) || is_writable ( $this->domain_path )) {
			return true;
		}
		return false;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::write()
	 */
	public function write($domain, $dest_filename, $content, $size = -1, $attr = array(), $compress = false) {
		if (! $this->check_domain ( $domain )) { // 检测存储域是否存在
			$this->errno = - 7;
		}
		if (! $handle = fopen ( $this->domain_path . $dest_filename, 'wb+' )) return false;
		if ($size > 0)
			$write_check = fwrite ( $handle, $content, $size );
		else
			$write_check = fwrite ( $handle, $content );
		fclose ( $handle );
		echo 'aaa';
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::delete()
	 */
	public function delete($domain, $filename) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::file_exists()
	 */
	public function file_exists($domain, $filename) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::get_attr()
	 */
	public function get_attr($domain, $filename, $attrKey = array()) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::get_domain_capacity()
	 */
	public function get_domain_capacity($domain) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::get_files_num()
	 */
	public function get_files_num($domain, $path) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::get_list()
	 */
	public function get_list($domain, $prefix = NULL, $limit = 10, $offset = 0) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::get_list_by_path()
	 */
	public function get_list_by_path($domain, $path = NULL, $limit = 100, $offset = 0, $fold = true) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::get_url()
	 */
	public function get_url($domain, $filename) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::read()
	 */
	public function read($domain, $filename) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::set_domain_attr()
	 */
	public function set_domain_attr($domain, $attr = array()) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::set_file_attr()
	 */
	public function set_file_attr($domain, $filename, $attr = array()) {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Storage_Abstract::upload()
	 */
	public function upload($domain, $destFileName, $srcFileName, $attr = array(), $compress = false) {
	}

}