<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: comment_table_model.php 875 2012-06-13 02:19:05Z
 *          85825770@qq.com $
 */
class comment_table_model extends Model {

	public $table_name;

	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'comment_table';
		parent::__construct ();
	}

	/**
	 * 修改表记录总数
	 *
	 * @param integer $tableid
	 *        	表ID
	 * @param string $num
	 *        	要修改的数值（如果要添加请使用+=，如果要减少请使用-=）
	 */
	public function edit_total($tableid, $num) {
		return $this->where(array ('tableid' => $tableid ))->update ( array ('total' => $num ) );
	}

	/**
	 * 创建新的评论数据表
	 *
	 * @param integer $id
	 *        	创建新的评论表
	 */
	public function creat_table($id = '') {
		if (empty ( $id )) $id = $this->insert ( array ('creat_at' => TIME ), true );
		if ($this->query ( "CREATE TABLE `" . $this->prefix . "comment_data_" . $id . "` (`id` int(10) unsigned NOT NULL auto_increment,`commentid` char(30) NOT NULL default '',`userid` int(10) unsigned default '0',`username` varchar(20) default NULL,`creat_at` int(10) default 0,`ip` varchar(15) default NULL,`status` tinyint(1) default '0',`content` text,`support` mediumint(8) unsigned default '0',`reply` tinyint(1) NOT NULL default '0',PRIMARY KEY  (`id`),KEY `commentid` (`commentid`),KEY `support` (`support`)) ENGINE=MyISAM DEFAULT CHARSET=" . $this->db->get_charset () . ";" )) {
			return $id;
		} else {
			return false;
		}
	}
}