<?php
/**
 * 存储抽象类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-1-9
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Abstract.php 2 2013-01-14 07:14:05Z xutongle $
 */
abstract class Storage_Abstract {

	public $errno = '0';

	/**
	 * 构造函数
	 *
	 * @param array $config 配置数组
	 */
	public function __construct($config = array()){

	}

	/**
	 * 删除文件
	 *
	 * @param string $domain 存储域
	 * @param string $filename 文件地址
	 * @return bool
	 */
	abstract public function delete($domain, $filename);

	/**
	 * 返回运行过程中的错误信息
	 *
	 * @return string
	 */
	public function errmsg() {
		$errmsg = array ('0' => '成功','-7' => 'Domain不存在','-18'=>'文件不存在' );
		return isset ( $errmsg [$this->errno] ) ? $errmsg [$this->errno] : '未知错误';
	}

	/**
	 * 返回运行过程中的错误代码
	 *
	 * @return int
	 */
	public function errno() {
		return $this->errno;
	}

	/**
	 * 检查文件是否存在
	 *
	 * @param string $domain 存储域
	 * @param string $filename 文件地址
	 */
	abstract public function file_exists($domain, $filename);

	/**
	 * 获取文件属性
	 *
	 * @param string $domain 存储域
	 * @param string $filename 文件地址
	 * @param array $attrKey 属性值,如 array("fileName",
	 *        "length")，当attrKey为空时，以关联数组方式返回该文件的所有属性。
	 */
	abstract public function get_attr($domain, $filename, $attrKey = array());

	/**
	 * 获取domain所占存储的大小
	 *
	 * @param string $domain 存储域
	 * @return int
	 */
	abstract public function get_domain_capacity($domain);

	/**
	 * 获取指定domain下的文件数量
	 *
	 * @param string $domain
	 * @param string $path
	 * @return 执行成功时返回文件数，否则返回false
	 */
	abstract public function get_files_num($domain, $path);

	/**
	 * 获取指定domain下的文件名列表
	 *
	 * @param string $domain 存储域
	 * @param string $prefix 路径前缀
	 * @param int $limit 返回条数,最大100条,默认10条
	 * @param int $offset 起始条数。limit与offset之和最大为10000，超过此范围无法列出。
	 * @return 执行成功时返回文件列表数组，否则返回false
	 */
	abstract public function get_list($domain, $prefix = NULL, $limit = 10, $offset = 0);

	/**
	 * 获取指定Domain、指定目录下的文件列表
	 *
	 * @param string $domain 存储域
	 * @param string $path 目录地址
	 * @param int $limit 单次返回数量限制，默认100，最大1000
	 * @param int $offset 起始条数
	 * @param int $fold 是否折叠目录
	 * @return 执行成功时返回文件列表数组，否则返回false
	 */
	abstract public function get_list_by_path($domain, $path = NULL, $limit = 100, $offset = 0, $fold = true);

	/**
	 * 取得访问存储文件的url
	 *
	 * @param string $domain
	 * @param string $filename
	 * @return string
	 */
	abstract public function get_url($domain, $filename);

	/**
	 * 读取文件
	 *
	 * @param string $domain
	 * @param string $filename
	 * @return : 成功时返回文件内容，否则返回false
	 */
	abstract public function read($domain, $filename);

	/**
	 * 设置Domain属性
	 * <pre>目前支持的Domain属性
	 *
	 * expires: 浏览器缓存超时，功能与Apache的Expires配置相同
	 * allowReferer: 根据Referer防盗链
	 * private: 是否私有Domain
	 * 404Redirect:
	 * 404跳转页面，只能是本应用页面，或本应用Storage中文件。例如http://appname.sinaapp.com/404.html或http://appname-domain.stor.sinaapp.com/404.png
	 * tag: Domain简介。格式：array('tag1', 'tag2')
	 * </pre>
	 *
	 * @param string $domain 存储域
	 * @param string $attr 文件属性
	 * @return bool
	 */
	abstract public function set_domain_attr($domain, $attr = array());

	/**
	 * 设置文件属性
	 * <pre>目前支持的文件属性
	 * expires: 浏览器缓存超时，功能与Apache的Expires配置相同
	 * encoding: 设置通过Web直接访问文件时，Header中的Content-Encoding。
	 * type: 设置通过Web直接访问文件时，Header中的Content-Type。
	 * private: 设置文件为私有，则文件不可被下载。
	 * </pre>
	 *
	 * @param string $domain 存储域
	 * @param string $filename 文件名
	 * @param array $attr 文件属性。格式：array('attr0'=>'value0', 'attr1'=>'value1',
	 *        ......);
	 * @return bool
	 */
	abstract public function set_file_attr($domain, $filename, $attr = array());

	/**
	 * 将文件上传入存储
	 *
	 * @param string $domain 存储域
	 * @param string $destFileName 目标文件名
	 * @param string $srcFileName 源文件名
	 * @param array $attr 文件属性
	 * @param bool $compress 是否gzip压缩。如果设为true，则文件会经过gzip压缩后再存入Storage，常与
	 *        $attr=array('encoding'=>'gzip')联合使用
	 * @return : 写入成功时返回该文件的下载地址，否则返回false
	 */
	abstract public function upload($domain, $destFileName, $srcFileName, $attr = array(), $compress = false);

	/**
	 * 将数据写入存储
	 *
	 * @param string $domain 存储域
	 * @param string $destFileName 文件名
	 * @param string $content 文件内容,支持二进制数据
	 * @param int $size 写入长度,默认为不限制
	 * @param array $attr 文件属性
	 * @param bool $compress 是否gzip压缩。如果设为true，则文件会经过gzip压缩后再存入Storage，常与
	 *        $attr=array('encoding'=>'gzip')联合使用
	 * @return : 写入成功时返回该文件的下载地址，否则返回false
	 */
	abstract public function write($domain, $destFileName, $content, $size = -1, $attr = array(), $compress = false);
}