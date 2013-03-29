<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class poster_stat_model extends model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'poster_' . date ( 'Ym' );
		parent::__construct ();
		if (! $this->table_exists ( $this->table_name )) $this->create_table ();
	}

	/**
	 * 按月份创建表
	 */
	private function create_table() {
		$data_info = C ( 'database', $this->options );
		$charset = $data_info ['charset'];
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table_name . "` (
  		`id` int(10) unsigned NOT NULL auto_increment,
  		`pid` smallint(5) unsigned NOT NULL default '0',
  		`spaceid` smallint(5) unsigned NOT NULL default '0',
  		`username` char(20) NOT NULL,
  		`area` char(40) NOT NULL,
  		`ip` char(15) NOT NULL,
  		`referer` char(120) NOT NULL,
  		`clicktime` int(10) unsigned NOT NULL default '0',
  		`type` tinyint(1) unsigned NOT NULL default '1',
  		PRIMARY KEY  (`id`),
  		KEY `pid` (`pid`,`type`,`ip`)
		) ENGINE=MyISAM DEFAULT CHARSET=" . $charset . " ;";
		$this->query ( $sql );
	}

	/**
	 * 根据查询的日期，改变查询的表
	 *
	 * @param string $tablename
	 *        	表名
	 */
	private function change_table($tablename = '') {
		if ($tablename) $this->table_name = $this->get_prefix () . 'poster_' . $tablename;
	}

	/**
	 * 获取所有广告统计表，并形成下来框
	 *
	 * @param string $year
	 *        	查询的月份
	 * @return boolen/string
	 */
	public function get_list($year = '') {
		$year = isset ( $year ) ? $year : '';
		if ($year) $this->change_table ( $year );
		$this->change_table ( $year );
		$diff1 = date ( 'Y', TIME ); // 当前年份
		$diff2 = date ( 'm', TIME ); // 当前月份
		$diff = ($diff1 - 2010) * 12 + $diff2;
		$selectstr = '';
		for($y = $diff; $y > 0; $y --) {
			$value = date ( 'Ym', mktime ( 0, 0, 0, $y, 1, 2010 ) );
			if ($value < '201206' || ! $this->table_exists ( $this->get_prefix () . 'poster_' . $value )) break;
			$selected = $year == $value ? 'selected' : '';
			$selectstr .= "<option value='$value' $selected>" . date ( "Y-m", mktime ( 0, 0, 0, $y, 1, 2010 ) );
		}
		return $selectstr;
	}
}