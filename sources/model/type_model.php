<?php
/**
 * 类别表
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-1
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: type_model.php 92 2013-03-23 07:56:26Z 85825770@qq.com $
 */
class type_model extends Model {

	function __construct() {
		$this->setting = 'default';
		$this->table_name = 'type';
		parent::__construct ();
	}

	/**
	 * 说明: 查询应用下的分类
	 */
	function get_types() {
		if (! APP) return false;
		return $this->where(array ('application' => APP ))->order('typeid ASC')->select ();
	}
}